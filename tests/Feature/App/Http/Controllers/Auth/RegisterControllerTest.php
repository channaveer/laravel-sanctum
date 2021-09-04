<?php

namespace Tests\Feature\App\Http\Controllers\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Events\UserRegisteredEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function user_needs_fill_the_required_fields_to_register()
    {
        $this->postJson(route("auth.register"))
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
        $this->postJson(route("auth.register"), [
            "email" => $this->faker->word
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
        $this->postJson(route("auth.register"), [
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
    public function user_email_must_be_unique()
    {
        User::factory()->create(["email" => "channaveer@gmail.com"]);

        $newUser = User::factory()->raw(["email" => "channaveer@gmail.com"]);

        $this->postJson(route("auth.register"), $newUser)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(["email"]);
    }

    /**
     * @test
     */
    public function user_can_register()
    {
        $this->markTestIncomplete("Write code and tests to send mail and listeners too.");

        Event::fake();

        $user = [
            "name" => "Channaveer Hakari",
            "email" => "channaveer@gmail.com",
            "password" => "password",
            "confirm_password" => "password"
        ];

        $this->postJson(route("auth.register"), $user)
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
