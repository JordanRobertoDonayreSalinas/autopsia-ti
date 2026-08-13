<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Establecimiento;

class ProgramacionSectoresSeeder extends Seeder
{
    /**
     * Datos del cronograma — PDF vs3
     * Fechas en formato DD/MM/YY (día/mes/año 20XX)
     */
    private function cronograma(): array
    {
        return [
            // SECTOR 1 — ICA
            ['nombre'=>'PUESTO SALUD HUAMANI',       'provincia'=>'ICA',     'sector'=>1,  'cuadril'=>'C1',  'c'=>'07/04/26','f'=>'28/04/26','d'=>19],
            ['nombre'=>'PAMPA DE VILLACURI',          'provincia'=>'ICA',     'sector'=>1,  'cuadril'=>'C2',  'c'=>'07/04/26','f'=>'08/05/26','d'=>28],
            ['nombre'=>'LOS MOLINOS',                 'provincia'=>'ICA',     'sector'=>1,  'cuadril'=>'C3',  'c'=>'07/04/26','f'=>'12/06/26','d'=>58],
            ['nombre'=>'PAMPA DE LA ISLA',            'provincia'=>'ICA',     'sector'=>1,  'cuadril'=>'C4',  'c'=>'07/04/26','f'=>'08/05/26','d'=>28],
            ['nombre'=>'EL CARMEN-OLIVO',             'provincia'=>'ICA',     'sector'=>1,  'cuadril'=>'C5',  'c'=>'07/04/26','f'=>'12/06/26','d'=>58],
            ['nombre'=>'SANTA BARBARA',               'provincia'=>'ICA',     'sector'=>1,  'cuadril'=>'C7',  'c'=>'07/04/26','f'=>'08/05/26','d'=>28],
            // SECTOR 2 — ICA
            ['nombre'=>'LA TINGUIÑA',                 'provincia'=>'ICA',     'sector'=>2,  'cuadril'=>'C6',  'c'=>'07/04/26','f'=>'12/06/26','d'=>58],
            ['nombre'=>'PARCONA',                     'provincia'=>'ICA',     'sector'=>2,  'cuadril'=>'C8',  'c'=>'07/04/26','f'=>'12/06/26','d'=>58],
            ['nombre'=>'ACOMAYO',                     'provincia'=>'ICA',     'sector'=>2,  'cuadril'=>'C9',  'c'=>'07/04/26','f'=>'12/06/26','d'=>58],
            ['nombre'=>'CSMC VITALIZA',               'provincia'=>'ICA',     'sector'=>2,  'cuadril'=>'C10', 'c'=>'07/04/26','f'=>'12/06/26','d'=>58],
            ['nombre'=>'PASAJE TINGUIÑA VALLE',       'provincia'=>'ICA',     'sector'=>2,  'cuadril'=>'C11', 'c'=>'07/04/26','f'=>'08/05/26','d'=>28],
            ['nombre'=>'YAURILLA',                    'provincia'=>'ICA',     'sector'=>2,  'cuadril'=>'C12', 'c'=>'07/04/26','f'=>'08/05/26','d'=>28],
            // SECTOR 3 — ICA
            ['nombre'=>'SAN MARTIN DE PORRAS',        'provincia'=>'ICA',     'sector'=>3,  'cuadril'=>'C13', 'c'=>'07/04/26','f'=>'08/05/26','d'=>28],
            ['nombre'=>'SEÑOR DE LUREN',              'provincia'=>'ICA',     'sector'=>3,  'cuadril'=>'C14', 'c'=>'07/04/26','f'=>'08/05/26','d'=>28],
            ['nombre'=>'FONAVI IV',                   'provincia'=>'ICA',     'sector'=>3,  'cuadril'=>'C15', 'c'=>'07/04/26','f'=>'12/06/26','d'=>58],
            ['nombre'=>'SAN JUAN BAUTISTA',           'provincia'=>'ICA',     'sector'=>3,  'cuadril'=>'C16', 'c'=>'07/04/26','f'=>'12/06/26','d'=>58],
            ['nombre'=>'SUBTANJALLA',                 'provincia'=>'ICA',     'sector'=>3,  'cuadril'=>'C17', 'c'=>'07/04/26','f'=>'12/06/26','d'=>58],
            ['nombre'=>'LA ANGOSTURA',                'provincia'=>'ICA',     'sector'=>3,  'cuadril'=>'C18', 'c'=>'07/04/26','f'=>'12/06/26','d'=>58],
            ['nombre'=>'SAN JOAQUIN',                 'provincia'=>'ICA',     'sector'=>3,  'cuadril'=>'C19', 'c'=>'07/04/26','f'=>'12/06/26','d'=>58],
            ['nombre'=>'CSMC CRISTO MORENO DE LUREN', 'provincia'=>'ICA',     'sector'=>3,  'cuadril'=>'C20', 'c'=>'07/04/26','f'=>'12/06/26','d'=>58],
            // SECTOR 4 — ICA
            ['nombre'=>'GUADALUPE',                   'provincia'=>'ICA',     'sector'=>4,  'cuadril'=>'C1',  'c'=>'29/04/26','f'=>'04/07/26','d'=>58],
            ['nombre'=>'CERRO PRIETO',                'provincia'=>'ICA',     'sector'=>4,  'cuadril'=>'C2',  'c'=>'09/05/26','f'=>'10/06/26','d'=>28],
            ['nombre'=>'COLLAZOS',                    'provincia'=>'ICA',     'sector'=>4,  'cuadril'=>'C3',  'c'=>'13/06/26','f'=>'15/07/26','d'=>28],
            ['nombre'=>'CAMINO DE REYES',             'provincia'=>'ICA',     'sector'=>4,  'cuadril'=>'C4',  'c'=>'09/05/26','f'=>'10/06/26','d'=>28],
            ['nombre'=>'YANQUIZA',                    'provincia'=>'ICA',     'sector'=>4,  'cuadril'=>'C5',  'c'=>'13/06/26','f'=>'15/07/26','d'=>28],
            ['nombre'=>'ARRABALES',                   'provincia'=>'ICA',     'sector'=>4,  'cuadril'=>'C7',  'c'=>'09/05/26','f'=>'10/06/26','d'=>28],
            ['nombre'=>'CSMC COLOR ESPERANZA',        'provincia'=>'ICA',     'sector'=>4,  'cuadril'=>'C6',  'c'=>'13/06/26','f'=>'19/08/26','d'=>58],
            // SECTOR 5 — ICA
            ['nombre'=>'EL HUARANGO',                 'provincia'=>'ICA',     'sector'=>5,  'cuadril'=>'C8',  'c'=>'13/06/26','f'=>'15/07/26','d'=>28],
            ['nombre'=>'CACHICHE',                    'provincia'=>'ICA',     'sector'=>5,  'cuadril'=>'C10', 'c'=>'13/06/26','f'=>'15/07/26','d'=>28],
            // SECTOR 6 — ICA
            ['nombre'=>'PARIÑA GRANDE',               'provincia'=>'ICA',     'sector'=>6,  'cuadril'=>'C9',  'c'=>'13/06/26','f'=>'04/07/26','d'=>19],
            ['nombre'=>'SAN RAFAEL',                  'provincia'=>'ICA',     'sector'=>6,  'cuadril'=>'C11', 'c'=>'09/05/26','f'=>'30/05/26','d'=>19],
            ['nombre'=>'CALLEJON LOS ESPINOS',        'provincia'=>'ICA',     'sector'=>6,  'cuadril'=>'C12', 'c'=>'09/05/26','f'=>'30/05/26','d'=>19],
            ['nombre'=>'PUNO',                        'provincia'=>'ICA',     'sector'=>6,  'cuadril'=>'C13', 'c'=>'09/05/26','f'=>'30/05/26','d'=>19],
            ['nombre'=>'LUJARAJA',                    'provincia'=>'ICA',     'sector'=>6,  'cuadril'=>'C14', 'c'=>'09/05/26','f'=>'30/05/26','d'=>19],
            ['nombre'=>'LOS CALDERONES',              'provincia'=>'ICA',     'sector'=>6,  'cuadril'=>'C15', 'c'=>'13/06/26','f'=>'04/07/26','d'=>19],
            ['nombre'=>'PARIÑA CHICO',                'provincia'=>'ICA',     'sector'=>6,  'cuadril'=>'C16', 'c'=>'13/06/26','f'=>'15/07/26','d'=>28],
            ['nombre'=>'EL ARENAL',                   'provincia'=>'ICA',     'sector'=>6,  'cuadril'=>'C17', 'c'=>'13/06/26','f'=>'15/07/26','d'=>28],
            ['nombre'=>'PP.JJ. EL ROSARIO',           'provincia'=>'ICA',     'sector'=>6,  'cuadril'=>'C18', 'c'=>'13/06/26','f'=>'15/07/26','d'=>28],
            // SECTOR 7 — ICA
            ['nombre'=>'LOS AQUIJES',                 'provincia'=>'ICA',     'sector'=>7,  'cuadril'=>'C1',  'c'=>'06/07/26','f'=>'10/09/26','d'=>58],
            ['nombre'=>'CS PUEBLO NUEVO',             'provincia'=>'ICA',     'sector'=>7,  'cuadril'=>'C2',  'c'=>'11/06/26','f'=>'17/08/26','d'=>58],
            ['nombre'=>'TATE',                        'provincia'=>'ICA',     'sector'=>7,  'cuadril'=>'C3',  'c'=>'11/06/26','f'=>'21/09/26','d'=>58],
            ['nombre'=>'CSMC SANTISIMA VIRGEN DE YAUCA','provincia'=>'ICA',   'sector'=>7,  'cuadril'=>'C4',  'c'=>'11/06/26','f'=>'17/08/26','d'=>58],
            ['nombre'=>'PACHACUTEC',                  'provincia'=>'ICA',     'sector'=>7,  'cuadril'=>'C5',  'c'=>'16/07/26','f'=>'17/08/26','d'=>28],
            ['nombre'=>'EL PALTO',                    'provincia'=>'ICA',     'sector'=>7,  'cuadril'=>'C6',  'c'=>'20/08/26','f'=>'21/09/26','d'=>28],
            ['nombre'=>'LA PALMA GRANDE',             'provincia'=>'ICA',     'sector'=>7,  'cuadril'=>'C7',  'c'=>'11/06/26','f'=>'17/08/26','d'=>58],
            ['nombre'=>'C.S SANTIAGO',                'provincia'=>'ICA',     'sector'=>7,  'cuadril'=>'C8',  'c'=>'16/07/26','f'=>'21/09/26','d'=>58],
            // SECTOR 8 — ICA
            ['nombre'=>'COCHARCAS',                   'provincia'=>'ICA',     'sector'=>8,  'cuadril'=>'C9',  'c'=>'06/07/26','f'=>'27/07/26','d'=>19],
            ['nombre'=>'CHANCHAJALLA',                'provincia'=>'ICA',     'sector'=>8,  'cuadril'=>'C10', 'c'=>'16/07/26','f'=>'17/08/26','d'=>28],
            // SECTOR 9 — ICA
            ['nombre'=>'SANTA DOMINGUITA',            'provincia'=>'ICA',     'sector'=>9,  'cuadril'=>'C11', 'c'=>'01/06/26','f'=>'02/07/26','d'=>28],
            ['nombre'=>'LA VENTA',                    'provincia'=>'ICA',     'sector'=>9,  'cuadril'=>'C12', 'c'=>'01/06/26','f'=>'02/07/26','d'=>28],
            ['nombre'=>'PS CORDOVA',                  'provincia'=>'ICA',     'sector'=>9,  'cuadril'=>'C13', 'c'=>'01/06/26','f'=>'02/07/26','d'=>28],
            ['nombre'=>'PAMPA CHACALTANA',             'provincia'=>'ICA',     'sector'=>9,  'cuadril'=>'C14', 'c'=>'01/06/26','f'=>'02/07/26','d'=>28],
            ['nombre'=>'OCUCAJE',                     'provincia'=>'ICA',     'sector'=>9,  'cuadril'=>'C15', 'c'=>'06/07/26','f'=>'10/09/26','d'=>58],
            // SECTOR 10 — ICA
            ['nombre'=>'AGUADA DE PALOS',             'provincia'=>'ICA',     'sector'=>10, 'cuadril'=>'C18', 'c'=>'16/07/26','f'=>'06/08/26','d'=>19],
            ['nombre'=>'PS CALLANGO',                 'provincia'=>'ICA',     'sector'=>10, 'cuadril'=>'C17', 'c'=>'16/07/26','f'=>'06/08/26','d'=>19],
            ['nombre'=>'SAN JOSE DE CURIS',           'provincia'=>'ICA',     'sector'=>10, 'cuadril'=>'C16', 'c'=>'16/07/26','f'=>'17/08/26','d'=>28],
            ['nombre'=>'PAMPAHUASI',                  'provincia'=>'ICA',     'sector'=>10, 'cuadril'=>'C19', 'c'=>'13/06/26','f'=>'15/07/26','d'=>28],
            ['nombre'=>'HUARANGAL',                   'provincia'=>'ICA',     'sector'=>10, 'cuadril'=>'C20', 'c'=>'13/06/26','f'=>'15/07/26','d'=>28],
            // SECTOR 11 — PISCO
            ['nombre'=>'CS SAN CLEMENTE',             'provincia'=>'PISCO',   'sector'=>11, 'cuadril'=>'C19', 'c'=>'16/07/26','f'=>'21/09/26','d'=>58],
            ['nombre'=>'TUPAC AMARU',                 'provincia'=>'PISCO',   'sector'=>11, 'cuadril'=>'C20', 'c'=>'16/07/26','f'=>'21/09/26','d'=>58],
            ['nombre'=>'CSMC TUPAC AMARU',            'provincia'=>'PISCO',   'sector'=>11, 'cuadril'=>'C1',  'c'=>'11/09/26','f'=>'17/11/26','d'=>58],
            ['nombre'=>'SAN MIGUEL',                  'provincia'=>'PISCO',   'sector'=>11, 'cuadril'=>'C2',  'c'=>'18/08/26','f'=>'18/09/26','d'=>28],
            ['nombre'=>'SAN JUAN DE DIOS',            'provincia'=>'PISCO',   'sector'=>11, 'cuadril'=>'C3',  'c'=>'22/09/26','f'=>'27/11/26','d'=>58],
            ['nombre'=>'SAN MARTIN DE PORRES',        'provincia'=>'PISCO',   'sector'=>11, 'cuadril'=>'C4',  'c'=>'18/08/26','f'=>'18/09/26','d'=>28],
            ['nombre'=>'LA ESPERANZA',                'provincia'=>'PISCO',   'sector'=>11, 'cuadril'=>'C5',  'c'=>'18/08/26','f'=>'18/09/26','d'=>28],
            ['nombre'=>'SAN ANDRES',                  'provincia'=>'PISCO',   'sector'=>11, 'cuadril'=>'C6',  'c'=>'22/09/26','f'=>'23/10/26','d'=>28],
            ['nombre'=>'INDEPENDENCIA',               'provincia'=>'PISCO',   'sector'=>11, 'cuadril'=>'C7',  'c'=>'18/08/26','f'=>'23/10/26','d'=>58],
            // SECTOR 12 — PISCO
            ['nombre'=>'PARACAS',                     'provincia'=>'PISCO',   'sector'=>12, 'cuadril'=>'C8',  'c'=>'22/09/26','f'=>'23/10/26','d'=>28],
            ['nombre'=>'SANTA CRUZ',                  'provincia'=>'PISCO',   'sector'=>12, 'cuadril'=>'C9',  'c'=>'28/07/26','f'=>'02/10/26','d'=>58],
            ['nombre'=>'LAGUNA GRANDE',               'provincia'=>'PISCO',   'sector'=>12, 'cuadril'=>'C10', 'c'=>'18/08/26','f'=>'08/09/26','d'=>19],
            ['nombre'=>'SAN ANDRES',                  'provincia'=>'PISCO',   'sector'=>12, 'cuadril'=>'C11', 'c'=>'03/07/26','f'=>'04/08/26','d'=>28],
            ['nombre'=>'SAN MARTIN DE PORRES',        'provincia'=>'PISCO',   'sector'=>12, 'cuadril'=>'C12', 'c'=>'03/07/26','f'=>'04/08/26','d'=>28],
            // SECTOR 13 — PISCO
            ['nombre'=>'HUMAY',                       'provincia'=>'PISCO',   'sector'=>13, 'cuadril'=>'C16', 'c'=>'18/08/26','f'=>'18/09/26','d'=>28],
            ['nombre'=>'CUCHILLA VIEJA',              'provincia'=>'PISCO',   'sector'=>13, 'cuadril'=>'C17', 'c'=>'07/08/26','f'=>'08/09/26','d'=>28],
            ['nombre'=>'BERNALES',                    'provincia'=>'PISCO',   'sector'=>13, 'cuadril'=>'C18', 'c'=>'07/08/26','f'=>'13/10/26','d'=>58],
            ['nombre'=>'LOS PARACAS',                 'provincia'=>'PISCO',   'sector'=>13, 'cuadril'=>'C19', 'c'=>'22/09/26','f'=>'23/10/26','d'=>28],
            ['nombre'=>'DOS PALMAS',                  'provincia'=>'PISCO',   'sector'=>13, 'cuadril'=>'C20', 'c'=>'22/09/26','f'=>'23/10/26','d'=>28],
            ['nombre'=>'TOMA DE LEON',                'provincia'=>'PISCO',   'sector'=>13, 'cuadril'=>'C1',  'c'=>'18/11/26','f'=>'19/12/26','d'=>28],
            ['nombre'=>'TORO LATERAL 4',              'provincia'=>'PISCO',   'sector'=>13, 'cuadril'=>'C2',  'c'=>'21/10/26','f'=>'18/11/26','d'=>28],
            ['nombre'=>'TORO LATERAL 5',              'provincia'=>'PISCO',   'sector'=>13, 'cuadril'=>'C3',  'c'=>'28/11/26','f'=>'30/12/26','d'=>28],
            // SECTOR 14 — PISCO
            ['nombre'=>'HUANCANO',                    'provincia'=>'PISCO',   'sector'=>14, 'cuadril'=>'C13', 'c'=>'03/07/26','f'=>'04/08/26','d'=>28],
            ['nombre'=>'PAMPANO',                     'provincia'=>'PISCO',   'sector'=>14, 'cuadril'=>'C14', 'c'=>'03/07/26','f'=>'04/08/26','d'=>28],
            ['nombre'=>'CAMACHO',                     'provincia'=>'PISCO',   'sector'=>14, 'cuadril'=>'C15', 'c'=>'11/09/26','f'=>'02/10/26','d'=>19],
            // SECTOR 15 — CHINCHA
            ['nombre'=>'PUEBLO NUEVO',                'provincia'=>'CHINCHA', 'sector'=>15, 'cuadril'=>'C5',  'c'=>'19/09/26','f'=>'02/12/26','d'=>64],
            ['nombre'=>'SAN ISIDRO',                  'provincia'=>'CHINCHA', 'sector'=>15, 'cuadril'=>'C4',  'c'=>'19/09/26','f'=>'27/11/26','d'=>60],
            ['nombre'=>'LOS ALAMOS',                  'provincia'=>'CHINCHA', 'sector'=>15, 'cuadril'=>'C6',  'c'=>'24/10/26','f'=>'25/11/26','d'=>28],
            ['nombre'=>'EL SALVADOR',                 'provincia'=>'CHINCHA', 'sector'=>15, 'cuadril'=>'C7',  'c'=>'24/10/26','f'=>'25/11/26','d'=>28],
            ['nombre'=>'SAN AGUSTIN',                 'provincia'=>'CHINCHA', 'sector'=>15, 'cuadril'=>'C8',  'c'=>'24/10/26','f'=>'25/11/26','d'=>28],
            ['nombre'=>'CONDORILLO ALTO',             'provincia'=>'CHINCHA', 'sector'=>15, 'cuadril'=>'C9',  'c'=>'03/10/26','f'=>'04/11/26','d'=>28],
            ['nombre'=>'CRUZ BLANCA',                 'provincia'=>'CHINCHA', 'sector'=>15, 'cuadril'=>'C10', 'c'=>'09/09/26','f'=>'10/10/26','d'=>28],
            ['nombre'=>'BALCONCITO',                  'provincia'=>'CHINCHA', 'sector'=>15, 'cuadril'=>'C11', 'c'=>'05/08/26','f'=>'05/09/26','d'=>28],
            ['nombre'=>'SUNAMPE',                     'provincia'=>'CHINCHA', 'sector'=>15, 'cuadril'=>'C12', 'c'=>'05/08/26','f'=>'10/10/26','d'=>58],
            ['nombre'=>'CSMC NUEVO HORIZONTE',        'provincia'=>'CHINCHA', 'sector'=>15, 'cuadril'=>'C13', 'c'=>'05/08/26','f'=>'10/10/26','d'=>58],
            // SECTOR 16 — CHINCHA
            ['nombre'=>'CHINCHA BAJA',                'provincia'=>'CHINCHA', 'sector'=>16, 'cuadril'=>'C14', 'c'=>'05/08/26','f'=>'10/10/26','d'=>58],
            ['nombre'=>'SANTA ROSA',                  'provincia'=>'CHINCHA', 'sector'=>16, 'cuadril'=>'C15', 'c'=>'03/10/26','f'=>'04/11/26','d'=>28],
            ['nombre'=>'LURINCHINCHA',                'provincia'=>'CHINCHA', 'sector'=>16, 'cuadril'=>'C18', 'c'=>'14/10/26','f'=>'18/11/26','d'=>28],
            ['nombre'=>'TAMBO DE MORA',               'provincia'=>'CHINCHA', 'sector'=>16, 'cuadril'=>'C17', 'c'=>'09/09/26','f'=>'14/11/26','d'=>58],
            ['nombre'=>'GROCIO PRADO',                'provincia'=>'CHINCHA', 'sector'=>16, 'cuadril'=>'C16', 'c'=>'19/09/26','f'=>'25/11/26','d'=>58],
            ['nombre'=>'TOPARA',                      'provincia'=>'CHINCHA', 'sector'=>16, 'cuadril'=>'C19', 'c'=>'24/10/26','f'=>'25/11/26','d'=>28],
            ['nombre'=>'ALTO LARAN',                  'provincia'=>'CHINCHA', 'sector'=>16, 'cuadril'=>'C20', 'c'=>'24/10/26','f'=>'30/12/26','d'=>58],
            ['nombre'=>'AYLLOQUE',                    'provincia'=>'CHINCHA', 'sector'=>16, 'cuadril'=>'C1',  'c'=>'21/12/26','f'=>'21/01/27','d'=>28],
            // SECTOR 17 — CHINCHA
            ['nombre'=>'EL CARMEN',                   'provincia'=>'CHINCHA', 'sector'=>17, 'cuadril'=>'C2',  'c'=>'22/10/26','f'=>'23/11/26','d'=>28],
            ['nombre'=>'WIRACOCHA',                   'provincia'=>'CHINCHA', 'sector'=>17, 'cuadril'=>'C7',  'c'=>'26/11/26','f'=>'28/12/26','d'=>28],
            ['nombre'=>'SAN JOSE',                    'provincia'=>'CHINCHA', 'sector'=>17, 'cuadril'=>'C13', 'c'=>'12/10/26','f'=>'12/11/26','d'=>28],
            ['nombre'=>'HOJA REDONDA',                'provincia'=>'CHINCHA', 'sector'=>17, 'cuadril'=>'C5',  'c'=>'03/12/26','f'=>'04/01/27','d'=>28],
            ['nombre'=>'HUACHINGA',                   'provincia'=>'CHINCHA', 'sector'=>17, 'cuadril'=>'C6',  'c'=>'26/11/26','f'=>'28/12/26','d'=>28],
            ['nombre'=>'CHAVIN',                      'provincia'=>'CHINCHA', 'sector'=>17, 'cuadril'=>'C3',  'c'=>'31/12/26','f'=>'21/01/27','d'=>19],
            // SECTOR 18 — CHINCHA
            ['nombre'=>'SAN PEDRO DE HUACARPANA',     'provincia'=>'CHINCHA', 'sector'=>18, 'cuadril'=>'C8',  'c'=>'26/11/26','f'=>'28/12/26','d'=>28],
            ['nombre'=>'LISCAY',                      'provincia'=>'CHINCHA', 'sector'=>18, 'cuadril'=>'C9',  'c'=>'05/11/26','f'=>'07/12/26','d'=>28],
            ['nombre'=>'VISTA ALEGRE',                'provincia'=>'CHINCHA', 'sector'=>18, 'cuadril'=>'C10', 'c'=>'12/10/26','f'=>'12/11/26','d'=>28],
            ['nombre'=>'BELLAVISTA',                  'provincia'=>'CHINCHA', 'sector'=>18, 'cuadril'=>'C11', 'c'=>'07/09/26','f'=>'12/11/26','d'=>58],
            ['nombre'=>'SAN JUAN DE YANAC',           'provincia'=>'CHINCHA', 'sector'=>18, 'cuadril'=>'C12', 'c'=>'12/10/26','f'=>'12/11/26','d'=>28],
            ['nombre'=>'HUAÑUPIZA',                   'provincia'=>'CHINCHA', 'sector'=>18, 'cuadril'=>'C4',  'c'=>'28/11/26','f'=>'19/12/26','d'=>19],
            // SECTOR 19 — PALPA
            ['nombre'=>'RIO GRANDE',                  'provincia'=>'PALPA',   'sector'=>19, 'cuadril'=>'C14', 'c'=>'12/10/26','f'=>'17/12/26','d'=>58],
            ['nombre'=>'SAN IGNACIO',                 'provincia'=>'PALPA',   'sector'=>19, 'cuadril'=>'C15', 'c'=>'05/11/26','f'=>'26/11/26','d'=>19],
            ['nombre'=>'SACRAMENTO',                  'provincia'=>'PALPA',   'sector'=>19, 'cuadril'=>'C18', 'c'=>'16/11/26','f'=>'17/12/26','d'=>28],
            ['nombre'=>'LLIPATA',                     'provincia'=>'PALPA',   'sector'=>19, 'cuadril'=>'C17', 'c'=>'16/11/26','f'=>'17/12/26','d'=>28],
            ['nombre'=>'CSMC MENTE SANA',             'provincia'=>'PALPA',   'sector'=>19, 'cuadril'=>'C16', 'c'=>'26/11/26','f'=>'01/02/27','d'=>58],
            ['nombre'=>'PUEBLO NUEVO',                'provincia'=>'PALPA',   'sector'=>19, 'cuadril'=>'C19', 'c'=>'26/11/26','f'=>'08/02/27','d'=>64],
            ['nombre'=>'LA ISLA',                     'provincia'=>'PALPA',   'sector'=>19, 'cuadril'=>'C20', 'c'=>'31/12/26','f'=>'01/02/27','d'=>28],
            // SECTOR 20 — PALPA
            ['nombre'=>'SANTA CRUZ',                  'provincia'=>'PALPA',   'sector'=>20, 'cuadril'=>'C7',  'c'=>'29/12/26','f'=>'05/03/27','d'=>58],
            ['nombre'=>'SAN FRANCISCO',               'provincia'=>'PALPA',   'sector'=>20, 'cuadril'=>'C3',  'c'=>'22/01/27','f'=>'12/02/27','d'=>19],
            ['nombre'=>'EL CARMEN',                   'provincia'=>'PALPA',   'sector'=>20, 'cuadril'=>'C1',  'c'=>'22/01/27','f'=>'23/02/27','d'=>28],
            ['nombre'=>'SARAMARCA',                   'provincia'=>'PALPA',   'sector'=>20, 'cuadril'=>'C4',  'c'=>'21/12/26','f'=>'11/01/27','d'=>19],
            ['nombre'=>'PAMPA BLANCA',                'provincia'=>'PALPA',   'sector'=>20, 'cuadril'=>'C5',  'c'=>'05/01/27','f'=>'26/01/27','d'=>19],
            ['nombre'=>'EL PALMAR',                   'provincia'=>'PALPA',   'sector'=>20, 'cuadril'=>'C6',  'c'=>'29/12/26','f'=>'19/01/27','d'=>19],
            ['nombre'=>'TIBILLO',                     'provincia'=>'PALPA',   'sector'=>20, 'cuadril'=>'C2',  'c'=>'24/11/26','f'=>'25/12/26','d'=>28],
            // SECTOR 21 — NAZCA
            ['nombre'=>'SAN LUIS DE PAJONAL',         'provincia'=>'NAZCA',   'sector'=>21, 'cuadril'=>'C8',  'c'=>'29/12/26','f'=>'19/01/27','d'=>19],
            ['nombre'=>'BUENA FE',                    'provincia'=>'NAZCA',   'sector'=>21, 'cuadril'=>'C9',  'c'=>'08/12/26','f'=>'08/01/27','d'=>28],
            ['nombre'=>'LAS CAÑAS',                   'provincia'=>'NAZCA',   'sector'=>21, 'cuadril'=>'C10', 'c'=>'13/11/26','f'=>'04/12/26','d'=>19],
            ['nombre'=>'SAN JAVIER',                  'provincia'=>'NAZCA',   'sector'=>21, 'cuadril'=>'C11', 'c'=>'13/11/26','f'=>'04/12/26','d'=>19],
            ['nombre'=>'CSMC DECIDETE A SER FELIZ',   'provincia'=>'NAZCA',   'sector'=>21, 'cuadril'=>'C12', 'c'=>'13/11/26','f'=>'15/12/26','d'=>28],
            // SECTOR 22 — NAZCA
            ['nombre'=>'VISTA ALEGRE',                'provincia'=>'NAZCA',   'sector'=>22, 'cuadril'=>'C13', 'c'=>'13/11/26','f'=>'21/01/27','d'=>60],
            ['nombre'=>'COPARA',                      'provincia'=>'NAZCA',   'sector'=>22, 'cuadril'=>'C14', 'c'=>'18/12/26','f'=>'19/01/27','d'=>28],
            ['nombre'=>'LAS TRANCAS',                 'provincia'=>'NAZCA',   'sector'=>22, 'cuadril'=>'C15', 'c'=>'27/11/26','f'=>'29/12/26','d'=>28],
            ['nombre'=>'TARUGA',                      'provincia'=>'NAZCA',   'sector'=>22, 'cuadril'=>'C16', 'c'=>'02/02/27','f'=>'05/03/27','d'=>28],
            ['nombre'=>'CABILDO',                     'provincia'=>'NAZCA',   'sector'=>22, 'cuadril'=>'C17', 'c'=>'18/12/26','f'=>'19/01/27','d'=>28],
            // SECTOR 23 — NAZCA
            ['nombre'=>'EL INGENIO',                  'provincia'=>'NAZCA',   'sector'=>23, 'cuadril'=>'C18', 'c'=>'18/12/26','f'=>'23/02/27','d'=>58],
            ['nombre'=>'SAN MIGUEL PASCANA',          'provincia'=>'NAZCA',   'sector'=>23, 'cuadril'=>'C19', 'c'=>'09/02/27','f'=>'12/03/27','d'=>28],
            ['nombre'=>'TULIN',                       'provincia'=>'NAZCA',   'sector'=>23, 'cuadril'=>'C20', 'c'=>'02/02/27','f'=>'05/03/27','d'=>28],
            ['nombre'=>'TUPAC AMARU',                 'provincia'=>'NAZCA',   'sector'=>23, 'cuadril'=>'C1',  'c'=>'24/02/27','f'=>'17/03/27','d'=>19],
            ['nombre'=>'CHANGUILLO',                  'provincia'=>'NAZCA',   'sector'=>23, 'cuadril'=>'C2',  'c'=>'26/12/26','f'=>'10/03/27','d'=>64],
            ['nombre'=>'COYUNGO',                     'provincia'=>'NAZCA',   'sector'=>23, 'cuadril'=>'C3',  'c'=>'13/02/27','f'=>'17/03/27','d'=>28],
            ['nombre'=>'MARCONA',                     'provincia'=>'NAZCA',   'sector'=>23, 'cuadril'=>'C4',  'c'=>'12/01/27','f'=>'22/03/27','d'=>60],
        ];
    }

