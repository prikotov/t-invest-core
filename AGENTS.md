# Инструкции для AI-агента

## Описание

CLI утилита для работы с T-Invest API (Т-Инвестиции).

## Технологии

- PHP 8.4+
- Symfony Console, DependencyInjection
- Guzzle HTTP Client
- Monolog

## Структура

```
├── bin/skill                    # CLI entry point
├── config/
│   ├── container.php
│   └── services.yaml
├── src/
│   ├── Command/
│   ├── Service/Portfolio/
│   └── Component/TInvest/
│       ├── Shared/
│       ├── UsersService/
│       ├── OperationsService/
│       ├── InstrumentsService/
│       └── OrdersService/
└── tests/
```

## Команды

```bash
./bin/skill portfolio:positions
```

## Архитектура

1. **Command** - форматирование вывода
2. **Service** - бизнес-логика
3. **Component** - API вызовы, DTO

## API-компоненты

Каждый сервис:
- `*ServiceComponentInterface.php`
- `*ServiceComponent.php`
- `Dto/`, `Mapper/`, `Enum/`

## Разработка

```bash
composer cs-check && composer stan && composer psalm && composer test
```

## Стиль кода

- `declare(strict_types=1);`
- readonly-свойства в DTO
- Без комментариев

## API Reference

https://russianinvestments.github.io/investAPI/swagger-ui/openapi.yaml
