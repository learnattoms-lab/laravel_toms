<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Order;
use App\Entity\Enrollment;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CourseRepository $courseRepository
    ) {
        // Initialize Stripe with API key from environment
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY'] ?? '');
    }

    #[Route('/start/{courseId}', name: 'checkout_start', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function start(Request $request, int $courseId): Response
    {
        $course = $this->courseRepository->find($courseId);
        
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        // Check if user is already enrolled
        $existingEnrollment = $this->entityManager->getRepository(Enrollment::class)
            ->findOneBy(['user' => $this->getUser(), 'course' => $course]);
            
        if ($existingEnrollment) {
            $this->addFlash('warning', 'You are already enrolled in this course.');
            return $this->redirectToRoute('course_show', ['id' => $courseId]);
        }

        try {
            // Create Stripe checkout session
            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $course->getTitle(),
                            'description' => $course->getDescription(),
                        ],
                        'unit_amount' => $course->getPriceCents(),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('checkout_success', [
                    'courseId' => $courseId,
                    'session_id' => '{CHECKOUT_SESSION_ID}'
                ], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('checkout_cancel', [
                    'courseId' => $courseId
                ], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
                'metadata' => [
                    'course_id' => $courseId,
                    'user_id' => $this->getUser()->getId(),
                ],
            ]);

            // Create order record
            $order = new Order();
            $order->setUser($this->getUser());
            $order->setCourse($course);
            $order->setAmountCents($course->getPriceCents());
            $order->setCurrency('usd');
            $order->setStatus('pending');
            $order->setStripeSessionId($checkoutSession->id);
            $order->setCreatedAt(new \DateTime());

            $this->entityManager->persist($order);
            $this->entityManager->flush();

            return $this->redirect($checkoutSession->url);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Unable to create checkout session. Please try again.');
            return $this->redirectToRoute('course_show', ['id' => $courseId]);
        }
    }

    #[Route('/success', name: 'checkout_success', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');
        $courseId = $request->query->get('courseId');

        if (!$sessionId || !$courseId) {
            throw $this->createNotFoundException('Invalid checkout session');
        }

        try {
            // Retrieve the checkout session from Stripe
            $checkoutSession = Session::retrieve($sessionId);
            
            if ($checkoutSession->payment_status !== 'paid') {
                throw new \Exception('Payment not completed');
            }

            // Find the order
            $order = $this->entityManager->getRepository(Order::class)
                ->findOneBy(['stripeSessionId' => $sessionId]);
                
            if (!$order) {
                throw new \Exception('Order not found');
            }

            // Update order status
            $order->setStatus('paid');
            $order->setStripePaymentIntentId($checkoutSession->payment_intent);
            $order->setUpdatedAt(new \DateTime());

            // Create enrollment
            $enrollment = new Enrollment();
            $enrollment->setUser($this->getUser());
            $enrollment->setCourse($order->getCourse());
            $enrollment->setEnrolledAt(new \DateTime());
            $enrollment->setStatus('active');
            $enrollment->setProgress(0);

            $this->entityManager->persist($enrollment);
            $this->entityManager->flush();

            $this->addFlash('success', 'Payment successful! You are now enrolled in the course.');

            return $this->redirectToRoute('course_show', ['id' => $courseId]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'There was an issue processing your payment. Please contact support.');
            return $this->redirectToRoute('course_show', ['id' => $courseId]);
        }
    }

    #[Route('/cancel/{courseId}', name: 'checkout_cancel', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(int $courseId): Response
    {
        $this->addFlash('info', 'Checkout was cancelled.');
        return $this->redirectToRoute('course_show', ['id' => $courseId]);
    }
}
