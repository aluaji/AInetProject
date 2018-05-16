<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBaseProjectTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('admin')->default(false);
            $table->boolean('blocked')->default(false);
            $table->string('phone')->nullable();
            $table->string('profile_photo')->nullable();
            $table->json('profile_settings')->nullable();
        });

        Schema::create('associate_members', function (Blueprint $table) {
            $table->integer('main_user_id')->unsigned();
            $table->foreign('main_user_id')->references('id')->on('users');
            $table->integer('associated_user_id')->unsigned();
            $table->foreign('associated_user_id')->references('id')->on('users');
            $table->primary(['main_user_id', 'associated_user_id']);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create('movement_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('type', ['expense', 'revenue']);
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id')->unsigned();
            $table->foreign('owner_id')->references('id')->on('users');
            $table->integer('account_type_id')->unsigned();
            $table->foreign('account_type_id')->references('id')->on('account_types');
            $table->date('date');
            $table->string('code');
            $table->string('description')->nullable();
            $table->decimal('start_balance', 11, 2)->default(0);
            $table->decimal('current_balance', 11, 2)->default(0);
            // Non-normalized field to simplify the detection of the last
            // movement date
            // Usage is optional
            $table->date('last_movement_date')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->nullable();
            $table->unique(['owner_id', 'code']);         // account code is unique for each user
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['pdf', 'png', 'jpeg']);
            $table->string('original_name');
            $table->string('description')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('movements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->integer('movement_category_id')->unsigned();
            $table->foreign('movement_category_id')->references('id')->on('movement_categories');
            $table->date('date');
            $table->decimal('value', 11, 2);
            $table->decimal('start_balance', 11, 2);
            $table->decimal('end_balance', 11, 2);
            $table->string('description')->nullable();

            // Non-normalized field to handle movement type
            // They could be obtained from movement_categories, but the
            // presence of this data on the movement simplifies and
            // improves performance of balance calculation
            // Usage is optional
            $table->enum('type', ['expense', 'revenue'])->nullable();

            // The document associated with the movement
            // Null if movement has no document
            $table->integer('document_id')->nullable()->unsigned();
            $table->foreign('document_id')->references('id')->on('documents');

            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movements');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('movement_categories');
        Schema::dropIfExists('account_types');
        Schema::dropIfExists('associate_members');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('admin');
            $table->dropColumn('blocked');
            $table->dropColumn('phone');
            $table->dropColumn('profile_photo');
            $table->dropColumn('profile_settings');
        });
    }
}
