<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Card5\Game;

class ProjectController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        if (!$this->get("game")) {
            $game = new Game(["Jag", "Datorn"]);

            $this->save("game", $game);
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

    #[Route("/proj", name: "project")]
    public function home(): Response
    {
        return $this->render("project/home.html.twig");
    }

    #[Route("/proj/about", name: "project_about")]
    public function about(): Response
    {
        return $this->render('project/about.html.twig');
    }

    #[Route("/proj/api", name: "project_api")]
    public function api(): Response
    {
        return $this->render('project/about.html.twig');
    }

    #[Route("/proj/report", name: "project_report")]
    public function report(): Response
    {
        return $this->render('project/about.html.twig');
    }

    #[Route("/proj/game", name: "project_game", methods: ["GET"])]
    public function game(): Response
    {
        $game = $this->get("game");

        $game->reset();

        $this->save("game", $game);
        
        return $this->renderGame();
    }

    #[Route("/proj/game", name: "project_gameplay", methods: ["POST"])]
    public function gameplay(Request $request): Response
    {
        $game = $this->get("game");
        $postData = $request->request->all();

        $game->action($postData);

        $this->save("game", $game);

        return $this->renderGame();
    }

    private function renderGame(): Response
    {
        $game = $this->get("game");

        $data = [
            "players" => $game->getPlayers(),
            "state" => $game->getState(),
            "pot" => $game->getPot(),
            "events" => $game->getEvents(),
            "canCheck" => $game->canCheck(),
            "canBet" => $game->canBet(),
            "canCall" => $game->canCall(),
            "canFold" => $game->canFold(),
            "canDraw" => $game->canDraw(),
            "currentPlayer" => $game->getCurrentPlayer(),
        ];

        return $this->render('project/game.html.twig', $data);
    }
}
