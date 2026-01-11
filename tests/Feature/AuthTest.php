<?php

use App\Models\User;

use function Pest\Laravel\postJson;

beforeEach(function (): void {
    $this->password = 'password123';
});

it('can register a new user', function (): void {
    $response = postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => $this->password,
        'password_confirmation' => $this->password,
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});

it('requires all fields for registration', function (): void {
    $response = postJson('/api/register', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('requires password confirmation for registration', function (): void {
    $response = postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => $this->password,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

it('prevents duplicate email registration', function (): void {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => $this->password,
        'password_confirmation' => $this->password,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('can login with valid credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt($this->password),
    ]);

    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => $this->password,
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
        ]);
});

it('cannot login with invalid credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt($this->password),
    ]);

    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('requires email and password for login', function (): void {
    $response = postJson('/api/login', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

it('can logout authenticated user', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('auth-token')->plainTextToken;

    $response = postJson('/api/logout', [], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertSuccessful()
        ->assertJson(['message' => 'Logged out successfully']);

    expect($user->tokens)->toBeEmpty();
});

it('can get authenticated user', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('auth-token')->plainTextToken;

    $response = $this->getJson('/api/user', [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
        ])
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);
});

it('requires authentication to access protected routes', function (): void {
    $response = postJson('/api/logout');

    $response->assertUnauthorized();

    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
});
