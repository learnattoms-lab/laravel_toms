<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ORM\Table(name: 'quiz')]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Lesson::class, inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lesson $lesson = null;

    #[ORM\Column(type: 'json')]
    private ?array $questions = [];

    #[ORM\Column]
    private ?int $passMark = null;

    #[ORM\Column]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $instructions = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $timeLimit = 0; // in minutes, 0 = no limit

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $allowRetakes = true;

    #[ORM\Column(type: 'integer', options: ['default' => 3])]
    private int $maxAttempts = 3;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $shuffleQuestions = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $showCorrectAnswers = false;

    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: QuizAttempt::class, cascade: ['persist', 'remove'])]
    private Collection $attempts;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->questions = [];
        $this->attempts = new ArrayCollection();
        $this->passMark = 70; // Default 70% pass mark
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

    public function getQuestions(): ?array
    {
        return $this->questions;
    }

    public function setQuestions(?array $questions): static
    {
        $this->questions = $questions;
        return $this;
    }

    public function addQuestion(array $question): static
    {
        $this->questions[] = $question;
        return $this;
    }

    public function removeQuestion(int $index): static
    {
        if (isset($this->questions[$index])) {
            unset($this->questions[$index]);
            $this->questions = array_values($this->questions);
        }
        return $this;
    }

    public function getQuestionCount(): int
    {
        return count($this->questions);
    }

    public function getTotalPoints(): int
    {
        $total = 0;
        foreach ($this->questions as $question) {
            $total += $question['points'] ?? 1;
        }
        return $total;
    }

    public function getPassMark(): ?int
    {
        return $this->passMark;
    }

    public function setPassMark(int $passMark): static
    {
        $this->passMark = $passMark;
        return $this;
    }

    public function getPassMarkPercentage(): float
    {
        return ($this->passMark / $this->getTotalPoints()) * 100;
    }

    public function getFormattedPassMark(): string
    {
        return $this->passMark . '/' . $this->getTotalPoints() . ' (' . number_format($this->getPassMarkPercentage(), 1) . '%)';
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

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): static
    {
        $this->instructions = $instructions;
        return $this;
    }

    public function getTimeLimit(): int
    {
        return $this->timeLimit;
    }

    public function setTimeLimit(int $timeLimit): static
    {
        $this->timeLimit = $timeLimit;
        return $this;
    }

    public function hasTimeLimit(): bool
    {
        return $this->timeLimit > 0;
    }

    public function getFormattedTimeLimit(): string
    {
        if ($this->timeLimit === 0) {
            return 'No time limit';
        }
        
        $hours = intval($this->timeLimit / 60);
        $minutes = $this->timeLimit % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $minutes . ' minutes';
    }

    public function isAllowRetakes(): bool
    {
        return $this->allowRetakes;
    }

    public function setAllowRetakes(bool $allowRetakes): static
    {
        $this->allowRetakes = $allowRetakes;
        return $this;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function setMaxAttempts(int $maxAttempts): static
    {
        $this->maxAttempts = $maxAttempts;
        return $this;
    }

    public function isShuffleQuestions(): bool
    {
        return $this->shuffleQuestions;
    }

    public function setShuffleQuestions(bool $shuffleQuestions): static
    {
        $this->shuffleQuestions = $shuffleQuestions;
        return $this;
    }

    public function isShowCorrectAnswers(): bool
    {
        return $this->showCorrectAnswers;
    }

    public function setShowCorrectAnswers(bool $showCorrectAnswers): static
    {
        $this->showCorrectAnswers = $showCorrectAnswers;
        return $this;
    }

    /**
     * @return Collection<int, QuizAttempt>
     */
    public function getAttempts(): Collection
    {
        return $this->attempts;
    }

    public function addAttempt(QuizAttempt $attempt): static
    {
        if (!$this->attempts->contains($attempt)) {
            $this->attempts->add($attempt);
            $attempt->setQuiz($this);
        }

        return $this;
    }

    public function removeAttempt(QuizAttempt $attempt): static
    {
        if ($this->attempts->removeElement($attempt)) {
            if ($attempt->getQuiz() === $this) {
                $attempt->setQuiz(null);
            }
        }

        return $this;
    }

    public function getAttemptsByUser(User $user): Collection
    {
        return $this->attempts->filter(function(QuizAttempt $attempt) use ($user) {
            return $attempt->getStudent() === $user;
        });
    }

    public function getBestAttemptByUser(User $user): ?QuizAttempt
    {
        $userAttempts = $this->getAttemptsByUser($user);
        
        if ($userAttempts->isEmpty()) {
            return null;
        }
        
        $bestAttempt = null;
        $bestScore = -1;
        
        foreach ($userAttempts as $attempt) {
            if ($attempt->getScore() > $bestScore) {
                $bestScore = $attempt->getScore();
                $bestAttempt = $attempt;
            }
        }
        
        return $bestAttempt;
    }

    public function getUserAttemptCount(User $user): int
    {
        return $this->getAttemptsByUser($user)->count();
    }

    public function canUserTakeQuiz(User $user): bool
    {
        if (!$this->allowRetakes) {
            return $this->getAttemptsByUser($user)->isEmpty();
        }
        
        return $this->getUserAttemptCount($user) < $this->maxAttempts;
    }

    public function getShuffledQuestions(): array
    {
        if (!$this->shuffleQuestions) {
            return $this->questions;
        }
        
        $questions = $this->questions;
        shuffle($questions);
        return $questions;
    }

    public function getQuestionTypes(): array
    {
        $types = [];
        foreach ($this->questions as $question) {
            $type = $question['type'] ?? 'multiple_choice';
            if (!in_array($type, $types)) {
                $types[] = $type;
            }
        }
        return $types;
    }

    public function getDifficultyLevel(): string
    {
        $totalPoints = $this->getTotalPoints();
        $questionCount = $this->getQuestionCount();
        
        if ($questionCount === 0) {
            return 'Unknown';
        }
        
        $averagePoints = $totalPoints / $questionCount;
        
        if ($averagePoints >= 3) {
            return 'Hard';
        } elseif ($averagePoints >= 2) {
            return 'Medium';
        } else {
            return 'Easy';
        }
    }

    public function getEstimatedDuration(): int
    {
        if ($this->timeLimit > 0) {
            return $this->timeLimit;
        }
        
        // Estimate based on question count and types
        $estimatedMinutes = 0;
        foreach ($this->questions as $question) {
            $type = $question['type'] ?? 'multiple_choice';
            switch ($type) {
                case 'multiple_choice':
                    $estimatedMinutes += 1;
                    break;
                case 'true_false':
                    $estimatedMinutes += 0.5;
                    break;
                case 'short_answer':
                    $estimatedMinutes += 2;
                    break;
                case 'essay':
                    $estimatedMinutes += 5;
                    break;
                default:
                    $estimatedMinutes += 1;
            }
        }
        
        return (int) $estimatedMinutes;
    }

    public function getFormattedEstimatedDuration(): string
    {
        $minutes = $this->getEstimatedDuration();
        $hours = intval($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $remainingMinutes . 'm';
        }
        
        return $minutes . ' minutes';
    }
}
