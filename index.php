<?php
include 'conexao.php';

if (session_id() == '') {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';

    if ($acao == 'cadastrar') {
        $descricao = trim(isset($_POST['descricao']) ? $_POST['descricao'] : '');
        $categoria = trim(isset($_POST['categoria']) ? $_POST['categoria'] : '');
        $compraTexto = isset($_POST['valor_compra']) ? $_POST['valor_compra'] : '0';
        $vendaTexto = isset($_POST['valor_venda']) ? $_POST['valor_venda'] : '0';

        $compra = (float)str_replace(',', '.', $compraTexto);
        $venda = (float)str_replace(',', '.', $vendaTexto);
        $estoque = isset($_POST['estoque']) ? (int)$_POST['estoque'] : -1;

        if ($descricao == '' || $categoria == '' || $compra < 0 || $venda <= 0 || $estoque < 0) {
            $_SESSION['mensagem'] = 'Preencha os campos corretamente.';
            $_SESSION['tipo_mensagem'] = 'erro';
        } else {
            $d = mysqli_real_escape_string($conexao, $descricao);
            $c = mysqli_real_escape_string($conexao, $categoria);

            $sql = "INSERT INTO tb_produtos
            (descricao, categoria, valor_compra, valor_venda, estoque)
            VALUES ('$d', '$c', $compra, $venda, $estoque)";

            if (mysqli_query($conexao, $sql)) {
                $_SESSION['mensagem'] = 'Produto cadastrado com sucesso!';
                $_SESSION['tipo_mensagem'] = 'sucesso';
            } else {
                $_SESSION['mensagem'] = 'Erro ao cadastrar produto: ' . mysqli_error($conexao);
                $_SESSION['tipo_mensagem'] = 'erro';
            }
        }

        header('Location: index.php');
        exit;
    }

    if ($acao == 'vender') {
        $id = isset($_POST['produto_id']) ? (int)$_POST['produto_id'] : 0;
        $quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 0;

        if ($id < 1 || $quantidade < 1) {
            $_SESSION['mensagem'] = 'Selecione um produto e informe uma quantidade válida.';
            $_SESSION['tipo_mensagem'] = 'erro';
            header('Location: index.php');
            exit;
        }

        mysqli_query($conexao, 'START TRANSACTION');

        $consulta = mysqli_query($conexao,
            "SELECT id, descricao, valor_venda, estoque
            FROM tb_produtos WHERE id=$id FOR UPDATE"
        );

        $produto = $consulta ? mysqli_fetch_assoc($consulta) : false;

        if (!$produto) {
            mysqli_query($conexao, 'ROLLBACK');
            $_SESSION['mensagem'] = 'Produto não encontrado.';
            $_SESSION['tipo_mensagem'] = 'erro';

        } elseif ((int)$produto['estoque'] < $quantidade) {
            mysqli_query($conexao, 'ROLLBACK');
            $_SESSION['mensagem'] = 'Estoque insuficiente.';
            $_SESSION['tipo_mensagem'] = 'erro';

        } else {
            $preco = (float)$produto['valor_venda'];
            $total = round($preco * $quantidade, 2);

            $ok1 = mysqli_query($conexao,
                "INSERT INTO tb_vendas
                (produto_id, quantidade, valor_unitario, total)
                VALUES ($id, $quantidade, $preco, $total)"
            );

            $ok2 = mysqli_query($conexao,
                "UPDATE tb_produtos
                SET estoque=estoque-$quantidade
                WHERE id=$id AND estoque >= $quantidade"
            );

            if ($ok1 && $ok2 && mysqli_affected_rows($conexao) == 1) {
                mysqli_query($conexao, 'COMMIT');
                $_SESSION['mensagem'] = 'Venda registrada! Total: R$ ' . number_format($total, 2, ',', '.');
                $_SESSION['tipo_mensagem'] = 'sucesso';
            } else {
                mysqli_query($conexao, 'ROLLBACK');
                $_SESSION['mensagem'] = 'Não foi possível registrar a venda: ' . mysqli_error($conexao);
                $_SESSION['tipo_mensagem'] = 'erro';
            }
        }

        header('Location: index.php');
        exit;
    }

    if ($acao == 'excluir') {
        $id = isset($_POST['produto_id']) ? (int)$_POST['produto_id'] : 0;

        if ($id > 0 && mysqli_query($conexao, "DELETE FROM tb_produtos WHERE id=$id")) {
            $_SESSION['mensagem'] = 'Produto excluído.';
            $_SESSION['tipo_mensagem'] = 'sucesso';
        } else {
            $_SESSION['mensagem'] = 'Não foi possível excluir. Verifique se o produto já possui vendas.';
            $_SESSION['tipo_mensagem'] = 'erro';
        }

        header('Location: index.php');
        exit;
    }
}

$busca = trim(isset($_GET['busca']) ? $_GET['busca'] : '');
$filtro = mysqli_real_escape_string($conexao, $busca);

$sqlProdutos = "SELECT * FROM tb_produtos
WHERE descricao LIKE '%$filtro%'
OR categoria LIKE '%$filtro%'
ORDER BY descricao";

$resultado = mysqli_query($conexao, $sqlProdutos);

if (!$resultado) {
    die('Erro ao consultar produtos: ' . mysqli_error($conexao));
}

$opcoes = mysqli_query($conexao,
    'SELECT id, descricao, categoria, estoque
    FROM tb_produtos ORDER BY descricao'
);

if (!$opcoes) {
    die('Erro ao carregar produtos: ' . mysqli_error($conexao));
}

