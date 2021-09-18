<?php

namespace Tests\Feature\App\Http\Controllers\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Jobs\ResetPasswordJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function password_reset_details_are_sent()
    {
        $this->markTestIncomplete("Write code and tests to send mail and listeners too.");

        Bus::fake();

        $user = User::factory()->create();

        $this->postJson(route("auth.forgot-password"), [
            "email" => $user->email
        ])
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas("password_resets", [
            "email" => $user->email
        ]);

        Bus::assertDispatched(ResetPasswordJob::class);
    }

    /**
     * @test
     */
    public function it_requires_validation_fields()
    {
        $this->postJson(route("auth.forgot-password"))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "email"
            ]);

        $this->assertDatabaseCount("password_resets", 0);
    }


    /**
     * @test
     */
    public function it_must_not_allow_to_reset_password_if_email_is_invalid()
    {
        $this->postJson(route("auth.forgot-password"), [
            "email" => $this->faker->word
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "email"
            ]);

        $this->assertDatabaseCount("password_resets", 0);
    }
}
