<?php

namespace Tests\Feature;

use App\Models\CabeceraMonitoreo;
use App\Models\Establecimiento;
use App\Models\EquipoComputo;
use App\Models\MonitoreoEquipo;
use App\Models\MonitoreoModulos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Caso de prueba mínimo de la Fase 6 del informe de revisión: una acta
 * capturada en campo (cabecera + equipo humano + un módulo + equipos de
 * cómputo) debe sincronizarse contra POST /api/v1/sync exactamente igual
 * que si se hubiera creado desde la web. Antes de este fix, el payload de
 * Flutter no traía la clave 'consultorios' y el acta quedaba casi vacía.
 */
class OfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    private function crearEstablecimiento(): Establecimiento
    {
        return Establecimiento::create([
            'codigo' => '00099999',
            'nombre' => 'IPRESS DE PRUEBA',
            'provincia' => 'LIMA',
            'distrito' => 'LIMA',
            'categoria' => 'I-1',
            'red' => 'RED TEST',
            'microred' => 'MICRORED TEST',
            'responsable' => 'RESPONSABLE TEST',
        ]);
    }

    public function test_sync_rechaza_peticiones_sin_token(): void
    {
        $response = $this->postJson('/api/v1/sync', ['actas' => []]);

        $response->assertStatus(401);
    }

    public function test_sync_crea_acta_completa_con_modulos_equipos_y_personal(): void
    {
        $user = User::factory()->create([
            'username' => '71883058',
            'status' => 'active',
            'role' => 'operador',
        ]);
        Sanctum::actingAs($user);

        $establecimiento = $this->crearEstablecimiento();

        $payload = [
            'actas' => [[
                'offline_id' => 'ACTA-TEST-1',
                'establecimiento_id' => $establecimiento->id,
                'fecha' => '2026-08-17',
                'responsable' => 'JORDAN ROBERTO DONAYRE SALINAS',
                'implementador' => 'JORDAN ROBERTO DONAYRE SALINAS',
                'tipo_origen' => 'ESTANDAR',
                'equipo_monitoreo' => [[
                    'tipo_doc' => 'DNI',
                    'doc' => '12345678',
                    'nombres' => 'JUAN',
                    'apellido_paterno' => 'PEREZ',
                    'cargo' => 'Jefe de IPRESS',
                    'institucion' => 'IPRESS DE PRUEBA',
                ]],
                'consultorios' => [[
                    'titulo_consultorio' => 'TRIAJE / ADMISION',
                    'contenido' => ['observaciones' => 'Todo en orden'],
                    'equipos' => [[
                        'descripcion' => 'PC Escritorio HP ProDesk',
                        'cantidad' => 1,
                        'estado' => 'OPERATIVO',
                        'propio' => 'ESTABLECIMIENTO',
                        'nro_serie' => 'SN-TEST-001',
                        'observacion' => 'Funciona correctamente',
                    ]],
                ]],
            ]],
        ];

        $response = $this->postJson('/api/v1/sync', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('sincronizados', 1)
            ->assertJsonPath('actas.0.offline_id', 'ACTA-TEST-1');

        $actaId = $response->json('actas.0.id');
        $this->assertNotNull($actaId, 'El servidor debe devolver el id real del acta creada');

        // La cabecera se creó con los datos reales del payload, no vacía.
        $this->assertDatabaseHas('mon_cabecera_monitoreo', [
            'id' => $actaId,
            'establecimiento_id' => $establecimiento->id,
            'responsable' => 'JORDAN ROBERTO DONAYRE SALINAS',
            'implementador' => 'JORDAN ROBERTO DONAYRE SALINAS',
            'tipo_origen' => 'ESTANDAR',
        ]);

        // El módulo evaluado (consultorio) sí se creó — antes NUNCA se creaba.
        $this->assertSame(1, MonitoreoModulos::where('cabecera_monitoreo_id', $actaId)->count());

        // El equipo de cómputo inventariado sí se creó — antes NUNCA se creaba.
        $this->assertDatabaseHas('mon_equipos_computo', [
            'cabecera_monitoreo_id' => $actaId,
            'descripcion' => 'PC ESCRITORIO HP PRODESK',
            'nro_serie' => 'SN-TEST-001',
        ]);

        // El personal del establecimiento presente en la visita sí se creó.
        $this->assertDatabaseHas('mon_equipo_monitoreo', [
            'cabecera_monitoreo_id' => $actaId,
            'doc' => '12345678',
        ]);
    }

    public function test_sync_reporta_error_de_un_acta_sin_tumbar_las_demas(): void
    {
        $user = User::factory()->create(['username' => '71883059', 'status' => 'active']);
        Sanctum::actingAs($user);

        $establecimiento = $this->crearEstablecimiento();

        $payload = [
            'actas' => [
                // Acta 1: inválida (sin establecimiento_id)
                ['offline_id' => 'ACTA-MALA', 'fecha' => '2026-08-17'],
                // Acta 2: válida
                [
                    'offline_id' => 'ACTA-BUENA',
                    'establecimiento_id' => $establecimiento->id,
                    'fecha' => '2026-08-17',
                    'responsable' => 'AUDITOR TEST',
                    'implementador' => 'AUDITOR TEST',
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/sync', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('sincronizados', 1)
            ->assertJsonPath('actas.0.offline_id', 'ACTA-BUENA');

        $this->assertCount(1, $response->json('errores'));
    }
}
