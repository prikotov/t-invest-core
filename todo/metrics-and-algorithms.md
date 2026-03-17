# Метрики и алгоритмы для ребалансировки

## 1. Метрики портфеля

### 1.1 Доходность

#### Общая доходность
```
Total Return = (CurrentValue - InitialValue) / InitialValue * 100%
```

#### Доходность позиции
```
Position Return = (CurrentPrice - AvgPrice) / AvgPrice * 100%
```

#### Годовая доходность (CAGR)
```
CAGR = (EndingValue / BeginningValue) ^ (1 / Years) - 1
```

#### Взвешенная по времени доходность (TWR)
```
TWR = ∏(1 + R_i) - 1
где R_i - доходность за период между вводами/выводами
```

---

### 1.2 Риск

#### Волатильность (Standard Deviation)
```
σ = √(Σ(R_i - R_avg)² / (n-1))
```
- R_i - дневная доходность
- R_avg - средняя дневная доходность
- n - количество периодов

#### Value at Risk (VaR) - 95%
```
VaR_95 = -μ + 1.65 * σ
```
- μ - средняя доходность
- σ - волатильность

#### Maximum Drawdown
```
MDD = (Peak - Trough) / Peak * 100%
```

#### Sharpe Ratio
```
Sharpe = (R_p - R_f) / σ_p
```
- R_p - доходность портфеля
- R_f - безрисковая ставка (например, ОФЗ)
- σ_p - волатильность портфеля

---

### 1.3 Диверсификация

#### Herfindahl-Hirschman Index (HHI)
```
HHI = Σ(w_i²)
где w_i - вес i-го актива
```
- HHI < 0.15 - высокая диверсификация
- 0.15 ≤ HHI < 0.25 - умеренная
- HHI ≥ 0.25 - низкая

#### Эффективное количество активов
```
N_effective = 1 / HHI
```

#### Корреляционная матрица
```
ρ_ij = Cov(R_i, R_j) / (σ_i * σ_j)
```

---

## 2. Технические индикаторы

### 2.1 Moving Averages

#### Simple Moving Average (SMA)
```
SMA_n = (P_1 + P_2 + ... + P_n) / n
```
- n = 20, 50, 200 (стандартные периоды)

#### Exponential Moving Average (EMA)
```
EMA_t = α * P_t + (1 - α) * EMA_{t-1}
α = 2 / (n + 1)
```

**Сигналы:**
- Цена > SMA200 - долгосрочный бычий тренд
- Цена < SMA200 - долгосрочный медвежий тренд
- SMA50 > SMA200 - Golden Cross (покупка)
- SMA50 < SMA200 - Death Cross (продажа)

---

### 2.2 Relative Strength Index (RSI)
```
RSI = 100 - 100 / (1 + RS)
RS = AvgGain / AvgLoss
```
- Период: 14 дней
- AvgGain = средняя прибыль за период
- AvgLoss = средний убыток за период

**Сигналы:**
- RSI > 70 - перекупленность (продажа)
- RSI < 30 - перепроданность (покупка)
- Дивергенция RSI и цены - разворот

---

### 2.3 MACD (Moving Average Convergence Divergence)
```
MACD = EMA12 - EMA26
Signal = EMA9(MACD)
Histogram = MACD - Signal
```

**Сигналы:**
- MACD > Signal - бычий сигнал
- MACD < Signal - медвежий сигнал
- Пересечение MACD и Signal - смена тренда

---

### 2.4 Bollinger Bands
```
Middle = SMA20
Upper = Middle + 2 * σ20
Lower = Middle - 2 * σ20
```

**Сигналы:**
- Цена > Upper - перекупленность
- Цена < Lower - перепроданность
- Сужение полос - ожидание сильного движения

---

### 2.5 Average True Range (ATR) - Волатильность
```
TR = max(High - Low, |High - PrevClose|, |Low - PrevClose|)
ATR = SMA14(TR)
```

Используется для:
- Размера стоп-лосса (например, 2 * ATR)
- Оценки текущей волатильности

---

## 3. Фундаментальные показатели

### 3.1 Мультипликаторы

| Мультипликатор | Формула | Интерпретация |
|----------------|---------|---------------|
| P/E | Price / EPS | Сколько лет окупится инвестиция |
| P/B | Price / Book Value | Цена к балансовой стоимости |
| P/S | Price / Revenue | Цена к выручке |
| EV/EBITDA | Enterprise Value / EBITDA | Стоимость компании к прибыли |
| Dividend Yield | Dividends / Price | Дивидендная доходность |

**Сравнение:**
- С историческими значениями компании
- С отраслью (секторальный медиана)
- С рынком в целом

---

### 3.2 Правило Грэма
```
P/E * P/B < 22.5
```
Если произведение меньше 22.5 - акция недооценена

---

### 3.3 Дивидендный анализ

#### Dividend Payout Ratio
```
Payout Ratio = Dividends / Net Income
```
- < 50% - консервативная политика
- 50-70% - нормальная политика
- > 70% - агрессивная, риск снижения

#### Dividend Growth Rate (CAGR дивидендов)
```
DGR = (D_n / D_0)^(1/n) - 1
```

#### Dividend Coverage Ratio
```
Coverage = EPS / DPS
```
- > 2 - безопасно
- 1.5-2 - нормально
- < 1.5 - риск снижения

---

## 4. Алгоритм ребалансировки

