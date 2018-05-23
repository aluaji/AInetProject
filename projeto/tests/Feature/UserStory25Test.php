<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * As a user I want to view/download documents from the associated movements. Only I and users that belong to my group
 * of associate members can view/download these documents.
 */
class UserStory25Test extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_view_a_document()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();

        Storage::fake('local');
        [$document] = $this->createPDFDocument($movement, 'document.pdf', 10, 'a pdf document', true);

        $this->get('/document/'.$document->id)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_view_fails_with_others_accounts()
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

        $this->actingAs($this->adminUser)
            ->get('/document/'.$document->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_view_fails_for_non_existing_document()
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
            ->get('/document/220')
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_view_a_document()
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

        $this->actingAs($this->mainUser)
            ->get('/document/'.$document->id)
            ->assertSuccessful()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="document.pdf"');
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_view_a_document()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->adminUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();

        Storage::fake('local');
        [$document, $file] = $this->createPDFDocument($movement, 'document.pdf', 10, 'a pdf document', true);

        $this->actingAs($this->adminUser)
            ->get('/document/'.$document->id)
            ->assertSuccessful()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="document.pdf"');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_view_a_png()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();

        Storage::fake('local');
        [$document, $file] = $this->createPNGDocument($movement, 'receipt.png','a receipt', true);

        $this->actingAs($this->mainUser)
            ->get('/document/'.$document->id)
            ->assertSuccessful()
            ->assertHeader('content-type', 'image/png')
            ->assertHeader('content-disposition', 'attachment; filename="receipt.png"');
    }

}
