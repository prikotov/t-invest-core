# TASK-validate-last-prices-response: Строгая валидация ответа GetLastPrices

## Проблема

`MarketDataServiceComponent::getLastPrices()` и `LastPriceMapper` превращают пустое HTTP-тело, отсутствие поля `lastPrices` или его неверный тип в успешный пустой результат. Потребитель не может отличить корректный пустой ответ от дефекта API.

## Требования

- [x] Пустое HTTP-тело завершается явным исключением.
- [x] Отсутствующее поле `lastPrices` завершается явным исключением.
- [x] Поле `lastPrices` неверного типа завершается явным исключением.
- [x] `lastPrices` обязан быть именно JSON-массивом: `{}` и объект с ключами — ошибка, пустой `[]` допустим (проверка типов на уровне компонента через не-ассоциативный `json_decode`).
- [x] Верхний уровень ответа — именно JSON-объект: top-level JSON-массив — ошибка.
- [x] Корректный пустой массив `lastPrices: []` остаётся допустимым ответом.
- [x] Ошибки JSON не маскируются.
- [x] Поведение покрыто unit-тестами без реального API.
- [x] `composer stan` и `composer test` проходят.
- [ ] `composer cs-check` — падает на отсутствующем сниффе `PrikotovCodingStandard...DtoStructureSniff` в vendor (преждесуществующее, вне диффа; baseline не трогали по указанию).
- [ ] `composer psalm` — 4 преждесуществующие ошибки в `OrderbookCommand.php` / `OutputFormatTrait.php` (baseline не трогали по указанию; изменённые файлы чисты).

## Границы

- Не выполнять реальные T-Invest API-вызовы.
- Не менять публичные DTO и сигнатуры интерфейсов без необходимости.
- Не добавлять fallback, маскирующий ошибки ответа.

## Связанная задача

Блокирует `stocks2/todo/TASK-sync-tinvest-last-prices-isolation.todo.md`.
