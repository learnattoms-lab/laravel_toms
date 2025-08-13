# Toms Music School - Learning Management System (LMS)

A comprehensive, fully functional Learning Management System for 1-on-1 online music lessons, built with Symfony and designed to handle scheduling, payments, teacher-student assignment, progress tracking, and more.

## 🎵 Features

### Core Functionality
- **Teacher Availability & Booking Engine**: Teachers input weekly time slots, students book lessons
- **Attendance & Payroll System**: Automatic tracking, substitute assignment, and payment processing
- **Student Reschedule Rules**: One free reschedule per month with smart scheduling
- **Progress Tracker**: Detailed lesson summaries and preparation guidelines
- **Content & Assignment Upload**: Support for PDFs, videos, images, and YouTube links
- **Payment & Plan System**: Free demo, monthly, and yearly subscription options

### User Roles
- **Students**: Book lessons, track progress, submit assignments
- **Teachers**: Set availability, conduct lessons, upload content
- **Administrators**: Manage users, monitor system, generate reports

### Technical Features
- **Responsive Design**: Mobile-first approach inspired by Simplilearn
- **Docker Support**: Complete development environment setup
- **MySQL Database**: Robust data management with proper relationships
- **Modern UI/UX**: Beautiful, intuitive interface with smooth animations

## 🚀 Quick Start

### Prerequisites
- Docker and Docker Compose
- Git

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

4. **Set up the database**
   ```bash
   # The database will be automatically created with the initial schema
   # Access MySQL at localhost:3306
   # Database: toms_lms
   # Username: toms_user
   # Password: toms_password
   ```

5. **Access the application**
   - Main site: http://localhost:8080
   - Admin: http://localhost:8080/admin
   - Teacher: http://localhost:8080/teacher
   - Student: http://localhost:8080/student

## 🏗️ Project Structure

```
toms/
├── docker/                 # Docker configuration files
│   ├── mysql/             # MySQL setup and initialization
│   ├── nginx/             # Nginx configuration
│   └── php/               # PHP Dockerfile and configuration
├── src/                   # Symfony source code
│   └── Controller/        # Application controllers
├── templates/             # Twig templates
│   ├── home/             # Landing page templates
│   ├── teacher/          # Teacher-related templates
│   ├── plan/             # Subscription plan templates
│   └── base.html.twig    # Base template
├── config/                # Symfony configuration
├── docker-compose.yml     # Docker services configuration
├── composer.json          # PHP dependencies
└── README.md             # This file
```

## 🎯 Key Components

### 1. Teacher Availability & Booking Engine
- Teachers input weekly available time slots with timezone support
- Calendar UI showing bookable slots
- Students choose between free demo (15 min) or paid plans
- Automatic teacher assignment and confirmation system
- Google Calendar integration (planned)

### 2. Attendance & Payroll System
- Every lesson = 60 minutes, 1-on-1 format
- Automatic substitute teacher assignment for missed classes
- Students never rescheduled from the system side
- Teachers paid only for hours actually taught
- Monthly payroll summaries with export functionality

### 3. Student Reschedule Rules
- One free reschedule per month per student
- Additional reschedules marked as absence (non-refundable)
- Admin/teacher view for tracking reschedule usage
- Quick alternative slot suggestions

### 4. Progress Tracker
- After every class, teachers input:
  - "What we did today" summary
  - "What to prepare for next week"
- Visual progress tracking per student
- Seamless teacher handovers
- New/substitute teachers can instantly understand student progress

### 5. Content & Assignment Upload
- Teachers can upload: PDFs, DOCs, images, reference videos
- YouTube link support for additional content
- Assignments auto-labeled by class/week
- Optional expiry deadlines
- Student submission system for practice videos/audio

### 6. Payment & Plan System
- **Free Demo**: One-time, 15-minute booking
- **Monthly Plan**: 4 sessions/month at $199.99
- **Yearly Plan**: 48 sessions/year at $1,999.99 (Save $400)
- Auto-renewal reminders and billing history
- Admin dashboard for plan management

## 🎨 Design & UI

### Responsive Design
- Mobile-first approach
- Bootstrap 5 framework
- Custom CSS with CSS variables
- Smooth animations and transitions
- Font Awesome icons throughout

### Color Scheme
- **Primary**: Blue (#2563eb)
- **Secondary**: Purple (#7c3aed)
- **Accent**: Orange (#f59e0b)
- **Text**: Dark gray (#1f2937)
- **Background**: Light gray (#f9fafb)

### Typography
- **Font Family**: Inter (Google Fonts)
- **Weights**: 300, 400, 500, 600, 700
- **Responsive**: Scales appropriately on all devices

## 🔧 Development

### Docker Services
- **PHP 8.2**: Symfony application server
- **MySQL 8.0**: Database with pre-configured schema
- **Nginx**: Web server with optimized configuration

### Database Schema
The system includes comprehensive tables for:
- Users (students, teachers, admins)
- Teacher availability and profiles
- Subscription plans and student subscriptions
- Bookings and lessons
- Content and assignments
- Student submissions
- Payroll tracking

### Environment Configuration
Create a `.env` file with:
```env
DATABASE_URL="mysql://toms_user:toms_password@mysql:3306/toms_lms?serverVersion=8.0&charset=utf8mb4"
APP_SECRET=your-secret-key-here
```

## 📱 Responsive Features

### Mobile Optimization
- Touch-friendly navigation
- Optimized button sizes
- Responsive grid layouts
- Mobile-optimized forms
- Fast loading on mobile networks

### Cross-Device Compatibility
- Desktop: Full feature set
- Tablet: Optimized layouts
- Mobile: Streamlined experience
- All devices: Consistent branding

## 🚀 Deployment

### Production Considerations
- Update environment variables
- Configure SSL certificates
- Set up proper database backups
- Implement monitoring and logging
- Configure CDN for static assets

### Scaling
- Load balancer for multiple PHP instances
- Database replication for high availability
- Redis for session management
- CDN for global content delivery

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is proprietary software. All rights reserved.

## 🆘 Support

For support and questions:
- Email: info@tomsschool.com
- Phone: +1 (555) 123-4567
- Address: 123 Music Street, NY 10001

## 🔮 Future Enhancements

### Planned Features
- Google Calendar integration
- Google Meet auto-generation
- Mobile app (white-labeled)
- Advanced analytics dashboard
- Gamification and badges
- Push/email reminders
- Student journaling interface
- Advanced reporting system

### Technical Improvements
- API endpoints for mobile apps
- WebSocket support for real-time features
- Advanced caching strategies
- Performance optimization
- Security enhancements

---

**Built with ❤️ for music lovers everywhere**
