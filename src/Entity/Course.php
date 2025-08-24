<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
#[ORM\Table(name: 'course')]
#[ORM\HasLifecycleCallbacks]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(length: 100)]
    private ?string $instrument = null;

    #[ORM\Column(length: 50)]
    private ?string $level = null;

    #[ORM\Column]
    private ?int $priceCents = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $teacher = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\Column]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Lesson::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['orderIndex' => 'ASC'])]
    private Collection $lessons;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Session::class, cascade: ['persist', 'remove'])]
    private Collection $sessions;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Enrollment::class, cascade: ['persist', 'remove'])]
    private Collection $enrollments;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $thumbnail = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $tags = [];

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $totalLessons = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $totalDuration = 0; // in minutes

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->enrollments = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->tags = [];
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getInstrument(): ?string
    {
        return $this->instrument;
    }

    public function setInstrument(string $instrument): static
    {
        $this->instrument = $instrument;
        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;
        return $this;
    }

    public function getPriceCents(): ?int
    {
        return $this->priceCents;
    }

    public function setPriceCents(int $priceCents): static
    {
        $this->priceCents = $priceCents;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->priceCents / 100;
    }

    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->getPrice(), 2);
    }

    public function getTeacher(): ?User
    {
        return $this->teacher;
    }

    public function setTeacher(?User $teacher): static
    {
        $this->teacher = $teacher;
        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function isPublished(): bool
    {
        return $this->publishedAt !== null;
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



    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;
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

    public function getTotalLessons(): int
    {
        return $this->totalLessons;
    }

    public function setTotalLessons(int $totalLessons): static
    {
        $this->totalLessons = $totalLessons;
        return $this;
    }

    public function getTotalDuration(): int
    {
        return $this->totalDuration;
    }

    public function setTotalDuration(int $totalDuration): static
    {
        $this->totalDuration = $totalDuration;
        return $this;
    }

    public function getFormattedDuration(): string
    {
        $hours = intval($this->totalDuration / 60);
        $minutes = $this->totalDuration % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $minutes . 'm';
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->setCourse($this);
            $this->updateCourseStats();
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            if ($lesson->getCourse() === $this) {
                $lesson->setCourse(null);
            }
            $this->updateCourseStats();
        }

        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setCourse($this);
        }

        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->sessions->removeElement($session)) {
            if ($session->getCourse() === $this) {
                $session->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Enrollment>
     */
    public function getEnrollments(): Collection
    {
        return $this->enrollments;
    }

    public function addEnrollment(Enrollment $enrollment): static
    {
        if (!$this->enrollments->contains($enrollment)) {
            $this->enrollments->add($enrollment);
            $enrollment->setCourse($this);
        }

        return $this;
    }

    public function removeEnrollment(Enrollment $enrollment): static
    {
        if ($this->enrollments->removeElement($enrollment)) {
            if ($enrollment->getCourse() === $this) {
                $enrollment->setCourse(null);
            }
        }

        return $this;
    }

    public function getActiveEnrollments(): Collection
    {
        return $this->enrollments->filter(function(Enrollment $enrollment) {
            return $enrollment->getStatus() === 'active';
        });
    }

    public function getEnrollmentCount(): int
    {
        return $this->getActiveEnrollments()->count();
    }

    private function updateCourseStats(): void
    {
        $this->totalLessons = $this->lessons->count();
        $this->totalDuration = $this->lessons->reduce(function($carry, Lesson $lesson) {
            return $carry + $lesson->getDurationMin();
        }, 0);
    }

    public function getNextLessonForUser(User $user): ?Lesson
    {
        $enrollment = $this->enrollments->filter(function(Enrollment $e) use ($user) {
            return $e->getStudent() === $user && $e->getStatus() === 'active';
        })->first();

        if (!$enrollment) {
            return null;
        }

        $completedLessons = $enrollment->getProgressPct() / 100 * $this->totalLessons;
        $nextLessonIndex = (int) floor($completedLessons);

        return $this->lessons->get($nextLessonIndex);
    }

    public function getProgressForUser(User $user): float
    {
        $enrollment = $this->enrollments->filter(function(Enrollment $e) use ($user) {
            return $e->getStudent() === $user && $e->getStatus() === 'active';
        })->first();

        return $enrollment ? $enrollment->getProgressPct() : 0.0;
    }
}
