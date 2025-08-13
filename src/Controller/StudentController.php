<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StudentController extends AbstractController
{
    #[Route('/student', name: 'student_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('student/dashboard.html.twig', [
            'page_title' => 'Student Dashboard - Toms Music School'
        ]);
    }
}
