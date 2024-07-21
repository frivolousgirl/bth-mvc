<?php

namespace App\Card5;

use App\Card5\DeckOfCards;
use App\Card5\HandEvaluator;
use App\Card\Card;

class Player
{
    public $hand = [];
    public string $name;
    private bool $folded = false;
    private bool $hasSwapped = false;
    private HandEvaluator $handEvaluator;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->handEvaluator = new HandEvaluator();
    }

    public function reset(): void
    {
        $this->folded = false;
        $this->hasSwapped = false;
        $this->hand = [];
    }

    public function hasSwapped(): bool
    {
        return $this->hasSwapped;
    }

    public function receiveCards(array $cards)
    {
        $this->hand = array_merge($this->hand, $cards);
    }

    public function discardAndDraw(array $indices, DeckOfCards $deck)
    {
        foreach ($indices as $index) {
            unset($this->hand[$index]);
        }
        $this->hand = array_values($this->hand);
        $newCards = $deck->deal(count($indices));
        $this->receiveCards($newCards);
        $this->hasSwapped = true;
    }

    public function fold()
    {
        $this->folded = true;
    }

    public function hasFolded()
    {
        return $this->folded;
    }

    public function decideCardsToSwap(): array
    {
        $handEvaluation = $this->handEvaluator->evaluateHand($this->hand);

        switch ($handEvaluation) {
            case 'Straight Flush':
            case 'Four of a Kind':
            case 'Full House':
            case 'Flush':
            case 'Straight':
                return [];

            case 'Three of a Kind':
            case 'Two Pair':
            case 'One Pair':
                return $this->getIndicesOfUnwantedCards();

            case 'High Card':
            default:
                return $this->getIndicesOfLowCards();
        }
    }

    private function getIndicesOfUnwantedCards(): array
    {
        // Implement logic to find indices of cards not part of pairs or three of a kind
        $cards = [];
        for ($i = 0; $i < count($this->hand); $i++) {
            $unique = true;
            for ($j = 0; $j < count($this->hand); $j++) {
                if ($j === $i) {
                    continue;
                }
                $a = Card::valueFromRank($this->hand[$i]->rank);
                $b = Card::valueFromRank($this->hand[$j]->rank);
                if ($a === $b) {
                    $unique = false;
                    break;
                }
            }
            if ($unique) {
                $cards[] = $i;
            }
        }
        return $cards;
    }

    private function getIndicesOfLowCards(): array
    {
        // Sort hand by rank to find the lowest cards
        usort($this->hand, function ($a, $b) {
            return Card::valueFromRank($a->rank) <=> Card::valueFromRank($b->rank);
        });

        // Swap all except the highest cards
        $indices = [];
        for ($i = 0; $i < count($this->hand) - 2; $i++) {
            $indices[] = $i;
        }
        return $indices;
    }
}
