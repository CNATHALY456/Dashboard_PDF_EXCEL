<?php
require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();


$imgTemp = $_POST['graficoTemp'] ?? '';
$imgLluvia = $_POST['graficoLluvia'] ?? '';
$imgHumedad = $_POST['graficoHumedad'] ?? '';
$imgPromedio = $_POST['graficoCalorPromedio'] ?? '';


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
body { font-family: Arial; margin: 20px; }
h1, h2, h3 { text-align: center; }
h1 { color: #2a5298; }

.info {
    margin-bottom: 20px;
    text-align: center;
}

.seccion {
    margin-top: 30px;
}

img {
    width: 100%;
    max-height: 250px;
    margin-top: 10px;
}

.descripcion {
    font-size: 12px;
    text-align: center;
    margin-top: 5px;
}

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

<h1>DASHBOARD AMBIENTAL</h1>

<div class='info'>
    <p><strong>Universidad:</strong> Universidad Cristiana de las Asambleas de Dios</p>
    <p><strong>Materia:</strong> Sistemas de Información Gerencial</p>
    <p><strong>Docente:</strong> Andrés Alfaro</p>
    <p><strong>Alumna:</strong> Nathaly Alexandra Contreras Arevalo</p>
</div>

<div class='seccion'>
    <h3>Gráfico de Temperatura</h3>
    <img src='$imgTemp'>
    <p class='descripcion'>Comparación del nivel de calor entre ciudades.</p>
</div>

<div class='seccion'>
    <h3>Gráfico de Lluvia</h3>
    <img src='$imgLluvia'>
    <p class='descripcion'>Cantidad de precipitación registrada.</p>
</div>

<div class='seccion'>
    <h3>Gráfico de Humedad</h3>
    <img src='$imgHumedad'>
    <p class='descripcion'>Porcentaje de humedad relativa.</p>
</div>

<div class='seccion'>
    <h3>Promedio de Temperatura</h3>
    <img src='$imgPromedio'>
    <p class='descripcion'>Nivel promedio de temperatura.</p>
</div>

<div class='seccion'>
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
</div>
";

$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "portrait");
$dompdf->render();

$dompdf->stream("reporte_dashboard.pdf", ["Attachment" => true]);
?>