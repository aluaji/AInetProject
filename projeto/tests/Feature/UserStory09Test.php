<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * As a user I should be able to change my password.
 */
class UserStory09Test extends UserStoryTestCase
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_password_change_fails_with_empty_form()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->patch('/me/password')
            ->assertSessionHasErrors(['old_password', 'password']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_password_change_fails_with_empty_new_password()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->patch('/me/password', [
                'old_password' => 'abc',
            ])
            ->assertSessionHasErrors(['password'])
            ->assertSessionHasNoErrors('old_password');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_password_change_fails_with_invalid_new_password()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->patch('/me/password', [
                'old_password' => 'abc',
                'password' => '12',
                'password_confirmation' => '12',
            ])
            ->assertSessionHasErrors(['password'])
            ->assertSessionHasNoErrors('old_password');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_password_change_fails_with_invalid_password_confirmation()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->patch('/me/password', [
                'old_password' => 'abc',
                'password' => '123',
                'password_confirmation' => 'abc',
            ])
            ->assertSessionHasErrors(['password'])
            ->assertSessionHasNoErrors('old_password');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_password_change_fails_with_invalid_old_password()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->patch('/me/password', [
                'old_password' => 'cba',
                'password' => '123',
                'password_confirmation' => '123',
            ])
            ->assertSessionHasErrors(['old_password'])
            ->assertSessionHasNoErrors('password');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_password_change_succeeds_for_regular_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->patch('/me/password', [
                'old_password' => 'abc',
                'password' => 'xyz',
                'password_confirmation' => 'xyz',
            ])
            ->assertSuccessfulOrRedirect();

        $this->mainUser->refresh();
        $this->assertTrue(Hash::check('xyz', $this->mainUser->password));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_password_change_succeeds_for_admin_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedAdminUser();

        $this->actingAs($this->adminUser)
            ->patch('/me/password', [
                'old_password' => 'fff',
                'password' => 'xyz',
                'password_confirmation' => 'xyz',
            ])
            ->assertSuccessfulOrRedirect();

        $this->adminUser->refresh();
        $this->assertTrue(Hash::check('xyz', $this->adminUser->password));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_password_change_fails_for_guests()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedAdminUser();

        $this->patch('/me/password', [
                'old_password' => 'abc',
                'password' => 'xyz',
                'password_confirmation' => 'xyz',
            ])
            ->assertRedirect('/login');
    }
}
