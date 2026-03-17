# Руководство по использованию

## Установка и настройка

### Требования
- PHP 8.4+
- Composer
- Токен T-Invest API (достаточно read-only)

### Установка

```bash
git clone <repo>
cd t-invest-core
composer install
```

### Конфигурация

```bash
cp .env.example .env.local
nano .env.local
```

```env
# .env.local
TINKOFF_TOKEN=your_token_here
TINKOFF_ACCOUNT_ID=your_account_id
TINKOFF_BASE_URL=https://invest-public-api.tinkoff.ru
```

---

## Быстрый старт

### 1. Проверка подключения

```bash
./bin/skill portfolio:positions
```

### 2. Анализ портфеля

```bash
./bin/skill portfolio:analyze
```

### 3. Рекомендации по ребалансировке

```bash
./bin/skill portfolio:rebalance:plan --target=balanced
```

---

## Регулярный workflow

### Еженедельно (5 минут)

```bash
# Отчёт за неделю + сигналы
./bin/skill portfolio:report --period=week
```

Проверить:
- Есть ли сигналы (RSI, события)
- Есть ли отклонения > 5%

### Ежемесячно (15 минут)

```bash
# 1. Полный анализ
./bin/skill portfolio:analyze

# 2. Рекомендации
./bin/skill portfolio:rebalance:plan --target=balanced

# 3. Детальный анализ позиций для сделок
./bin/skill analyze:quick --ticker=SBER
./bin/skill analyze:quick --ticker=SU26238RMFS
```

После этого — принять решение и исполнить сделки вручную в приложении брокера.

---

## Анализ кандидатов для покупки

### Скрининг

```bash
# Недооценённые с дивидендами
./bin/skill screen:stocks --max-pe=6 --min-dividend=8

# По сектору
./bin/skill screen:stocks --sector=financial --min-dividend=7

# Акции роста
./bin/skill screen:stocks --min-revenue-growth=15 --min-roe=15
```

### Детальный анализ

```bash
./bin/skill analyze:quick --ticker=SBER
./bin/skill analyze:technical --ticker=SBER
./bin/skill analyze:fundamental --ticker=SBER
```

---

## Принятие решений

### Алгоритм

```
1. ПРОВЕРИТЬ ОТКЛОНЕНИЕ
   │
   ├─► < 5%  ──► НЕ ДЕЙСТВОВАТЬ
   │
   └─► ≥ 5%  ──► Продолжить
                  │
                  ▼
2. ПРОВЕРИТЬ АНАЛИЗ
   │
   ├─► Технический: тренд за сделку?
   ├─► Фундаментальный: скоринг?
   │
   └─► Оценить момент
                  │
                  ▼
3. ПРИНЯТЬ РЕШЕНИЕ
   │
   └─► Исполнить вручную в приложении брокера
```

### Пример: Решение о продаже

```bash
# 1. Проверить отклонение
./bin/skill portfolio:analyze
# → SBER = 14%, цель = 10%, отклонение = +4%

# 2. Технический анализ
./bin/skill analyze:technical --ticker=SBER
# → Бычий тренд, RSI = 68 — хороший момент для продажи

# 3. Фундаментальный анализ
./bin/skill analyze:fundamental --ticker=SBER
# → Недооценена, но для диверсификации продажа ОК

# 4. Решение: Продать в приложении брокера
```

---

## Команды

### Анализ портфеля

```bash
# Полный анализ
./bin/skill portfolio:analyze

# Отчёт за период
./bin/skill portfolio:report --period=week
./bin/skill portfolio:report --period=month

# Рекомендации по ребалансировке
./bin/skill portfolio:rebalance:plan --target=balanced
./bin/skill portfolio:rebalance:plan --config=my-portfolio.yaml
```

### Анализ инструментов

```bash
# Быстрый анализ
./bin/skill analyze:quick --ticker=SBER

# Технический анализ
./bin/skill analyze:technical --ticker=SBER

# Фундаментальный анализ
./bin/skill analyze:fundamental --ticker=SBER
```

### Скрининг

```bash
./bin/skill screen:stocks \
  --min-dividend=7 \
  --max-pe=8 \
  --sector=financial
```

### Форматы вывода

```bash
./bin/skill portfolio:analyze --format=table  # по умолчанию
./bin/skill portfolio:analyze --format=json
./bin/skill portfolio:analyze --format=csv
```

---

## Автоматизация

### Cron для еженедельного отчёта

```cron
# Каждое воскресенье в 18:00
0 18 * * 0 cd /path/to/t-invest-core && ./bin/skill portfolio:report --period=week | mail -s "Портфель" user@example.com
```

### Скрипт ежемесячного анализа

```bash
#!/bin/bash
# analyze-monthly.sh

DATE=$(date +%Y%m)
mkdir -p reports

echo "=== Анализ портфеля $DATE ==="

./bin/skill portfolio:analyze --format=json > "reports/analyze-$DATE.json"
./bin/skill portfolio:rebalance:plan --target=balanced > "reports/plan-$DATE.txt"

echo "Отчёты сохранены в reports/"
cat reports/plan-$DATE.txt
```

---

## Экспорт данных

```bash
# Для Excel
./bin/skill portfolio:positions --format=csv > positions.csv
./bin/skill portfolio:analyze --format=csv > analysis.csv

# Для Python
./bin/skill portfolio:analyze --format=json > portfolio.json
```

```python
# analysis.py
import json
import pandas as pd

with open('portfolio.json') as f:
    data = json.load(f)

df = pd.DataFrame(data['positions'])
print(df[['ticker', 'weight', 'return']])
```

---

## Устранение неполадок

### Ошибка авторизации

```
Error: Unauthorized (401)
```

→ Проверить токен в `.env.local`

### Rate limit

```
Error: Too Many Requests (429)
```

→ Подождать 1 минуту

### Нет данных

```
Error: Instrument not found
```

→ Проверить тикер, использовать `--figi` вместо `--ticker`

---

## Типичный сценарий

```
┌─────────────────────────────────────────────────────────────────┐
│  ЕЖЕНЕДЕЛЬНО                                                    │
│  ──────────                                                     │
│  ./bin/skill portfolio:report --period=week                     │
│                                                                  │
│  → Проверить сигналы                                            │
│  → Проверить отклонения                                         │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  ЕСЛИ ЕСТЬ ОТКЛОНЕНИЯ > 5%                                      │
│  ──────────────────────                                         │
│  ./bin/skill analyze:quick --ticker=XXX                         │
│                                                                  │
│  → Оценить момент                                               │
│  → Принять решение                                              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  ИСПОЛНЕНИЕ                                                     │
│  ──────────                                                     │
│  Открыть приложение брокера                                     │
│  Создать заявку вручную                                         │
│  (тикер, количество, цена — из рекомендаций)                    │
└─────────────────────────────────────────────────────────────────┘
```
