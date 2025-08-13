<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeacherController extends AbstractController
{
    #[Route('/teachers', name: 'teachers')]
    public function index(): Response
    {
        // Mock data for now - will be replaced with database queries
        $teachers = [
            [
                'name' => 'Alex Johnson',
                'instrument' => 'Guitar',
                'bio' => 'Professional guitarist with 10+ years of teaching experience',
                'rating' => 4.9,
                'students' => 45,
                'hourly_rate' => 75
            ],
            [
                'name' => 'Maria Rodriguez',
                'instrument' => 'Piano',
                'bio' => 'Classical pianist and jazz enthusiast with music degree',
                'rating' => 4.8,
                'students' => 38,
                'hourly_rate' => 80
            ],
            [
                'name' => 'David Chen',
                'instrument' => 'Drums',
                'bio' => 'Session drummer and music producer with touring experience',
                'rating' => 4.9,
                'students' => 52,
                'hourly_rate' => 70
            ]
        ];

        return $this->render('teacher/index.html.twig', [
            'teachers' => $teachers,
            'page_title' => 'Our Teachers - Toms Music School'
        ]);
    }

    #[Route('/teacher', name: 'teacher_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('teacher/dashboard.html.twig', [
            'page_title' => 'Teacher Dashboard - Toms Music School'
        ]);
    }
}
