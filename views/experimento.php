<?php require_once __DIR__ . '/formatacao.php'; ?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro e avaliação da água</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="public/style.css">
</head>

<body>
    <main class="container py-4">
        <header class="pb-3 border-bottom">
            <h1 class="h2 fw-bold">Cadastro e avaliação da água</h1>
        </header>
        <section class="py-4 border-bottom" aria-labelledby="titulo-formulario">
            <h2 class="h5 fw-bold" id="titulo-formulario">Dados da amostra</h2>
            <p>Informe as medições antes e depois do filtro. Use ponto ou vírgula para decimais.</p>
            <?php if ($erros !== []): ?>
                <div class="border p-3 my-3" role="alert">
                    <h3 class="h6 fw-bold">Revise os campos indicados</h3>
                    <ul><?php foreach ($erros as $campo => $erro): ?>
                            <li><a href="#<?= escapar($campo) ?>"><?= escapar($erro) ?></a></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form class="mt-4" method="post" action="">
                <div class="mb-3">
                    <label class="form-label" for="nome">Nome da amostra (para cadastrar)</label>
                    <input class="form-control" id="nome" name="nome" maxlength="80"
                        value="<?= escapar(is_string($entrada['nome'] ?? null) ? $entrada['nome'] : '') ?>">
                </div>
                <?php foreach (parametros() as $chave => $regra): ?>
                    <fieldset class="row g-3 py-3 border-top m-0">
                        <legend class="col-12 col-sm-4 fs-6 fw-bold mb-0"><?= escapar($regra['nome']) ?><small
                                class="d-block fw-normal"><?= escapar($regra['unidade']) ?></small></legend>
                        <div class="col-12 col-sm-8">
                            <div class="row g-3">
                                <?php foreach (['antes' => 'Antes', 'depois' => 'Depois'] as $momento => $rotulo):
                                    $campo = $chave . '_' . $momento;
                                    $valor = is_string($entrada[$campo] ?? null) ? $entrada[$campo] : '';
                                    ?>
                                    <div class="col-6">
                                        <label class="form-label" for="<?= $campo ?>"><?= $rotulo ?> <span
                                                class="visually-hidden">— <?= escapar($regra['nome']) ?></span></label>
                                        <input class="form-control" id="<?= $campo ?>" name="<?= $campo ?>" type="text"
                                            inputmode="decimal" required value="<?= escapar($valor) ?>" <?= isset($erros[$campo]) ? 'aria-invalid="true" aria-describedby="erro-' . $campo . '"' : '' ?>>
                                        <?php if (isset($erros[$campo])): ?><small class="d-block small mt-1"
                                                id="erro-<?= $campo ?>"><?= escapar($erros[$campo]) ?></small><?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </fieldset>
                <?php endforeach; ?>
                <button class="btn btn-success mt-3" type="submit" name="acao" value="comparar">Avaliar amostra</button>
                <button class="btn btn-success mt-3" type="submit" name="acao" value="salvar">Cadastrar amostra</button>
            </form>
            <?php if ($mensagem !== ''): ?>
                <p role="status"><?= escapar($mensagem) ?></p><?php endif; ?>
        </section>
        <?php if ($resultados !== []): ?>
            <section class="py-4 border-bottom" aria-label="Avaliação da amostra">
                <p><strong>Avaliação após o filtro:</strong> <?= escapar($parecer) ?></p>
                <div class="table-responsive" role="region" aria-label="Resultados; role horizontalmente em telas pequenas"
                    tabindex="0">
                    <table class="table resultados">
                        <thead>
                            <tr>
                                <th scope="col">Parâmetro</th>
                                <th scope="col">Antes</th>
                                <th scope="col">Depois</th>
                                <th scope="col">Redução</th>
                                <th scope="col">Classificação antes → depois</th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($resultados as $linha): ?>
                                <tr>
                                    <th scope="row"><?= escapar($linha['nome']) ?><small
                                            class="d-block fw-normal"><?= escapar($linha['unidade']) ?></small></th>
                                    <td><?= numeroVisivel($linha['antes']) ?></td>
                                    <td><?= numeroVisivel($linha['depois']) ?></td>
                                    <td><?= $linha['percentual'] === null ? escapar($linha['nota']) : numeroVisivel($linha['percentual']) . '%' ?>
                                    </td>
                                    <td><?= escapar($linha['classeAntes']) ?> → <?= escapar($linha['classeDepois']) ?></td>
                                </tr><?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
        <section class="py-4 border-bottom" aria-labelledby="titulo-amostras">
            <h2 class="h5 fw-bold" id="titulo-amostras">Amostras cadastradas</h2>
            <?php if ($amostras === []): ?>
                <p>Nenhuma amostra cadastrada.</p>
            <?php else:
                foreach ($amostras as $amostra): ?>
                    <details class="my-3">
                        <summary class="fw-bold py-2"><?= escapar($amostra['nome']) ?> · amostra <?= (int) $amostra['id'] ?>
                        </summary>
                        <?php $linhasSalvas = compararMedicoes($amostra['medicoes'], referenciasEscolares()); ?>
                        <p><?= escapar(parecerAmostra($linhasSalvas)) ?></p>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Parâmetro</th>
                                        <th>Antes</th>
                                        <th>Depois</th>
                                        <th>Após o filtro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($linhasSalvas as $linha): ?>
                                        <tr>
                                            <th><?= escapar($linha['nome']) ?><small
                                                    class="d-block fw-normal"><?= escapar($linha['unidade']) ?></small></th>
                                            <td><?= numeroVisivel($linha['antes']) ?></td>
                                            <td><?= numeroVisivel($linha['depois']) ?></td>
                                            <td><?= escapar($linha['classeDepois']) ?></td>
                                        </tr><?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </details>
                <?php endforeach; endif; ?>
        </section>
        <footer class="py-4">
            <p class="small"><strong>Potabilidade não comprovada:</strong> esses parâmetros não avaliam todos os
                contaminantes químicos e microbiológicos. Não use a amostra para consumo.</p>
            <details class="my-3">
                <summary class="fw-bold py-2">Critérios de comparação</summary>
                <p class="small">pH 6,0–9,5 (faixa operacional do <a
                        href="https://saaec.com.br/agua/qualidade-da-agua/">SAAE Cerquilho</a>); turbidez até 5 NTU,
                    cloro livre 0,2–5 mg/L e dureza até 300 mg/L de CaCO₃ (recorte da <a
                        href="https://bvsms.saude.gov.br/bvs/saudelegis/gm/2021/prt0888_07_05_2021.html">Portaria
                        888/2021</a> para água em distribuição). Temperatura: sem referência. Redução percentual =
                    ((antes − depois) / antes) × 100; redução não significa sempre melhoria.</p>
            </details>
        </footer>
    </main>
</body>

</html>