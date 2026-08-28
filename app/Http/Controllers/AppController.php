<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeSpaPath($request);

        return view('theme');
    }

    private function authorizeSpaPath(Request $request): void
    {
        $user = $request->user();

        if (! $user) {
            return;
        }

        $path = trim($request->path(), '/');

        foreach ($this->spaAccessRules() as $prefix => $rule) {
            if ($path !== $prefix && ! str_starts_with($path, $prefix.'/')) {
                continue;
            }

            $roles = $rule['roles'] ?? [];
            if ($roles !== [] && ! $user->hasAnyRole($roles)) {
                abort(403);
            }

            foreach ($rule['permissions'] ?? [] as $permission) {
                if (! $this->schoolPermissionEnabled($permission, $request)) {
                    abort(403);
                }
            }

            if (
                ($rule['requires_electronic_invoicing'] ?? false)
                && ! $user->hasRole('super-admin')
                && ! $this->electronicInvoicingEnabled($request)
            ) {
                abort(403);
            }

            return;
        }
    }

    private function schoolPermissionEnabled(string $permission, Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        $school = getSchool($user);
        $freshSchool = School::query()->find($school->id);

        return (bool) $freshSchool?->hasSchoolPermission($permission);
    }

    private function electronicInvoicingEnabled(Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        $school = getSchool($user);

        return (bool) School::query()->find($school->id)?->electronic_invoicing_enabled;
    }

    private function spaAccessRules(): array
    {
        return [
            'players' => ['roles' => ['super-admin', 'school', 'assistant'], 'permissions' => ['school.module.players']],
            'deportistas' => ['roles' => ['super-admin', 'school', 'assistant'], 'permissions' => ['school.module.players']],
            'inscriptions' => ['roles' => ['super-admin', 'school', 'assistant'], 'permissions' => ['school.module.inscriptions']],
            'inscripciones' => ['roles' => ['super-admin', 'school', 'assistant'], 'permissions' => ['school.module.inscriptions']],
            'assists' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.attendances']],
            'asistencias' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.attendances']],
            'training-sessions' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.training_sessions']],
            'sesiones-entrenamiento' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.training_sessions']],
            'planificacion-sesiones' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.session_planning']],
            'metodologia' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.methodology']],
            'planificacion-documental' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.document_planning']],
            'payments' => ['roles' => ['super-admin', 'school', 'assistant'], 'permissions' => ['school.module.payments']],
            'mensualidades/notificaciones-deuda' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.payments']],
            'mensualidades' => ['roles' => ['super-admin', 'school', 'assistant'], 'permissions' => ['school.module.payments']],
            'matches' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.matches']],
            'control-competencias' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.matches']],
            'player-stats' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.matches']],
            'top-players' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.matches']],
            'player' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.matches']],
            'competition-stats' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.matches']],
            'evaluaciones-deportistas' => ['roles' => ['super-admin', 'school', 'instructor'], 'permissions' => ['school.module.evaluations']],
            'configuracion/escuela' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.school_profile']],
            'configuracion/contratos' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.contracts']],
            'configuracion/numeracion-facturacion' => [
                'roles' => ['super-admin', 'school'],
                'permissions' => ['school.module.billing'],
                'requires_electronic_invoicing' => true,
            ],
            'administracion/numeracion-facturacion' => [
                'roles' => ['super-admin', 'school'],
                'permissions' => ['school.module.billing'],
                'requires_electronic_invoicing' => true,
            ],
            'configuracion/documentos-club' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.club_documents']],
            'configuracion/usuarios' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.user_management']],
            'configuracion/g-entrenamiento' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.training_groups']],
            'configuracion/horarios' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.training_groups']],
            'admin/schedules' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.training_groups']],
            'configuracion/g-competencia' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.competition_groups']],
            'configuracion/torneos' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.competition_groups']],
            'admin/tournaments' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.competition_groups']],
            'configuracion/plantillas-evaluacion' => ['roles' => ['super-admin']],
            'configuracion/schools' => ['roles' => ['super-admin']],
            'configuracion/schools-info' => ['roles' => ['super-admin']],
            'administracion/plantillas-evaluacion' => ['roles' => ['super-admin']],
            'administracion/contratos' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.contracts']],
            'facturas/comprobantes-pago' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.billing', 'school.feature.system_notify']],
            'facturas/solicitudes-uniformes' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.billing', 'school.feature.system_notify']],
            'facturas/items' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.billing']],
            'facturas/cargos-personalizados' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.billing']],
            'facturas/crear' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.billing']],
            'facturas' => ['roles' => ['super-admin', 'school', 'assistant'], 'permissions' => ['school.module.billing']],
            'inventario' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.inventory']],
            'salidas' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.school_outings']],
            'saldos-a-favor' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.player_credits']],
            'notificaciones' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.feature.system_notify']],
            'informes/asistencias' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.reports']],
            'informes/actividad-instructores' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.reports']],
            'informes/mensualidades-asistencias' => ['roles' => ['super-admin', 'school'], 'permissions' => ['school.module.reports']],
            'informes' => ['roles' => ['super-admin', 'school', 'assistant'], 'permissions' => ['school.module.reports']],
        ];
    }
}
