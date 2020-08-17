<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\Product;
use App\Entity\ProductInterface;
use App\Form\PaymentType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\PaymentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/liste")
 */
class ProductController extends AbstractController
{

    /**
     * @var PaymentManager
     */
    protected $paymentManager;

    /**
     * ProductController constructor.
     * @param PaymentManager $paymentManager
     */
    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * @Route("/", name="product_index", methods={"GET"})
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function show(int $id, Request $request): Response
    {
        $payment = new Payment();
        $payment->setDate(new \DateTime('now'));
        $form = $this->createForm(PaymentType::class, $payment, [
            'action' => $this->generateUrl('product_contribute', array('id' => $id))
        ]);

//        $form = $this->createFormBuilder($payment)
//            ->add('email', TextType::class)
//            ->add('amount', TextType::class)
//            ->add('date', DateType::class)
//            ->add('save', SubmitType::class, ['label' => 'Contribuer'])
//            ->setAction($this->generateUrl('product_contribute', array('id' => $id)))
//            ->getForm();
//
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $data = $form->getData();
//            dd($data);
//            return $this->paymentManager->createPayment($payment);
//            //return $this->redirectToRoute('product_contribute');
//        }

        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '. $id
            );
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'paymentForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/contribute", name="product_contribute", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function contribute(Request $request): Response
    {
        return $this->paymentManager->createPayment($request->request->get('payment'));
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('product_index');
    }
}
