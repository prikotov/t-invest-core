# Чек-лист задач

## Статус
- [ ] Не начато
- [~] В процессе
- [x] Выполнено

---

## Фаза 1: Расширение API компонентов (read-only)

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

## Фаза 2: Сервисы бизнес-логики

### UserMemoryService (НОВЫЙ, ВЫСОКИЙ ПРИОРИТЕТ)
- [ ] Создать директорию `src/Service/UserMemory/`
- [ ] `Dto/UserProfileDto.php`
  - risk_tolerance, horizon, experience, focus
  - preferences (max_position, avoid_tickers, favorite_sectors)
- [ ] `Dto/UserHistoryDto.php`
  - история сделок, результаты
- [ ] `Dto/LessonLearnedDto.php`
  - уроки из прошлых решений
- [ ] `UserMemoryServiceInterface.php`
- [ ] `UserMemoryService.php`
  - [ ] `getProfile()` — получить профиль
  - [ ] `updateProfile()` — обновить профиль
  - [ ] `addRecipeResult()` — сохранить результат рецепта
  - [ ] `addLesson()` — добавить урок
  - [ ] `getRelevantMemory()` — получить релевантную память для контекста
- [ ] Создать `data/user-profile.yaml`
- [ ] Создать `data/user-memory.yaml`
- [ ] Зарегистрировать в `config/services.yaml`
- [ ] Unit-тесты

### RecipeService (НОВЫЙ, ВЫСОКИЙ ПРИОРИТЕТ)
- [ ] Создать директорию `src/Service/Recipe/`
- [ ] `Dto/RecipeDto.php`
  - id, ticker, thesis, entry_levels, stop_loss, targets
  - status (active/closed), created_at, updated_at
  - history (обновления)
- [ ] `Dto/RecipeUpdateDto.php`
- [ ] `Dto/RecipeResultDto.php`
  - result (profit/loss/breakeven), actual_pnl, lessons
- [ ] `RecipeServiceInterface.php`
- [ ] `RecipeService.php`
  - [ ] `create()` — создать рецепт
  - [ ] `get()` — получить по ID
  - [ ] `list()` — список активных
  - [ ] `update()` — обновить (добавить запись в историю)
  - [ ] `close()` — закрыть с результатом
  - [ ] `checkTriggers()` — проверить триггеры (уровни, стопы, цели)
- [ ] Создать `data/recipes/` директорию
- [ ] Зарегистрировать в `config/services.yaml`
- [ ] Unit-тесты

### MonitoringService (НОВЫЙ)
- [ ] Создать директорию `src/Service/Monitoring/`
- [ ] `Dto/MonitoringTaskDto.php`
  - name, type (price_cross/recurring_prompt/custom)
  - schedule (RRule), prompt, status
- [ ] `Dto/MonitoringResultDto.php`
- [ ] `MonitoringServiceInterface.php`
- [ ] `MonitoringService.php`
  - [ ] `create()` — создать задачу
  - [ ] `list()` — список задач
  - [ ] `run()` — выполнить проверку
  - [ ] `pause()` / `resume()` — управление
- [ ] Создать `data/monitoring/` директорию
- [ ] Зарегистрировать в `config/services.yaml`
- [ ] Unit-тесты

### CalendarService (НОВЫЙ)
- [ ] Создать директорию `src/Service/Calendar/`
- [ ] `Dto/EventDto.php`
  - date, type (earnings/dividend/cbr/expiry)
  - ticker, description, importance
- [ ] `Dto/WeekCalendarDto.php`
- [ ] `CalendarServiceInterface.php`
- [ ] `CalendarService.php`
  - [ ] `getWeekEvents()` — события на неделю
  - [ ] `getTickerEvents()` — события по тикеру
  - [ ] `getUpcoming()` — ближайшие события
- [ ] Источник: T-Invest API + парсинг внешних источников?
- [ ] Зарегистрировать в `config/services.yaml`
- [ ] Unit-тесты