### 4.1 Определение отклонений
```php
function calculateDeviations(
    array $currentAllocation,  // ['SBER' => 0.25, 'GAZP' => 0.20, ...]
    array $targetAllocation    // ['SBER' => 0.20, 'GAZP' => 0.25, ...]
): array {
    $deviations = [];
    foreach ($targetAllocation as $ticker => $targetWeight) {
        $currentWeight = $currentAllocation[$ticker] ?? 0;
        $deviations[$ticker] = [
            'current' => $currentWeight,
            'target' => $targetWeight,
            'deviation' => $currentWeight - $targetWeight,
            'abs_deviation' => abs($currentWeight - $targetWeight),
        ];
    }
    return $deviations;
}
```

---

### 4.2 Генерация сделок

```php
function generateTrades(
    array $deviations,
    float $totalPortfolioValue,
    int $threshold = 5 // % отклонения для ребалансировки
): array {
    $trades = [];
    
    // Продажа переовешенных
    foreach ($deviations as $ticker => $data) {
        if ($data['deviation'] > $threshold / 100) {
            $amountToSell = $data['deviation'] * $totalPortfolioValue;
            $trades[] = [
                'ticker' => $ticker,
                'action' => 'SELL',
                'amount' => $amountToSell,
                'reason' => 'Overweight'
            ];
        }
    }
    
    // Покупка недовешенных
    foreach ($deviations as $ticker => $data) {
        if ($data['deviation'] < -$threshold / 100) {
            $amountToBuy = -$data['deviation'] * $totalPortfolioValue;
            $trades[] = [
                'ticker' => $ticker,
                'action' => 'BUY',
                'amount' => $amountToBuy,
                'reason' => 'Underweight'
            ];
        }
    }
    
    return $trades;
}
```

---

### 4.3 Оптимизация с учётом ограничений

**Ограничения:**
1. Минимальный лот (например, 10 акций SBER)
2. Комиссии (0.05% от сделки)
3. Налоги (13% с прибыли)
4. Кэш для округления

```php
function optimizeTrades(array $trades, array $constraints): array {
    foreach ($trades as &$trade) {
        // Округление до лотов
        $lotSize = $constraints['lots'][$trade['ticker']] ?? 1;
        $price = $constraints['prices'][$trade['ticker']];
        $sharesNeeded = $trade['amount'] / $price;
        $lotsNeeded = floor($sharesNeeded / $lotSize);
        $trade['lots'] = $lotsNeeded;
        $trade['shares'] = $lotsNeeded * $lotSize;
        $trade['actual_amount'] = $trade['shares'] * $price;
        
        // Расчёт комиссии
        $trade['commission'] = $trade['actual_amount'] * $constraints['commission_rate'];
    }
    
    return $trades;
}
```

---

## 5. Система сигналов

### 5.1 Комплексный скоринг

```php
function calculateScore(
    InstrumentDto $instrument,
    TechnicalAnalysisDto $technical,
    FundamentalAnalysisDto $fundamental
): int {
    $score = 0;
    
    // Технический анализ (40%)
    if ($technical->trend === 'BULLISH') $score += 20;
    if ($technical->rsi < 30) $score += 10;  // Перепроданность
    if ($technical->macdSignal === 'BUY') $score += 10;
    
    // Фундаментальный анализ (40%)
    if ($fundamental->peRatio < $fundamental->industryPeMedian) $score += 15;
    if ($fundamental->dividendYield > 5) $score += 15;
    if ($fundamental->payoutRatio < 0.6) $score += 10;
    
    // Качество компании (20%)
    if ($fundamental->roe > 15) $score += 10;
    if ($fundamental->debtToEquity < 0.5) $score += 10;
    
    return $score; // 0-100
}
```

**Интерпретация:**
- 80-100: Сильная покупка
- 60-79: Покупка
- 40-59: Держать
- 20-39: Продажа
- 0-19: Сильная продажа

---

### 5.2 Приоритизация сделок

```php
function prioritizeTrades(array $trades): array {
    usort($trades, function($a, $b) {
        // 1. Сначала продажа (получение кэша)
        if ($a['action'] !== $b['action']) {
            return $a['action'] === 'SELL' ? -1 : 1;
        }
        
        // 2. По абсолютному отклонению (больше = приоритетнее)
        return $b['abs_deviation'] <=> $a['abs_deviation'];
    });
    
    return $trades;
}
```

---

## 6. Структуры данных

### 6.1 DTO для метрик портфеля

```php
final class PortfolioMetricsDto
{
    public function __construct(
        public readonly float $totalValue,
        public readonly float $totalReturn,
        public readonly float $annualizedReturn,
        public readonly float $volatility,
        public readonly float $sharpeRatio,
        public readonly float $maxDrawdown,
        public readonly float $hhi,
        public readonly int $effectiveAssets,
        public readonly array $sectorAllocation,
    ) {}
}
```

### 6.2 DTO для предложения ребалансировки

```php
final class RebalancingSuggestionDto
{
    public function __construct(
        public readonly string $ticker,
        public readonly string $figi,
        public readonly float $currentWeight,
        public readonly float $targetWeight,
        public readonly float $deviation,
        public readonly string $action, // BUY, SELL, HOLD
        public readonly int $sharesToTrade,
        public readonly float $amountToTrade,
        public readonly float $estimatedCommission,
        public readonly ?string $reason,
    ) {}
}
```

---

## 7. Источники данных для расчётов

| Метрика | Источник | API метод |
|---------|----------|-----------|
| Текущие цены | Last Prices | `getLastPrices()` |
| Исторические цены | Candles | `getCandles()` |
| Объёмы торгов | Candles | `getCandles()` |
| P/E, P/B | Fundamentals | `getAssetFundamentals()` |
| Дивиденды | Dividends | `getDividends()` |
| Позиции | Portfolio | `getPortfolio()` |
| История сделок | Operations | `getOperations()` |
