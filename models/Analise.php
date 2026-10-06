<?php
declare(strict_types=1);

function referenciasEscolares(): array
{
    return [

        'ph' => ['min' => 6.0, 'max' => 9.5],

        'turbidez' => ['min' => 0.0, 'max' => 5.0],
        'cloro' => ['min' => 0.2, 'max' => 5.0],
        'dureza' => ['min' => 0.0, 'max' => 300.0],
        'temperatura' => null,
    ];
}

function parecerAmostra(array $linhas): string
{
    $fora = [];
    $sem = [];
    foreach ($linhas as $linha) {
        if ($linha['classeDepois'] === 'sem referência') {
            $sem[] = $linha['nome'];
        } elseif ($linha['classeDepois'] !== 'na faixa') {
            $fora[] = $linha['nome'];
        }
    }
    $texto = $fora === [] ? 'Os parâmetros com referência estão nas faixas adotadas.'
        : 'Fora das faixas adotadas: ' . implode(', ', $fora) . '.';
    if ($sem !== []) {
        $texto .= ' Sem referência: ' . implode(', ', $sem) . '.';
    }
    return $texto . ' Avaliação parcial; não comprova potabilidade.';
}

