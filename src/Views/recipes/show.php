<section class="recipe-show">
    <?php if (empty($recipe)) : ?>
        <p>Рецепт не найден.</p>
    <?php else : ?>
        <h1><?= htmlspecialchars((string) $recipe->title) ?></h1>
        <p class="description"><?= htmlspecialchars((string) $recipe->description) ?></p>

        <h2>Ингредиенты</h2>
        <ul>
            <?php foreach (explode(',', (string) $recipe->ingredients) as $ingredient) : ?>
                <li><?= htmlspecialchars(trim($ingredient)) ?></li>
            <?php endforeach; ?>
        </ul>

        <h2>Приготовление</h2>
        <p><?= nl2br(htmlspecialchars((string) $recipe->steps)) ?></p>

        <form action="/favorites/add" method="POST">
            <input type="hidden" name="recipe_id" value="<?= $recipe->id ?>">
            <button type="submit">❤️ В избранное</button>
        </form>
    <?php endif; ?>
</section>