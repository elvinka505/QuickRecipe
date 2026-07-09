<section class="auth">
    <h1>Вход</h1>
    <?php if (!empty($error)) : ?>
        <p class="error"><?= htmlspecialchars((string) $error) ?></p>
    <?php endif; ?>
    <form action="/login" method="POST">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Войти</button>
    </form>

    <p>Нет аккаунта? <a href="/register">Зарегистрироваться</a></p>
</section>