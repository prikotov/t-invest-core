# TASK-operations-by-cursor: курсорное чтение операций GetOperationsByCursor

Дата: 2026-11-09
Автор: Лид Арагорн (stocks2)
Исполнитель: Бэкендер Левша
Статус: in_progress
Запросивший проект: stocks2 (перенос Yii2 broker/tinkoff/operations/sync, EPIC-sync-tinvest-console-jobs)

## Проблема

В пакете есть только некурсорный `OperationsServiceComponent::getOperations()`. Проекту stocks2 для переноса
синхронизации операций нужен полный курсорный контракт T-Invest API `OperationsService/GetOperationsByCursor`
со постраничным чтением (limit 500, cursor/nextCursor/hasNext).

## Эталон (только чтение)

Legacy Yii2: `/home/dp/MyProjects/stocks/common/modules/Broker/modules/Tinkoff/integration/components/OperationsService/`
- `dto/GetOperationsByCursorRequestDto.php`
- `wrappers/GetOperationsByCursorResponseWrapper.php`
- `mappers/GetOperationsByCursorRequestMapper.php`
- `OperationsServiceComponent::getOperationsByCursor()`

Контракт поведения (частичные отказы, обработка пустого ответа): `/home/dp/MyProjects/stocks2/docs/architecture/tinvest-operations-sync-legacy-contract.md`.

## Требования

- Метод интерфейса `OperationsServiceComponentInterface::getOperationsByCursor(token, request): ResponseWrapper`
  (имена типов — по конвенциям пакета, не копировать namespace Yii2).
- Request: accountId (brokerAccountId), cursor (nullable), limit (по умолчанию 500), fromDate если есть в эталоне.
- Response/wrapper: элементы операций, hasNext, nextCursor; разбор ответа строго по структуре API, без guessed defaults.
- Тесты: мок HTTP-клиента — успешная страница, пустой items, hasNext=true без nextCursor, HTTP-ошибочный статус
  (BadResponseException возвращается компонентом HTTP, не пробрасывается — см. контракт §11).
- Проверки пакета: phpunit, phpstan, phpcs, psalm — все зелёные.
- Коммит в ветку task/operations-by-cursor, НЕ пушить (публикация по отдельному решению владельца).

## Out of scope

- Записи в какие-либо БД, реальные API-вызовы, изменения stocks2.

## Итог (2026-11-09, Лид Арагорн)

Выполнено, коммит 4e32527. Проверки: phpunit 111/476 OK, phpstan/phpcs/psalm чисто.
Решения по вопросам исполнителя:
1. Пустое тело ответа — строгий TInvestRequestException оставляем: соответствует контракту переноса
   (пустота ответа не превращается в пустой снимок, §14.3 tinvest-operations-sync-legacy-contract).
2. Файл задачи коммитится отдельным docs-коммитом.
3. Обновление phpstan 2.x — вне скоупа, отдельная задача пакета.
Отклонения от Yii2-эталона приняты: hasNext без nextCursor — данные (решает потребитель),
UnexpectedValueException вместо TypeError, eager array вместо ленивого Generator, токен в конструкторе.
Статус: done (локально), публикация в origin/main — по решению владельца.
