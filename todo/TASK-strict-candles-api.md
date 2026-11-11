# TASK-strict-candles-api: Строгий разбор ответа GetCandles для переноса cron candles/get-all в stocks2

## Контекст

 stocks2 переносит Yii2 cron-команду `broker/tinkoff/candles/get-all` (массовое обновление дневных свечей `ti_candle`).
 Утверждённый as-is контракт: `~/MyProjects/stocks2/docs/architecture/tinvest-candles-get-all-legacy-contract.md`
 (эталонная ревизия Yii2 `9de4a4e`, доменное ревью Грэм 2026-11-11: APPROVE).

 По правилам переноса stocks2 не строит собственный broker API-слой: недостающая функциональность добавляется
 в этот пакет, stocks2 использует только публичный API пакета.

 Разведка (Левша, 2026-11-11) подтвердила: текущий публичный API компонента `MarketDataServiceComponent::getCandles()`
 не позволяет точно воспроизвести контракт. Реализация в stocks2 заблокирована до новой ревизии пакета.

## Подтверждённые расхождения (file:line по 18539b3)

1. `MarketDataService::getCandles()` всегда добавляет `limit=100` — `src/Service/MarketData/MarketDataService.php:28-36`;
   контракт Yii2 поле `limit` не отправляет.
2. Строковый интервал имеет скрытый fallback на HOUR — `src/Service/MarketData/MarketDataService.php:87`.
3. Пустое тело HTTP превращается в пустой список свечей — `src/Component/TInvest/MarketDataService/MarketDataServiceComponent.php:77-79`;
   по контракту это ошибка и остановка сценария.
4. Ошибки брокера (`code`/`description`/`message`) не распознаются: компонент передаёт декодированное тело прямо в mapper
   (`MarketDataServiceComponent.php:81-84`), `CandleMapper` превращает отсутствие `candles` в пустой успех
   (`src/Component/TInvest/MarketDataService/Mapper/CandleMapper.php:22-25`). Из-за этого `50002` («instrument not found»)
   неотличим от пустой истории, а прочие ошибки нельзя остановить по матрице контракта.
5. Guessed defaults в `CandleMapper`: `volume ?? 0`, `time ?? now`, `isComplete ?? true` (`CandleMapper.php:43-45`) —
   несовместимы со строгим as-is разбором.

## Требования (Must Have)

- [x] `GetCandlesRequestDto`: `$limit` допускает `null`; при `null` поле `limit` в теле запроса отсутствует
      (DTO уже был nullable, компонент уже опускал поле — факт зафиксирован тестом на фиксированном HTTP-клиенте).
- [x] Пустое тело HTTP-ответа → типизированное исключение (не пустой список свечей).
- [x] Невалидный JSON и валидный JSON не-массив (скаляр/`null`) → типизированное исключение.
      Валидный JSON-массив (объект или список) проходит дальше по текущему пути.
- [x] Ответ с ошибкой брокера → типизированное исключение с доступом к `code`, `description`, `message`;
      `stocks2` может однозначно распознать `50002` (продолжить цикл) и отличить от прочих кодов (остановка);
      строковый `code='5'` сохраняет строгий тип и не спутается с int `5`.
- [x] `CandleMapper`: обязательные поля свечи (`volume`, `open`, `high`, `low`, `close`, `time`, `isComplete`)
      без guessed defaults — отсутствие/дефект обязательного поля → исключение.
      Вложенные `units`/`nano`: контракт фиксирует as-is fallback «отсутствующее → 0»
      (`QuotationVO.php:22-30` в stocks Yii2 + `QuantityHelper.php:12-15`) — воспроизведён и задокументирован тестом.
- [x] Явный `CandleIntervalEnum::DAY` доступен через публичный API компонента без строковых fallback-ов
      (компонентный уровень это уже обеспечивает — покрыт тестом).
- [x] Тело запроса: `instrument.uid`, точные `from`/`to` (`format('c')`), без `limit` при `null` — покрыт тестом
      на фиксированном HTTP-клиенте (сверка тела запроса).
- [x] Обратная совместимость CLI (`market:candles`) и высокоуровневого `MarketDataService` сохранена:
      удобные дефолты CLI-уровня (`limit=100`, строковый интервал) остались на уровне высокоуровневого сервиса,
      строгие гарантии — на уровне компонента. Существующие использования компонента в пакете актуализации не требовали:
      исключения никто не перехватывал, зависимостей от «пустое тело → пустой успех» нет.
- [x] Тесты на каждую ветку: пустое тело; невалидный JSON; JSON не-массив; ошибка брокера 50002; ошибка брокера
      50003/прочие; отсутствие `candles` при валидном объекте; обязательные поля свечи (каждое отсутствующее);
      вложенные `units`/`nano` (оба/одно отсутствует); тело запроса без `limit` и с точными `from/to/uid`.
- [x] Проверки зелёные: `composer cs-check && composer stan && composer psalm && composer test`
      (139 тестов, 539 assertions).

## Out of Scope

- Реализация синхронизации в stocks2 (отдельная задача там же).
- CLI-команды свечей: новые сценарии не добавляем, только сохранение работоспособности существующих.

## Результат для stocks2

- Новая ревизия `dev-main` пакета, после которой stocks2 выполняет `composer update prikotov/t-invest-core`
  и снимает блокировку `TASK-sync-tinvest-candles-sync`.

## История изменений

| Дата | Автор | Изменение |
| :--- | :--- | :--- |
| 2026-11-11 12:17:10 (1794374230) | Лид Арагорн (pi) | Создание задачи по итогам разведки Левши (блокировка TASK-sync-tinvest-candles-sync в stocks2, PR #438) |
| 2026-11-11 12:50:08 (1794376208) | Левша (backend_developer_levsha) | Реализация: TInvestResponseException/TInvestBrokerException, строгий разбор GetCandles, CandleMapper без guessed defaults, тесты всех веток; проверки зелёные (feat b7cf058, test b82eff9) |
