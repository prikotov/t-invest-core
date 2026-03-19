# API T-Invest - Требуемые endpoint'ы (read-only)

## Справочник

Спецификация: https://russianinvestments.github.io/investAPI/swagger-ui/openapi.yaml

**Важно:** Используются только read-only endpoint'ы. Все сделки исполняются вручную.

---

## 1. Market Data Service (НОВЫЙ)

### 1.1 Получение свечей
```
GET /v1/marketdata/candles
```

**Параметры:**
- `figi` (required)
- `from`, `to` (required)
- `interval` (required)

**Задачи:**
- [ ] `MarketDataServiceComponentInterface.php`
- [ ] `Dto/CandleDto.php`
- [ ] `Dto/GetCandlesRequestDto.php`
- [ ] `Dto/GetCandlesResponseDto.php`
- [ ] `Enum/CandleIntervalEnum.php`
- [ ] `Mapper/CandleMapper.php`
- [ ] Тесты

---

### 1.2 Получение последних цен
```
GET /v1/marketdata/last-prices
```

**Параметры:**
- `figi` (array)

**Задачи:**
- [ ] `Dto/LastPriceDto.php`
- [ ] `Dto/GetLastPricesRequestDto.php`
- [ ] `Dto/GetLastPricesResponseDto.php`
- [ ] `Mapper/LastPriceMapper.php`
- [ ] Добавить `getLastPrices()` в интерфейс

---

## 2. Operations Service (РАСШИРЕНИЕ)

### 2.1 История операций
```
GET /v2/operations
```

**Параметры:**
- `accountId` (required)
- `from`, `to`
- `state`, `figi` (optional)

**Задачи:**
- [ ] `Dto/OperationDto.php`
- [ ] `Dto/GetOperationsRequestDto.php`
- [ ] `Dto/GetOperationsResponseDto.php`
- [ ] `Enum/OperationTypeEnum.php`
- [ ] `Enum/OperationStateEnum.php`
- [ ] `Mapper/OperationMapper.php`
- [ ] Добавить `getOperations()` в интерфейс
- [ ] Тесты

---

## 3. Instruments Service (РАСШИРЕНИЕ)

### 3.1 Список акций
```
GET /v1/instruments/shares
```

**Задачи:**
- [ ] Расширить `InstrumentDto.php` (sector, lot)
- [ ] Добавить `getShares()` в интерфейс

---

### 3.2 Торговый календарь ✅
```
GET /v1/instruments/trading/schedules
```

**Параметры:**
- `exchange` (optional) - биржа (MOEX)
- `from`, `to` (optional) - период

**Возвращает:**
- `is_trading_day` - торговый день или выходной
- `start_time`, `end_time` - часы торгов
- `morning_trading`, `evening_trading` - аукционы
- `clearing` - время клиринга

**Задачи:**
- [x] `TradingScheduleDto.php`
- [x] `TradingDayDto.php`
- [x] `TradingScheduleRequestDto.php`
- [x] `Mapper/TradingScheduleMapper.php`
- [x] `Mapper/TradingScheduleRequestMapper.php`
- [x] Добавить `getTradingSchedule()` в интерфейс InstrumentsService
- [x] Команда `schedule [--exchange=MOEX] [--date=YYYY-MM-DD]`
- [ ] Тесты

**Пример:**
```bash
./bin/skill schedule --exchange=MOEX --date=2024-03-18
# MOEX: Торговый день
# Основная сессия: 10:00-18:40
# Аукцион открытия: 09:50-10:00
# Аукцион закрытия: 18:40-18:50
# Клиринг: 18:50-19:00
```

---

## Структура директорий

```
src/Component/TInvest/
├── MarketDataService/                    # НОВЫЙ
│   ├── Dto/
│   │   ├── CandleDto.php
│   │   ├── GetCandlesRequestDto.php
│   │   ├── GetCandlesResponseDto.php
│   │   ├── LastPriceDto.php
│   │   ├── GetLastPricesRequestDto.php
│   │   └── GetLastPricesResponseDto.php
│   ├── Enum/
│   │   └── CandleIntervalEnum.php
│   ├── Mapper/
│   │   ├── CandleMapper.php
│   │   └── LastPriceMapper.php
│   ├── MarketDataServiceComponentInterface.php
│   └── MarketDataServiceComponent.php
│
├── OperationsService/
│   ├── Dto/
│   │   ├── OperationDto.php              # НОВЫЙ
│   │   ├── GetOperationsRequestDto.php   # НОВЫЙ
│   │   └── GetOperationsResponseDto.php  # НОВЫЙ
│   ├── Enum/
│   │   ├── OperationTypeEnum.php         # НОВЫЙ
│   │   └── OperationStateEnum.php        # НОВЫЙ
│   ├── Mapper/
│   │   └── OperationMapper.php           # НОВЫЙ
│   └── ...
│
└── InstrumentsService/
    └── ... (расширение существующего)
```

---

## Очередность реализации

### Sprint 1 (P0)
1. `getCandles()` - исторические свечи
2. `getLastPrices()` - текущие цены
3. `getOperations()` - история операций

### Sprint 2 (P1)
4. `getShares()` - информация об акциях

---

## Регистрация в DI

```yaml
# config/services.yaml
TInvest\Skill\Component\TInvest\MarketDataService\MarketDataServiceComponentInterface:
  class: TInvest\Skill\Component\TInvest\MarketDataService\MarketDataServiceComponent
  arguments:
    $client: '@GuzzleHttp\ClientInterface'
    $baseUrl: '%baseUrl%'
    $token: '%token%'
```
