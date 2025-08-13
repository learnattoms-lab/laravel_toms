<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $instrument = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $skillLevel = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $timezone = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $preferences = [];

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastLoginAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'boolean')]
    private bool $emailVerified = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $appleId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebookId = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $experiencePoints = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $level = 1;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $achievements = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $badges = [];

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $rating = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $totalLessons = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $completedLessons = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $practiceHours = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastPracticeAt = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $learningGoals = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $progressData = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isTeacher = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $teacherBio = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $teacherSpecialties = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $teacherCertifications = [];

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $hourlyRate = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $availability = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $studentReviews = [];

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $totalStudents = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $activeStudents = 0;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->roles = ['ROLE_USER'];
        $this->preferences = [];
        $this->achievements = [];
        $this->badges = [];
        $this->learningGoals = [];
        $this->progressData = [];
        $this->teacherSpecialties = [];
        $this->teacherCertifications = [];
        $this->availability = [];
        $this->studentReviews = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFullName(): string
    {
        if ($this->firstName && $this->lastName) {
            return $this->firstName . ' ' . $this->lastName;
        }
        return $this->firstName ?? $this->lastName ?? $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function getAge(): ?int
    {
        if (!$this->dateOfBirth) {
            return null;
        }
        return $this->dateOfBirth->diff(new \DateTime())->y;
    }

    public function getInstrument(): ?string
    {
        return $this->instrument;
    }

    public function setInstrument(?string $instrument): static
    {
        $this->instrument = $instrument;
        return $this;
    }

    public function getSkillLevel(): ?string
    {
        return $this->skillLevel;
    }

    public function setSkillLevel(?string $skillLevel): static
    {
        $this->skillLevel = $skillLevel;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;
        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): static
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getPreferences(): ?array
    {
        return $this->preferences;
    }

    public function setPreferences(?array $preferences): static
    {
        $this->preferences = $preferences;
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

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool $emailVerified): static
    {
        $this->emailVerified = $emailVerified;
        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): static
    {
        $this->googleId = $googleId;
        return $this;
    }

    public function getAppleId(): ?string
    {
        return $this->appleId;
    }

    public function setAppleId(?string $appleId): static
    {
        $this->appleId = $appleId;
        return $this;
    }

    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    public function setFacebookId(?string $facebookId): static
    {
        $this->facebookId = $facebookId;
        return $this;
    }

    public function getExperiencePoints(): int
    {
        return $this->experiencePoints;
    }

    public function setExperiencePoints(int $experiencePoints): static
    {
        $this->experiencePoints = $experiencePoints;
        return $this;
    }

    public function addExperiencePoints(int $points): static
    {
        $this->experiencePoints += $points;
        $this->updateLevel();
        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;
        return $this;
    }

    private function updateLevel(): void
    {
        // Simple level calculation: every 100 XP = 1 level
        $this->level = max(1, intval($this->experiencePoints / 100) + 1);
    }

    public function getAchievements(): ?array
    {
        return $this->achievements;
    }

    public function setAchievements(?array $achievements): static
    {
        $this->achievements = $achievements;
        return $this;
    }

    public function addAchievement(string $achievement): static
    {
        if (!in_array($achievement, $this->achievements)) {
            $this->achievements[] = $achievement;
        }
        return $this;
    }

    public function getBadges(): ?array
    {
        return $this->badges;
    }

    public function setBadges(?array $badges): static
    {
        $this->badges = $badges;
        return $this;
    }

    public function addBadge(string $badge): static
    {
        if (!in_array($badge, $this->badges)) {
            $this->badges[] = $badge;
        }
        return $this;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(?string $rating): static
    {
        $this->rating = $rating;
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

    public function getCompletedLessons(): int
    {
        return $this->completedLessons;
    }

    public function setCompletedLessons(int $completedLessons): static
    {
        $this->completedLessons = $completedLessons;
        return $this;
    }

    public function getPracticeHours(): int
    {
        return $this->practiceHours;
    }

    public function setPracticeHours(int $practiceHours): static
    {
        $this->practiceHours = $practiceHours;
        return $this;
    }

    public function addPracticeHours(int $hours): static
    {
        $this->practiceHours += $hours;
        return $this;
    }

    public function getLastPracticeAt(): ?\DateTimeInterface
    {
        return $this->lastPracticeAt;
    }

    public function setLastPracticeAt(?\DateTimeInterface $lastPracticeAt): static
    {
        $this->lastPracticeAt = $lastPracticeAt;
        return $this;
    }

    public function getLearningGoals(): ?array
    {
        return $this->learningGoals;
    }

    public function setLearningGoals(?array $learningGoals): static
    {
        $this->learningGoals = $learningGoals;
        return $this;
    }

    public function addLearningGoal(string $goal): static
    {
        if (!in_array($goal, $this->learningGoals)) {
            $this->learningGoals[] = $goal;
        }
        return $this;
    }

    public function getProgressData(): ?array
    {
        return $this->progressData;
    }

    public function setProgressData(?array $progressData): static
    {
        $this->progressData = $progressData;
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

    public function isTeacher(): bool
    {
        return $this->isTeacher;
    }

    public function setIsTeacher(bool $isTeacher): static
    {
        $this->isTeacher = $isTeacher;
        if ($isTeacher && !in_array('ROLE_TEACHER', $this->roles)) {
            $this->roles[] = 'ROLE_TEACHER';
        }
        return $this;
    }

    public function getTeacherBio(): ?string
    {
        return $this->teacherBio;
    }

    public function setTeacherBio(?string $teacherBio): static
    {
        $this->teacherBio = $teacherBio;
        return $this;
    }

    public function getTeacherSpecialties(): ?array
    {
        return $this->teacherSpecialties;
    }

    public function setTeacherSpecialties(?array $teacherSpecialties): static
    {
        $this->teacherSpecialties = $teacherSpecialties;
        return $this;
    }

    public function getTeacherCertifications(): ?array
    {
        return $this->teacherCertifications;
    }

    public function setTeacherCertifications(?array $teacherCertifications): static
    {
        $this->teacherCertifications = $teacherCertifications;
        return $this;
    }

    public function getHourlyRate(): ?string
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate(?string $hourlyRate): static
    {
        $this->hourlyRate = $hourlyRate;
        return $this;
    }

    public function getAvailability(): ?array
    {
        return $this->availability;
    }

    public function setAvailability(?array $availability): static
    {
        $this->availability = $availability;
        return $this;
    }

    public function getStudentReviews(): ?array
    {
        return $this->studentReviews;
    }

    public function setStudentReviews(?array $studentReviews): static
    {
        $this->studentReviews = $studentReviews;
        return $this;
    }

    public function getTotalStudents(): int
    {
        return $this->totalStudents;
    }

    public function setTotalStudents(int $totalStudents): static
    {
        $this->totalStudents = $totalStudents;
        return $this;
    }

    public function getActiveStudents(): int
    {
        return $this->activeStudents;
    }

    public function setActiveStudents(int $activeStudents): static
    {
        $this->activeStudents = $activeStudents;
        return $this;
    }

    // Helper methods
    public function getProfileCompletionPercentage(): int
    {
        $fields = [
            'firstName', 'lastName', 'phone', 'dateOfBirth', 'instrument', 
            'skillLevel', 'bio', 'city', 'country', 'timezone'
        ];
        
        $completed = 0;
        foreach ($fields as $field) {
            $getter = 'get' . ucfirst($field);
            if (method_exists($this, $getter) && $this->$getter()) {
                $completed++;
            }
        }
        
        return round(($completed / count($fields)) * 100);
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('ROLE_ADMIN');
    }

    public function isStudent(): bool
    {
        return !$this->isTeacher() && !$this->isAdmin();
    }

    public function getDisplayName(): string
    {
        if ($this->firstName) {
            return $this->firstName;
        }
        return $this->email;
    }

    public function getAvatarInitials(): string
    {
        if ($this->firstName && $this->lastName) {
            return strtoupper(substr($this->firstName, 0, 1) . substr($this->lastName, 0, 1));
        }
        if ($this->firstName) {
            return strtoupper(substr($this->firstName, 0, 1));
        }
        if ($this->lastName) {
            return strtoupper(substr($this->lastName, 0, 1));
        }
        return strtoupper(substr($this->email, 0, 2));
    }
}
