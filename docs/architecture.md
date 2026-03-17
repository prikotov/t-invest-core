# Архитектура советника

## Общая схема

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              CLI Layer                                       │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐              │
│  │ portfolio:*     │  │ analyze:*       │  │ screen:*        │              │
│  │ Commands        │  │ Commands        │  │ Commands        │              │
│  └────────┬────────┘  └────────┬────────┘  └────────┬────────┘              │
│           │                    │                    │                        │
└───────────┼────────────────────┼────────────────────┼────────────────────────┘
            │                    │                    │
            ▼                    ▼                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                            Service Layer                                     │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐              │
│  │ Portfolio       │  │ Technical       │  │ Fundamental     │              │
│  │ AnalysisService │  │ AnalysisService │  │ AnalysisService │              │
│  └────────┬────────┘  └────────┬────────┘  └────────┬────────┘              │
│           │                    │                    │                        │
│  ┌────────┴────────────────────┴────────────────────┴────────┐              │
│  │                    RebalancingService                      │              │
│  │              (генерация рекомендаций)                       │              │
│  └─────────────────────────────┬──────────────────────────────┘              │
│                                │                                             │
└────────────────────────────────┼─────────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           API Component Layer                                │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐              │
│  │ Operations      │  │ MarketData      │  │ Instruments     │              │
│  │ ServiceComponent│  │ ServiceComponent│  │ ServiceComponent│              │
│  │                 │  │                 │  │                 │              │
│  │ • getPortfolio  │  │ • getCandles    │  │ • getFundamenta │              │
│  │ • getOperations │  │ • getLastPrices │  │ • getDividends  │              │
│  └────────┬────────┘  └────────┬────────┘  └────────┬────────┘              │
│           │                    │                    │                        │
└───────────┼────────────────────┼────────────────────┼────────────────────────┘
            │                    │                    │
            ▼                    ▼                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           T-Invest API (read-only)                           │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  HTTP Client (Guzzle) → https://invest-public-api.tinkoff.ru       │    │
