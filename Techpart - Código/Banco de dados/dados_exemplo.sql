-- Dados de exemplo para o sistema de controle de estoque
USE sistema_cadastro;

-- Inserir clientes de exemplo
INSERT INTO clientes (nome, email, telefone, endereco) VALUES
('João Silva', 'joao.silva@email.com', '(11) 98765-4321', 'Rua das Flores, 123 - São Paulo/SP'),
('Maria Oliveira', 'maria.oliveira@email.com', '(11) 91234-5678', 'Av. Paulista, 1000 - São Paulo/SP'),
('Pedro Santos', 'pedro.santos@email.com', '(21) 99876-5432', 'Rua do Comércio, 456 - Rio de Janeiro/RJ'),
('Ana Souza', 'ana.souza@email.com', '(31) 98765-1234', 'Av. Amazonas, 789 - Belo Horizonte/MG'),
('Carlos Ferreira', 'carlos.ferreira@email.com', '(41) 99988-7766', 'Rua XV de Novembro, 1500 - Curitiba/PR');

-- Inserir vendedores de exemplo
INSERT INTO vendedores (codigo, nome, celular, email) VALUES
('V001', 'Roberto Almeida', '(11) 97777-8888', 'roberto.almeida@empresa.com'),
('V002', 'Fernanda Lima', '(11) 96666-7777', 'fernanda.lima@empresa.com'),
('V003', 'Marcelo Costa', '(21) 95555-6666', 'marcelo.costa@empresa.com');

-- Inserir produtos de exemplo
INSERT INTO produtos (codigo, descricao, tipo, preco, custo, quantidade_estoque, fabricante, modelo, especificacoes) VALUES
('P001', 'Notebook Dell Inspiron', 'Eletrônicos', 3500.00, 2800.00, 15, 'Dell', 'Inspiron 15', 'Intel Core i5, 8GB RAM, 256GB SSD'),
('P002', 'Smartphone Samsung Galaxy', 'Eletrônicos', 2200.00, 1700.00, 25, 'Samsung', 'Galaxy S21', '128GB, 6GB RAM, Câmera 64MP'),
('P003', 'Monitor LG 24 polegadas', 'Eletrônicos', 950.00, 750.00, 30, 'LG', '24MK430H', 'Full HD, IPS, HDMI'),
('P004', 'Teclado Mecânico Gamer', 'Periféricos', 350.00, 220.00, 40, 'Redragon', 'K552', 'Switch Blue, RGB'),
('P005', 'Mouse sem fio Logitech', 'Periféricos', 120.00, 80.00, 50, 'Logitech', 'M280', '1000 DPI, Wireless'),
('P006', 'Headset Gamer', 'Periféricos', 280.00, 180.00, 35, 'HyperX', 'Cloud Stinger', 'Estéreo, Microfone'),
('P007', 'SSD Kingston 480GB', 'Componentes', 380.00, 280.00, 45, 'Kingston', 'A400', 'SATA 3, 500MB/s'),
('P008', 'Memória RAM 8GB', 'Componentes', 250.00, 180.00, 60, 'Corsair', 'Vengeance', 'DDR4, 3200MHz'),
('P009', 'Fonte de Alimentação 500W', 'Componentes', 320.00, 220.00, 25, 'EVGA', '500W', '80 Plus, PFC Ativo'),
('P010', 'Placa de Vídeo GTX 1650', 'Componentes', 1500.00, 1200.00, 12, 'NVIDIA', 'GTX 1650', '4GB GDDR6');

-- Inserir usuário cliente de exemplo
INSERT INTO usuarios (usuario, senha, tipo) VALUES
('cliente1', 'senha123', 'cliente');

-- Inserir algumas vendas de exemplo
INSERT INTO vendas (id_cliente, id_vendedor, valor_total) VALUES
(1, 1, 3500.00),
(2, 2, 2200.00),
(3, 3, 950.00),
(4, 1, 630.00),
(5, 2, 1880.00);

-- Inserir itens das vendas
INSERT INTO itens_venda (id_venda, id_produto, quantidade, preco_unitario) VALUES
(1, 1, 1, 3500.00),
(2, 2, 1, 2200.00),
(3, 3, 1, 950.00),
(4, 4, 1, 350.00),
(4, 5, 1, 280.00),
(5, 7, 2, 380.00),
(5, 8, 2, 250.00),
(5, 5, 1, 120.00);

-- Atualizar o estoque após as vendas
UPDATE produtos SET quantidade_estoque = quantidade_estoque - 1 WHERE id = 1;
UPDATE produtos SET quantidade_estoque = quantidade_estoque - 1 WHERE id = 2;
UPDATE produtos SET quantidade_estoque = quantidade_estoque - 1 WHERE id = 3;
UPDATE produtos SET quantidade_estoque = quantidade_estoque - 1 WHERE id = 4;
UPDATE produtos SET quantidade_estoque = quantidade_estoque - 1 WHERE id = 5;
UPDATE produtos SET quantidade_estoque = quantidade_estoque - 2 WHERE id = 7;
UPDATE produtos SET quantidade_estoque = quantidade_estoque - 2 WHERE id = 8;
UPDATE produtos SET quantidade_estoque = quantidade_estoque - 1 WHERE id = 5;

-- Inserir algumas movimentações de estoque
INSERT INTO movimentacoes_estoque (id_produto, tipo, quantidade, motivo, id_usuario) VALUES
(1, 'entrada', 20, 'Compra inicial', 1),
(2, 'entrada', 30, 'Compra inicial', 1),
(3, 'entrada', 35, 'Compra inicial', 1),
(4, 'entrada', 45, 'Compra inicial', 1),
(5, 'entrada', 55, 'Compra inicial', 1),
(1, 'saida', 1, 'Venda', 1),
(2, 'saida', 1, 'Venda', 1),
(3, 'saida', 1, 'Venda', 1),
(4, 'saida', 1, 'Venda', 1),
(5, 'saida', 2, 'Venda', 1),
(7, 'saida', 2, 'Venda', 1),
(8, 'saida', 2, 'Venda', 1),
(6, 'entrada', 40, 'Compra adicional', 1),
(7, 'entrada', 50, 'Compra adicional', 1),
(8, 'entrada', 65, 'Compra adicional', 1),
(9, 'entrada', 30, 'Compra adicional', 1),
(10, 'entrada', 15, 'Compra adicional', 1); 