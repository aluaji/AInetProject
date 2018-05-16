<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserStory24Test extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_delete_a_document()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();

        Storage::fake('local');
        $document = $this->createPDFDocument($movement, 'document.pdf', 10, 'a pdf document');

        $this->delete('/document/'.$document->id)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_delete_fails_with_others_accounts()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();

        Storage::fake('local');
        $document = $this->createPDFDocument($movement, 'document.pdf', 10, 'a pdf document');

        $this->actingAs($this->adminUser)
            ->delete('/document/'.$document->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_delete_fails_for_non_existing_document()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();

        Storage::fake('local');
        $this->createPDFDocument($movement, 'document.pdf', 10, 'a pdf document');

        $this->actingAs($this->mainUser)
            ->delete('/document/220')
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_delete_a_document()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();

        Storage::fake('local');
        $document = $this->createPDFDocument($movement, 'document.pdf', 10, 'a pdf document');

        $this->actingAs($this->mainUser)
            ->delete('/document/'.$document->id)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('movements', [
            'id' => $movement->id,
            'document_id' => null
        ]);

        $this->assertDatabaseMissing('documents', [
            'id' => $document->id
        ]);

        $files = collect(Storage::disk('local')->allFiles($this->filesPath.'/'.$account->id));
        $this->assertCount(0, $files);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_delete_a_document()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->adminUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();

        Storage::fake('local');
        $document = $this->createPDFDocument($movement, 'document.pdf', 10, 'a pdf document');

        $this->actingAs($this->adminUser)
            ->delete('/document/'.$document->id)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('movements', [
            'id' => $movement->id,
            'document_id' => null
        ]);

        $this->assertDatabaseMissing('documents', [
            'id' => $document->id
        ]);

        $files = collect(Storage::disk('local')->allFiles($this->filesPath.'/'.$account->id));
        $this->assertCount(0, $files);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_delete_only_removes_one_entry()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movements = $this->seedTransactions($account, 'expense', 2, 11);

        Storage::fake('local');
        $document1 = $this->createPDFDocument($movements->first(), 'document.pdf', 10, 'a pdf document');
        $document2 = $this->createPDFDocument($movements->last(), 'document.pdf', 10, 'a pdf document');

        $movement = $movements->first();
        $this->actingAs($this->mainUser)
            ->delete('/document/'.$document1->id)
            ->assertSuccessfulOrRedirect();

        $this->assertDatabaseHas('movements', [
            'id' => $movement->id,
            'document_id' => null
        ]);
        $this->assertDatabaseHas('movements', [
            'id' => $movements->last()->id,
            'document_id' => $document2->id
        ]);

        $this->assertDatabaseMissing('documents', [
            'id' => $document1->id
        ]);
        $this->assertDatabaseHas('documents', [
            'id' => $document2->id
        ]);

        $files = collect(Storage::disk('local')->allFiles($this->filesPath.'/'.$account->id));
        $this->assertCount(1, $files);
    }
}
