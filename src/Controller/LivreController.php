<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Repository\AuteurRepository;
use App\Repository\CategorieRepository;
use App\Repository\LivreRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/livres')]
final class LivreController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function list(LivreRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), 200, [], ['groups' => ['livre:read']]);
    }

    #[Route('', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        AuteurRepository $auteurs,
        CategorieRepository $categories
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        foreach (['titre', 'datePublication', 'auteurId', 'categorieId'] as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Champs requis manquant: $field"], 400);
            }
        }

        $auteur = $auteurs->find((int) $data['auteurId']);
        $categorie = $categories->find((int) $data['categorieId']);
        if (!$auteur || !$categorie) {
            return $this->json(['error' => 'Auteur ou catégorie introuvable'], 404);
        }

        $datePub = DateTimeImmutable::createFromFormat('Y-m-d', (string) $data['datePublication']);
        if (!$datePub) {
            return $this->json(['error' => 'datePublication invalide (format attendu: YYYY-MM-DD)'], 400);
        }

        $livre = (new Livre())
            ->setTitre((string) $data['titre'])
            ->setDatePublication($datePub)
            ->setDisponible(true);

        if (count($errors = $validator->validate($livre)) > 0) {
            return $this->json(['error' => (string) $errors], 400);
        }

        $em->persist($livre);
        $em->flush();

        return $this->json($livre, 201, [], ['groups' => ['livre:read']]);
    }

    #[Route('/{id<\d+>}', methods: ['GET'])]
    public function show(Livre $livre): JsonResponse
    {
        return $this->json($livre, 200, [], ['groups' => ['livre:read']]);
    }

    #[Route('/{id<\d+>}', methods: ['PUT', 'PATCH'])]
    public function update(
        Livre $livre,
        Request $request,
        EntityManagerInterface $em,
        AuteurRepository $auteurs,
        CategorieRepository $categories
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['titre'])) {
            $livre->setTitre((string) $data['titre']);
        }
        if (isset($data['datePublication'])) {
            $d = DateTimeImmutable::createFromFormat('Y-m-d', (string) $data['datePublication']);
            if (!$d) {
                return $this->json(['error' => 'datePublication invalide (YYYY-MM-DD)'], 400);
            }
            $livre->setDatePublication($d);
        }
        if (isset($data['auteurId'])) {
            $a = $auteurs->find((int) $data['auteurId']);
            if (!$a) return $this->json(['error' => 'Auteur introuvable'], 404);
            $livre->setAuteur($a);
        }
        if (isset($data['categorieId'])) {
            $c = $categories->find((int) $data['categorieId']);
            if (!$c) return $this->json(['error' => 'Catégorie introuvable'], 404);
            $livre->setCategorie($c);
        }
        if (array_key_exists('disponible', $data)) {
            $livre->setDisponible((bool) $data['disponible']);
        }

        $em->flush();
        return $this->json($livre, 200, [], ['groups' => ['livre:read']]);
    }

    #[Route('/{id<\d+>}', methods: ['DELETE'])]
    public function delete(Livre $livre, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($livre);
        $em->flush();
        return $this->json(null, 204);
    }
}
