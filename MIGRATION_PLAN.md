# Laravel + Vue.js Migration Task Plan

## Document Overview

This document provides a detailed, manageable task plan for migrating the Tom's Music School LMS from Symfony 6.3 to Laravel 11.x + Vue.js 3.x + Tailwind CSS. Each task includes dependencies, acceptance criteria, and time estimates.

**Document Version**: 1.0  
**Last Updated**: [Current Date]

---

## Task Status Legend

- 🟢 **Not Started** - Task not yet begun
- 🟡 **In Progress** - Task actively being worked on
- 🔵 **Review** - Task completed, awaiting review
- ✅ **Complete** - Task completed and approved
- ⚠️ **Blocked** - Task blocked by dependencies
- ❌ **Cancelled** - Task cancelled

---

## Phase 0: Local Development Environment Setup

### Task 0.1: Docker Prerequisites Installation
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 1 hour  
**Dependencies**: None

**Description**:
Install Docker and Docker Compose on the local machine for containerized development environment.

**Subtasks**:
1. Install Docker Desktop (or Docker Engine + Docker Compose)
   - macOS: Download Docker Desktop from docker.com
   - Linux: Install Docker Engine and Docker Compose via package manager
   - Windows: Download Docker Desktop from docker.com
2. Verify Docker installation: `docker --version`
3. Verify Docker Compose installation: `docker compose version`
4. Start Docker Desktop/Engine
5. Test Docker: `docker run hello-world`
6. Install IDE extensions (PHP Intelephense, Volar for Vue, Tailwind CSS IntelliSense)
7. Install Docker extension for IDE (optional but recommended)

**Acceptance Criteria**:
- ✅ Docker installed and running
- ✅ Docker Compose installed and working
- ✅ Docker can run containers
- ✅ IDE properly configured

**Commands to Verify**:
```bash
docker --version          # Should show Docker version
docker compose version    # Should show Docker Compose version
docker run hello-world    # Should run successfully
docker ps                 # Should show running containers (may be empty initially)
```

---

### Task 0.2: Create Docker Configuration Files
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 3 hours  
**Dependencies**: Task 0.1

**Description**:
Create Docker configuration files for the Laravel development environment including PHP, MySQL, Redis, and Node.js services.

**Subtasks**:
1. Create `docker-compose.yml` in project root
2. Create `Dockerfile` for PHP application
3. Create `docker/php/Dockerfile` for PHP-FPM service
4. Create `docker/nginx/Dockerfile` for Nginx service (optional, can use Laravel Sail)
5. Create `docker/nginx/nginx.conf` (if using custom Nginx)
6. Create `docker/php/php.ini` for PHP configuration
7. Create `docker/mysql/init.sql` for database initialization (optional)
8. Create `.dockerignore` file
9. Create `docker-compose.override.yml.example` for local overrides
10. Configure all services (app, mysql, redis, node)
11. Test Docker Compose configuration: `docker compose config`

**Acceptance Criteria**:
- ✅ `docker-compose.yml` created and valid
- ✅ All Dockerfiles created
- ✅ Services properly configured
- ✅ Configuration validated (`docker compose config` runs without errors)

**Docker Compose Structure**:
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: toms_laravel_app
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
      - ./docker/php/php.ini:/usr/local/etc/php/conf.d/custom.ini
    ports:
      - "8000:8000"
    depends_on:
      - mysql
      - redis
    environment:
      - DB_HOST=mysql
      - DB_DATABASE=toms_music_school
      - DB_USERNAME=toms_user
      - DB_PASSWORD=toms_password
      - REDIS_HOST=redis
      - REDIS_PORT=6379

  mysql:
    image: mysql:8.0
    container_name: toms_laravel_mysql
    environment:
      MYSQL_DATABASE: toms_music_school
      MYSQL_USER: toms_user
      MYSQL_PASSWORD: toms_password
      MYSQL_ROOT_PASSWORD: root_password
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/init.sql:/docker-entrypoint-initdb.d/init.sql

  redis:
    image: redis:7-alpine
    container_name: toms_laravel_redis
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data

  node:
    image: node:20-alpine
    container_name: toms_laravel_node
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
      - node_modules:/var/www/html/node_modules
    command: sh -c "npm install && npm run dev"
    ports:
      - "5173:5173"
    depends_on:
      - app

volumes:
  mysql_data:
  redis_data:
  node_modules:
```

**Dockerfile Structure**:
```dockerfile
# docker/php/Dockerfile
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && docker-php-ext-enable pdo_mysql mbstring

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
```

---

### Task 0.3: Create New Laravel Project Structure
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 1.5 hours  
**Dependencies**: Task 0.2

**Description**:
Initialize a new Laravel 11.x project using Docker containers.

**Subtasks**:
1. Create new directory: `laravel-toms-music-school`
2. Start Docker containers: `docker compose up -d`
3. Access PHP container: `docker compose exec app bash`
4. Initialize Laravel project inside container: `composer create-project laravel/laravel .`
5. Verify Laravel installation: `php artisan --version`
6. Test basic routes
7. Set up `.env` file from `.env.example`
8. Configure database connection (using Docker service names)
9. Run migrations: `php artisan migrate`
10. Access application in browser: `http://localhost:8000`

**Acceptance Criteria**:
- ✅ Laravel project created successfully
- ✅ Docker containers running
- ✅ `php artisan` command works inside container
- ✅ `.env` file configured with Docker service names
- ✅ Database connection tested
- ✅ Basic route accessible in browser

**Commands**:
```bash
# Create project directory
mkdir laravel-toms-music-school
cd laravel-toms-music-school

# Start Docker containers
docker compose up -d

# Access PHP container
docker compose exec app bash

# Inside container: Install Laravel
composer create-project laravel/laravel .

# Inside container: Generate app key
php artisan key:generate

# Inside container: Run migrations (after DB setup)
php artisan migrate

# From host machine: Access application
curl http://localhost:8000
```

**Docker Environment Variables** (.env):
```env
DB_HOST=mysql          # Docker service name
DB_DATABASE=toms_music_school
DB_USERNAME=toms_user
DB_PASSWORD=toms_password
REDIS_HOST=redis       # Docker service name
REDIS_PORT=6379
```

**Helper Scripts to Create**:
```bash
# scripts/docker-php.sh - Execute PHP commands
#!/bin/bash
docker compose exec app php "$@"

# scripts/docker-composer.sh - Execute Composer commands
#!/bin/bash
docker compose exec app composer "$@"

# scripts/docker-artisan.sh - Execute Artisan commands
#!/bin/bash
docker compose exec app php artisan "$@"

# scripts/docker-npm.sh - Execute npm commands
#!/bin/bash
docker compose exec node npm "$@"
```

---

### Task 0.4: Configure Git Repository
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 30 minutes  
**Dependencies**: Task 0.3

**Description**:
Set up Git repository for the new Laravel project with proper `.gitignore` configuration including Docker-related files.

**Subtasks**:
1. Initialize Git repository (if not already initialized)
2. Verify `.gitignore` includes Laravel defaults
3. Add Docker-related entries to `.gitignore`:
   - `.docker/`
   - `docker-compose.override.yml` (keep example)
   - Docker volumes (if tracked)
4. Add custom entries for IDE files, OS files
5. Keep `docker-compose.yml` and `Dockerfile` in repository
6. Create initial commit
7. Set up remote repository connection (optional)

**Acceptance Criteria**:
- ✅ Git repository initialized
- ✅ `.gitignore` properly configured
- ✅ Docker files tracked appropriately
- ✅ Initial commit created
- ✅ Sensitive files excluded from version control

**`.gitignore` Additions**:
```
# Docker
.docker/
docker-compose.override.yml
*.log
```

---

### Task 0.5: Install and Configure Development Tools
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 2 hours  
**Dependencies**: Task 0.3

**Description**:
Install and configure code quality tools, testing frameworks, and development dependencies inside Docker container.

**Subtasks**:
1. Access PHP container: `docker compose exec app bash`
2. Install Laravel Pint: `composer require laravel/pint --dev`
3. Install PHPStan: `composer require --dev phpstan/phpstan`
4. Configure Laravel Pint rules
5. Create PHPStan configuration file
6. Install Laravel Telescope: `composer require laravel/telescope --dev`
7. Install Laravel Debugbar: `composer require barryvdh/laravel-debugbar --dev`
8. Publish Telescope assets: `php artisan telescope:install`
9. Configure IDE for Laravel (PhpStorm/VS Code settings)
10. Test tools: Run Pint and PHPStan inside container

**Acceptance Criteria**:
- ✅ Laravel Pint installed and configured
- ✅ PHPStan installed and configured
- ✅ Code formatting runs without errors
- ✅ Static analysis runs without errors
- ✅ IDE properly configured for Laravel
- ✅ All tools work inside Docker container

