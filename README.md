# T-Invest CLI

CLI утилита для работы с T-Invest API (Т-Инвестиции).

## Установка

```bash
composer require prikotov/t-invest-core:@dev
```

## Конфигурация

Создайте `.env.local`:

```env
TINKOFF_TOKEN=your_token
TINKOFF_ACCOUNT_ID=your_account_id
```

## Использование

```bash
./vendor/bin/skill portfolio:positions
./vendor/bin/skill --help
```

## Команды

| Команда | Описание |
|---------|----------|
| `portfolio:positions` | Позиции портфеля |

## Разработка

```bash
composer install
composer test
composer cs-check
composer stan
composer psalm
```

## API Reference

OpenAPI: https://russianinvestments.github.io/investAPI/swagger-ui/openapi.yaml

## Лицензия

MIT
