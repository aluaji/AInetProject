<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * As a user I want to view a summary of my financial situation, namely the total balance of all accounts
 * (user’s grand total) and the summary information about each account including the relative weight (percentage) of
 * each account over the total balance.
 */
class UserStory26Test extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_does_not_have_a_dashboard()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->get('/dashboard/'.$this->mainUser->id)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function dashboard_fails_for_invalid_user()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->actingAs($this->adminUser)
            ->get('/dashboard/220')
            ->assertNotFound();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function dashboard_fails_if_user_has_no_permissions()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->actingAs($this->adminUser)
            ->get('/dashboard/'.$this->mainUser->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_dashboard_is_available_for_a_regular_user()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->seedOpenedAccountsForUser($this->adminUser->id);
        $this->seedClosedAccountsForUser($this->adminUser->id);

        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->merge($this->seedClosedAccountsForUser($this->mainUser->id));

        $summary = $accounts->pluck('current_balance');
        $total = $summary->sum();
        $percentage = $accounts->transform(function ($account) use ($total) {
            return number_format($account->current_balance * 100 / $total, 2);
        });

        $this->actingAs($this->mainUser)
            ->get('/dashboard/'.$this->mainUser->id)
            ->assertSee(number_format($total, 2))
            ->assertSeeAll($summary->toArray(), 'Summary info is missing')
            ->assertSeeAll($percentage->toArray(), 'Percentage 0-100 with two decimal places is missing');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_dashboard_is_available_for_an_admin()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedClosedAccountsForUser($this->mainUser->id);

        $accounts = $this->seedOpenedAccountsForUser($this->adminUser->id)
            ->merge($this->seedClosedAccountsForUser($this->adminUser->id));

        $summary = $accounts->pluck('current_balance');
        $total = $summary->sum();
        $percentage = $accounts->transform(function ($account) use ($total) {
            return number_format($account->current_balance * 100 / $total, 2);
        });

        $this->actingAs($this->adminUser)
            ->get('/dashboard/'.$this->adminUser->id)
            ->assertSee(number_format($total, 2))
            ->assertSeeAll($summary->toArray(), 'Summary info is missing')
            ->assertSeeAll($percentage->toArray(), 'Percentage 0-100 with two decimal places is missing');
    }
}
