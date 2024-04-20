<?php

namespace App\Controller;

use App\Controller\AbstractCardController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardGameController extends AbstractCardController
{
    #[Route("/card", name: "card_start")]
    public function home(): Response
    {
        return $this->render('card/home.html.twig');
    }

    #[Route('/card/deck', name:'card_deck')]
    public function cardDeck(): Response
    {
        $deck = $this->requestStack->getSession()->get("deck");

        $data = [
            "deck" => $deck,
        ];

        return $this->render('card/card_deck.html.twig', $data);
    }

    #[Route('/card/deck/shuffle', name:'card_deck_shuffle')]
    public function cardDeckShuffle(): Response
    {
        $session = $this->requestStack->getSession();
        $deck = $session->get("deck");
        $deck->reset();
        $deck->shuffle();

        $session->set("deck", $deck);

        $data = [
            "deck" => $deck,
        ];

        return $this->render('card/card_deck_shuffle.html.twig', $data);
    }

    #[Route('/card/deck/draw', name:'card_deck_draw')]
    public function cardDeckDraw(): Response
    {
        $session = $this->requestStack->getSession();
        $deck = $session->get("deck");
        $card = $deck->drawCard();

        $session->set("deck", $deck);

        $data = [
            "suit" => $card->getSuitSymbol(),
            "rank" => $card->rank,
            "number_of_cards" => $deck->countCards(),
        ];

        return $this->render('card/card_deck_draw.html.twig', $data);
    }

    #[Route('/card/deck/draw/{number<\d+>}', name:'card_deck_draw_number')]
    public function cardDeckDrawNumber(int $number): Response
    {
        $session = $this->requestStack->getSession();
        $deck = $session->get("deck");
        $cards = [];

        for ($i = 0; $i < $number; $i++) {
            $cards[] = $deck->drawCard();
        }

        $session->set("deck", $deck);

        $data = [
            "cards" => $cards,
            "number_of_cards" => $deck->countCards(),
        ];

        return $this->render('card/card_deck_draw_number.html.twig', $data);
    }
}
