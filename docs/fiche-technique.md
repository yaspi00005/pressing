# Fiche technique — interface du Pressing

Application Symfony 6.4 · Twig · Bootstrap 5 (thème **Workload**). Cette fiche applique la même organisation que le projet SOTRAMA : un gabarit commun, une grille de 12 colonnes, des cartes pour chaque sujet, quatre modèles de pages, des composants partagés et toutes les personnalisations dans deux fichiers.

## 1. Règles

| Règle | Pourquoi |
|---|---|
| Le thème n'est **jamais modifié** (`public/theme/`) | On peut le remplacer ou le mettre à jour sans rien perdre. |
| On utilise **ses classes** (Bootstrap + Workload), pas les nôtres | Toutes les pages ont le même aspect. |
| Les ajustements sont **regroupés** : `public/css/pressing.css` et `public/js/pressing.js`, chargés *après* le thème | Un seul endroit où corriger. |
| **Aucun script dans les pages** | Politique de sécurité du contenu (CSP) active en production ; tout passe par `pressing.js`. |

```
public/theme/            thème Workload d'origine (CSS, JS, polices, icônes) — non modifié
public/css/pressing.css  ajustements visuels : tailles compactes, tableaux fins, pastilles douces
public/js/pressing.js    comportements : confirmations, totaux en direct, graphiques, select2…
templates/base.html.twig         gabarit commun (menu, en-tête, messages, fenêtres)
templates/base_simple.html.twig  sans menu ni en-tête : connexion
templates/partials/      menu, fil d'Ariane, messages, pastilles, pagination, fenêtres…
templates/form/theme.html.twig   habillage des formulaires
templates/<module>/      pages de chaque module
```

## 2. Gabarit commun

`base.html.twig` contient tout ce qui ne change jamais : logo + bouton du menu, en-tête (titre, recherche globale, *Nouvelle commande*, cloche d'alertes, profil), menu latéral (`partials/_menu`, entrées selon le rôle), fil d'Ariane, messages flash, fenêtres communes.

| Bloc | Rôle |
|---|---|
| `title` | Titre de la page : onglet, en-tête et fil d'Ariane. |
| `body` | Le contenu — **la seule zone que chaque page remplit**. |
| `breadcrumb` | Fil d'Ariane (automatique). Pages parentes : `{% set fil = [{label: 'Clients', url: path('app_clients_index')}] %}` |
| `stylesheets`, `javascripts` | Styles ou scripts propres à une page (rares). |

```twig
{% extends 'base.html.twig' %}
{% block title %}Commandes{% endblock %}
{% block body %} … le contenu … {% endblock %}
```

## 3. Grille et cartes

Une rangée (`row`) = 12 unités. Découpages utilisés : `col-12` (listes), `col-xl-8` + `col-xl-4` (fiches : informations + actions), `col-md-6` (deux champs côte à côte), `col-xl-3 col-6` (compteurs). Sur téléphone les colonnes s'empilent seules.

Chaque sujet est une **carte** : en-tête (icône `text-primary me-2` + titre à gauche, boutons à droite avec `flex-wrap gap-2`) puis `card-body`. `card carte-action` met en avant l'action principale (Avancement, Encaisser).

## 4. Les quatre types de pages

| Type | Disposition | Exemples |
|---|---|---|
| **Tableau de bord** | Bandeau d'accueil, 4 cartes de chiffres, 4 cartes d'argent, graphiques, liste à traiter | `dashboard/` |
| **Liste** | Une carte : en-tête (nombre de résultats · Export Excel · Nouveau), filtres sur une ligne, tableau `table-responsive` + `table-hover`, pagination | `commande/index`, `clients/index`, `caisse/index`, `administration/journal` |
| **Fiche** | En-tête (n° + pastilles + boutons), puis `col-xl-8` informations + `col-xl-4` actions | `commande/show`, `clients/show` |
| **Formulaire** | Carte centrée (`col-xl-7/8`), boutons *Annuler* / *Enregistrer* en bas à droite | `clients/new`, `administration/user_form` |

### Listes
Filtres **dans l'adresse** (partageables), pagination serveur avec choix 10 / 25 / 50 / 100 / 500 lignes, export CSV (s'ouvre dans Excel) qui reprend les filtres, ligne vide avec icône (`_empty_state`).
Conventions du tableau : montants alignés à droite `class="num"` · info secondaire sous le nom `<small>` · n° cliquable `text-primary fw-bold` · reste à payer `text-danger fw-bold` · statuts en pastilles (`_badge`).

