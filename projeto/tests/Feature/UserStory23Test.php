<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * As a user I want to add (by uploading) documents (pdf, png or jpeg) and associate each one with a specific movement.
 */
class UserStory23Test extends BaseAccountsTest
{
    // @codingStandardsIgnoreStart
    /** @test */
    public function a_guest_cannot_associate_documents_to_movements()
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

        $this->post('/documents/'.$movement->id, $data)
            ->assertRedirect('/login');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_association_fails_with_others_accounts()
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

        $this->actingAs($this->adminUser)
            ->post('/documents/'.$movement->id, $data)
            ->assertForbidden();
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_association_fails_with_invalid_movement()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $this->seedTransactions($account, 'revenue', 1)
            ->first();

        Storage::fake('local');
        $file = UploadedFile::fake()->create('document.pdf', 10);
        $data = [
            'document_description' => 'a document',
            'document_file' => $file
        ];

        $this->actingAs($this->mainUser)
            ->post('/documents/220', $data)
            ->assertStatus(404);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_association_fails_with_invalid_document()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'document_file' => 'just text'
        ];

        $this->actingAs($this->mainUser)
            ->post('/documents/'.$movement->id, $data)
            ->assertSessionHasErrors(['document_file'])
            ->assertSessionHasNoErrors(['document_description']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_association_fails_with_invalid_mime()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'document_file' => UploadedFile::fake()->create('document.docx', 10)
        ];

        $this->actingAs($this->mainUser)
            ->post('/documents/'.$movement->id, $data)
            ->assertSessionHasErrors(['document_file'])
            ->assertSessionHasNoErrors(['document_description']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function document_association_fails_without_file()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $account = $this->seedOpenedAccountsForUser($this->mainUser->id)
            ->first();
        $movement = $this->seedTransactions($account, 'expense', 1)
            ->first();
        $data = [
            'document_description' => 'just a description'
        ];

        $this->actingAs($this->mainUser)
            ->post('/documents/'.$movement->id, $data)
            ->assertSessionHasErrors(['document_file'])
            ->assertSessionHasNoErrors(['document_description']);
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
            'document_description' => 'a document',
            'document_file' => $file
        ];

        $this->actingAs($this->mainUser)
            ->post('/documents/'.$movement->id, $data)
            ->assertSessionHasNoErrors(['document_file', 'document_description']);

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
    public function an_admin_can_add_a_document_to_an_existing_movement()
    {
        // @codingStandardsIgnoreEnd
        // Given, When, Then
        $accounts = $this->seedOpenedAccountsForUser($this->adminUser->id);
        $this->seedTransactions($accounts->last(), 'mixed', 10);
        $account = $accounts->first();
        $movement = $this->seedTransactions($account, 'expense', 1, 11)
            ->first();
        Storage::fake('local');
        $file = UploadedFile::fake()->create('document.pdf', 10);
        $data = [
            'document_description' => 'a document',
            'document_file' => $file
        ];

        $this->actingAs($this->adminUser)
            ->post('/documents/'.$movement->id, $data)
            ->assertSessionHasNoErrors(['document_file', 'document_description']);

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
            'document_description' => 'a receipt',
            'document_file' => $file
        ];

        $this->actingAs($this->mainUser)
            ->post('/documents/'.$movement->id, $data)
            ->assertSessionHasNoErrors(['document_file', 'document_description']);

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
            'document_description' => 'a receipt',
            'document_file' => $file
        ];

        $this->actingAs($this->mainUser)
            ->post('/documents/'.$movement->id, $data)
            ->assertSessionHasNoErrors(['document_file', 'document_description']);

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
            'document_description' => 'a receipt',
            'document_file' => $file
        ];

        $this->actingAs($this->mainUser)
            ->post('/documents/'.$movement->id, $data)
            ->assertSessionHasNoErrors(['document_file', 'document_description']);

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
