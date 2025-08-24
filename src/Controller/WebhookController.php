<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Enrollment;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/webhook')]
class WebhookController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
        // Initialize Stripe with API key from environment
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY'] ?? '');
    }

    #[Route('/stripe', name: 'webhook_stripe', methods: ['POST'])]
    public function stripeWebhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');
        $endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';

        if (empty($endpointSecret)) {
            $this->logger->error('Stripe webhook secret not configured');
            return new Response('Webhook secret not configured', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            $this->logger->error('Invalid webhook signature: ' . $e->getMessage());
            return new Response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;
            case 'charge.refunded':
                $this->handleChargeRefunded($event->data->object);
                break;
            default:
                $this->logger->info('Received unknown event type: ' . $event->type);
        }

        return new Response('Webhook received', 200);
    }

    private function handleCheckoutSessionCompleted($session): void
    {
        $this->logger->info('Processing checkout session completed: ' . $session->id);

        // Find the order
        $order = $this->entityManager->getRepository(Order::class)
            ->findOneBy(['stripeSessionId' => $session->id]);

        if (!$order) {
            $this->logger->error('Order not found for session: ' . $session->id);
            return;
        }

        // Update order status
        $order->setStatus('paid');
        $order->setStripePaymentIntentId($session->payment_intent);
        $order->setUpdatedAt(new \DateTime());

        // Create enrollment if it doesn't exist
        $existingEnrollment = $this->entityManager->getRepository(Enrollment::class)
            ->findOneBy(['user' => $order->getUser(), 'course' => $order->getCourse()]);

        if (!$existingEnrollment) {
            $enrollment = new Enrollment();
            $enrollment->setUser($order->getUser());
            $enrollment->setCourse($order->getCourse());
            $enrollment->setEnrolledAt(new \DateTime());
            $enrollment->setStatus('active');
            $enrollment->setProgress(0);

            $this->entityManager->persist($enrollment);
        }

        $this->entityManager->flush();
        $this->logger->info('Order and enrollment processed successfully for session: ' . $session->id);
    }

    private function handlePaymentIntentSucceeded($paymentIntent): void
    {
        $this->logger->info('Processing payment intent succeeded: ' . $paymentIntent->id);

        // Find the order
        $order = $this->entityManager->getRepository(Order::class)
            ->findOneBy(['stripePaymentIntentId' => $paymentIntent->id]);

        if (!$order) {
            $this->logger->error('Order not found for payment intent: ' . $paymentIntent->id);
            return;
        }

        // Update order status
        $order->setStatus('paid');
        $order->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();
        $this->logger->info('Order status updated to paid for payment intent: ' . $paymentIntent->id);
    }

    private function handlePaymentIntentFailed($paymentIntent): void
    {
        $this->logger->info('Processing payment intent failed: ' . $paymentIntent->id);

        // Find the order
        $order = $this->entityManager->getRepository(Order::class)
            ->findOneBy(['stripePaymentIntentId' => $paymentIntent->id]);

        if (!$order) {
            $this->logger->error('Order not found for payment intent: ' . $paymentIntent->id);
            return;
        }

        // Update order status
        $order->setStatus('failed');
        $order->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();
        $this->logger->info('Order status updated to failed for payment intent: ' . $paymentIntent->id);
    }

    private function handleChargeRefunded($charge): void
    {
        $this->logger->info('Processing charge refunded: ' . $charge->id);

        // Find the order by payment intent ID
        $order = $this->entityManager->getRepository(Order::class)
            ->findOneBy(['stripePaymentIntentId' => $charge->payment_intent]);

        if (!$order) {
            $this->logger->error('Order not found for charge: ' . $charge->id);
            return;
        }

        // Update order status
        $order->setStatus('refunded');
        $order->setUpdatedAt(new \DateTime());

        // Cancel enrollment if it exists
        $enrollment = $this->entityManager->getRepository(Enrollment::class)
            ->findOneBy(['user' => $order->getUser(), 'course' => $order->getCourse()]);

        if ($enrollment) {
            $enrollment->setStatus('cancelled');
            $enrollment->setUpdatedAt(new \DateTime());
        }

        $this->entityManager->flush();
        $this->logger->info('Order status updated to refunded and enrollment cancelled for charge: ' . $charge->id);
    }
}
