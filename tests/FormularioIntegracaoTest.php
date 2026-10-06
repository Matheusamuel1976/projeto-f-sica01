<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class FormularioIntegracaoTest extends TestCase
{
    private function pagina(string $metodo, array $dados = []): string
    {
        $servidorAnterior = $_SERVER; $postAnterior = $_POST;
        $_SERVER['REQUEST_METHOD'] = $metodo; $_POST = $dados;
        $arquivoAmostras = tempnam(sys_get_temp_dir(), 'jacinto-form-');
        ob_start();
        try {
            $repositorioTeste = new Amostras($arquivoAmostras);
            require __DIR__ . '/../index.php';
            return (string) ob_get_contents();
        } finally {
            unlink($arquivoAmostras); ob_end_clean(); $_SERVER = $servidorAnterior; $_POST = $postAnterior;
        }
    }
    public function testGetMostraFormularioSemTabela(): void
    {
        $html = $this->pagina('GET');
        self::assertSame(10, substr_count($html, 'type="text"'));
        self::assertStringContainsString('Dados da amostra', $html);
        self::assertStringNotContainsString('<table class="table resultados">', $html);
    }
    public function testPostIntegraValidacaoCalculoEView(): void
    {
        $html = $this->pagina('POST', entradaDeTeste());
        self::assertStringContainsString('<table class="table resultados">', $html);
        self::assertStringContainsString('75%', $html);
        self::assertStringContainsString('na faixa → na faixa', $html);
        self::assertStringContainsString('não comprova potabilidade', $html);
    }
    public function testErroPreservaValoresEEscapaHtml(): void
    {
        $dados = entradaDeTeste(); $dados['ph_antes'] = '<script>alert(1)</script>';
        unset($dados['cloro_depois']); $html = $this->pagina('POST', $dados);
        self::assertStringContainsString('Revise os campos indicados', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('value="100"', $html);
        self::assertStringNotContainsString('<table class="table resultados">', $html);
    }
    public function testCadastroPersisteNoRepositorio(): void
    {
        $dados = entradaDeTeste(); $dados['acao'] = 'salvar'; $dados['nome'] = 'Ensaio da turma';
        $html = $this->pagina('POST', $dados);
        self::assertStringContainsString('Amostra cadastrada', $html);
        self::assertStringContainsString('Ensaio da turma · amostra 1', $html);
    }
    public function testCadastroRecusaNomeVazio(): void
    {
        $dados = entradaDeTeste(); $dados['acao'] = 'salvar';
        self::assertStringContainsString('Nome da amostra:', $this->pagina('POST', $dados));
    }
    public function testPaginaTemApenasMedicoesAvaliacaoECadastro(): void
    {
        $html = $this->pagina('GET');
        self::assertStringContainsString('Cadastrar amostra', $html);
        self::assertStringNotContainsString('Leitura do experimento', $html);
        self::assertStringNotContainsString('Simular camadas', $html);
        self::assertStringNotContainsString('Observações reais para estudar', $html);
    }
}
