<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ComparacaoTest extends TestCase
{
    private function comparar(array $entrada): array
    {
        return compararMedicoes(lerMedicoes($entrada)['valores']);
    }
    public function testCalculaReducaoDosTresParametros(): void
    {
        $r = $this->comparar(entradaDeTeste());
        self::assertSame(75.0, $r[1]['percentual']);
        self::assertSame(50.0, $r[2]['percentual']);
        self::assertEqualsWithDelta(20, $r[3]['percentual'], 0.000001);
        self::assertSame(-15.0, $r[1]['mudanca']);
        self::assertSame('diminuiu', $r[1]['direcao']);
    }
    public function testAumentoGeraPercentualNegativo(): void
    {
        $a = entradaDeTeste(); $a['turbidez_depois'] = '30'; $r = $this->comparar($a)[1];
        self::assertSame(-50.0, $r['percentual']);
        self::assertSame(10.0, $r['mudanca']);
        self::assertSame('aumentou', $r['direcao']);
    }
    public function testIgualdadeERemocaoCompleta(): void
    {
        $a = entradaDeTeste(); $a['turbidez_depois'] = '20'; $r = $this->comparar($a)[1];
        self::assertSame(0.0, $r['percentual']);
        self::assertSame('sem mudança', $r['direcao']);
        $a['turbidez_depois'] = '0';
        self::assertSame(100.0, $this->comparar($a)[1]['percentual']);
    }
    public function testInicialZeroNaoDividePorZero(): void
    {
        foreach (['0', '1'] as $final) {
            $a = entradaDeTeste(); $a['cloro_antes'] = '0'; $a['cloro_depois'] = $final;
            $r = $this->comparar($a)[2];
            self::assertNull($r['percentual']);
            self::assertSame('indefinida: inicial zero', $r['nota']);
        }
    }
    public function testPHETemperaturaNaoTemRemocaoPercentual(): void
    {
        $r = $this->comparar(entradaDeTeste());
        self::assertNull($r[0]['percentual']); self::assertNull($r[4]['percentual']);
        self::assertSame('não se aplica', $r[0]['nota']);
        self::assertSame(-0.5, $r[0]['mudanca']);
    }
    public function testEstouroNumericoNaoExibeInfinito(): void
    {
        $valores = lerMedicoes(entradaDeTeste())['valores'];
        $valores['cloro'] = ['antes' => 1.0e-300, 'depois' => 1.0e300];
        $r = compararMedicoes($valores)[2];
        self::assertNull($r['percentual']);
        self::assertSame('fora do alcance numérico', $r['nota']);
    }
}
