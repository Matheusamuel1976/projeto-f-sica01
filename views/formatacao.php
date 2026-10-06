<?php
declare(strict_types=1);
function escapar(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
function numeroVisivel(float $valor): string
{
    if ($valor != 0 && (abs($valor) < 0.000001 || abs($valor) >= 1000000)) {
        return escapar(sprintf('%.6g', $valor));
    }
    return rtrim(rtrim(number_format($valor, 6, ',', ''), '0'), ',');
}
