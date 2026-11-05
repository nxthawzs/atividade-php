<?php
include 'conexao.php';
session_start();
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) ?: 0;
$produto = ["codigo"=>"", "nome"=>"", "descricao"=>"", "preco"=>"", "estoque"=>""];
$titulo = "Cadastrar produto";
if ($id > 0) {
    $sql = mysqli_prepare($conexao, "SELECT codigo, nome, descricao, preco, estoque FROM produtos WHERE id=?");
    mysqli_stmt_bind_param($sql, "i", $id);
    mysqli_stmt_execute($sql);
    $achou = mysqli_stmt_get_result($sql);
    if (mysqli_num_rows($achou) === 0) { $_SESSION["mensagem"] = "Produto não encontrado."; header("Location: index.php"); exit; }
    $produto = mysqli_fetch_assoc($achou);
    $titulo = "Editar produto";
}
$erro = $_SESSION["erro"] ?? "";
unset($_SESSION["erro"]);
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= $titulo ?> | Estoque Fácil</title><link rel="stylesheet" href="assets/style.css"></head>
<body><header class="topbar"><a class="brand" href="index.php"><span class="brand-icon">◆</span> Estoque Fácil</a><span class="topbar-note">Cadastro de produtos</span></header>
<main class="container narrow"><a class="back-link" href="index.php">← Voltar aos produtos</a><section class="panel form-panel"><p class="eyebrow">CATÁLOGO</p><h1><?= $titulo ?></h1><p class="subtitle">Preencha os campos abaixo.</p>
<?php if ($erro !== ""): ?><div class="notice notice-error"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<form action="salvar_produto.php" method="post" class="product-form" id="productForm"><input type="hidden" name="id" value="<?= $id ?>">
<label>Código do produto<input name="codigo" required maxlength="40" value="<?= htmlspecialchars($produto["codigo"]) ?>" placeholder="Ex.: PROD001"></label>
<label>Nome do produto<input name="nome" required maxlength="120" value="<?= htmlspecialchars($produto["nome"]) ?>" placeholder="Ex.: Teclado USB"></label>
<label class="full">Descrição<textarea name="descricao" rows="3" maxlength="500" placeholder="Descrição opcional"><?= htmlspecialchars($produto["descricao"] ?? "") ?></textarea></label>
<label>Preço (R$)<input type="number" name="preco" min="0.01" step="0.01" required value="<?= htmlspecialchars((string)$produto["preco"]) ?>" placeholder="0,00"></label>
<label>Quantidade em estoque<input type="number" name="estoque" min="0" step="1" required value="<?= htmlspecialchars((string)$produto["estoque"]) ?>" placeholder="0"></label>
<div class="form-actions full"><a class="button button-light" href="index.php">Cancelar</a><button class="button button-primary" type="submit">Salvar produto</button></div></form></section></main><script src="assets/script.js"></script></body></html>
