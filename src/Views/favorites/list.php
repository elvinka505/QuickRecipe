<section class="favorites">
    <h1>Избранное</h1>

    <?php if (empty($favorites)) : ?>
        <p>Вы ещё не добавили ни одного рецепта в избранное.</p>
        <a href="/recipes">Перейти к рецептам →</a>
    <?php else : ?>
        <div class="recipes-grid">
            <?php foreach ($favorites as $recipe) : ?>
                <div class="recipe-card">
                    <h2><?= htmlspecialchars((string) $recipe->title) ?></h2>
                    <p><?= htmlspecialchars((string) $recipe->description) ?></p>
                    <a href="/recipes/show?id=<?= $recipe->id ?>">Подробнее →</a>

                    <form action="/favorites/remove" method="POST">
                        <input type="hidden" name="recipe_id" value="<?= $recipe->id ?>">
                        <button type="submit">Удалить из избранного</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>