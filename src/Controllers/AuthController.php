<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AbstractController;
use App\Core\Attributes\Route;
use App\Models\User;

class AuthController extends AbstractController
{
    #[Route('/login')]
    public function loginForm(): void
    {
        $this->render('auth/login', ['title' => 'Вход']);
    }

    #[Route('/login', 'POST')]
    public function login(): void
    {
        $email = trim((string) $this->router->getBodyParam('email', ''));
        $password = $this->router->getBodyParam('password', '');

        if (
            $email === ''
            || $email === '0'
            || in_array($password, [null, '', '0'], true)
        ) {
            $this->render('auth/login', [
                'title' => 'Вход',
                'error' => 'Заполните все поля',
            ]);
            return;
        }

        $user = User::findByEmail($email);

        if ($user instanceof User && password_verify($password, $user->password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->name;
            $this->redirect('/');
            return;
        }

        $this->render('auth/login', [
            'title' => 'Вход',
            'error' => 'Неверный email или пароль',
        ]);
    }

    #[Route('/register')]
    public function registerForm(): void
    {
        $this->render('auth/register', ['title' => 'Регистрация']);
    }

    #[Route('/register', 'POST')]
    public function register(): void
    {
        $name = trim((string) $this->router->getBodyParam('name', ''));
        $email = trim((string) $this->router->getBodyParam('email', ''));
        $password = $this->router->getBodyParam('password', '');

        if (
            $name === ''
            || $name === '0'
            || $email === ''
            || $email === '0'
            || in_array($password, [null, '', '0'], true)
        ) {
            $this->render('auth/register', [
                'title' => 'Регистрация',
                'error' => 'Заполните все поля',
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth/register', [
                'title' => 'Регистрация',
                'error' => 'Введите корректный email',
            ]);
            return;
        }

        if (strlen((string) $password) < 6) {
            $this->render('auth/register', [
                'title' => 'Регистрация',
                'error' => 'Пароль должен быть не менее 6 символов',
            ]);
            return;
        }

        if (User::findByEmail($email) instanceof User) {
            $this->render('auth/register', [
                'title' => 'Регистрация',
                'error' => 'Пользователь с таким email уже существует',
            ]);
            return;
        }

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = password_hash((string) $password, PASSWORD_BCRYPT);
        $user->save();

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name;

        $this->redirect('/');
    }

    #[Route('/logout')]
    public function logout(): void
    {
        session_destroy();
        $this->redirect('/');
    }
}
