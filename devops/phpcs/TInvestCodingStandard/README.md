# PHPCS Custom Standard

## Структура

```
devops/phpcs/TInvestCodingStandard/
├── ruleset.xml
└── Sniffs/
    └── Structure/
        └── DtoStructureSniff.php
```

## Правила

### DtoStructureSniff

Проверяет что все DTO классы (`*Dto.php`) соответствуют структуре:

1. **`final readonly class`** - класс должен быть объявлен как final readonly
2. **Только конструктор** - единственный метод в классе это `__construct()`
3. **Пустое тело конструктора** - никаких операторов в теле конструктора
4. **Promoted readonly properties** - свойства объявляются как параметры конструктора с модификатором `readonly`
5. **Без констант и traits** - DTO не должны содержать константы или использовать трейты

### Пример правильного DTO

```php
<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\Shared\Dto;

final readonly class MoneyDto
{
    public function __construct(
        public readonly string $currency,
        public readonly float $value,
        public readonly int $units,
        public readonly int $nano
    ) {
    }
}
```

## Использование

```bash
# Проверка
composer cs-check

# Автоматическое исправление (где возможно)
composer cs-fix
```

## Применяется к

- `src/Component/*/Dto/*.php`
- `src/Service/*/Dto/*.php`
