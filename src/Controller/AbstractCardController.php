<?php

namespace App\Controller;

use App\Card\DeckOfCards;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractCardController extends AbstractController
{
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        $session = $requestStack->getSession();

        if (!$session->get("deck")) {
            $deck = new DeckOfCards();

            $session->set("deck", $deck);
        }
    }
}
