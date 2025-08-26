# Sistema de Controle de Estoque

Este é um sistema de controle de estoque desenvolvido em PHP com MySQL.

## Configuração do Banco de Dados

Para configurar o banco de dados do sistema, siga os passos abaixo:

### Pré-requisitos

- Servidor web (Apache, Nginx, etc.)
- PHP 7.0 ou superior
- MySQL 5.6 ou superior
- XAMPP, WAMP, LAMP ou similar (recomendado para ambiente de desenvolvimento)

### Passos para configuração

1. **Verificar configurações de conexão**

   Abra o arquivo `config.php` e verifique se as configurações de conexão estão corretas:

   ```php
   $host = "localhost"; // Servidor MySQL
   $username = "root";        // Usuário MySQL
   $password = "";            // Senha MySQL
   $dbname = "sistema_cadastro"; // Nome do banco de dados
   ```

2. **Inicializar o banco de dados**

   Existem duas opções para inicializar o banco de dados:

   **Opção 1**: Acesse o arquivo `inicializar_banco.php` através do navegador:
   ```
   http://localhost/att28-2/inicializar_banco.php
   ```

   **Opção 2**: Execute o script SQL diretamente no MySQL:
   - Acesse o phpMyAdmin (http://localhost/phpmyadmin)
   - Crie um novo banco de dados chamado `sistema_cadastro`
   - Importe o arquivo `criar_banco_dados.sql`

3. **Verificar a instalação**

   Após a inicialização do banco de dados, acesse o sistema:
   ```
   http://localhost/att28-2/index.php
   ```

4. **Credenciais padrão**

   O sistema já vem com um usuário administrador padrão:
   - **Usuário**: admin
   - **Senha**: admin123

## Estrutura do Banco de Dados

O banco de dados contém as seguintes tabelas:

1. **clientes** - Armazena informações dos clientes
2. **vendedores** - Armazena informações dos vendedores
3. **produtos** - Armazena informações dos produtos em estoque
4. **usuarios** - Armazena informações dos usuários do sistema
5. **vendas** - Armazena informações das vendas realizadas
6. **itens_venda** - Armazena os itens de cada venda
7. **movimentacoes_estoque** - Armazena as movimentações de entrada e saída do estoque

## Funcionalidades do Sistema

- Cadastro e gerenciamento de clientes
- Cadastro e gerenciamento de produtos
- Cadastro e gerenciamento de vendedores
- Registro de vendas com atualização automática do estoque
- Controle de estoque com registro de movimentações
- Dashboard com gráficos e estatísticas de vendas
- Relatórios de vendas por período

## Suporte

Para suporte ou dúvidas, entre em contato com o administrador do sistema. 