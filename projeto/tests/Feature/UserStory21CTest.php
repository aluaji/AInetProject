<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserStory21CTest extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_delete_a_movement()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $this->delete('/movement/'.$movement->id)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_delete_fails_with_others_accounts()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $this->actingAs($this->adminUser)
            ->delete('/movement/'.$movement->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_delete_fails_for_non_existing_movement()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $this->actingAs($this->adminUser)
            ->delete('/movement/220')
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_delete_a_movement()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $this->actingAs($this->mainUser)
            ->delete('/movement/'.$movement->id)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseMissing('movements', [
            'id' => $movement->id
        ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_delete_a_movement()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->adminUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $this->actingAs($this->adminUser)
            ->delete('/movement/'.$movement->id)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseMissing('movements', [
            'id' => $movement->id
        ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function deleting_a_movement_also_deletes_associate_document()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movements = $this->seedTransactions($account, 'expense', 2, 11);

        Storage::fake('local');
        $this->createPDFDocument($movements->first(), 'document.pdf', 10, 'a pdf document');
        $this->createPDFDocument($movements->last(), 'document.pdf', 10, 'a pdf document');

        $movement = $movements->first();
        $this->actingAs($this->mainUser)
            ->delete('/movement/'.$movement->id)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseMissing('movements', [
            'id' => $movement->id
        ]);
        $this->assertDatabaseMissing('documents', [
            'id' => $movement->document_id
        ]);

        $files = collect(Storage::disk('local')->allFiles($this->filesPath.'/'.$account->id));
        $this->assertCount(1, $files);
    }

}
