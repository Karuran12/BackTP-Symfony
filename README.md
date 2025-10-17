### Tom GUIITET
### Karuran GAJAROOBAN

# BACKEND TP API (Symfony 6) — README

API simple de gestion d’une bibliothèque : livres, auteurs, catégories, utilisateurs et emprunts. Projet réalisé par Tom et Karuran.

## 1) Stack & prérequis
- Composer
- Symfony CLI (Symfony 6.4+, )
- MySQL

## 2) Installation
```bash
git clone <url-du-repo>
cd tp
composer install
```

## 3) Routes de l'API

> Base URL (dev) : `http://127.0.0.1:8001`  
> Toutes les réponses sont en JSON. Envoyer `Content-Type: application/json` pour les requêtes avec corps.

### Accueil
- **GET /** — ping/accueil (vérifier que le serveur répond)

### Livres
- **GET /livres** — lister tous les livres
- **POST /livres** — créer un livre  
  Body : `{ "titre": "Dune", "datePublication": "1965-06-01", "auteurId": 1, "categorieId": 1 }`
- **GET /livres/{id}** — afficher un livre
- **PUT /livres/{id}** — mettre à jour (remplacement complet)
- **PATCH /livres/{id}** — mise à jour partielle
- **DELETE /livres/{id}** — supprimer

### Emprunts
- **POST /emprunts** — emprunter un livre  
  Body : `{ "utilisateurId": 1, "livreId": 1 }`
- **POST /emprunts/{id}/retour** — rendre un livre

### Utilisateurs
- **GET /utilisateurs/{id}/emprunts** — **compte** des emprunts **en cours** + **liste triée** par `dateEmprunt` (ancienne → récente)

### Auteurs
- **GET /auteurs/{id}/livres/empruntes?du=YYYY-MM-DD&au=YYYY-MM-DD** — **tous les livres** de l’auteur **empruntés au moins une fois** dans l’intervalle (liste **unique**)

