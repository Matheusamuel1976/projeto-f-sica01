<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ValidacaoTest extends TestCase
{
    public function testAceitaMedicoesEDecimalComVirgula(): void
    {
        $a = entradaDeTeste(); $a['ph_antes'] = ' 7,2 ';
        $r = lerMedicoes($a);
        self::assertSame([], $r['erros']);
        self::assertSame(7.2, $r['valores']['ph']['antes']);
    }
    public function testDetectaAusenteEBranco(): void
    {
        $a = entradaDeTeste(); unset($a['ph_antes']); $a['cloro_depois'] = ' ';
        self::assertSame(['ph_antes', 'cloro_depois'], array_keys(lerMedicoes($a)['erros']));
    }
    public function testRecusaTextoArrayEInfinito(): void
    {
        $a = entradaDeTeste(); $a['ph_antes'] = 'abc'; $a['cloro_antes'] = ['1'];
        $a['dureza_depois'] = str_repeat('9', 400);
        self::assertCount(3, lerMedicoes($a)['erros']);
    }
    public function testRecusaNegativosEPHForaDoDominio(): void
    {
        $a = entradaDeTeste(); $a['cloro_antes'] = '-1'; $a['ph_antes'] = '14.1'; $a['ph_depois'] = '-0.1';
        self::assertCount(3, lerMedicoes($a)['erros']);
    }
    public function testAceitaBordasDoPHEValidaZeroAbsoluto(): void
    {
        $a = entradaDeTeste(); $a['ph_antes'] = '0'; $a['ph_depois'] = '14'; $a['temperatura_antes'] = '-273.15';
        self::assertSame([], lerMedicoes($a)['erros']);
        $a['temperatura_depois'] = '-274';
        self::assertSame(['temperatura_depois'], array_keys(lerMedicoes($a)['erros']));
    }
}
