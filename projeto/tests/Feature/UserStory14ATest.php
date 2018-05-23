<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Storage;
use Tests\TestCase;

/**
 * As a user I want to view the list of my opened accounts and the list of my closed accounts.
 */
class UserStory14ATest extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function accounts_index_is_not_available_to_guests()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedOpenedAccountsForUser($this->mainUser->id);

        $this->get('/accounts/'.$this->mainUser->id)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function accounts_index_fails_if_invalid_user()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedOpenedAccountsForUser($this->mainUser->id);

        $this->actingAs($this->mainUser)
            ->get('/accounts/232')
            ->assertStatus(404); // Assumes implicit model binding
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function accounts_index_shows_all_accounts()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $accounts = $accounts->merge($this->seedClosedAccountsForUser($this->mainUser->id));

        $this->actingAs($this->mainUser)
            ->get('/accounts/'.$this->mainUser->id)
            ->assertSuccessful()
            ->assertSeeAll($this->types->pluck('name'), 'Expected account type names are missing')
            ->assertSeeAll($accounts->pluck('code'), 'Expected account codes are missing')
            ->assertSeeAll($accounts->pluck('current_balance'), 'Expected balance is missing or mistype');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function accounts_index_works_with_admins()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $accounts = $this->seedOpenedAccountsForUser($this->adminUser->id);
        $accounts = $accounts->merge($this->seedClosedAccountsForUser($this->adminUser->id));

        $this->actingAs($this->adminUser)
            ->get('/accounts/'.$this->adminUser->id)
            ->assertSuccessful()
            ->assertSeeAll($this->types->slice(0, 3)->pluck('name'), 'Expected account type names are missing')
            ->assertSeeAll($accounts->pluck('code'), 'Expected account codes are missing')
            ->assertSeeAll($accounts->pluck('current_balance'), 'Expected balance is missing or mistype');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function accounts_index_works_with_blocked_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $accounts = $accounts->merge($this->seedClosedAccountsForUser($this->mainUser->id));
        $this->mainUser->blocked = true;
        $this->mainUser->save();

        $this->actingAs($this->mainUser)
            ->get('/accounts/'.$this->mainUser->id)
            ->assertSuccessful()
            ->assertSeeAll($this->types->slice(0, 3)->pluck('name'), 'Expected account type names are missing')
            ->assertSeeAll($accounts->pluck('code'), 'Expected account codes are missing')
            ->assertSeeAll($accounts->pluck('current_balance'), 'Expected balance is missing or mistype');
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function accounts_index_only_shows_owner_accounts()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $adminAccounts = $this->seedOpenedAccountsForUser($this->adminUser->id);
        $userAccounts = $this->seedOpenedAccountsForUser($this->mainUser->id);

        $this->actingAs($this->adminUser)
            ->get('/accounts/'.$this->adminUser->id)
            ->assertSuccessful()
            ->assertSeeAll($this->types->slice(0, 3)->pluck('name'), 'Expected account type names are missing')
            ->assertSeeAll($adminAccounts->pluck('code'), 'Expected account codes are missing')
            ->assertSeeAll($adminAccounts->pluck('current_balance'), 'Expected balance is missing or mistype')
            ->assertDontSeeAll($userAccounts->pluck('code'), 'Failure: displays accounts not owned');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function accounts_index_does_not_allows_impersonation()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedOpenedAccountsForUser($this->adminUser->id);
        $this->seedOpenedAccountsForUser($this->mainUser->id);

        $this->actingAs($this->mainUser)
            ->get('/accounts/'.$this->adminUser->id)
            ->assertForbidden();
    }

}
