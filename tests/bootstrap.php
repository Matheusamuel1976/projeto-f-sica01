<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/Medicoes.php';
require_once __DIR__ . '/../models/Analise.php';
require_once __DIR__ . '/../models/Amostras.php';

function entradaDeTeste(): array
{
    return ['ph_antes' => '7', 'ph_depois' => '6.5', 'turbidez_antes' => '20', 'turbidez_depois' => '5',
        'cloro_antes' => '2', 'cloro_depois' => '1', 'dureza_antes' => '100', 'dureza_depois' => '80',
        'temperatura_antes' => '25', 'temperatura_depois' => '24'];
}
