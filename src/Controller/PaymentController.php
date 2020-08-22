<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\PaymentInterface;
use App\Form\PaymentType;
use App\Repository\PaymentRepository;
use App\Service\PaymentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/payment")
 * @IsGranted("ROLE_ADMIN")
 */
class PaymentController extends AbstractController
{

    /**
     * @Route("/", name="payment_index", methods={"GET"})
     * @param PaymentRepository $paymentRepository
     * @return Response
     */
    public function index(PaymentRepository $paymentRepository): Response
    {
        return $this->render('payment/index.html.twig', [
            'payments' => $paymentRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="payment_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $payment = new Payment();
        $payment->setDate(new \DateTime('now'));
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($payment);
            $entityManager->flush();

            return $this->redirectToRoute('payment_index');
        }

        return $this->render('payment/new.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="payment_show", methods={"GET"})
     * @param PaymentInterface $payment
     * @return Response
     */
    public function show(PaymentInterface $payment): Response
    {
        return $this->render('payment/show.html.twig', [
            'payment' => $payment,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="payment_edit", methods={"GET","POST"})
     * @param Request $request
     * @param PaymentInterface $payment
     * @return Response
     */
    public function edit(Request $request, PaymentInterface $payment): Response
    {
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('payment_index');
        }

        return $this->render('payment/edit.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="payment_delete", methods={"DELETE"})
     * @param Request $request
     * @param PaymentInterface $payment
     * @return Response
     */
    public function delete(Request $request, PaymentInterface $payment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$payment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($payment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('payment_index');
    }
}
