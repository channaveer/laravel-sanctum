<?php

namespace Tests\Feature\App\Http\Controllers\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function user_can_login()
    {
        $this->markTestIncomplete("Write and test code to limit login attempts.");

        $user = User::factory()->create();

        $this->postJson(route("auth.authenticate"), [
            "email" => $user->email,
            "password" => "password"
        ])
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount("personal_access_tokens", 1);
    }

    /**
     * 
     * @test
     */
    public function it_requires_validation_fields()
    {
        $this->postJson(route("auth.authenticate"))
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
    public function it_must_not_allow_user_to_login_when_email_is_invalid()
    {
        $this->postJson(route("auth.authenticate"), [
            "email"     => $this->faker->word,
            "password"  => "password"
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                "email"
            ]);
    }


    /**
     * @test
     */
    public function it_must_not_allow_user_to_login_with_wrong_credentials()
    {
        $user = User::factory()->create();

        $this->postJson(route("auth.authenticate"), [
            "email" => $user->email,
            "password" => $this->faker->password
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function it_must_not_allow_user_to_login_when_email_is_unverified()
    {
        $user = User::factory()
            ->unverifiedEmail()
            ->create();

        $this->postJson(route("auth.authenticate"), [
            "email" => $user->email,
            "password" => $this->faker->password
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseCount("personal_access_tokens", 0);
    }

    /**
     * @test
     */
    public function it_must_not_allow_user_to_login_when_account_is_blocked()
    {
        $user = User::factory()
            ->isBlocked()
            ->create();

        $this->postJson(route("auth.authenticate"), [
            "email" => $user->email,
            "password" => $this->faker->password
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseCount("personal_access_tokens", 0);
    }

    /**
     * @test
     */
    public function an_authenticated_user_can_logout()
    {
        $user = User::factory()->create();

        $loggedInUserResponse = $this->postJson(route("auth.authenticate"), [
            "email" => $user->email,
            "password" => "password"
        ])
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount("personal_access_tokens", 1);

        $loggedInUser = $loggedInUserResponse->getOriginalContent();

        Sanctum::actingAs($loggedInUser["data"]["user"]);

        $this->postJson(route("auth.logout"))
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount("personal_access_tokens", 0);
    }

    /**
     * @test
     */
    public function it_should_not_allow_a_guest_user_to_logout()
    {
        $this->postJson(route("auth.logout"))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
