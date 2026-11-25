<?php
// Fitur: Login, registrasi dan akses pengguna
// connect with ../../config/auth.php, ../../database/factories/UserFactory.php, ../../database/migrations/0001_01_01_000000_create_users_table.php
// connect with ../../database/migrations/2026_11_13_000001_create_personal_access_tokens_table.php, ../../database/seeders/DatabaseSeeder.php, ../../folder-generated/app/Http/Controllers/UserController.php
// connect with ../../tests/Feature/AgentIdeKernelTest.php, ../../tests/Feature/AuthTest.php, ../../tests/Feature/ChatAiTest.php
// connect with ../../tests/Feature/ChatStorageTest.php, ../../tests/Feature/HarnessTest.php, ../../tests/Feature/IdePromptTest.php
// connect with ../../tests/Feature/LiveHarnessTest.php, ../Http/Controllers/AuthController.php, ../Services/AgentIde/FallbackAgentResponder.php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
