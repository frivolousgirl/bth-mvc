<?php

namespace App\Controller;

use App\Controller\AbstractCardController;
use App\Game21\Game;
use App\Game21\FlashMessage;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;

class Game21Controller extends AbstractCardController implements FlashMessage
{
    public function __construct(RequestStack $requestStack)
    {
        parent::__construct($requestStack);

        $session = $this->getSession();

        if (!$session->get("game"))
        {
            $session->set("game", new Game());
        }
    }

    public function addFlashMessage(string $type, mixed $message): void
    {
        $this->addFlash($type, $message);
    }

    #[Route("/game", name: "game")]
    public function game(): Response
    {
        return $this->render('21/home.html.twig');
    }

    #[Route("game/run", "game_run")]
    public function run(Request $request): Response
    {
        $game = $this->get("game");

        if ($request->query->get("init") == "1")
        {
            $game->init();
            $this->save("game", $game);
        }

        $data = [
            "game" => $game,
        ];

        return $this->render("21/run.html.twig", $data);
    }

    #[Route("game/action", "game_action")]
    public function action(): Response
    {
        $action = $_POST["action"];

        $game = $this->get("game");

        if ($action == "take")
        {
            $game->drawPlayerCard($this);
            $this->save("game", $game);
        }
        else if ($action == "stay")
        {
            $game->playerStays($this);
        }
        else if ($action == "new")
        {
            return $this->newGame();
        }

        return $this->redirectToRoute("game_run");
    }

    private function newGame(): Response
    {
        $data = [
            "init" => 1,
        ];

        return $this->redirectToRoute("game_run", $data);
    }
}