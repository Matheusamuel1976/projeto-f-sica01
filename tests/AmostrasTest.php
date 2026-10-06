<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class AmostrasTest extends TestCase
{
    private string $arquivo;
    protected function setUp(): void { $this->arquivo = tempnam(sys_get_temp_dir(), 'jacinto-json-'); }
    protected function tearDown(): void { if (is_file($this->arquivo)) unlink($this->arquivo); }
    public function testCadastroListagemEOrdem(): void
    {
        $repo = new Amostras($this->arquivo); $medicoes = lerMedicoes(entradaDeTeste())['valores'];
        self::assertSame([], $repo->listar());
        self::assertSame(1, $repo->salvar('Primeira', $medicoes));
        $repo->salvar("Segunda ' amostra", $medicoes);
        $lista = $repo->listar();
        self::assertCount(2, $lista); self::assertSame("Segunda ' amostra", $lista[0]['nome']);
        self::assertEquals($medicoes, $lista[0]['medicoes']);
    }
    public function testNomeVazioOuLongo(): void
    {
        $repo = new Amostras($this->arquivo);
        foreach ([' ', str_repeat('a', 81)] as $nome) {
            try { $repo->salvar($nome, []); self::fail('Deveria recusar'); }
            catch (InvalidArgumentException $erro) { self::assertStringContainsString('Nome da amostra', $erro->getMessage()); }
        }
    }
    public function testPersistenciaEntreLeituras(): void
    {
        $arquivo = tempnam(sys_get_temp_dir(), 'jacinto-teste-');
        try {
            $repo = new Amostras($arquivo); $repo->salvar('Persistida', lerMedicoes(entradaDeTeste())['valores']);
            unset($repo); $outro = new Amostras($arquivo);
            self::assertSame('Persistida', $outro->listar()[0]['nome']);
            $json = json_decode(file_get_contents($arquivo), true, 512, JSON_THROW_ON_ERROR);
            self::assertSame('Persistida', $json[0]['nome']); unset($outro);
        } finally { unlink($arquivo); }
    }
}
