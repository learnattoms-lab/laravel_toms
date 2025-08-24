<?php

namespace App\Entity;

use App\Repository\QuizAttemptRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizAttemptRepository::class)]
#[ORM\Table(name: 'quiz_attempt')]
class QuizAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: 'attempts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $student = null;

    #[ORM\Column]
    private ?int $score = null;

    #[ORM\Column]
    private ?bool $passed = null;

    #[ORM\Column]
    private ?\DateTimeInterface $submittedAt = null;

    #[ORM\Column(type: 'json')]
    private ?array $responses = [];

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timeSpent = null; // in seconds

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $questionOrder = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->submittedAt = new \DateTime();
        $this->startedAt = new \DateTime();
        $this->responses = [];
        $this->questionOrder = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;
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

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;
        return $this;
    }

    public function getMaxScore(): int
    {
        return $this->quiz ? $this->quiz->getTotalPoints() : 0;
    }

    public function getScorePercentage(): float
    {
        if ($this->score === null || $this->getMaxScore() === 0) {
            return 0.0;
        }
        
        return ($this->score / $this->getMaxScore()) * 100;
    }

    public function getFormattedScore(): string
    {
        if ($this->score === null) {
            return 'Not completed';
        }
        
        return $this->score . '/' . $this->getMaxScore() . ' (' . number_format($this->getScorePercentage(), 1) . '%)';
    }

    public function getGradeLetter(): ?string
    {
        $percentage = $this->getScorePercentage();
        
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        if ($percentage > 0) return 'F';
        
        return null;
    }

    public function isPassed(): ?bool
    {
        return $this->passed;
    }

    public function setPassed(bool $passed): static
    {
        $this->passed = $passed;
        return $this;
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

    public function getResponses(): ?array
    {
        return $this->responses;
    }

    public function setResponses(?array $responses): static
    {
        $this->responses = $responses;
        return $this;
    }

    public function addResponse(int $questionIndex, $response): static
    {
        $this->responses[$questionIndex] = $response;
        return $this;
    }

    public function getResponse(int $questionIndex)
    {
        return $this->responses[$questionIndex] ?? null;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): static
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

    public function getTimeSpent(): ?int
    {
        return $this->timeSpent;
    }

    public function setTimeSpent(?int $timeSpent): static
    {
        $this->timeSpent = $timeSpent;
        return $this;
    }

    public function getFormattedTimeSpent(): string
    {
        if ($this->timeSpent === null) {
            return 'Unknown';
        }
        
        $hours = intval($this->timeSpent / 3600);
        $minutes = intval(($this->timeSpent % 3600) / 60);
        $seconds = $this->timeSpent % 60;
        
        if ($hours > 0) {
            return sprintf('%dh %dm %ds', $hours, $minutes, $seconds);
        } elseif ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $seconds);
        } else {
            return $seconds . 's';
        }
    }

    public function getQuestionOrder(): ?array
    {
        return $this->questionOrder;
    }

    public function setQuestionOrder(?array $questionOrder): static
    {
        $this->questionOrder = $questionOrder;
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

    public function isCompleted(): bool
    {
        return $this->completedAt !== null;
    }

    public function isInProgress(): bool
    {
        return $this->startedAt !== null && $this->completedAt === null;
    }

    public function getDuration(): ?\DateInterval
    {
        if (!$this->startedAt || !$this->completedAt) {
            return null;
        }
        
        return $this->startedAt->diff($this->completedAt);
    }

    public function getFormattedDuration(): string
    {
        $duration = $this->getDuration();
        if (!$duration) {
            return 'In progress';
        }
        
        if ($duration->h > 0) {
            return $duration->h . 'h ' . $duration->i . 'm';
        } elseif ($duration->i > 0) {
            return $duration->i . 'm ' . $duration->s . 's';
        } else {
            return $duration->s . 's';
        }
    }

    public function getCorrectAnswerCount(): int
    {
        if (!$this->quiz || !$this->responses) {
            return 0;
        }
        
        $correct = 0;
        $questions = $this->quiz->getQuestions();
        
        foreach ($this->responses as $questionIndex => $response) {
            if (isset($questions[$questionIndex])) {
                $question = $questions[$questionIndex];
                if ($this->isResponseCorrect($question, $response)) {
                    $correct++;
                }
            }
        }
        
        return $correct;
    }

    public function getIncorrectAnswerCount(): int
    {
        return count($this->responses) - $this->getCorrectAnswerCount();
    }

    public function getAccuracy(): float
    {
        if (empty($this->responses)) {
            return 0.0;
        }
        
        return ($this->getCorrectAnswerCount() / count($this->responses)) * 100;
    }

    public function getFormattedAccuracy(): string
    {
        return number_format($this->getAccuracy(), 1) . '%';
    }

    public function getAttemptNumber(): int
    {
        if (!$this->quiz || !$this->student) {
            return 1;
        }
        
        $attempts = $this->quiz->getAttemptsByUser($this->student);
        $attemptNumber = 1;
        
        foreach ($attempts as $attempt) {
            if ($attempt->getId() === $this->id) {
                break;
            }
            $attemptNumber++;
        }
        
        return $attemptNumber;
    }

    public function isFirstAttempt(): bool
    {
        return $this->getAttemptNumber() === 1;
    }

    public function isLastAttempt(): bool
    {
        if (!$this->quiz) {
            return true;
        }
        
        return $this->getAttemptNumber() >= $this->quiz->getMaxAttempts();
    }

    public function canRetake(): bool
    {
        if (!$this->quiz) {
            return false;
        }
        
        return $this->quiz->isAllowRetakes() && !$this->isLastAttempt();
    }

    public function getPerformanceLevel(): string
    {
        $percentage = $this->getScorePercentage();
        
        if ($percentage >= 90) return 'Excellent';
        if ($percentage >= 80) return 'Good';
        if ($percentage >= 70) return 'Satisfactory';
        if ($percentage >= 60) return 'Needs Improvement';
        if ($percentage > 0) return 'Poor';
        
        return 'Not Attempted';
    }

    public function getPerformanceColor(): string
    {
        $percentage = $this->getScorePercentage();
        
        if ($percentage >= 90) return 'success';
        if ($percentage >= 80) return 'info';
        if ($percentage >= 70) return 'warning';
        if ($percentage >= 60) return 'warning';
        if ($percentage > 0) return 'danger';
        
        return 'secondary';
    }

    private function isResponseCorrect(array $question, $response): bool
    {
        $type = $question['type'] ?? 'multiple_choice';
        $correctAnswer = $question['correct_answer'] ?? null;
        
        switch ($type) {
            case 'multiple_choice':
            case 'true_false':
                return $response === $correctAnswer;
            
            case 'multiple_select':
                if (!is_array($response) || !is_array($correctAnswer)) {
                    return false;
                }
                sort($response);
                sort($correctAnswer);
                return $response === $correctAnswer;
            
            case 'short_answer':
                $response = strtolower(trim($response));
                $correctAnswer = strtolower(trim($correctAnswer));
                return $response === $correctAnswer;
            
            default:
                return false;
        }
    }
}
