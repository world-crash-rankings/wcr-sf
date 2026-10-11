Reprise de la feature : $ARGUMENTS

Règles strictes pour cette session :

- Les commandes Git sont autorisées (`git add`, `git commit`, `git push`...), mais demande toujours confirmation avant d'en exécuter une
- Si le schéma Doctrine doit évoluer : modifier l'entité puis générer la migration avec `make db-migrate` (ou `bin/console doctrine:migrations:diff` + `make db-migrate`) — ne jamais écrire de SQL brut à la main

## Fichier de suivi

Au démarrage, lis le fichier `.claude/feature-status/$ARGUMENTS.md` et fais un résumé de :
- L'objectif de la feature
- Les migrations Doctrine déjà créées
- Les fichiers déjà modifiés
- Où on en était

Puis demande-moi ce qu'on attaque dans cette session.

Met à jour ce fichier au fil de la session à chaque fois qu'un fichier est modifié, et fais un point sur l'état en fin de session.

## Qualité du code

En fin de session, lance `make qa` (phpcs + phpstan niveau 8) et corrige toutes les erreurs avant de terminer.

## Rappels projet

- `declare(strict_types=1);` obligatoire sur tout nouveau fichier PHP
- Toutes les chaînes affichées à l'utilisateur doivent être en anglais (site entièrement en anglais)
- La logique métier liée aux scores/classements passe par `ScoreService`, pas par les contrôleurs
- Templates Twig : Bootstrap 5 uniquement, pas de CSS inline ni de balise `<style>`
