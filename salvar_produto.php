<?php
include 'conexao.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: index.php"); exit; }
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT) ?: 0;
$codigo = trim($_POST["codigo"] ?? "");
$nome = trim($_POST["nome"] ?? "");
$descricao = trim($_POST["descricao"] ?? "");
$precoTexto = str_replace(",", ".", trim($_POST["preco"] ?? ""));
$estoqueTexto = trim($_POST["estoque"] ?? "");
if ($codigo === "" || $nome === "" || !is_numeric($precoTexto) || (float)$precoTexto <= 0 || filter_var($estoqueTexto, FILTER_VALIDATE_INT) === false || (int)$estoqueTexto < 0) {
    $_SESSION["erro"] = "Preencha os campos corretamente. O preço deve ser maior que zero e o estoque não pode ser negativo.";
    header("Location: " . ($id ? "produto.php?id=" . $id : "produto.php")); exit;
}
$preco = (float)$precoTexto;
$estoque = (int)$estoqueTexto;
if ($id > 0) {
    $sql = mysqli_prepare($conexao, "UPDATE produtos SET codigo=?, nome=?, descricao=?, preco=?, estoque=? WHERE id=?");
    mysqli_stmt_bind_param($sql, "sssdii", $codigo, $nome, $descricao, $preco, $estoque, $id);
} else {
    $sql = mysqli_prepare($conexao, "INSERT INTO produtos (codigo, nome, descricao, preco, estoque) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($sql, "sssdi", $codigo, $nome, $descricao, $preco, $estoque);
}
if (mysqli_stmt_execute($sql)) {
    $_SESSION["mensagem"] = $id ? "Produto atualizado com sucesso." : "Produto cadastrado com sucesso.";
    header("Location: index.php"); exit;
}
if (mysqli_stmt_errno($sql) === 1062) { $_SESSION["erro"] = "Esse código de produto já está cadastrado."; }
else { $_SESSION["erro"] = "Não foi possível salvar o produto."; }
header("Location: " . ($id ? "produto.php?id=" . $id : "produto.php")); exit;
?>
