<?php


namespace App\Service;


use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentManager
{

    /**
     * @param Request $request
     * @return Response
     */
    public function createPayment(Request $request): Response
    {
        dd($request);
        // Set your secret key. Remember to switch to your live secret key in production!
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        Stripe::setApiKey('sk_test_51HB0wcEUBXiqdgnEQySryKnZ0GAtAvJ2lg0fSxezgr2zHkTKUz3GCiCarSpD1ZFqpSTYBo6iMnDY8pzIBeXuNIQC00oHOlPqTc');

        try {
            PaymentIntent::create([
                'amount' => 1000,
                'currency' => 'eur',
                'payment_method_types' => ['card'],
                'receipt_email' => 'gus.guyon@gmail.com',
            ]);
        } catch (ApiErrorException $e) {
        }
    }

}