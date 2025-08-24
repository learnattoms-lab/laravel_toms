<?php

namespace App\Entity;

use App\Repository\AssignmentSubmissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssignmentSubmissionRepository::class)]
#[ORM\Table(name: 'assignment_submission')]
class AssignmentSubmission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Assignment::class, inversedBy: 'submissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Assignment $assignment = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $student = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'json')]
    private ?array $files = [];

    #[ORM\Column]
    private ?\DateTimeInterface $submittedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $gradedBy = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $gradePoints = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $feedbackHtml = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $gradedAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isLate = false;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $latePenaltyApplied = 0;

    #[ORM\OneToMany(mappedBy: 'submission', targetEntity: Comment::class, cascade: ['persist', 'remove'])]
    private Collection $comments;

    public function __construct()
    {
        $this->submittedAt = new \DateTime();
        $this->files = [];
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAssignment(): ?Assignment
    {
        return $this->assignment;
    }

    public function setAssignment(?Assignment $assignment): static
    {
        $this->assignment = $assignment;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getFiles(): ?array
    {
        return $this->files;
    }

    public function setFiles(?array $files): static
    {
        $this->files = $files;
        return $this;
    }

    public function addFile(string $file): static
    {
        if (!in_array($file, $this->files)) {
            $this->files[] = $file;
        }
        return $this;
    }

    public function removeFile(string $file): static
    {
        $key = array_search($file, $this->files);
        if ($key !== false) {
            unset($this->files[$key]);
            $this->files = array_values($this->files);
        }
        return $this;
    }

    public function getFileCount(): int
    {
        return count($this->files);
    }

    public function getSubmittedAt(): ?\DateTimeInterface
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(\DateTimeInterface $submittedAt): static
    {
        $this->submittedAt = $submittedAt;
        return $this;
    }

    public function getGradedBy(): ?User
    {
        return $this->gradedBy;
    }

    public function setGradedBy(?User $gradedBy): static
    {
        $this->gradedBy = $gradedBy;
        return $this;
    }

    public function getGradePoints(): ?int
    {
        return $this->gradePoints;
    }

    public function setGradePoints(?int $gradePoints): static
    {
        $this->gradePoints = $gradePoints;
        if ($gradePoints !== null) {
            $this->gradedAt = new \DateTime();
        }
        return $this;
    }

    public function getGradePercentage(): ?float
    {
        if ($this->gradePoints === null || !$this->assignment) {
            return null;
        }
        
        return ($this->gradePoints / $this->assignment->getMaxPoints()) * 100;
    }

    public function getFormattedGrade(): string
    {
        if ($this->gradePoints === null) {
            return 'Not graded';
        }
        
        $percentage = $this->getGradePercentage();
        if ($percentage >= 90) {
            return 'A (' . $this->gradePoints . '/' . $this->assignment->getMaxPoints() . ')';
        } elseif ($percentage >= 80) {
            return 'B (' . $this->gradePoints . '/' . $this->assignment->getMaxPoints() . ')';
        } elseif ($percentage >= 70) {
            return 'C (' . $this->gradePoints . '/' . $this->assignment->getMaxPoints() . ')';
        } elseif ($percentage >= 60) {
            return 'D (' . $this->gradePoints . '/' . $this->assignment->getMaxPoints() . ')';
        } else {
            return 'F (' . $this->gradePoints . '/' . $this->assignment->getMaxPoints() . ')';
        }
    }

    public function getGradeLetter(): ?string
    {
        $percentage = $this->getGradePercentage();
        if ($percentage === null) {
            return null;
        }
        
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    public function getFeedbackHtml(): ?string
    {
        return $this->feedbackHtml;
    }

    public function setFeedbackHtml(?string $feedbackHtml): static
    {
        $this->feedbackHtml = $feedbackHtml;
        return $this;
    }

    public function getGradedAt(): ?\DateTimeInterface
    {
        return $this->gradedAt;
    }

    public function setGradedAt(?\DateTimeInterface $gradedAt): static
    {
        $this->gradedAt = $gradedAt;
        return $this;
    }

    public function isGraded(): bool
    {
        return $this->gradePoints !== null;
    }

    public function isLate(): bool
    {
        return $this->isLate;
    }

    public function setIsLate(bool $isLate): static
    {
        $this->isLate = $isLate;
        return $this;
    }

    public function getLatePenaltyApplied(): int
    {
        return $this->latePenaltyApplied;
    }

    public function setLatePenaltyApplied(int $latePenaltyApplied): static
    {
        $this->latePenaltyApplied = $latePenaltyApplied;
        return $this;
    }

    public function getFinalGrade(): ?int
    {
        if ($this->gradePoints === null) {
            return null;
        }
        
        if ($this->isLate && $this->latePenaltyApplied > 0) {
            $penalty = ($this->gradePoints * $this->latePenaltyApplied) / 100;
            return max(0, (int) ($this->gradePoints - $penalty));
        }
        
        return $this->gradePoints;
    }

    public function getFormattedFinalGrade(): string
    {
        $finalGrade = $this->getFinalGrade();
        if ($finalGrade === null) {
            return 'Not graded';
        }
        
        return $finalGrade . '/' . $this->assignment->getMaxPoints();
    }

    public function getDaysLate(): int
    {
        if (!$this->assignment || !$this->assignment->getDueAt()) {
            return 0;
        }
        
        $dueDate = $this->assignment->getDueAt();
        $submittedDate = $this->submittedAt;
        
        if ($submittedDate <= $dueDate) {
            return 0;
        }
        
        $interval = $dueDate->diff($submittedDate);
        return $interval->days;
    }

    public function getFormattedDaysLate(): string
    {
        $daysLate = $this->getDaysLate();
        if ($daysLate === 0) {
            return 'On time';
        }
        
        return $daysLate . ' day' . ($daysLate > 1 ? 's' : '') . ' late';
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setSubmission($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getSubmission() === $this) {
                $comment->setSubmission(null);
            }
        }

        return $this;
    }

    public function getCommentCount(): int
    {
        return $this->comments->count();
    }

    public function getStatus(): string
    {
        if ($this->isGraded()) {
            return 'graded';
        }
        
        if ($this->isLate) {
            return 'late';
        }
        
        return 'submitted';
    }

    public function getStatusLabel(): string
    {
        $labels = [
            'submitted' => 'Submitted',
            'late' => 'Late',
            'graded' => 'Graded'
        ];
        
        return $labels[$this->getStatus()] ?? ucfirst($this->getStatus());
    }

    public function getStatusBadgeClass(): string
    {
        $classes = [
            'submitted' => 'badge bg-info',
            'late' => 'badge bg-warning',
            'graded' => 'badge bg-success'
        ];
        
        return $classes[$this->getStatus()] ?? 'badge bg-secondary';
    }

    public function canBeGradedBy(User $user): bool
    {
        if (!$this->assignment) {
            return false;
        }
        
        $course = $this->assignment->getCourse();
        if (!$course) {
            return false;
        }
        
        // Only teachers of the course can grade
        return $course->getTeacher() === $user;
    }

    public function canBeEditedBy(User $user): bool
    {
        // Students can edit their own submissions if not graded
        if ($this->student === $user && !$this->isGraded()) {
            return true;
        }
        
        // Teachers can edit submissions for their courses
        return $this->canBeGradedBy($user);
    }
}
