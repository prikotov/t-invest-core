# AI Agent Instructions

## Project Overview

T-Invest Skill - PHP console application for Tinkoff Invest API integration.

## Tech Stack

- PHP 8.1+
- Symfony Console (CLI)
- Symfony DependencyInjection (DI)
- Guzzle HTTP Client
- Monolog (Logging)
- PHPUnit (Testing)

## Project Structure

```
├── bin/skill                    # CLI entry point
├── config/
│   ├── container.php            # DI container bootstrap
│   └── services.yaml            # Service definitions
├── src/
│   ├── Command/                 # Console commands
│   └── Component/TInvest/       # API components
│       ├── Shared/              # Shared DTOs, Factories, Helpers
│       ├── UsersService/        # Users API
│       ├── OperationsService/   # Portfolio/Operations API
│       ├── InstrumentsService/  # Instruments/Dividends API
│       └── OrdersService/       # Orders API
├── tests/                       # PHPUnit tests
├── .env                         # Environment config (committed)
└── .env.local                   # Local overrides (gitignored)
```

## Commands

### Install dependencies
```bash
composer install
```

### Run tests
```bash
composer test
# or
./vendor/bin/phpunit
```

### Code style
```bash
composer cs-check    # Check PSR-12
composer cs-fix      # Fix PSR-12 violations
```

### Static analysis
```bash
composer stan
# or
./vendor/bin/phpstan analyse

composer psalm
# or
./vendor/bin/psalm
```

### Run application
```bash
./bin/skill
./bin/skill main
./bin/skill --help
```

## Architecture Patterns

### Service Components
Each API service follows this structure:
- `*ServiceComponentInterface.php` - Interface
- `*ServiceComponent.php` - Implementation
- `Dto/` - Data Transfer Objects
- `Mapper/` - Response mappers (JSON → DTO)
- `Request/` - Request DTOs (optional)
- `Enum/` - Enums (optional)

### Dependency Injection
- All services registered in `config/services.yaml`
- Autowiring enabled
- Parameters bound via `$token`, `$accountId`, `$baseUrl`

### Naming Conventions
- PSR-4 autoloading: `TInvest\Skill\*`
- DTOs: `*Dto.php` (in Dto/ folder)
- Mappers: `*Mapper.php` (in Mapper/ folder)
- Components: `*Component.php`

## Environment Variables

Required in `.env` or `.env.local`:
```
TINVEST_TOKEN=your-api-token
TINVEST_ACCOUNT_ID=your-account-id
TINVEST_BASE_URL=https://invest-public-api.tinkoff.ru/
LOG_LEVEL=debug
```

## When Making Changes

### Adding new API service
1. Create service directory under `src/Component/TInvest/`
2. Create Interface, Component, DTOs, Mappers
3. Register in `config/services.yaml`
4. Add unit tests in `tests/Component/TInvest/*/Mapper/`

### Before committing
1. Run `composer cs-check` - fix violations
2. Run `composer stan` - fix PHPStan errors
3. Run `composer psalm` - fix Psalm errors
4. Run `composer test` - ensure tests pass

### Code style notes
- No comments in code unless explicitly requested
- Use `declare(strict_types=1);`
- Use readonly properties in DTOs
- Use PHP 8.1+ features (enums, readonly, etc.)

## API Reference

Services map to Tinkoff Invest API gRPC endpoints:
- `UsersService` → `tinkoff.public.invest.api.contract.v1.UsersService`
- `OperationsService` → `tinkoff.public.invest.api.contract.v1.OperationsService`
- `InstrumentsService` → `tinkoff.public.invest.api.contract.v1.InstrumentsService`
- `OrdersService` → `tinkoff.public.invest.api.contract.v1.OrdersService`
