# Трассировка сценариев

Срез исходного кода: `3d73b65`. Ссылки `файл:строка` относятся к этому срезу. Диаграммы созданы редактируемыми фигурами Word (`dfd.docx`); для комплектности приложены `dfd.drawio`, `dfd-0.png`, `dfd-1.png`.

## Элементы DFD

| Тип | Название | Назначение | Где в коде |
|---|---|---|---|
| Внешняя сущность | E1 Пользователь системы | Гость, читатель, библиотекарь и администратор — роли одного внешнего источника/получателя данных. | templates/layout.php:16; src/Auth.php:22 |
| Процесс | 1.0 Управлять учётными записями | Регистрация, вход, выход, профиль, изменение роли и блокировки. | public/index.php:38; public/api.php:122 |
| Процесс | 2.0 Вести каталог и отзывы | Поиск, карточка, CSV, добавление книги и отзыва, загрузка/выдача обложки. | public/api.php:16; public/api.php:46; public/api.php:109 |
| Процесс | 3.0 Оформлять бронирования | Создание и отмена брони, очереди и списки. | src/LoanService.php:159; src/ReservationRepository.php:29 |
| Процесс | 4.0 Обрабатывать выдачи и отчёты | Выдача и возврат, свои книги, списки читателей/должников, CSV. | src/LoanService.php:12; src/ReportService.php:3 |
| Хранилище | D1 База данных библиотеки | users, genres, books, copies, loans, reservations, reviews. Таблицы объединены в логическое хранилище БД. | database/schema.sql:6; src/Database.php:7 |
| Хранилище | D2 Файлы обложек | public/uploads; имя файла дополнительно хранится в books.cover. | public/api.php:117; src/BookRepository.php:85 |

Сеансы PHP являются техническим механизмом доступа; Cookie и сеанс отражены в реестре № 23. Уровень 1 показывает прикладные потоки, а не последовательность вызовов функций. Повторы E1/D1 не означают разных пользователей или разных БД. Все внешние F01–F08 сохранены на обоих уровнях.

## Регистрация читателя

Процесс DFD: 1.0. Запрос: `POST /index.php?page=register`.

| Шаг | Где (файл:строка) | Функция / endpoint | Какие данные передаются |
|---|---|---|---|
| 1 | templates/register.php:3 | HTML-форма | ФИО, email, пароль, категория, группа, телефон; ограничения required/minlength/pattern. |
| 2 | templates/register.php:3 | Отправка формы | application/x-www-form-urlencoded; обычная навигация браузера, без api.js/fetch. |
| 3 | public/index.php:38; src/Validator.php:17 | Контроллер и Validator | required для трёх полей и длина пароля не менее 6; email() не вызывается. |
| 4 | src/Auth.php:47; src/Database.php:45 | Auth::register → Database::insert | INSERT users через prepare/execute; роль reader задаётся сервером, пароль преобразуется MD5. |
| 5 | public/index.php:47; src/helpers.php:8 | Сеанс и redirect | ID созданного читателя записывается в сеанс; ответ 302 Location: index.php?page=catalog. |
| 6 | public/index.php:10; public/assets/js/catalog.js:67 | Новая страница | Браузер выполняет GET каталога 200; затем отдельный AJAX-запрос books.search. |

## Поиск книги Чистый код

Процесс DFD: 2.0. Запрос: `GET /api.php?action=books.search&q=Чистый&genre=&sort=b.title&p=1`.

| Шаг | Где (файл:строка) | Функция / endpoint | Какие данные передаются |
|---|---|---|---|
| 1 | templates/catalog.php:4 | Форма поиска | q=Чистый; genre пустой; sort=b.title; страница 1. |
| 2 | public/assets/js/catalog.js:59; public/assets/js/catalog.js:40; public/assets/js/api.js:4 | submit → load → api | Отмена обычной отправки; URLSearchParams и fetch GET api.php. |
| 3 | public/api.php:16 | Обработчик books.search | q, genre и sort передаются в BookRepository::search; p приводится к int. |
| 4 | src/BookRepository.php:8; src/BookRepository.php:47; src/Database.php:7 | SELECT и подсчёт экземпляров | books LEFT JOIN genres; для каждой книги COUNT copies со status=available. Для поиска prepared SQL не применяется. |
| 5 | public/api.php:27; src/helpers.php:15 | JSON-ответ 200 | ok, items, total=1, pages=1, page=1; найдена книга Чистый код. |
| 6 | public/assets/js/catalog.js:54; public/assets/js/api.js:24 | Отрисовка каталога | Счётчик и карточки обновляются через DOM; название, автор и жанр экранируются escapeHtml. |

## Сетевое подтверждение

`scenario1.har` содержит реальный перехват браузера Chromium: POST регистрации → 302 → GET каталога 200 и GET поиска → 200. В регистрации отправлена обычная HTML-форма, а не JSON API. При q=Чистый в JSON total=1 и книга «Чистый код». Поля пароля, Cookie/Set-Cookie и значения cookies обезличены до публикации.

## Расхождения и ограничения

1. Формат email и pattern группы ограничены HTML, но соответствующие проверки не вызываются в контроллере регистрации.
2. Поиск использует конкатенацию SQL; экспорт CSV применяет отдельный CatalogExport и не исправляет старый поиск.
3. Скрытая кнопка/страница не гарантирует права endpoint: у loan.issue есть проверка входа, но нет проверки роли библиотекаря.
4. SQL с параметрами защищает значения от подстановки в текст SQL, но не заменяет проверку диапазона rating или принадлежности брони.
5. Запуск выполнен на SQLite. MySQL/MariaDB и ограничения ENUM/длины/сопоставления отдельно не проверялись.
