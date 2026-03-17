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
│   ├── Service/                 # Business logic layer
│   │   └── Portfolio/           # Portfolio service group
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
```

### Code style
```bash
composer cs-check    # Check PSR-12
composer cs-fix      # Fix PSR-12 violations
```

### Static analysis
```bash
composer stan        # PHPStan
composer psalm       # Psalm
```

### Run application
```bash
./bin/skill
./bin/skill portfolio:positions
./bin/skill --help
```

## Architecture Patterns

### Three-Layer Architecture

1. **Command** - Console commands, only output formatting
2. **Service** - Business logic, data preparation for commands
3. **Component** - Raw API calls, DTOs from API responses

### Service Layer
```
src/Service/{Group}/
├── Dto/                          # View DTOs for commands
├── {Group}ServiceInterface.php   # Interface
└── {Group}Service.php            # Implementation
```

### API Components
Each API service follows this structure:
- `*ServiceComponentInterface.php` - Interface (one method per API endpoint)
- `*ServiceComponent.php` - Implementation
- `Dto/` - API response DTOs
- `Mapper/` - Response mappers (JSON → DTO)
- `Request/` - Request DTOs (optional)
- `Enum/` - Enums (optional)

### Dependency Injection
- All services registered in `config/services.yaml`
- Autowiring enabled
- Parameters bound via `$token`, `$accountId`, `$baseUrl`
- DTOs excluded from autowiring

## Environment Variables

Required in `.env` or `.env.local`:
```
TINVEST_TOKEN=your-api-token
TINVEST_ACCOUNT_ID=your-account-id
TINVEST_BASE_URL=https://invest-public-api.tbank.ru/rest/
LOG_LEVEL=debug
```

## API Details

- Base URL: `https://invest-public-api.tbank.ru/rest/`
- Content-Type: `application/json`
- REST API (not gRPC)

## When Making Changes

### Adding new API service
1. Create service directory under `src/Component/TInvest/`
2. Create Interface, Component, DTOs, Mappers
3. Register in `config/services.yaml`
4. Add unit tests in `tests/Component/TInvest/*/Mapper/`

### Adding new console command
1. Create service in `src/Service/{Group}/`
2. Create command in `src/Command/`
3. Register command in `bin/skill`

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

Services map to Tinkoff Invest API endpoints:
- `UsersService` → `tinkoff.public.invest.api.contract.v1.UsersService`
- `OperationsService` → `tinkoff.public.invest.api.contract.v1.OperationsService`
- `InstrumentsService` → `tinkoff.public.invest.api.contract.v1.InstrumentsService`
- `OrdersService` → `tinkoff.public.invest.api.contract.v1.OrdersService`
