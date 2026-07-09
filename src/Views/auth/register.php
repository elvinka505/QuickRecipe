<section class="auth">
    <h1>Регистрация</h1>
    <?php if (!empty($error)) : ?>
        <p class="error"><?= htmlspecialchars((string) $error) ?></p>
    <?php endif; ?>
    <form action="/register" method="POST">
        <label for="name">Имя</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Зарегистрироваться</button>
    </form>

    <p>Уже есть аккаунт? <a href="/login">Войти</a></p>
</section>