<?php


namespace App\Controller;


use App\Service\PaymentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{

    /**
     * @var PaymentManager
     */
    protected $paymentManager;

    /**
     * WebhookController constructor.
     * @param PaymentManager $paymentManager
     */
    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * @Route("/webhook", name="stripe_webhook", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function webhookStripe(Request $request): Response
    {
        return $this->paymentManager->catchWebhookStripe($request);
    }
}