### SummaryService (НОВЫЙ)
- [ ] Создать директорию `src/Service/Summary/`
- [ ] `Dto/SymbolSummaryDto.php`
  - price, change_1d, change_1m, rsi
  - pe, pb, div_yield, roe, score
  - signal, recommendation
- [ ] `SummaryServiceInterface.php`
- [ ] `SummaryService.php`
  - [ ] `getSummary()` — обзор одного тикера
  - [ ] `getSummaries()` — обзор нескольких тикеров
- [ ] Зарегистрировать в `config/services.yaml`
- [ ] Unit-тесты

### PortfolioAnalysisService
- [ ] Создать директорию `src/Service/PortfolioAnalysis/`
- [ ] `Dto/PortfolioMetricsDto.php`
- [ ] `Dto/PositionAnalysisDto.php`
- [ ] `Dto/AllocationDto.php`
- [ ] `PortfolioAnalysisServiceInterface.php`
- [ ] `PortfolioAnalysisService.php`
  - [ ] `calculateAllocation()`
  - [ ] `calculateReturns()`
  - [ ] `calculateRisk()`
  - [ ] `calculateDeviations()`
- [ ] Зарегистрировать в `config/services.yaml`
- [ ] Unit-тесты

### TechnicalAnalysisService
- [ ] Создать директорию `src/Service/TechnicalAnalysis/`
- [ ] `Dto/TrendAnalysisDto.php`
- [ ] `Dto/SignalDto.php`
- [ ] `TechnicalAnalysisServiceInterface.php`
- [ ] `TechnicalAnalysisService.php`
  - [ ] `calculateSMA()`
  - [ ] `calculateEMA()`
  - [ ] `calculateRSI()`
  - [ ] `calculateMACD()`
  - [ ] `determineTrend()`
  - [ ] `generateSignals()`
- [ ] Зарегистрировать в `config/services.yaml`
- [ ] Unit-тесты

### FundamentalAnalysisService
- [ ] Создать директорию `src/Service/FundamentalAnalysis/`
- [ ] `Dto/ValuationDto.php`
- [ ] `Dto/DividendAnalysisDto.php`
- [ ] `FundamentalAnalysisServiceInterface.php`
- [ ] `FundamentalAnalysisService.php`
  - [ ] `analyzeValuation()`
  - [ ] `analyzeDividends()`
  - [ ] `calculateScore()`
- [ ] Зарегистрировать в `config/services.yaml`
- [ ] Unit-тесты

### RebalancingService
- [ ] Создать директорию `src/Service/Rebalancing/`
- [ ] `Dto/TargetAllocationDto.php`
- [ ] `Dto/RecommendationActionDto.php`
- [ ] `Dto/RecommendationsDto.php`
- [ ] `RebalancingServiceInterface.php`
- [ ] `RebalancingService.php`
  - [ ] `calculateDeviations()`
  - [ ] `generateRecommendations()`
  - [ ] `evaluateTiming()`
- [ ] Зарегистрировать в `config/services.yaml`
- [ ] Unit-тесты

---

## Фаза 3: Консольные команды

### Команды профиля и памяти
- [ ] `src/Command/ProfileSetupCommand.php` — интерактивная настройка профиля
- [ ] `src/Command/ProfileShowCommand.php` — показать профиль
- [ ] `src/Command/MemoryShowCommand.php` — показать память
- [ ] `src/Command/MemoryLessonCommand.php` — добавить урок

### Команды рецептов
- [ ] `src/Command/RecipeCreateCommand.php` — создать рецепт
- [ ] `src/Command/RecipeListCommand.php` — список рецептов
- [ ] `src/Command/RecipeShowCommand.php` — детали рецепта
- [ ] `src/Command/RecipeUpdateCommand.php` — обновить рецепт
- [ ] `src/Command/RecipeCloseCommand.php` — закрыть с результатом

### Команды мониторинга
- [ ] `src/Command/MonitorCreateCommand.php` — создать мониторинг
- [ ] `src/Command/MonitorListCommand.php` — список задач
- [ ] `src/Command/MonitorRunCommand.php` — запустить проверку

