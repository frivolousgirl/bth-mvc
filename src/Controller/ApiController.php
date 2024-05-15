<?php

namespace App\Controller;

use App\Controller\AbstractCardController;
use App\Repository\BookRepository;
use App\Entity\Book;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiController extends AbstractCardController
{
    private BookRepository $bookRepository;

    public function __construct(RequestStack $requestStack, BookRepository $bookRepository)
    {
        parent::__construct($requestStack);

        $this->bookRepository = $bookRepository;
    }

    #[Route("/api", name: "api")]
    public function api(): Response
    {
        $routes = $this->getRoutes();

        $data = [
            "routes" => $routes,
        ];

        return $this->render("api/home.html.twig", $data);
    }

    #[Route("/api/game", "api_game", format: "json", defaults: ["title" => "returns the game 21 score"])]
    public function apiGame(): JsonResponse
    {
        $game = $this->get("game");

        if (!$game) {
            return new JsonResponse();
        }

        $response = [
            "player_points" => $game->getPlayerPoints(),
            "bank_points" => $game->getBankPoints(),
        ];

        return new JsonResponse($response);
    }

    #[Route("/api/quote", name: "api_quote", format: "json", defaults: ['title' => 'returns a random quote'])]
    public function apiQuote(): JsonResponse
    {
        $quotes = array(
            "Don not seek for everything to happen as you wish it would, but rather wish that everything happens as it actually will. Then your life will flow well. - Epictetus",
            "The impediment to action advances action. What stands in the way becomes the way. - Marcus Aurelius, Meditations",
            "The happiness of your life depends upon the quality of your thoughts. - Marcus Aurelius, Meditations");

        $quote = $quotes[array_rand($quotes)];

        $response = [
            "timestamp" => date("Y-m-d H:i:s"),
            "quote" => $quote
        ];

        return new JsonResponse($response);
    }

    #[Route("/api/deck", name: "api_deck", format: "json", defaults: ["title" => "returns a deck sorted on suit and rank"])]
    public function apiDeck(): JsonResponse
    {
        $deck = $this->requestStack->getSession()->get("deck");

        return new JsonResponse($deck->getAllCardsSorted());
    }

    #[Route("/api/deck/shuffle", methods: ['POST'], name: "api_shuffle", format: "json", defaults: ['title' => 'shuffles the deck and returns it'])]
    public function apiShuffle(): JsonResponse
    {
        $session = $this->requestStack->getSession();
        $deck = $session->get('deck');
        $deck->shuffle();
        $session->set("deck", $deck);

        return new JsonResponse($deck->getAllCards());
    }

    #[Route("/api/deck/draw/", methods: ['POST'], name: "api_draw", format: "json", defaults: ['title' => 'draws a card from the deck and returns it, together with the number of remaining cards in the same deck'])]
    public function apiDraw(): JsonResponse
    {
        return $this->apiDrawInternal(1);
    }

    #[Route("/api/deck/draw/{number<\d+>}", methods: ['POST'], name: "api_draw_number", format: "json", defaults: ['number' => 5, 'title' => 'draws {number} of cards from the deck and returns them, together with the number of remaining cards in the same deck'])]
    public function apiDrawNumber($number): JsonResponse
    {
        return $this->apiDrawInternal($number);
    }

    #[Route("/api/library/books", name: "api_library_books", format: "json", defaults: ['title' => 'returns all books in the library'])]
    public function apiListBooks(): JsonResponse
    {
        $books = $this->bookRepository->findAll();

        $data = [];

        foreach ($books as $book)
        {
            $data[] = $this->mapBook($book);
        }

        return new JsonResponse($data);
    }

    private function mapBook(Book $book): array
    {
        return [
            "id" => $book->getId(),
            "title" => $book->getTitle(),
            "isbn" => $book->getIsbn(),
            "author" => $book->getAuthor(),
            "image" => $book->getImage()
        ];
    }

    #[Route("/api/library/book/{isbn}", name: "api_library_book_isbn", format: "json", defaults: ['isbn' => '9789129728583', 'title' => 'returns the book matching {isbn}'])]
    public function apiGetBook(string $isbn): JsonResponse
    {
        $book = $this->bookRepository->findOneBy(["isbn" => $isbn]);

        if ($book)
        {
            $data = $this->mapBook($book);

            return new JsonResponse($data);
        }

        return new JsonResponse();
    }

    private function apiDrawInternal($number)
    {
        $session = $this->requestStack->getSession();
        $deck = $session->get('deck');
        $cards = [];
        for ($i = 0; $i < $number; $i++) {
            $cards[] = $deck->drawCard();
        }
        $session->set("deck", $deck);

        $data = [
            "cards" => $cards,
            "number_of_cards" => count($deck->getAllCards()),
        ];

        return new JsonResponse($data);
    }

    private function getRoutes()
    {
        $routes = [];

        // Get all routes from the Router
        $router = $this->container->get('router');
        $allRoutes = $router->getRouteCollection()->all();

        foreach ($allRoutes as $routeName => $route) {
            $routePath = $route->getPath();

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
