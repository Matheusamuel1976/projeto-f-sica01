# Qualidade da água e eficiência de um biofiltro

Projeto escolar para cadastrar amostras, comparar medições antes e depois de um filtro e classificar parâmetros da água. Desenvolvido em PHP, HTML5, CSS3 e Bootstrap, com testes PHPUnit e armazenamento em JSON, sem banco de dados.

A avaliação é parcial: os parâmetros analisados não comprovam potabilidade nem substituem análises químicas e microbiológicas completas.

## Requisitos

- PHP **8.4 ou superior**, conforme o enunciado da atividade. O `composer.json` atualmente permite PHP 8.3+, mas isso não substitui o requisito da entrega.
- Composer e Laravel Herd para executar o projeto localmente.
- Extensões PHP exigidas pelo Composer/PHPUnit, além de `mbstring` para validar o nome das amostras. Verifique com `composer check-platform-reqs` após instalar.
- Xdebug com cobertura habilitada ou PCOV para medir cobertura de testes.
- Acesso à internet para instalar as dependências e carregar o CSS do Bootstrap pela CDN.

## Instalação e execução pelo Laravel Herd

1. Instale o [Laravel Herd para Windows](https://herd.laravel.com/windows) e selecione PHP 8.4 ou superior. O projeto usa PHP diretamente; não exige a instalação do framework Laravel.
2. Abra um terminal na pasta que contém `index.php` e `composer.json`.
3. No gerenciador de sites do Herd, vincule esta pasta como um projeto existente e selecione a versão de PHP do site. Use a raiz do projeto como diretório de entrada: o `index.php` está nela.
4. Confirme que o PHP do terminal também atende ao requisito e instale as dependências:

```sh
php -v
composer install
composer check-platform-reqs
```

5. Abra o endereço `.test` informado pelo Herd para o site.

A documentação oficial explica como [vincular projetos existentes](https://latest.herdphp.com/docs/windows/1/getting-started/sites) e [selecionar versões do PHP](https://latest.herdphp.com/docs/windows/1/advanced-usage/php-versions). Se o terminal mostrar outra versão, ajuste o PHP utilizado antes de executar os testes.

Como alternativa para uma verificação local, na raiz do projeto:

```sh
php -S 127.0.0.1:8000
```

Abra [http://127.0.0.1:8000](http://127.0.0.1:8000). Essa alternativa não comprova execução pelo Herd.

## Uso do sistema

1. Preencha os valores antes e depois do filtro para pH, turbidez, cloro residual livre, dureza e temperatura. Os dez campos são obrigatórios e aceitam ponto ou vírgula decimal.
2. Clique em **Avaliar amostra** para visualizar as classificações, os percentuais de redução aplicáveis e o parecer final.
3. Para armazenar as medições, informe um nome de 1 a 80 caracteres e clique em **Cadastrar amostra**.
4. Consulte as medições salvas na seção **Amostras cadastradas**.

As amostras são gravadas em `data/amostras.json`, criado no primeiro cadastro. A pasta `data/` deve permitir escrita pelo PHP. Esse arquivo está no `.gitignore`; faça uma cópia de segurança se precisar preservar os registros. Os testes usam arquivos temporários e não cadastram suas medições no sistema.

## Algoritmos implementados

| Algoritmo | Arquivo / função | Comportamento |
| --- | --- | --- |
| Validação das medições | `models/Medicoes.php` — `lerMedicoes()` | Rejeita campos ausentes, texto, arrays, números não finitos e valores fora do domínio adotado. Converte vírgula decimal em ponto. |
| Classificação por faixa | `models/Medicoes.php` — `classificarFaixa()` | Retorna “abaixo da faixa”, “na faixa”, “acima da faixa” ou “sem referência”. Os limites são inclusivos. |
| Comparação e eficiência | `models/Medicoes.php` — `compararMedicoes()` | Calcula a diferença depois − antes, a direção da mudança e a redução percentual por parâmetro. |
| Faixas de referência | `models/Analise.php` — `referenciasEscolares()` | Define os critérios escolares utilizados na classificação. |
| Parecer integrado | `models/Analise.php` — `parecerAmostra()` | Lista os parâmetros finais fora da faixa e aqueles sem referência, mantendo a indicação de avaliação parcial. |
| Persistência das amostras | `models/Amostras.php` — classe `Amostras` | Valida o nome, atribui identificadores, salva e lista registros JSON com bloqueio de arquivo. |

### Referências e domínios

| Parâmetro | Faixa adotada na classificação | Domínio aceito na entrada |
| --- | --- | --- |
| pH | 6,0 a 9,5 | 0 a 14 |
| Turbidez | 0 a 5 NTU | Valor não negativo |
| Cloro residual livre | 0,2 a 5 mg/L | Valor não negativo |
| Dureza | 0 a 300 mg/L de CaCO₃ | Valor não negativo |
| Temperatura | Sem faixa de referência | A partir de −273,15 °C |

As faixas de turbidez, cloro e dureza são um recorte da [Portaria GM/MS nº 888/2021](https://bvsms.saude.gov.br/bvs/saudelegis/gm/2021/prt0888_07_05_2021.html) para água em distribuição: mínimo de cloro no art. 32 e valores máximos nas tabelas de padrão químico e organoléptico. A faixa de pH é a referência operacional apresentada pelo [SAAE Cerquilho](https://saaec.com.br/agua/qualidade-da-agua/). Fontes consultadas em 06/10/2026.

Esses critérios não representam todas as exigências legais nem os limites específicos de cada etapa do tratamento. Temperatura é comparada entre as medições, mas permanece “sem referência”. Os domínios de entrada são restrições do algoritmo e não garantem condições adequadas para consumo.

### Cálculo da eficiência do biofiltro

O sistema avalia a remoção observada **por parâmetro**, comparando as medições antes e depois:

```text
redução (%) = ((antes − depois) / antes) × 100
```

O percentual é calculado para turbidez, cloro e dureza. Resultados positivos indicam redução; zero indica igualdade; negativos indicam aumento. Quando o valor inicial é zero, a taxa é apresentada como indefinida, evitando divisão por zero. Se o cálculo exceder o alcance numérico, o percentual também não é exibido.

pH e temperatura usam a diferença absoluta, sem percentual de remoção. Não há simulação de camadas do filtro. Uma redução de cloro, por exemplo, pode deixar a água abaixo da faixa adotada; por isso, redução não é automaticamente melhoria.

## Testes unitários e integração

Na raiz do projeto, após `composer install`:

```sh
composer test
```

Para gerar um registro de execução sem códigos de cor:

```sh
php vendor/phpunit/phpunit/phpunit --colors=never > reports/execucao.txt
```

| Classe de teste | Verificações principais |
| --- | --- |
| `ValidacaoTest` | Campos ausentes, formatos inválidos, valores impossíveis, vírgula decimal e bordas do domínio. |
| `ClassificacaoTest` | Faixas inclusivas, valores abaixo/acima e ausência de referência. |
| `ReferenciasTest` | Limites das faixas adotadas, incluindo pH 6,0 e 9,5. |
| `ComparacaoTest` | Reduções conhecidas, aumento, igualdade, remoção completa, inicial zero e estouro numérico. |
| `ParecerTest` | Integração entre classificação dos parâmetros e parecer final. |
| `AmostrasTest` | Cadastro, nomes inválidos, ordem de listagem e persistência. |
| `FormularioIntegracaoTest` | Formulário, avaliação, cadastro, mensagens de erro e escape de HTML. |

Exemplos matemáticos usados nos testes: turbidez de 20 para 5 resulta em 75% de redução; cloro de 2 para 1, em 50%; dureza de 100 para 80, em 20%. São entradas sintéticas de testes, não dados coletados nem resultados experimentais.

Na verificação de 06/10/2026, passaram **27 testes e 92 verificações**, com PHPUnit 12.5.38 em PHP 8.3.33. Essa execução não comprova validação em PHP 8.4+ ou pelo Herd.

### Cobertura mínima de 80%

Instale e habilite Xdebug ou PCOV no **PHP utilizado pelo terminal**. Depois execute:

```sh
composer coverage
```

O comando executa os testes, gera `reports/coverage/index.html` e `reports/clover.xml`, e verifica o mínimo de 80% de linhas executáveis. A configuração atual inclui `models/` e `controllers/`, deixando a interface fora da medição. Para conferir novamente um relatório já gerado:

```sh
php tests/verificar-cobertura.php
```

Se aparecer `No code coverage driver available`, a extensão não está disponível no PHP utilizado; confira `php --ini` e `php -m`. Na revisão de 06/10/2026, não foi possível medir cobertura por ausência do driver. Portanto, **a cobertura de 80% ainda não está comprovada**.

## Estrutura do projeto

```text
index.php              Entrada da aplicação
controllers/           Processamento do formulário
models/                Validação, classificação, cálculos e persistência
views/                 Página HTML e formatação da saída
public/                Estilos CSS
data/                  Armazenamento local de amostras
tests/                 Testes PHPUnit e verificação de cobertura
reports/               Registro de execução e relatórios de cobertura
phpunit.xml            Configuração da suíte e arquivos medidos
composer.json          Dependências e comandos de testes
```

## Relação com Química, Biologia e ODS 6

O pH permite discutir acidez e basicidade; a dureza está associada principalmente a íons de cálcio e magnésio; a turbidez permite acompanhar alterações nas partículas suspensas; e o cloro residual está relacionado à desinfecção. A comparação antes/depois ajuda a discutir os efeitos e os limites do filtro.

A ausência de testes microbiológicos impede concluir se microrganismos foram removidos. Essa limitação conecta a análise química à Biologia e ao [ODS 6 — Água potável e saneamento](https://brasil.un.org/pt-br/sdgs/6). O relatório técnico entregue separadamente deve apresentar a discussão dos resultados experimentais.

