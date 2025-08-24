<?php

namespace App\Entity;

use App\Repository\AssignmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssignmentRepository::class)]
#[ORM\Table(name: 'assignment')]
class Assignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Lesson::class, inversedBy: 'assignments')]
    private ?Lesson $lesson = null;

    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'assignments')]
    private ?Session $session = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $instructionsHtml = null;

    #[ORM\Column]
    private ?\DateTimeInterface $dueAt = null;

    #[ORM\Column]
    private ?int $maxPoints = null;

    #[ORM\Column]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'assignment', targetEntity: AssignmentSubmission::class, cascade: ['persist', 'remove'])]
    private Collection $submissions;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $rubric = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $attachments = [];

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isRequired = true;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $allowLateSubmission = false;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $latePenalty = 0; // Percentage penalty for late submissions

    public function __construct()
    {
        $this->submissions = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->attachments = [];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): static
    {
        $this->session = $session;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getInstructionsHtml(): ?string
    {
        return $this->instructionsHtml;
    }

    public function setInstructionsHtml(string $instructionsHtml): static
    {
        $this->instructionsHtml = $instructionsHtml;
        return $this;
    }

    public function getDueAt(): ?\DateTimeInterface
    {
        return $this->dueAt;
    }

    public function setDueAt(\DateTimeInterface $dueAt): static
    {
        $this->dueAt = $dueAt;
        return $this;
    }

    public function isOverdue(): bool
    {
        return $this->dueAt < new \DateTime();
    }

    public function getDaysUntilDue(): int
    {
        $now = new \DateTime();
        $interval = $now->diff($this->dueAt);
        return $interval->invert ? -$interval->days : $interval->days;
    }

    public function getFormattedDueDate(): string
    {
        return $this->dueAt ? $this->dueAt->format('M j, Y g:i A') : '';
    }

    public function getMaxPoints(): ?int
    {
        return $this->maxPoints;
    }

    public function setMaxPoints(int $maxPoints): static
    {
        $this->maxPoints = $maxPoints;
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

    public function getRubric(): ?string
    {
        return $this->rubric;
    }

    public function setRubric(?string $rubric): static
    {
        $this->rubric = $rubric;
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

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): static
    {
        $this->isRequired = $isRequired;
        return $this;
    }

    public function isAllowLateSubmission(): bool
    {
        return $this->allowLateSubmission;
    }

    public function setAllowLateSubmission(bool $allowLateSubmission): static
    {
        $this->allowLateSubmission = $allowLateSubmission;
        return $this;
    }

    public function getLatePenalty(): int
    {
        return $this->latePenalty;
    }

    public function setLatePenalty(int $latePenalty): static
    {
        $this->latePenalty = $latePenalty;
        return $this;
    }

    /**
     * @return Collection<int, AssignmentSubmission>
     */
    public function getSubmissions(): Collection
    {
        return $this->submissions;
    }

    public function addSubmission(AssignmentSubmission $submission): static
    {
        if (!$this->submissions->contains($submission)) {
            $this->submissions->add($submission);
            $submission->setAssignment($this);
        }

        return $this;
    }

    public function removeSubmission(AssignmentSubmission $submission): static
    {
        if ($this->submissions->removeElement($submission)) {
            if ($submission->getAssignment() === $this) {
                $submission->setAssignment(null);
            }
        }

        return $this;
    }

    public function getSubmissionByUser(User $user): ?AssignmentSubmission
    {
        return $this->submissions->filter(function(AssignmentSubmission $submission) use ($user) {
            return $submission->getStudent() === $user;
        })->first();
    }

    public function hasSubmissionFromUser(User $user): bool
    {
        return $this->getSubmissionByUser($user) !== null;
    }

    public function getSubmissionCount(): int
    {
        return $this->submissions->count();
    }

    public function getGradedSubmissionCount(): int
    {
        return $this->submissions->filter(function(AssignmentSubmission $submission) {
            return $submission->getGradePoints() !== null;
        })->count();
    }

    public function getAverageScore(): float
    {
        $gradedSubmissions = $this->submissions->filter(function(AssignmentSubmission $submission) {
            return $submission->getGradePoints() !== null;
        });

        if ($gradedSubmissions->isEmpty()) {
            return 0.0;
        }

        $totalScore = 0;
        foreach ($gradedSubmissions as $submission) {
            $totalScore += $submission->getGradePoints();
        }

        return $totalScore / $gradedSubmissions->count();
    }

    public function getFormattedAverageScore(): string
    {
        return number_format($this->getAverageScore(), 1) . '/' . $this->maxPoints;
    }

    public function getCourse(): ?Course
    {
        if ($this->lesson) {
            return $this->lesson->getCourse();
        }
        if ($this->session) {
            return $this->session->getCourse();
        }
        return null;
    }

    public function getAssignmentType(): string
    {
        if ($this->lesson) {
            return 'lesson';
        }
        if ($this->session) {
            return 'session';
        }
        return 'standalone';
    }
}
