<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controllers\AuthController;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Router;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class AuthControllerTest extends TestCase
{
    private Router $router;
    private Logger $logger;

    protected function setUp(): void
    {
        Database::reset();
        $_ENV['DB_PATH'] = ':memory:';
        Database::getConnection();

        $this->router = $this->createStub(Router::class);
        $this->logger = new Logger();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        unset($_SESSION['user_id'], $_SESSION['user_name']);
    }

    protected function tearDown(): void
    {
        Database::reset();
        unset($_ENV['DB_PATH']);
        unset($_SESSION['user_id'], $_SESSION['user_name']);
    }

    private function makeController(): AuthController
    {
        return new class ($this->router, $this->logger) extends AuthController {
            public array $rendered = [];
            public array $redirects = [];

            protected function render(string $view, array $data = []): void
            {
                $this->rendered[] = ['view' => $view, 'data' => $data];
            }

            protected function redirect(string $url): void
            {
                $this->redirects[] = $url;
            }
        };
    }

    public function testLoginFormRenders(): void
    {
        $ctrl = $this->makeController();

        $ctrl->loginForm();

        $this->assertSame('auth/login', $ctrl->rendered[0]['view']);
        $this->assertSame('Вход', $ctrl->rendered[0]['data']['title']);
    }

    public function testRegisterFormRenders(): void
    {
        $ctrl = $this->makeController();

        $ctrl->registerForm();

        $this->assertSame('auth/register', $ctrl->rendered[0]['view']);
        $this->assertSame('Регистрация', $ctrl->rendered[0]['data']['title']);
    }

    public function testLoginWithEmptyFieldsShowsError(): void
    {
        $this->router
            ->method('getBodyParam')
            ->willReturn('');

        $ctrl = $this->makeController();

        $ctrl->login();

        $this->assertSame('auth/login', $ctrl->rendered[0]['view']);
        $this->assertSame('Заполните все поля', $ctrl->rendered[0]['data']['error']);
    }

    public function testLoginWithWrongPasswordShowsError(): void
    {
        $user = new User();
        $user->name = 'Elvina';
        $user->email = 'elvina@test.com';
        $user->password = password_hash('correct123', PASSWORD_BCRYPT);
        $user->save();

        $this->router
            ->method('getBodyParam')
            ->willReturnMap([
                ['email', '', 'elvina@test.com'],
                ['password', '', 'wrongpass'],
            ]);

        $ctrl = $this->makeController();

        $ctrl->login();

        $this->assertSame('auth/login', $ctrl->rendered[0]['view']);
        $this->assertSame('Неверный email или пароль', $ctrl->rendered[0]['data']['error']);
    }

    public function testLoginSuccessRedirects(): void
    {
        $user = new User();
        $user->name = 'Elvina';
        $user->email = 'elvina@test.com';
        $user->password = password_hash('secret123', PASSWORD_BCRYPT);
        $user->save();

        $this->router
            ->method('getBodyParam')
            ->willReturnMap([
                ['email', '', 'elvina@test.com'],
                ['password', '', 'secret123'],
            ]);

        $ctrl = $this->makeController();

        $ctrl->login();

        $this->assertContains('/', $ctrl->redirects);
        $this->assertSame($user->id, $_SESSION['user_id']);
        $this->assertSame('Elvina', $_SESSION['user_name']);
    }

    public function testRegisterWithEmptyFieldsShowsError(): void
    {
        $this->router
            ->method('getBodyParam')
            ->willReturn('');

        $ctrl = $this->makeController();

        $ctrl->register();

        $this->assertSame('auth/register', $ctrl->rendered[0]['view']);
        $this->assertSame('Заполните все поля', $ctrl->rendered[0]['data']['error']);
    }

    public function testRegisterWithInvalidEmailShowsError(): void
    {
        $this->router
            ->method('getBodyParam')
            ->willReturnMap([
                ['name', '', 'Elvina'],
                ['email', '', 'not-email'],
                ['password', '', 'secret123'],
            ]);

        $ctrl = $this->makeController();

        $ctrl->register();

        $this->assertSame('auth/register', $ctrl->rendered[0]['view']);
        $this->assertSame('Введите корректный email', $ctrl->rendered[0]['data']['error']);
    }

    public function testRegisterWithShortPasswordShowsError(): void
    {
        $this->router
            ->method('getBodyParam')
            ->willReturnMap([
                ['name', '', 'Elvina'],
                ['email', '', 'elvina@test.com'],
                ['password', '', '123'],
            ]);

        $ctrl = $this->makeController();

        $ctrl->register();

        $this->assertSame('auth/register', $ctrl->rendered[0]['view']);
        $this->assertSame('Пароль должен быть не менее 6 символов', $ctrl->rendered[0]['data']['error']);
    }

    public function testRegisterWithDuplicateEmailShowsError(): void
    {
        $user = new User();
        $user->name = 'Existing';
        $user->email = 'dup@test.com';
        $user->password = 'hash';
        $user->save();

        $this->router
            ->method('getBodyParam')
            ->willReturnMap([
                ['name', '', 'New User'],
                ['email', '', 'dup@test.com'],
                ['password', '', 'secret123'],
            ]);

        $ctrl = $this->makeController();

        $ctrl->register();

        $this->assertSame('auth/register', $ctrl->rendered[0]['view']);
        $this->assertSame('Пользователь с таким email уже существует', $ctrl->rendered[0]['data']['error']);
    }

    public function testRegisterSuccessRedirects(): void
    {
        $this->router
            ->method('getBodyParam')
            ->willReturnMap([
                ['name', '', 'New User'],
                ['email', '', 'new@test.com'],
                ['password', '', 'secret123'],
            ]);

        $ctrl = $this->makeController();

        $ctrl->register();

        $this->assertContains('/', $ctrl->redirects);
        $this->assertSame('New User', $_SESSION['user_name']);

        $createdUser = User::findByEmail('new@test.com');
        $this->assertInstanceOf(User::class, $createdUser);
    }

    public function testLogoutRedirects(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Elvina';

        $ctrl = $this->makeController();

        $ctrl->logout();

        $this->assertContains('/', $ctrl->redirects);
    }
}
