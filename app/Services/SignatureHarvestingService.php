<?php

namespace App\Services;

use App\Models\Profesional;
use App\Models\Participante;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SignatureHarvestingService
{
    /**
     * Scans the system for existing professional records and signature metadata.
     * 
     * @return array Summary of the operation results
     */
    public function harvest()
    {
        $stats = [
            'new_professionals' => 0,
            'updated_digital' => 0,
            'processed' => 0
        ];

        try {
            // 1. Sync professionals from Participants (Asistencia Técnica / Implementación)
            // Note: Table 'participantes' uses 'dni' and 'apellidos'
            $participants = Participante::select('dni', 'nombres', 'apellidos', 'cargo', 'unidad_ejecutora')
                ->whereNotNull('dni')
                ->get();

            $cleaningMap = [
                // Double-UTF8 patterns
                "\xC3\x83\xC2\xB3" => "ó",
                "\xC3\x83\xC2\xBA" => "ú",
                "\xC3\x83\xC2\xAD" => "í",
                "\xC3\x83\xC2\xA9" => "é",
                "\xC3\x83\xC2\xA1" => "á",
                "\xC3\x83\xC2\xB1" => "ñ",
                "\xC3\x83\xC2\x91" => "Ñ",
                "\xC3\x83\xC2\x93" => "Ó",
                "\xC3\x82\xC2\xBA" => "º",
                "Ôö£┬«" => "é",
                "Ôö£├┤" => "ó",
                "Ôö£┬½" => "í",
                "Ôö£┬í" => "á",
                "├║" => "ú",
                "├▒" => "ñ",
                // Legacy simplified patterns
                '?A?EZ' => 'ÑAÑEZ', 'MU?ANTE' => 'MUÑANTE', 'PE?A' => 'PEÑA', 'ORME?O' => 'ORMEÑO',
                'MU?OZ' => 'MUÑOZ', 'NU?EZ' => 'NUÑEZ', 'CONDE?A' => 'CONDEÑA', 'SALDA?A' => 'SALDAÑA',
                'A?ANCA' => 'AÑANCA', 'CASTA?EDA' => 'CASTAÑEDA', 'MUCHAYPI?A' => 'MUCHAYPIÑA',
                'QUICA?O' => 'QUICAÑO', '?AUPA' => 'ÑAUPA', '?AUPAS' => 'ÑAUPAS', 'ZU?IGA' => 'ZÚÑIGA',
                'SERME?O' => 'SERMEÑO', 'LI?AN' => 'LIÑAN', '?AHUIS' => 'ÑAHUIS', 'SANTIBA?EZ' => 'SANTIBAÑEZ',
                'AQUI?O' => 'AQUIÑO', 'CURI?AUPA' => 'CURIÑAUPA', 'GEJA?O' => 'GEJAÑO', 'A?AGUARI' => 'AÑAGUARI',
                'ACU?A' => 'ACUÑA', 'AVENDA?O' => 'AVENDAÑO', 'CAAMA?O' => 'CAAMAÑO', 'CAPU?AY' => 'CAPUÑAY',
                'CCO?AS' => 'CCOÑAS', 'CO?ES' => 'COÑES', 'CONTE?A' => 'CONTEÑA', 'DUE?AS' => 'DUEÑAS',
                'LUDE?A' => 'LUDEÑA', 'MAGUI?O' => 'MAGUIÑO', 'MU?OA' => 'MUÑOA', 'PI?EIRO' => 'PIÑEIRO',
                'QUICA?A' => 'QUICAÑA', 'SIGUE?AS' => 'SIGUEÑAS', 'SUA?A' => 'SUAÑA', 'VEGO?O' => 'VEGOÑO',
                'VEGO?A' => 'VEGOÑA', 'YNO?AN' => 'YNOÑAN', 'GARC?A' => 'GARCÍA', 'S?NCHEZ' => 'SÁNCHEZ',
                'G?MEZ' => 'GÓMEZ', 'CH?VEZ' => 'CHÁVEZ', 'B?RBARA' => 'BÁRBARA', 'RA?L' => 'RAÚL',
                'MAYT?' => 'MAYTÉ', 'L?ZARO' => 'LÁZARO', 'GUZM?N' => 'GUZMÁN', 'HERN?NDEZ' => 'HERNÁNDEZ',
                'PADR?N' => 'PADRÓN', 'LUC?A' => 'LUCÍA', 'MAR?A' => 'MARÍA'
            ];

            $cleanName = function($name) use ($cleaningMap) {
                if (empty($name)) return '';
                foreach ($cleaningMap as $search => $replace) {
                    $name = str_replace($search, $replace, $name);
                }
                return mb_strtoupper(trim($name));
            };

            foreach ($participants as $p) {
                $prof = Profesional::firstOrNew(['doc' => trim($p->dni)]);
                
                // Split apellidos if possible (assuming "Paterno Materno")
                $apellidosArray = explode(' ', trim($p->apellidos ?? ''));
                $paterno = $apellidosArray[0] ?? '';
                $materno = isset($apellidosArray[1]) ? implode(' ', array_slice($apellidosArray, 1)) : '';

                $prof->fill([
                    'tipo_doc' => 'DNI',
                    'nombres' => $cleanName($p->nombres),
                    'apellido_paterno' => $cleanName($paterno),
                    'apellido_materno' => $cleanName($materno),
                    'cargo' => $cleanName($p->cargo),
                ]);
                
                if ($prof->isDirty()) {
                    $prof->save();
                    $stats['new_professionals']++;
                }
                $stats['processed']++;
            }

            // 2. Sync from Users Table (Internal Staff)
            $users = DB::table('users')->get();
            foreach ($users as $user) {
                if (empty($user->username) || !is_numeric($user->username)) continue;

                $prof = Profesional::firstOrNew(['doc' => trim($user->username)]);
                $prof->fill([
                    'tipo_doc' => 'DNI',
                    'nombres' => $cleanName($user->name),
                    'apellido_paterno' => $cleanName($user->apellido_paterno ?? ''),
                    'apellido_materno' => $cleanName($user->apellido_materno ?? ''),
                    'cargo' => $cleanName($user->role ?? 'PERSONAL'),
                    'email' => strtolower(trim($user->email ?? '')),
                ]);

                if ($prof->isDirty()) {
                    $prof->save();
                    $stats['processed']++;
                }
            }

            // 3. Sync Implementors from Actas (Strings)
            $implementorsAsistencia = DB::table('actas')->whereNotNull('implementador')->distinct()->pluck('implementador');
            $implementorsMonitoreo = DB::table('mon_cabecera_monitoreo')->whereNotNull('implementador')->distinct()->pluck('implementador');
            
            $allImplementors = $implementorsAsistencia->merge($implementorsMonitoreo)->unique();

            foreach ($allImplementors as $fullName) {
                // Try to match name with existing professional to get DNI, or create a placeholder if we find a DNI elsewhere
                // Since 'implementador' field usually doesn't have a DNI, we only update existing ones to ensure clean names
                $profesionalesExistentes = Profesional::all();
                foreach($profesionalesExistentes as $prof) {
                    $nomCompleto = "{$prof->apellido_paterno} {$prof->apellido_materno} {$prof->nombres}";
                    $nomCompletoInverso = "{$prof->nombres} {$prof->apellido_paterno} {$prof->apellido_materno}";
                    
                    $cleanTarget = $cleanName($fullName);
                    
                    if ($cleanTarget === $cleanName($nomCompleto) || $cleanTarget === $cleanName($nomCompletoInverso)) {
                        // Match found! Ensure the existing record is clean
                        $prof->save(); // The mutators in Professional model already handle normalization
                    }
                }
            }

            // 4. Identify Digital Signers from com_dni table
            $professionals = Profesional::all();
            foreach ($professionals as $prof) {
                // Look for Digital Signer flag
                $hasDigitalRecord = DB::table('com_dni')
                    ->where('profesional_id', $prof->id) 
                    ->where('firma_sihce', 'SI')
                    ->exists();
                
                if ($hasDigitalRecord && $prof->tipo_firma !== 'DIGITAL') {
                    $prof->update(['tipo_firma' => 'DIGITAL']);
                    $stats['updated_digital']++;
                }
            }

            return [
                'success' => true,
                'message' => "Sincronización completada.",
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            Log::error("Error in SignatureHarvestingService: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error durante la sincronización: " . $e->getMessage(),
                'stats' => $stats
            ];
        }
    }
}
