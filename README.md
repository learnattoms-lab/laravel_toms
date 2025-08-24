# TOMS Music School - Learning Management System

A comprehensive, modern Learning Management System (LMS) built with Symfony 6.3, designed specifically for music education with integrated Google Meet, Azure Blob Storage, and Stripe payment processing.

## 🎵 Features

### Core LMS Functionality
- **User Management**: Multi-role system (Students, Teachers, Admins)
- **Course Management**: Create, organize, and deliver music courses
- **Lesson System**: Structured learning with multimedia content
- **Progress Tracking**: Monitor student advancement and achievements
- **Assignment System**: Submit, grade, and provide feedback
- **Quiz System**: Interactive assessments with multiple question types
- **Notes & Comments**: Collaborative learning features

### Advanced Integrations
- **Google Meet Integration**: Automatic session scheduling with video conferencing
- **Azure Blob Storage**: Scalable file storage for course materials
- **Stripe Payments**: Secure payment processing for course enrollments
- **OAuth Authentication**: Google OAuth for seamless integration

### Technical Features
- **Responsive Design**: Modern UI with Bootstrap 5.3
- **Real-time Updates**: Live session management and notifications
- **File Management**: Advanced file handling with metadata tracking
- **API-First Architecture**: RESTful endpoints for mobile apps
- **Security**: Role-based access control and secure authentication

## 🚀 Quick Start

### Prerequisites
- Docker and Docker Compose
- PHP 8.2+
- Composer
- MySQL 8.0

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd toms
   ```

2. **Start the Docker environment**
   ```bash
   docker-compose up -d
   ```

3. **Install dependencies**
   ```bash
   docker-compose exec php composer install
   ```

4. **Configure environment variables**
   ```bash
   cp env.example .env
   # Edit .env with your configuration
   ```

5. **Run database migrations**
   ```bash
   docker-compose exec php php bin/console doctrine:migrations:migrate
   ```

6. **Access the application**
   - Web: http://localhost:8080
   - Database: http://localhost:8081 (Adminer)

## ⚙️ Configuration

### Environment Variables

#### Google OAuth
```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=${APP_URL}/oauth/google/callback
GOOGLE_DEFAULT_CALENDAR_ID=primary
```

#### Azure Blob Storage
```env
AZURE_BLOB_CONNECTION_STRING=DefaultEndpointsProtocol=https;AccountName=...
AZURE_BLOB_CONTAINER=toms-lms
AZURE_BLOB_PUBLIC_BASE=https://your_account.blob.core.windows.net/toms-lms
```

#### Stripe Payments
```env
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

## 🏗️ Architecture

### Entity Structure
- **User**: Central user entity with role-based permissions
- **Course**: Music courses with lessons and materials
- **Lesson**: Individual learning units with content
- **Session**: Scheduled live sessions with Google Meet integration
- **Assignment**: Coursework with submission tracking
- **Quiz**: Interactive assessments with scoring
- **Enrollment**: Student-course relationships with progress tracking
- **Certificate**: Course completion certificates

### Service Layer
- **GoogleCalendarService**: Handles Google Meet session creation
- **AzureBlobStorageService**: Manages file uploads and storage
- **StripeService**: Processes payments and subscriptions
- **OAuthCredentialService**: Manages OAuth token refresh

### Security
- **Role-Based Access Control**: Granular permissions per role
- **OAuth Integration**: Secure third-party authentication
- **API Security**: JWT tokens and request validation
- **File Security**: Secure file access with temporary URLs

## 📱 User Roles

### Students
- Enroll in courses
- Access lesson content
- Submit assignments
- Take quizzes
- Track progress
- Join live sessions

### Teachers
- Create and manage courses
- Schedule live sessions
- Grade assignments
- Monitor student progress
- Generate certificates

### Administrators
- Manage users and roles
- Oversee system operations
- Monitor API health
- Manage system settings

## 🔌 API Endpoints

### Authentication
- `POST /api/auth/login` - User authentication
- `POST /api/auth/refresh` - Token refresh
- `GET /api/auth/profile` - User profile

### Courses
- `GET /api/courses` - List available courses
- `POST /api/courses` - Create new course (teachers only)
- `GET /api/courses/{id}` - Course details
- `POST /api/courses/{id}/enroll` - Enroll in course

### Sessions
- `GET /api/sessions` - List sessions
- `POST /api/sessions` - Create session (teachers only)
- `GET /api/sessions/{id}/join` - Get session join link

### Files
- `POST /api/files/upload` - Upload file
- `GET /api/files/{id}` - Download file
- `DELETE /api/files/{id}` - Delete file

## 🎯 Use Cases

### For Music Schools
- **Online Course Delivery**: Reach students globally
- **Live Instruction**: Real-time video sessions
- **Progress Tracking**: Monitor student development
- **Resource Management**: Centralized content storage

### For Teachers
- **Course Creation**: Build structured music curricula
- **Student Management**: Track individual progress
- **Live Sessions**: Conduct virtual music lessons
- **Assessment Tools**: Evaluate student performance

### For Students
- **Flexible Learning**: Study at their own pace
- **Interactive Content**: Engage with multimedia materials
- **Live Practice**: Participate in real-time sessions
- **Progress Monitoring**: Track learning achievements

## 🛠️ Development

### Running Tests
```bash
docker-compose exec php php bin/phpunit
```

### Code Quality
```bash
docker-compose exec php composer cs-fix
docker-compose exec php composer phpstan
```

### Database Management
```bash
# Create migration
docker-compose exec php php bin/console doctrine:migrations:diff

# Run migrations
docker-compose exec php php bin/console doctrine:migrations:migrate

# Reset database
docker-compose exec php php bin/console doctrine:database:drop --force
docker-compose exec php php bin/console doctrine:database:create
docker-compose exec php php bin/console doctrine:migrations:migrate
```

## 📊 Monitoring & Health Checks

### API Health Endpoints
- `/admin/api/health` - Overall system health
- `/admin/api/health/google` - Google API status
- `/admin/api/health/azure` - Azure Blob Storage status
- `/admin/api/health/stripe` - Stripe API status

### Logging
- Application logs: `var/log/`
- API access logs
- Error tracking and monitoring

## 🔒 Security Considerations

- **OAuth Token Management**: Secure storage and refresh
- **File Access Control**: Role-based file permissions
- **API Rate Limiting**: Prevent abuse and DDoS
- **Data Encryption**: Sensitive data encryption at rest
- **Audit Logging**: Track all system activities

## 🚀 Deployment

### Production Checklist
- [ ] Environment variables configured
- [ ] SSL certificates installed
- [ ] Database backups configured
- [ ] Monitoring and alerting set up
- [ ] Performance optimization applied
- [ ] Security audit completed

### Scaling Considerations
- **Load Balancing**: Multiple application instances
- **Database Clustering**: Read replicas for performance
- **CDN Integration**: Global content delivery
- **Caching Strategy**: Redis for session and data caching

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Contact the development team
- Check the documentation

## 🔮 Roadmap

### Upcoming Features
- **Mobile App**: Native iOS and Android applications
- **AI Integration**: Smart content recommendations
- **Advanced Analytics**: Detailed learning insights
- **Multi-language Support**: Internationalization
- **Advanced Assessment**: AI-powered grading
- **Social Learning**: Student collaboration features

---

**Built with ❤️ for music education**
