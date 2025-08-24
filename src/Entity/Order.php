<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
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
    private ?int $amountCents = null;

    #[ORM\Column(length: 3)]
    private ?string $currency = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePaymentIntentId = null;

    #[ORM\Column]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $failureReason = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->status = 'pending';
        $this->currency = 'usd';
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

    public function getAmountCents(): ?int
    {
        return $this->amountCents;
    }

    public function setAmountCents(int $amountCents): static
    {
        $this->amountCents = $amountCents;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amountCents / 100;
    }

    public function getFormattedAmount(): string
    {
        $currencySymbols = [
            'usd' => '$',
            'eur' => '€',
            'gbp' => '£',
            'cad' => 'C$',
            'aud' => 'A$'
        ];

        $symbol = $currencySymbols[$this->currency] ?? $this->currency;
        return $symbol . number_format($this->getAmount(), 2);
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = strtolower($currency);
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
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): static
    {
        $this->stripeSessionId = $stripeSessionId;
        return $this;
    }

    public function getStripePaymentIntentId(): ?string
    {
        return $this->stripePaymentIntentId;
    }

    public function setStripePaymentIntentId(?string $stripePaymentIntentId): static
    {
        $this->stripePaymentIntentId = $stripePaymentIntentId;
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

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
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

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function setFailureReason(?string $failureReason): static
    {
        $this->failureReason = $failureReason;
        return $this;
    }

    public function markAsPaid(string $paymentIntentId): static
    {
        $this->setStatus('paid');
        $this->setStripePaymentIntentId($paymentIntentId);
        $this->setFailureReason(null);
        return $this;
    }

    public function markAsFailed(string $reason): static
    {
        $this->setStatus('failed');
        $this->setFailureReason($reason);
        return $this;
    }

    public function markAsRefunded(): static
    {
        $this->setStatus('refunded');
        return $this;
    }

    public function getStatusLabel(): string
    {
        $labels = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded'
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClass(): string
    {
        $classes = [
            'pending' => 'badge bg-warning',
            'paid' => 'badge bg-success',
            'failed' => 'badge bg-danger',
            'refunded' => 'badge bg-secondary'
        ];

        return $classes[$this->status] ?? 'badge bg-secondary';
    }

    public function getOrderNumber(): string
    {
        return 'ORD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
}
