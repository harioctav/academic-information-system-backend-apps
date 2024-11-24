<?php

namespace Tests\Feature\Controllers\Api\Locations;

use App\Enums\UserRole;
use App\Models\Province;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProvinceControllerTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    // Create user with admin role
    $user = User::factory()->create();
    $adminRole = Role::create([
      'name' => UserRole::SuperAdmin->value,
      'guard_name' => 'api'
    ]);
    $user->assignRole($adminRole);

    Passport::actingAs($user);
  }

  #[Test]
  public function canShowProvinceList()
  {
    // Arrange
    Province::factory()->count(5)->create();

    // Act
    $response = $this->getJson('/api/locations/provinces');

    // Assert
    $response->assertStatus(200)
      ->assertJsonStructure([
        'data' => [
          '*' => [
            'id',
            'uuid',
            'name',
            'code',
            'created_at',
            'updated_at'
          ]
        ],
        'links',
        'meta'
      ]);
  }

  #[Test]
  public function canMakeNewProvince()
  {
    // Arrange
    $provinceData = [
      'name' => 'Provinsi Test',
      'code' => '99'
    ];

    // Act
    $response = $this->postJson('/api/locations/provinces', $provinceData);

    // Assert
    $response->assertStatus(200)
      ->assertJson([
        'message' => 'Successfully created Province Data'
      ])
      ->assertJsonStructure([
        'code',
        'message',
        'data' => [
          'id',
          'uuid',
          'name',
          'code',
          'created_at',
          'updated_at'
        ]
      ]);

    $this->assertDatabaseHas('provinces', [
      'name' => 'Provinsi Test',
      'code' => '99'
    ]);
  }
}
