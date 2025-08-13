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

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {}

    #[Route('', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        $stats = $this->userRepository->getUserStatistics();
        $recentUsers = $this->userRepository->findRecentUsers(5);
        $topTeachers = $this->userRepository->findTopTeachers(5);
        
        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'topTeachers' => $topTeachers
        ]);
    }

    #[Route('/teachers', name: 'admin_teachers')]
    public function teachers(): Response
    {
        $teachers = $this->userRepository->findTeachersWithStats();
        
        return $this->render('admin/teachers.html.twig', [
            'teachers' => $teachers
        ]);
    }

    #[Route('/teachers/add', name: 'admin_add_teacher', methods: ['GET', 'POST'])]
    public function addTeacher(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');
            $instrument = $request->request->get('instrument');
            $hourlyRate = $request->request->get('hourlyRate');
            
            // Check if user already exists
            $existingUser = $this->userRepository->findByEmail($email);
            
            if ($existingUser) {
                // Update existing user to be a teacher
                $existingUser->setIsTeacher(true);
                $existingUser->setFirstName($firstName);
                $existingUser->setLastName($lastName);
                $existingUser->setInstrument($instrument);
                $existingUser->setHourlyRate((float) $hourlyRate);
                
                if (!in_array('ROLE_TEACHER', $existingUser->getRoles())) {
                    $roles = $existingUser->getRoles();
                    $roles[] = 'ROLE_TEACHER';
                    $existingUser->setRoles($roles);
                }
            } else {
                // Create new teacher user
                $teacher = new User();
                $teacher->setEmail($email);
                $teacher->setFirstName($firstName);
                $teacher->setLastName($lastName);
                $teacher->setInstrument($instrument);
                $teacher->setHourlyRate((float) $hourlyRate);
                $teacher->setIsTeacher(true);
                $teacher->setRoles(['ROLE_USER', 'ROLE_TEACHER']);
                $teacher->setIsActive(true);
                
                $this->entityManager->persist($teacher);
            }
            
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Teacher added successfully!');
            return $this->redirectToRoute('admin_teachers');
        }
        
        return $this->render('admin/add_teacher.html.twig');
    }

    #[Route('/teachers/{id}/edit', name: 'admin_edit_teacher', methods: ['GET', 'POST'])]
    public function editTeacher(Request $request, User $teacher): Response
    {
        if (!$teacher->isTeacher()) {
            throw $this->createNotFoundException('User is not a teacher');
        }
        
        if ($request->isMethod('POST')) {
            $teacher->setFirstName($request->request->get('firstName'));
            $teacher->setLastName($request->request->get('lastName'));
            $teacher->setInstrument($request->request->get('instrument'));
            $teacher->setHourlyRate((float) $request->request->get('hourlyRate'));
            $teacher->setTeacherBio($request->request->get('teacherBio'));
            
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Teacher updated successfully!');
            return $this->redirectToRoute('admin_teachers');
        }
        
        return $this->render('admin/edit_teacher.html.twig', [
            'teacher' => $teacher
        ]);
    }

    #[Route('/teachers/{id}/toggle-status', name: 'admin_toggle_teacher_status', methods: ['POST'])]
    public function toggleTeacherStatus(User $teacher): Response
    {
        if (!$teacher->isTeacher()) {
            throw $this->createNotFoundException('User is not a teacher');
        }
        
        $teacher->setIsActive(!$teacher->isActive());
        $this->entityManager->flush();
        
        $status = $teacher->isActive() ? 'activated' : 'deactivated';
        $this->addFlash('success', "Teacher {$status} successfully!");
        
        return $this->redirectToRoute('admin_teachers');
    }

    #[Route('/students', name: 'admin_students')]
    public function students(): Response
    {
        $students = $this->userRepository->findStudentsWithProgress();
        
        return $this->render('admin/students.html.twig', [
            'students' => $students
        ]);
    }

    #[Route('/analytics', name: 'admin_analytics')]
    public function analytics(): Response
    {
        $stats = $this->userRepository->getUserStatistics();
        $topStudents = $this->userRepository->findTopStudentsByXP(10);
        $topTeachers = $this->userRepository->findTopTeachers(10);
        
        return $this->render('admin/analytics.html.twig', [
            'stats' => $stats,
            'topStudents' => $topStudents,
            'topTeachers' => $topTeachers
        ]);
    }

    #[Route('/reports', name: 'admin_reports')]
    public function reports(): Response
    {
        return $this->render('admin/reports.html.twig');
    }
}
