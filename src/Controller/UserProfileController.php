<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
class UserProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {}

    #[Route('/dashboard', name: 'user_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function dashboard(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Please login to access the dashboard.');
            return $this->redirectToRoute('login');
        }
        
        // Mock data for demonstration
        $currentCourse = [
            'title' => 'Piano Fundamentals',
            'description' => 'Learn the basics of piano playing',
            'lessonsCompleted' => 3,
            'totalLessons' => 12,
            'progress' => 25
        ];
        
        $upcomingSessions = [
            [
                'teacher' => 'Sarah Johnson',
                'instrument' => 'Piano',
                'topic' => 'Scales and Finger Exercises',
                'date' => 'Today',
                'time' => '2:00 PM - 3:00 PM',
                'isToday' => true
            ]
        ];
        
        $recentActivity = [
            [
                'type' => 'lesson_completed',
                'title' => 'Completed Piano Lesson #3',
                'description' => 'You\'ve mastered the C major scale!',
                'time' => '2 hours ago',
                'icon' => 'check-circle',
                'color' => 'success'
            ],
            [
                'type' => 'practice',
                'title' => 'Practice Session',
                'description' => '45 minutes of focused practice',
                'time' => 'Yesterday',
                'icon' => 'clock',
                'color' => 'primary'
            ],
            [
                'type' => 'achievement',
                'title' => 'Achievement Unlocked!',
                'description' => 'First Steps - Complete your first lesson',
                'time' => '2 days ago',
                'icon' => 'trophy',
                'color' => 'warning'
            ]
        ];
        
        $todayGoal = [
            'title' => 'Practice C Major Scale',
            'status' => 'in_progress',
            'completed' => false
        ];
        
        return $this->render('user_profile/dashboard.html.twig', [
            'user' => $user,
            'currentCourse' => $currentCourse,
            'upcomingSessions' => $upcomingSessions,
            'recentActivity' => $recentActivity,
            'todayGoal' => $todayGoal
        ]);
    }

    #[Route('/profile', name: 'user_profile')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        return $this->render('user_profile/index.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/profile/edit', name: 'user_profile_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($request->isMethod('POST')) {
            $user->setFirstName($request->request->get('firstName'));
            $user->setLastName($request->request->get('lastName'));
            $user->setPhone($request->request->get('phone'));
            $user->setInstrument($request->request->get('instrument'));
            $user->setSkillLevel($request->request->get('skillLevel'));
            $user->setBio($request->request->get('bio'));
            $user->setCity($request->request->get('city'));
            $user->setCountry($request->request->get('country'));
            $user->setTimezone($request->request->get('timezone'));
            
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('user_profile');
        }
        
        return $this->render('user_profile/edit.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/achievements', name: 'user_achievements')]
    #[IsGranted('ROLE_USER')]
    public function achievements(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        return $this->render('user_profile/achievements.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/progress', name: 'user_progress')]
    #[IsGranted('ROLE_USER')]
    public function progress(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        return $this->render('user_profile/progress.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/settings', name: 'user_settings')]
    #[IsGranted('ROLE_USER')]
    public function settings(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        return $this->render('user_profile/settings.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/learning', name: 'user_learning')]
    #[IsGranted('ROLE_USER')]
    public function learning(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Mock data for demonstration
        $activeClasses = [
            [
                'id' => 1,
                'title' => 'Piano Fundamentals',
                'description' => 'Learn the basics of piano playing with expert guidance',
                'startDate' => new \DateTime('2024-12-01'),
                'endDate' => new \DateTime('2024-12-31'),
                'startTime' => '10:00 AM',
                'endTime' => '11:00 AM',
                'duration' => '1 Hour',
                'totalSessions' => 6,
                'sessionsAttended' => 4,
                'attendancePercentage' => 67,
                'minSessionsRequired' => 5,
                'sessions' => [
                    [
                        'id' => 1,
                        'date' => new \DateTime('2024-12-01'),
                        'status' => 'Present',
                        'hasAttachments' => false
                    ],
                    [
                        'id' => 2,
                        'date' => new \DateTime('2024-12-08'),
                        'status' => 'Present',
                        'hasAttachments' => true
                    ],
                    [
                        'id' => 3,
                        'date' => new \DateTime('2024-12-15'),
                        'status' => 'Missed',
                        'hasAttachments' => false
                    ],
                    [
                        'id' => 4,
                        'date' => new \DateTime('2024-12-22'),
                        'status' => 'Present',
                        'hasAttachments' => true
                    ],
                    [
                        'id' => 5,
                        'date' => new \DateTime('2024-12-29'),
                        'status' => 'Upcoming',
                        'hasAttachments' => false
                    ],
                    [
                        'id' => 6,
                        'date' => new \DateTime('2024-12-31'),
                        'status' => 'Upcoming',
                        'hasAttachments' => false
                    ]
                ]
            ]
        ];

        $completedClasses = [
            [
                'id' => 2,
                'title' => 'Guitar Basics',
                'description' => 'Introduction to guitar playing techniques',
                'startDate' => new \DateTime('2024-11-01'),
                'endDate' => new \DateTime('2024-11-30'),
                'attendancePercentage' => 100,
                'sessionsAttended' => 4,
                'totalSessions' => 4,
                'grade' => 'A+'
            ]
        ];
        
        return $this->render('user_profile/learning.html.twig', [
            'user' => $user,
            'activeClasses' => $activeClasses,
            'completedClasses' => $completedClasses
        ]);
    }

    #[Route('/session/{classId}', name: 'user_session')]
    #[IsGranted('ROLE_USER')]
    public function sessionDetail(int $classId): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Mock data for demonstration
        $sessions = [
            [
                'id' => 1,
                'date' => new \DateTime('2024-12-01'),
                'status' => 'Present',
                'hasAttachments' => false
            ],
            [
                'id' => 2,
                'date' => new \DateTime('2024-12-08'),
                'status' => 'Present',
                'hasAttachments' => true
            ],
            [
                'id' => 3,
                'date' => new \DateTime('2024-12-15'),
                'status' => 'Missed',
                'hasAttachments' => false
            ],
            [
                'id' => 4,
                'date' => new \DateTime('2024-12-22'),
                'status' => 'Present',
                'hasAttachments' => true
            ],
            [
                'id' => 5,
                'date' => new \DateTime('2024-12-29'),
                'status' => 'Upcoming',
                'hasAttachments' => false
            ],
            [
                'id' => 6,
                'date' => new \DateTime('2024-12-31'),
                'status' => 'Upcoming',
                'hasAttachments' => false
            ]
        ];

        $selectedSession = [
            'id' => 2,
            'title' => 'Piano Fundamentals - Session 2',
            'attendanceStatus' => 'Present',
            'attendancePercentage' => 97.08,
            'attendedDuration' => '3Hr 51min',
            'totalDuration' => '4Hr 0min',
            'hasRecording' => true,
            'materials' => [
                [
                    'name' => 'Piano_Sheet_Music_08DecSession.pdf',
                    'type' => 'pdf',
                    'updatedOn' => new \DateTime('2024-12-08'),
                    'downloadUrl' => '#'
                ],
                [
                    'name' => 'Practice_Exercises_08Dec.txt',
                    'type' => 'txt',
                    'updatedOn' => new \DateTime('2024-12-08'),
                    'downloadUrl' => '#'
                ]
            ],
            'assignments' => [
                [
                    'title' => 'Practice C Major Scale',
                    'description' => 'Practice the C major scale with both hands for 30 minutes daily. Focus on proper finger positioning and smooth transitions.',
                    'dueDate' => new \DateTime('2024-12-15'),
                    'dueTime' => '11:00 PM',
                    'points' => 25
                ]
            ],
            'comments' => [
                [
                    'author' => 'Sarah Johnson (Teacher)',
                    'content' => 'Great progress on the C major scale! Your finger positioning is improving. Keep practicing the transitions between notes.',
                    'timestamp' => new \DateTime('2024-12-08 11:30:00')
                ],
                [
                    'author' => 'Alex Smith (Student)',
                    'content' => 'Thank you! I\'ve been practicing daily. The scale is getting easier to play.',
                    'timestamp' => new \DateTime('2024-12-09 14:20:00')
                ]
            ]
        ];
        
        return $this->render('user_profile/session_detail.html.twig', [
            'user' => $user,
            'sessions' => $sessions,
            'selectedSession' => $selectedSession
        ]);
    }
}
