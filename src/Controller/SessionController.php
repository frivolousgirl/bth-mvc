<?php

namespace App\Controller;

use App\Dice\Dice;
use App\Dice\DiceGraphic;
use App\Dice\DiceHand;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionController extends AbstractController
{
    #[Route("/session", name: "session_start")]
    public function home(): Response
    {
        return $this->render('session/home.html.twig');
    }

    #[Route('/session/delete', name: 'session_delete')]
    public function delete(): Response
    {
        session_start();
        session_unset();
        session_destroy();

        $this->addFlash('success', 'Session has been destroyed');

        return $this->redirectToRoute('session_start');
    }
}
