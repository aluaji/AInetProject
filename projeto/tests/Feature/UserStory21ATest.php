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
class UserStory21ATest extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_get_movement_creation_form()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $this->get('/movements/'.$account->id.'/create')
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_cannot_get_the_movement_form_for_invalid_account()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $this->actingAs($this->mainUser)
            ->get('/movements/220/create')
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_get_movement_creation_form()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $this->actingAs($this->mainUser)
            ->get('/movements/'.$account->id.'/create')
            ->assertStatus(200);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_user_can_get_movement_creation_form()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->adminUser->id)
            ->first();

        $this->actingAs($this->adminUser)
            ->get('/movements/'.$account->id.'/create')
            ->assertStatus(200);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_user_cannot_get_movement_creation_for_others_accounts()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();

        $this->actingAs($this->adminUser)
            ->get('/movements/'.$account->id.'/create')
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_create_a_movement()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->first()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'description' => 'new description',
        ];

        $this->post('/movements/'.$account->id.'/create', $data)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_for_non_existing_account()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->first()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'description' => 'new description',
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/220/create', $data)
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_with_empty_form()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create')
            ->assertSessionHasErrors(['movement_category_id', 'date', 'value'])
            ->assertSessionHasNoErrors(['description', 'document_file', 'document_description']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_with_invalid_type()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => 200000,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 1,
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasErrors(['movement_category_id'])
            ->assertSessionHasNoErrors(['date', 'value', 'description', 'document_file', 'document_description']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_use_proper_rule_to_validate_movement_category_id()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        DB::table('movement_categories')->insert(['id' => 2000000, 'name' => 'just a new type', 'type' => 'expense']);

        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => 2000000,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 1,
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasNoErrors(['movement_category_id']);
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_with_invalid_date()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->first()->id,
            'date' => '99999999',
            'value' => 1,
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasErrors(['date'])
            ->assertSessionHasNoErrors([
                'movement_category_id', 'value', 'description', 'document_file', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_with_non_numeric_value()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->first()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => '89asd',
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasErrors(['value'])
            ->assertSessionHasNoErrors([
                'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_with_zero_value()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->first()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0,
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasErrors(['value'])
            ->assertSessionHasNoErrors([
                'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_with_a_negative_value()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['expense']->first()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => -10,
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasErrors(['value'])
            ->assertSessionHasNoErrors([
                'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_with_others_accounts()
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

        $this->actingAs($this->adminUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_create_an_expense_movement()
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

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['account_id'] = $account->id;
        $data['type'] = 'expense';

        $this->assertDatabaseHas('movements', $data);
        $this->assertDatabaseMissing('movements', ['created_at' => null]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_create_an_revenue_movement()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['account_id'] = $account->id;
        $data['type'] = 'revenue';

        $this->assertDatabaseHas('movements', $data);
        $this->assertDatabaseMissing('movements', ['created_at' => null]);
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_create_an_revenue_movement_with_description()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'description' => 'movement description'
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['account_id'] = $account->id;
        $data['type'] = 'revenue';

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function an_admin_can_create_a_movement()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->adminUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
        ];

        $this->actingAs($this->adminUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['account_id'] = $account->id;
        $data['type'] = 'revenue';

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_cannot_impersonate_account()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $adminAccount = $this->seedOpenedAccountsForUser($this->adminUser->id)
            ->first();
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'account_id' => $account->id
        ];

        $this->actingAs($this->adminUser)
            ->post('/movements/'.$adminAccount->id.'/create', $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['account_id'] = $adminAccount->id;
        $data['type'] = 'revenue';

        $this->assertDatabaseHas('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_cannot_set_the_type_column()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'type' => 'expense'
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['account_id'] = $account->id;

        $this->assertDatabaseMissing('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_cannot_set_start_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'start_balance' => 9999
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['account_id'] = $account->id;
        $data['type'] = 'revenue';

        $this->assertDatabaseMissing('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_cannot_set_end_balance()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'end_balance' => 9999
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['account_id'] = $account->id;
        $data['type'] = 'revenue';

        $this->assertDatabaseMissing('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_cannot_set_created_at()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'created_at' => Carbon::now()->subDays(4)->format('Y-m-d H:i:s')
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $data['account_id'] = $account->id;
        $data['type'] = 'revenue';

        $this->assertDatabaseMissing('movements', $data);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_with_invalid_document()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'document_file' => 'just text'
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasErrors(['document_file'])
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_with_invalid_mime()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'document_file' => UploadedFile::fake()->create('document.docx', 10)
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasErrors(['document_file'])
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function movement_creation_fails_with_document_description_without_document()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'document_description' => 'documentas adasdasd'
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasErrors(['document_file'])
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_description'
            ]);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_create_a_movement_with_a_document()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        Storage::fake('local');
        $file = UploadedFile::fake()->create('document.pdf', 10);
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'document_description' => 'a document',
            'document_file' => $file
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
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

        $expects = [
            'movement_category_id' => $data['movement_category_id'],
            'date' => $data['date'],
            'value' => $data['value'],
            'type' => 'revenue',
            'document_id' => $document->id,
            'account_id' => $account->id,
        ];
        $this->assertDatabaseHas('movements', $expects);

        $movement = DB::table('movements')->where($expects)->first();

        $files = collect(Storage::disk('local')->allFiles($this->filesPath.'/'.$account->id));
        $this->assertCount(1, $files);
        $this->assertEquals(basename($files[0]), $movement->id.'.'.$document->type);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_regular_user_can_create_a_movement_with_a_image_document()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->mainUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        Storage::fake('local');
        $file = UploadedFile::fake()->image('receipt.png');
        $data = [
            'movement_category_id' => $this->categories['revenue']->random()->id,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'value' => 0.1,
            'document_description' => 'another document',
            'document_file' => $file
        ];

        $this->actingAs($this->mainUser)
            ->post('/movements/'.$account->id.'/create', $data)
            ->assertSessionHasNoErrors([
                'value', 'movement_category_id', 'date', 'description', 'document_file', 'document_description'
            ]);

        $this->assertDatabaseHas('documents', [
            'type' => 'png',
            'original_name' => 'receipt.png',
            'description' => 'another document',
        ]);
        $document = DB::table('documents')->first();
        $this->assertNotNull($document->created_at, 'Document created_at is null');

        $expects = [
            'movement_category_id' => $data['movement_category_id'],
            'date' => $data['date'],
            'value' => $data['value'],
            'type' => 'revenue',
            'document_id' => $document->id,
            'account_id' => $account->id,
        ];
        $this->assertDatabaseHas('movements', $expects);

        $movement = DB::table('movements')->where($expects)->first();

        $files = collect(Storage::disk('local')->allFiles($this->filesPath.'/'.$account->id));
        $this->assertCount(1, $files);
        $this->assertEquals(basename($files[0]), $movement->id.'.'.$document->type);
    }
}
