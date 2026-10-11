Nouvelle feature démarrée : $ARGUMENTS

On part de zéro sur cette feature. Règles pour toute la session :

- Les commandes Git sont autorisées (`git add`, `git commit`, `git push`...), mais demande toujours confirmation avant d'en exécuter une
- Si le schéma Doctrine doit évoluer : modifier l'entité puis générer la migration avec `make db-migrate` (ou `bin/console doctrine:migrations:diff` + `make db-migrate`) — ne jamais écrire de SQL brut à la main

## Fichier de suivi

Au démarrage, demande-moi :
- L'objectif de la feature (ce qu'on cherche à faire)
- S'il y a une évolution de schéma prévue (nouvelle entité, nouveau champ, etc.)

Puis crée le fichier `.claude/feature-status/[nom-feature].md` avec la structure suivante :

```markdown
# Feature : [nom]

## Objectif
[description de ce qu'on cherche à faire]

## Migrations Doctrine
[liste des migrations créées, ou "aucune pour l'instant"]

## Fichiers modifiés
- [liste au fur et à mesure]

## État
[ce qui est fait, ce qui reste à faire]
```

Met à jour ce fichier au fil de la session à chaque fois qu'un fichier est modifié, et fais un point sur l'état en fin de session.

## Qualité du code

En fin de session, lance `make qa` (phpcs + phpstan niveau 8) et corrige toutes les erreurs avant de terminer.

## Rappels projet

- `declare(strict_types=1);` obligatoire sur tout nouveau fichier PHP
- Toutes les chaînes affichées à l'utilisateur doivent être en anglais (site entièrement en anglais)
- La logique métier liée aux scores/classements passe par `ScoreService`, pas par les contrôleurs
- Templates Twig : Bootstrap 5 uniquement, pas de CSS inline ni de balise `<style>`
