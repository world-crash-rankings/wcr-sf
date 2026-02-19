# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

World Crash Rankings (WCR) - A Symfony 8.0 web application that manages Burnout 3: Takedown crash mode rankings, players, scores, and related statistics. The application tracks world records, player rankings across multiple metrics (total, average position, average percent, average stars), and provides a full admin panel for data management.

## Development Commands

### Quality Assurance
```bash
make phpcs         # Check code style (PSR-12)
make phpcs-fix     # Auto-fix code style issues
make phpstan       # Run static analysis (level 8)
make qa            # Run both phpcs and phpstan
```

### Database Management
```bash
make db-create     # Create database
make db-migrate    # Run migrations
make db-fixtures   # Load fixtures
make db-reset      # Drop, recreate, migrate, and load fixtures
make db-rollback   # Rollback last migration
```

### Development Server
```bash
make serve         # Start Symfony development server
make serve-stop    # Stop development server
make serve-log     # View server logs
```

### Testing
```bash
make test          # Run PHPUnit tests
make test-coverage # Run tests with coverage report (requires Xdebug)
```

### Dependencies
```bash
make install       # Install Composer dependencies
make update        # Update Composer dependencies
```

### Cache Management
```bash
make cache-clear   # Clear Symfony cache
make cache-warmup  # Warm up cache
```

Run `make` or `make help` to see all available commands.

## Architecture

### Technology Stack
- **Framework**: Symfony 8.0
- **PHP**: 8.4+
- **ORM**: Doctrine ORM 3.6
- **Database**: MySQL
- **Template Engine**: Twig
- **Asset Management**: Asset Mapper (not Webpack Encore)
- **Frontend**: Symfony UX (Turbo, Stimulus)

### Code Quality Standards
- **Coding Standard**: PSR-12 (enforced by PHP_CodeSniffer)
- **Static Analysis**: PHPStan level 8 (strictest)
- **Additional Rules**:
  - Strict types declaration required (`declare(strict_types=1)`)
  - Short array syntax only (no `array()`)
  - Symfony-aware PHPStan rules enabled via phpstan-symfony extension

### Directory Structure
```
src/
├── Command/          # Console commands (create-user, generate-sitemap)
├── Controller/       # HTTP controllers (public + Admin/)
├── DataFixtures/     # Doctrine fixtures
├── Entity/           # Doctrine entities (Player, Score, Zone, Country, etc.)
├── Enum/             # PHP enums (Platform, ProofType, GlitchType, etc.)
├── Event/            # Custom event classes
├── EventSubscriber/  # Event subscribers (Discord notifications)
├── Form/             # Symfony form types
├── Repository/       # Doctrine repositories with custom queries
├── Security/         # Custom user provider
├── Service/          # Business logic (ScoreService)
├── Twig/             # Custom Twig extensions
└── Kernel.php        # Application kernel

config/
├── packages/         # Bundle configurations
├── routes/           # Routing configurations
└── services.yaml     # Service container configuration

migrations/           # Doctrine migrations
tests/                # PHPUnit tests
templates/            # Twig templates (public + admin/)
public/               # Web-accessible files
```

### Dependency Injection
- Autowiring enabled by default in `config/services.yaml`
- All classes in `src/` are automatically registered as services
- Service IDs are fully-qualified class names

## Key Business Logic

### ScoreService (`src/Service/ScoreService.php`)
Central service handling all ranking calculations. When a score is added, updated, or deleted:
- **percent_wr**: Score as percentage of world record
- **stars**: Based on zone-specific thresholds
- **chart_rank / best_rank**: Position tracking in zone rankings
- **Personal records (pr_entry)**: Auto-flagged
- **Player statistics**: total, avg_pos, avg_percent, avg_stars recalculated
- **Player ranks**: total_rank, avg_pos_rank, avg_percent_rank, avg_stars_rank updated
- **Non-rankable glitches**: Freeze and Sink glitch types are excluded from personal records and rankings

### Entities
- **Player**: Profiles with computed statistics and rankings
- **Score**: Individual crash scores with platform, proof type, glitch type metadata
- **Zone**: Crash zones/maps with star thresholds and strategies
- **Country**: Player nationalities with country-level rankings
- **Car / Strat**: Vehicles and strategies linked to zones
- **Star**: Zone-specific star threshold scoring
- **News**: Site announcements with rich text
- **User**: Admin authentication (ROLE_ADMIN, ROLE_SUPER_ADMIN)

### Enums
- **Platform**: GC, Xbox, PS2
- **ProofType**: Pic, XBL, Replay, Live, Freeze
- **GlitchType**: None, Glitch, Sink, Freeze
- **Frequency** / **Version**: Additional score metadata

### Notifications
- Discord notifications sent on new scores via Symfony Notifier (ScoreDiscordSubscriber)

## Git Workflow

- **NEVER create git commits** - The user handles all commits themselves
- **NEVER run `git add`, `git commit`, or `git push` commands**
- Only run `git status` or `git diff` for informational purposes when explicitly requested
- If the user asks to "commit changes", only explain what files were modified and let them commit

## Important Notes

- **Language**: The website is entirely in English - all content, messages, and UI text must be in English only
- **Generated Files**: `config/preload.php` and `config/reference.php` are auto-generated by Symfony and excluded from PHPCS
- **Strict Types**: All new PHP files must include `declare(strict_types=1);`
- **No Docker**: This project runs without Docker (unlike some Symfony projects)
- **Turbo Forms**: Add `data-turbo="false"` to forms that have issues with Symfony UX Turbo intercepting submissions
- **Pagination**: Uses KNP Paginator for all paginated lists
- **SEO**: Full meta tags (OG, Twitter, canonical), JSON-LD structured data, sitemap generation command
