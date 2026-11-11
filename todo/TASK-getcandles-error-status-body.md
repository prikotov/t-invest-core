# TASK-getcandles-error-status-body: Тело ответа с ошибочным HTTP-статусом проходит строгий разбор GetCandles

## Контекст

Follow-up [TASK-strict-candles-api.md](TASK-strict-candles-api.md) по блокирующему замечанию B1 ревью Пуаро stocks2 PR #438.
As-is контракт stocks2 (`docs/architecture/tinvest-candles-get-all-legacy-contract.md` §5.1): legacy-HTTP-клиент Yii2
«возвращает ответ с ошибочным HTTP-статусом вместо выброса BadResponseException», и ветка матрицы §7
«code=5/description=50002/instrument not found → Continue» **не обусловлена HTTP-статусом**.
T-Invest доставляет брокерные ошибки с не-2xx статусами (типично 400) — при текущем поведении пакета
(`http_errors` на стороне потребителя + компонент не перехватывает `BadResponseException`) тело с ошибкой брокера
не доходит до строгого разбора, и stocks2 остановит весь запуск вместо предусмотренного пропуска.

## Требования (Must Have)

- [ ] `MarketDataServiceComponent::getCandles()`: перехват `BadResponseException` от HTTP-клиента — тело ответа
      `$e->getResponse()->getBody()` проходит тот же строгий пайплайн разбора (пустое тело/JSON/ошибка брокера/candles),
      как при 2xx. Скоуп строго GetCandles: `getLastPrices()`, `getOrderBook()` и прочие методы не меняются.
- [ ] Сообщение `TInvestRequestException` для пустого тела содержит фактический HTTP-статус ответа
      (включая статусы из `BadResponseException`).
- [ ] Тесты (MockHandler): `400 + {5,50002,instrument not found}` → `TInvestBrokerException` (пропускаемая ветка);
      `400` + пустое тело → `TInvestRequestException` с «HTTP 400»; `400` + невалидный JSON → `TInvestResponseException`;
      регрессия 2xx-веток не сломана.
- [ ] Проверки зелёные: `composer cs-check && composer stan && composer psalm && composer test`.

## Out of Scope

- Изменение поведения остальных методов компонента и высокоуровневого сервиса.
- Изменение DI/конфигурации потребителей (stocks2 сам решает вопрос `http_errors` для своего общего клиента).

## История изменений

| Дата | Автор | Изменение |
| :--- | :--- | :--- |
| 2026-11-11 15:35:29 (1794386129) | Лид Арагорн (pi) | Создание задачи по блокирующему замечанию B1 ревью Пуаро (stocks2 PR #438): as-is §5.1 требует разбора тела независимо от HTTP-статуса |
