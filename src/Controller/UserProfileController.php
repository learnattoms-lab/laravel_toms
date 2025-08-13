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
}
