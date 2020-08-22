<?php


namespace App\Service;


use App\Entity\Payment;
use App\Entity\Product;
use App\Entity\ProductInterface;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PaymentManager
{

    protected $entityManager;
    protected $productRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
    }


    /**
     * @param array $payment
     * @return PaymentIntent
     */
    public function createPaymentIntentStripe(array $payment): string
    {
        // Set your secret key. Remember to switch to your live secret key in production!
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        Stripe::setApiKey('sk_test_51HB0wcEUBXiqdgnEQySryKnZ0GAtAvJ2lg0fSxezgr2zHkTKUz3GCiCarSpD1ZFqpSTYBo6iMnDY8pzIBeXuNIQC00oHOlPqTc');

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $payment['amount'] * 100,
                'currency' => 'eur',
                'payment_method_types' => ['card'],
                'receipt_email' => $payment['email'],
                'description' => $payment['product_id']
            ]);

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];
            return json_encode($output);
        } catch (ApiErrorException $e) {
            http_response_code(500);
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function catchWebhookStripe(Request $request): Response
    {
        // Set your secret key. Remember to switch to your live secret key in production!
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        Stripe::setApiKey('sk_test_51HB0wcEUBXiqdgnEQySryKnZ0GAtAvJ2lg0fSxezgr2zHkTKUz3GCiCarSpD1ZFqpSTYBo6iMnDY8pzIBeXuNIQC00oHOlPqTc');

        $endpoint_secret = 'whsec_9dPcJ4cIt2yWOCf04TFz6yi1q5lSA1Ph';

        $event = null;

        $header = 'Stripe-Signature';
        $signature = $request->headers->get($header);

        if (is_null($signature)) {
            throw new BadRequestHttpException(sprintf('Missing header %s', $header));
        }

        try {
            $event = Webhook::constructEvent($request->getContent(), $signature, $endpoint_secret);
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            throw new BadRequestHttpException('Invalid Stripe payload');
        } catch(SignatureVerificationException $e) {
            // Invalid signature
            throw new BadRequestHttpException('Invalid Stripe signature');
        }

        // Handle the event
        switch ($event->type) {
            case 'charge.succeeded':
                break;
            case 'payment_intent.created':
                break;
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contains a StripePaymentIntent
                $this->handlePaymentIntentSucceeded($paymentIntent);
                break;
            default:
                // Unexpected event type
                throw new BadRequestHttpException('Unexpected Stripe event type:' . $event->type);
        }

        return new Response();
    }

    /**
     * @param PaymentIntent $paymentIntent
     * @return Response
     */
    public function handlePaymentIntentSucceeded(PaymentIntent $paymentIntent): Response
    {
        $product = $this->entityManager
            ->getRepository(Product::class)
            ->find($paymentIntent->description);

        $currentContrib = $product->getCurrentContribution();
        $product->setCurrentContribution($currentContrib + ($paymentIntent->amount / 100));

        $this->createPayment($paymentIntent->amount, $paymentIntent->receipt_email, $product);

        $this->entityManager->flush();

        return new Response();
    }

    /**
     * @param int $amount
     * @param string $email
     * @param ProductInterface $product
     * @return Response
     */
    public function createPayment(int $amount, string $email, ProductInterface $product): Response
    {
        $payment = new Payment();
        $payment->setDate(new \DateTime('now'));
        $payment->setAmount($amount);
        $payment->setEmail($email);
        $payment->setProduct($product);
        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return new Response();
    }

}