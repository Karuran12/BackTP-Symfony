<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'name' => 'API publique',
            'endpoints' => [
                'GET /livres',
                'POST /livres',
                'GET /livres/{id}',
                'PUT|PATCH /livres/{id}',
                'DELETE /livres/{id}',
                'POST /emprunts',
                'POST /emprunts/{id}/retour',
                'GET /utilisateurs/{id}/emprunts',
                'GET /auteurs/{id}/livres/empruntes?du=YYYY-MM-DD&au=YYYY-MM-DD',
            ],
        ]);
    }
}
