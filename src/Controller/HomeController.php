<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'Accueil',
        ]);
    }

    /**
     * @Route("/gus")
     */
    public function makeAdmin()
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find(1);

        $user->setRoles(['ROLE_ADMIN']);

        $this->getDoctrine()->getManager()->flush();

        return new Response();
    }
}
