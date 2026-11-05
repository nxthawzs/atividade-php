<?php

$servidor = "localhost";
$usuario = "root";
$senha = "usbw";
$banco = "gerenciamento_produtos";

$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);
if (!$conexao) {
    die("Erro na conexão com o banco de dados: " . mysqli_connect_error());
}
mysqli_set_charset($conexao, "utf8");
?>
