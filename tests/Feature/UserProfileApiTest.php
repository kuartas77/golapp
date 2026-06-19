<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SchoolUser;
use App\Models\User;
use Tests\TestCase;

final class UserProfileApiTest extends TestCase
{
    public function test_authenticated_user_can_view_own_profile(): void
    {
        $this->user->profile()->update([
            'identification_document' => '100200300',
            'position' => 'ENTRENADOR(A)',
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/profile')
            ->assertOk()
            ->assertJsonPath('data.user.id', $this->user->id)
            ->assertJsonPath('data.profile.identification_document', '100200300')
            ->assertJsonPath('data.profile.position', 'ENTRENADOR(A)')
            ->assertJsonPath('data.can_update', true);
    }

    public function test_authenticated_user_profile_is_created_when_missing(): void
    {
        $this->user->profile()->delete();

        $this->actingAs($this->user)
            ->getJson('/api/v2/profile')
            ->assertOk()
            ->assertJsonPath('data.user.id', $this->user->id)
            ->assertJsonPath('data.can_update', true);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_authenticated_user_can_update_only_profile_fields(): void
    {
        $originalName = $this->user->name;
        $originalEmail = $this->user->email;

        $this->actingAs($this->user)
            ->putJson('/api/v2/profile', [
                'name' => 'No debe cambiar',
                'email' => 'nuevo@example.com',
                'identification_document' => '987654321',
                'date_birth' => '1990-05-10',
                'gender' => 'M',
                'address' => 'Calle 10 # 20-30',
                'phone' => '6011234567',
                'mobile' => '3001234567',
                'position' => 'ENTRENADOR(A)',
                'studies' => 'Licenciatura en educación física',
                'references' => 'Referencia profesional',
                'contacts' => 'Contacto de emergencia',
                'experience' => 'Experiencia deportiva',
                'aptitude' => 'Liderazgo y comunicación',
            ])
            ->assertOk()
            ->assertJsonPath('data.profile.identification_document', '987654321')
            ->assertJsonPath('data.profile.position', 'ENTRENADOR(A)');

        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->user->id,
            'identification_document' => '987654321',
            'position' => 'ENTRENADOR(A)',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => $originalName,
            'email' => $originalEmail,
        ]);
    }

    public function test_school_can_view_profile_for_user_in_same_school(): void
    {
        $target = $this->createSchoolUser();
        $target->profile()->update([
            'mobile' => '3112223344',
            'position' => 'COORDINADOR(A)',
        ]);

        $this->actingAs($this->user)
            ->getJson("/api/v2/admin/users/{$target->id}/profile")
            ->assertOk()
            ->assertJsonPath('data.user.id', $target->id)
            ->assertJsonPath('data.profile.mobile', '3112223344')
            ->assertJsonPath('data.can_update', false);
    }

    public function test_school_cannot_view_profile_for_user_in_another_school(): void
    {
        [, $otherUser] = $this->createSchoolAndUser(roles: [User::INSTRUCTOR]);

        $this->actingAs($this->user)
            ->getJson("/api/v2/admin/users/{$otherUser->id}/profile")
            ->assertForbidden();
    }

    public function test_instructor_cannot_use_admin_profile_endpoint(): void
    {
        [, $instructor] = $this->createSchoolAndUser(roles: [User::INSTRUCTOR]);

        $this->actingAs($instructor)
            ->getJson("/api/v2/admin/users/{$this->user->id}/profile")
            ->assertForbidden();
    }

    private function createSchoolUser(): User
    {
        $user = $this->createUser([
            'school_id' => $this->school['id'],
        ], [User::INSTRUCTOR]);

        SchoolUser::query()->create([
            'school_id' => $this->school['id'],
            'user_id' => $user->id,
        ]);

        return $user;
    }
}
