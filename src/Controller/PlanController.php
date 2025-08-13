<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlanController extends AbstractController
{
    #[Route('/plans', name: 'plans')]
    public function index(): Response
    {
        $plans = [
            [
                'name' => 'Free Demo',
                'type' => 'demo',
                'price' => 0,
                'sessions' => 1,
                'duration' => '15 minutes',
                'features' => [
                    'One 15-minute lesson',
                    'Meet your teacher',
                    'Experience our platform',
                    'No commitment required'
                ],
                'button_text' => 'Book Free Demo',
                'button_class' => 'btn-outline-primary'
            ],
            [
                'name' => 'Monthly Plan',
                'type' => 'monthly',
                'price' => 199.99,
                'sessions' => 4,
                'duration' => 'per month',
                'features' => [
                    '4 one-hour lessons',
                    'Progress tracking',
                    'Practice assignments',
                    'Flexible scheduling',
                    'Cancel anytime'
                ],
                'button_text' => 'Start Monthly Plan',
                'button_class' => 'btn-primary'
            ],
            [
                'name' => 'Yearly Plan',
                'type' => 'yearly',
                'price' => 1999.99,
                'sessions' => 48,
                'duration' => 'per year',
                'savings' => 'Save $400',
                'features' => [
                    '48 one-hour lessons',
                    'All monthly features',
                    'Priority scheduling',
                    'Free practice materials',
                    'Certificate upon completion'
                ],
                'button_text' => 'Start Yearly Plan',
                'button_class' => 'btn-success'
            ]
        ];

        return $this->render('plan/index.html.twig', [
            'plans' => $plans,
            'page_title' => 'Subscription Plans - Toms Music School'
        ]);
    }
}
