<?php

namespace Tests\Feature\App\Http\Controllers\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\PasswordReset;
use App\Events\ResetPasswordEvent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetPasswordControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function verify_reset_password()
    {
        $passwordReset = PasswordReset::factory()->create();

        $this->postJson(route("auth.verify-reset-password"), [
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
    public function it_requires_email_and_token_to_verify_reset_password()
    {
        $this->postJson(route("auth.verify-reset-password"))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "email",
                "token"
            ]);
    }

    /**
     * @test
     */
    public function it_must_not_verify_reset_password_when_wrong_email_and_token()
    {
        $passwordReset = PasswordReset::factory()->create();

        $this->postJson(route("auth.verify-reset-password"), [
            "email" => $this->faker->email,
            "token" => $this->faker->uuid
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas("password_resets", [
            "token" => $passwordReset->token
        ]);
    }

    /**
     * @test
     */
    public function reset_password()
    {
        $this->markTestIncomplete("Write code and tests to send mail and listeners too.");

        Event::fake([
            ResetPasswordEvent::class
        ]);

        $passwordReset = PasswordReset::factory()->create();

        $this->patchJson(route("auth.reset-password"), [
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

    /**
     * @test
     */
    public function it_requires_validation_fields_to_reset_password()
    {
        $this->patchJson(route("auth.reset-password"))
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
    public function it_must_have_valid_email_and_token_to_reset_password()
    {
        $passwordReset = PasswordReset::factory()->create();

        $this->patchJson(route("auth.reset-password"), [
            "email"             => $this->faker->email,
            "token"             => $this->faker->uuid,
            "password"          => "test@123",
            "confirm_password"  => "test@123",
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas("password_resets", [
            "token" => $passwordReset->token
        ]);
    }

    /**
     * @test
     */
    public function it_must_match_password_and_confirm_password()
    {
        $this->patchJson(route("auth.reset-password"), [
            "password"          => "test",
            "confirm_password"  => "test@123",
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "confirm_password"
            ]);
    }
}
