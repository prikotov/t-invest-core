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
├── bin/t-invest                 # CLI entry point
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
./bin/t-invest portfolio:show
./bin/t-invest market:prices SBER GAZP
./bin/t-invest market:candles --ticker SBER
./bin/t-invest market:orderbook --ticker SBER
./bin/t-invest instruments:search SBER
./bin/t-invest instruments:fundamentals SBER
./bin/t-invest operations:history
./bin/t-invest events:dividends --ticker SBER
./bin/t-invest events:bonds --ticker SU26238RMFS
./bin/t-invest events:reports --ticker SBER
```

## Форматы вывода

Все команды поддерживают `--format`:
- `md` (по умолчанию) - Markdown таблица
- `json` - JSON массив объектов
- `csv` - CSV с заголовком
- `text` - ASCII-таблица

```bash
./bin/t-invest portfolio:show              # md
./bin/t-invest portfolio:show --format csv
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
