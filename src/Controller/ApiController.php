<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route("/api", name: "api", format: "json")]
    public function api()
    {
        $routes = $this->getRoutes();

        return new JsonResponse($routes);
    }

    #[Route("/api/quote", name: "api_quote", format: "json")]
    public function api_quote()
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
                $routes[] = [
                    'name' => $routeName,
                    'path' => $routePath
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