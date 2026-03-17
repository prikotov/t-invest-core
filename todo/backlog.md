# Backlog — Задачи финансового советника

> Задачи отложены до реализации базовой интеграции с T-Invest API

---

## Сервисы бизнес-логики (отложено)

### UserMemoryService
- [ ] Создать директорию `src/Service/UserMemory/`
- [ ] `Dto/UserProfileDto.php`
- [ ] `Dto/UserHistoryDto.php`
- [ ] `Dto/LessonLearnedDto.php`
- [ ] `UserMemoryServiceInterface.php`
- [ ] `UserMemoryService.php`
- [ ] Создать `data/user-profile.yaml`
- [ ] Unit-тесты

### RecipeService
- [ ] Создать директорию `src/Service/Recipe/`
- [ ] `Dto/RecipeDto.php`
- [ ] `Dto/RecipeUpdateDto.php`
- [ ] `Dto/RecipeResultDto.php`
- [ ] `RecipeServiceInterface.php`
- [ ] `RecipeService.php`
- [ ] Создать `data/recipes/` директорию
- [ ] Unit-тесты

### MonitoringService
- [ ] Создать директорию `src/Service/Monitoring/`
- [ ] `Dto/MonitoringTaskDto.php`
- [ ] `Dto/MonitoringResultDto.php`
- [ ] `MonitoringServiceInterface.php`
- [ ] `MonitoringService.php`
- [ ] Создать `data/monitoring/` директорию
- [ ] Unit-тесты

### CalendarService
- [ ] Создать директорию `src/Service/Calendar/`
- [ ] `Dto/EventDto.php`
- [ ] `Dto/WeekCalendarDto.php`
- [ ] `CalendarServiceInterface.php`
- [ ] `CalendarService.php`
- [ ] Unit-тесты

### SummaryService
- [ ] Создать директорию `src/Service/Summary/`
- [ ] `Dto/SymbolSummaryDto.php`
- [ ] `SummaryServiceInterface.php`
- [ ] `SummaryService.php`
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
- [ ] Unit-тесты

---

## Консольные команды советника (отложено)

### Команды профиля и памяти
- [ ] `src/Command/ProfileSetupCommand.php`
- [ ] `src/Command/ProfileShowCommand.php`
- [ ] `src/Command/MemoryShowCommand.php`
- [ ] `src/Command/MemoryLessonCommand.php`

### Команды рецептов
- [ ] `src/Command/RecipeCreateCommand.php`
- [ ] `src/Command/RecipeListCommand.php`
- [ ] `src/Command/RecipeShowCommand.php`
- [ ] `src/Command/RecipeUpdateCommand.php`
- [ ] `src/Command/RecipeCloseCommand.php`

### Команды мониторинга
- [ ] `src/Command/MonitorCreateCommand.php`
- [ ] `src/Command/MonitorListCommand.php`
- [ ] `src/Command/MonitorRunCommand.php`

### Команды анализа портфеля
- [ ] `src/Command/PortfolioAnalyzeCommand.php`
- [ ] `src/Command/PortfolioReportCommand.php`
- [ ] `src/Command/RebalancingPlanCommand.php`

### Команды анализа инструментов
- [ ] `src/Command/AnalyzeTechnicalCommand.php`
- [ ] `src/Command/AnalyzeFundamentalCommand.php`
- [ ] `src/Command/AnalyzeQuickCommand.php`

### Команды обзора и календаря
- [ ] `src/Command/SummaryCommand.php`
- [ ] `src/Command/CalendarWeekCommand.php`

### Команды скрининга
- [ ] `src/Command/ScreenStocksCommand.php`

---

## Конфигурация советника (отложено)

### Структура директорий
- [ ] `data/user-profile.yaml`
- [ ] `data/user-memory.yaml`
- [ ] `data/recipes/`
- [ ] `data/monitoring/`

### Модели портфелей
- [ ] `config/allocations/conservative.yaml`
- [ ] `config/allocations/balanced.yaml`
- [ ] `config/allocations/aggressive.yaml`

### Шаблоны мониторинга
- [ ] `config/monitoring-templates.yaml`

---

## Оценка времени для backlog

| Блок | Оценка |
|------|--------|
| UserMemoryService | 4-6 ч |
| RecipeService | 6-8 ч |
| MonitoringService | 6-8 ч |
| CalendarService | 4-6 ч |
| SummaryService | 3-4 ч |
| PortfolioAnalysisService | 6-8 ч |
| TechnicalAnalysisService | 8-10 ч |
| FundamentalAnalysisService | 6-8 ч |
| RebalancingService | 6-8 ч |
| Команды | 15-20 ч |
| Конфигурация | 3-4 ч |

**Итого backlog:** 67-92 часа

---

## Условия перехода к backlog

1. Готова базовая интеграция с T-Invest API
2. Реализованы команды для работы с API
3. API покрыто тестами
