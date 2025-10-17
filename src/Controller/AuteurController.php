<?php

namespace App\Controller;

use App\Repository\AuteurRepository;
use App\Repository\EmpruntRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auteurs')]
final class AuteurController extends AbstractController
{
    #[Route('/{id<\d+>}/livres/empruntes', methods: ['GET'])]
    public function livresEmpruntesEntreDeuxDates(
        int $id,
        Request $request,
        AuteurRepository $auteurs,
        EmpruntRepository $emprunts
    ): JsonResponse {
        $auteur = $auteurs->find($id);
        if (!$auteur) return $this->json(['error' => 'Auteur introuvable'], 404);

        $du = (string) $request->query->get('du');
        $au = (string) $request->query->get('au');
        if (!$du || !$au) return $this->json(['error' => 'Paramètres requis: du=YYYY-MM-DD&au=YYYY-MM-DD'], 400);

        $d1 = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $du . ' 00:00:00');
        $d2 = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $au . ' 23:59:59');
        if (!$d1 || !$d2) {
            return $this->json(['error' => 'Format de dates invalide'], 400);
        }

        $livres = $emprunts->createQueryBuilder('e')
            ->select('DISTINCT l')
            ->join('e.livre', 'l')
            ->andWhere('l.auteur = :a')->setParameter('a', $auteur)
            ->andWhere('e.dateEmprunt BETWEEN :d1 AND :d2')
            ->setParameter('d1', $d1)->setParameter('d2', $d2)
            ->getQuery()->getResult();

        return $this->json($livres, 200, [], ['groups' => ['livre:read']]);
    }
}
