<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Table(name: 'comment')]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AssignmentSubmission::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AssignmentSubmission $submission = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column(type: 'text')]
    private ?string $body = null;

    #[ORM\Column]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isInternal = false;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $attachments = [];

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->attachments = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubmission(): ?AssignmentSubmission
    {
        return $this->submission;
    }

    public function setSubmission(?AssignmentSubmission $submission): static
    {
        $this->submission = $submission;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;
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

    public function getFormattedCreatedAt(): string
    {
        return $this->createdAt ? $this->createdAt->format('M j, Y g:i A') : '';
    }

    public function getRelativeTime(): string
    {
        if (!$this->createdAt) {
            return '';
        }

        $now = new \DateTime();
        $interval = $this->createdAt->diff($now);

        if ($interval->y > 0) {
            return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
        } elseif ($interval->m > 0) {
            return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
        } elseif ($interval->d > 0) {
            return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            return 'Just now';
        }
    }

    public function isInternal(): bool
    {
        return $this->isInternal;
    }

    public function setIsInternal(bool $isInternal): static
    {
        $this->isInternal = $isInternal;
        return $this;
    }

    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    public function setAttachments(?array $attachments): static
    {
        $this->attachments = $attachments;
        return $this;
    }

    public function addAttachment(string $attachment): static
    {
        if (!in_array($attachment, $this->attachments)) {
            $this->attachments[] = $attachment;
        }
        return $this;
    }

    public function removeAttachment(string $attachment): static
    {
        $key = array_search($attachment, $this->attachments);
        if ($key !== false) {
            unset($this->attachments[$key]);
            $this->attachments = array_values($this->attachments);
        }
        return $this;
    }

    public function getAttachmentCount(): int
    {
        return count($this->attachments);
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    public function isAuthor(User $user): bool
    {
        return $this->author === $user;
    }

    public function canBeEditedBy(User $user): bool
    {
        // Authors can edit their own comments
        if ($this->isAuthor($user)) {
            return true;
        }

        // Teachers can edit comments on submissions for their courses
        if ($this->submission && $this->submission->canBeGradedBy($user)) {
            return true;
        }

        return false;
    }

    public function canBeDeletedBy(User $user): bool
    {
        // Authors can delete their own comments
        if ($this->isAuthor($user)) {
            return true;
        }

        // Teachers can delete comments on submissions for their courses
        if ($this->submission && $this->submission->canBeGradedBy($user)) {
            return true;
        }

        return false;
    }

    public function getAssignment(): ?Assignment
    {
        return $this->submission ? $this->submission->getAssignment() : null;
    }

    public function getCourse(): ?Course
    {
        $assignment = $this->getAssignment();
        return $assignment ? $assignment->getCourse() : null;
    }

    public function getShortBody(int $maxLength = 100): string
    {
        if (strlen($this->body) <= $maxLength) {
            return $this->body;
        }

        return substr($this->body, 0, $maxLength) . '...';
    }

    public function getWordCount(): int
    {
        return str_word_count(strip_tags($this->body));
    }

    public function isRecent(): bool
    {
        if (!$this->createdAt) {
            return false;
        }

        $now = new \DateTime();
        $interval = $this->createdAt->diff($now);
        
        // Consider recent if less than 24 hours old
        return $interval->days === 0;
    }
}
