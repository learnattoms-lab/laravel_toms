<?php

namespace App\Service;

use App\Entity\StoredFile;
use App\Entity\User;
use App\Repository\StoredFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\DeleteBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\GenerateBlobSharedAccessSignatureOptions;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AzureBlobStorageService implements FileStorageInterface
{
    private BlobRestProxy $blobClient;
    private string $containerName;
    private string $publicBaseUrl;
    private EntityManagerInterface $entityManager;
    private StoredFileRepository $storedFileRepository;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        StoredFileRepository $storedFileRepository,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->storedFileRepository = $storedFileRepository;
        $this->logger = $logger;
        
        $this->initializeAzureClient();
    }

    private function initializeAzureClient(): void
    {
        $connectionString = $_ENV['AZURE_BLOB_CONNECTION_STRING'] ?? '';
        if (empty($connectionString)) {
            throw new \Exception('Azure Blob Storage connection string not configured');
        }

        $this->blobClient = BlobRestProxy::createBlobService($connectionString);
        $this->containerName = $_ENV['AZURE_BLOB_CONTAINER'] ?? 'toms-lms';
        $this->publicBaseUrl = $_ENV['AZURE_BLOB_PUBLIC_BASE'] ?? '';
    }

    public function upload(string $path, UploadedFile $file, User $uploadedBy): StoredFile
    {
        try {
            // Generate unique blob name
            $extension = $file->getClientOriginalExtension();
            $blobName = $this->generateBlobName($path, $extension);
            
            // Read file content
            $fileContent = file_get_contents($file->getPathname());
            if ($fileContent === false) {
                throw new \Exception('Failed to read uploaded file');
            }

            // Set blob options
            $options = new CreateBlobOptions();
            $options->setBlobContentType($file->getMimeType());
            $options->setBlobContentMD5(base64_encode(md5($fileContent, true)));

            // Upload to Azure
            $this->blobClient->createBlockBlob(
                $this->containerName,
                $blobName,
                $fileContent,
                $options
            );

            // Create StoredFile entity
            $storedFile = new StoredFile();
            $storedFile->setBlobName($blobName);
            $storedFile->setOriginalName($file->getClientOriginalName());
            $storedFile->setContentType($file->getMimeType());
            $storedFile->setSize($file->getSize());
            $storedFile->setUrl($this->getPublicUrl($blobName));
            $storedFile->setUploadedBy($uploadedBy);

            // Persist to database
            $this->entityManager->persist($storedFile);
            $this->entityManager->flush();

            $this->logger->info('File uploaded successfully to Azure Blob Storage', [
                'blob_name' => $blobName,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'user_id' => $uploadedBy->getId()
            ]);

            return $storedFile;

        } catch (ServiceException $e) {
            $this->logger->error('Azure Blob Storage upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            throw new \Exception('Failed to upload file to cloud storage: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('File upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            throw $e;
        }
    }

    public function delete(string $blobName): void
    {
        try {
            // Delete from Azure
            $options = new DeleteBlobOptions();
            $this->blobClient->deleteBlob($this->containerName, $blobName, $options);

            // Find and remove from database
            $storedFile = $this->storedFileRepository->findByBlobName($blobName);
            if ($storedFile) {
                $this->entityManager->remove($storedFile);
                $this->entityManager->flush();
            }

            $this->logger->info('File deleted successfully from Azure Blob Storage', [
                'blob_name' => $blobName
            ]);

        } catch (ServiceException $e) {
            $this->logger->error('Azure Blob Storage deletion failed', [
                'blob_name' => $blobName,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to delete file from cloud storage: ' . $e->getMessage());
        }
    }

    public function temporaryUrl(string $blobName, \DateInterval $ttl): string
    {
        try {
            $options = new GenerateBlobSharedAccessSignatureOptions();
            $options->setExpiry((new \DateTime())->add($ttl));
            $options->setPermissions('r'); // Read permission only

            $sasToken = $this->blobClient->generateBlobSharedAccessSignature(
                $this->containerName,
                $blobName,
                $options
            );

            return $this->getPublicUrl($blobName) . '?' . $sasToken;

        } catch (ServiceException $e) {
            $this->logger->error('Failed to generate SAS token', [
                'blob_name' => $blobName,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to generate temporary download link: ' . $e->getMessage());
        }
    }

    public function getPublicUrl(string $blobName): string
    {
        if (!empty($this->publicBaseUrl)) {
            return rtrim($this->publicBaseUrl, '/') . '/' . $blobName;
        }

        // Fallback to connection string-based URL
        return sprintf(
            'https://%s.blob.core.windows.net/%s/%s',
            $this->extractAccountName(),
            $this->containerName,
            $blobName
        );
    }

    public function downloadUrl(string $blobName, int $ttlMinutes = 60): string
    {
        $ttl = new \DateInterval('PT' . $ttlMinutes . 'M');
        return $this->temporaryUrl($blobName, $ttl);
    }

    public function copyFile(string $sourceBlobName, string $destinationPath, string $newExtension = null): StoredFile
    {
        try {
            $extension = $newExtension ?: pathinfo($sourceBlobName, PATHINFO_EXTENSION);
            $newBlobName = $this->generateBlobName($destinationPath, $extension);

            // Copy blob in Azure
            $this->blobClient->copyBlob(
                $this->containerName,
                $newBlobName,
                $this->containerName,
                $sourceBlobName
            );

            // Get source file metadata
            $sourceFile = $this->storedFileRepository->findByBlobName($sourceBlobName);
            if (!$sourceFile) {
                throw new \Exception('Source file not found in database');
            }

            // Create new StoredFile entity
            $newStoredFile = new StoredFile();
            $newStoredFile->setBlobName($newBlobName);
            $newStoredFile->setOriginalName($sourceFile->getOriginalName());
            $newStoredFile->setContentType($sourceFile->getContentType());
            $newStoredFile->setSize($sourceFile->getSize());
            $newStoredFile->setUrl($this->getPublicUrl($newBlobName));
            $newStoredFile->setUploadedBy($sourceFile->getUploadedBy());

            $this->entityManager->persist($newStoredFile);
            $this->entityManager->flush();

            return $newStoredFile;

        } catch (ServiceException $e) {
            $this->logger->error('Azure Blob Storage copy failed', [
                'source' => $sourceBlobName,
                'destination' => $destinationPath,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to copy file: ' . $e->getMessage());
        }
    }

    public function fileExists(string $blobName): bool
    {
        try {
            $this->blobClient->getBlobProperties($this->containerName, $blobName);
            return true;
        } catch (ServiceException $e) {
            if ($e->getCode() === 404) {
                return false;
            }
            throw new \Exception('Failed to check file existence: ' . $e->getMessage());
        }
    }

    public function getFileProperties(string $blobName): array
    {
        try {
            $properties = $this->blobClient->getBlobProperties($this->containerName, $blobName);
            
            return [
                'size' => $properties->getContentLength(),
                'content_type' => $properties->getContentType(),
                'last_modified' => $properties->getLastModified(),
                'etag' => $properties->getETag()
            ];
        } catch (ServiceException $e) {
            $this->logger->error('Failed to get blob properties', [
                'blob_name' => $blobName,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to get file properties: ' . $e->getMessage());
        }
    }

    private function generateBlobName(string $path, string $extension): string
    {
        $timestamp = (new \DateTime())->format('Y/m/d/H/i/s');
        $random = bin2hex(random_bytes(8));
        
        $path = trim($path, '/');
        return sprintf('%s/%s_%s.%s', $path, $timestamp, $random, $extension);
    }

    private function extractAccountName(): string
    {
        $connectionString = $_ENV['AZURE_BLOB_CONNECTION_STRING'] ?? '';
        if (preg_match('/AccountName=([^;]+)/', $connectionString, $matches)) {
            return $matches[1];
        }
        
        throw new \Exception('Could not extract account name from connection string');
    }

    public function getContainerUrl(): string
    {
        return sprintf(
            'https://%s.blob.core.windows.net/%s',
            $this->extractAccountName(),
            $this->containerName
        );
    }

    public function testConnection(): bool
    {
        try {
            // Try to list blobs (limit to 1 to minimize data transfer)
            $this->blobClient->listBlobs($this->containerName, ['maxResults' => 1]);
            return true;
        } catch (ServiceException $e) {
            $this->logger->error('Azure Blob Storage connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
