# World Crash Rankings

Web application for managing **Burnout 2** crash mode rankings, players, scores, and statistics.

[![CI](https://github.com/world-crash-rankings/wcr-sf/actions/workflows/quality.yml/badge.svg?branch=dev)](https://github.com/world-crash-rankings/wcr-sf/actions/workflows/quality.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen)](https://phpstan.org)
[![PHPCS](https://img.shields.io/badge/code%20style-PSR--12-blue)](https://www.php-fig.org/psr/psr-12)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Symfony](https://img.shields.io/badge/Symfony-8.0-000000?logo=symfony&logoColor=white)](https://symfony.com)

## Tech Stack

- **Framework**: Symfony 8.0
- **PHP**: 8.4+
- **ORM**: Doctrine ORM 3.6
- **Database**: MySQL
- **Template Engine**: Twig
- **Asset Management**: Asset Mapper
- **Frontend**: Bootstrap 5, Symfony UX (Turbo, Stimulus)
- **Code Quality**: PHPStan (level 8), PHP CodeSniffer (PSR-12)

## Features

### Rankings
- 4 ranking types: Total, Average Position, Stars, Percent
- Dynamic rank calculations updated on every score change
- Country-specific rankings

### Players
- Player profiles with detailed statistics
- Personal records tracking
- Platform and proof type statistics
- Player vs player comparison tool

### Scores
- Score entry with platform (GC/Xbox/PS2), proof type, glitch type
- World record and personal record auto-detection
- Rank history tracking (chart rank, best rank)

### Zones
- Zone descriptions and top scores
- Strategy management per zone
- Video gallery per zone
- Star threshold scoring system

### Admin Panel
- Full CRUD for all entities (Players, Scores, Zones, Countries, Strategies, News, Users)
- Score filtering and search
- Role-based access (ROLE_ADMIN, ROLE_SUPER_ADMIN)

### Notifications
- Discord notifications on new scores via Symfony Notifier

## Installation

```bash
# Install dependencies
make install

# Create database
make db-create

# Run migrations
make db-migrate

# Load fixtures (optional)
make db-fixtures

# Create an admin user
php bin/console app:create-user
```

## Useful Commands

### Code Quality
```bash
make phpcs         # Check code style (PSR-12)
make phpcs-fix     # Auto-fix code style issues
make phpstan       # Static analysis (level 8)
make qa            # Run phpcs + phpstan
```

### Database
```bash
make db-create     # Create database
make db-migrate    # Run migrations
make db-fixtures   # Load fixtures
make db-reset      # Full reset (drop, recreate, migrate, fixtures)
```

### Tests
```bash
make test          # Run tests
make test-coverage # Run tests with code coverage
```

### Development Server
```bash
make serve         # Start Symfony dev server
make serve-stop    # Stop server
make serve-log     # View server logs
```

Run `make` or `make help` to see all available commands.
