<?php

namespace App\Entity;

use App\Repository\StoredFileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StoredFileRepository::class)]
#[ORM\Table(name: 'stored_file')]
class StoredFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $blobName = null;

    #[ORM\Column(length: 255)]
    private ?string $originalName = null;

    #[ORM\Column(length: 100)]
    private ?string $contentType = null;

    #[ORM\Column]
    private ?int $size = null;

    #[ORM\Column(length: 500)]
    private ?string $url = null;

    #[ORM\Column]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $uploadedBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBlobName(): ?string
    {
        return $this->blobName;
    }

    public function setBlobName(string $blobName): static
    {
        $this->blobName = $blobName;
        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): static
    {
        $this->originalName = $originalName;
        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): static
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUploadedBy(): ?User
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(?User $uploadedBy): static
    {
        $this->uploadedBy = $uploadedBy;
        return $this;
    }

    public function getFormattedSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->contentType, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->contentType === 'application/pdf';
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->contentType, 'video/');
    }

    public function isAudio(): bool
    {
        return str_starts_with($this->contentType, 'audio/');
    }
}
