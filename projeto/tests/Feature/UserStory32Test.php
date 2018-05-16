<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * As a user, when accessing someone else’s financial data, and documents, I shall not be allowed to change any data
 * - I cannot add, edit or delete anything that belongs to the other user.
 */
class UserStory32Test extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_cannot_delete_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);
        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->actingAs($this->users[0])
            ->delete('/account/'.$account->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_of_cannot_delete_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);
        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->delete('/account/'.$account->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_cannot_close_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);
        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->actingAs($this->users[0])
            ->delete('/account/'.$account->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_of_cannot_close_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id);
        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->delete('/account/'.$account->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_cannot_reopen_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'deleted_at' => Carbon::now()
        ]);
        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->actingAs($this->users[0])
            ->patch('/account/'.$account->id.'/reopen')
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function associates_of_cannot_reopen_an_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedUserAccount($this->mainUser->id, $this->types[0]->id, [
            'deleted_at' => Carbon::now()
        ]);
        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->patch('/account/'.$account->id.'/reopen')
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_fails_by_associates()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => $account->start_balance,
        ];
        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->actingAs($this->users[0])
            ->put('/account/'.$account->id, $data)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function account_update_fails_by_associates_of()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'account_type_id' => $this->types->last()->id,
            'code' => $this->faker->uuid,
            'date' => $account->date,
            'start_balance' => $account->start_balance,
        ];
        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->put('/account/'.$account->id, $data)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_with_associates()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
        ];
        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->actingAs($this->users[0])
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_with_associates_of()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
        ];
        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_with_associates()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
        ];

        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->actingAs($this->users[0])
            ->put('/movement/'.$movement->id, $data)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_with_associates_of()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
        ];

        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->put('/movement/'.$movement->id, $data)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_delete_fails_with_associates()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->actingAs($this->users[0])
            ->delete('/movement/'.$movement->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_delete_fails_with_associates_of()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->delete('/movement/'.$movement->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_association_fails_with_associates()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        Storage::fake('local');
        $file = UploadedFile::fake()->create('document.pdf', 10);
        $data = [
            'document_description' => 'a document',
            'document_file' => $file
        ];

        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->actingAs($this->users[0])
            ->post('/documents/'.$movement->id, $data)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_association_fails_with_associates_of()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        Storage::fake('local');
        $file = UploadedFile::fake()->create('document.pdf', 10);
        $data = [
            'document_description' => 'a document',
            'document_file' => $file
        ];

        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->post('/documents/'.$movement->id, $data)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_delete_fails_with_associates()
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

        DB::table('associate_members')->insert([
            'main_user_id' => $this->mainUser->id,
            'associated_user_id' => $this->users[0]->id,
        ]);

        $this->actingAs($this->users[0])
            ->delete('/document/'.$document->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_delete_fails_with_associates_of()
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

        DB::table('associate_members')->insert([
            'main_user_id' => $this->users[0]->id,
            'associated_user_id' => $this->mainUser->id,
        ]);

        $this->actingAs($this->users[0])
            ->delete('/document/'.$document->id)
            ->assertForbidden();
    }


}
