<section class="search">
    <h1>Поиск рецептов</h1>

    <form action="/recipes/search" method="POST">
        <input
            type="text"
            name="ingredients"
            placeholder="молоко, яйца, мука..."
            value="<?= htmlspecialchars($ingredients ?? '') ?>"
        >
        <button type="submit">Найти</button>
    </form>

    <?php if (!empty($ingredients)) : ?>
        <p>Результаты по запросу: <strong><?= htmlspecialchars($ingredients) ?></strong></p>

        <div class="recipes-grid">
            <?php if (empty($results)) : ?>
                <p>Ничего не найдено.</p>
            <?php else : ?>
                <?php foreach ($results as $recipe) : ?>
                    <div class="recipe-card">
                        <h2><?= htmlspecialchars((string) $recipe->title) ?></h2>
                        <p><?= htmlspecialchars((string) $recipe->description) ?></p>
                        <a href="/recipes/show?id=<?= $recipe->id ?>">Подробнее →</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>