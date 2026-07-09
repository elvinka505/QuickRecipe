<section class="recipes-list">
    <h1>Все рецепты</h1>

    <?php if (!empty($_SESSION['user_id'])) : ?>
        <a href="/recipes/create">+ Добавить рецепт</a>
    <?php endif; ?>

    <div class="recipes-grid">
        <?php if (empty($recipes)) : ?>
            <p>Рецептов пока нет.</p>
        <?php else : ?>
            <?php foreach ($recipes as $recipe) : ?>
                <?php $isFav = in_array($recipe->id, $favoriteIds); ?>
                <div class="recipe-card">
                    <h2><?= htmlspecialchars((string) $recipe->title) ?></h2>
                    <p><?= htmlspecialchars((string) $recipe->description) ?></p>

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.75rem;">
                        <a href="/recipes/show?id=<?= $recipe->id ?>">Подробнее →</a>

                        <?php if (!empty($_SESSION['user_id'])) : ?>
                            <form method="POST"
                                  action="/favorites/<?= $isFav ? 'remove' : 'add' ?>"
                                  style="margin:0;">
                                <input type="hidden" name="recipe_id" value="<?= $recipe->id ?>">
                                <button type="submit"
                                        title="<?= $isFav ? 'Убрать из избранного' : 'В избранное' ?>"
                                        style="background:none; border:none; cursor:pointer; font-size:1.25rem;">
                                    <?= $isFav ? '⭐' : '☆' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>