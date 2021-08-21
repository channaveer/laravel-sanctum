<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\PasswordReset;
use App\Events\ResetPasswordEvent;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function email_and_token_are_required_to_verify_reset_password()
    {
        $this->postJson("/api/auth/verify-reset-password")
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "email",
                "token"
            ]);
    }

    /**
     * @test
     */
    public function wrong_email_and_token_cannot_verify_reset_password()
    {
        $passwordReset = PasswordReset::factory()->create();

        $this->postJson("/api/auth/verify-reset-password", [
            "email" => "channaveer@gmail.com",
            "token" => "test@123"
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas("password_resets", [
            "token" => $passwordReset->token
        ]);
    }

    /**
     * @test
     */
    public function verify_reset_password()
    {
        $passwordReset = PasswordReset::factory()->create();

        $this->postJson("/api/auth/verify-reset-password", [
            "email" => $passwordReset->email,
            "token" => $passwordReset->token
        ])
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas("password_resets", [
            "token" => $passwordReset->token
        ]);
    }

    /**
     * @test
     */
    public function reset_password_fields_are_required_reset_password()
    {
        $this->patchJson("/api/auth/reset-password")
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "email",
                "token",
                "password",
                "confirm_password"
            ]);
    }

    /**
     * @test
     */
    public function email_and_token_must_be_valid_to_reset_password()
    {
        $this->patchJson("/api/auth/reset-password", [
            "email"             => "channaveer@gmail.com",
            "token"             => "test123",
            "password"          => "test@123",
            "confirm_password"  => "test@123",
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "token",
            ]);
    }

    /**
     * @test
     */
    public function password_and_confirm_password_must_be_same_to_reset_password()
    {
        $this->patchJson("/api/auth/reset-password", [
            "password"          => "test",
            "confirm_password"  => "test@123",
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "confirm_password"
            ]);
    }

    /**
     * @test
     */
    public function reset_password()
    {
        Event::fake([
            ResetPasswordEvent::class
        ]);

        $passwordReset = PasswordReset::factory()->create();

        $this->patchJson("/api/auth/reset-password", [
            "email"             => $passwordReset->email,
            "token"             => $passwordReset->token,
            "password"          => "test@123",
            "confirm_password"  => "test@123",
        ])
            ->assertStatus(Response::HTTP_OK);

        $user = User::where(["email" => $passwordReset->email])->first()->makeVisible("password");

        $this->assertTrue(Hash::check("test@123", $user->password));

        $this->assertDatabaseMissing("password_resets", [
            "token" => $passwordReset->token
        ]);

        Event::assertDispatched(ResetPasswordEvent::class);
    }
}
