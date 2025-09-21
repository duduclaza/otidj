<?php
/**
 * Script para gerar um template de teste simples
 */

// Dados do template
$data = [
    ['Modelo', 'Peso Cheio (g)', 'Peso Vazio (g)', 'Capacidade Folhas', 'Preço Toner (R$)', 'Cor', 'Tipo'],
    ['HP CF280A', '850.5', '120.3', '2700', '89.90', 'Black', 'Original'],
    ['Canon 045', '720.8', '110.2', '1300', '75.50', 'Yellow', 'Compativel'],
    ['Brother TN-421', '680.9', '105.1', '1800', '65.00', 'Magenta', 'Remanufaturado']
];

// Gerar CSV
$filename = 'template_toners_test.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write data
foreach ($data as $row) {
    fputcsv($output, $row, ',');
}

fclose($output);
exit;
?>
