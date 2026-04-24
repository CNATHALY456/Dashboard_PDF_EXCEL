<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();


$sheet->mergeCells('A1:D1');
$sheet->setCellValue('A1', 'DASHBOARD AMBIENTAL - EL SALVADOR');

$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


$sheet->mergeCells('A2:D2');
$sheet->setCellValue('A2', 'Universidad Cristiana de las Asambleas de Dios');

$sheet->mergeCells('A3:D3');
$sheet->setCellValue('A3', 'Alumna: Nathaly Alexandra Contreras Arevalo');

$sheet->mergeCells('A4:D4');
$sheet->setCellValue('A4', 'Materia: Sistemas de Información Gerencial');


$sheet->setCellValue('A6', 'Ciudad');
$sheet->setCellValue('B6', 'Lluvia (mm)');
$sheet->setCellValue('C6', 'Temperatura (°C)');
$sheet->setCellValue('D6', 'Humedad (%)');

$sheet->getStyle('A6:D6')->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '2A5298']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ]
]);


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

    $temp = $data['current']['temperature_2m'];
    $hum = $data['current']['relative_humidity_2m'];
    $lluvia = $data['current']['precipitation'];

    $sheet->setCellValue('A' . $fila, $c['nombre']);
    $sheet->setCellValue('B' . $fila, $lluvia);
    $sheet->setCellValue('C' . $fila, $temp);
    $sheet->setCellValue('D' . $fila, $hum);

    $fila++;
}


$sheet->getStyle("A7:D" . ($fila - 1))
      ->getAlignment()
      ->setHorizontal(Alignment::HORIZONTAL_CENTER);


$sheet->getStyle("A6:D" . ($fila - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ]
]);


foreach(range('A','D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}


$filename = "reporte_dashboard_" . date("Y-m-d") . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>