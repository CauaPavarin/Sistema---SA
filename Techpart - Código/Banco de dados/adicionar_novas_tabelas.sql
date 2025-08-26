-- Novas tabelas para o sistema de controle de estoque

-- Tabela de fornecedores
CREATE TABLE IF NOT EXISTS fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cnpj VARCHAR(20) NOT NULL,
    contato VARCHAR(100),
    telefone VARCHAR(20),
    email VARCHAR(100),
    endereco VARCHAR(255),
    site VARCHAR(100),
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de compras (pedidos para fornecedores)
CREATE TABLE IF NOT EXISTS compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_fornecedor INT NOT NULL,
    numero_pedido VARCHAR(50),
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_entrega DATETIME,
    status ENUM('pendente', 'parcial', 'entregue', 'cancelado') DEFAULT 'pendente',
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    observacoes TEXT,
    id_usuario INT,
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de itens de compra
CREATE TABLE IF NOT EXISTS itens_compra (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_compra INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    quantidade_recebida INT DEFAULT 0,
    FOREIGN KEY (id_compra) REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES produtos(id) ON DELETE RESTRICT
);

-- Tabela de avaliação de fornecedores
CREATE TABLE IF NOT EXISTS avaliacao_fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_fornecedor INT NOT NULL,
    id_compra INT NOT NULL,
    prazo INT, -- Nota de 1 a 5
    qualidade INT, -- Nota de 1 a 5
    preco INT, -- Nota de 1 a 5
    atendimento INT, -- Nota de 1 a 5
    comentarios TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT,
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id) ON DELETE CASCADE,
    FOREIGN KEY (id_compra) REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de níveis de estoque
CREATE TABLE IF NOT EXISTS niveis_estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT NOT NULL,
    estoque_minimo INT NOT NULL DEFAULT 5,
    estoque_ideal INT NOT NULL DEFAULT 20,
    gerar_alerta BOOLEAN DEFAULT TRUE,
    id_fornecedor_padrao INT,
    tempo_reposicao INT DEFAULT 7, -- Tempo médio de reposição em dias
    FOREIGN KEY (id_produto) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_fornecedor_padrao) REFERENCES fornecedores(id) ON DELETE SET NULL
);

-- Tabela de alertas de estoque
CREATE TABLE IF NOT EXISTS alertas_estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT NOT NULL,
    tipo ENUM('baixo', 'critico', 'vencimento') NOT NULL,
    mensagem TEXT NOT NULL,
    data_alerta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolvido BOOLEAN DEFAULT FALSE,
    data_resolucao DATETIME,
    id_usuario_resolucao INT,
    FOREIGN KEY (id_produto) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_resolucao) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de serviços técnicos
CREATE TABLE IF NOT EXISTS servicos_tecnicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    protocolo VARCHAR(20) NOT NULL,
    id_cliente INT NOT NULL,
    equipamento VARCHAR(100) NOT NULL,
    marca VARCHAR(50),
    modelo VARCHAR(50),
    numero_serie VARCHAR(50),
    problema_relatado TEXT NOT NULL,
    diagnostico TEXT,
    solucao TEXT,
    status ENUM('recebido', 'diagnostico', 'aguardando_aprovacao', 'aguardando_pecas', 'em_manutencao', 'concluido', 'entregue', 'cancelado') DEFAULT 'recebido',
    prioridade ENUM('baixa', 'normal', 'alta', 'urgente') DEFAULT 'normal',
    data_entrada DATETIME DEFAULT CURRENT_TIMESTAMP,
    previsao_entrega DATETIME,
    data_conclusao DATETIME,
    data_entrega DATETIME,
    valor_servico DECIMAL(10,2) DEFAULT 0.00,
    valor_pecas DECIMAL(10,2) DEFAULT 0.00,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    forma_pagamento VARCHAR(50),
    observacoes TEXT,
    id_tecnico INT,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_tecnico) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de peças utilizadas em serviços
CREATE TABLE IF NOT EXISTS pecas_servico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_servico INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_servico) REFERENCES servicos_tecnicos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES produtos(id) ON DELETE RESTRICT
);

-- Tabela de histórico de serviços
CREATE TABLE IF NOT EXISTS historico_servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_servico INT NOT NULL,
    status_anterior ENUM('recebido', 'diagnostico', 'aguardando_aprovacao', 'aguardando_pecas', 'em_manutencao', 'concluido', 'entregue', 'cancelado'),
    status_novo ENUM('recebido', 'diagnostico', 'aguardando_aprovacao', 'aguardando_pecas', 'em_manutencao', 'concluido', 'entregue', 'cancelado') NOT NULL,
    comentario TEXT,
    data_modificacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT,
    FOREIGN KEY (id_servico) REFERENCES servicos_tecnicos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de configurações de relatórios
CREATE TABLE IF NOT EXISTS configuracoes_relatorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    tipo ENUM('vendas', 'estoque', 'servicos', 'financeiro', 'fornecedores', 'clientes') NOT NULL,
    parametros TEXT, -- Armazena parâmetros em formato JSON
    id_usuario INT,
    compartilhado BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Índices para melhorar a performance
CREATE INDEX idx_fornecedores_nome ON fornecedores(nome);
CREATE INDEX idx_compras_fornecedor ON compras(id_fornecedor);
CREATE INDEX idx_compras_data ON compras(data_pedido);
CREATE INDEX idx_compras_status ON compras(status);
CREATE INDEX idx_itens_compra_produto ON itens_compra(id_produto);
CREATE INDEX idx_niveis_estoque_produto ON niveis_estoque(id_produto);
CREATE INDEX idx_alertas_produto ON alertas_estoque(id_produto);
CREATE INDEX idx_alertas_tipo ON alertas_estoque(tipo);
CREATE INDEX idx_alertas_resolvido ON alertas_estoque(resolvido);
CREATE INDEX idx_servicos_cliente ON servicos_tecnicos(id_cliente);
CREATE INDEX idx_servicos_status ON servicos_tecnicos(status);
CREATE INDEX idx_servicos_data ON servicos_tecnicos(data_entrada);
CREATE INDEX idx_pecas_servico_produto ON pecas_servico(id_produto); 