## 5. Formulaires
Construits par Symfony et habillés par `templates/form/theme.html.twig` : on écrit `{{ form_row(form.nom) }}`, pas le HTML du champ. Champs liés côte à côte en `col-md-6`. Listes longues avec recherche (classe `js-select2`). Erreurs vérifiées côté serveur et **listées d'un coup dans une fenêtre** avec l'emplacement du champ (« Lignes › n° 2 › Prix unitaire »). Lignes répétées (articles d'une commande) : « + Ajouter un article », chaque ligne supprimable.

## 6. Composants (`templates/partials/`)
`_menu` · `_logo` · `_fil_ariane` · `_flash` · `_badge` (statut de commande, paiement Payé / Partiel / Non payé) · `_badge_actif` · `_pagination` · `_empty_state` · `_modal_confirmation` · `_modal_erreurs` · `_journal_fiche`.
```twig
{{ include('partials/_badge.html.twig', {commande: c}, with_context = false) }}
{{ include('partials/_pagination.html.twig') }}   {# variable : pagination #}
```

## 7. CSS et JavaScript
- **Couleur principale** : `var(--primary)` (vert du thème), jamais en dur. Sens des couleurs : vert = action principale / réussi · rouge = reste à payer / suppression · orange = attention / partiel · gris = secondaire.
- **JavaScript** : les éléments sont repérés par des classes `js-…` ou des attributs `data-…` ; chaque fonction est isolée (une erreur n'empêche pas les autres).
```twig
{# Confirmation sans écrire de JavaScript #}
<form method="post" action="…" data-confirm="La catégorie sera supprimée."
      data-confirm-titre="Supprimer ?" data-confirm-type="danger" data-confirm-bouton="Oui, supprimer">
```
Autres attributs : `data-print` (imprimer), `data-ouvrir-modal`, `js-auto-submit` (filtre envoyé au changement), `canvas[data-chart]` (graphique Chart.js).

## 8. Modules et rôles

| Module (menu) | Administrateur | Réception / caisse | Atelier |
|---|:-:|:-:|:-:|
| Tableau de bord, Guichet, Commandes (consultation) | ✔ | ✔ | ✔ |
| Créer / modifier / encaisser / livrer / annuler une commande | ✔ | ✔ | — (peut faire avancer Reçu → En traitement → Prêt) |
| Clients, Paiements, Réclamations | ✔ | ✔ | — |
| Stock produits | ✔ | — | ✔ |
| Catalogue (catégories, articles, services & tarifs) | ✔ | — | — |
| Administration (dépenses, utilisateurs, journal) | ✔ | — | — |

Même disposition, contenu selon le rôle : le tableau de bord masque l'argent à l'atelier, la fiche commande n'affiche *Encaisser* qu'à la réception.

## 9. Fonctions transversales

| Fonction | Emplacement | Fonctionnement |
|---|---|---|
| Recherche globale | En-tête | N° de ticket, nom ou téléphone → fiche (n° exact) ou liste |
| Guichet | Menu | Recherche plein écran, douchette de code-barres compatible (saisie + Entrée), liste « Prêtes à retirer » |
| Export Excel | En-tête des listes | CSV UTF-8 ; reprend les filtres en cours |
| Ticket | Fiche commande | Format ticket 80 mm, impression directe |
| WhatsApp | Fiche commande | Message prérempli selon le statut (reçue, prête, livrée), sans API |
| Alertes | Cloche | Retards, prêtes à retirer, impayés, réclamations, stock bas |
| Historique | Bas des fiches + Administration › Journal | Qui a fait quoi et quand |
| Confirmations | Actions sensibles | Fenêtre commune avant suppression, annulation, désactivation |
| Application installable | Navigateur du téléphone | PWA : icône sur l'écran d'accueil |

## 10. Téléphone et tablette
Grille empilée automatiquement · menu replié derrière ☰ · tableaux `table-responsive` · boutons d'en-tête de carte à la ligne (`flex-wrap`) · colonnes secondaires masquées avec `d-none d-md-table-cell` · claviers numériques sur les montants · application installable.

## 11. Recette : créer une page
1. Créer `templates/<module>/<page>.html.twig`, hériter de `base.html.twig`, donner un `title`.
2. Choisir le type de page et reprendre sa disposition ; découper en `row` / `col-…` ; un sujet = une carte.
3. Réutiliser les partiels (badges, pagination, confirmations).
4. Placer avec `d-flex`, `gap`, marges ; du CSS dans `pressing.css` seulement en dernier recours.
5. Ajouter l'entrée de menu dans `_menu.html.twig` avec sa condition de rôle, et la règle dans `config/packages/security.yaml`.
6. Vérifier sur téléphone.
