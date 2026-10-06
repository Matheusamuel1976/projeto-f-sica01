<?php
declare(strict_types=1);
$arquivo = __DIR__ . '/../reports/clover.xml';
if (!is_file($arquivo)) {
    fwrite(STDERR, "Relatório ausente. Execute PHPUnit com Xdebug ou PCOV e --coverage-clover.\n");
    exit(1);
}
$xml = simplexml_load_file($arquivo);
if ($xml === false || !isset($xml->project->metrics)) {
    fwrite(STDERR, "Relatório Clover inválido.\n"); exit(1);
}
$metricas = $xml->project->metrics;
$total = (int) $metricas['statements']; $cobertos = (int) $metricas['coveredstatements'];
$porcentagem = $total > 0 ? $cobertos / $total * 100 : 0;
printf("Cobertura de linhas executáveis (models + controllers): %.2f%%; mínimo: 80%%.\n", $porcentagem);
exit($porcentagem >= 80 ? 0 : 1);
