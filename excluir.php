<?php
include 'conexao.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: index.php"); exit; }
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
if (!$id) { $_SESSION["mensagem"] = "Produto inválido."; header("Location: index.php"); exit; }
$sql = mysqli_prepare($conexao, "DELETE FROM produtos WHERE id=?");
mysqli_stmt_bind_param($sql, "i", $id);
if (mysqli_stmt_execute($sql)) { $_SESSION["mensagem"] = mysqli_stmt_affected_rows($sql) ? "Produto excluído." : "Produto não encontrado."; }
else { $_SESSION["mensagem"] = "Não foi possível excluir: este produto pode estar associado a vendas."; }
header("Location: index.php"); exit;
?>
