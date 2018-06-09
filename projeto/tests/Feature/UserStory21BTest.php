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
 * As a user I want to add, edit or delete movements of an account. Movement data should include the type
 * (revenue or expense), category, date (no time information is required), value and a description (optional).
 * It might also include a link to the associated document (US24). Note that start and end balance are to be
 * calculated automatically – user should not edit these values;
 */
class UserStory21BTest extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_get_movement_edit_form()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $this->get('/movement/'.$movement->id)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_cannot_get_form_for_nonexisting_movement()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $this->actingAs($this->mainUser)
            ->get('/movement/220')
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_get_movement_edit_form()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $this->actingAs($this->mainUser)
            ->get('/movement/'.$movement->id)
            ->assertStatus(200);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_user_can_get_movement_edit_form()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->adminUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $this->actingAs($this->adminUser)
            ->get('/movement/'.$movement->id)
            ->assertStatus(200);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_user_cannot_get_movement_edit_for_others_accounts()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $this->actingAs($this->adminUser)
            ->get('/movement/'.$movement->id)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_update_a_movement()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $data = [
            'movement_category_id' => $this->categories['expense']->first()->id,
            'date' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'value' => 2,
            'description' => 'new description',
        ];

        $this->put('/movement/'.$movement->id, $data)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_for_non_existing_movement()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $data = [
            'movement_category_id' => $this->categories['expense']->first()->id,
            'date' => Carbon::now()->subDays(10)->format('Y-m-d'),
            'value' => 2,
            'description' => 'new description',
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/220', $data)
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_with_empty_form()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id)
            ->assertSessionHasErrors(['movement_category_id', 'date', 'value'])
            ->assertSessionHasNoErrors(['description', 'document_file', 'document_description']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_with_invalid_type()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();
        $data = [
            'movement_category_id' => 200000,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 1,
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasErrors(['movement_category_id'])
            ->assertSessionHasNoErrors(['date', 'value', 'description', 'document_file', 'document_description']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_with_invalid_date()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->first()->id,
            'date' => '99999999',
            'value' => 1,
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasErrors(['date'])
            ->assertSessionHasNoErrors([
                'movement_category_id', 'value', 'description', 'document_file', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_with_non_numeric_value()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->first()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => '89asd',
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasErrors(['value'])
            ->assertSessionHasNoErrors([
                'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_with_zero_value()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->first()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0,
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasErrors(['value'])
            ->assertSessionHasNoErrors([
                'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_with_a_negative_value()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->first()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => -10,
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasErrors(['value'])
            ->assertSessionHasNoErrors([
                'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_with_others_accounts()
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

        $this->actingAs($this->adminUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_switch_from_revenue_to_expense()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'revenue', 1)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->random()->id,
            'date' => $movement->date,
            'value' => $movement->value,
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['id'] = $movement->id;
        $data['type'] = 'expense';
        $data['created_at'] = $movement->created_at;

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_switch_from_expense_to_revenue()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => $movement->date,
            'value' => $movement->value,
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['id'] = $movement->id;
        $data['type'] = 'revenue';
        $data['created_at'] = $movement->created_at;

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function update_use_proper_rule_to_validate_movement_category_id()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        DB::table('movement_categories')->insert(['id' => 2000000, 'name' => 'just a new type', 'type' => 'expense']);

        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => 2000000,
            'date' => $movement->date,
            'value' => $movement->value,
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['id'] = $movement->id;
        $data['created_at'] = $movement->created_at;

        $this->assertDatabaseHas('movements', $data);
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_change_movement_date()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => Carbon::now()->subDays(35)->format('Y-m-d'),
            'value' => $movement->value,
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['id'] = $movement->id;
        $data['type'] = $movement->type;
        $data['created_at'] = $movement->created_at;

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_change_movement_value()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => 123.45,
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['id'] = $movement->id;
        $data['type'] = $movement->type;
        $data['created_at'] = $movement->created_at;

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_change_description()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'description' => 'description was updated'
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['id'] = $movement->id;
        $data['type'] = $movement->type;
        $data['created_at'] = $movement->created_at;

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_update_a_movement()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->adminUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => 123.45,
        ];

        $this->actingAs($this->adminUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['id'] = $movement->id;
        $data['type'] = $movement->type;
        $data['created_at'] = $movement->created_at;

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_cannot_switch_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $movement = $this->seedTransactions($accounts->first(), 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'account_id' => $accounts->last()->id
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['id'] = $movement->id;
        $data['type'] = $movement->type;
        $data['created_at'] = $movement->created_at;
        $data['account_id'] = $movement->account_id;

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_cannot_set_the_type_column()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'type' => 'revenue'
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['id'] = $movement->id;
        $data['type'] = $movement->type;
        $data['created_at'] = $movement->created_at;

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_cannot_set_start_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'start_balance' => $movement->start_balance + 5,
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['id'] = $movement->id;
        $data['start_balance'] = $movement->start_balance;
        $data['created_at'] = $movement->created_at;

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_cannot_set_end_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'end_balance' => $movement->end_balance + 5,
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['id'] = $movement->id;
        $data['end_balance'] = $movement->end_balance;
        $data['created_at'] = $movement->created_at;

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_cannot_set_created_at()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'created_at' => Carbon::now()->subDays(150)->format('Y-m-d')
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['id'] = $movement->id;
        $data['created_at'] = $movement->created_at;

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_with_invalid_document()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'document_file' => 'just text'
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasErrors(['document_file'])
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_with_invalid_mime()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'document_file' => UploadedFile::fake()->create('document.docx', 10)
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasErrors(['document_file'])
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_update_fails_with_document_description_without_file()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'document_description' => 'documentas adasdasd'
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasErrors(['document_file'])
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_add_a_document_to_an_existing_movement()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();
        Storage::fake('local');
        $file = UploadedFile::fake()->create('document.pdf', 10);
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'document_description' => 'a document',
            'document_file' => $file
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $this->assertDatabaseHas('documents', [
            'type' => 'pdf',
            'original_name' => 'document.pdf',
            'description' => 'a document',
        ]);
        $document = DB::table('documents')->first();
        $this->assertNotNull($document->created_at, 'Document created_at is null');

        $data['id'] = $movement->id;
        $data['created_at'] = $movement->created_at;

        $expects = [
            'id' => $movement->id,
            'document_id' => $document->id,
            'created_at' => $movement->created_at,
        ];
        $this->assertDatabaseHas('movements', $expects);

        $movement = DB::table('movements')->where($expects)->first();

        $files = collect(Storage::disk('local')->allFiles($this->filesPath.'/'.$account->id));
        $this->assertCount(1, $files);
        $this->assertEquals(basename($files[0]), $movement->id.'.'.$document->type);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_add_an_image_to_an_existing_movement()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();
        Storage::fake('local');
        $file = UploadedFile::fake()->image('receipt.png');
        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'document_description' => 'a receipt',
            'document_file' => $file
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $this->assertDatabaseHas('documents', [
            'type' => 'png',
            'original_name' => 'receipt.png',
            'description' => 'a receipt',
        ]);
        $document = DB::table('documents')->first();
        $this->assertNotNull($document->created_at, 'Document created_at is null');

        $data['id'] = $movement->id;
        $data['created_at'] = $movement->created_at;

        $expects = [
            'id' => $movement->id,
            'document_id' => $document->id,
            'created_at' => $movement->created_at,
        ];
        $this->assertDatabaseHas('movements', $expects);

        $movement = DB::table('movements')->where($expects)->first();

        $files = collect(Storage::disk('local')->allFiles($this->filesPath.'/'.$account->id));
        $this->assertCount(1, $files);
        $this->assertEquals(basename($files[0]), $movement->id.'.'.$document->type);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_replace_a_movement_document()
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
        $file = UploadedFile::fake()->create('receipt.pdf', 50);

        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'document_description' => 'a receipt',
            'document_file' => $file
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $this->assertDatabaseHas('documents', [
            'type' => 'pdf',
            'original_name' => 'receipt.pdf',
            'description' => 'a receipt',
        ]);
        $this->assertEquals(1, DB::table('documents')->count(), 'Expects only one document record');

        $document = DB::table('documents')->first();
        $this->assertNotNull($document->created_at, 'Document created_at is null');

        $data['id'] = $movement->id;
        $data['created_at'] = $movement->created_at;

        $expects = [
            'id' => $movement->id,
            'document_id' => $document->id,
            'created_at' => $movement->created_at,
        ];
        $this->assertDatabaseHas('movements', $expects);

        $movement = DB::table('movements')->where($expects)->first();

        $files = collect(Storage::disk('local')->allFiles($this->filesPath.'/'.$account->id));
        $this->assertCount(1, $files);
        $this->assertEquals(basename($files[0]), $movement->id.'.'.$document->type);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_switch_movement_documents()
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
        $file = UploadedFile::fake()->image('receipt.png');

        $data = [
            'movement_category_id' => $movement->movement_category_id,
            'date' => $movement->date,
            'value' => $movement->value,
            'document_description' => 'a receipt',
            'document_file' => $file
        ];

        $this->actingAs($this->mainUser)
            ->put('/movement/'.$movement->id, $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $this->assertDatabaseHas('documents', [
            'type' => 'png',
            'original_name' => 'receipt.png',
            'description' => 'a receipt',
        ]);
        $this->assertEquals(1, DB::table('documents')->count(), 'Expects only one document record');

        $document = DB::table('documents')->first();
        $this->assertNotNull($document->created_at, 'Document created_at is null');

        $data['id'] = $movement->id;
        $data['created_at'] = $movement->created_at;

        $expects = [
            'id' => $movement->id,
            'document_id' => $document->id,
            'created_at' => $movement->created_at,
        ];
        $this->assertDatabaseHas('movements', $expects);

        $movement = DB::table('movements')->where($expects)->first();

        $files = collect(Storage::disk('local')->allFiles($this->filesPath.'/'.$account->id));
        $this->assertCount(1, $files);
        $this->assertEquals(basename($files[0]), $movement->id.'.'.$document->type);
    }
}
