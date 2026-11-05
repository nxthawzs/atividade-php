

CREATE TABLE IF NOT EXISTS tb_produtos (
    id INT NOT NULL AUTO_INCREMENT,
    descricao VARCHAR(120) NOT NULL,
    categoria VARCHAR(80) NOT NULL,
    valor_compra DECIMAL(10,2) NOT NULL,
    valor_venda DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS tb_vendas (
    id INT NOT NULL AUTO_INCREMENT,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    valor_unitario DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_vendas_produto (produto_id),
    CONSTRAINT fk_tb_vendas_produtos FOREIGN KEY (produto_id)
      REFERENCES tb_produtos(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO tb_produtos (descricao, categoria, valor_compra, valor_venda, estoque)
SELECT 'Notebook Samsung', 'Informática', 3800.00, 4120.00, 50
WHERE NOT EXISTS (SELECT 1 FROM tb_produtos WHERE descricao='Notebook Samsung' AND categoria='Informática');

INSERT INTO tb_produtos (descricao, categoria, valor_compra, valor_venda, estoque)
SELECT 'Mouse óptico', 'Informática', 18.00, 29.90, 35
WHERE NOT EXISTS (SELECT 1 FROM tb_produtos WHERE descricao='Mouse óptico' AND categoria='Informática');

INSERT INTO tb_produtos (descricao, categoria, valor_compra, valor_venda, estoque)
SELECT 'Cabo HDMI', 'Acessórios', 12.00, 24.50, 12
WHERE NOT EXISTS (SELECT 1 FROM tb_produtos WHERE descricao='Cabo HDMI' AND categoria='Acessórios');
