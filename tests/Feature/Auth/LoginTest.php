<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 
     * @test
     */
    public function user_must_fill_required_fields()
    {
        $this->postJson("/api/auth/authenticate")
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "email",
                "password"
            ]);
    }

    /**
     * 
     * @test
     */
    public function user_cannot_login_if_email_is_not_valid()
    {
        $this->postJson("/api/auth/authenticate", [
            "email" => "channaveer",
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "email"
            ]);
    }


    /**
     * @test
     */
    public function user_cannot_login_with_wrong_credentials()
    {
        $user = User::factory()->create();

        $this->postJson("/api/auth/authenticate", [
            "email" => $user->email,
            "password" => "test@123"
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function user_with_unverified_email_cannot_login()
    {
        $user = User::factory()
            ->unverifiedEmail()
            ->create([
                "password" => "password"
            ]);

        $response = $this->postJson("/api/auth/authenticate", [
            "email" => $user->email,
            "password" => "password"
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseCount("personal_access_tokens", 0);
    }

    /**
     * @test
     */
    public function blocked_user_cannot_login()
    {
        $user = User::factory()
            ->isBlocked()
            ->create([
                "password" => "password"
            ]);

        $this->postJson("/api/auth/authenticate", [
            "email" => $user->email,
            "password" => "password"
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseCount("personal_access_tokens", 0);
    }

    /**
     * @test
     */
    public function user_with_valid_credentials_can_login()
    {
        $user = User::factory()
            ->create([
                "password" => "password"
            ]);

        $this->postJson("/api/auth/authenticate", [
            "email" => $user->email,
            "password" => "password"
        ])
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount("personal_access_tokens", 1);
    }

    /**
     * @test
     */
    public function a_login_user_can_logout()
    {
        $user = User::factory()
            ->create([
                "password" => "password"
            ]);

        $loggedInUserResponse = $this->postJson("/api/auth/authenticate", [
            "email" => $user->email,
            "password" => "password"
        ])
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount("personal_access_tokens", 1);

        $loggedInUser = $loggedInUserResponse->getOriginalContent();

        Sanctum::actingAs($loggedInUser["data"]["user"]);

        $this->postJson("/api/auth/logout", [])
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount("personal_access_tokens", 0);
    }
}