**Commands**:
```bash
# Access container
docker compose exec app bash

# Inside container
composer require laravel/pint --dev
composer require --dev phpstan/phpstan
composer require laravel/telescope --dev
php artisan telescope:install

# Test Pint
./vendor/bin/pint --test

# Test PHPStan
./vendor/bin/phpstan analyse
```

---

### Task 0.6: Set Up Frontend Development Environment
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 2 hours  
**Dependencies**: Task 0.3

**Description**:
Configure Vite, Vue.js, Tailwind CSS, and all frontend dependencies using Node.js Docker container.

**Subtasks**:
1. Access Node container: `docker compose exec node sh`
2. Install Vue.js 3: `npm install vue@^3.4.0`
3. Install Vue Router 4: `npm install vue-router@^4.2.0`
4. Install Pinia: `npm install pinia@^2.1.0`
5. Install Axios: `npm install axios`
6. Install Tailwind CSS: `npm install -D tailwindcss postcss autoprefixer`
7. Install PrimeVue: `npm install primevue primeicons`
8. Install Vuelidate: `npm install vuelidate@^2.0.0`
9. Configure `vite.config.js` for Vue
10. Configure `tailwind.config.js`
11. Create `resources/css/app.css` with Tailwind directives
12. Create `resources/js/app.js` as Vue entry point
13. Start Vite dev server: `docker compose exec node npm run dev`
14. Test Vite dev server: Access `http://localhost:5173`
15. Test production build: `docker compose exec node npm run build`

**Acceptance Criteria**:
- ✅ All npm packages installed
- ✅ Vite configuration working
- ✅ Tailwind CSS configured
- ✅ Vue.js app mounts successfully
- ✅ Hot module replacement (HMR) working via Docker volumes
- ✅ Build process works: `npm run build`

**Commands**:
```bash
# Access Node container
docker compose exec node sh

# Inside container
npm install vue@^3.4.0
npm install vue-router@^4.2.0
npm install pinia@^2.1.0
npm install axios
npm install -D tailwindcss postcss autoprefixer
npm install primevue primeicons
npm install vuelidate@^2.0.0
npm run dev

# From host: Access Vite dev server
# http://localhost:5173
```

**Vite Configuration** (vite.config.js):
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
        },
    },
});
```

---

### Task 0.7: Database Setup for Local Development
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 1 hour  
**Dependencies**: Task 0.3

**Description**:
Set up MySQL database in Docker container and configure Laravel to connect to it.

**Subtasks**:
1. Verify MySQL container running: `docker compose ps mysql`
2. Access MySQL container: `docker compose exec mysql bash`
3. Test MySQL connection: `mysql -u toms_user -p`
4. Update `.env` with Docker database credentials:
   - `DB_HOST=mysql` (Docker service name)
   - `DB_DATABASE=toms_music_school`
   - `DB_USERNAME=toms_user`
   - `DB_PASSWORD=toms_password`
5. Test database connection from PHP container: `php artisan migrate:status`
6. Create initial migration (if needed): `php artisan migrate`
7. Test connection with simple query

**Acceptance Criteria**:
- ✅ MySQL container running
- ✅ Database created via environment variables
- ✅ `.env` configured correctly with Docker service names
- ✅ Laravel can connect to database from PHP container
- ✅ Database user has proper permissions

**Commands**:
```bash
# Check MySQL container status
docker compose ps mysql

# Access MySQL container
docker compose exec mysql bash

# Inside MySQL container
mysql -u root -p
# root password: root_password

# Create database (usually auto-created via environment variables)
CREATE DATABASE IF NOT EXISTS toms_music_school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Test from PHP container
docker compose exec app php artisan migrate:status
```

**Environment Variables** (.env):
```env
DB_CONNECTION=mysql
DB_HOST=mysql              # Docker service name, not localhost
DB_PORT=3306
DB_DATABASE=toms_music_school
DB_USERNAME=toms_user
DB_PASSWORD=toms_password
```

---

### Task 0.8: Configure Environment Variables for Docker
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 1.5 hours  
**Dependencies**: Task 0.3, Task 0.7

**Description**:
Set up all environment variables needed for Docker-based local development.

**Subtasks**:
1. Copy existing `.env` values from Symfony project (where applicable)
2. Configure app name, URL, and debug mode for local
3. Set database credentials (using Docker service names):
   - `DB_HOST=mysql` (not `localhost`)
   - `DB_DATABASE=toms_music_school`
   - `DB_USERNAME=toms_user`
   - `DB_PASSWORD=toms_password`
4. Configure cache and queue drivers:
   - `CACHE_DRIVER=redis`
   - `QUEUE_CONNECTION=redis`
   - `REDIS_HOST=redis` (Docker service name, not localhost)
   - `REDIS_PORT=6379`
5. Configure session driver: `SESSION_DRIVER=redis`
6. Set up mail configuration (use Mailpit or Mailtrap for local)
7. Add Google OAuth credentials (from Symfony `.env`)
8. Add Google Cloud Storage (GCS) credentials
9. Add Stripe API keys (test keys)
10. Configure CORS settings for Docker:
    - `APP_URL=http://localhost:8000`
    - `FRONTEND_URL=http://localhost:5173`
11. Create `.env.example` with Docker-friendly defaults
12. Document all required environment variables
13. Test application with new environment variables

**Acceptance Criteria**:
- ✅ All environment variables documented
- ✅ `.env.example` updated with Docker service names
- ✅ Local `.env` configured with Docker service names
- ✅ App runs without missing env variable errors
- ✅ All services connect using Docker service names

**Key Environment Variables for Docker**:
```env
APP_NAME="Tom's Music School"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173

# Database (Docker service names)
DB_CONNECTION=mysql
DB_HOST=mysql              # Docker service name, NOT localhost
DB_PORT=3306
DB_DATABASE=toms_music_school
DB_USERNAME=toms_user
DB_PASSWORD=toms_password

# Cache & Queue (Docker service names)
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=redis           # Docker service name, NOT localhost
REDIS_PORT=6379

# Mail (use Mailpit in Docker)
MAIL_MAILER=smtp
MAIL_HOST=mailpit          # If using Mailpit service
MAIL_PORT=1025

# OAuth & Third-party
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

GOOGLE_CLOUD_KEY_FILE=
GOOGLE_CLOUD_STORAGE_BUCKET=toms-lms
GOOGLE_CLOUD_STORAGE_PUBLIC_BASE_URL=
GOOGLE_CLOUD_STORAGE_MAKE_PUBLIC=false

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://localhost:3000
```

**Docker Network Communication**:
- Services communicate using service names as hostnames
- `localhost` refers to the container itself, not the host machine
- Use service names: `mysql`, `redis`, `node`, `app`

---

### Task 0.9: Create Docker Helper Scripts
**Status**: 🟢 Not Started  
**Priority**: Medium  
**Estimated Time**: 1 hour  
**Dependencies**: Task 0.3

**Description**:
Create helper scripts to simplify Docker command execution.

**Subtasks**:
1. Create `scripts/` directory
2. Create `scripts/docker-php.sh` - Execute PHP commands
3. Create `scripts/docker-artisan.sh` - Execute Artisan commands
4. Create `scripts/docker-composer.sh` - Execute Composer commands
5. Create `scripts/docker-npm.sh` - Execute npm commands
6. Create `scripts/docker-bash.sh` - Access container shell
7. Create `scripts/docker-mysql.sh` - Access MySQL CLI
8. Make all scripts executable: `chmod +x scripts/*.sh`
9. Create `Makefile` with common Docker commands (optional)
10. Document scripts usage

**Acceptance Criteria**:
- ✅ All scripts created
- ✅ Scripts executable
- ✅ Scripts work correctly
- ✅ Documentation provided

**Helper Scripts**:

```bash
#!/bin/bash
# scripts/docker-artisan.sh
docker compose exec app php artisan "$@"

#!/bin/bash
# scripts/docker-composer.sh
docker compose exec app composer "$@"

#!/bin/bash
# scripts/docker-php.sh
docker compose exec app php "$@"

#!/bin/bash
# scripts/docker-npm.sh
docker compose exec node npm "$@"

#!/bin/bash
# scripts/docker-bash.sh
docker compose exec app bash

#!/bin/bash
# scripts/docker-mysql.sh
docker compose exec mysql mysql -u toms_user -ptoms_password toms_music_school
```

**Makefile (Optional)**:
```makefile
.PHONY: up down restart build logs shell artisan composer npm migrate seed test pint phpstan

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

build:
	docker compose build

logs:
	docker compose logs -f

shell:
	docker compose exec app bash

artisan:
	docker compose exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

composer:
	docker compose exec app composer $(filter-out $@,$(MAKECMDGOALS))

npm:
	docker compose exec node npm $(filter-out $@,$(MAKECMDGOALS))

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

test:
	docker compose exec app php artisan test

pint:
	docker compose exec app ./vendor/bin/pint

phpstan:
	docker compose exec app ./vendor/bin/phpstan analyse

%:
	@:
```

