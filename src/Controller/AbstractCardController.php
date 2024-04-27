<?php

namespace App\Controller;

use App\Card\DeckOfCards;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class AbstractCardController extends AbstractController
{
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        $session = $this->getSession();

        if (!$session->get("deck")) {
            $deck = new DeckOfCards();

            $session->set("deck", $deck);
        }
    }

    protected function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    protected function save(string $sessionKey, mixed $data): void
    {
        $session = $this->getSession();
        $session->set($sessionKey, $data);
    }

    protected function get(string $sessionKey): mixed
    {
        $session = $this->getSession();
        return $session->get($sessionKey);
    }
}
