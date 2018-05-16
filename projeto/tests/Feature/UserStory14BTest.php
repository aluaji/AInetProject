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
class UserStory14BTest extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function opened_accounts_are_not_available_to_guests()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedOpenedAccountsForUser($this->mainUser->id);

        $this->get('/accounts/'.$this->mainUser->id.'/opened')
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function opened_accounts_fail_if_invalid_user()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedOpenedAccountsForUser($this->mainUser->id);

        $this->actingAs($this->mainUser)
            ->get('/accounts/232/opened')
            ->assertStatus(404); // Assumes implicit model binding
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function opened_accounts_does_not_show_closed_accounts()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $opened = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $closed = $this->seedClosedAccountsForUser($this->mainUser->id);

        $this->actingAs($this->mainUser)
            ->get('/accounts/'.$this->mainUser->id.'/opened')
            ->assertSuccessful()
            ->assertSeeAll($this->types->slice(0, 3)->pluck('name'), 'Expected account type names are missing')
            ->assertSeeAll($opened->pluck('code'), 'Expected account codes are missing')
            ->assertSeeAll($opened->pluck('current_balance'), 'Expected balance is missing or mistype')
            ->assertDontSeeAll($closed->pluck('code'), 'Failure: closed accounts are visible');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function opened_accounts_work_with_admins()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $opened = $this->seedOpenedAccountsForUser($this->adminUser->id);
        $closed = $this->seedClosedAccountsForUser($this->adminUser->id);

        $this->actingAs($this->adminUser)
            ->get('/accounts/'.$this->adminUser->id.'/opened')
            ->assertSuccessful()
            ->assertSeeAll($this->types->slice(0, 3)->pluck('name'), 'Expected account type names are missing')
            ->assertSeeAll($opened->pluck('code'), 'Expected account codes are missing')
            ->assertSeeAll($opened->pluck('current_balance'), 'Expected balance is missing or mistype')
            ->assertDontSeeAll($closed->pluck('code'), 'Failure: closed accounts are visible');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function opened_accounts_work_with_blocked_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $opened = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $closed = $this->seedClosedAccountsForUser($this->mainUser->id);
        $this->mainUser->blocked = true;
        $this->mainUser->save();

        $this->actingAs($this->mainUser)
            ->get('/accounts/'.$this->mainUser->id.'/opened')
            ->assertSuccessful()
            ->assertSeeAll($this->types->slice(0, 3)->pluck('name'), 'Expected account type names are missing')
            ->assertSeeAll($opened->pluck('code'), 'Expected account codes are missing')
            ->assertSeeAll($opened->pluck('current_balance'), 'Expected balance is missing or mistype')
            ->assertDontSeeAll($closed->pluck('code'), 'Failure: closed accounts are visible');
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function opened_accounts_only_show_owner_accounts()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $opened = $this->seedOpenedAccountsForUser($this->adminUser->id);
        $hidden =
            $this->seedClosedAccountsForUser($this->adminUser->id)
            ->merge($this->seedOpenedAccountsForUser($this->mainUser->id))
            ->merge($this->seedClosedAccountsForUser($this->mainUser->id));

        $this->actingAs($this->adminUser)
            ->get('/accounts/'.$this->adminUser->id.'/opened')
            ->assertSuccessful()
            ->assertSeeAll($this->types->slice(0, 3)->pluck('name'), 'Expected account type names are missing')
            ->assertSeeAll($opened->pluck('code'), 'Expected account codes are missing')
            ->assertSeeAll($opened->pluck('current_balance'), 'Expected balance is missing or mistype')
            ->assertDontSeeAll($hidden->pluck('code'), 'Failure: displays accounts not owned');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function opened_accounts_does_not_allow_impersonation()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedOpenedAccountsForUser($this->adminUser->id);
        $this->seedOpenedAccountsForUser($this->mainUser->id);

        $this->actingAs($this->mainUser)
            ->get('/accounts/'.$this->adminUser->id.'/opened')
            ->assertForbidden();
    }

}