    /** Normaliza un nombre para comparación */
    private function norm(string $s): string
    {
        $s = mb_strtoupper($s);
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        $s = preg_replace('/[^A-Z0-9 ]/', '', $s);
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    /** Convierte DD/MM/YY a Y-m-d */
    private function fecha(string $dmy): string
    {
        [$d, $m, $y] = explode('/', $dmy);
        $year = (int)$y < 50 ? '20' . $y : '19' . $y;
        return sprintf('%04d-%02d-%02d', $year, $m, $d);
    }

    public function run(): void
    {
        DB::table('programacion_sectores')->truncate();
        // Cargar establecimientos para el match
        $establecimientos = Establecimiento::all(['id', 'nombre', 'provincia']);

        // Preparar lista en memoria
        $listaEst = [];
        foreach ($establecimientos as $e) {
            $listaEst[] = [
                'id' => $e->id,
                'nombre' => $this->norm($e->nombre),
                'provincia' => $this->norm($e->provincia)
            ];
        }

        $now = now();

        foreach ($this->cronograma() as $item) {
            $nNorm = $this->norm($item['nombre']);
            $pNorm = $this->norm($item['provincia']);

            $estId = null;

            // 1. Búsqueda exacta (Nombre + Provincia) PREFERENTE
            foreach ($listaEst as $e) {
                if ($e['nombre'] === $nNorm && $e['provincia'] === $pNorm) {
                    $estId = $e['id'];
                    break;
                }
            }

            // 2. Búsqueda parcial (Nombre + Provincia) - Para palabras clave
            if (!$estId) {
                $palabras = array_filter(explode(' ', $nNorm), fn($w) => strlen($w) >= 4);
                if (count($palabras) > 0) {
                    foreach ($listaEst as $e) {
                        if ($e['provincia'] === $pNorm && !array_diff($palabras, explode(' ', $e['nombre']))) {
                            $estId = $e['id'];
                            break;
                        }
                    }
                }
            }

            // 3. Búsqueda de fallback: Exacta (Solo Nombre) - Cuando la provincia en el PDF está mal asignada y no es homónimo
            if (!$estId) {
                foreach ($listaEst as $e) {
                    if ($e['nombre'] === $nNorm) {
                        $estId = $e['id'];
                        break;
                    }
                }
            }

            // 4. Búsqueda de fallback: Parcial (Solo Nombre)
            if (!$estId) {
                $palabras = array_filter(explode(' ', $nNorm), fn($w) => strlen($w) >= 4);
                if (count($palabras) > 0) {
                    foreach ($listaEst as $e) {
                        if (!array_diff($palabras, explode(' ', $e['nombre']))) {
                            $estId = $e['id'];
                            break;
                        }
                    }
                }
            }

            DB::table('programacion_sectores')->insert([
                'establecimiento_id' => $estId,
                'nombre_pdf'         => $item['nombre'],
                'provincia'          => $item['provincia'],
                'sector'             => $item['sector'],
                'cuadril'            => $item['cuadril'],
                'comienzo'           => $this->fecha($item['c']),
                'fin'                => $this->fecha($item['f']),
                'dias'               => $item['d'],
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        $total = DB::table('programacion_sectores')->count();
        $conEst = DB::table('programacion_sectores')->whereNotNull('establecimiento_id')->count();
        $this->command->info("✅ Programación cargada: $total registros ($conEst con establecimiento vinculado).");
    }
}
