# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

World Crash Rankings (WCR) - Symfony 8.0 version. This is a **migration project** from a legacy Zend Framework 2 application located at `../wcr-zf2`. The application manages crash mode rankings, players, scores, and related statistics.

**Migration Status**: Early stage - basic Symfony 8.0 skeleton is initialized, legacy ZF2 modules need to be migrated progressively.

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
- **Database**: MySQL (same as ZF2 version)
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
├── Controller/     # HTTP controllers
├── Entity/         # Doctrine entities
├── Repository/     # Doctrine repositories
└── Kernel.php      # Application kernel

config/
├── packages/       # Bundle configurations
├── routes/         # Routing configurations
└── services.yaml   # Service container configuration

migrations/         # Doctrine migrations
tests/              # PHPUnit tests
templates/          # Twig templates
public/             # Web-accessible files
```

### Dependency Injection
- Autowiring enabled by default in `config/services.yaml`
- All classes in `src/` are automatically registered as services
- Service IDs are fully-qualified class names

## Migration from ZF2

### Legacy Application Structure
The ZF2 version (`../wcr-zf2`) has these modules that need migration:
- **Application**: Core (ACL, maintenance mode, routing)
- **Player**: Player management, profiles, comparisons, statistics
- **Score**: Score tracking and management
- **Ranking**: Ranking calculations and displays
- **Country**: Country/zone management
- **Zone**: Geographic zone handling
- **News**: News content management
- **Vids**: Video content management
- **Admin**: Administrative functionality
- **Log**: Application logging

### Key ZF2 Features to Migrate

**ACL System** (ZF2: `module/Application/Module.php:68-106`):
- Role-based access control with `guest` and `admin` roles
- Currently route-based, needs conversion to Symfony Security voters/attributes
- Admin status stored in session

**Database Stored Procedures** (`../wcr-zf2/routines.sql`):
- MySQL stored procedures for ranking calculations (`avg_percent`, `avg_pos`)
- These will need to be preserved and potentially wrapped in Doctrine custom functions

**Maintenance Mode** (ZF2: controlled via config):
- Returns HTTP 503 with static HTML when enabled
- Should be migrated to Symfony event listener or middleware

### Migration Strategy
When migrating a ZF2 module to Symfony:
1. **Entities**: ZF2 models → Doctrine entities in `src/Entity/`
2. **Controllers**: ZF2 controllers → Symfony controllers in `src/Controller/`
3. **Routes**: ZF2 `module.config.php` routes → `config/routes.yaml` or annotations
4. **Forms**: ZF2 forms → Symfony Form components in `src/Form/`
5. **Views**: ZF2 `.phtml` → Twig templates in `templates/`
6. **Services**: ZF2 service manager → Symfony DI container autowiring

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
- **Legacy Reference**: Always refer to `../wcr-zf2/CLAUDE.md` for understanding the original business logic
- **Database Compatibility**: Must maintain compatibility with existing MySQL database structure and stored procedures
- **Turbo Forms**: Add `data-turbo="false"` to forms that have issues with Symfony UX Turbo intercepting submissions