│  │                                                                      │    │
│  │  Используются ТОЛЬКО read-endpoint'ы:                               │    │
│  │  • /v3/broker/accounts/{id}/portfolio                               │    │
│  │  • /v1/marketdata/candles                                           │    │
│  │  • /v1/marketdata/last-prices                                       │    │
│  │  • /v2/operations                                                   │    │
│  │  • /v1/instruments/*                                                │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────┘
```

**Важно:** Советник использует только read-only доступ к API. Все сделки исполняются вручную.

---

## Поток данных

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                          ПОТОК АНАЛИЗА                                        │
└──────────────────────────────────────────────────────────────────────────────┘

1. СБОР ДАННЫХ (read-only)
   ┌────────────────────────────────────────────────────────────────────────┐
   │                                                                         │
   │   T-Invest API                                                          │
   │   ├── getPortfolio() ─────────────► PortfolioDto                        │
   │   │   └── positions[], totalAmount, expectedYield                       │
   │   │                                                                     │
   │   ├── getLastPrices() ───────────► LastPriceDto[]                       │
   │   │   └── текущие цены                                                 │
   │   │                                                                     │
   │   ├── getCandles() ──────────────► CandleDto[][]                        │
   │   │   └── исторические свечи (30-200 дней)                              │
   │   │                                                                     │
   │   ├── getAssetFundamentals() ───► FundamentalDto[]                      │
   │   │   └── P/E, P/B, ROE, Debt/Equity                                    │
   │   │                                                                     │
   │   └── getDividends() ────────────► DividendDto[]                        │
   │       └── дивиденды, даты реестров                                      │
   │                                                                         │
   └────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
2. АНАЛИЗ
   ┌────────────────────────────────────────────────────────────────────────┐
   │                                                                         │
   │   PortfolioAnalysisService                                             │
   │   ├── calculateAllocation() ────► Секторное/валютное распределение     │
   │   ├── calculateReturns() ───────► Доходность (общая, годовая)          │
   │   ├── calculateRisk() ──────────► Волатильность, Sharpe, MaxDD         │
   │   └── calculateDeviations() ────► Отклонения от цели                    │
   │                                                                         │
   │   TechnicalAnalysisService                                             │
   │   ├── calculateSMA() ───────────► SMA20, SMA50, SMA200                 │
   │   ├── calculateEMA() ───────────► EMA12, EMA26                         │
   │   ├── calculateRSI() ───────────► RSI (14)                             │
   │   ├── calculateMACD() ──────────► MACD, Signal, Histogram              │
   │   └── determineTrend() ─────────► BULLISH/BEARISH/SIDEWAYS             │
   │                                                                         │
   │   FundamentalAnalysisService                                           │
   │   ├── analyzeValuation() ───────► P/E vs Industry, P/B vs History      │
   │   ├── analyzeDividends() ───────► Yield, Payout, Growth                │
   │   └── calculateScore() ─────────► Общий скоринг (0-100)                │
   │                                                                         │
   └────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
3. ГЕНЕРАЦИЯ РЕКОМЕНДАЦИЙ
   ┌────────────────────────────────────────────────────────────────────────┐
   │                                                                         │
   │   RebalancingService                                                   │
   │   │                                                                     │
   │   ├── Вход:                                                             │
   │   │   ├── currentAllocation (текущее распределение)                    │
   │   │   ├── targetAllocation (целевое распределение)                     │
   │   │   ├── threshold (порог, напр. 5%)                                  │
   │   │   └── signals (сигналы от анализа)                                 │
   │   │                                                                     │
   │   ├── Логика:                                                           │
   │   │   ├── 1. Найти позиции с отклонением > threshold                   │
   │   │   ├── 2. Рассчитать количество лотов                               │
   │   │   ├── 3. Оценить момент (тех + фунд)                               │
   │   │   └── 4. Сформулировать рекомендации                               │
   │   │                                                                     │
   │   └── Выход: RecommendationsDto                                        │
   │       ├── actions[] (рекомендуемые действия)                           │
   │       ├── reasoning[] (обоснование для каждого)                        │
   │       └── warnings[] (предупреждения)                                  │
   │                                                                         │
   └────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
4. ВЫВОД
   ┌────────────────────────────────────────────────────────────────────────┐
   │                                                                         │
   │   Console Output (table/json/csv)                                      │
   │   │                                                                     │
   │   ├── Таблица с метриками портфеля                                     │
   │   ├── Отклонения от целевого распределения                             │
   │   ├── Рекомендуемые действия (купить/продать)                         │
   │   ├── Обоснование (тех + фунд анализ)                                  │
   │   └── Информация для ручного исполнения                               │
   │       (тикер, количество лотов, цена)                                  │
   │                                                                         │
   └────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
                        ВЫ ИСПОЛНЯЕТЕ ВРУЧНУЮ
                        В ПРИЛОЖЕНИИ БРОКЕРА
```

---

## Компоненты

### API Components (read-only)

| Компонент | Методы | Данные |
|-----------|--------|--------|
| **OperationsService** | `getPortfolio()` | Позиции, стоимость, доходность |
| | `getOperations()` | История сделок, дивиденды |
| **MarketDataService** | `getCandles()` | Исторические цены, объёмы |
| | `getLastPrices()` | Текущие цены |
| **InstrumentsService** | `getAssetFundamentals()` | P/E, P/B, ROE |
| | `getDividends()` | Дивиденды, даты |
| | `getShares()` | Информация об акциях |

### Service Layer

| Сервис | Ответственность |
|--------|-----------------|
| **PortfolioAnalysisService** | Метрики портфеля, распределение, отклонения |
| **TechnicalAnalysisService** | Индикаторы, тренды, сигналы |
| **FundamentalAnalysisService** | Оценка, мультипликаторы, скоринг |
| **RebalancingService** | Генерация рекомендаций по ребалансировке |

---

## Модели портфелей

### Структура конфигурации

```yaml
# config/allocations/balanced.yaml
name: "Сбалансированный"
description: "Средний риск, средняя доходность"
rebalance_threshold: 5  # %

target_allocation:
  by_class:
    stocks:
      weight: 50
      min: 40
      max: 60
    bonds:
      weight: 30
      min: 20
      max: 40
    etf:
      weight: 15
      min: 10
      max: 25
    cash:
      weight: 5
      min: 3
      max: 10

  by_sector:
    financial:
      max: 35
    energy:
      max: 25
    technology:
      max: 20

  by_currency:
    RUB:
      min: 50
      max: 80
    USD:
      min: 10
      max: 30

  positions:
    - ticker: SBER
      weight: 10
      min: 5
      max: 15
    - ticker: LKOH
      weight: 10
    - ticker: SU26238RMFS
      weight: 15
    - ticker: FXUS
      weight: 10
```

---

## Кэширование

| Данные | TTL | Причина |
|--------|-----|---------|
| Портфель | 1 минута | Часто меняется |
| Последние цены | 10 секунд | Актуальность |
| Свечи | 1 час | История не меняется |
| Фундаментальные данные | 24 часа | Редко меняется |
| Дивиденды | 1 час | Обновления |

---

## Лимиты API

```
┌─────────────────────────────────────────────────────────────┐
│                    RATE LIMITS                               │
├─────────────────────────────────────────────────────────────┤
│ Запросов в минуту:     60                                    │
│ Запросов в день:       10 000                                │
├─────────────────────────────────────────────────────────────┤
│                    MONITORING                                │
├─────────────────────────────────────────────────────────────┤
│ • X-RateLimit-Remaining в заголовках                        │
│ • Логирование приближения к лимиту                          │
│ • Автоматический backoff при 429                            │
└─────────────────────────────────────────────────────────────┘
```

---

## Пример рекомендаций

### Выход RecommendationsDto

```php
final class RecommendationsDto
{
    /**
     * @param list<RecommendationActionDto> $actions
     * @param list<string> $warnings
     */
    public function __construct(
        public readonly array $actions,
        public readonly array $warnings,
        public readonly float $totalSellAmount,
        public readonly float $totalBuyAmount,
        public readonly float $estimatedCommissions,
    ) {}
}

