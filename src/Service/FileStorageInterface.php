<?php

namespace App\Service;

use App\Entity\StoredFile;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileStorageInterface
{
    /**
     * Upload a file to storage
     *
     * @param string $path The path where the file should be stored
     * @param UploadedFile $file The uploaded file
     * @param User $uploadedBy The user who uploaded the file
     * @return StoredFile The stored file entity with metadata
     * @throws \Exception If upload fails
     */
    public function upload(string $path, UploadedFile $file, User $uploadedBy): StoredFile;

    /**
     * Delete a file from storage
     *
     * @param string $blobName The name/identifier of the blob to delete
     * @throws \Exception If deletion fails
     */
    public function delete(string $blobName): void;

    /**
     * Generate a temporary URL for a file
     *
     * @param string $blobName The name/identifier of the blob
     * @param \DateInterval $ttl Time to live for the temporary URL
     * @return string The temporary URL
     * @throws \Exception If URL generation fails
     */
    public function temporaryUrl(string $blobName, \DateInterval $ttl): string;

    /**
     * Get the public URL for a file (if applicable)
     *
     * @param string $blobName The name/identifier of the blob
     * @return string The public URL
     */
    public function getPublicUrl(string $blobName): string;

    /**
     * Test the connection to the storage service
     *
     * @return bool True if connection is successful, false otherwise
     */
    public function testConnection(): bool;
}
