<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\TestCase;

/**
 * As an anonymous user I want to register as a new user of the application. Registration data should include user’s
 * name (only spaces and letters), e-mail (must be unique), password (3 or more characters), phone number (optional)
 * and a photo (optional - by uploading an image).
 */
class UserStory02Test extends UserStoryTestCase
{
    private $photoPath = 'profiles';

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_route_exists()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->get('/register')
            ->assertStatus(200);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function the_register_use_proper_enctype()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->get('/register')
            ->assertStatus(200)
            ->assertSee('enctype="multipart/form-data"');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_fails_with_empty_form()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert

        $this->post('/register')
            ->assertSessionHasErrors(['email', 'password', 'name'])
            ->assertSessionHasNoErrors(['phone', 'profile_photo']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_fails_with_a_name_with_numbers()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert

        $this->post('/register', [
            'name' => 'name12345'
        ])->assertSessionHasErrors('name');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_fails_with_a_name_with_underscore()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert

        $this->post('/register', [
            'name' => 'name_'
        ])->assertSessionHasErrors('name');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_fails_with_whitespace_only_name()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert

        $this->post('/register', [
            'name' => '        '
        ])->assertSessionHasErrors('name');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_fails_with_existing_email()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->post('/register', [
            'email' => 'user@mail.pt'
        ])->assertSessionHasErrors('email');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_fails_with_invalid_email()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert

        $this->post('/register', [
            'email' => 'user@mail'
        ])->assertSessionHasErrors('email');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_fails_with_invalid_password()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert

        $this->post('/register', [
            'password' => '12',
            'password_confirmation' => '12',
        ])->assertSessionHasErrors('password');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_succeeds_with_mandatory_fields()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert

        $this->post('/register', [
            'email' => 'new@mail.pt',
            'name' => 'letters and spaces',
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertSessionHasNoErrors(['name', 'email', 'password']);

        $this->assertDatabaseHas('users', ['email' => 'new@mail.pt']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_fails_with_letters_in_phone()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->post('/register', [
            'email' => 'new@mail.pt',
            'name' => 'letters and spaces',
            'password' => '123',
            'password_confirmation' => '123',
            'phone' => '+sd 123 232'
        ])->assertSessionHasErrors('phone');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_fails_with_parenthesis_in_phone()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->post('/register', [
            'email' => 'new@mail.pt',
            'name' => 'letters and spaces',
            'password' => '123',
            'password_confirmation' => '123',
            'phone' => '+323 (123) 232'
        ])->assertSessionHasErrors('phone');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_succeeds_with_phone_number()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert

        $this->post('/register', [
            'email' => 'new@mail.pt',
            'name' => 'letters and spaces',
            'password' => '123',
            'password_confirmation' => '123',
            'phone' => '+351 244 800 900'
        ])->assertSessionHasNoErrors(['name', 'email', 'password', 'phone']);


        $user = User::where('email', 'new@mail.pt')->first();
        $this->assertNotNull($user);
        $this->assertEquals('+351 244 800 900', $user->phone);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_fails_with_text_profile_photo()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->post('/register', [
            'email' => 'new@mail.pt',
            'name' => 'letters and spaces',
            'password' => '123',
            'password_confirmation' => '123',
            'profile_photo' => 'just text'
        ])->assertSessionHasErrors('profile_photo');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_fails_with_document_profile_photo()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->post('/register', [
            'email' => 'new@mail.pt',
            'name' => 'letters and spaces',
            'password' => '123',
            'password_confirmation' => '123',
            'profile_photo' => UploadedFile::fake()->create('document.pdf', 10)
        ])->assertSessionHasErrors('profile_photo');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_succeeds_with_png_profile_photo()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        Storage::fake('public');

        $file = UploadedFile::fake()->image('selfie.png');

        $this->post('/register', [
            'email' => 'new@mail.pt',
            'name' => 'letters and spaces',
            'password' => '123',
            'password_confirmation' => '123',
            'profile_photo' => $file
        ])->assertSessionHasNoErrors(['name', 'email', 'password', 'profile_photo']);

        $user = User::where('email', 'new@mail.pt')->first();
        $this->assertNotNull($user);
        $files = collect(Storage::disk('public')->allFiles($this->photoPath));
        $this->assertCount(1, $files);
        $this->assertEquals(basename($files[0]), $user->profile_photo);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_succeeds_with_jpg_profile_photo()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        Storage::fake('public');

        $file = UploadedFile::fake()->image('selfie.jpg');
        $this->post('/register', [
            'email' => 'new@mail.pt',
            'name' => 'letters and spaces',
            'password' => '123',
            'password_confirmation' => '123',
            'profile_photo' => $file
        ])->assertSessionHasNoErrors(['name', 'email', 'password', 'profile_photo']);

        $user = User::where('email', 'new@mail.pt')->first();
        $this->assertNotNull($user);
        $files = collect(Storage::disk('public')->allFiles($this->photoPath));
        $this->assertCount(1, $files);
        $this->assertEquals(basename($files[0]), $user->profile_photo);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_register_succeeds_with_same_photo_on_different_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        Storage::fake('public');
        $file = UploadedFile::fake()->image('selfie.png');
        $file->store('profiles', 'public');
        factory(User::class)->create([
            'email' => 'user1@mail.pt',
            'name' => 'letters and spaces',
            'password' => bcrypt('123'),
            'profile_photo' => $file->hashName()
        ]);

        $this->post('/register', [
            'email' => 'user2@mail.pt',
            'name' => 'letters and spaces',
            'password' => '123',
            'password_confirmation' => '123',
            'profile_photo' => $file
        ])->assertSessionHasNoErrors(['name', 'email', 'password', 'profile_photo']);

        $users = User::all();
        $this->assertCount(2, $users);

        $files = collect(Storage::disk('public')->allFiles($this->photoPath));
        $namesFromProfile = $users->map->profile_photo->unique()->sort();
        $namesFromDisk = $files->map(function ($name) {
            return basename($name);
        })->sort();
        $this->assertEquals($namesFromDisk->values()->all(), $namesFromProfile->values()->all());
    }
}
