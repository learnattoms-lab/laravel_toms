<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ORM\Table(name: 'lesson')]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?int $orderIndex = null;

    #[ORM\Column(type: 'text')]
    private ?string $contentHtml = null;

    #[ORM\Column]
    private ?int $durationMin = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $resources = [];

    #[ORM\Column]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'lesson', targetEntity: Assignment::class, cascade: ['persist', 'remove'])]
    private Collection $assignments;

    #[ORM\OneToMany(mappedBy: 'lesson', targetEntity: Quiz::class, cascade: ['persist', 'remove'])]
    private Collection $quizzes;

    #[ORM\OneToMany(mappedBy: 'lesson', targetEntity: Note::class, cascade: ['persist', 'remove'])]
    private Collection $notes;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $learningObjectives = [];

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isRequired = true;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
        $this->quizzes = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->resources = [];
        $this->learningObjectives = [];
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    public function setOrderIndex(int $orderIndex): static
    {
        $this->orderIndex = $orderIndex;
        return $this;
    }

    public function getContentHtml(): ?string
    {
        return $this->contentHtml;
    }

    public function setContentHtml(string $contentHtml): static
    {
        $this->contentHtml = $contentHtml;
        return $this;
    }

    public function getDurationMin(): ?int
    {
        return $this->durationMin;
    }

    public function setDurationMin(int $durationMin): static
    {
        $this->durationMin = $durationMin;
        return $this;
    }

    public function getFormattedDuration(): string
    {
        $hours = intval($this->durationMin / 60);
        $minutes = $this->durationMin % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $minutes . 'm';
    }

    public function getResources(): ?array
    {
        return $this->resources;
    }

    public function setResources(?array $resources): static
    {
        $this->resources = $resources;
        return $this;
    }

    public function addResource(string $resource): static
    {
        if (!in_array($resource, $this->resources)) {
            $this->resources[] = $resource;
        }
        return $this;
    }

    public function removeResource(string $resource): static
    {
        $key = array_search($resource, $this->resources);
        if ($key !== false) {
            unset($this->resources[$key]);
            $this->resources = array_values($this->resources);
        }
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

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;
        return $this;
    }

    public function getLearningObjectives(): ?array
    {
        return $this->learningObjectives;
    }

    public function setLearningObjectives(?array $learningObjectives): static
    {
        $this->learningObjectives = $learningObjectives;
        return $this;
    }

    public function addLearningObjective(string $objective): static
    {
        if (!in_array($objective, $this->learningObjectives)) {
            $this->learningObjectives[] = $objective;
        }
        return $this;
    }

    public function removeLearningObjective(string $objective): static
    {
        $key = array_search($objective, $this->learningObjectives);
        if ($key !== false) {
            unset($this->learningObjectives[$key]);
            $this->learningObjectives = array_values($this->learningObjectives);
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
            $assignment->setLesson($this);
        }

        return $this;
    }

    public function removeAssignment(Assignment $assignment): static
    {
        if ($this->assignments->removeElement($assignment)) {
            if ($assignment->getLesson() === $this) {
                $assignment->setLesson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Quiz>
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quiz $quiz): static
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->setLesson($this);
        }

        return $this;
    }

    public function removeQuiz(Quiz $quiz): static
    {
        if ($this->quizzes->removeElement($quiz)) {
            if ($quiz->getLesson() === $this) {
                $quiz->setLesson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): static
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setLesson($this);
        }

        return $this;
    }

    public function removeNote(Note $note): static
    {
        if ($this->notes->removeElement($note)) {
            if ($note->getLesson() === $this) {
                $note->setLesson(null);
            }
        }

        return $this;
    }

    public function getNoteByUser(User $user): ?Note
    {
        return $this->notes->filter(function(Note $note) use ($user) {
            return $note->getUser() === $user;
        })->first();
    }

    public function hasQuiz(): bool
    {
        return !$this->quizzes->isEmpty();
    }

    public function hasAssignment(): bool
    {
        return !$this->assignments->isEmpty();
    }

    public function isCompletedByUser(User $user): bool
    {
        // Check if user has completed all required elements
        if ($this->isRequired) {
            // For now, just check if there are no incomplete assignments
            // This could be enhanced with quiz completion tracking
            $incompleteAssignments = $this->assignments->filter(function(Assignment $assignment) use ($user) {
                // Check if user has submitted all assignments
                return $assignment->getSubmissions()->filter(function($submission) use ($user) {
                    return $submission->getStudent() === $user;
                })->isEmpty();
            });
            
            return $incompleteAssignments->isEmpty();
        }
        
        return true;
    }
}
