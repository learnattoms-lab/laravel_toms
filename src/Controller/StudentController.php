<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student')]
#[IsGranted('ROLE_STUDENT')]
class StudentController extends AbstractController
{
    #[Route('/dashboard', name: 'student_dashboard')]
    public function dashboard(): Response
    {
        // Mock data for demonstration - replace with real database queries
        $enrolledCourses = [
            [
                'id' => 1,
                'name' => 'Piano Fundamentals',
                'teacher' => 'Sarah Johnson',
                'instrument' => 'Piano',
                'startDate' => '2025-01-15',
                'endDate' => '2025-03-15',
                'totalSessions' => 8,
                'completedSessions' => 3,
                'attendance' => 100,
                'nextSession' => '2025-01-22 14:00',
                'status' => 'active',
                'progress' => 37.5,
                'image' => 'https://images.unsplash.com/photo-1520523839897-bd0b52f945a0?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'
            ],
            [
                'id' => 2,
                'name' => 'Guitar Basics',
                'teacher' => 'Mike Rodriguez',
                'instrument' => 'Guitar',
                'startDate' => '2025-01-20',
                'endDate' => '2025-03-20',
                'totalSessions' => 6,
                'completedSessions' => 1,
                'attendance' => 100,
                'nextSession' => '2025-01-27 16:00',
                'status' => 'active',
                'progress' => 16.7,
                'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'
            ],
            [
                'id' => 3,
                'name' => 'Vocal Training',
                'teacher' => 'Lisa Chen',
                'instrument' => 'Vocals',
                'startDate' => '2025-02-01',
                'endDate' => '2025-04-01',
                'totalSessions' => 4,
                'completedSessions' => 0,
                'attendance' => 0,
                'nextSession' => '2025-02-01 15:00',
                'status' => 'upcoming',
                'progress' => 0,
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'
            ]
        ];

        return $this->render('student/dashboard.html.twig', [
            'enrolledCourses' => $enrolledCourses,
            'activeTab' => 'all-courses'
        ]);
    }

    #[Route('/course/{id}', name: 'student_course_detail')]
    public function courseDetail(int $id): Response
    {
        // Mock course data - replace with real database queries
        $course = [
            'id' => $id,
            'name' => 'Piano Fundamentals - Database Management using SQL',
            'teacher' => 'Sarah Johnson',
            'instrument' => 'Piano',
            'startDate' => '2025-01-15',
            'endDate' => '2025-03-15',
            'startTime' => '14:00',
            'endTime' => '14:45',
            'timezone' => 'CDT',
            'totalSessions' => 8,
            'sessionDuration' => 45,
            'attendance' => 100,
            'attendedSessions' => 8,
            'image' => 'https://images.unsplash.com/photo-1520523839897-bd0b52f945a0?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'
        ];

        // Mock sessions data
        $sessions = [
            [
                'id' => 1,
                'date' => '2025-01-15',
                'time' => '14:00',
                'status' => 'completed',
                'attendance' => 'present',
                'recording' => 'https://example.com/recording1.mp4',
                'materials' => ['lesson_plan.pdf', 'practice_sheet.pdf'],
                'notes' => 'Great progress on basic scales!'
            ],
            [
                'id' => 2,
                'date' => '2025-01-22',
                'time' => '14:00',
                'status' => 'completed',
                'attendance' => 'present',
                'recording' => 'https://example.com/recording2.mp4',
                'materials' => ['chord_progression.pdf'],
                'notes' => 'Excellent chord transitions!'
            ],
            [
                'id' => 3,
                'date' => '2025-01-29',
                'time' => '14:00',
                'status' => 'completed',
                'attendance' => 'present',
                'recording' => 'https://example.com/recording3.mp4',
                'materials' => ['finger_exercises.pdf'],
                'notes' => 'Keep practicing finger independence'
            ],
            [
                'id' => 4,
                'date' => '2025-02-05',
                'time' => '14:00',
                'status' => 'upcoming',
                'attendance' => null,
                'recording' => null,
                'materials' => ['next_lesson_preview.pdf'],
                'notes' => 'Prepare for intermediate techniques'
            ],
            [
                'id' => 5,
                'date' => '2025-02-12',
                'time' => '14:00',
                'status' => 'upcoming',
                'attendance' => null,
                'recording' => null,
                'materials' => [],
                'notes' => null
            ],
            [
                'id' => 6,
                'date' => '2025-02-19',
                'time' => '14:00',
                'status' => 'upcoming',
                'attendance' => null,
                'recording' => null,
                'materials' => [],
                'notes' => null
            ],
            [
                'id' => 7,
                'date' => '2025-02-26',
                'time' => '14:00',
                'status' => 'upcoming',
                'attendance' => null,
                'recording' => null,
                'materials' => [],
                'notes' => null
            ],
            [
                'id' => 8,
                'date' => '2025-03-05',
                'time' => '14:00',
                'status' => 'upcoming',
                'attendance' => null,
                'recording' => null,
                'materials' => [],
                'notes' => null
            ]
        ];

        return $this->render('student/course_detail.html.twig', [
            'course' => $course,
            'sessions' => $sessions,
            'activeTab' => 'course-' . $id
        ]);
    }

    #[Route('/session/{id}/join', name: 'student_join_session')]
    public function joinSession(int $id): Response
    {
        // Mock session joining logic - replace with real implementation
        $this->addFlash('success', 'Successfully joined session!');
        
        return $this->redirectToRoute('student_dashboard');
    }

    #[Route('/session/{id}/recording', name: 'student_view_recording')]
    public function viewRecording(int $id): Response
    {
        // Mock recording viewing logic - replace with real implementation
        return $this->render('student/recording.html.twig', [
            'sessionId' => $id
        ]);
    }

    #[Route('/session/{id}/materials', name: 'student_download_materials')]
    public function downloadMaterials(int $id): Response
    {
        // Mock materials download logic - replace with real implementation
        $this->addFlash('success', 'Materials downloaded successfully!');
        
        return $this->redirectToRoute('student_dashboard');
    }
}
