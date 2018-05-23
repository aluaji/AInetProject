<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * As an anonymous user I want to authenticate the application with valid credentials (email and password).
 */
class UserStory03Test extends UserStoryTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->seedMainUser();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_login_route_exists()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->get('/login')
            ->assertStatus(200);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_login_fails_with_invalid_user()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert

        $this->post('/login', [
            'email' => 'user1@mail.pt',
            'password' => 'fails'
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_login_fails_with_invalid_password()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert

        $this->post('/login', [
            'email' => 'user@mail.pt',
            'password' => 'invalid'
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_login_succeeds_with_valid_credentials()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert

        $this->post('/login', [
            'email' => 'user@mail.pt',
            'password' => 'abc'
        ])->assertSessionMissing('email');

        $this->assertAuthenticatedAs($this->mainUser);
    }
}
