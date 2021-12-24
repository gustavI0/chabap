<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\Product;
use App\Entity\ProductInterface;
use App\Form\PaymentType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\PaymentManager;
use App\Service\ProductManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

/**
 * @Route("/liste")
 */
class ProductController extends AbstractController
{

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var PaymentManager
     */
    protected $paymentManager;

    /**
     * ProductController constructor.
     * @param ProductManager $productManager
     * @param PaymentManager $paymentManager
     */
    public function __construct(ProductManager $productManager, PaymentManager $paymentManager)
    {
        $this->productManager = $productManager;
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
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productImages = $product->getImages();
            foreach($productImages as $key => $productImage){
                $productImage->setProduct($product);
                $productImages->set($key, $productImage);
            }

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
     * @return Response
     */
    public function show(int $id): Response
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '. $id
            );
        }

        $payment = new Payment();
        $payment->setProduct($product);

        $paymentData = ['left_contribution' => $payment->getLeftToContribute()];
        $form = $this->createFormBuilder($paymentData)
            ->add('email', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Email'],
            ])
            ->add('amount', IntegerType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Montant en €'],
            ])
            ->add('left_contribution', HiddenType::class)
            ->add('pay', SubmitType::class, [
                'label' => 'Participer'])
            ->setAction($this->generateUrl('product_contribute', array('id' => $id)))
            ->getForm();

        $percentage = $this->productManager->calculatePercentage($product);

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'percentage' => $percentage,
            'payment' => $payment,
            'paymentForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/contribute", name="product_contribute", methods={"GET", "POST"})
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function contribute(int $id, Request $request): Response
    {
        $contribution = $request->request->get('form');

        if ($contribution['amount'] > $contribution['left_contribution']) {
            $this->addFlash(
                'warning',
                'Le montant de votre contribution est supérieur au prix total. Pensez à la cagnotte du voyage !'
            );
            return $this->redirectToRoute('product_show', array('id' => $id));
        }
        if ($contribution['amount'] < 1) {
            $this->addFlash(
                'danger',
                'Le montant de votre contribution doit être supérieur ou égal à 1 !'
            );
            return $this->redirectToRoute('product_show', array('id' => $id));
        }

        $contribution['product_id'] = $id;

        $paymentIntent =  $this->paymentManager->createPaymentIntentStripe($contribution);

        return $this->render('payment/contribute.html.twig', [
            'paymentIntent' => json_decode($paymentIntent),
            'payment' => $contribution
        ]);
    }



    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productImages = $product->getImages();
            foreach($productImages as $key => $productImage){
                $productImage->setProduct($product);
                $productImages->set($key, $productImage);
            }
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
