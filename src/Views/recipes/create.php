<div style="max-width:640px; margin:2rem auto; padding:0 1rem;">
    <h1><?= htmlspecialchars((string) $title) ?></h1>

    <?php if (!empty($error)) : ?>
        <p style="color:red; margin-bottom:1rem;"><?= htmlspecialchars((string) $error) ?></p>
    <?php endif; ?>

    <form method="POST" action="/recipes/create" style="display:flex; flex-direction:column; gap:1rem;">

        <label>
            Название *
            <input type="text" name="title" required
                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        </label>

        <label>
            Описание
            <textarea name="description" rows="2"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </label>

        <label>
            Ингредиенты * (через запятую)
            <textarea
                    name="ingredients"
                    rows="4"
                    required
            ><?= htmlspecialchars($_POST['ingredients'] ?? '') ?></textarea>        </label>

        <label>
            Шаги приготовления *
            <textarea name="steps" rows="6" required><?= htmlspecialchars($_POST['steps'] ?? '') ?></textarea>
        </label>

        <label>
            Время приготовления (мин)
            <input type="number" name="cook_time" min="0"
                   value="<?= (int)($_POST['cook_time'] ?? 0) ?>">
        </label>

        <label>
            Сложность
            <select name="difficulty">
                <?php foreach (['easy' => 'Лёгкая', 'medium' => 'Средняя', 'hard' => 'Сложная'] as $val => $label) : ?>
                    <option value="<?= $val ?>" <?= ($_POST['difficulty'] ?? 'easy') === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Категория
            <select name="category">
                <?php foreach (
                [
                                   'breakfast' => 'Завтрак',
                                   'lunch'     => 'Обед',
                                   'dinner'    => 'Ужин',
                                   'snack'     => 'Перекус',
                                   'dessert'   => 'Десерт',
                                   'other'     => 'Другое',
                               ] as $val => $label
) : ?>
                    <option value="<?= $val ?>" <?= ($_POST['category'] ?? 'other') === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit">💾 Сохранить рецепт</button>
    </form>
</div>