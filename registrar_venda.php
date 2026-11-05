<?php
include 'conexao.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: index.php"); exit; }
$produtoId = filter_input(INPUT_POST, "produto_id", FILTER_VALIDATE_INT);
$quantidade = filter_input(INPUT_POST, "quantidade", FILTER_VALIDATE_INT);
if (!$produtoId || !$quantidade || $quantidade < 1) { $_SESSION["erro_venda"] = "Selecione um produto e informe uma quantidade válida."; header("Location: venda.php"); exit; }
mysqli_begin_transaction($conexao);
try {
    $sql = mysqli_prepare($conexao, "SELECT nome, preco, estoque FROM produtos WHERE id=? FOR UPDATE");
    mysqli_stmt_bind_param($sql, "i", $produtoId);
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql);
    $produto = mysqli_fetch_assoc($resultado);
    if (!$produto) { throw new Exception("Produto não encontrado."); }
    if ((int)$produto["estoque"] < $quantidade) { throw new Exception("Estoque insuficiente. Disponível: " . (int)$produto["estoque"] . " unidade(s)."); }
    $preco = (float)$produto["preco"];
    $total = round($preco * $quantidade, 2);
    $sql = mysqli_prepare($conexao, "INSERT INTO vendas (produto_id, quantidade, preco_unitario, total) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($sql, "iidd", $produtoId, $quantidade, $preco, $total);
    if (!mysqli_stmt_execute($sql)) { throw new Exception("Não foi possível registrar a venda."); }
    $sql = mysqli_prepare($conexao, "UPDATE produtos SET estoque=estoque-? WHERE id=? AND estoque>=?");
    mysqli_stmt_bind_param($sql, "iii", $quantidade, $produtoId, $quantidade);
    if (!mysqli_stmt_execute($sql) || mysqli_stmt_affected_rows($sql) !== 1) { throw new Exception("Não foi possível atualizar o estoque."); }
    mysqli_commit($conexao);
    $_SESSION["mensagem"] = "Venda registrada: " . $produto["nome"] . " — " . $quantidade . " unidade(s), total R$ " . number_format($total, 2, ",", ".");
    header("Location: index.php"); exit;
} catch (Throwable $e) {
    mysqli_rollback($conexao);
    $_SESSION["erro_venda"] = $e->getMessage();
    header("Location: venda.php?id=" . (int)$produtoId); exit;
}
?>
