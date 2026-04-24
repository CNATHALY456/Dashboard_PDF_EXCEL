<?php
require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();


$ciudades = [
    ["nombre" => "San Salvador", "lat" => 13.69, "lon" => -89.19],
    ["nombre" => "Santa Ana", "lat" => 13.99, "lon" => -89.56],
    ["nombre" => "San Miguel", "lat" => 13.48, "lon" => -88.18],
    ["nombre" => "La Unión", "lat" => 13.34, "lon" => -87.84],
    ["nombre" => "Sonsonate", "lat" => 13.72, "lon" => -89.72]
];


$filas = "";

foreach ($ciudades as $c) {

    $url = "https://api.open-meteo.com/v1/forecast?latitude={$c['lat']}&longitude={$c['lon']}&current=temperature_2m,relative_humidity_2m,precipitation";

    $json = file_get_contents($url);
    $data = json_decode($json, true);

    $temp = $data['current']['temperature_2m'];
    $hum = $data['current']['relative_humidity_2m'];
    $lluvia = $data['current']['precipitation'];

    $filas .= "
        <tr>
            <td>{$c['nombre']}</td>
            <td>{$lluvia}</td>
            <td>{$temp}</td>
            <td>{$hum}</td>
        </tr>
    ";
}


$html = "
<style>
    body { font-family: Arial; }
    h1 { text-align: center; color: #2a5298; }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th {
        background: #2a5298;
        color: white;
        padding: 10px;
    }

    td {
        padding: 8px;
        text-align: center;
        border: 1px solid #ddd;
    }
</style>

<h1>Dashboard Ambiental</h1>

<p><strong>Alumna:</strong> Nathaly Alexandra Contreras Arevalo</p>

<h3>Resumen Climático</h3>

<table>
    <thead>
        <tr>
            <th>Ciudad</th>
            <th>Lluvia</th>
            <th>Temperatura</th>
            <th>Humedad</th>
        </tr>
    </thead>
    <tbody>
        $filas
    </tbody>
</table>
";

$dompdf->loadHtml($html);
$dompdf->render();


$dompdf->stream("reporte_dashboard.pdf", ["Attachment" => true]);
?>