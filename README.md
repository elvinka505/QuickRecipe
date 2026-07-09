# QuickRecipe

Учебный веб-проект на PHP 8.5, разработанный в рамках семестровой работы по дисциплине «Разработка веб-приложений».

Основной целью проекта была реализация собственного каркаса веб-приложения без использования готовых фреймворков с соблюдением современных PHP-стандартов (PSR) и архитектурных требований.

## Возможности

- Front Controller;
- маршрутизация через PHP-атрибуты (`#[Route]`);
- базовый MVC;
- работа с базой данных через слой `Database`;
- автозагрузка классов по PSR-4;
- конфигурация через `.env`;
- логирование по PSR-3 (Monolog);
- PSR-7 Request/Response;
- Middleware по PSR-15 для логирования запросов;
- модульные и интеграционные тесты;
- отчёт о соответствии стандарту PSR-12.

## Стек

- PHP 8.5
- SQLite
- Composer
- Monolog
- PHPUnit
- PHP_CodeSniffer
- Rector
- Dotenv

## Структура проекта

```
public/             — Front Controller
src/                — исходный код приложения
tests/              — модульные и интеграционные тесты
database/           — база данных
docs/               — документация и скриншоты
build/              — результаты покрытия тестами
```

## Установка

```bash
composer install
cp .env.example .env
```

Запуск встроенного веб-сервера:

```bash
php -S localhost:8000 -t public
```

## Тестирование

Запуск тестов:

```bash
vendor/bin/phpunit
```

Проверка покрытия:

```bash
vendor/bin/phpunit --coverage-html build/coverage
```

## Анализ кода

Проверка соответствия PSR-12:

```bash
vendor/bin/phpcs
```

Автоматическое исправление:

```bash
vendor/bin/phpcbf
```

Автоматическая модернизация кода:

```bash
vendor/bin/rector process
```

## Дополнительно

В репозитории также присутствует отчёт `migration_report.md`, посвящённый подготовке проекта к PHP 8.5 с использованием PHP_CodeSniffer, PHPCBF и Rector.