<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ClassificacaoTest extends TestCase
{
    public function testSemCriterioMostraSemReferencia(): void
    {
        foreach (compararMedicoes(lerMedicoes(entradaDeTeste())['valores']) as $r) {
            self::assertSame('sem referência', $r['classeAntes']);
            self::assertSame('sem referência', $r['classeDepois']);
        }
    }
    public function testFaixaInclusivaAbaixoEAcima(): void
    {
        
        $faixa = ['min' => 2, 'max' => 4];
        self::assertSame('na faixa', classificarFaixa(2, $faixa));
        self::assertSame('na faixa', classificarFaixa(4, $faixa));
        self::assertSame('abaixo da faixa', classificarFaixa(1, $faixa));
        self::assertSame('acima da faixa', classificarFaixa(5, $faixa));
    }
    public function testReducaoPodeSairDaFaixa(): void
    {
        $a = entradaDeTeste(); $a['cloro_depois'] = '0.5';
        $r = compararMedicoes(lerMedicoes($a)['valores'], ['cloro' => ['min' => 1, 'max' => 3]])[2];
        self::assertSame('na faixa', $r['classeAntes']);
        self::assertSame('abaixo da faixa', $r['classeDepois']);
        self::assertSame(75.0, $r['percentual']);
    }
}