### Команды анализа портфеля
- [ ] `src/Command/PortfolioAnalyzeCommand.php`
- [ ] `src/Command/PortfolioReportCommand.php`
- [ ] `src/Command/RebalancingPlanCommand.php`

### Команды анализа инструментов
- [ ] `src/Command/AnalyzeTechnicalCommand.php`
- [ ] `src/Command/AnalyzeFundamentalCommand.php`
- [ ] `src/Command/AnalyzeQuickCommand.php`

### Команды обзора и календаря
- [ ] `src/Command/SummaryCommand.php` — быстрый обзор тикеров
- [ ] `src/Command/CalendarWeekCommand.php` — календарь на неделю

### Команды скрининга
- [ ] `src/Command/ScreenStocksCommand.php`

### Регистрация в bin/skill
- [ ] Зарегистрировать все команды

---

## Фаза 4: Конфигурация и данные

### Структура директорий
- [ ] `data/user-profile.yaml` — профиль инвестора
- [ ] `data/user-memory.yaml` — история + уроки
- [ ] `data/recipes/` — рецепты
- [ ] `data/monitoring/` — задачи мониторинга

### Модели портфелей
- [ ] `config/allocations/conservative.yaml`
- [ ] `config/allocations/balanced.yaml`
- [ ] `config/allocations/aggressive.yaml`

### Шаблоны мониторинга
- [ ] `config/monitoring-templates.yaml`

---

## Приоритеты

| Приоритет | Блок | Оценка времени |
|-----------|------|----------------|
| **P0** | UserMemoryService | 4-6 ч |
| **P0** | RecipeService (базовый) | 6-8 ч |
| **P0** | MarketDataService (getCandles, getLastPrices) | 4-6 ч |
| **P0** | OperationsService (getOperations) | 2-3 ч |
| **P1** | MonitoringService | 6-8 ч |
| **P1** | SummaryService | 3-4 ч |
| **P1** | CalendarService | 4-6 ч |
| **P1** | PortfolioAnalysisService | 6-8 ч |
| **P1** | RebalancingService | 6-8 ч |
| **P1** | Команды profile, recipe, monitor | 6-8 ч |
| **P2** | TechnicalAnalysisService | 8-10 ч |
| **P2** | FundamentalAnalysisService | 6-8 ч |
| **P2** | Команды analyze, summary, calendar | 4-6 ч |
| **P3** | Команды screen | 2-3 ч |

**Общая оценка:** 67-92 часов

---

## MVP (минимальная версия)

### Этап 1: Основа (20-25 ч)
1. [ ] `getCandles()` — исторические цены
2. [ ] `getOperations()` — история операций
3. [ ] `UserMemoryService` — профиль + память
4. [ ] `RecipeService` — создание/просмотр рецептов
5. [ ] `profile:setup`, `recipe:create`, `recipe:list` — команды

### Этап 2: Анализ (15-20 ч)
6. [ ] `PortfolioAnalysisService` — метрики портфеля
7. [ ] `SummaryService` — быстрый обзор
8. [ ] `portfolio:analyze`, `summary` — команды
9. [ ] `RebalancingService` — рекомендации
10. [ ] `portfolio:rebalance:plan` — команда

### Этап 3: Автоматизация (15-20 ч)
11. [ ] `MonitoringService` — мониторинг
12. [ ] `CalendarService` — календарь
13. [ ] `monitor:create`, `calendar:week` — команды
14. [ ] Интеграция Recipe + Monitoring

---

## Зависимости

```
UserMemoryService
    ↓
RecipeService → PortfolioAnalysisService → RebalancingService
    ↓                ↓
MonitoringService  TechnicalAnalysisService
    ↓                ↓
CalendarService   FundamentalAnalysisService
                         ↓
                    SummaryService
```

**Порядок реализации:**
1. UserMemory (основа для всего)
2. API Components (MarketData, Operations)
3. Recipe (основной workflow)
4. Analysis Services (Portfolio, Technical, Fundamental)
5. Monitoring, Calendar, Summary (автоматизация)
6. Rebalancing (финальные рекомендации)
