<?php

namespace App\Tests\Controller;

use App\Controller\AbstractCardController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ConcreteCardController extends AbstractCardController
{
    public function storeAndRetrieve(string $sessionKey, mixed $data): mixed
    {
        $this->save($sessionKey, $data);
        return $this->get($sessionKey);
    }
}

class ConcreteCardControllerTest extends TestCase
{
    private $requestStack;
    private $session;
    private $controller;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->requestStack->method('getSession')
            ->willReturn($this->session);

        $this->controller = new ConcreteCardController($this->requestStack);
    }

    public function testStoreAndRetrieve()
    {
        $sessionKey = 'test_key';
        $data = 'test_data';

        // Mock the session set and get methods
        $this->session->expects($this->once())
            ->method('set')
            ->with($sessionKey, $data);

        $this->session->expects($this->once())
            ->method('get')
            ->with($sessionKey)
            ->willReturn($data);

        // Call the storeAndRetrieve method
        $result = $this->controller->storeAndRetrieve($sessionKey, $data);

        // Assert the data was correctly retrieved
        $this->assertSame($data, $result);
    }
}
