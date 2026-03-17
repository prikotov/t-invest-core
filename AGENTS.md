# Инструкции для AI-агента

## Описание проекта

T-Invest Skill - консольное приложение на PHP для работы с API Тинькофф Инвестиций.

## Технологии

- PHP 8.1+
- Symfony Console (CLI)
- Symfony DependencyInjection (DI)
- Guzzle HTTP Client
- Monolog (логирование)
- PHPUnit (тестирование)

## Структура проекта

```
├── bin/skill                    # Точка входа CLI
├── config/
│   ├── container.php            # Инициализация DI-контейнера
│   └── services.yaml            # Определение сервисов
├── src/
│   ├── Command/                 # Консольные команды
│   ├── Service/                 # Слой бизнес-логики
│   │   └── Portfolio/           # Группа сервисов портфеля
│   └── Component/TInvest/       # API-компоненты
│       ├── Shared/              # Общие DTO, фабрики, хелперы
│       ├── UsersService/        # API пользователей
│       ├── OperationsService/   # API портфеля/операций
│       ├── InstrumentsService/  # API инструментов/дивидендов
│       └── OrdersService/       # API заявок
├── tests/                       # Тесты PHPUnit
├── .env                         # Конфигурация (в репозитории)
└── .env.local                   # Локальные переопределения (игнорируется git)
```

## Команды

### Установка зависимостей
```bash
composer install
```

### Тесты
```bash
composer test
```

### Код-стайл
```bash
composer cs-check    # Проверка PSR-12
composer cs-fix      # Исправление нарушений PSR-12
```

### Статический анализ
```bash
composer stan        # PHPStan
composer psalm       # Psalm
```

### Запуск приложения
```bash
./bin/skill
./bin/skill portfolio:positions
./bin/skill --help
```

## Архитектура

### Трёхуровневая архитектура

1. **Command** - консольные команды, только форматирование вывода
2. **Service** - бизнес-логика, подготовка данных для команд
3. **Component** - вызовы API, DTO из ответов API

### Слой сервисов
```
src/Service/{Group}/
├── Dto/                          # View-DTO для команд
├── {Group}ServiceInterface.php   # Интерфейс
└── {Group}Service.php            # Реализация
```

### API-компоненты
Каждый API-сервис следует структуре:
- `*ServiceComponentInterface.php` - Интерфейс (один метод на endpoint API)
- `*ServiceComponent.php` - Реализация
- `Dto/` - DTO ответов API
- `Mapper/` - Мапперы ответов (JSON → DTO)
- `Request/` - DTO запросов (опционально)
- `Enum/` - Перечисления (опционально)

### Dependency Injection
- Все сервисы регистрируются в `config/services.yaml`
- Autowiring включён
- Параметры привязываются через `$token`, `$accountId`, `$baseUrl`
- DTO исключены из autowiring

## При внесении изменений

### Добавление нового API-сервиса
1. Создать директорию сервиса в `src/Component/TInvest/`
2. Создать Interface, Component, DTOs, Mappers
3. Зарегистрировать в `config/services.yaml`
4. Добавить тесты в `tests/Component/TInvest/*/Mapper/`

### Добавление новой консольной команды
1. Создать сервис в `src/Service/{Group}/`
2. Создать команду в `src/Command/`
3. Зарегистрировать команду в `bin/skill`

### Перед коммитом
1. `composer cs-check` - исправить нарушения
2. `composer stan` - исправить ошибки PHPStan
3. `composer psalm` - исправить ошибки Psalm
4. `composer test` - убедиться что тесты проходят

### Стиль кода
- Без комментариев в коде, если явно не запрошено
- Использовать `declare(strict_types=1);`
- Использовать readonly-свойства в DTO
- Использовать возможности PHP 8.1+ (enums, readonly и т.д.)

## Справочник API

Сервисы соответствуют эндпоинтам API Тинькофф Инвестиций:
- `UsersService` → `tinkoff.public.invest.api.contract.v1.UsersService`
- `OperationsService` → `tinkoff.public.invest.api.contract.v1.OperationsService`
- `InstrumentsService` → `tinkoff.public.invest.api.contract.v1.InstrumentsService`
- `OrdersService` → `tinkoff.public.invest.api.contract.v1.OrdersService`
