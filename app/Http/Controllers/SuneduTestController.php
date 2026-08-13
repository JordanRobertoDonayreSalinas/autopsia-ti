<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuneduTestController extends Controller
{
    /**
     * Endpoint de prueba (MOCK) para simular la respuesta de SUNEDU
     * Utiliza la data real proporcionada por el usuario para pruebas de UI.
     */
    public function buscar($dni)
    {
        // Solo retornamos datos si es el DNI de prueba
        if ($dni === '71883058') {
            return response()->json([
                "success" => true,
                "data" => [
                    "oData" => [
                        "GradosNacionales" => [
                            [
                                "IdSolicitud" => 6043253,
                                "CodAbreGyt" => "B",
                                "ActoTip" => "BACHILLERATO AUTOMÁTICO",
                                "Apemat" => "SALINAS",
                                "Apepat" => "DONAYRE",
                                "Universidad" => "UNIVERSIDAD INCA GARCILASO DE LA VEGA ASOCIACIÓN CIVIL",
                                "DiplFec" => "08/05/18",
                                "GradTitu" => "BACHILLER EN INGENIERÍA DE SISTEMAS Y CÓMPUTO",
                                "Nombre" => "JORDAN ROBERTO",
                                "Apellidos" => "DONAYRE SALINAS"
                            ],
                            [
                                "IdSolicitud" => 7873013,
                                "CodAbreGyt" => "T",
                                "ActoTip" => "EXAMEN DE SUFICIENCIA PROFESIONAL",
                                "Apemat" => "SALINAS",
                                "Apepat" => "DONAYRE",
                                "Universidad" => "UNIVERSIDAD AUTÓNOMA DE ICA S.A.C.",
                                "DiplFec" => "24/03/22",
                                "GradTitu" => "INGENIERO DE SISTEMAS",
                                "Nombre" => "JORDAN ROBERTO",
                                "Apellidos" => "DONAYRE SALINAS"
                            ]
                        ],
                        "GradosExtranjeros" => null
                    ],
                    "bSuccess" => true,
                    "sMessage" => "2 grados nacionales y 0 grados extranjeros encontrados."
                ]
            ]);
        }

        // Si es otro DNI, devolvemos que no hay datos
        return response()->json([
            "success" => true,
            "data" => [
                "oData" => [
                    "GradosNacionales" => [],
                    "GradosExtranjeros" => null
                ],
                "bSuccess" => true,
                "sMessage" => "0 grados nacionales y 0 grados extranjeros encontrados."
            ]
        ]);
    }
}