**Usage Examples**:
```bash
# Make commands
make up           # Start containers
make artisan migrate
make composer install
make npm run dev

# Script commands
./scripts/docker-artisan.sh migrate
./scripts/docker-composer.sh install
./scripts/docker-npm.sh run dev
./scripts/docker-bash.sh  # Access container shell
```

---

## Phase 1: Foundation Setup

### Task 1.1: Analyze Existing Database Schema
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 4 hours  
**Dependencies**: None

**Description**:
Analyze existing Symfony/Doctrine database schema to understand all tables, relationships, indexes, and constraints.

**Subtasks**:
1. Export current database schema: `mysqldump --no-data`
2. Document all tables and their columns
3. Document all foreign key relationships
4. Document all indexes
5. Document all unique constraints
6. Document all enum types
7. Document all JSON columns and their structure
8. Create ER diagram (optional but helpful)
9. Identify any custom column types
10. Document default values
11. Document nullable columns
12. Create schema documentation file

**Deliverables**:
- Database schema export file
- Schema documentation (`docs/database-schema.md`)
- ER diagram (if created)

**Acceptance Criteria**:
- ✅ Complete database schema documented
- ✅ All relationships mapped
- ✅ All indexes identified
- ✅ JSON column structures documented

---

### Task 1.2: Create Database Migrations - Users Table
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 3 hours  
**Dependencies**: Task 1.1

**Description**:
Create Laravel migration for users table with all columns, relationships, and indexes from Symfony.

**Subtasks**:
1. Analyze User entity from Symfony
2. Create migration: `php artisan make:migration create_users_table`
3. Define all columns with proper types
4. Add indexes (email, is_active, roles)
5. Add unique constraint on email
6. Set up timestamps (`created_at`, `updated_at`)
7. Add soft deletes if applicable
8. Add enum types or string types for roles
9. Define JSON columns for preferences, achievements, etc.
10. Test migration: `php artisan migrate`
11. Verify table structure matches Symfony version

**Acceptance Criteria**:
- ✅ Migration creates table successfully
- ✅ All columns match Symfony User entity
- ✅ All indexes created
- ✅ Unique constraint on email works
- ✅ JSON columns properly defined

**Columns to Include** (based on analysis):
- id, email, password, roles (JSON), first_name, last_name
- phone, date_of_birth, instrument, skill_level, bio
- profile_picture, city, country, timezone
- preferences (JSON), created_at, last_login_at, is_active
- email_verified, google_id, apple_id, facebook_id
- experience_points, level, achievements (JSON), badges (JSON)
- rating, total_lessons, completed_lessons, practice_hours
- last_practice_at, learning_goals (JSON), progress_data (JSON)
- notes, is_teacher, teacher_bio, teacher_specialties (JSON)
- teacher_certifications (JSON), hourly_rate
- availability (JSON), student_reviews (JSON)
- total_students, active_students
- failed_login_attempts, last_failed_login_at, last_failed_login_ip
- is_locked, locked_until, etc.

---

### Task 1.3: Create Database Migrations - Courses Table
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 2 hours  
**Dependencies**: Task 1.2

**Subtasks**:
1. Analyze Course entity from Symfony
2. Create migration for courses table
3. Define foreign key to users (teacher_id)
4. Add indexes (teacher_id, published_at, instrument, level)
5. Add JSON columns for tags
6. Add timestamps and soft deletes
7. Test migration

**Acceptance Criteria**:
- ✅ Courses table created
- ✅ Foreign key relationship to users
- ✅ All indexes created
- ✅ JSON columns work correctly

---

### Task 1.4: Create Database Migrations - Lessons Table
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 2 hours  
**Dependencies**: Task 1.3

**Subtasks**:
1. Analyze Lesson entity
2. Create migration for lessons table
3. Define foreign key to courses
4. Add order_index column
5. Add timestamps
6. Test migration

**Acceptance Criteria**:
- ✅ Lessons table created
- ✅ Foreign key to courses works
- ✅ Order indexing works

---

### Task 1.5: Create Database Migrations - Sessions Table
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 2 hours  
**Dependencies**: Task 1.3

**Subtasks**:
1. Analyze Session entity
2. Create migration for sessions table
3. Define foreign keys (course_id, lesson_id, tutor_id)
4. Add pivot table for session_students (many-to-many)
5. Add indexes for dates and status
6. Add JSON column for materials
7. Test migration

**Acceptance Criteria**:
- ✅ Sessions table created
- ✅ Pivot table created
- ✅ All foreign keys work
- ✅ Indexes created

---

### Task 1.6: Create Database Migrations - Enrollments Table
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 2 hours  
**Dependencies**: Task 1.2, Task 1.3

**Subtasks**:
1. Analyze Enrollment entity
2. Create migration for enrollments table
3. Add unique constraint on (student_id, course_id)
4. Define foreign keys (student_id, course_id)
5. Add status column with enum or string
6. Add progress tracking columns
7. Add JSON columns for completed_lessons, quiz_scores, assignment_scores
8. Add indexes
9. Test migration

**Acceptance Criteria**:
- ✅ Enrollments table created
- ✅ Unique constraint works
- ✅ Foreign keys work
- ✅ JSON columns work

---

### Task 1.7: Create Database Migrations - Assignments Table
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 2 hours  
**Dependencies**: Task 1.4, Task 1.5

**Subtasks**:
1. Analyze Assignment entity
2. Create migration for assignments table
3. Add nullable foreign keys (lesson_id, session_id)
4. Add indexes
5. Add JSON column for attachments
6. Add timestamps
7. Test migration

**Acceptance Criteria**:
- ✅ Assignments table created
- ✅ Foreign keys work (nullable)
- ✅ JSON columns work

---

### Task 1.8: Create Database Migrations - Assignment Submissions Table
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 2 hours  
**Dependencies**: Task 1.7, Task 1.2

**Subtasks**:
1. Analyze AssignmentSubmission entity
2. Create migration for assignment_submissions table
3. Define foreign keys (assignment_id, student_id)
4. Add indexes
5. Add JSON column for attachments
6. Add grading columns
7. Test migration

**Acceptance Criteria**:
- ✅ Assignment submissions table created
- ✅ Foreign keys work
- ✅ All columns defined

---

### Task 1.9: Create Database Migrations - Quizzes Table
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 2 hours  
**Dependencies**: Task 1.4

**Subtasks**:
1. Analyze Quiz entity
2. Create migration for quizzes table
3. Define foreign key to lessons
4. Add JSON column for questions
5. Add indexes
6. Test migration

**Acceptance Criteria**:
- ✅ Quizzes table created
- ✅ JSON column for questions works
- ✅ Foreign key works

---

### Task 1.10: Create Database Migrations - Quiz Attempts Table
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 2 hours  
**Dependencies**: Task 1.9, Task 1.2

**Subtasks**:
1. Analyze QuizAttempt entity
2. Create migration for quiz_attempts table
3. Define foreign keys (quiz_id, student_id)
4. Add JSON column for answers
5. Add scoring columns
6. Add indexes
7. Test migration

**Acceptance Criteria**:
- ✅ Quiz attempts table created
- ✅ Foreign keys work
- ✅ JSON column works

---

### Task 1.11: Create Database Migrations - Orders Table
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 2 hours  
**Dependencies**: Task 1.2, Task 1.3

**Subtasks**:
1. Analyze Order entity
2. Create migration for orders table
3. Define foreign keys (user_id, course_id)
4. Add Stripe-related columns
5. Add status column
6. Add indexes
7. Test migration

**Acceptance Criteria**:
- ✅ Orders table created
- ✅ Foreign keys work
- ✅ Stripe columns defined

---

### Task 1.12: Create Database Migrations - Remaining Tables
**Status**: 🟢 Not Started  
**Priority**: Medium  
**Estimated Time**: 4 hours  
**Dependencies**: Task 1.2

**Description**:
Create migrations for remaining tables: certificates, comments, notes, oauth_credentials, stored_files.

**Subtasks**:
1. Create certificates table migration
2. Create comments table migration (polymorphic relationships)
3. Create notes table migration
4. Create oauth_credentials table migration
5. Create stored_files table migration
6. Test all migrations
7. Verify all relationships

**Acceptance Criteria**:
- ✅ All tables created
- ✅ All foreign keys work
- ✅ Polymorphic relationships work (for comments)

---

### Task 1.13: Create Eloquent Models - User Model
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 4 hours  
**Dependencies**: Task 1.2

**Description**:
Create User Eloquent model with all relationships, accessors, mutators, and scopes.

**Subtasks**:
1. Create `app/Models/User.php`
2. Add `declare(strict_types=1)`
3. Implement `Authenticatable` interface
4. Add `HasApiTokens` trait (Laravel Sanctum)
5. Define `$fillable` or `$guarded` array
6. Define `$casts` for JSON columns, dates, booleans
7. Define `$hidden` for sensitive fields (password)
8. Create relationships:
   - `courses()` - hasMany (as teacher)
   - `enrollments()` - hasMany
   - `sessions()` - belongsToMany (as student)
   - `taughtSessions()` - hasMany (as teacher)
   - `assignments()` - hasManyThrough
   - `assignmentSubmissions()` - hasMany
   - `quizAttempts()` - hasMany
   - `orders()` - hasMany
   - `certificates()` - hasMany
   - `oauthCredentials()` - hasMany
   - `storedFiles()` - hasMany
   - `notes()` - hasMany
