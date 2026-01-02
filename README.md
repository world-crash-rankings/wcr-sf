# World Crash Rankings - Symfony

Version Symfony 8.0 du projet World Crash Rankings.

Develop
-------

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/world-crash-rankings/wcr-sf/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/world-crash-rankings/wcr-sf/?branch=develop)
[![Build Status](https://scrutinizer-ci.com/g/world-crash-rankings/wcr-sf/badges/build.png?b=dev)]()


## Contexte

Ce projet est une migration de l'ancienne version développée en **Zend Framework 2**, située dans `../wcr-zf2`.

L'objectif est de moderniser l'application en utilisant Symfony 8.0 avec les technologies actuelles.

## Stack technique

- **Framework**: Symfony 8.0
- **PHP**: 8.x
- **ORM**: Doctrine
- **Qualité de code**: PHPStan (niveau 8), PHP CodeSniffer (PSR-12)

## Installation

```bash
# Installer les dépendances
make install

# Créer la base de données
make db-create

# Exécuter les migrations
make db-migrate
```

## Commandes utiles

### Qualité de code
```bash
make phpcs         # Vérifier le style de code
make phpcs-fix     # Corriger automatiquement les erreurs
make phpstan       # Analyse statique
make qa            # Lance phpcs + phpstan
```

### Base de données
```bash
make db-create     # Créer la BDD
make db-migrate    # Exécuter les migrations
make db-fixtures   # Charger les fixtures
make db-reset      # Réinitialiser complètement la BDD
```

### Tests
```bash
make test          # Lancer les tests
make test-coverage # Tests avec couverture de code
```

### Serveur de développement
```bash
make serve         # Démarrer le serveur Symfony
make serve-stop    # Arrêter le serveur
make serve-log     # Afficher les logs
```

Tapez `make` ou `make help` pour voir toutes les commandes disponibles.

## Migration depuis ZF2

L'ancienne version ZF2 se trouve dans `../wcr-zf2`. Les fonctionnalités sont migrées progressivement vers cette version Symfony.
