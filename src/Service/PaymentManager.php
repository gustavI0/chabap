<?php


namespace App\Service;


use App\Entity\Payment;
use App\Form\PaymentType;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentManager
{

    public function createPaymentForm(): Form
    {

    }

    /**
     * @param array $paymentData
     * @return Response
     */
    public function createPayment(array $paymentData): Response
    {
        dd($paymentData);
        // Set your secret key. Remember to switch to your live secret key in production!
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        Stripe::setApiKey('sk_test_51HB0wcEUBXiqdgnEQySryKnZ0GAtAvJ2lg0fSxezgr2zHkTKUz3GCiCarSpD1ZFqpSTYBo6iMnDY8pzIBeXuNIQC00oHOlPqTc');

        try {
            PaymentIntent::create([
                'amount' => $paymentData['amount'],
                'currency' => 'eur',
                'payment_method_types' => ['card'],
                'receipt_email' => $paymentData['email'],
            ]);
        } catch (ApiErrorException $e) {
        }
    }

}