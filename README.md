# Pressing — gestion de pressing (Symfony 6.1)

Application de gestion d'un pressing : dépôt du linge, suivi des commandes, encaissements, caisse, stock et relation client.

## Fonctionnalités

- **Commandes** : plusieurs articles et services par commande, observation par pièce, numéro automatique `PAAMMJJ-001`, majoration **express**, remise, **livraison à domicile**, acompte et paiements partiels (espèces, Wave, Orange Money, carte, chèque, virement), livraison à crédit confirmée.
- **Circuit de statut** : Reçu → En traitement → Prêt à retirer → Livré (ou Annulé) ; retards détectés automatiquement ; ticket 80 mm ; message **WhatsApp** prérempli.
- **Guichet** : recherche immédiate par n° de ticket (douchette compatible), nom ou téléphone.
- **Listes** : filtres dans l'adresse, pagination serveur (10 à 500 lignes), **export Excel** (commandes, clients, paiements).
- **Paiements / caisse** : totaux, filtres par période, mode et caissier, solde (encaissements − dépenses), impression.
- **Clients** : fiche avec historique, total dépensé, solde dû, points de fidélité (à la livraison).
- **Catalogue** : catégories, articles, services (lavage, repassage…) et **grille de tarifs** article × service.
- **Dépenses**, **stock** de consommables avec seuil d'alerte, **réclamations** avec suivi.
- **Tableau de bord** : activité du jour, argent, graphiques (commandes par mois, répartition par statut, encaissements), meilleurs clients, alertes.
- **Rôles** : Administrateur, Réception / caisse, Atelier ; **Administration** : utilisateurs (création, rôle, désactivation) et **journal d'activité**.
- **Application installable** (PWA) et en-têtes de sécurité (CSP) en production.

L'organisation de l'interface (gabarits, cartes, types de pages, composants) est décrite dans [`docs/fiche-technique.md`](docs/fiche-technique.md).

## Configuration

Les paramètres du pressing sont dans `config/services.yaml` : nom, adresse et téléphone (ticket), devise, indicatif pays pour WhatsApp, pourcentage express, délais standard, frais de livraison par défaut, points de fidélité par tranche.

## Installation

```bash
composer install
php bin/console doctrine:migrations:migrate
symfony serve   # ou php -S 127.0.0.1:8000 -t public
```

Puis créer le premier administrateur avec `php bin/console app:create-admin`, ajouter les services (Catalogue › Services & tarifs), les articles et leurs prix.
