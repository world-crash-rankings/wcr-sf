# World Crash Rankings

Web application for managing **Burnout 3: Takedown** crash mode rankings, players, scores, and statistics.

Develop
-------

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/world-crash-rankings/wcr-sf/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/world-crash-rankings/wcr-sf/?branch=develop)
[![Build Status](https://scrutinizer-ci.com/g/world-crash-rankings/wcr-sf/badges/build.png?b=dev)]()

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
