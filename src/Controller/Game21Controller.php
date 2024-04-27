<?php

namespace App\Controller;

use App\Controller\AbstractCardController;

use App\Game21\Player;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;

class Game21Controller extends AbstractCardController
{
    public function __construct(RequestStack $requestStack)
    {
        parent::__construct($requestStack);

        $session = $this->getSession();

        if (!$session->get("player"))
        {
            $session->set("player", new Player());
        }

        if (!$session->get("bank"))
        {
            $session->set("bank", new Player());
        }
    }

    #[Route("/game", name: "game")]
    public function game(): Response
    {
        return $this->render('21/home.html.twig');
    }

    #[Route("game/run", "game_run")]
    public function run(Request $request): Response
    {
        $player1 = $this->get("player");
        $player2 = $this->get("bank");

        if ($request->query->get("init") == "1")
        {
            $player1->init();
            $player2->init();

            $this->save("player", $player1);
            $this->save("bank", $player2);
            $this->save("canTakeCard", true);
            $this->save("canStop", false);

            $this->get("deck")->shuffle();
        }

        $data = [
            "canTakeCard" => $this->get("canTakeCard"),
            "canStop" => $this->get("canStop"),
            "player1" => $player1,
            "player2" => $player2,
        ];

        return $this->render("21/run.html.twig", $data);
    }

    #[Route("game/action", "game_action")]
    public function action(): Response
    {
        $action = $_POST["action"];

        if ($action == "take")
        {
            $this->takeCard();
        }
        else if ($action == "stay")
        {
            $this->stay();
        }

        return $this->redirectToRoute("game_run");
    }

    private function takeCard(): void
    {
        $deck = $this->get("deck");
        
        $card = $deck->drawCard();

        $player = $this->get("player");
        $player->addCard($card);

        $this->save("deck", $deck);
        $this->save("player", $player);
        $this->save("canStop", true);
    }

    private function stay(): void
    {
        $this->save("canTakeCard", false);
        $this->save("canStop", false);

        $deck = $this->get("deck");
        
        $player = $this->get("player");
        $bank = $this->get("bank");
        
        while ($bank->sumCardValues() < $player->sumCardValues())
        {
            $card = $deck->drawCard();
            $bank->addCard($card);
        }
    }
}