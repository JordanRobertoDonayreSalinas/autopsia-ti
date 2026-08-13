<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RenipressService
{
    protected $baseUrl = 'http://renipress.susalud.gob.pe:8080/wb-renipress';
    protected $app20Url = 'http://app20.susalud.gob.pe:8080/registro-renipress-webapp';
    protected $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    /**
     * Consulta el portal de RENIPRESS para obtener datos de servicios vía JSON API interno.
     */
    public function getDatosEstablecimiento($codigo)
    {
        $idipress = str_pad($codigo, 8, '0', STR_PAD_LEFT);
        
        try {
            Log::info("Iniciando sincronización RENIPRESS Robusta (JSON) - ID: {$idipress}");

            $jar = new \GuzzleHttp\Cookie\CookieJar();
            $client = new \GuzzleHttp\Client([
                'cookies' => $jar,
                'headers' => [
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Referer' => "{$this->app20Url}/ipress.htm?action=consultaPorCodInico&idipress={$idipress}&est=1",
                ],
                'timeout' => 25,
                'verify' => false 
            ]);

            // Paso 1: Establecer sesión (Handshake)
            $handshakeUrl = "{$this->app20Url}/ipress.htm?action=consultaPorCodInico&idipress={$idipress}&est=1";
            $client->get($handshakeUrl);

            // Paso 2: Petición POST al endpoint de carga de datos (JSON)
            // Este endpoint devuelve TODA la información de la IPRESS en una sola estructura
            $apiUrl = "{$this->app20Url}/ipress.htm?action=cargarIpress&idipress={$idipress}";
            
            $response = $client->request('POST', $apiUrl, [
                'form_params' => [
                    'idipress' => $idipress
                ]
            ]);

            $json = (string) $response->getBody();
            $result = json_decode($json, true);

            if (isset($result['mensaje']) && $result['mensaje'] === 'ok' && isset($result['datos'])) {
                $raw = $result['datos'];
                
                $data = [
                    'upss' => $this->mapJsonData($raw['p_crCURSOR_UPSS'] ?? []),
                    'servicios' => $this->mapJsonData($raw['p_crCURSOR_UPS'] ?? []),
                    'especialidades' => $this->mapJsonData($raw['p_crCURSOR_ESPECIALIDES'] ?? []),
                    'cartera' => $this->mapJsonData($raw['P_CURSORCARTERA'] ?? [])
                ];

                Log::info("Sincronización JSON exitosa para {$codigo}");
                return $data;
            }

            Log::warning("El API interno de RENIPRESS no devolvió datos válidos para {$codigo}. Mensaje: " . ($result['mensaje'] ?? 'Sin respuesta'));

        } catch (\Exception $e) {
            Log::error("Fallo sincronización JSON RENIPRESS para {$codigo}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Consulta RENIPRESS y devuelve una estructura JSON limpia optimizada para LLM / Agentes.
     */
    public function getDatosLimpios($codigo)
    {
        $idipress = str_pad($codigo, 8, '0', STR_PAD_LEFT);
        
        try {
            Log::info("Consultando datos limpios RENIPRESS para Agente - ID: {$idipress}");

            $jar = new \GuzzleHttp\Cookie\CookieJar();
            $client = new \GuzzleHttp\Client([
                'cookies' => $jar,
                'headers' => [
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Referer' => "{$this->app20Url}/ipress.htm?action=consultaPorCodInico&idipress={$idipress}&est=1",
                ],
                'timeout' => 25,
                'verify' => false 
            ]);

            // Paso 1: Handshake
            $handshakeUrl = "{$this->app20Url}/ipress.htm?action=consultaPorCodInico&idipress={$idipress}&est=1";
            $client->get($handshakeUrl);

            // Paso 2: POST a cargarIpress
            $apiUrl = "{$this->app20Url}/ipress.htm?action=cargarIpress&idipress={$idipress}";
            $response = $client->request('POST', $apiUrl, [
                'form_params' => [
                    'idipress' => $idipress
                ]
            ]);

            $json = (string) $response->getBody();
            $result = json_decode($json, true);

            if (isset($result['mensaje']) && $result['mensaje'] === 'ok' && isset($result['datos']['p_crCURSOR_DATOS'][0])) {
                $datos = $result['datos']['p_crCURSOR_DATOS'][0];

                // Extraer datos del médico si vienen concatenados (ej. "NOMBRES N,NDNIN,N40176162")
                $medicoNombre = trim($datos['MEDICO_DATOS'] ?? '');
                $medicoTipoDoc = '';
                $medicoNumDoc = '';
                if (preg_match('/^(.*?)(?:\s+N,N?([A-Z]+)N,N?(\d+))?$/i', $medicoNombre, $matches)) {
                    if (count($matches) > 1) $medicoNombre = trim($matches[1]);
                    if (count($matches) > 2) $medicoTipoDoc = trim($matches[2]);
                    if (count($matches) > 3) $medicoNumDoc = trim($matches[3]);
                }

                // Extraer departamento, provincia y distrito de la dirección
                $direccion = trim($datos['ESTABLECIMIENTO_DIRECCION'] ?? '');
                $distrito = '';
                $provincia = '';
                $departamento = '';
                if (preg_match('/DISTRITO\s+(.*?)\s+PROVINCIA\s+(.*?)\s+DEPARTAMENTO\s+(.*)$/i', $direccion, $ubigeoMatches)) {
                    $distrito = trim($ubigeoMatches[1]);
                    $provincia = trim($ubigeoMatches[2]);
                    $departamento = trim($ubigeoMatches[3]);
                }

                return [
                    'nombre'                      => trim($datos['ESTABLECIMIENTO_NOMBRE'] ?? ''),
                    'codigo_ipress'              => trim($datos['CO_UNICOIPRESS'] ?? ''),
                    'institucion'                 => trim($datos['ESTABLECIMIENTO_INSTITUCION'] ?? ''),
                    'direccion'                   => $direccion,
                    'departamento'                => $departamento,
                    'provincia'                   => $provincia,
                    'distrito'                    => $distrito,
                    'centro_poblado'              => trim($datos['CENTRO_POBLADO'] ?? ''),
                    'telefono'                    => trim($datos['ESTABLECIMIENTO_TELEFONO'] ?? ''),
                    'longitud'                    => trim($datos['ESTABLECIMIENTO_LONGITUD'] ?? ''),
                    'latitud'                     => trim($datos['ESTABLECIMIENTO_LATITUD'] ?? ''),
                    'altitud'                     => trim($datos['ESTABLECIMIENTO_ALTITUD'] ?? ''),
                    'correo'                      => trim($datos['ESTABLECIMIENTO_CORREO'] ?? ''),
                    'fecha_creacion_resolucion'   => trim($datos['ESTABLECIMIENTO_INICIO'] ?? ''),
                    'fecha_registro'              => trim($datos['ESTABLECIMIENTO_REGISTRO'] ?? ''),
                    'numero_resolucion_creacion'  => trim($datos['ADICIONAL_CATEGORIA_NUMERO'] ?? ''),
                    'horario_atencion'            => trim($datos['ADICIONAL_HORATENCION'] ?? ''),
                    'categoria'                   => trim($datos['ADICIONAL_CATEGORIA'] ?? ''),
                    'numero_ambientes'            => trim($datos['ADICIONAL_NUM_AMBESTAB'] ?? ''),
                    'numero_camas'                => trim($datos['CAMAS'] ?? ''),
                    'director_medico'             => [
                        'nombres'            => $medicoNombre,
                        'tipo_documento'     => $medicoTipoDoc ?: 'DNI',
                        'numero_documento'   => $medicoNumDoc,
                        'colegio_profesional'=> trim($datos['MEDICO_COLEGIO'] ?? ''),
                        'colegiatura'        => trim($datos['MEDICO_NUM_COLEGIATURA'] ?? ''),
                        'rne'                => trim($datos['MEDICO_RNE'] ?? '')
                    ],
                    'minsa'                       => [
                        'red'      => trim($datos['MINSA_RED'] ?? ''),
                        'microred' => trim($datos['MINSA_MICRORED'] ?? ''),
                        'clas'     => trim($datos['MINSA_CLAS'] ?? ''),
                        'odsis'    => trim($datos['MINSA_ODSIS'] ?? '')
                    ],
                    'estado'                      => trim($datos['SITUACION_ESTADO'] ?? ''),
                    'condicion'                   => trim($datos['SITUACION_CONDICION'] ?? ''),
                    'upss'                        => $this->mapJsonData($result['datos']['p_crCURSOR_UPSS'] ?? []),
                    'ups'                         => $this->mapJsonData($result['datos']['p_crCURSOR_UPS'] ?? [])
                ];
            }

            Log::warning("No se encontraron datos principales en RENIPRESS para ID {$idipress}");
        } catch (\Exception $e) {
            Log::error("Error en getDatosLimpios para {$codigo}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Mapea los cursores JSON de SUSALUD al formato estándar del sistema.
     */
    protected function mapJsonData($cursor)
    {
        if (!is_array($cursor)) return [];

        $results = [];
        foreach ($cursor as $item) {
            // SUSALUD usa nombres de columnas en mayúsculas
            $codigo = $item['CODIGO'] ?? $item['CO_UPS'] ?? $item['CO_ESPECIALIDAD'] ?? $item['CU_CARTERA'] ?? '';
            $nombre = $item['NOMBRE'] ?? $item['DE_UPS'] ?? $item['DE_ESPECIALIDAD'] ?? $item['DE_CAR_SER'] ?? '';
            $estado = $item['ESTADO'] ?? $item['ES_UPS'] ?? $item['ES_ESTADO'] ?? '';

            if ($codigo && $nombre) {
                $results[] = [
                    'codigo' => trim(strip_tags($codigo)),
                    'nombre' => trim(strip_tags($nombre)),
                    'estado' => trim(strip_tags($estado))
                ];
            }
        }
        return $results;
    }
}
