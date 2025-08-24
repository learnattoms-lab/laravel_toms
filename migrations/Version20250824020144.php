<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250824020144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assignment (id INT AUTO_INCREMENT NOT NULL, lesson_id INT DEFAULT NULL, session_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, instructions_html LONGTEXT NOT NULL, due_at VARCHAR(255) NOT NULL, max_points INT NOT NULL, created_at VARCHAR(255) NOT NULL, rubric LONGTEXT DEFAULT NULL, attachments JSON DEFAULT NULL, is_required TINYINT(1) DEFAULT 1 NOT NULL, allow_late_submission TINYINT(1) DEFAULT 0 NOT NULL, late_penalty INT DEFAULT 0 NOT NULL, INDEX IDX_30C544BACDF80196 (lesson_id), INDEX IDX_30C544BA613FECDF (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE assignment_submission (id INT AUTO_INCREMENT NOT NULL, assignment_id INT NOT NULL, student_id INT NOT NULL, graded_by_id INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, files JSON NOT NULL, submitted_at VARCHAR(255) NOT NULL, grade_points INT DEFAULT NULL, feedback_html LONGTEXT DEFAULT NULL, graded_at DATETIME DEFAULT NULL, is_late TINYINT(1) DEFAULT 0 NOT NULL, late_penalty_applied INT DEFAULT 0 NOT NULL, INDEX IDX_E5A63E2CD19302F8 (assignment_id), INDEX IDX_E5A63E2CCB944F1A (student_id), INDEX IDX_E5A63E2CC814BC2E (graded_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE certificate (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, course_id INT NOT NULL, issued_at VARCHAR(255) NOT NULL, certificate_url VARCHAR(500) NOT NULL, serial VARCHAR(100) NOT NULL, final_score NUMERIC(5, 2) NOT NULL, grade VARCHAR(20) NOT NULL, notes LONGTEXT DEFAULT NULL, metadata JSON DEFAULT NULL, is_valid TINYINT(1) DEFAULT 1 NOT NULL, revoked_at DATETIME DEFAULT NULL, revocation_reason LONGTEXT DEFAULT NULL, INDEX IDX_219CDA4AA76ED395 (user_id), INDEX IDX_219CDA4A591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, submission_id INT NOT NULL, author_id INT NOT NULL, body LONGTEXT NOT NULL, created_at VARCHAR(255) NOT NULL, is_internal TINYINT(1) DEFAULT 0 NOT NULL, attachments JSON DEFAULT NULL, INDEX IDX_9474526CE1FD4933 (submission_id), INDEX IDX_9474526CF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, teacher_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, instrument VARCHAR(100) NOT NULL, level VARCHAR(50) NOT NULL, price_cents INT NOT NULL, published_at DATETIME DEFAULT NULL, created_at VARCHAR(255) NOT NULL, updated_at VARCHAR(255) NOT NULL, thumbnail VARCHAR(255) DEFAULT NULL, tags JSON DEFAULT NULL, total_lessons INT DEFAULT 0 NOT NULL, total_duration INT DEFAULT 0 NOT NULL, INDEX IDX_169E6FB941807E1D (teacher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enrollment (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, student_id INT NOT NULL, status VARCHAR(20) NOT NULL, started_at VARCHAR(255) NOT NULL, completed_at DATETIME DEFAULT NULL, progress_pct NUMERIC(5, 2) DEFAULT \'0\' NOT NULL, created_at VARCHAR(255) NOT NULL, updated_at VARCHAR(255) NOT NULL, last_accessed_at DATETIME DEFAULT NULL, lessons_completed INT DEFAULT 0 NOT NULL, total_lessons INT DEFAULT 0 NOT NULL, completed_lessons JSON DEFAULT NULL, quiz_scores JSON DEFAULT NULL, assignment_scores JSON DEFAULT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_DBDCD7E1591CC992 (course_id), INDEX IDX_DBDCD7E1CB944F1A (student_id), UNIQUE INDEX unique_student_course (student_id, course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lesson (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, title VARCHAR(255) NOT NULL, order_index INT NOT NULL, content_html LONGTEXT NOT NULL, duration_min INT NOT NULL, resources JSON DEFAULT NULL, created_at VARCHAR(255) NOT NULL, summary LONGTEXT DEFAULT NULL, learning_objectives JSON DEFAULT NULL, is_required TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_F87474F3591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, lesson_id INT NOT NULL, body LONGTEXT NOT NULL, updated_at VARCHAR(255) NOT NULL, tags JSON DEFAULT NULL, is_public TINYINT(1) DEFAULT 0 NOT NULL, word_count INT DEFAULT 0 NOT NULL, character_count INT DEFAULT 0 NOT NULL, INDEX IDX_CFBDFA14A76ED395 (user_id), INDEX IDX_CFBDFA14CDF80196 (lesson_id), UNIQUE INDEX unique_user_lesson (user_id, lesson_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_credential (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, provider VARCHAR(50) NOT NULL, access_token LONGTEXT NOT NULL, refresh_token LONGTEXT DEFAULT NULL, expires_at VARCHAR(255) NOT NULL, created_at VARCHAR(255) NOT NULL, updated_at VARCHAR(255) NOT NULL, INDEX IDX_97B9B2F0A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, course_id INT NOT NULL, amount_cents INT NOT NULL, currency VARCHAR(3) NOT NULL, status VARCHAR(20) NOT NULL, stripe_session_id VARCHAR(255) DEFAULT NULL, stripe_payment_intent_id VARCHAR(255) DEFAULT NULL, created_at VARCHAR(255) NOT NULL, updated_at VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, failure_reason VARCHAR(255) DEFAULT NULL, INDEX IDX_F5299398A76ED395 (user_id), INDEX IDX_F5299398591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, lesson_id INT NOT NULL, questions JSON NOT NULL, pass_mark INT NOT NULL, created_at VARCHAR(255) NOT NULL, instructions LONGTEXT DEFAULT NULL, time_limit INT DEFAULT 0 NOT NULL, allow_retakes TINYINT(1) DEFAULT 1 NOT NULL, max_attempts INT DEFAULT 3 NOT NULL, shuffle_questions TINYINT(1) DEFAULT 0 NOT NULL, show_correct_answers TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_A412FA92CDF80196 (lesson_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz_attempt (id INT AUTO_INCREMENT NOT NULL, quiz_id INT NOT NULL, student_id INT NOT NULL, score INT NOT NULL, passed TINYINT(1) NOT NULL, submitted_at VARCHAR(255) NOT NULL, responses JSON NOT NULL, started_at DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, time_spent INT DEFAULT NULL, question_order JSON DEFAULT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_AB6AFC6853CD175 (quiz_id), INDEX IDX_AB6AFC6CB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, lesson_id INT DEFAULT NULL, tutor_id INT NOT NULL, start_at VARCHAR(255) NOT NULL, end_at VARCHAR(255) NOT NULL, join_url VARCHAR(500) DEFAULT NULL, google_event_id VARCHAR(255) DEFAULT NULL, materials JSON DEFAULT NULL, recording_url VARCHAR(500) DEFAULT NULL, created_at VARCHAR(255) NOT NULL, updated_at VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, status VARCHAR(50) DEFAULT \'scheduled\' NOT NULL, max_students INT DEFAULT 0 NOT NULL, INDEX IDX_D044D5D4591CC992 (course_id), INDEX IDX_D044D5D4CDF80196 (lesson_id), INDEX IDX_D044D5D4208F64F1 (tutor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session_students (session_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_2FC5EDDA613FECDF (session_id), INDEX IDX_2FC5EDDAA76ED395 (user_id), PRIMARY KEY(session_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stored_file (id INT AUTO_INCREMENT NOT NULL, uploaded_by_id INT NOT NULL, blob_name VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, content_type VARCHAR(100) NOT NULL, size INT NOT NULL, url VARCHAR(500) NOT NULL, created_at VARCHAR(255) NOT NULL, INDEX IDX_C339E77CA2B28FE8 (uploaded_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BACDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BA613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('ALTER TABLE assignment_submission ADD CONSTRAINT FK_E5A63E2CD19302F8 FOREIGN KEY (assignment_id) REFERENCES assignment (id)');
        $this->addSql('ALTER TABLE assignment_submission ADD CONSTRAINT FK_E5A63E2CCB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE assignment_submission ADD CONSTRAINT FK_E5A63E2CC814BC2E FOREIGN KEY (graded_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CE1FD4933 FOREIGN KEY (submission_id) REFERENCES assignment_submission (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB941807E1D FOREIGN KEY (teacher_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE oauth_credential ADD CONSTRAINT FK_97B9B2F0A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT FK_AB6AFC6853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT FK_AB6AFC6CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4208F64F1 FOREIGN KEY (tutor_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE session_students ADD CONSTRAINT FK_2FC5EDDA613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_students ADD CONSTRAINT FK_2FC5EDDAA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stored_file ADD CONSTRAINT FK_C339E77CA2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assignment DROP FOREIGN KEY FK_30C544BACDF80196');
        $this->addSql('ALTER TABLE assignment DROP FOREIGN KEY FK_30C544BA613FECDF');
        $this->addSql('ALTER TABLE assignment_submission DROP FOREIGN KEY FK_E5A63E2CD19302F8');
        $this->addSql('ALTER TABLE assignment_submission DROP FOREIGN KEY FK_E5A63E2CCB944F1A');
        $this->addSql('ALTER TABLE assignment_submission DROP FOREIGN KEY FK_E5A63E2CC814BC2E');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA76ED395');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A591CC992');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CE1FD4933');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB941807E1D');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1591CC992');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1CB944F1A');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3591CC992');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14A76ED395');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14CDF80196');
        $this->addSql('ALTER TABLE oauth_credential DROP FOREIGN KEY FK_97B9B2F0A76ED395');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398591CC992');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92CDF80196');
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY FK_AB6AFC6853CD175');
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY FK_AB6AFC6CB944F1A');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4591CC992');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4CDF80196');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4208F64F1');
        $this->addSql('ALTER TABLE session_students DROP FOREIGN KEY FK_2FC5EDDA613FECDF');
        $this->addSql('ALTER TABLE session_students DROP FOREIGN KEY FK_2FC5EDDAA76ED395');
        $this->addSql('ALTER TABLE stored_file DROP FOREIGN KEY FK_C339E77CA2B28FE8');
        $this->addSql('DROP TABLE assignment');
        $this->addSql('DROP TABLE assignment_submission');
        $this->addSql('DROP TABLE certificate');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE enrollment');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE oauth_credential');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE quiz_attempt');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE session_students');
        $this->addSql('DROP TABLE stored_file');
    }
}
