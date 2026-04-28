<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$imgTemp = $_POST['graficoTemp'] ?? '';
$imgLluvia = $_POST['graficoLluvia'] ?? '';
$imgHumedad = $_POST['graficoHumedad'] ?? '';
$imgPromedio = $_POST['graficoCalorPromedio'] ?? '';

$spreadsheet = new Spreadsheet();


$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Resumen");


$sheet->mergeCells('A1:D1');
$sheet->setCellValue('A1', 'DASHBOARD AMBIENTAL - EL SALVADOR');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A2:D2');
$sheet->setCellValue('A2', 'Universidad Cristiana de las Asambleas de Dios');

$sheet->mergeCells('A3:D3');
$sheet->setCellValue('A3', 'Docente: Andrés Alfaro');

$sheet->mergeCells('A4:D4');
$sheet->setCellValue('A4', 'Alumna: Nathaly Alexandra Contreras Arevalo');


$sheet->setCellValue('A6', 'Ciudad');
$sheet->setCellValue('B6', 'Lluvia (mm)');
$sheet->setCellValue('C6', 'Temperatura (°C)');
$sheet->setCellValue('D6', 'Humedad (%)');

$sheet->getStyle('A6:D6')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '2A5298']
    ],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

/* DATOS */
$ciudades = [
    ["nombre" => "San Salvador", "lat" => 13.69, "lon" => -89.19],
    ["nombre" => "Santa Ana", "lat" => 13.99, "lon" => -89.56],
    ["nombre" => "San Miguel", "lat" => 13.48, "lon" => -88.18],
    ["nombre" => "La Unión", "lat" => 13.34, "lon" => -87.84],
    ["nombre" => "Sonsonate", "lat" => 13.72, "lon" => -89.72]
];

$fila = 7;

foreach ($ciudades as $c) {
    $url = "https://api.open-meteo.com/v1/forecast?latitude={$c['lat']}&longitude={$c['lon']}&current=temperature_2m,relative_humidity_2m,precipitation";

    $json = file_get_contents($url);
    $data = json_decode($json, true);

    $sheet->setCellValue('A' . $fila, $c['nombre']);
    $sheet->setCellValue('B' . $fila, $data['current']['precipitation']);
    $sheet->setCellValue('C' . $fila, $data['current']['temperature_2m']);
    $sheet->setCellValue('D' . $fila, $data['current']['relative_humidity_2m']);

    $fila++;
}


$sheet->getStyle("A6:D" . ($fila - 1))->applyFromArray([
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
]);

$sheet->getStyle("A7:D" . ($fila - 1))
      ->getAlignment()
      ->setHorizontal(Alignment::HORIZONTAL_CENTER);

foreach(range('A','D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}


$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle("Graficos");


foreach (range('A','L') as $col) {
    $sheet2->getColumnDimension($col)->setWidth(18);
}


$sheet2->mergeCells('A1:L1');
$sheet2->setCellValue('A1', 'GRÁFICOS CLIMÁTICOS');
$sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet2->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


$sheet2->setCellValue('A2', 'Temperatura');
$sheet2->setCellValue('J2', 'Lluvia');
$sheet2->setCellValue('A19', 'Humedad');
$sheet2->setCellValue('F19', 'Promedio');

$sheet2->getStyle('A2:F19')->getFont()->setBold(true);


function insertarImagen($base64, $celda, $sheet, $nombre, $ancho, $alto) {
    if (!$base64) return;

    $data = explode(',', $base64);
    if (count($data) < 2) return;

    $img = base64_decode($data[1]);

    $ruta = tempnam(sys_get_temp_dir(), 'img') . '.png';
    file_put_contents($ruta, $img);

    $drawing = new Drawing();
    $drawing->setName($nombre);
    $drawing->setPath($ruta);
    $drawing->setWidth($ancho);
    $drawing->setHeight($alto);
    $drawing->setCoordinates($celda);
    $drawing->setWorksheet($sheet);
}


insertarImagen($imgTemp, 'A3', $sheet2, 'Temp', 700, 250);   // A-H ancho
insertarImagen($imgLluvia, 'J3', $sheet2, 'Lluvia', 400, 250);

insertarImagen($imgHumedad, 'A20', $sheet2, 'Humedad', 400, 250);
insertarImagen($imgPromedio, 'F20', $sheet2, 'Promedio', 400, 250);


$filename = "reporte_dashboard_" . date("Y-m-d") . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>