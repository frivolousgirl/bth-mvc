<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Card5\Game;
use App\Card5\HandEvaluator;
use App\Card5\Pot;
use App\Card5\DeckOfCards;
use App\Card5\GameState;
use App\Card5\BettingRound;
use App\Card5\PlayerManager;
use App\Card5\PlayerFactory;
use App\Card5\EventLogger;
use App\Card5\GameActionChecker;
use App\Card5\RandomNumberGenerator;

class ProjectController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        if (!$this->get("game")) {
            $handEvaluator = new HandEvaluator();
            $pot = new Pot();
            $playerFactory = new PlayerFactory($handEvaluator);
            $playerManager = new PlayerManager(["Jag", "Datorn"]
                , $playerFactory
            );
            $eventLogger = new EventLogger();
            $bettingRound = new BettingRound($playerManager
                , $pot
                , $eventLogger
                , $handEvaluator
                , Game::ANTE
            );
            $gameState = new GameState();
            $gameActionChecker = new GameActionChecker($gameState
                , $bettingRound
                , $playerManager
            );

            $game = new Game($playerManager
                , $handEvaluator
                , new DeckOfCards()
                , $pot
                , $gameState
                , $bettingRound
                , $eventLogger
                , $gameActionChecker
                , new RandomNumberGenerator()
            );

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
        $routes = $this->getRoutes();

        $data = [
            "routes" => $routes,
        ];

        return $this->render('project/api.html.twig', $data);
    }

    #[Route("/proj/report", name: "project_report")]
    public function report(): Response
    {
        return $this->render('project/report.html.twig');
    }

    #[Route("/proj/game", name: "project_game", methods: ["GET"])]
    public function game(): Response
    {
        $this->startNew();

        return $this->renderGame();
    }

    private function startNew(): void
    {
        $game = $this->get("game");

        $game->reset();

        $this->save("game", $game);
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

    #[Route("/proj/api/state", "api_state", format: "json", defaults: ["title" => "returns the game's current state"])]
    public function apiGetState(): JsonResponse
    {
        $game = $this->get("game");

        if (!$game) {
            return new JsonResponse();
        }

        $response = [
            "state" => $game->getState()
        ];

        return new JsonResponse($response);
    }

    #[Route("/proj/api/events", "api_events", format: "json", defaults: ["title" => "returns the game's events"])]
    public function apiGetEvents(): JsonResponse
    {
        $game = $this->get("game");

        if (!$game) {
            return new JsonResponse();
        }

        $response = [
            "events" => $game->getEvents()
        ];

        return new JsonResponse($response);
    }

    #[Route("/proj/api/players", "api_players", format: "json", defaults: ["title" => "returns the game's players"])]
    public function apiGetPlayers(): JsonResponse
    {
        $game = $this->get("game");

        if (!$game) {
            return new JsonResponse();
        }

        $response = [
            "players" => $game->getPlayers()
        ];

        return new JsonResponse($response);
    }

    #[Route("/proj/api/pot", "api_pot", format: "json", defaults: ["title" => "returns the game's pot"])]
    public function apiGetPot(): JsonResponse
    {
        $game = $this->get("game");

        if (!$game) {
            return new JsonResponse();
        }

        $response = [
            "pot" => $game->getPot()
        ];

        return new JsonResponse($response);
    }

    #[Route("/proj/api/reset", "api_reset", methods: ['POST'], format: "json", defaults: ["title" => "resets the current game"])]
    public function apiReset(): JsonResponse
    {
        $game = $this->get("game");

        if (!$game) {
            return new JsonResponse();
        }

        $prevState = $game->getState();

        $this->startNew();

        $game = $this->get("game");

        $response = [
            "prevState" => $prevState,
            "currentState" => $game->getState(),
        ];

        return new JsonResponse($response);
    }

    private function getRoutes()
    {
        $routes = [];

        // Get all routes from the Router
        $router = $this->container->get('router');
        $allRoutes = $router->getRouteCollection()->all();

        foreach ($allRoutes as $routeName => $route) {
            $routePath = $route->getPath();

            if (!str_starts_with($routePath, '/proj/api')) {
                continue;
            }

            // Check if the route returns JSON response
            if ($this->isJsonRoute($route)) {
                $defaults = $route->getDefaults();
                $url = $routePath;

                foreach ($defaults as $key => $value) {
                    $pattern = '{' . $key . '}';
                    $url = str_replace($pattern, $value, $url);
                }

                $routes[] = [
                    'name' => $routeName,
                    'path' => $routePath,
                    'title' => $defaults['title'],
                    'methods' => $route->getMethods(),
                    'url' => $url,
                ];
            }
        }

        return $routes;
    }

    private function isJsonRoute($route)
    {
        // Check if the route returns JSON response
        return $route->getDefault('_format') === 'json';
    }
}
