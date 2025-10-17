<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Repository\EmpruntRepository;
use App\Repository\LivreRepository;
use App\Repository\UtilisateurRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/emprunts')]
final class EmpruntController extends AbstractController
{
    public function __construct(private LoggerInterface $logger) {}

    #[Route('', methods: ['POST'])]
    public function emprunter(
        Request $request,
        EntityManagerInterface $em,
        UtilisateurRepository $users,
        LivreRepository $livres,
        EmpruntRepository $emprunts
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        if (!isset($data['utilisateurId'], $data['livreId'])) {
            return $this->json(['error' => 'Champs requis: utilisateurId, livreId'], 400);
        }

        $user = $users->find((int) $data['utilisateurId']);
        $livre = $livres->find((int) $data['livreId']);
        if (!$user || !$livre) {
            return $this->json(['error' => 'Utilisateur ou livre introuvable'], 404);
        }

        if (!$livre->isDisponible() || $emprunts->livreEstDejaEmprunte($livre->getId())) {
            $this->logger->info('Tentative emprunt livre non disponible', ['livre' => $livre->getId()]);
            return $this->json(['error' => 'Livre déjà emprunté / indisponible'], 409);
        }

        $actifs = $emprunts->countEmpruntsActifsParUtilisateur($user->getId());
        if ($actifs >= 4) {
            $this->logger->info("Limite d'emprunts atteinte", ['user' => $user->getId(), 'actifs' => $actifs]);
            return $this->json(['error' => 'Limite de 4 emprunts atteinte'], 409);
        }

        $emprunt = (new Emprunt())
            ->setUtilisateur($user)
            ->setLivre($livre)
            ->setDateEmprunt(new DateTimeImmutable());

        $livre->setDisponible(false);

        $em->persist($emprunt);
        $em->flush();

        return $this->json($emprunt, 201, [], ['groups' => ['emprunt:read']]);
    }

    #[Route('/{id<\d+>}/retour', methods: ['POST'])]
    public function rendre(Emprunt $emprunt, EntityManagerInterface $em): JsonResponse
    {
        if (!$emprunt->isActif()) {
            return $this->json(['error' => 'Emprunt déjà retourné'], 409);
        }

        $emprunt->setDateRetour(new DateTimeImmutable());
        $emprunt->getLivre()->setDisponible(true);

        $em->flush();
        return $this->json($emprunt, 200, [], ['groups' => ['emprunt:read']]);
    }
}
