# Ребалансировка портфеля - Постановка задач

## Цель
Аналитический советник для ребалансировки портфеля. **Только анализ и рекомендации**, все сделки исполняются вручную.

---

## Этап 1: Сбор данных (read-only)

### 1.1 Текущее состояние портфеля
**Статус: ✅ Реализовано**

| Метод API | Endpoint | Назначение |
|-----------|----------|------------|
| `getPortfolio()` | `/v3/broker/accounts/{account_id}/portfolio` | Позиции, стоимости, доходность |

**Задачи:**
- [ ] Расчёт доли каждого инструмента в портфеле (%)
- [ ] Расчёт секториального распределения
- [ ] Расчёт валютного распределения

---

### 1.2 Исторические цены и свечи
**Статус: ❌ Не реализовано**

| Метод API | Endpoint | Назначение |
|-----------|----------|------------|
| `getCandles()` | `/v1/marketdata/candles` | Исторические свечи |
| `getLastPrices()` | `/v1/marketdata/last-prices` | Последние цены |

**Зачем нужно:**
- Анализ трендов
- Расчёт волатильности
- Технические индикаторы (MA, RSI, MACD)

**Задачи:**
- [ ] Создать `MarketDataService` компонент
- [ ] Реализовать DTO для свечей
- [ ] Реализовать маппер

---

### 1.3 История операций
**Статус: ❌ Не реализовано**

| Метод API | Endpoint | Назначение |
|-----------|----------|------------|
| `getOperations()` | `/v2/operations` | История операций |

**Зачем нужно:**
- Анализ эффективности решений
- Расчёт реализованной прибыли
- История дивидендов

**Задачи:**
- [ ] Добавить `getOperations()` в OperationsService
- [ ] Создать DTO для операций

---

### 1.4 Фундаментальные показатели
**Статус: ⚠️ Частично реализовано**

| Метод API | Endpoint | Назначение |
|-----------|----------|------------|
| `getAssetFundamentals()` | `/v1/instruments/assets/fundamentals` | ✅ Реализовано |
| `getDividends()` | `/v1/instruments/dividends` | ✅ Реализовано |
| `getShares()` | `/v1/instruments/shares` | ❌ Не реализовано |

**Задачи:**
- [ ] Добавить `getShares()` - информация об акциях (сектора, лоты)

---

## Этап 2: Аналитические сервисы

### 2.1 Сервис анализа портфеля
**Приоритет: Высокий**

```
src/Service/PortfolioAnalysis/
├── Dto/
│   ├── PortfolioMetricsDto.php
│   ├── PositionAnalysisDto.php
│   └── AllocationDto.php
├── PortfolioAnalysisServiceInterface.php
└── PortfolioAnalysisService.php
```

**Функционал:**
- [ ] Расчёт долей (% от портфеля)
- [ ] Расчёт доходности
- [ ] Расчёт риска (волатильность)
- [ ] Секторальное/валютное распределение
- [ ] Отклонения от целевой структуры

---

### 2.2 Сервис технического анализа
**Приоритет: Средний**

```
src/Service/TechnicalAnalysis/
├── Dto/
│   ├── TrendAnalysisDto.php
│   └── SignalDto.php
├── TechnicalAnalysisServiceInterface.php
└── TechnicalAnalysisService.php
```

**Функционал:**
- [ ] Moving Averages (SMA, EMA)
- [ ] RSI
- [ ] MACD
- [ ] Определение тренда
- [ ] Торговые сигналы

---

### 2.3 Сервис фундаментального анализа
**Приоритет: Средний**

```
src/Service/FundamentalAnalysis/
├── Dto/
│   ├── ValuationDto.php
│   └── DividendAnalysisDto.php
├── FundamentalAnalysisServiceInterface.php
└── FundamentalAnalysisService.php
```

**Функционал:**
- [ ] Анализ мультипликаторов (P/E, P/B)
- [ ] Анализ дивидендов
- [ ] Скоринг (0-100)

---

### 2.4 Сервис рекомендаций
**Приоритет: Высокий**

```
src/Service/Rebalancing/
├── Dto/
│   ├── TargetAllocationDto.php
│   ├── RecommendationActionDto.php
│   └── RecommendationsDto.php
├── RebalancingServiceInterface.php
└── RebalancingService.php
```

**Функционал:**
- [ ] Сравнение текущего и целевого распределения
- [ ] Генерация рекомендаций (купить/продать)
- [ ] Расчёт количества лотов
- [ ] Оценка момента (тех + фунд)
- [ ] Обоснование для каждого действия

---

## Этап 3: Консольные команды

### Команды анализа
- [ ] `portfolio:analyze` - анализ портфеля
- [ ] `portfolio:report --period=week|month` - отчёт за период
- [ ] `portfolio:rebalance:plan --target=balanced` - рекомендации

### Команды анализа инструментов
- [ ] `analyze:technical --ticker=XXX` - технический анализ
- [ ] `analyze:fundamental --ticker=XXX` - фундаментальный анализ
- [ ] `analyze:quick --ticker=XXX` - быстрый обзор

### Команды скрининга
- [ ] `screen:stocks` - скрининг акций по критериям

---

## Этап 4: Конфигурация моделей портфелей

```yaml
# config/allocations/balanced.yaml
name: "Сбалансированный"
rebalance_threshold: 5

target_allocation:
  by_class:
    stocks: 50
    bonds: 30
    etf: 15
    cash: 5

  positions:
    - ticker: SBER
      weight: 10
    - ticker: LKOH
      weight: 10
    # ...
```

---

## Приоритеты

| Приоритет | Задача | Сложность |
|-----------|--------|-----------|
| P0 | getCandles, getLastPrices | Средняя |
| P0 | getOperations | Низкая |
| P1 | PortfolioAnalysisService | Высокая |
| P1 | RebalancingService | Высокая |
| P1 | Команды portfolio:* | Средняя |
| P2 | TechnicalAnalysisService | Высокая |
| P2 | FundamentalAnalysisService | Средняя |
| P3 | Команды analyze:*, screen:* | Средняя |

---

## MVP (минимальная версия)

1. [ ] `getCandles()` - исторические цены
2. [ ] `getOperations()` - история операций
3. [ ] `PortfolioAnalysisService` - базовые метрики
4. [ ] `RebalancingService` - расчёт отклонений и рекомендации
5. [ ] `portfolio:analyze` - команда
6. [ ] `portfolio:rebalance:plan` - команда

**Время MVP:** ~20 часов
