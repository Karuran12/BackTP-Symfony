<?php

namespace App\Controller;

use App\Repository\EmpruntRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/utilisateurs')]
final class UtilisateurController extends AbstractController
{
    #[Route('/{id<\d+>}/emprunts', methods: ['GET'])]
    public function empruntsEnCours(
        int $id,
        UtilisateurRepository $users,
        EmpruntRepository $emprunts
    ): JsonResponse {
        $user = $users->find($id);
        if (!$user) return $this->json(['error' => 'Utilisateur introuvable'], 404);

        $liste = $emprunts->createQueryBuilder('e')
            ->andWhere('e.utilisateur = :u')->setParameter('u', $user)
            ->andWhere('e.dateRetour IS NULL')
            ->orderBy('e.dateEmprunt', 'ASC')
            ->getQuery()->getResult();

        return $this->json([
            'utilisateur' => ['id' => $user->getId(), 'nom' => $user->getNom(), 'prenom' => $user->getPrenom()],
            'count' => count($liste),
            'emprunts' => $liste,
        ], 200, [], ['groups' => ['emprunt:read']]);
    }
}
