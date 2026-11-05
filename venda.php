<?php
include 'conexao.php';
session_start();
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) ?: 0;
$resultado = mysqli_query($conexao, "SELECT id, codigo, nome, preco, estoque FROM produtos ORDER BY nome");
$produtos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
$erro = $_SESSION["erro_venda"] ?? "";
unset($_SESSION["erro_venda"]);
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Registrar venda | Estoque Fácil</title><link rel="stylesheet" href="assets/style.css"></head>
<body><header class="topbar"><a class="brand" href="index.php"><span class="brand-icon">◆</span> Estoque Fácil</a><span class="topbar-note">Registro de venda</span></header>
<main class="container narrow"><a class="back-link" href="index.php">← Voltar aos produtos</a><section class="panel form-panel"><p class="eyebrow">MOVIMENTAÇÃO</p><h1>Registrar venda</h1><p class="subtitle">A quantidade vendida será descontada do estoque automaticamente.</p>
<?php if ($erro !== ""): ?><div class="notice notice-error"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
<?php if (count($produtos) === 0): ?><p>Cadastre pelo menos um produto antes de registrar uma venda.</p><?php else: ?>
<form action="registrar_venda.php" method="post" class="product-form" id="saleForm"><label class="full">Produto<select name="produto_id" id="produto_id" required><option value="">Selecione um produto</option>
<?php foreach ($produtos as $p): ?><option value="<?= (int)$p["id"] ?>" data-preco="<?= htmlspecialchars((string)$p["preco"]) ?>" data-estoque="<?= (int)$p["estoque"] ?>" <?= $id === (int)$p["id"] ? "selected" : "" ?>><?= htmlspecialchars($p["codigo"] . " — " . $p["nome"]) ?> (estoque: <?= (int)$p["estoque"] ?>)</option><?php endforeach; ?>
</select></label><div class="sale-info full"><span>Preço unitário<strong id="unitPrice">R$ 0,00</strong></span><span>Disponível<strong id="availableStock">—</strong></span></div>
<label>Quantidade<input type="number" name="quantidade" id="quantidade" min="1" step="1" value="1" required></label><div class="total-box"><span>Total da venda</span><strong id="saleTotal">R$ 0,00</strong></div><div class="form-actions full"><a class="button button-light" href="index.php">Cancelar</a><button class="button button-primary" type="submit">Confirmar venda</button></div></form><?php endif; ?></section></main><script src="assets/script.js"></script></body></html>
