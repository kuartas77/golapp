<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;
use App\Support\SchoolModuleAccess;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ViewerRoleTest extends TestCase
{
    public function test_school_admin_can_create_a_viewer_with_enabled_modules(): void
    {
        $viewerRole = Role::query()->where('name', User::VIEWER)->firstOrFail();

        $response = $this->actingAs($this->user)->postJson('/api/v2/admin/users', [
            'name' => 'Visualizador Uno',
            'email' => 'viewer.one@gmail.com',
            'rol_id' => $viewerRole->id,
            'viewer_modules' => ['school.module.players', 'school.module.reports'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.roles.0', User::VIEWER)
            ->assertJsonPath('data.viewer_modules', ['school.module.players', 'school.module.reports']);

        $viewer = User::query()->where('email', 'viewer.one@gmail.com')->firstOrFail();
        $this->assertTrue($viewer->hasDirectPermission(SchoolModuleAccess::permissionName('school.module.players')));
        $this->assertFalse($viewer->hasDirectPermission(SchoolModuleAccess::permissionName('school.module.payments')));
    }

    public function test_viewer_requires_at_least_one_enabled_module(): void
    {
        $viewerRole = Role::query()->where('name', User::VIEWER)->firstOrFail();
        $school = School::query()->findOrFail($this->school['id']);
        $permissions = $school->getResolvedSchoolPermissions();
        $permissions['school.module.inventory'] = false;
        $school->forceFill(['school_permissions' => $permissions])->save();

        $this->actingAs($this->user)->postJson('/api/v2/admin/users', [
            'name' => 'Visualizador Invalido',
            'email' => 'viewer.invalid@gmail.com',
            'rol_id' => $viewerRole->id,
            'viewer_modules' => ['school.module.inventory'],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('viewer_modules');
    }

    public function test_viewer_access_is_the_intersection_of_role_assignment_and_school_module(): void
    {
        $viewer = $this->viewer(['school.module.players']);

        $this->actingAs($viewer)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/datatables/players_enabled')
            ->assertOk();

        $this->actingAs($viewer)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/datatables/inventory_products')
            ->assertForbidden();

        $school = School::query()->findOrFail($this->school['id']);
        $permissions = $school->getResolvedSchoolPermissions();
        $permissions['school.module.players'] = false;
        $school->forceFill(['school_permissions' => $permissions])->save();
        School::forgetCachedSchool($school->id);

        $this->actingAs($viewer)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/datatables/players_enabled')
            ->assertForbidden();
    }

    public function test_viewer_can_read_inscription_limits_without_loading_billing_catalogs(): void
    {
        $viewer = $this->viewer(['school.module.inscriptions']);

        $this->actingAs($viewer)
            ->getJson('/api/v2/inscriptions/limit-summary?year=2026')
            ->assertOk()
            ->assertJsonStructure(['year', 'current', 'limit', 'remaining', 'is_full']);

        $this->actingAs($viewer)
            ->getJson('/api/v2/admin/invoice-items-custom')
            ->assertForbidden();
    }

    public function test_viewer_can_load_the_competitions_datatable(): void
    {
        $viewer = $this->viewer(['school.module.matches']);

        $this->actingAs($viewer)
            ->get('/control-competencias')
            ->assertOk();

        $this->actingAs($viewer)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/api/v2/datatables/matches')
            ->assertOk()
            ->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
    }

    public function test_viewer_cannot_call_mutation_routes_directly(): void
    {
        $viewer = $this->viewer(['school.module.inventory', 'school.module.payments']);

        $this->actingAs($viewer)
            ->postJson('/api/v2/inventory/products', [
                'name' => 'Producto no autorizado',
            ])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->postJson('/api/v2/payments/bulk-update', [])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->postJson('/player-evaluations', [])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->postJson('/notifications', [])
            ->assertForbidden();

        $this->assertDatabaseMissing('inventory_products', ['name' => 'Producto no autorizado']);
    }

    public function test_viewer_cannot_read_mutation_form_catalogs(): void
    {
        $viewer = $this->viewer(['school.module.training_groups', 'school.module.competition_groups']);

        $this->actingAs($viewer)
            ->getJson('/api/v2/settings/groups')
            ->assertForbidden();
    }

    public function test_financial_reports_require_reports_and_payments_assignments(): void
    {
        $viewer = $this->viewer(['school.module.reports']);

        $this->actingAs($viewer)
            ->getJson('/api/v2/reports/payments')
            ->assertForbidden();

        $viewer->givePermissionTo(SchoolModuleAccess::permissionName('school.module.payments'));

        $this->actingAs($viewer->fresh())
            ->getJson('/api/v2/reports/payments')
            ->assertOk();
    }

    public function test_module_options_are_catalog_driven_and_exclude_features(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v2/admin/users/module-options')
            ->assertOk();

        $keys = collect($response->json('data'))->pluck('key');

        $this->assertEqualsCanonicalizing(SchoolModuleAccess::keys(), $keys->all());
        $this->assertFalse($keys->contains('school.feature.system_notify'));
        $response->assertJsonStructure(['data' => [['key', 'label', 'group', 'school_enabled']]]);
    }

    public function test_an_existing_assignment_is_retained_while_its_school_module_is_disabled(): void
    {
        $viewer = $this->viewer(['school.module.players']);
        $viewerRole = Role::query()->where('name', User::VIEWER)->firstOrFail();
        $school = School::query()->findOrFail($this->school['id']);
        $permissions = $school->getResolvedSchoolPermissions();
        $permissions['school.module.players'] = false;
        $school->forceFill(['school_permissions' => $permissions])->save();
        School::forgetCachedSchool($school->id);

        $this->actingAs($this->user)->putJson("/api/v2/admin/users/{$viewer->id}", [
            'name' => $viewer->name,
            'email' => $viewer->email,
            'rol_id' => $viewerRole->id,
            'viewer_modules' => ['school.module.players'],
        ])->assertOk()
            ->assertJsonPath('data.viewer_modules', ['school.module.players']);

        $this->assertTrue($viewer->fresh()->hasDirectPermission(
            SchoolModuleAccess::permissionName('school.module.players')
        ));
    }

    public function test_changing_away_from_viewer_removes_direct_module_permissions(): void
    {
        $viewer = $this->viewer(['school.module.players', 'school.module.reports']);
        $instructorRole = Role::query()->where('name', 'instructor')->firstOrFail();

        $this->actingAs($this->user)->putJson("/api/v2/admin/users/{$viewer->id}", [
            'name' => $viewer->name,
            'email' => $viewer->email,
            'rol_id' => $instructorRole->id,
        ])->assertOk()
            ->assertJsonPath('data.roles.0', 'instructor')
            ->assertJsonPath('data.viewer_modules', []);

        $this->assertFalse($viewer->fresh()->permissions()
            ->whereIn('name', SchoolModuleAccess::permissionNames())
            ->exists());
    }

    public function test_viewer_can_update_only_its_own_profile(): void
    {
        $viewer = $this->viewer(['school.module.players']);

        $this->actingAs($viewer)->putJson('/api/v2/profile', [
            'phone' => '3001234567',
        ])->assertOk()
            ->assertJsonPath('data.profile.phone', '3001234567');

        $this->actingAs($viewer)->putJson("/api/v2/admin/users/{$this->user->id}", [
            'name' => 'No autorizado',
            'email' => $this->user->email,
            'rol_id' => Role::query()->where('name', 'school')->value('id'),
        ])->assertForbidden();
    }

    public function test_viewer_cannot_open_mutation_only_spa_paths_directly(): void
    {
        $viewer = $this->viewer([
            'school.module.evaluations',
            'school.module.attendances',
            'school.module.payments',
            'school.module.matches',
            'school.module.training_groups',
            'school.module.competition_groups',
            'school.module.billing',
        ]);

        foreach ([
            '/evaluaciones-deportistas/create',
            '/evaluaciones-deportistas/1/edit',
            '/asistencias/qr',
            '/mensualidades/notificaciones-deuda',
            '/control-competencias/nuevo/1',
            '/control-competencias/1',
            '/configuracion/g-entrenamiento/admin',
            '/configuracion/g-competencia/admin',
            '/facturas/crear/1',
        ] as $path) {
            $this->actingAs($viewer)->get($path)->assertForbidden();
        }
    }

    public function test_viewer_role_and_permissions_migration_is_idempotent(): void
    {
        $migration = require database_path('migrations/2026_09_01_000001_add_viewer_role_and_module_permissions.php');

        $migration->up();
        $migration->up();

        $this->assertSame(1, Role::query()->where('name', User::VIEWER)->count());
        $this->assertSame(
            count(SchoolModuleAccess::keys()),
            Permission::query()->whereIn('name', SchoolModuleAccess::permissionNames())->count()
        );
    }

    private function viewer(array $modules): User
    {
        $viewer = $this->createUser([
            'school_id' => $this->school['id'],
            'email' => sprintf('viewer.%s@gmail.com', uniqid()),
        ], [User::VIEWER]);

        SchoolUser::query()->create([
            'school_id' => $this->school['id'],
            'user_id' => $viewer->id,
        ]);

        $viewer->givePermissionTo(array_map(SchoolModuleAccess::permissionName(...), $modules));

        return $viewer;
    }
}
