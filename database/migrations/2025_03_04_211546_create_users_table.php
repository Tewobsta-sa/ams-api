<?php

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
        // Ensure roles table exists before users
        if (!Schema::hasTable('roles')) {
            throw new Exception("The 'roles' table must be created before 'users'. Run the 'roles' migration first.");
        }

        // Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique(); // Ensure unique usernames
            $table->string('password');
            $table->timestamp('password_changed_at')->nullable();
            $table->string('security_question'); // Fixed typo from 'sequrity_question'
            $table->string('answer');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->rememberToken();
            $table->timestamps();
        });

        // Password Reset Tokens Table (Adjusted for No Emails)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Linked to users instead of email
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions Table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Ensure consistency
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
