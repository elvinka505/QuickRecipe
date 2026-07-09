<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'QuickRecipe') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header>
    <nav>
        <a href="/">🍳 QuickRecipe</a>
        <ul>
            <li><a href="/recipes">Рецепты</a></li>
            <li><a href="/recipes/search">Поиск</a></li>
            <li><a href="/favorites">Избранное</a></li>
            <?php if (!empty($_SESSION['user_id'])) : ?>
                <li><span style="color:#9a5a5a">👋 <?= htmlspecialchars((string) $_SESSION['user_name']) ?></span></li>
                <li><a href="/logout">Выйти</a></li>
            <?php else : ?>
                <li><a href="/login">Войти</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main>
    <?= $content ?>
</main>

<footer>
    <p>QuickRecipe © 2025</p>
</footer>

</body>
</html>