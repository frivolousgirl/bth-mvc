<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LuckyController extends AbstractController
{
    #[Route('/lucky', name: 'lucky')]
    public function number(): Response
    {
        $numbers = $this->generateUniqueRandomNumbers(5, 1, 50);
        $starnr = $this->generateUniqueRandomNumbers(2, 1, 10);

        sort($numbers);
        sort($starnr);

        $data = [
            "number" => implode(", ", $numbers),
            "starnumbers" => implode(", ", $starnr)
        ];

        return $this->render('lucky.html.twig', $data);
    }

    private function generateUniqueRandomNumbers(int $count, int $min, int $max): array
    {
        $numbers = [];

        while (count($numbers) < $count) {
            $value = random_int($min, $max);
            if (!in_array($value, $numbers)) {
                $numbers[] = $value;
            }
        }

        return $numbers;
    }
}