9. Create accessors (e.g., `full_name`)
10. Create mutators (e.g., password hashing)
11. Create scopes (e.g., `active()`, `teachers()`, `students()`)
12. Create helper methods (e.g., `hasRole()`, `isTeacher()`)
13. Add PHPDoc comments
14. Write unit tests for model

**Acceptance Criteria**:
- ✅ Model created with strict typing
- ✅ All relationships work
- ✅ Accessors/mutators work
- ✅ Scopes work
- ✅ Unit tests pass

**Model Structure**:
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    
    protected $fillable = [/* ... */];
    protected $casts = [/* ... */];
    protected $hidden = ['password', 'remember_token'];
    
    // Relationships
    // Scopes
    // Accessors/Mutators
    // Helper Methods
}
```

---

### Task 1.14: Create Eloquent Models - Course Model
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 3 hours  
**Dependencies**: Task 1.13, Task 1.3

**Subtasks**:
1. Create `app/Models/Course.php`
2. Add strict typing
3. Define fillable/casts
4. Create relationships:
   - `teacher()` - belongsTo User
   - `lessons()` - hasMany
   - `sessions()` - hasMany
   - `enrollments()` - hasMany
5. Create scopes (`published()`, `byInstrument()`, `byLevel()`)
6. Create accessors (`formattedPrice()`, etc.)
7. Write unit tests

**Acceptance Criteria**:
- ✅ Model created
- ✅ All relationships work
- ✅ Scopes work
- ✅ Tests pass

---

### Task 1.15: Create Eloquent Models - Remaining Models
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 12 hours  
**Dependencies**: Task 1.13, Task 1.14

**Description**:
Create all remaining Eloquent models: Lesson, Session, Enrollment, Assignment, AssignmentSubmission, Quiz, QuizAttempt, Order, Certificate, Comment, Note, OAuthCredential, StoredFile.

**Subtasks**:
For each model:
1. Create model file with strict typing
2. Define fillable/casts
3. Create relationships
4. Create scopes
5. Create accessors/mutators where needed
6. Write unit tests

**Acceptance Criteria**:
- ✅ All models created
- ✅ All relationships work
- ✅ All tests pass

---

### Task 1.16: Set Up Laravel Sanctum for API Authentication
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 3 hours  
**Dependencies**: Task 1.13, Task 0.7

**Description**:
Install and configure Laravel Sanctum for API token authentication.

**Subtasks**:
1. Install Sanctum: `composer require laravel/sanctum`
2. Publish configuration: `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
3. Run migrations: `php artisan migrate`
4. Add `HasApiTokens` trait to User model (already done in Task 1.13)
5. Configure Sanctum middleware in `bootstrap/app.php`
6. Create middleware for API authentication
7. Test token generation
8. Test token authentication

**Acceptance Criteria**:
- ✅ Sanctum installed and configured
- ✅ Tokens can be generated
- ✅ API routes protected with Sanctum
- ✅ Token authentication works

**Configuration**:
```php
// config/sanctum.php - adjust token expiration
'expiration' => 60 * 24 * 7, // 7 days
```

---

### Task 1.17: Set Up Vue.js Authentication Composable
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 4 hours  
**Dependencies**: Task 1.16, Task 0.5 (Install and Configure Development Tools)

**Description**:
Create Vue.js composable for authentication functionality.

**Subtasks**:
1. Create `resources/js/composables/useAuth.js`
2. Implement login method (API call)
3. Implement register method (API call)
4. Implement logout method
5. Implement token storage (localStorage)
6. Implement token refresh logic
7. Create reactive state for user
8. Create reactive state for isAuthenticated
9. Implement axios interceptor for token attachment
10. Create error handling
11. Test login flow
12. Test register flow

**Acceptance Criteria**:
- ✅ Composable created
- ✅ Login works
- ✅ Register works
- ✅ Token stored securely
- ✅ Token attached to API requests
- ✅ User state reactive

**Composable Structure**:
```javascript
// resources/js/composables/useAuth.js
import { ref, computed } from 'vue';
import api from '@/utils/api';

export function useAuth() {
    const user = ref(null);
    const token = ref(localStorage.getItem('auth_token'));
    
    const isAuthenticated = computed(() => !!token.value);
    
    const login = async (credentials) => {
        // Implementation
    };
    
    const register = async (data) => {
        // Implementation
    };
    
    const logout = () => {
        // Implementation
    };
    
    return {
        user,
        token,
        isAuthenticated,
        login,
        register,
        logout,
    };
}
```

---

### Task 1.18: Create Pinia Auth Store
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 3 hours  
**Dependencies**: Task 1.17, Task 0.5

**Description**:
Create Pinia store for global authentication state management.

**Subtasks**:
1. Create `resources/js/stores/auth.js`
2. Define state (user, token, isAuthenticated)
3. Define actions (login, register, logout, fetchUser)
4. Define getters (isAdmin, isTeacher, isStudent)
5. Implement persistence (localStorage)
6. Integrate with useAuth composable
7. Test store functionality

**Acceptance Criteria**:
- ✅ Store created
- ✅ State management works
- ✅ Persistence works
- ✅ Getters work correctly
- ✅ Actions work correctly

---

### Task 1.19: Set Up Vue Router with Route Guards
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 4 hours  
**Dependencies**: Task 1.18, Task 0.5

**Description**:
Configure Vue Router with routes and authentication guards.

**Subtasks**:
1. Create `resources/js/router/index.js`
2. Define public routes (home, about, contact, login, register)
3. Define protected routes (dashboard, courses, etc.)
4. Define admin routes (admin dashboard, user management)
5. Define teacher routes (teacher dashboard, course management)
6. Implement route guards (beforeEach)
7. Implement lazy loading for routes
8. Create route meta for roles
9. Test route navigation
10. Test route guards

**Acceptance Criteria**:
- ✅ Router configured
- ✅ Routes defined
- ✅ Lazy loading works
- ✅ Route guards work
- ✅ Role-based access works

**Router Structure**:
```javascript
// resources/js/router/index.js
import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        // Public routes
        { path: '/', component: () => import('@/pages/HomePage.vue') },
        { path: '/login', component: () => import('@/pages/LoginPage.vue') },
        // Protected routes
        {
            path: '/dashboard',
            component: () => import('@/pages/Dashboard.vue'),
            meta: { requiresAuth: true }
        },
        // Admin routes
        {
            path: '/admin',
            component: () => import('@/pages/admin/AdminDashboard.vue'),
            meta: { requiresAuth: true, requiresRole: 'admin' }
        },
    ],
});

router.beforeEach((to, from, next) => {
    // Guard implementation
});

export default router;
```

---

### Task 1.20: Create Axios Instance with Interceptors
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 2 hours  
**Dependencies**: Task 1.17, Task 0.5

**Description**:
Create configured Axios instance for API calls with interceptors.

**Subtasks**:
1. Create `resources/js/utils/api.js`
2. Configure base URL (`/api/v1`)
3. Set default headers (Content-Type, Accept)
4. Create request interceptor (attach auth token)
5. Create response interceptor (handle errors)
6. Handle 401 errors (logout user)
7. Handle 422 errors (validation errors)
8. Handle 500 errors (server errors)
9. Test interceptors
10. Test API calls

**Acceptance Criteria**:
- ✅ Axios instance created
- ✅ Interceptors work
- ✅ Token attached to requests
- ✅ Error handling works
- ✅ 401 redirects to login

---

## Phase 2: Backend Core Conversion

### Task 2.1: Create Repository Interfaces
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 4 hours  
**Dependencies**: Task 1.15

**Description**:
Create repository interfaces following Repository pattern.

**Subtasks**:
1. Create `app/Repositories/Interfaces/` directory
2. Create `UserRepositoryInterface.php`
3. Create `CourseRepositoryInterface.php`
4. Create `LessonRepositoryInterface.php`
5. Create `SessionRepositoryInterface.php`
6. Create `EnrollmentRepositoryInterface.php`
7. Create `AssignmentRepositoryInterface.php`
8. Create `QuizRepositoryInterface.php`
9. Create `OrderRepositoryInterface.php`
10. Define methods for each interface
11. Add PHPDoc comments

**Acceptance Criteria**:
- ✅ All interfaces created
- ✅ Methods defined
- ✅ Interfaces follow PSR standards

---

### Task 2.2: Implement Repository Classes
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 16 hours  
**Dependencies**: Task 2.1

**Description**:
Implement all repository classes with database operations.

**Subtasks**:
For each repository:
1. Create repository class implementing interface
2. Implement CRUD methods
3. Implement query methods (find, all, paginate)
4. Implement relationship methods
5. Implement scopes and filters
6. Add proper error handling
7. Write unit tests
8. Write feature tests

