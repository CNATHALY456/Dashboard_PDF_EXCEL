<?php

$ciudades = [
    ["nombre" => "San Salvador", "lat" => 13.69, "lon" => -89.19],
    ["nombre" => "Santa Ana", "lat" => 13.99, "lon" => -89.56],
    ["nombre" => "San Miguel", "lat" => 13.48, "lon" => -88.18],
    ["nombre" => "La Unión", "lat" => 13.34, "lon" => -87.84],
    ["nombre" => "Sonsonate", "lat" => 13.72, "lon" => -89.72]
];


$filename = "reporte_climatico_" . date("Y-m-d") . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=$filename");


$output = fopen('php://output', 'w');


fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ["DASHBOARD AMBIENTAL - EL SALVADOR"]);
fputcsv($output, ["Universidad Cristiana de las Asambleas de Dios"]);
fputcsv($output, ["Docente: Andrés Alfaro"]);
fputcsv($output, ["Alumna: Nathaly Alexandra Contreras Arevalo"]);
fputcsv($output, []);


fputcsv($output, ["Ciudad", "Lluvia (mm)", "Temperatura (°C)", "Humedad (%)"]);


foreach ($ciudades as $c) {

    $url = "https://api.open-meteo.com/v1/forecast?latitude={$c['lat']}&longitude={$c['lon']}&current=temperature_2m,relative_humidity_2m,precipitation";

    $json = file_get_contents($url);
    $data = json_decode($json, true);

    fputcsv($output, [
        $c['nombre'],
        $data['current']['precipitation'],
        $data['current']['temperature_2m'],
        $data['current']['relative_humidity_2m']
    ]);
}

fclose($output);
exit;
?>