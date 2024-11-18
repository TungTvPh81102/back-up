<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('last_name',50);
            $table->string('first_name',50);
            $table->string('email',50)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('phone',13)->nullable()->unique();
            $table->enum('role', [User::ROLE_ADMIN, User::ROLE_MEMBER,  User::ROLE_STAFF])->default(User::ROLE_MEMBER);
            $table->rememberToken();
            $table->enum('status', [User::STATUS_ACTIVE, User::STATUS_INACTIVE, User::STATUS_BANNED])->default(User::STATUS_ACTIVE);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['last_name', 'first_name', 'email', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