**Acceptance Criteria**:
- ✅ All repositories implemented
- ✅ All methods work
- ✅ Tests pass
- ✅ Error handling implemented

---

### Task 2.3: Convert GoogleCalendarService to Laravel
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Task 1.15, Task 0.7

**Description**:
Convert Symfony GoogleCalendarService to Laravel service.

**Subtasks**:
1. Copy existing service code
2. Replace Symfony dependencies with Laravel
3. Replace PSR LoggerInterface with `Illuminate\Support\Facades\Log`
4. Replace `$_ENV` with `config()` helper
5. Replace Doctrine EntityManager with Eloquent models
6. Update dependency injection
7. Add proper error handling
8. Add Laravel events for session creation
9. Write unit tests
10. Test Google Meet link generation

**Acceptance Criteria**:
- ✅ Service converted
- ✅ Uses Laravel facades
- ✅ Uses Eloquent models
- ✅ Tests pass
- ✅ Google Meet links generated correctly

**File Structure**:
```
app/Services/
├── GoogleCalendarService.php
└── Interfaces/
    └── GoogleCalendarServiceInterface.php
```

---

### Task 2.4: Convert to Google Cloud Storage Service
**Status**: ✅ Complete  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Task 1.15, Task 0.7

**Description**:
Convert Symfony AzureBlobStorageService to Google Cloud Storage (GCS) service for Laravel. Updated from Azure Blob Storage to GCS for GCP deployment.

**Subtasks**:
1. Copy existing service code
2. Replace Azure Blob Storage SDK with Google Cloud Storage SDK
3. Replace Symfony dependencies with Laravel
4. Replace `$_ENV` with `config()`
5. Replace Doctrine with Eloquent
6. Implement GCS client initialization
7. Add Laravel validation for file uploads
8. Use Laravel queues for large uploads
9. Add proper error handling
10. Write unit tests
11. Test file upload functionality

**Acceptance Criteria**:
- ✅ Service converted to GCS
- ✅ Uses Google Cloud Storage SDK
- ✅ File uploads work
- ✅ Tests pass
- ✅ GCS configuration added to services config

**Note**: Requires `google/cloud-storage` package
Install via: `composer require google/cloud-storage`

---

### Task 2.5: Create StripeService
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Task 1.15, Task 0.7

**Description**:
Extract Stripe logic from CheckoutController into dedicated service.

**Subtasks**:
1. Analyze existing CheckoutController
2. Create `app/Services/StripeService.php`
3. Create `app/Services/Interfaces/StripeServiceInterface.php`
4. Move Stripe checkout session creation to service
5. Move payment processing to service
6. Implement webhook handling methods
7. Add proper error handling
8. Use Laravel config for API keys
9. Use Laravel queues for webhook processing
10. Write unit tests
11. Write integration tests

**Acceptance Criteria**:
- ✅ StripeService created
- ✅ Checkout sessions created
- ✅ Webhooks handled
- ✅ Tests pass

---

### Task 2.6: Create OAuthService
**Status**: 🟢 Not Started  
**Priority**: Medium  
**Estimated Time**: 6 hours  
**Dependencies**: Task 1.15, Task 0.7

**Description**:
Extract OAuth logic from OAuthController into service.

**Subtasks**:
1. Analyze existing OAuthController
2. Install Laravel Socialite: `composer require laravel/socialite`
3. Create `app/Services/OAuthService.php`
4. Implement Google OAuth flow
5. Implement token refresh
6. Store OAuth credentials
7. Handle OAuth errors
8. Write unit tests
9. Test OAuth flow

**Acceptance Criteria**:
- ✅ OAuthService created
- ✅ Google OAuth works
- ✅ Token refresh works
- ✅ Tests pass

---

### Task 2.7: Create API AuthController
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 4 hours  
**Dependencies**: Task 1.16, Task 2.2

**Description**:
Create API authentication controller.

**Subtasks**:
1. Create `app/Http/Controllers/Api/AuthController.php`
2. Implement `login()` method
3. Implement `register()` method
4. Implement `logout()` method
5. Implement `user()` method (get current user)
6. Implement `refresh()` method (token refresh)
7. Create Form Requests for validation
8. Return API Resources
9. Add proper error handling
10. Write feature tests

**Acceptance Criteria**:
- ✅ Controller created
- ✅ Login works
- ✅ Register works
- ✅ Logout works
- ✅ Token generation works
- ✅ Tests pass

