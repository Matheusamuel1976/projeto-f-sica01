<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ParecerTest extends TestCase
{
    public function testParecerParcialComParametrosNaFaixa(): void
    {
        $r = compararMedicoes(lerMedicoes(entradaDeTeste())['valores'], referenciasEscolares());
        $parecer = parecerAmostra($r);
        self::assertStringContainsString('estão nas faixas', $parecer);
        self::assertStringContainsString('Sem referência: Temperatura', $parecer);
        self::assertStringContainsString('não comprova potabilidade', $parecer);
    }
    public function testParecerListaParametrosForaDaFaixa(): void
    {
        $a = entradaDeTeste(); $a['ph_depois'] = '5'; $a['cloro_depois'] = '0';
        $r = compararMedicoes(lerMedicoes($a)['valores'], referenciasEscolares());
        self::assertStringContainsString('Fora das faixas adotadas: pH, Cloro residual', parecerAmostra($r));
    }
    public function testParecerSemReferenciaEListaVazia(): void
    {
        self::assertStringContainsString('Sem referência:', parecerAmostra(compararMedicoes(lerMedicoes(entradaDeTeste())['valores'])));
        self::assertStringContainsString('não comprova potabilidade', parecerAmostra([]));
    }
}
