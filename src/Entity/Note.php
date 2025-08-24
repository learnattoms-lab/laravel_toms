<?php

namespace App\Entity;

use App\Repository\NoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[ORM\Table(name: 'note')]
#[ORM\UniqueConstraint(name: 'unique_user_lesson', columns: ['user_id', 'lesson_id'])]
class Note
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Lesson::class, inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lesson $lesson = null;

    #[ORM\Column(type: 'text')]
    private ?string $body = null;

    #[ORM\Column]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $tags = [];

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPublic = false;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $wordCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $characterCount = 0;

    public function __construct()
    {
        $this->updatedAt = new \DateTime();
        $this->tags = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;
        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;
        $this->updatedAt = new \DateTime();
        $this->updateCounts();
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): static
    {
        $this->tags = $tags;
        return $this;
    }

    public function addTag(string $tag): static
    {
        if (!in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
        return $this;
    }

    public function removeTag(string $tag): static
    {
        $key = array_search($tag, $this->tags);
        if ($key !== false) {
            unset($this->tags[$key]);
            $this->tags = array_values($this->tags);
        }
        return $this;
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags);
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    public function getWordCount(): int
    {
        return $this->wordCount;
    }

    public function setWordCount(int $wordCount): static
    {
        $this->wordCount = $wordCount;
        return $this;
    }

    public function getCharacterCount(): int
    {
        return $this->characterCount;
    }

    public function setCharacterCount(int $characterCount): static
    {
        $this->characterCount = $characterCount;
        return $this;
    }

    public function getFormattedUpdatedAt(): string
    {
        return $this->updatedAt ? $this->updatedAt->format('M j, Y g:i A') : '';
    }

    public function getRelativeUpdatedAt(): string
    {
        if (!$this->updatedAt) {
            return '';
        }

        $now = new \DateTime();
        $interval = $this->updatedAt->diff($now);

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

    public function getShortBody(int $maxLength = 150): string
    {
        if (strlen($this->body) <= $maxLength) {
            return $this->body;
        }

        return substr($this->body, 0, $maxLength) . '...';
    }

    public function isEmpty(): bool
    {
        return empty(trim($this->body));
    }

    public function getReadingTime(): int
    {
        // Average reading speed: 200 words per minute
        return max(1, intval($this->wordCount / 200));
    }

    public function getFormattedReadingTime(): string
    {
        $minutes = $this->getReadingTime();
        if ($minutes < 1) {
            return 'Less than 1 minute';
        } elseif ($minutes === 1) {
            return '1 minute';
        } else {
            return $minutes . ' minutes';
        }
    }

    public function getCourse(): ?Course
    {
        return $this->lesson ? $this->lesson->getCourse() : null;
    }

    public function canBeViewedBy(User $user): bool
    {
        // Users can always view their own notes
        if ($this->user === $user) {
            return true;
        }

        // Public notes can be viewed by anyone
        if ($this->isPublic) {
            return true;
        }

        // Teachers can view notes from their courses
        $course = $this->getCourse();
        if ($course && $course->getTeacher() === $user) {
            return true;
        }

        return false;
    }

    public function canBeEditedBy(User $user): bool
    {
        // Users can only edit their own notes
        return $this->user === $user;
    }

    public function canBeDeletedBy(User $user): bool
    {
        // Users can only delete their own notes
        return $this->user === $user;
    }

    public function getNoteType(): string
    {
        if (empty($this->body)) {
            return 'empty';
        }

        $body = strtolower($this->body);
        
        if (strpos($body, 'question') !== false || strpos($body, '?') !== false) {
            return 'question';
        }
        
        if (strpos($body, 'practice') !== false || strpos($body, 'exercise') !== false) {
            return 'practice';
        }
        
        if (strpos($body, 'reminder') !== false || strpos($body, 'remember') !== false) {
            return 'reminder';
        }
        
        return 'general';
    }

    public function getNoteTypeIcon(): string
    {
        $type = $this->getNoteType();
        
        $icons = [
            'empty' => 'far fa-sticky-note',
            'question' => 'fas fa-question-circle',
            'practice' => 'fas fa-dumbbell',
            'reminder' => 'fas fa-bell',
            'general' => 'fas fa-edit'
        ];
        
        return $icons[$type] ?? 'far fa-sticky-note';
    }

    public function getNoteTypeColor(): string
    {
        $type = $this->getNoteType();
        
        $colors = [
            'empty' => 'text-muted',
            'question' => 'text-primary',
            'practice' => 'text-success',
            'reminder' => 'text-warning',
            'general' => 'text-info'
        ];
        
        return $colors[$type] ?? 'text-muted';
    }

    private function updateCounts(): void
    {
        $cleanBody = strip_tags($this->body);
        $this->wordCount = str_word_count($cleanBody);
        $this->characterCount = strlen($cleanBody);
    }

    public function getFormattedSize(): string
    {
        if ($this->isEmpty()) {
            return 'Empty';
        }
        
        return $this->wordCount . ' words, ' . $this->characterCount . ' characters';
    }

    public function isRecent(): bool
    {
        if (!$this->updatedAt) {
            return false;
        }

        $now = new \DateTime();
        $interval = $this->updatedAt->diff($now);
        
        // Consider recent if less than 24 hours old
        return $interval->days === 0;
    }
}
