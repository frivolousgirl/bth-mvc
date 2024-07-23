<?php

use App\Card5\PlayerManager;
use App\Card5\PlayerFactory;
use App\Card5\Player;
use App\Card5\DeckOfCards;
use PHPUnit\Framework\TestCase;

class PlayerManagerTest extends TestCase
{
    private PlayerManager $playerManager;
    private PlayerFactory $playerFactory;
    private DeckOfCards $deckOfCards;
    private array $playerMocks;

    protected function setUp(): void
    {
        $this->playerFactory = $this->createMock(PlayerFactory::class);
        $this->deckOfCards = $this->createMock(DeckOfCards::class);

        $playerNames = ['Alice', 'Bob', 'Charlie'];

        $this->playerMocks = [];
        foreach ($playerNames as $name) {
            $playerMock = $this->createMock(Player::class);
            $this->playerMocks[] = $playerMock;
        }

        $this->playerFactory->expects($this->exactly(3))
            ->method('create')
            ->withConsecutive(['Alice'], ['Bob'], ['Charlie'])
            ->willReturnOnConsecutiveCalls(...$this->playerMocks);

        $this->playerManager = new PlayerManager($playerNames, $this->playerFactory);
    }

    public function testGetPlayersReturnsCorrectPlayers(): void
    {
        $players = $this->playerManager->getPlayers();
        $this->assertCount(3, $players);
        foreach ($players as $player) {
            $this->assertInstanceOf(Player::class, $player);
        }
    }

    public function testDealCardsDistributesCardsToPlayers(): void
    {
        $this->deckOfCards->expects($this->exactly(3))
            ->method('deal')
            ->with(5)
            ->willReturnOnConsecutiveCalls(
                ['card1', 'card2', 'card3', 'card4', 'card5'],
                ['card6', 'card7', 'card8', 'card9', 'card10'],
                ['card11', 'card12', 'card13', 'card14', 'card15']
            );

        foreach ($this->playerMocks as $player) {
            $player->expects($this->once())
                ->method('receiveCards')
                ->with($this->isType('array'));
        }

        $this->playerManager->dealCards($this->deckOfCards);
    }

    public function testDiscardAndDrawCallsPlayerMethods(): void
    {
        $playerId = 1;
        $cardsToDiscard = ['card1', 'card2'];

        $this->playerMocks[$playerId]->expects($this->once())
            ->method('discardAndDraw')
            ->with($cardsToDiscard, $this->deckOfCards);

        $this->playerManager->discardAndDraw($playerId, $cardsToDiscard, $this->deckOfCards);
    }

    public function testFoldCallsPlayerFold(): void
    {
        $playerId = 2;

        $this->playerMocks[$playerId]->expects($this->once())
            ->method('fold');

        $this->playerManager->fold($playerId);
    }

    public function testResetCallsPlayerReset(): void
    {
        foreach ($this->playerMocks as $player) {
            $player->expects($this->once())
                ->method('reset');
        }

        $this->playerManager->reset();
    }
}
