<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * As a user I want to access all financial information and view/download documents of any user whose group of associate
 * members I belong to – the fact that a user belongs to my group of associate members does not grant me authorization
 * to access its financial data.
 */
class UserStory31Test extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function accounts_index_is_accessible_by_associates()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $accounts = $accounts->merge($this->seedClosedAccountsForUser($this->mainUser->id));

        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->actingAs($this->users[0])
            ->get('/accounts/'.$this->mainUser->id)
            ->assertSuccessful()
            ->assertSeeAll($this->types->pluck('name'), 'Expected account type names are missing')
            ->assertSeeAll($accounts->pluck('code'), 'Expected account codes are missing')
            ->assertSeeAll($accounts->pluck('current_balance'), 'Expected balance is missing or mistype');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function accounts_index_is_not_accessible_by_associates_of()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $accounts = $accounts->merge($this->seedClosedAccountsForUser($this->mainUser->id));

        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->get('/accounts/'.$this->mainUser->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_can_get_movements()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $this->seedTransactions($account, 'mixed', 8);
        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->response = $this->actingAs($this->users[0])
            ->get('/movements/'.$account->id)
            ->assertSuccessful();

        $this->assertOrderedMovements();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_of_cannot_get_movements()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->get('/movements/'.$account->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_associate_can_view_a_document()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();

        Storage::fake('local');
        [$document, $file] = $this->createPDFDocument($movement, 'document.pdf', 10, 'a pdf document', true);

        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->actingAs($this->users[0])
            ->get('/document/'.$document->id)
            ->assertSuccessful()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="document.pdf"');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_associate_of_cannot_view_a_document()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();

        Storage::fake('local');
        [$document] = $this->createPDFDocument($movement, 'document.pdf', 10, 'a pdf document', true);

        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->get('/document/'.$document->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_dashboard_is_available_for_associates()
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

        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->actingAs($this->users[0])
            ->get('/dashboard/'.$this->mainUser->id)
            ->assertSee(number_format($total, 2))
            ->assertSeeAll($summary->toArray(), 'Summary info is missing')
            ->assertSeeAll($percentage->toArray(), 'Percentage 0-100 with two decimal places is missing');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_dashboard_is_not_available_for_associates_of()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $this->seedOpenedAccountsForUser($this->adminUser->id);
        $this->seedClosedAccountsForUser($this->adminUser->id);

        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->merge($this->seedClosedAccountsForUser($this->mainUser->id));

        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->get('/dashboard/'.$this->mainUser->id)
            ->assertForbidden();
    }
}
