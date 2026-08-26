<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

class TestSupportLoginTest extends TestCase
{
    public function test_insecure_login_test_user_route_does_not_exist(): void
    {
        $response = $this->get('/login-test-user');

        $response->assertStatus(404);
        $this->assertGuest();
    }

    public function test_unauthenticated_user_accessing_admin_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_test_support_login_authenticates_configured_admin_user(): void
    {
        $adminEmail = config('auth.filament_admin_email', 'admin@madeena.local');
        $admin = User::factory()->create([
            'email' => $adminEmail,
            'role' => 'admin',
        ]);

        $response = $this->get('/test-support/login');

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_test_support_login_returns_404_when_configured_account_missing(): void
    {
        $response = $this->get('/test-support/login');

        $response->assertStatus(404);
        $this->assertGuest();
    }

    public function test_test_support_login_does_not_authenticate_unrelated_admin(): void
    {
        $admin = User::factory()->create([
            'email' => 'custom-admin@madeena.local',
            'role' => 'admin',
        ]);

        $response = $this->get('/test-support/login');

        $response->assertStatus(404);
        $this->assertGuest();
    }

    public function test_test_support_login_does_not_authenticate_configured_email_with_non_admin_role(): void
    {
        $adminEmail = config('auth.filament_admin_email', 'admin@madeena.local');
        User::factory()->create([
            'email' => $adminEmail,
            'role' => 'user',
        ]);

        $response = $this->get('/test-support/login');

        $response->assertStatus(404);
        $this->assertGuest();
    }

    public function test_test_support_route_is_not_registered_in_production_environment(): void
    {
        $this->app['env'] = 'production';
        $this->assertFalse(app()->environment(['local', 'testing']));
        $this->assertTrue(app()->isProduction());

        $router = $this->app->make('router');
        $router->setRoutes(new \Illuminate\Routing\RouteCollection());

        require base_path('routes/web.php');

        $routes = $router->getRoutes();
        $this->assertNull($routes->getByName('test-support.login'));
        $this->assertFalse($routes->hasNamedRoute('test-support.login'));

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $routes->match($this->app['request']->create('/test-support/login', 'GET'));
    }
}
