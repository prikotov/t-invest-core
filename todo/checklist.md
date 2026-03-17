# Чек-лист задач

## Текущий фокус: Интеграция с T-Invest API

> Задачи советника → [todo/backlog.md](backlog.md)

---

## Фаза 1: API компоненты

### MarketDataService (НОВЫЙ)
- [x] Создать директорию `src/Component/TInvest/MarketDataService/`
- [x] `MarketDataServiceComponentInterface.php`
- [x] `MarketDataServiceComponent.php`
- [x] `Dto/CandleDto.php`
- [x] `Dto/GetCandlesRequestDto.php`
- [x] `Dto/GetCandlesResponseDto.php`
- [x] `Dto/LastPriceDto.php`
- [x] `Dto/GetLastPricesRequestDto.php`
- [x] `Dto/GetLastPricesResponseDto.php`
- [x] `Enum/CandleIntervalEnum.php`
- [x] `Mapper/CandleMapper.php`
- [x] `Mapper/LastPriceMapper.php`
- [x] Зарегистрировать в `config/services.yaml`
- [x] Тесты для мапперов

### OperationsService (РАСШИРЕНИЕ)
- [x] `Dto/OperationDto.php`
- [x] `Dto/GetOperationsRequestDto.php`
- [x] `Dto/GetOperationsResponseDto.php`
- [x] `Enum/OperationTypeEnum.php`
- [x] `Enum/OperationStateEnum.php`
- [x] `Mapper/OperationMapper.php`
- [x] Добавить `getOperations()` в интерфейс
- [x] Тесты

### InstrumentsService (РАСШИРЕНИЕ)
- [ ] Расширить `InstrumentDto.php` (sector, lot)
- [ ] Добавить `getShares()` в интерфейс
- [ ] Тесты

---

## Фаза 2: Консольные команды

### Команды для работы с API
- [x] `src/Command/MarketCandlesCommand.php` — свечи
- [x] `src/Command/MarketPricesCommand.php` — текущие цены
- [x] `src/Command/OperationsHistoryCommand.php` — история операций
- [x] Зарегистрировать в `bin/skill` (автоматически через теги)

---

## Приоритеты

| Приоритет | Задача | Оценка | Статус |
|-----------|--------|--------|--------|
| **P0** | getCandles | 4-6 ч | ✅ |
| **P0** | getLastPrices | 2-3 ч | ✅ |
| **P0** | getOperations | 2-3 ч | ✅ |
| **P0** | Команды CLI | 3-4 ч | ✅ |
| **P1** | getShares | 2-3 ч | ⏳ |

---

## MVP

1. [x] `getCandles()` — исторические свечи
2. [x] `getLastPrices()` — текущие цены
3. [x] `getOperations()` — история операций
4. [x] Команды: `market:candles`, `market:prices`, `operations:history`

**Статус:** MVP завершён ✅

---

## Новые файлы

### MarketDataService
- `src/Component/TInvest/MarketDataService/MarketDataServiceComponentInterface.php`
- `src/Component/TInvest/MarketDataService/MarketDataServiceComponent.php`
- `src/Component/TInvest/MarketDataService/Dto/CandleDto.php`
- `src/Component/TInvest/MarketDataService/Dto/GetCandlesRequestDto.php`
- `src/Component/TInvest/MarketDataService/Dto/GetCandlesResponseDto.php`
- `src/Component/TInvest/MarketDataService/Dto/LastPriceDto.php`
- `src/Component/TInvest/MarketDataService/Dto/GetLastPricesRequestDto.php`
- `src/Component/TInvest/MarketDataService/Dto/GetLastPricesResponseDto.php`
- `src/Component/TInvest/MarketDataService/Enum/CandleIntervalEnum.php`
- `src/Component/TInvest/MarketDataService/Mapper/CandleMapper.php`
- `src/Component/TInvest/MarketDataService/Mapper/LastPriceMapper.php`
- `tests/Component/TInvest/MarketDataService/Mapper/CandleMapperTest.php`
- `tests/Component/TInvest/MarketDataService/Mapper/LastPriceMapperTest.php`

### OperationsService (расширение)
- `src/Component/TInvest/OperationsService/Dto/OperationDto.php`
- `src/Component/TInvest/OperationsService/Dto/GetOperationsRequestDto.php`
- `src/Component/TInvest/OperationsService/Dto/GetOperationsResponseDto.php`
- `src/Component/TInvest/OperationsService/Enum/OperationTypeEnum.php`
- `src/Component/TInvest/OperationsService/Enum/OperationStateEnum.php`
- `src/Component/TInvest/OperationsService/Mapper/OperationMapper.php`
- `tests/Component/TInvest/OperationsService/Mapper/OperationMapperTest.php`

### Команды CLI
- `src/Command/MarketCandlesCommand.php`
- `src/Command/MarketPricesCommand.php`
- `src/Command/OperationsHistoryCommand.php`
