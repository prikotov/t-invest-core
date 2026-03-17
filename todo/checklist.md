# Чек-лист задач

## Текущий фокус: Интеграция с T-Invest API

> Задачи советника → [todo/backlog.md](backlog.md)

---

## Фаза 1: API компоненты

### MarketDataService (НОВЫЙ)
- [ ] Создать директорию `src/Component/TInvest/MarketDataService/`
- [ ] `MarketDataServiceComponentInterface.php`
- [ ] `MarketDataServiceComponent.php`
- [ ] `Dto/CandleDto.php`
- [ ] `Dto/GetCandlesRequestDto.php`
- [ ] `Dto/GetCandlesResponseDto.php`
- [ ] `Dto/LastPriceDto.php`
- [ ] `Dto/GetLastPricesRequestDto.php`
- [ ] `Dto/GetLastPricesResponseDto.php`
- [ ] `Enum/CandleIntervalEnum.php`
- [ ] `Mapper/CandleMapper.php`
- [ ] `Mapper/LastPriceMapper.php`
- [ ] Зарегистрировать в `config/services.yaml`
- [ ] Тесты для мапперов

### OperationsService (РАСШИРЕНИЕ)
- [ ] `Dto/OperationDto.php`
- [ ] `Dto/GetOperationsRequestDto.php`
- [ ] `Dto/GetOperationsResponseDto.php`
- [ ] `Enum/OperationTypeEnum.php`
- [ ] `Enum/OperationStateEnum.php`
- [ ] `Mapper/OperationMapper.php`
- [ ] Добавить `getOperations()` в интерфейс
- [ ] Тесты

### InstrumentsService (РАСШИРЕНИЕ)
- [ ] Расширить `InstrumentDto.php` (sector, lot)
- [ ] Добавить `getShares()` в интерфейс
- [ ] Тесты

---

## Фаза 2: Консольные команды

### Команды для работы с API
- [ ] `src/Command/MarketCandlesCommand.php` — свечи
- [ ] `src/Command/MarketPricesCommand.php` — текущие цены
- [ ] `src/Command/OperationsHistoryCommand.php` — история операций
- [ ] Зарегистрировать в `bin/skill`

---

## Приоритеты

| Приоритет | Задача | Оценка |
|-----------|--------|--------|
| **P0** | getCandles | 4-6 ч |
| **P0** | getLastPrices | 2-3 ч |
| **P0** | getOperations | 2-3 ч |
| **P1** | getShares | 2-3 ч |
| **P1** | Команды CLI | 3-4 ч |

**Итого:** 13-19 часов

---

## MVP

1. [ ] `getCandles()` — исторические свечи
2. [ ] `getLastPrices()` — текущие цены
3. [ ] `getOperations()` — история операций
4. [ ] Команды: `market:candles`, `market:prices`, `operations:history`

**Время:** ~10-12 часов
