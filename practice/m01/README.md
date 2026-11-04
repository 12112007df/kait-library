# Модуль 1 Исследование репозитория проекта

Умаров Джабраил Мунавварович, ИСПО332 ПП03. Объём — 16 часов.
Период по ТЗ: 03.11–05.11.2026. Исследование и снимки: 03.11.2026.

## Актуальный отчёт

[Отчёт_03.10.2026.docx](Отчёт_03.10.2026.docx) — 20 страниц, 12 рисунков, 8 таблиц.
Для сдачи по имени методички добавлена [копия ISPO332_Umarov_M01.docx](ISPO332_Umarov_M01.docx). Оба имени содержат один и тот же документ и побайтно совпадают; отдельных версий нет.
Дата в новом имени файла не меняет даты практики внутри документа.

Подробная [сверка с методичкой](requirements.md) показывает готовые материалы и оставшиеся личные действия. Для запуска у себя на Windows используйте [windows-start.md](windows-start.md) и start-library.cmd в корне проекта.

## Что подтверждено

Исследован исходный срез fa96e45cc8d3eac09a6b40496ef120d688406ea5:
36 коммитов, 3 автора, 35 файлов, 1840 строк кода. Коммиты практики в эту статистику не входят.
На изолированном стенде PHP 8.5.12 / FrankenPHP 1.13.4 / SQLite 3.45.2 выполнены 10 проверок.
04.11.2026 добавлен экспорт CSV: текущая версия содержит 36 файлов и 2048 строк кода. Её срез зафиксирован в current-baseline.json, замер — в current-metrics.md; 13 проверок дополнения — в csv-checks.json. Исторические 35 файлов и 1840 строк не подменяются текущими числами.

На личном компьютере студента запуск ещё не подтверждён. Основная MySQL/MariaDB здесь не проверялась.
Опубликованного сайта нет; http://127.0.0.1:8080 — адрес локального запуска.
Проверка check_practice.py подтверждает комплектность; согласование проекта, инструктаж и настройку личного SSH она не подтверждает.
Минимум 2000 строк для текущего приложения выполнен: 2048 без комментариев, пустых строк и practice. Выбор проекта и допуск согласуются с руководителем.

## Материалы и разделы отчёта

| Материал | Файл |
|---|---|
| Паспорт, стек, структура, зависимости и выводы | [passport.md](passport.md) |
| Исходный срез и метрики | [baseline.json](baseline.json), [metrics.md](metrics.md) |
| История, авторы, ветки и горячие точки | [repo-stats.md](repo-stats.md), [git-history.txt](git-history.txt) |
| Условия и результаты запуска | [run-log.md](run-log.md), [runtime.json](runtime.json), [smoke-results.json](smoke-results.json) |
| Проверка комплектности | [readiness.txt](readiness.txt) |
| Ответы для защиты | [defence.md](defence.md) |
| Снимки и сведения об их получении | [screenshots/README.md](screenshots/README.md) |

## Рисунки в отчёте

| № | Содержание | Файл |
|---|---|---|
| 1 | Исходный граф Git | [git-graph.png](git-graph.png) |
| 2 | Каталог | [app-running.png](app-running.png) |
| 3 | Страница библиотекаря | [app-librarian.png](app-librarian.png) |
| 4 | Поиск | [02-search.png](screenshots/02-search.png) |
| 5 | Регистрация | [03-register.png](screenshots/03-register.png) |
| 6 | Личный кабинет | [04-reader.png](screenshots/04-reader.png) |
| 7 | Структура и метрики | [06-structure-metrics.png](screenshots/06-structure-metrics.png) |
| 8 | Blame и комплектность | [07-blame-ready.png](screenshots/07-blame-ready.png) |
| 9 | Результат GitHub API | [08-github-result.png](screenshots/08-github-result.png) |
| 10 | Главная страница GitHub | [09-github-repository.jpg](screenshots/09-github-repository.jpg) |
| 11 | Каталог М01 на GitHub | [10-github-module.jpg](screenshots/10-github-module.jpg) |
| 12 | Объединённый PR | [11-github-merged-pr.jpg](screenshots/11-github-merged-pr.jpg) |

Снимки GitHub отражают состояние страницы в момент съёмки, до последующих переименований и дополнений.
PR № 1 объединил первоначальные материалы; текущая версия лежит в main и дополнена отдельными коммитами.

Сверка с методичкой и помощник Windows включены в [PR № 2](https://github.com/12112007df/kait-library/pull/2).

Экспорт CSV, актуальные метрики и дополненный отчёт включены в [PR № 3](https://github.com/12112007df/kait-library/pull/3). Вход библиотекарем → каталог → «Скачать каталог CSV».
