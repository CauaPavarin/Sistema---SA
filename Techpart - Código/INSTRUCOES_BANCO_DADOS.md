# Instruções para Instalação do Banco de Dados

Este documento contém instruções detalhadas para a instalação e configuração do banco de dados para o sistema de controle de estoque.

## Arquivos Disponíveis

1. `criar_banco_dados.sql` - Script SQL para criar o banco de dados e todas as tabelas
2. `dados_exemplo.sql` - Script SQL com dados de exemplo para testar o sistema
3. `inicializar_banco.php` - Script PHP para inicializar o banco de dados via navegador
4. `importar_dados_exemplo.php` - Script PHP para importar apenas os dados de exemplo

## Opções de Instalação

### Opção 1: Instalação via navegador (recomendada)

1. Certifique-se de que o servidor web (Apache) e o MySQL estão em execução
2. Acesse o arquivo `inicializar_banco.php` através do navegador:
   ```
   http://localhost/att28-2/inicializar_banco.php
   ```
3. O script criará o banco de dados, todas as tabelas e um usuário administrador padrão
4. Na página exibida, você terá a opção de carregar os dados de exemplo clicando no botão correspondente

### Opção 2: Instalação via phpMyAdmin

1. Acesse o phpMyAdmin (normalmente em http://localhost/phpmyadmin)
2. Crie um novo banco de dados chamado `sistema_cadastro`
3. Selecione o banco de dados criado
4. Vá na aba "Importar"
5. Selecione o arquivo `criar_banco_dados.sql` e clique em "Executar"
6. Para importar os dados de exemplo, repita o processo com o arquivo `dados_exemplo.sql`

### Opção 3: Instalação via linha de comando MySQL

1. Abra o terminal ou prompt de comando
2. Execute os seguintes comandos:

```bash
mysql -u root -p < criar_banco_dados.sql
mysql -u root -p sistema_cadastro < dados_exemplo.sql
```

## Configuração de Conexão

O sistema está configurado para conectar ao MySQL com as seguintes credenciais:

- **Servidor:** localhost
- **Usuário:** root
- **Senha:** (em branco)
- **Banco de dados:** sistema_cadastro

Se você precisar alterar essas configurações, edite o arquivo `config.php`:

```php
$host = "localhost"; // Seu servidor MySQL
$username = "root";        // Seu usuário MySQL
$password = "";            // Sua senha MySQL
$dbname = "sistema_cadastro"; // Nome do banco de dados
```

## Estrutura do Banco de Dados

O banco de dados contém as seguintes tabelas:

1. **clientes** - Armazena informações dos clientes
2. **vendedores** - Armazena informações dos vendedores
3. **produtos** - Armazena informações dos produtos em estoque
4. **usuarios** - Armazena informações dos usuários do sistema
5. **vendas** - Armazena informações das vendas realizadas
6. **itens_venda** - Armazena os itens de cada venda
7. **movimentacoes_estoque** - Armazena as movimentações de entrada e saída do estoque

## Usuário Padrão

O sistema já vem com um usuário administrador padrão:

- **Usuário:** admin
- **Senha:** admin123

É altamente recomendável alterar essa senha após o primeiro login.

## Resolução de Problemas

### Erro de conexão com o banco de dados

Verifique se:
- O servidor MySQL está em execução
- As credenciais no arquivo `config.php` estão corretas
- O banco de dados `sistema_cadastro` foi criado

### Erro ao criar tabelas

Se ocorrer algum erro durante a criação das tabelas, verifique:
- Se você tem permissões suficientes no MySQL
- Se não há tabelas com o mesmo nome já existentes

### Erro ao importar dados de exemplo

Se ocorrer algum erro ao importar os dados de exemplo:
- Verifique se o banco de dados e as tabelas foram criados corretamente
- Tente importar o arquivo `dados_exemplo.sql` diretamente pelo phpMyAdmin

Para mais informações ou suporte, entre em contato com o administrador do sistema. 