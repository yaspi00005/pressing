# Pressing — gestion de pressing (Symfony 6.1)

Application de gestion d'un pressing : dépôt du linge, suivi des commandes, encaissements, caisse, stock et relation client.

## Fonctionnalités

- **Commandes** : plusieurs articles par commande, service par article (lavage, repassage, nettoyage à sec…), observation par pièce (tache, couleur, défaut), numéro automatique `PAAMMJJ-001`, majoration **express**, remise, **livraison à domicile** avec frais, notes internes.
- **Circuit de statut** : Reçu → En traitement → Prêt à retirer → Livré (ou Annulé), retards signalés automatiquement.
- **Paiements** : acompte à la création, paiements partiels, plusieurs modes (espèces, Wave, Orange Money, carte, chèque, virement), livraison à crédit possible (confirmation explicite).
- **Ticket de dépôt** imprimable (format ticket 80 mm) et **message WhatsApp** prérempli pour prévenir le client (commande reçue / prête).
- **Grille tarifaire** article × service ; le prix se remplit seul à la saisie de la commande.
- **Clients** : fiche avec historique, total dépensé, solde dû, **points de fidélité** (crédités à la livraison).
- **Caisse** : rapport journalier (encaissements par mode, dépenses, solde) et suivi des **dépenses** par catégorie.
- **Stock** de produits consommables avec seuil d'alerte.
- **Réclamations** clients avec suivi de résolution.
- **Tableau de bord** : encaissé du jour/mois, dépôts, commandes prêtes/en retard, impayés, résultat du mois, meilleurs clients, alertes.

## Configuration

Les paramètres du pressing sont dans `config/services.yaml` : nom, adresse et téléphone (ticket), devise, indicatif pays pour WhatsApp, pourcentage express, délais standard, frais de livraison par défaut, points de fidélité par tranche.

## Installation

```bash
composer install
php bin/console doctrine:migrations:migrate
symfony serve   # ou php -S 127.0.0.1:8000 -t public
```

Puis créer les services (Services & tarifs), les articles, et renseigner les prix.