**Form Requests to Create**:
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Requests/Auth/RegisterRequest.php`

**API Resources to Create**:
- `app/Http/Resources/UserResource.php`

---

### Task 2.8: Create API CourseController
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Task 2.2, Task 2.7

**Description**:
Create API controller for course management.

**Subtasks**:
1. Create `app/Http/Controllers/Api/CourseController.php`
2. Implement `index()` - list courses
3. Implement `show()` - course details
4. Implement `store()` - create course (teachers only)
5. Implement `update()` - update course (teachers only)
6. Implement `destroy()` - delete course (teachers only)
7. Implement `enroll()` - enroll in course
8. Create Form Requests
9. Return API Resources
10. Add authorization (policies)
11. Write feature tests

**Acceptance Criteria**:
- ✅ Controller created
- ✅ CRUD operations work
- ✅ Enrollment works
- ✅ Authorization works
- ✅ Tests pass

**Routes**:
```
GET    /api/v1/courses
GET    /api/v1/courses/{id}
POST   /api/v1/courses
PUT    /api/v1/courses/{id}
DELETE /api/v1/courses/{id}
POST   /api/v1/courses/{id}/enroll
```

---

### Task 2.9: Create API SessionController
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Task 2.3, Task 2.2

**Description**:
Create API controller for session management.

**Subtasks**:
1. Create `app/Http/Controllers/Api/SessionController.php`
2. Implement `index()` - list sessions
3. Implement `show()` - session details
4. Implement `store()` - create session (teachers only)
5. Implement `update()` - update session (teachers only)
6. Implement `destroy()` - delete session (teachers only)
7. Implement `join()` - get join link
8. Integrate GoogleCalendarService
9. Create Form Requests
10. Return API Resources
11. Write feature tests

**Acceptance Criteria**:
- ✅ Controller created
- ✅ CRUD operations work
- ✅ Google Meet integration works
- ✅ Tests pass

---

### Task 2.10: Create API AssignmentController
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Task 2.2, Task 2.4

**Description**:
Create API controller for assignment management.

**Subtasks**:
1. Create `app/Http/Controllers/Api/AssignmentController.php`
2. Implement `index()` - list assignments
3. Implement `show()` - assignment details
4. Implement `store()` - create assignment (teachers only)
5. Implement `update()` - update assignment (teachers only)
6. Implement `submit()` - submit assignment (students)
7. Implement `grade()` - grade submission (teachers)
8. Integrate file upload
9. Create Form Requests
10. Return API Resources
11. Write feature tests

**Acceptance Criteria**:
- ✅ Controller created
- ✅ CRUD operations work
- ✅ File upload works
- ✅ Grading works
- ✅ Tests pass

---

### Task 2.11: Create API QuizController
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Task 2.2

**Description**:
Create API controller for quiz management.

**Subtasks**:
1. Create `app/Http/Controllers/Api/QuizController.php`
2. Implement `index()` - list quizzes
3. Implement `show()` - quiz details
4. Implement `store()` - create quiz (teachers only)
5. Implement `update()` - update quiz (teachers only)
6. Implement `attempt()` - start quiz attempt (students)
7. Implement `submit()` - submit quiz attempt (students)
8. Implement `results()` - get quiz results
9. Create Form Requests
10. Return API Resources
11. Write feature tests

**Acceptance Criteria**:
- ✅ Controller created
- ✅ CRUD operations work
- ✅ Quiz taking works
- ✅ Results calculated correctly
- ✅ Tests pass

---

### Task 2.12: Create API CheckoutController
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 4 hours  
**Dependencies**: Task 2.5, Task 2.2

**Description**:
Create API controller for payment processing.

**Subtasks**:
1. Create `app/Http/Controllers/Api/CheckoutController.php`
2. Implement `start()` - create checkout session
3. Implement `success()` - handle successful payment
4. Implement `cancel()` - handle cancelled payment
5. Integrate StripeService
6. Create Order after payment
7. Create Enrollment after payment
8. Create Form Requests
9. Return API Resources
10. Write feature tests

**Acceptance Criteria**:
- ✅ Controller created
- ✅ Checkout works
- ✅ Payment processing works
- ✅ Enrollment created after payment
- ✅ Tests pass

---

### Task 2.13: Create API WebhookController
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 4 hours  
**Dependencies**: Task 2.5

**Description**:
Create API controller for Stripe webhooks.

**Subtasks**:
1. Create `app/Http/Controllers/Api/WebhookController.php`
2. Implement `stripe()` - handle Stripe webhooks
3. Verify webhook signatures
4. Handle different webhook events
5. Use Laravel queues for processing
6. Add proper error handling
7. Write feature tests
8. Test with Stripe webhook testing tool

**Acceptance Criteria**:
- ✅ Controller created
- ✅ Webhooks received
- ✅ Events processed
- ✅ Queue jobs work
- ✅ Tests pass

**Webhook Events to Handle**:
- `checkout.session.completed`
- `payment_intent.succeeded`
- `payment_intent.payment_failed`
- `charge.refunded`

---

### Task 2.14: Create API UserController
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 4 hours  
**Dependencies**: Task 2.2

**Description**:
Create API controller for user profile management.

**Subtasks**:
1. Create `app/Http/Controllers/Api/UserController.php`
2. Implement `profile()` - get user profile
3. Implement `updateProfile()` - update user profile
4. Implement `dashboard()` - get dashboard data
5. Implement `progress()` - get user progress
6. Implement `achievements()` - get achievements
7. Create Form Requests
8. Return API Resources
9. Write feature tests

**Acceptance Criteria**:
- ✅ Controller created
- ✅ Profile operations work
- ✅ Dashboard data returned
- ✅ Tests pass

---

### Task 2.15: Create API AdminController
**Status**: 🟢 Not Started  
**Priority**: Medium  
**Estimated Time**: 6 hours  
**Dependencies**: Task 2.2

**Description**:
Create API controller for admin functionality.

**Subtasks**:
1. Create `app/Http/Controllers/Api/AdminController.php`
2. Implement `dashboard()` - admin dashboard stats
3. Implement `users()` - list users
4. Implement `teachers()` - list teachers
5. Implement `students()` - list students
6. Implement `analytics()` - get analytics
7. Create middleware for admin role
8. Return API Resources
9. Write feature tests

**Acceptance Criteria**:
- ✅ Controller created
- ✅ Admin functions work
- ✅ Authorization works
- ✅ Tests pass

---

### Task 2.16: Create Form Request Classes
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 8 hours  
**Dependencies**: Task 2.7-2.15

**Description**:
Create all Form Request classes for validation.

**Subtasks**:
Create Form Requests for:
1. Auth: LoginRequest, RegisterRequest
2. Course: StoreCourseRequest, UpdateCourseRequest
3. Session: StoreSessionRequest, UpdateSessionRequest
4. Assignment: StoreAssignmentRequest, UpdateAssignmentRequest, SubmitAssignmentRequest
5. Quiz: StoreQuizRequest, UpdateQuizRequest, SubmitQuizRequest
6. User: UpdateProfileRequest
7. Admin: CreateUserRequest, UpdateUserRequest
8. File: UploadFileRequest

For each:
- Define validation rules
- Define custom messages
- Define authorize() method
9. Write tests for validation

**Acceptance Criteria**:
- ✅ All Form Requests created
- ✅ Validation rules defined
- ✅ Validation works
- ✅ Tests pass

---

### Task 2.17: Create API Resource Classes
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 8 hours  
**Dependencies**: Task 2.7-2.15

**Description**:
Create all API Resource classes for consistent JSON responses.

**Subtasks**:
Create Resources for:
1. UserResource
2. CourseResource
3. LessonResource
4. SessionResource
5. EnrollmentResource
6. AssignmentResource
7. AssignmentSubmissionResource
8. QuizResource
9. QuizAttemptResource
10. OrderResource
11. CertificateResource

For each:
- Define toArray() method
- Include relationships
- Format dates/numbers
- Include conditional fields

**Acceptance Criteria**:
- ✅ All Resources created
- ✅ Responses consistent
- ✅ Relationships included
- ✅ Tests pass

---

### Task 2.18: Create Middleware Classes
**Status**: 🟢 Not Started  
**Priority**: Medium  
**Estimated Time**: 4 hours  
**Dependencies**: Task 1.16

**Description**:
Create custom middleware for role-based access control.

**Subtasks**:
1. Create `app/Http/Middleware/EnsureUserIsAdmin.php`
2. Create `app/Http/Middleware/EnsureUserIsTeacher.php`
3. Create `app/Http/Middleware/EnsureUserIsStudent.php`
4. Register middleware in `bootstrap/app.php`
5. Apply middleware to routes
6. Write tests

**Acceptance Criteria**:
- ✅ Middleware created
- ✅ Role checks work
- ✅ Applied to routes
- ✅ Tests pass

---

### Task 2.19: Create Policy Classes
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Task 2.7-2.15

**Description**:
Create Laravel policies for authorization.

**Subtasks**:
Create policies for:
1. CoursePolicy
2. SessionPolicy
3. AssignmentPolicy
4. QuizPolicy
5. UserPolicy

For each:
- Define view(), create(), update(), delete() methods
- Check user roles
- Check ownership
6. Register policies in `app/Providers/AuthServiceProvider.php`
7. Apply policies to controllers
8. Write tests

**Acceptance Criteria**:
- ✅ All policies created
- ✅ Authorization works
- ✅ Applied to controllers
- ✅ Tests pass

---

### Task 2.20: Set Up API Routes
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 4 hours  
**Dependencies**: Task 2.7-2.19

**Description**:
Organize and register all API routes.

**Subtasks**:
1. Create `routes/api.php`
2. Organize routes by feature
3. Add API versioning (`/api/v1/`)
4. Apply authentication middleware
5. Apply role middleware where needed
6. Use route groups for organization
7. Add route names
8. Document routes
9. Test all routes
10. Generate API documentation (optional)

**Acceptance Criteria**:
- ✅ Routes organized
- ✅ All routes working
- ✅ Middleware applied
- ✅ Route names defined
- ✅ Documentation created

**Route Structure**:
```php
Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Resource routes
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('sessions', SessionController::class);
    // ... etc
});
```

---

## Phase 3: Frontend Development

### Task 3.1: Create Base Layout Components
**Status**: ✅ Complete  
**Priority**: Critical  
**Estimated Time**: 6 hours  
**Dependencies**: Task 0.5 (Install and Configure Development Tools) (Install and Configure Development Tools), Task 1.19

**Description**:
Create base layout components with Tailwind CSS.

**Subtasks**:
1. Create `resources/js/components/layout/AppLayout.vue`
2. Create `resources/js/components/layout/AppHeader.vue`
3. Create `resources/js/components/layout/AppSidebar.vue`
4. Create `resources/js/components/layout/AppFooter.vue`
5. Implement navigation menu
6. Implement responsive design (mobile menu)
7. Add user menu dropdown
8. Style with Tailwind CSS
9. Test responsive behavior
10. Test navigation

**Acceptance Criteria**:
- ✅ Layout components created
- ✅ Responsive design works
- ✅ Navigation works
- ✅ Styled with Tailwind

---

### Task 3.2: Create Common UI Components
**Status**: ✅ Complete  
**Priority**: High  
**Estimated Time**: 8 hours  
**Dependencies**: Task 0.5 (Install and Configure Development Tools)

**Description**:
Create reusable UI components with Tailwind CSS.

**Subtasks**:
Create components:
1. `AppButton.vue` - Button with variants (primary, secondary, outline, danger)
2. `AppCard.vue` - Card component
3. `AppModal.vue` - Modal dialog
4. `AppLoading.vue` - Loading spinner
5. `AppAlert.vue` - Alert/notification
6. `AppBadge.vue` - Badge component
7. `AppInput.vue` - Input field
8. `AppTextarea.vue` - Textarea
9. `AppSelect.vue` - Select dropdown
10. `AppFileInput.vue` - File input
11. Style all with Tailwind CSS
12. Make components accessible
13. Write component tests

**Acceptance Criteria**:
- ✅ All components created
- ✅ Styled with Tailwind
- ✅ Accessible
- ✅ Reusable
- ✅ Tests pass

---

### Task 3.3: Create Authentication Pages
**Status**: ✅ Complete  
**Priority**: Critical  
**Estimated Time**: 8 hours  
**Dependencies**: Task 3.2, Task 2.7

**Description**:
Create login and register pages.

**Subtasks**:
1. Create `resources/js/pages/LoginPage.vue`
2. Create login form with Vuelidate
3. Integrate with auth store
4. Add error handling
5. Add loading states
6. Create `resources/js/pages/RegisterPage.vue`
7. Create register form with Vuelidate
8. Add multi-step form if needed
9. Integrate with auth store
10. Add OAuth buttons (Google)
11. Style with Tailwind CSS
12. Test login flow
13. Test register flow

**Acceptance Criteria**:
- ✅ Login page created
- ✅ Register page created
- ✅ Forms validated
- ✅ Authentication works
- ✅ Error handling works
- ✅ Styled with Tailwind

---

### Task 3.4: Create Home Page
**Status**: ✅ Complete  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Task 3.2

**Description**:
Create landing/home page.

**Subtasks**:
1. Create `resources/js/pages/HomePage.vue`
2. Create hero section
3. Create features section
4. Create testimonials section
5. Create CTA section
6. Style with Tailwind CSS
7. Make responsive
8. Add animations (optional)
9. Test on different screen sizes

**Acceptance Criteria**:
- ✅ Home page created
- ✅ Sections implemented
- ✅ Responsive design
- ✅ Styled with Tailwind

---

### Task 3.5: Create User Dashboard Page
**Status**: ✅ Complete  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Task 2.14, Task 3.1

**Description**:
Create user dashboard page.

**Subtasks**:
1. Create `resources/js/pages/Dashboard.vue`
2. Create dashboard layout
3. Display enrolled courses
4. Display upcoming sessions
5. Display recent assignments
6. Display progress stats
7. Create dashboard widgets
8. Integrate with API
9. Style with Tailwind CSS
10. Test dashboard

**Acceptance Criteria**:
- ✅ Dashboard created
- ✅ Data displayed
- ✅ API integration works
- ✅ Styled with Tailwind

---

### Task 3.6: Create Course Listing Page
**Status**: ✅ Complete  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Task 2.8, Task 3.2

**Description**:
Create course listing and detail pages.

**Subtasks**:
1. Create `resources/js/pages/CourseListPage.vue`
2. Create course card component
3. Implement filtering
4. Implement search
5. Implement pagination
6. Create `resources/js/pages/CourseDetailPage.vue`
7. Display course information
8. Display lessons
9. Add enroll button
10. Integrate with API
11. Style with Tailwind CSS
12. Test course pages

**Acceptance Criteria**:
- ✅ Course listing created
- ✅ Course detail created
- ✅ Filtering works
- ✅ Enrollment works
- ✅ Styled with Tailwind

---

### Task 3.7: Create Session Management Pages
**Status**: ✅ Complete  
**Priority**: High  
**Estimated Time**: 8 hours  
**Dependencies**: Task 2.9, Task 3.2

**Description**:
Create session management and viewing pages. Note: Teacher session creation page will be added in Task 3.11.

**Subtasks**:
1. Create `resources/js/pages/SessionListPage.vue`
2. Create `resources/js/pages/SessionDetailPage.vue`
3. Create `resources/js/pages/SessionCreatePage.vue` (for teachers)
4. Display session information
5. Display Google Meet link
6. Create session form (for teachers)
7. Integrate with Google Calendar service
8. Integrate with API
9. Style with Tailwind CSS
10. Test session pages

**Acceptance Criteria**:
- ✅ Session pages created
- ✅ Google Meet integration works
- ✅ CRUD operations work
- ✅ Styled with Tailwind

---

### Task 3.8: Create Assignment Pages
**Status**: ✅ Complete  
**Priority**: High  
**Estimated Time**: 8 hours  
**Dependencies**: Task 2.10, Task 3.2

**Description**:
Create assignment viewing and submission pages. Note: Assignment grading page for teachers will be added in Task 3.11.

**Subtasks**:
1. Create `resources/js/pages/AssignmentDetailPage.vue`
2. Create `resources/js/pages/AssignmentSubmitPage.vue` (for students)
3. Create `resources/js/pages/AssignmentGradePage.vue` (for teachers)
4. Display assignment information
5. Create submission form with file upload
6. Create grading form (for teachers)
7. Integrate with API
8. Integrate file upload
9. Style with Tailwind CSS
10. Test assignment pages

**Acceptance Criteria**:
- ✅ Assignment pages created
- ✅ File upload works
- ✅ Submission works
- ✅ Grading works
- ✅ Styled with Tailwind

---

### Task 3.9: Create Quiz Pages
**Status**: ✅ Complete  
**Priority**: High  
**Estimated Time**: 10 hours  
**Dependencies**: Task 2.11, Task 3.2

**Description**:
Create quiz taking and management pages.

**Subtasks**:
1. Create `resources/js/pages/QuizDetailPage.vue`
2. Create `resources/js/pages/QuizTakePage.vue`
3. Create `resources/js/pages/QuizResultsPage.vue`
4. Create `resources/js/pages/QuizCreatePage.vue` (for teachers)
5. Create quiz question components
6. Implement timer (if time limit)
7. Implement answer validation
8. Implement scoring
9. Integrate with API
10. Style with Tailwind CSS
11. Test quiz pages

**Acceptance Criteria**:
- ✅ Quiz pages created
- ✅ Quiz taking works
- ✅ Timer works
- ✅ Scoring works
- ✅ Styled with Tailwind

---

### Task 3.10: Create Admin Pages
**Status**: 🟢 Not Started  
**Priority**: Medium  
**Estimated Time**: 12 hours  
**Dependencies**: Task 2.15, Task 3.2

**Description**:
Create admin dashboard and management pages.

**Subtasks**:
1. Create `resources/js/pages/admin/AdminDashboard.vue`
2. Create `resources/js/pages/admin/UserManagementPage.vue`
3. Create `resources/js/pages/admin/TeacherManagementPage.vue`
4. Create `resources/js/pages/admin/AnalyticsPage.vue`
5. Create data tables
6. Create user forms
7. Implement user CRUD
8. Display analytics charts (optional - use Chart.js)
9. Integrate with API
10. Style with Tailwind CSS
11. Test admin pages

**Acceptance Criteria**:
- ✅ Admin pages created
- ✅ User management works
- ✅ Analytics displayed
- ✅ Styled with Tailwind

---

### Task 3.11: Create Payment/Checkout Pages
**Status**: ✅ Complete  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Task 2.12, Task 3.2

**Description**:
Create checkout and payment pages for course enrollment.

**Subtasks**:
1. Create `resources/js/pages/CheckoutPage.vue`
2. Create `resources/js/pages/CheckoutSuccessPage.vue`
3. Create `resources/js/pages/CheckoutCancelPage.vue`
4. Integrate with Stripe checkout
5. Handle payment success/cancel flows
6. Style with Tailwind CSS
7. Test payment flow

**Acceptance Criteria**:
- ✅ Checkout page created
- ✅ Payment flow works
- ✅ Success/cancel pages created
- ✅ Styled with Tailwind

---

### Task 3.12: Create Teacher Pages
**Status**: 🟢 Not Started  
**Priority**: Medium  
**Estimated Time**: 10 hours  
**Dependencies**: Task 2.8-2.11, Task 3.2

**Description**:
Create teacher-specific pages.

**Subtasks**:
1. Create `resources/js/pages/teacher/TeacherDashboard.vue`
2. Create `resources/js/pages/teacher/CourseManagementPage.vue`
3. Create `resources/js/pages/teacher/SessionManagementPage.vue`
4. Create `resources/js/pages/teacher/StudentManagementPage.vue`
5. Create course creation/edit forms
6. Create lesson management
7. Create session scheduling
8. Integrate with API
9. Style with Tailwind CSS
10. Test teacher pages

**Acceptance Criteria**:
- ✅ Teacher pages created
- ✅ Course management works
- ✅ Session management works
- ✅ Styled with Tailwind

---

### Task 3.13: Create Pinia Stores
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 8 hours  
**Dependencies**: Task 1.18, Task 2.7-2.15

**Description**:
Create Pinia stores for state management.

**Subtasks**:
Create stores:
1. `course.js` - Course data and operations
2. `session.js` - Session data and operations
3. `assignment.js` - Assignment data and operations
4. `quiz.js` - Quiz data and operations
5. `user.js` - User profile data
6. `notification.js` - Toast notifications
7. For each store:
   - Define state
   - Define actions
   - Define getters
   - Implement API calls
8. Test all stores

**Acceptance Criteria**:
- ✅ All stores created
- ✅ State management works
- ✅ API integration works
- ✅ Tests pass

---

### Task 3.14: Implement File Upload Component
**Status**: ✅ Complete (AppFileInput.vue created)  
**Priority**: High  
**Estimated Time**: 4 hours  
**Dependencies**: Task 2.4, Task 3.2

**Description**:
Create reusable file upload component.

**Subtasks**:
1. Create `resources/js/components/forms/FileUpload.vue`
2. Implement drag-and-drop
3. Implement file preview
4. Implement progress bar
5. Integrate with API
6. Handle errors
7. Style with Tailwind CSS
8. Test file upload

**Acceptance Criteria**:
- ✅ File upload component created
- ✅ Drag-and-drop works
- ✅ Progress display works
- ✅ File upload works
- ✅ Styled with Tailwind

---

### Task 3.15: Implement Toast Notifications
**Status**: 🟢 Not Started  
**Priority**: Medium  
**Estimated Time**: 3 hours  
**Dependencies**: Task 3.12

**Description**:
Create toast notification system.

**Subtasks**:
1. Create `resources/js/components/common/AppToast.vue`
2. Create toast store
3. Implement toast types (success, error, warning, info)
4. Implement auto-dismiss
5. Create toast container
6. Integrate with API errors
7. Style with Tailwind CSS
8. Test notifications

**Acceptance Criteria**:
- ✅ Toast system created
- ✅ All types work
- ✅ Auto-dismiss works
- ✅ Styled with Tailwind

---

### Task 3.16: Implement Loading States
**Status**: ✅ Complete (AppLoading.vue created and integrated)  
**Priority**: Medium  
**Estimated Time**: 3 hours  
**Dependencies**: Task 3.2

**Description**:
Implement loading states throughout the application.

**Subtasks**:
1. Create loading composable
2. Add loading states to API calls
3. Show loading spinners
4. Show skeleton loaders (optional)
5. Implement in all pages
6. Style with Tailwind CSS
7. Test loading states

**Acceptance Criteria**:
- ✅ Loading states implemented
- ✅ Loading indicators shown
- ✅ Styled with Tailwind

---

## Phase 4: Testing & Quality Assurance

### Task 4.1: Write Backend Unit Tests
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 16 hours  
**Dependencies**: Phase 2 complete

**Description**:
Write comprehensive unit tests for models, services, and repositories.

**Subtasks**:
1. Write tests for all models
2. Write tests for all repositories
3. Write tests for all services
4. Achieve minimum 70% code coverage
5. Fix any issues found
6. Run tests in CI/CD (if set up)

**Acceptance Criteria**:
- ✅ All models tested
- ✅ All repositories tested
- ✅ All services tested
- ✅ 70%+ code coverage
- ✅ All tests pass

---

### Task 4.2: Write Backend Feature Tests
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 20 hours  
**Dependencies**: Phase 2 complete

**Description**:
Write feature tests for all API endpoints.

**Subtasks**:
1. Write tests for AuthController
2. Write tests for CourseController
3. Write tests for SessionController
4. Write tests for AssignmentController
5. Write tests for QuizController
6. Write tests for CheckoutController
7. Write tests for WebhookController
8. Write tests for UserController
9. Write tests for AdminController
10. Achieve 100% coverage for controllers
11. Fix any issues found

**Acceptance Criteria**:
- ✅ All controllers tested
- ✅ 100% endpoint coverage
- ✅ All tests pass

---

### Task 4.3: Write Frontend Component Tests
**Status**: 🟢 Not Started  
**Priority**: Medium  
**Estimated Time**: 12 hours  
**Dependencies**: Phase 3 complete

**Description**:
Write tests for Vue components.

**Subtasks**:
1. Set up Vue Test Utils
2. Write tests for common components
3. Write tests for form components
4. Write tests for layout components
5. Achieve minimum 60% coverage
6. Fix any issues found

**Acceptance Criteria**:
- ✅ Components tested
- ✅ 60%+ coverage
- ✅ All tests pass

---

### Task 4.4: Integration Testing
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 8 hours  
**Dependencies**: Phase 2 & 3 complete

**Description**:
Test integration between frontend and backend.

**Subtasks**:
1. Test authentication flow
2. Test course enrollment flow
3. Test session creation flow
4. Test assignment submission flow
5. Test quiz taking flow
6. Test payment flow
7. Fix any issues found

**Acceptance Criteria**:
- ✅ All flows tested
- ✅ Integration works
- ✅ Issues fixed

---

## Phase 5: Performance & Optimization

### Task 5.1: Backend Performance Optimization
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 8 hours  
**Dependencies**: Phase 2 complete

**Description**:
Optimize backend performance.

**Subtasks**:
1. Implement eager loading for relationships
2. Add database indexes
3. Implement query caching
4. Implement API response caching
5. Optimize N+1 queries
6. Add queue workers for background jobs
7. Profile and optimize slow queries
8. Test performance improvements

**Acceptance Criteria**:
- ✅ Queries optimized
- ✅ Caching implemented
- ✅ Performance improved
- ✅ Response times acceptable

---

### Task 5.2: Frontend Performance Optimization
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 6 hours  
**Dependencies**: Phase 3 complete

**Description**:
Optimize frontend performance.

**Subtasks**:
1. Implement code splitting
2. Implement lazy loading for routes
3. Implement lazy loading for images
4. Optimize bundle size
5. Implement API request debouncing
6. Implement virtual scrolling for long lists (if needed)
7. Test performance improvements

**Acceptance Criteria**:
- ✅ Bundle size optimized
- ✅ Loading times improved
- ✅ Performance acceptable

---

## Phase 6: Documentation

### Task 6.1: API Documentation
**Status**: 🟢 Not Started  
**Priority**: Medium  
**Estimated Time**: 8 hours  
**Dependencies**: Phase 2 complete

**Description**:
Create comprehensive API documentation.

**Subtasks**:
1. Document all API endpoints
2. Document request/response formats
3. Document authentication
4. Document error codes
5. Create Postman collection (optional)
6. Use API documentation tool (Laravel API Docs or Scribe)

**Acceptance Criteria**:
- ✅ API documented
- ✅ Endpoints documented
- ✅ Examples provided

---

### Task 6.2: Frontend Documentation
**Status**: 🟢 Not Started  
**Priority**: Low  
**Estimated Time**: 4 hours  
**Dependencies**: Phase 3 complete

**Description**:
Document frontend components and structure.

**Subtasks**:
1. Document component structure
2. Document store structure
3. Document routing structure
4. Document composables
5. Create component usage examples

**Acceptance Criteria**:
- ✅ Frontend documented
- ✅ Structure documented

---

## Phase 7: Deployment Preparation

### Task 7.1: Production Environment Configuration
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 4 hours  
**Dependencies**: All phases complete

**Description**:
Configure application for production.

**Subtasks**:
1. Update `.env` for production
2. Set `APP_DEBUG=false`
3. Set `APP_ENV=production`
4. Configure production database
5. Configure production cache (Redis)
6. Configure production queue
7. Set up SSL certificates
8. Configure CORS for production domain
9. Test production configuration

**Acceptance Criteria**:
- ✅ Production config set
- ✅ Debug mode off
- ✅ SSL configured
- ✅ CORS configured

---

### Task 7.2: Build Frontend Assets
**Status**: 🟢 Not Started  
**Priority**: High  
**Estimated Time**: 2 hours  
**Dependencies**: Phase 3 complete

**Description**:
Build production frontend assets.

**Subtasks**:
1. Run `npm run build`
2. Verify assets generated
3. Test production build
4. Optimize assets
5. Test in production mode

**Acceptance Criteria**:
- ✅ Assets built
- ✅ Production build works
- ✅ Assets optimized

---

### Task 7.3: Database Migration to Production
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 4 hours  
**Dependencies**: Phase 1 complete

**Description**:
Migrate database to production.

**Subtasks**:
1. Backup existing database
2. Run migrations on production
3. Run seeders if needed
4. Verify database structure
5. Test database connections

**Acceptance Criteria**:
- ✅ Database migrated
- ✅ Data preserved
- ✅ All tables created

---

### Task 7.4: Deploy Application
**Status**: 🟢 Not Started  
**Priority**: Critical  
**Estimated Time**: 4 hours  
**Dependencies**: Task 7.1-7.3

**Description**:
Deploy application to production server.

**Subtasks**:
1. Deploy Laravel application
2. Deploy frontend assets
3. Set up queue workers
4. Set up cron jobs (if needed)
5. Configure web server (Nginx/Apache)
6. Test deployment
7. Monitor for errors

**Acceptance Criteria**:
- ✅ Application deployed
- ✅ All services running
- ✅ No errors
- ✅ Application accessible

---

## Task Dependencies Diagram

```
Phase 0 (Local Dev Setup)
├── 0.1 → 0.2 → 0.3
├── 0.2 → 0.4, 0.5, 0.6, 0.7
└── 0.6 → 0.7

