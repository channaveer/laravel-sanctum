<?php

namespace Tests\Feature\App\Http\Controllers\Auth;

use App\Models\PasswordReset;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailVerificationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function an_user_account_is_verified()
    {
        $this->markTestIncomplete("Write code and test to send welcome on board to users.");

        $passwordReset = PasswordReset::factory()->create();

        /** Verify user account by sending proper TOKEN & EMAIL */
        $this->postJson(route("auth.account-verification"), [
            "token" => $passwordReset->token,
            "email" => $passwordReset->email

        ])
            ->assertStatus(Response::HTTP_OK);

        /** Assert that user email_verified_at column is not null */
        $user = User::where(["email" => $passwordReset->email])->first();
        $this->assertNotNull($user->email_verified_at);

        /** Make sure that the token get deleted after account verification */
        $this->assertDatabaseMissing("password_resets", [
            "token" => $passwordReset->token
        ]);
    }

    /**
     * @test
     */
    public function it_requires_email_and_token_to_verify_account()
    {
        $passwordReset = PasswordReset::factory()->create();

        $this->postJson(route("auth.account-verification"))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "email",
                "token"
            ]);

        $this->assertDatabaseHas("password_resets", [
            "token" => $passwordReset->token
        ]);
    }

    /**
     * @test
     */
    public function it_must_not_verify_account_when_email_or_token_is_wrong()
    {
        $passwordReset = PasswordReset::factory()->create();

        $this->postJson(route("auth.account-verification"), [
            "email" => $this->faker->email,
            "token" => $this->faker->uuid,
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas("password_resets", [
            "token" => $passwordReset->token
        ]);
    }
}
