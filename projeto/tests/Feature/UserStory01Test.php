<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * As a user (anonymous or authenticated user) I want to access the site’s initial page with information about the
 * total of registered users, total number of accounts and movements registered on the platform.
 */
class UserStory01Test extends UserStoryTestCase
{

    // @codingStandardsIgnoreStart
    /** @test */
    public function main_page_should_display_statistics_for_guests()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedExtraUsers(3);
        $users = User::all();
        $this->seedAccounts($users, 5);
        $accounts = DB::table('accounts')->get();
        $this->seedMovements($accounts, 7);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee(strval($users->count()))
            ->assertSee(strval($accounts->count()))
            ->assertSee(strval($accounts->count() * 7));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function main_page_should_display_statistics_for_regular_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        //  total of registered users, total number of accounts and movements registered on the platform
        $this->seedMainUser();
        $this->seedExtraUsers(10);
        $users = User::all();
        $this->seedAccounts($users, 13);
        $accounts = DB::table('accounts')->get();
        $this->seedMovements($accounts, 17);

        $this->actingAs($this->mainUser)
            ->get('/')
            ->assertStatus(200)
            ->assertSee(strval($users->count()))
            ->assertSee(strval($accounts->count()))
            ->assertSee(strval($accounts->count() * 17));
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function main_page_should_display_statistics_for_admins()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        //  total of registered users, total number of accounts and movements registered on the platform
        $this->seedAdminUser();
        $this->seedExtraUsers(4);
        $users = User::all();
        $this->seedAccounts($users, 11);
        $accounts = DB::table('accounts')->get();
        $this->seedMovements($accounts, 17);

        $this->actingAs($this->adminUser)
            ->get('/')
            ->assertStatus(200)
            ->assertSee(strval($users->count()))
            ->assertSee(strval($accounts->count()))
            ->assertSee(strval($accounts->count() * 17));
    }
}
