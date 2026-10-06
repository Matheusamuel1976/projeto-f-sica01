<?php
declare(strict_types=1);

function parametros(): array
{
    return [
        'ph' => ['nome' => 'pH', 'unidade' => 'sem unidade', 'min' => 0, 'max' => 14, 'reducao' => false],
        'turbidez' => ['nome' => 'Turbidez', 'unidade' => 'NTU', 'min' => 0, 'max' => null, 'reducao' => true],
        'cloro' => ['nome' => 'Cloro residual livre', 'unidade' => 'mg/L', 'min' => 0, 'max' => null, 'reducao' => true],
        'dureza' => ['nome' => 'Dureza', 'unidade' => 'mg/L de CaCO₃', 'min' => 0, 'max' => null, 'reducao' => true],
        'temperatura' => ['nome' => 'Temperatura', 'unidade' => '°C', 'min' => -273.15, 'max' => null, 'reducao' => false],
    ];
}

function lerMedicoes(array $entrada): array
{
    $valores = [];
    $erros = [];
    foreach (parametros() as $chave => $regra) {
        foreach (['antes', 'depois'] as $momento) {
            $campo = $chave . '_' . $momento;
            $texto = $entrada[$campo] ?? '';
            $rotulo = $regra['nome'] . ' (' . $momento . ')';
            if (!is_string($texto) || trim($texto) === '') {
                $erros[$campo] = $rotulo . ': preencha com um número.';
                continue;
            }
            $texto = str_replace(',', '.', trim($texto));
            if (!preg_match('/^[+-]?(?:\d+(?:\.\d*)?|\.\d+)$/D', $texto)) {
                $erros[$campo] = $rotulo . ': use um número decimal, sem unidade ou separador de milhar.';
                continue;
            }
            $numero = (float) $texto;
            if (
                !is_finite($numero) || $numero < $regra['min'] ||
                ($regra['max'] !== null && $numero > $regra['max'])
            ) {
                $erros[$campo] = $rotulo . ': valor fora do domínio aceito (' . $regra['min'] .
                    ($regra['max'] === null ? ' ou maior' : ' a ' . $regra['max']) . ').';
                continue;
            }
            $valores[$chave][$momento] = $numero;
        }
    }
    return ['valores' => $valores, 'erros' => $erros];
}

function classificarFaixa(float $valor, ?array $faixa): string
{
    if ($faixa === null) {
        return 'sem referência';
    }
    if ($valor < $faixa['min']) {
        return 'abaixo da faixa';
    }
    if ($valor > $faixa['max']) {
        return 'acima da faixa';
    }
    return 'na faixa';
}

function compararMedicoes(array $valores, array $referencias = []): array
{
    $linhas = [];
    foreach (parametros() as $chave => $regra) {
        $antes = $valores[$chave]['antes'];
        $depois = $valores[$chave]['depois'];
        $mudanca = $depois - $antes;
        $percentual = null;
        $nota = 'não se aplica';
        if ($regra['reducao']) {
            if ($antes == 0) {
                $nota = 'indefinida: inicial zero';
            } else {

                $percentual = (($antes - $depois) / $antes) * 100;
                $nota = 'calculada';
                if (!is_finite($percentual)) {
                    $percentual = null;
                    $nota = 'fora do alcance numérico';
                }
            }
        }
        $faixa = $referencias[$chave] ?? null;
        $linhas[] = [
            'nome' => $regra['nome'],
            'unidade' => $regra['unidade'],
            'antes' => $antes,
            'depois' => $depois,
            'mudanca' => $mudanca,
            'direcao' => $mudanca > 0 ? 'aumentou' : ($mudanca < 0 ? 'diminuiu' : 'sem mudança'),
            'percentual' => $percentual,
            'nota' => $nota,
            'classeAntes' => classificarFaixa($antes, $faixa),
            'classeDepois' => classificarFaixa($depois, $faixa),
        ];
    }
    return $linhas;
}
