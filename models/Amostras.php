<?php
declare(strict_types=1);

final class Amostras
{

    public function __construct(private string $arquivo)
    {
    }


    public function salvar(string $nome, array $medicoes): int
    {
        $nome = trim($nome);
        if ($nome === '' || mb_strlen($nome) > 80) {
            throw new InvalidArgumentException('Nome da amostra: use de 1 a 80 caracteres.');
        }
        $arquivo = fopen($this->arquivo, 'c+');
        if ($arquivo === false) {
            throw new RuntimeException('Não foi possível abrir o arquivo de amostras.');
        }
        try {

            if (!flock($arquivo, LOCK_EX)) {
                throw new RuntimeException('Não foi possível salvar a amostra.');
            }
            $amostras = $this->decodificar((string) stream_get_contents($arquivo));
            $id = $amostras === [] ? 1 : max(array_column($amostras, 'id')) + 1;
            $amostras[] = [
                'id' => $id,
                'nome' => $nome,
                'medicoes' => $medicoes,
                'registrada_em' => date('c'),
            ];
            $json = json_encode($amostras, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            rewind($arquivo);
            if (!ftruncate($arquivo, 0) || fwrite($arquivo, $json) !== strlen($json)) {
                throw new RuntimeException('Não foi possível gravar as amostras.');
            }
            return $id;
        } finally {
            fclose($arquivo);
        }
    }

    public function listar(): array
    {
        if (!is_file($this->arquivo)) {
            return [];
        }
        $arquivo = fopen($this->arquivo, 'r');
        if ($arquivo === false) {
            throw new RuntimeException('Não foi possível ler as amostras.');
        }
        try {
            if (!flock($arquivo, LOCK_SH)) {
                throw new RuntimeException('Não foi possível ler as amostras.');
            }
            return array_reverse($this->decodificar((string) stream_get_contents($arquivo)));
        } finally {
            fclose($arquivo);
        }
    }

    private function decodificar(string $conteudo): array
    {
        if (trim($conteudo) === '') {
            return [];
        }
        $amostras = json_decode($conteudo, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($amostras) || !array_is_list($amostras)) {
            throw new RuntimeException('Arquivo de amostras inválido.');
        }
        return $amostras;
    }
}

