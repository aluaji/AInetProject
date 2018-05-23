<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\TestCase;

/**
 * As a user I should be able to update my profile, specifically: my name, e-mail, phone number and photo
 * (by uploading an image). When updating the e-mail, the application must guarantee that the new e-mail is
 * unique among all users (including blocked users).
 */
class UserStory10Test extends UserStoryTestCase
{
    private $photoPath = 'profiles';

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_fails_with_empty_form()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->put('/me/profile')
            ->assertSessionHasErrors(['email', 'name'])
            ->assertSessionHasNoErrors(['phone', 'profile_photo']);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_fails_with_a_name_with_numbers()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'name' => 'name12345'
            ])
            ->assertSessionHasErrors('name');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_fails_with_a_name_with_underscore()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
            'name' => 'name_'
            ])
            ->assertSessionHasErrors('name');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_fails_with_whitespace_only_name()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'name' => '        '
            ])
            ->assertSessionHasErrors('name');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_fails_with_existing_email()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();
        $this->seedUser('teste', 'new@mail.pt');

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt'
            ])
            ->assertSessionHasErrors('email');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_fails_with_invalid_email()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'user@mail'
            ])
            ->assertSessionHasErrors('email');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_succeeds_with_mandatory_fields()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();
        $password = $this->mainUser->password;

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces',
            ])
            ->assertSessionHasNoErrors(['name', 'email']);

        $this->mainUser->refresh();
        $this->assertEquals('new@mail.pt', $this->mainUser->email);
        $this->assertEquals('letters and spaces', $this->mainUser->name);
        $this->assertEquals($password, $this->mainUser->password);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_succeeds_keeping_email()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();
        $password = $this->mainUser->password;
        $email = $this->mainUser->email;

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => $email,
                'name' => 'letters and spaces',
            ])
            ->assertSessionHasNoErrors(['name', 'email']);

        $this->mainUser->refresh();
        $this->assertEquals($email, $this->mainUser->email);
        $this->assertEquals('letters and spaces', $this->mainUser->name);
        $this->assertEquals($password, $this->mainUser->password);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_succeeds_with_mandatory_fields_for_admins()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedAdminUser();
        $password = $this->adminUser->password;

        $this->actingAs($this->adminUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces',
            ])
            ->assertSessionHasNoErrors(['name', 'email']);

        $this->adminUser->refresh();
        $this->assertEquals('new@mail.pt', $this->adminUser->email);
        $this->assertEquals('letters and spaces', $this->adminUser->name);
        $this->assertEquals($password, $this->adminUser->password);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_fails_with_letters_in_phone()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces',
                'phone' => '+sd 123 232'
            ])
            ->assertSessionHasErrors('phone');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_fails_with_parenthesis_in_phone()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces',
                'phone' => '+323 (123) 232'
            ])
            ->assertSessionHasErrors('phone');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_succeeds_with_non_empty_phone_number()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();
        $password = $this->mainUser->password;

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces',
                'phone' => '+351 244 800 900'
            ])
            ->assertSessionHasNoErrors(['name', 'email', 'phone']);

        $this->mainUser->refresh();
        $this->assertEquals('new@mail.pt', $this->mainUser->email);
        $this->assertEquals('letters and spaces', $this->mainUser->name);
        $this->assertEquals('+351 244 800 900', $this->mainUser->phone);
        $this->assertEquals($password, $this->mainUser->password);
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_succeeds_with_empty_phone_number()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();
        $password = $this->mainUser->password;
        $this->mainUser->phone = '123123123';
        $this->mainUser->save();

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces'
            ])
            ->assertSessionHasNoErrors(['name', 'email', 'phone']);

        $this->mainUser->refresh();
        $this->assertEquals('new@mail.pt', $this->mainUser->email);
        $this->assertEquals('letters and spaces', $this->mainUser->name);
        $this->assertNull($this->mainUser->phone);
        $this->assertEquals($password, $this->mainUser->password);
    }


    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_fails_with_text_profile_photo()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces',
                'profile_photo' => 'just text'
            ])
            ->assertSessionHasErrors('profile_photo');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_fails_with_document_profile_photo()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces',
                'profile_photo' => UploadedFile::fake()->create('document.pdf', 10)
            ])
            ->assertSessionHasErrors('profile_photo');
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_succeeds_with_png_profile_photo()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();
        $password = $this->mainUser->password;
        Storage::fake('public');
        $file = UploadedFile::fake()->image('selfie.png');

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces',
                'profile_photo' => $file
            ])
            ->assertSessionHasNoErrors(['name', 'email', 'profile_photo']);

        $this->mainUser->refresh();
        $this->assertEquals('new@mail.pt', $this->mainUser->email);
        $this->assertEquals('letters and spaces', $this->mainUser->name);
        $this->assertEquals($password, $this->mainUser->password);

        $files = collect(Storage::disk('public')->allFiles($this->photoPath));
        $this->assertCount(1, $files);
        $this->assertEquals(basename($files[0]), $this->mainUser->profile_photo);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_succeeds_with_jpg_profile_photo()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();
        $password = $this->mainUser->password;
        Storage::fake('public');
        $file = UploadedFile::fake()->image('selfie.jpg');

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces',
                'profile_photo' => $file
            ])
            ->assertSessionHasNoErrors(['name', 'email', 'profile_photo']);

        $this->mainUser->refresh();
        $this->assertEquals('new@mail.pt', $this->mainUser->email);
        $this->assertEquals('letters and spaces', $this->mainUser->name);
        $this->assertEquals($password, $this->mainUser->password);

        $files = collect(Storage::disk('public')->allFiles($this->photoPath));
        $this->assertCount(1, $files);
        $this->assertEquals(basename($files[0]), $this->mainUser->profile_photo);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_succeeds_with_same_photo_on_different_users()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();
        $password = $this->mainUser->password;
        Storage::fake('public');
        $file = UploadedFile::fake()->image('selfie.png');
        $file->store('profiles', 'public');

        factory(User::class)->create([
            'email' => 'user1@mail.pt',
            'name' => 'letters and spaces',
            'password' => bcrypt('123'),
            'profile_photo' => $file->hashName()
        ]);

        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces',
                'profile_photo' => $file
            ])
            ->assertSessionHasNoErrors(['name', 'email', 'profile_photo']);


        $this->mainUser->refresh();
        $this->assertEquals('new@mail.pt', $this->mainUser->email);
        $this->assertEquals('letters and spaces', $this->mainUser->name);
        $this->assertEquals($password, $this->mainUser->password);

        $users = User::all();
        $files = collect(Storage::disk('public')->allFiles($this->photoPath));
        $namesFromProfile = $users->map->profile_photo->unique()->sort();
        $namesFromDisk = $files->map(function ($name) {
            return basename($name);
        })->sort();
        $this->assertEquals($namesFromDisk->values()->all(), $namesFromProfile->values()->all());
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_succeeds_keep_photo_if_no_photo_is_provided()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();
        $password = $this->mainUser->password;
        Storage::fake('public');
        $file = UploadedFile::fake()->image('selfie.png');
        $file->store('profiles', 'public');
        $name = $file->hashName();
        $this->mainUser->profile_photo = $name;
        $this->mainUser->save();


        $this->actingAs($this->mainUser)
            ->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces',
            ])
            ->assertSessionHasNoErrors(['name', 'email']);


        $this->mainUser->refresh();
        $this->assertEquals('new@mail.pt', $this->mainUser->email);
        $this->assertEquals('letters and spaces', $this->mainUser->name);
        $this->assertEquals($password, $this->mainUser->password);
        $this->assertEquals($name, $this->mainUser->profile_photo, 'Photo was reset');

        $files = collect(Storage::disk('public')->allFiles($this->photoPath));
        $this->assertCount(1, $files);
        $this->assertEquals(basename($files[0]), $this->mainUser->profile_photo);
    }

    // @codingStandardsIgnoreStart
    /** @test */
    public function a_profile_update_fails_for_guests()
    {
        // @codingStandardsIgnoreEnd
        // Arrange, Act, Assert
        $this->seedMainUser();

        $this->put('/me/profile', [
                'email' => 'new@mail.pt',
                'name' => 'letters and spaces',
            ])
            ->assertRedirect('/login');
    }
}