Phase 1 (Foundation)
├── 1.1 → 1.2-1.12
├── 1.2 → 1.13
├── 1.13 → 1.14 → 1.15
├── 1.13, 0.7 → 1.16
└── 1.16 → 1.17 → 1.18 → 1.19

Phase 2 (Backend)
├── 1.15 → 2.1 → 2.2
├── 1.15 → 2.3, 2.4, 2.5
└── 2.2 → 2.7-2.15 → 2.16-2.20

Phase 3 (Frontend)
├── 0.5, 1.19 → 3.1
├── 0.5 → 3.2
├── 3.2, 2.7 → 3.3
└── 2.7-2.15 → 3.4-3.11

Phase 4-7
└── (All previous phases)
```

---

## Time Tracking

**Total Estimated Time**: ~450-500 hours (11-12 weeks full-time)

**By Phase**:
- Phase 0: ~12 hours
- Phase 1: ~120 hours
- Phase 2: ~150 hours
- Phase 3: ~100 hours
- Phase 4: ~56 hours
- Phase 5: ~14 hours
- Phase 6: ~12 hours
- Phase 7: ~14 hours

---

## Notes

1. **Parallel Work**: Some tasks can be worked on in parallel (e.g., backend and frontend after Phase 1)
2. **Testing**: Write tests as you go, not at the end
3. **Code Review**: Review code after each major task
4. **Documentation**: Document as you code
5. **Git Workflow**: Use feature branches and merge requests
6. **Regular Backups**: Backup database regularly during migration

---

**Document End**

