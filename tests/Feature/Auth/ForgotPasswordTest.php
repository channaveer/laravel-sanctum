<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Jobs\ResetPasswordJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    public function user_needs_to_fill_required_details()
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
    public function a_user_cannot_request_reset_password_if_email_is_not_valid()
    {
        $this->postJson(route("auth.forgot-password"), [
            "email" => "channaveer"
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "email"
            ]);

        $this->assertDatabaseCount("password_resets", 0);
    }

    /**
     * @test
     */
    public function password_reset_details_are_sent_if_reset_password_details_are_correct()
    {
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
}
