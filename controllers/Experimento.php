<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/Medicoes.php';
require_once __DIR__ . '/../models/Analise.php';
require_once __DIR__ . '/../models/Amostras.php';

$entrada = [];
$erros = [];
$resultados = [];
$mensagem = '';
$parecer = '';
$repositorio = $repositorioTeste ?? new Amostras(__DIR__ . '/../data/amostras.json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entrada = $_POST;
    $leitura = lerMedicoes($entrada);
    $erros = $leitura['erros'];
    if ($erros === []) {
        $resultados = compararMedicoes($leitura['valores'], referenciasEscolares());
        $parecer = parecerAmostra($resultados);
        $acao = $entrada['acao'] ?? 'comparar';
        if ($acao === 'salvar') {
            try {
                $nome = is_string($entrada['nome'] ?? null) ? $entrada['nome'] : '';
                $repositorio->salvar($nome, $leitura['valores']);
                $mensagem = 'Amostra cadastrada. Ela aparece na lista abaixo.';
            } catch (InvalidArgumentException $erro) {
                $erros['nome'] = $erro->getMessage();
            }
        }
    }
}
$amostras = $repositorio->listar();
require __DIR__ . '/../views/experimento.php';
