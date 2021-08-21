<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Events\UserRegisteredEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function user_needs_fill_the_required_fields_to_register()
    {
        $this->postJson("/api/auth/register")
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "name",
                "email",
                "password",
                "confirm_password"
            ]);

        $this->assertDatabaseCount("users", 0);
    }

    /**
     * @test
     */
    public function user_must_enter_valid_email_while_registration()
    {
        $this->postJson("/api/auth/register", [
            "email" => "invalid_email"
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "email",
            ]);

        $this->assertDatabaseCount("users", 0);
    }

    /**
     * @test
     */
    public function password_and_confirm_password_must_match_while_registration()
    {
        $this->postJson("/api/auth/register", [
            "password" => "password@123",
            "confirm_password" => "password"
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "confirm_password"
            ]);

        $this->assertDatabaseCount("users", 0);
    }

    /**
     * @test
     */
    public function user_with_valid_registration_details_can_register()
    {
        Event::fake();

        $user = [
            "name" => "Channaveer Hakari",
            "email" => "channaveer@gmail.com",
            "password" => "password",
            "confirm_password" => "password"
        ];

        $this->postJson("/api/auth/register", $user)
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas("users", [
            "email" => $user["email"]
        ]);
        $this->assertDatabaseCount("users", 1);

        $this->assertDatabaseHas("password_resets", [
            "email" => $user["email"]
        ]);
        $this->assertDatabaseCount("password_resets", 1);

        Event::assertDispatched(UserRegisteredEvent::class);
    }
}
