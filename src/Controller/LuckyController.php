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
        $numbers = array();
        $starnr = array();

        while (count($numbers) < 5) {
            $value = random_int(1, 50);
            if (!in_array($value, $numbers)) {
                array_push($numbers, $value);
            }
        }
        
        while (count($starnr) < 2) {
            $value = random_int(1, 10);
            if (!in_array($value, $starnr)) {
                array_push($starnr, $value);
            }
        }
        
        sort($numbers);
        sort($starnr);

        $data = [
            "number" => implode(", ", $numbers),
            "starnumbers" => implode(", ", $starnr)
        ];

        return $this->render('lucky.html.twig', $data);
    }
}
