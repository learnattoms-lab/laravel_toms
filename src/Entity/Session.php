<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\Table(name: 'session')]
#[ORM\HasLifecycleCallbacks]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\ManyToOne(targetEntity: Lesson::class)]
    private ?Lesson $lesson = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $tutor = null;

    #[ORM\Column]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column]
    private ?\DateTimeInterface $endAt = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $joinUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleEventId = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $materials = [];

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $recordingUrl = null;

    #[ORM\Column]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'session', targetEntity: Assignment::class, cascade: ['persist', 'remove'])]
    private Collection $assignments;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 50, options: ['default' => 'scheduled'])]
    private string $status = 'scheduled';

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $maxStudents = 0;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'session_students')]
    private Collection $enrolledStudents;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
        $this->enrolledStudents = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->materials = [];
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTime();
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

    public function getTutor(): ?User
    {
        return $this->tutor;
    }

    public function setTutor(?User $tutor): static
    {
        $this->tutor = $tutor;
        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeInterface $startAt): static
    {
        $this->startAt = $startAt;
        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt): static
    {
        $this->endAt = $endAt;
        return $this;
    }

    public function getDuration(): int
    {
        if (!$this->startAt || !$this->endAt) {
            return 0;
        }
        
        $interval = $this->startAt->diff($this->endAt);
        return ($interval->h * 60) + $interval->i;
    }

    public function getFormattedDuration(): string
    {
        $duration = $this->getDuration();
        $hours = intval($duration / 60);
        $minutes = $duration % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $minutes . 'm';
    }

    public function isUpcoming(): bool
    {
        return $this->startAt > new \DateTime();
    }

    public function isOngoing(): bool
    {
        $now = new \DateTime();
        return $this->startAt <= $now && $this->endAt >= $now;
    }

    public function isCompleted(): bool
    {
        return $this->endAt < new \DateTime();
    }

    public function getJoinUrl(): ?string
    {
        return $this->joinUrl;
    }

    public function setJoinUrl(?string $joinUrl): static
    {
        $this->joinUrl = $joinUrl;
        return $this;
    }

    public function getGoogleEventId(): ?string
    {
        return $this->googleEventId;
    }

    public function setGoogleEventId(?string $googleEventId): static
    {
        $this->googleEventId = $googleEventId;
        return $this;
    }

    public function getMaterials(): ?array
    {
        return $this->materials;
    }

    public function setMaterials(?array $materials): static
    {
        $this->materials = $materials;
        return $this;
    }

    public function addMaterial(string $material): static
    {
        if (!in_array($material, $this->materials)) {
            $this->materials[] = $material;
        }
        return $this;
    }

    public function removeMaterial(string $material): static
    {
        $key = array_search($material, $this->materials);
        if ($key !== false) {
            unset($this->materials[$key]);
            $this->materials = array_values($this->materials);
        }
        return $this;
    }

    public function getRecordingUrl(): ?string
    {
        return $this->recordingUrl;
    }

    public function setRecordingUrl(?string $recordingUrl): static
    {
        $this->recordingUrl = $recordingUrl;
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getMaxStudents(): int
    {
        return $this->maxStudents;
    }

    public function setMaxStudents(int $maxStudents): static
    {
        $this->maxStudents = $maxStudents;
        return $this;
    }

    public function hasAvailableSpots(): bool
    {
        return $this->maxStudents === 0 || $this->enrolledStudents->count() < $this->maxStudents;
    }

    public function getAvailableSpots(): int
    {
        if ($this->maxStudents === 0) {
            return -1; // Unlimited
        }
        return max(0, $this->maxStudents - $this->enrolledStudents->count());
    }

    /**
     * @return Collection<int, Assignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(Assignment $assignment): static
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setSession($this);
        }

        return $this;
    }

    public function removeAssignment(Assignment $assignment): static
    {
        if ($this->assignments->removeElement($assignment)) {
            if ($assignment->getSession() === $this) {
                $assignment->setSession(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getEnrolledStudents(): Collection
    {
        return $this->enrolledStudents;
    }

    public function addEnrolledStudent(User $student): static
    {
        if (!$this->enrolledStudents->contains($student)) {
            $this->enrolledStudents->add($student);
        }

        return $this;
    }

    public function removeEnrolledStudent(User $student): static
    {
        $this->enrolledStudents->removeElement($student);

        return $this;
    }

    public function isStudentEnrolled(User $student): bool
    {
        return $this->enrolledStudents->contains($student);
    }

    public function getSessionTitle(): string
    {
        if ($this->lesson) {
            return $this->lesson->getTitle();
        }
        
        return $this->course->getTitle() . ' Session';
    }

    public function getFormattedStartTime(): string
    {
        return $this->startAt ? $this->startAt->format('M j, Y g:i A') : '';
    }

    public function getFormattedEndTime(): string
    {
        return $this->endAt ? $this->endAt->format('g:i A') : '';
    }

    public function getFormattedDateRange(): string
    {
        if (!$this->startAt || !$this->endAt) {
            return '';
        }
        
        if ($this->startAt->format('Y-m-d') === $this->endAt->format('Y-m-d')) {
            // Same day
            return $this->startAt->format('M j, Y') . ' ' . 
                   $this->startAt->format('g:i A') . ' - ' . 
                   $this->endAt->format('g:i A');
        }
        
        // Different days
        return $this->startAt->format('M j, Y g:i A') . ' - ' . 
               $this->endAt->format('M j, Y g:i A');
    }
}
