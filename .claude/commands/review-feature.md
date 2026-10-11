Revue de code de la feature : $ARGUMENTS

## Étape 1 : Lecture du résumé de la feature

Lis le fichier `.claude/feature-status/$ARGUMENTS.md` et extrais :
- L'objectif de la feature
- La liste des fichiers modifiés déclarés
- L'état actuel (ce qui est fait, ce qui reste)

## Étape 2 : Lecture du dernier commit

Exécute les commandes suivantes (lecture seule) pour récupérer le contenu du dernier commit sur la branche courante :

1. `git log -1 --pretty=format:"%H %s"` — pour obtenir le hash et le message du dernier commit
2. `git show --stat HEAD` — pour voir les fichiers modifiés dans ce commit
3. `git diff HEAD~1 HEAD` — pour lire le diff complet du dernier commit

Si le diff est trop long, lis directement les fichiers modifiés identifiés.

## Étape 3 : Revue de code

Effectue une revue rigoureuse sur les points suivants :

### Architecture Symfony
- Les fichiers sont-ils dans les bonnes couches (`Controller/`, `Service/`, `Repository/`, `Entity/`, `Form/`, `EventSubscriber/`) ?
- Pas de logique métier dans les Controllers (doit être dans `Service/`, notamment `ScoreService` pour tout ce qui touche scores/rankings)
- Pas de requêtes Doctrine brutes dans les Controllers (doit passer par un Repository)
- Services correctement autowirés (pas de câblage manuel inutile dans `services.yaml`)

### Qualité du code PHP
- `declare(strict_types=1);` présent en tête de chaque nouveau fichier
- Respect PSR-12 (nommage, typage, visibilité) — conforme à `make phpcs`
- Conforme à PHPStan niveau 8 — pas de erreurs `make phpstan`
- Pas de code mort, de TODO oubliés, de `var_dump`/`dd()`
- Types de retour explicites, paramètres typés
- Tableaux en syntaxe courte `[]` uniquement

### Sécurité
- Pas d'injection SQL possible (requêtes paramétrées via Doctrine)
- Données utilisateur correctement validées/échappées
- Routes admin protégées (`IsGranted`, `ROLE_ADMIN`/`ROLE_SUPER_ADMIN`)

### Templates Twig
- Pas de style inline (`style="..."` ou balise `<style>`)
- Utilisation exclusive de classes Bootstrap 5
- Tout le texte affiché est en anglais (le site est entièrement en anglais, pas de mélange de langues)
- Formulaires impactés par Turbo : vérifier `data-turbo="false"` si besoin

### Logique métier scores/rankings
- Si la feature touche aux scores : vérifier la cohérence avec `ScoreService` (percent_wr, stars, chart_rank/best_rank, pr_entry, recalcul des stats joueur et des rangs)
- Les glitches non classants (Freeze, Sink) sont bien exclus des PR et classements si pertinent
- Notifications Discord (`ScoreDiscordSubscriber`) non cassées si un nouveau score est créé

### Migrations
- Toute évolution de schéma passe par une migration Doctrine dans `migrations/`, jamais de SQL joué manuellement hors migration

### Performance
- Pas de requêtes N+1 (jointures manquantes dans les repositories)
- Pagination KNP utilisée sur les listes

### Cohérence avec le fichier de suivi
- Les fichiers modifiés dans le commit correspondent-ils à ceux listés dans `.claude/feature-status/$ARGUMENTS.md` ?
- Rien d'inattendu ou hors scope ?

## Étape 4 : Rapport de revue

Présente le rapport structuré ainsi :

### Résumé
- Feature : `$ARGUMENTS`
- Dernier commit : [hash court] - [message]
- Fichiers reviewés : [liste]

### Points positifs
[Ce qui est bien fait]

### Problèmes critiques 🔴
[Bugs, failles sécu, violations d'architecture graves — à corriger immédiatement]

### Améliorations recommandées 🟡
[Code smell, manque de typage, conventions non respectées — à corriger avant merge]

### Suggestions mineures 🟢
[Optionnel, style, lisibilité — à discuter]

### Verdict
✅ Prêt à merger / ⚠️ Corrections requises / ❌ Bloquant

Demande-moi si je veux que tu corriges les problèmes identifiés.
