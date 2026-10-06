<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ReferenciasTest extends TestCase
{
    public function testClassificaBordasDosCriteriosAdotados(): void
    {
        $refs = referenciasEscolares();
        foreach (['ph', 'turbidez', 'cloro', 'dureza'] as $chave) {
            self::assertSame('na faixa', classificarFaixa($refs[$chave]['min'], $refs[$chave]));
            self::assertSame('na faixa', classificarFaixa($refs[$chave]['max'], $refs[$chave]));
            self::assertSame('acima da faixa', classificarFaixa($refs[$chave]['max'] + 0.01, $refs[$chave]));
        }
        self::assertSame('sem referência', classificarFaixa(25, $refs['temperatura']));
    }
}
