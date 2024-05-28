<?php

namespace App\Tests\Controller;

use App\Controller\ApiController;
use App\Entity\Book;
use App\Repository\BookRepository;
use App\Game21\Game;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;

class ApiControllerTest extends TestCase
{
    private $requestStack;
    private $bookRepository;
    private $session;
    private $container;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->requestStack = new RequestStack();

        $request = new Request();
        $request->setSession($this->session);
        $this->requestStack->push($request);

        $this->bookRepository = $this->createMock(BookRepository::class);

        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testApiGame()
    {
        $game = $this->createMock(Game::class);
        $game->method('getPlayerPoints')->willReturn(10);
        $game->method('getBankPoints')->willReturn(15);

        $this->session->method('get')->willReturnMap([
            ['deck', null, null], // Return null for deck
            ['game', null, $game] // Return the mocked game for game
        ]);

        $controller = new ApiController($this->requestStack, $this->bookRepository);
        $controller->setContainer($this->container);

        $response = $controller->apiGame();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(10, $data['player_points']);
        $this->assertEquals(15, $data['bank_points']);
    }

    public function testApiQuote()
    {
        $controller = new ApiController($this->requestStack, $this->bookRepository);

        $response = $controller->apiQuote();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('quote', $data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertNotEmpty($data['quote']);
        $this->assertNotEmpty($data['timestamp']);
    }

    public function testApiListBooks()
    {
        $book1 = new Book();
        $book1->setId(1);
        $book1->setTitle('Book One');
        $book1->setIsbn('1234567890');
        $book1->setAuthor('Author One');
        $book1->setImage('image1.jpg');

        $book2 = new Book();
        $book2->setId(2);
        $book2->setTitle('Book Two');
        $book2->setIsbn('0987654321');
        $book2->setAuthor('Author Two');
        $book2->setImage('image2.jpg');

        $this->bookRepository->method('findAll')->willReturn([$book1, $book2]);

        $controller = new ApiController($this->requestStack, $this->bookRepository);

        $response = $controller->apiListBooks();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);

        $this->assertCount(2, $data);
        $this->assertEquals('Book One', $data[0]['title']);
        $this->assertEquals('Book Two', $data[1]['title']);
    }
}