final class RecommendationActionDto
{
    public function __construct(
        public readonly string $ticker,
        public readonly string $figi,
        public readonly string $action,        // BUY | SELL
        public readonly int $shares,
        public readonly int $lots,
        public readonly float $estimatedPrice,
        public readonly float $estimatedAmount,
        public readonly string $reason,        // overweight | underweight
        public readonly string $timing,        // good | neutral | bad
        public readonly string $technicalSummary,
        public readonly string $fundamentalSummary,
    ) {}
}
```

### Пример вывода

```
РЕКОМЕНДАЦИИ:

1. ПРОДАТЬ SBER
   Количество: 200 акций (20 лотов)
   Цена: ~₽ 267.50
   Сумма: ~₽ 53 500
   
   Причина: Перевес +4% от цели
   Момент: Хороший (бычий тренд, RSI 68)
   Фундаментально: Акция недооценена, но продажа оправдана для диверсификации
   
   Для исполнения: В приложении брокера продать SBER, 20 лотов

2. КУПИТЬ SU26238RMFS
   Количество: 50 облигаций
   Цена: ~₽ 1 070
   Сумма: ~₽ 53 500
   
   Причина: Недовес -5% от цели
   Доходность к погашению: ~7.2%
   
   Для исполнения: В приложении брокера купить ОФЗ 26238, 50 шт.

ПРЕДУПРЕЖДЕНИЯ:
⚠️ SBER: RSI приближается к перекупленности
⚠️ После продажи SBER будет налог с прибыли (позиция в плюсе)
```
