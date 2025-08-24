<?php

namespace App\Entity;

use App\Repository\EnrollmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnrollmentRepository::class)]
#[ORM\Table(name: 'enrollment')]
#[ORM\UniqueConstraint(name: 'unique_student_course', columns: ['student_id', 'course_id'])]
class Enrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $student = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, options: ['default' => 0.00])]
    private ?string $progressPct = null;

    #[ORM\Column]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastAccessedAt = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $lessonsCompleted = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $totalLessons = 0;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $completedLessons = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $quizScores = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $assignmentScores = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->startedAt = new \DateTime();
        $this->status = 'active';
        $this->progressPct = '0.00';
        $this->lessonsCompleted = 0;
        $this->totalLessons = 0;
        $this->completedLessons = [];
        $this->quizScores = [];
        $this->assignmentScores = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;
        if ($course) {
            $this->totalLessons = $course->getTotalLessons();
        }
        return $this;
    }

    public function getStudent(): ?User
    {
        return $this->student;
    }

    public function setStudent(?User $student): static
    {
        $this->student = $student;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->updatedAt = new \DateTime();
        
        if ($status === 'completed' && !$this->completedAt) {
            $this->completedAt = new \DateTime();
        }
        
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): static
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getProgressPct(): ?string
    {
        return $this->progressPct;
    }

    public function setProgressPct(string $progressPct): static
    {
        $this->progressPct = $progressPct;
        return $this;
    }

    public function getProgressPercentage(): float
    {
        return (float) $this->progressPct;
    }

    public function getFormattedProgress(): string
    {
        return number_format($this->getProgressPercentage(), 1) . '%';
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getLastAccessedAt(): ?\DateTimeInterface
    {
        return $this->lastAccessedAt;
    }

    public function setLastAccessedAt(?\DateTimeInterface $lastAccessedAt): static
    {
        $this->lastAccessedAt = $lastAccessedAt;
        return $this;
    }

    public function updateLastAccessed(): static
    {
        $this->lastAccessedAt = new \DateTime();
        return $this;
    }

    public function getLessonsCompleted(): int
    {
        return $this->lessonsCompleted;
    }

    public function setLessonsCompleted(int $lessonsCompleted): static
    {
        $this->lessonsCompleted = $lessonsCompleted;
        $this->updateProgress();
        return $this;
    }

    public function getTotalLessons(): int
    {
        return $this->totalLessons;
    }

    public function setTotalLessons(int $totalLessons): static
    {
        $this->totalLessons = $totalLessons;
        $this->updateProgress();
        return $this;
    }

    public function getCompletedLessons(): ?array
    {
        return $this->completedLessons;
    }

    public function setCompletedLessons(?array $completedLessons): static
    {
        $this->completedLessons = $completedLessons;
        $this->lessonsCompleted = count($completedLessons);
        $this->updateProgress();
        return $this;
    }

    public function addCompletedLesson(int $lessonId): static
    {
        if (!in_array($lessonId, $this->completedLessons)) {
            $this->completedLessons[] = $lessonId;
            $this->lessonsCompleted = count($this->completedLessons);
            $this->updateProgress();
        }
        return $this;
    }

    public function removeCompletedLesson(int $lessonId): static
    {
        $key = array_search($lessonId, $this->completedLessons);
        if ($key !== false) {
            unset($this->completedLessons[$key]);
            $this->completedLessons = array_values($this->completedLessons);
            $this->lessonsCompleted = count($this->completedLessons);
            $this->updateProgress();
        }
        return $this;
    }

    public function isLessonCompleted(int $lessonId): bool
    {
        return in_array($lessonId, $this->completedLessons);
    }

    public function getQuizScores(): ?array
    {
        return $this->quizScores;
    }

    public function setQuizScores(?array $quizScores): static
    {
        $this->quizScores = $quizScores;
        return $this;
    }

    public function addQuizScore(int $quizId, float $score): static
    {
        $this->quizScores[$quizId] = $score;
        return $this;
    }

    public function getQuizScore(int $quizId): ?float
    {
        return $this->quizScores[$quizId] ?? null;
    }

    public function getAssignmentScores(): ?array
    {
        return $this->assignmentScores;
    }

    public function setAssignmentScores(?array $assignmentScores): static
    {
        $this->assignmentScores = $assignmentScores;
        return $this;
    }

    public function addAssignmentScore(int $assignmentId, float $score): static
    {
        $this->assignmentScores[$assignmentId] = $score;
        return $this;
    }

    public function getAssignmentScore(int $assignmentId): ?float
    {
        return $this->assignmentScores[$assignmentId] ?? null;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    private function updateProgress(): void
    {
        if ($this->totalLessons > 0) {
            $this->progressPct = number_format(($this->lessonsCompleted / $this->totalLessons) * 100, 2);
            
            // Auto-complete if all lessons are done
            if ($this->lessonsCompleted >= $this->totalLessons && $this->status === 'active') {
                $this->setStatus('completed');
            }
        } else {
            $this->progressPct = '0.00';
        }
    }

    public function getAverageQuizScore(): float
    {
        if (empty($this->quizScores)) {
            return 0.0;
        }
        
        return array_sum($this->quizScores) / count($this->quizScores);
    }

    public function getAverageAssignmentScore(): float
    {
        if (empty($this->assignmentScores)) {
            return 0.0;
        }
        
        return array_sum($this->assignmentScores) / count($this->assignmentScores);
    }

    public function getOverallScore(): float
    {
        $quizScore = $this->getAverageQuizScore();
        $assignmentScore = $this->getAverageAssignmentScore();
        
        if ($quizScore > 0 && $assignmentScore > 0) {
            return ($quizScore + $assignmentScore) / 2;
        } elseif ($quizScore > 0) {
            return $quizScore;
        } elseif ($assignmentScore > 0) {
            return $assignmentScore;
        }
        
        return 0.0;
    }

    public function getFormattedOverallScore(): string
    {
        return number_format($this->getOverallScore(), 1) . '%';
    }

    public function getEnrollmentDuration(): \DateInterval
    {
        $start = $this->startedAt;
        $end = $this->completedAt ?? new \DateTime();
        
        return $start->diff($end);
    }

    public function getFormattedDuration(): string
    {
        $interval = $this->getEnrollmentDuration();
        
        if ($interval->y > 0) {
            return $interval->y . ' year' . ($interval->y > 1 ? 's' : '');
        } elseif ($interval->m > 0) {
            return $interval->m . ' month' . ($interval->m > 1 ? 's' : '');
        } elseif ($interval->d > 0) {
            return $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
        } else {
            return 'Less than a day';
        }
    }

    public function canAccessCourse(): bool
    {
        return $this->isActive() || $this->isCompleted();
    }

    public function getNextLesson(): ?Lesson
    {
        if (!$this->course) {
            return null;
        }

        $lessons = $this->course->getLessons();
        foreach ($lessons as $lesson) {
            if (!$this->isLessonCompleted($lesson->getId())) {
                return $lesson;
            }
        }

        return null;
    }
}