$mensagem = isset($_SESSION['mensagem']) ? $_SESSION['mensagem'] : '';
$tipoMensagem = isset($_SESSION['tipo_mensagem']) ? $_SESSION['tipo_mensagem'] : 'sucesso';

unset($_SESSION['mensagem']);
unset($_SESSION['tipo_mensagem']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Produtos</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<main class="pagina">

    <section class="sistema">

        <div class="titulo-secao">
            <span>📦</span>
            <h1>Cadastro de Produto</h1>
        </div>

        <?php if ($mensagem != '') { ?>
            <div class="mensagem <?php echo $tipoMensagem == 'erro' ? 'mensagem-erro' : ''; ?>">
                <?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php } ?>

        <form method="post" action="index.php" id="formCadastro" class="formulario-cadastro">

            <input type="hidden" name="acao" value="cadastrar">

            <label class="campo">
                <span>Descrição</span>
                <input type="text" name="descricao" required placeholder="Notebook Samsung">
            </label>

            <label class="campo">
                <span>Categoria</span>
                <select name="categoria" required>
                    <option value="">Selecione</option>
                    <option>Informática</option>
                    <option>Acessórios</option>
                    <option>Eletrônicos</option>
                    <option>Escritório</option>
                    <option>Outros</option>
                </select>
            </label>

            <label class="campo">
                <span>Valor Compra (R$)</span>
                <input type="number" name="valor_compra" min="0" step="0.01" required>
            </label>

            <label class="campo">
                <span>Valor Venda (R$)</span>
                <input type="number" name="valor_venda" min="0.01" step="0.01" required>
            </label>

            <label class="campo campo-estoque">
                <span>Qtd. Estoque</span>
                <input type="number" name="estoque" min="0" required>
            </label>

            <div class="botoes-formulario">
                <button type="submit" class="botao botao-azul">
                    Cadastrar Produto
                </button>

                <button type="button" class="botao botao-azul" id="abrirVenda">
                    Vender Produto
                </button>
            </div>

        </form>

        <div class="modal-fundo" id="modalVenda" hidden>

            <div class="modal-venda">

                <button type="button" class="fechar-modal" id="fecharVenda">×</button>

                <h2>Vender Produto</h2>

                <form method="post" action="index.php" id="formVenda">

                    <input type="hidden" name="acao" value="vender">

                    <label class="campo campo-modal">
                        <span>Produto</span>

                        <select name="produto_id" id="produtoVenda" required>
                            <option value="">Selecione um produto</option>

                            <?php while ($op = mysqli_fetch_assoc($opcoes)) { ?>
                                <option
                                    value="<?php echo (int)$op['id']; ?>"
                                    data-estoque="<?php echo (int)$op['estoque']; ?>">

                                    <?php echo htmlspecialchars(
                                        $op['descricao'] . ' - ' .
                                        $op['categoria'] . ' (estoque: ' .
                                        $op['estoque'] . ')',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </option>
                            <?php } ?>

                        </select>
                    </label>

                    <label class="campo campo-modal">
                        <span>Quantidade a vender</span>
                        <input type="number" name="quantidade" id="quantidadeVenda" min="1" value="1" required>
                    </label>

                    <div class="botoes-modal">

                        <button type="submit" class="botao botao-azul">
                            Confirmar Venda
                        </button>

                        <button type="button" class="botao botao-cinza" id="cancelarVenda">
                            Cancelar
                        </button>

                    </div>

                    <p class="ajuda-modal" id="ajudaEstoque">
                        Selecione um produto para consultar o estoque disponível.
                    </p>

                </form>
            </div>
        </div>

        <div class="titulo-inventario">

            <div class="nome-inventario">
                <span>📋</span>
                <h2>Inventário</h2>
            </div>

            <form method="get" action="index.php" class="formulario-busca">

                <input
                    type="search"
                    name="busca"
                    value="<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="Pesquisar por descrição ou categoria">

                <button type="submit" class="botao-busca">
                    ⌕
                </button>

            </form>

        </div>

        <div class="tabela-responsiva">

            <table>

                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Venda</th>
                        <th>Lucro Unit.</th>
                        <th>Estoque</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (mysqli_num_rows($resultado) == 0) { ?>

                        <tr>
                            <td colspan="6" class="vazio">
                                Nenhum produto encontrado.
                            </td>
                        </tr>

                    <?php } else { ?>

                        <?php while ($p = mysqli_fetch_assoc($resultado)) { ?>

                            <tr>

                                <td><?php echo htmlspecialchars($p['descricao'], ENT_QUOTES, 'UTF-8'); ?></td>

                                <td><?php echo htmlspecialchars($p['categoria'], ENT_QUOTES, 'UTF-8'); ?></td>

                                <td>
                                    R$ <?php echo number_format((float)$p['valor_venda'], 2, ',', '.'); ?>
                                </td>

                                <td>
                                    R$ <?php echo number_format(
                                        (float)$p['valor_venda'] - (float)$p['valor_compra'],
                                        2,
                                        ',',
                                        '.'
                                    ); ?>
                                </td>

                                <td><?php echo (int)$p['estoque']; ?></td>

                                <td>
                                    <form method="post" action="index.php"
                                        onsubmit="return confirm('Deseja excluir este produto?');">

                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="produto_id" value="<?php echo (int)$p['id']; ?>">

                                        <button type="submit" class="botao-excluir">
                                            ×
                                        </button>

                                    </form>
                                </td>

                            </tr>

                        <?php } ?>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<script src="assets/script.js"></script>

</body>
</html>