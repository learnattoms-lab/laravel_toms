<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table with basic fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NULL,
            first_name VARCHAR(100) NULL,
            last_name VARCHAR(100) NULL,
            phone VARCHAR(20) NULL,
            date_of_birth DATE NULL,
            instrument VARCHAR(100) NULL,
            skill_level VARCHAR(50) NULL,
            bio LONGTEXT NULL,
            profile_picture VARCHAR(255) NULL,
            city VARCHAR(100) NULL,
            country VARCHAR(100) NULL,
            timezone VARCHAR(100) NULL,
            preferences JSON NULL,
            created_at DATETIME NOT NULL,
            last_login_at DATETIME NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            email_verified TINYINT(1) NOT NULL DEFAULT 0,
            google_id VARCHAR(255) NULL,
            apple_id VARCHAR(255) NULL,
            facebook_id VARCHAR(255) NULL,
            experience_points INT NOT NULL DEFAULT 0,
            level INT NOT NULL DEFAULT 1,
            achievements JSON NULL,
            badges JSON NULL,
            rating DECIMAL(5,2) NULL,
            total_lessons INT NOT NULL DEFAULT 0,
            completed_lessons INT NOT NULL DEFAULT 0,
            practice_hours INT NOT NULL DEFAULT 0,
            last_practice_at DATETIME NULL,
            learning_goals JSON NULL,
            progress_data JSON NULL,
            notes LONGTEXT NULL,
            is_teacher TINYINT(1) NOT NULL DEFAULT 0,
            teacher_bio LONGTEXT NULL,
            teacher_specialties JSON NULL,
            teacher_certifications JSON NULL,
            hourly_rate DECIMAL(5,2) NULL,
            availability JSON NULL,
            student_reviews JSON NULL,
            total_students INT NOT NULL DEFAULT 0,
            active_students INT NOT NULL DEFAULT 0,
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE `user`');
    }
}
