# Estoque Fácil — versão com PHP procedural e MySQLi

Este projeto segue uma estrutura próxima ao exemplo de aula enviado: os arquivos PHP incluem `conexao.php`, recebem os formulários com `$_POST` e utilizam MySQLi para consultar e alterar o banco. As consultas com dados digitados usam comandos preparados para evitar inserir texto do usuário diretamente no SQL.

## Executar no USBWebServer
1. Extraia a pasta `gerenciamento_produtos` para o diretório raiz/site do USBWebServer (o botão `Root dir` costuma abrir o local correto).
2. No PHPMyAdmin do USBWebServer, selecione/importa o arquivo `database.sql`. Se o banco já estiver criado com outro nome, selecione esse banco e retire do SQL as linhas `CREATE DATABASE` e `USE` antes de importar.
3. Abra `conexao.php` e confira servidor, usuário, senha e nome do banco. O exemplo vem com `localhost`, `root`, senha `usbw` e banco `gerenciamento_produtos`; altere para os valores usados na sua instalação.
4. Abra o endereço local mostrado pelo USBWebServer e acrescente `/gerenciamento_produtos/` (inclua a porta se o seu USBWebServer usar uma).

## Funcionalidades
- Cadastrar, editar, excluir e pesquisar produtos por código ou nome.
- Registrar venda e baixar do estoque a quantidade vendida.
- Verificar estoque disponível no formulário e novamente no PHP.
- Registrar venda e atualizar estoque dentro de uma transação MySQL.

## Teste
Cadastre um produto com estoque 10, registre venda de 2 unidades e confira se o estoque passa para 8.
