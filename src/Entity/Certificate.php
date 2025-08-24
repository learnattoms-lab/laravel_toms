<?php

namespace App\Entity;

use App\Repository\CertificateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertificateRepository::class)]
#[ORM\Table(name: 'certificate')]
class Certificate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column]
    private ?\DateTimeInterface $issuedAt = null;

    #[ORM\Column(length: 500)]
    private ?string $certificateUrl = null;

    #[ORM\Column(length: 100)]
    private ?string $serial = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private ?string $finalScore = null;

    #[ORM\Column(length: 20)]
    private ?string $grade = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metadata = [];

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isValid = true;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $revokedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $revocationReason = null;

    public function __construct()
    {
        $this->issuedAt = new \DateTime();
        $this->serial = $this->generateSerial();
        $this->metadata = [];
        $this->isValid = true;
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

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;
        return $this;
    }

    public function getIssuedAt(): ?\DateTimeInterface
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(\DateTimeInterface $issuedAt): static
    {
        $this->issuedAt = $issuedAt;
        return $this;
    }

    public function getFormattedIssuedAt(): string
    {
        return $this->issuedAt ? $this->issuedAt->format('F j, Y') : '';
    }

    public function getCertificateUrl(): ?string
    {
        return $this->certificateUrl;
    }

    public function setCertificateUrl(string $certificateUrl): static
    {
        $this->certificateUrl = $certificateUrl;
        return $this;
    }

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(string $serial): static
    {
        $this->serial = $serial;
        return $this;
    }

    public function getFinalScore(): ?string
    {
        return $this->finalScore;
    }

    public function setFinalScore(string $finalScore): static
    {
        $this->finalScore = $finalScore;
        return $this;
    }

    public function getFinalScorePercentage(): float
    {
        return (float) $this->finalScore;
    }

    public function getFormattedFinalScore(): string
    {
        return number_format($this->getFinalScorePercentage(), 1) . '%';
    }

    public function getGrade(): ?string
    {
        return $this->grade;
    }

    public function setGrade(string $grade): static
    {
        $this->grade = $grade;
        return $this;
    }

    public function getGradeColor(): string
    {
        $grade = $this->grade;
        
        $colors = [
            'A' => 'success',
            'B' => 'info',
            'C' => 'warning',
            'D' => 'warning',
            'F' => 'danger'
        ];
        
        return $colors[$grade] ?? 'secondary';
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

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function addMetadata(string $key, $value): static
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    public function getMetadataValue(string $key)
    {
        return $this->metadata[$key] ?? null;
    }

    public function isIsValid(): bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): static
    {
        $this->isValid = $isValid;
        return $this;
    }

    public function getRevokedAt(): ?\DateTimeInterface
    {
        return $this->revokedAt;
    }

    public function setRevokedAt(?\DateTimeInterface $revokedAt): static
    {
        $this->revokedAt = $revokedAt;
        return $this;
    }

    public function getRevocationReason(): ?string
    {
        return $this->revocationReason;
    }

    public function setRevocationReason(?string $revocationReason): static
    {
        $this->revocationReason = $revocationReason;
        return $this;
    }

    public function isRevoked(): bool
    {
        return $this->revokedAt !== null;
    }

    public function getStatus(): string
    {
        if ($this->isRevoked()) {
            return 'revoked';
        }
        
        if (!$this->isValid) {
            return 'invalid';
        }
        
        return 'valid';
    }

    public function getStatusLabel(): string
    {
        $labels = [
            'valid' => 'Valid',
            'invalid' => 'Invalid',
            'revoked' => 'Revoked'
        ];
        
        return $labels[$this->getStatus()] ?? ucfirst($this->getStatus());
    }

    public function getStatusBadgeClass(): string
    {
        $classes = [
            'valid' => 'badge bg-success',
            'invalid' => 'badge bg-danger',
            'revoked' => 'badge bg-secondary'
        ];
        
        return $classes[$this->getStatus()] ?? 'badge bg-secondary';
    }

    public function getCertificateNumber(): string
    {
        return 'CERT-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getFullSerial(): string
    {
        $courseCode = $this->course ? strtoupper(substr($this->course->getTitle(), 0, 3)) : 'GEN';
        $year = $this->issuedAt ? $this->issuedAt->format('Y') : date('Y');
        $userId = str_pad($this->user->getId(), 4, '0', STR_PAD_LEFT);
        
        return sprintf('%s-%s-%s-%s', $courseCode, $year, $userId, $this->serial);
    }

    public function getVerificationUrl(): string
    {
        // This would be a public URL to verify the certificate
        return '/verify/certificate/' . $this->getFullSerial();
    }

    public function getExpiryDate(): ?\DateTimeInterface
    {
        // Certificates typically don't expire, but this could be configurable
        if (!$this->issuedAt) {
            return null;
        }
        
        // Example: 5 years from issue date
        $expiryDate = clone $this->issuedAt;
        $expiryDate->add(new \DateInterval('P5Y'));
        
        return $expiryDate;
    }

    public function isExpired(): bool
    {
        $expiryDate = $this->getExpiryDate();
        if (!$expiryDate) {
            return false;
        }
        
        return $expiryDate < new \DateTime();
    }

    public function getDaysUntilExpiry(): int
    {
        $expiryDate = $this->getExpiryDate();
        if (!$expiryDate) {
            return -1; // No expiry
        }
        
        $now = new \DateTime();
        $interval = $now->diff($expiryDate);
        
        if ($expiryDate < $now) {
            return -$interval->days; // Already expired
        }
        
        return $interval->days;
    }

    public function getFormattedExpiryDate(): string
    {
        $expiryDate = $this->getExpiryDate();
        if (!$expiryDate) {
            return 'Never expires';
        }
        
        return $expiryDate->format('F j, Y');
    }

    public function getCertificateTitle(): string
    {
        if (!$this->course) {
            return 'Certificate of Completion';
        }
        
        return 'Certificate of Completion in ' . $this->course->getTitle();
    }

    public function getInstructorName(): string
    {
        if (!$this->course || !$this->course->getTeacher()) {
            return 'N/A';
        }
        
        return $this->course->getTeacher()->getFullName();
    }

    public function getStudentName(): string
    {
        if (!$this->user) {
            return 'N/A';
        }
        
        return $this->user->getFullName();
    }

    public function getCourseDuration(): string
    {
        if (!$this->course) {
            return 'N/A';
        }
        
        return $this->course->getFormattedDuration();
    }

    public function getCompletionDate(): string
    {
        return $this->getFormattedIssuedAt();
    }

    public function canBeRevokedBy(User $user): bool
    {
        // Only course teachers or admins can revoke certificates
        if (!$this->course) {
            return false;
        }
        
        if ($user->isAdmin()) {
            return true;
        }
        
        return $this->course->getTeacher() === $user;
    }

    public function revoke(string $reason, User $revokedBy): static
    {
        $this->isValid = false;
        $this->revokedAt = new \DateTime();
        $this->revocationReason = $reason;
        
        // Add metadata about who revoked it
        $this->addMetadata('revoked_by', $revokedBy->getId());
        $this->addMetadata('revoked_by_name', $revokedBy->getFullName());
        
        return $this;
    }

    private function generateSerial(): string
    {
        // Generate a unique 8-character serial number
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $serial = '';
        
        for ($i = 0; $i < 8; $i++) {
            $serial .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $serial;
    }

    public function getCertificateData(): array
    {
        return [
            'id' => $this->getId(),
            'serial' => $this->getFullSerial(),
            'student_name' => $this->getStudentName(),
            'course_title' => $this->course ? $this->course->getTitle() : 'N/A',
            'instructor_name' => $this->getInstructorName(),
            'issue_date' => $this->getFormattedIssuedAt(),
            'final_score' => $this->getFormattedFinalScore(),
            'grade' => $this->getGrade(),
            'status' => $this->getStatusLabel(),
            'verification_url' => $this->getVerificationUrl()
        ];
    }
}
