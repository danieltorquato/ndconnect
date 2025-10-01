# N.D Connect - Sistema de Orçamentos

Sistema completo para geração de orçamentos de equipamentos para eventos, desenvolvido para a N.D Connect.

## 🎯 Funcionalidades

- **Catálogo de Produtos**: Palcos, geradores, efeitos, stands octanorme, som, luz e painéis LED
- **Sistema de Orçamento**: Seleção de produtos com cálculo automático de valores
- **Gestão de Clientes**: Cadastro e histórico de clientes
- **Geração de PDF**: Orçamentos em formato profissional para impressão
- **Interface Responsiva**: Design moderno com cores da marca N.D Connect

## 🛠️ Tecnologias Utilizadas

### Backend
- **PHP 7.4+** com PDO para banco de dados
- **MySQL** para armazenamento de dados
- **API REST** para comunicação com frontend

### Frontend
- **Ionic 7** com Angular 16
- **TypeScript** para tipagem estática
- **SCSS** para estilização personalizada

## 📋 Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Node.js 16 ou superior
- npm ou yarn

## 🚀 Instalação

### 1. Configuração do Banco de Dados

1. Crie um banco de dados MySQL chamado `ndconnect_orcamento`
2. Execute o script SQL localizado em `src/api/database.sql`
3. Atualize as credenciais no arquivo `src/api/Config/Database.php`

```php
private $host = "localhost";
private $db_name = "ndconnect_orcamento";
private $username = "seu_usuario";
private $password = "sua_senha";
```

### 2. Configuração do Backend

1. Configure um servidor web (Apache/Nginx) apontando para a pasta `src/api`
2. Certifique-se de que o PHP tem as extensões PDO e PDO_MySQL habilitadas

### 3. Configuração do Frontend

1. Navegue até a pasta do projeto:
```bash
cd "c:\Users\GV - BIO INBODY\Desktop\Torquato IT\ndconnect"
```

2. Instale as dependências:
```bash
npm install
```

3. Inicie o servidor de desenvolvimento:
```bash
ionic serve
```

## 📱 Como Usar

### 1. Acessar o Sistema
- Abra o navegador e acesse `http://localhost:8100`
- O sistema carregará automaticamente os produtos disponíveis

### 2. Criar um Orçamento

1. **Filtrar Produtos**: Use o seletor de categoria para filtrar produtos
2. **Adicionar Itens**: Clique em "Adicionar" nos produtos desejados
3. **Ajustar Quantidades**: Use os botões +/- para ajustar quantidades
4. **Preencher Dados do Cliente**: Informe nome (obrigatório) e outros dados
5. **Aplicar Desconto**: Se necessário, informe o valor do desconto
6. **Gerar Orçamento**: Clique em "Gerar Orçamento"

### 3. Visualizar PDF
- Após gerar o orçamento, o PDF será aberto automaticamente
- O PDF contém todas as informações do orçamento com layout profissional
- Use Ctrl+P para imprimir ou salvar como PDF

## 🎨 Design e Cores

O sistema utiliza as cores oficiais da N.D Connect:

- **Azul Principal**: #1e3a8a (títulos, elementos principais)
- **Laranja Secundário**: #f97316 (destaques, badges)
- **Vermelho Accent**: #dc2626 (elementos de destaque)
- **Cinza Claro**: #f8fafc (fundos, seções)

## 📊 Estrutura do Banco de Dados

### Tabelas Principais

- **categorias**: Categorias de produtos (Palco, Gerador, etc.)
- **produtos**: Catálogo de produtos com preços
- **clientes**: Dados dos clientes
- **orcamentos**: Cabeçalho dos orçamentos
- **orcamento_itens**: Itens de cada orçamento

## 🔧 Personalização

### Adicionar Novos Produtos

1. Acesse o banco de dados
2. Insira novos registros na tabela `produtos`
3. Os produtos aparecerão automaticamente no sistema

### Modificar Preços

1. Atualize os preços na tabela `produtos`
2. Os novos preços serão aplicados imediatamente

### Personalizar Layout do PDF

1. Edite o arquivo `src/api/simple_pdf.php`
2. Modifique o HTML e CSS conforme necessário

## 📞 Suporte

Para dúvidas ou suporte técnico, entre em contato:

- **Email**: contato@ndconnect.com.br
- **Telefone**: (11) 99999-9999

## 📄 Licença

Este sistema foi desenvolvido exclusivamente para a N.D Connect.

---

**N.D Connect** - Equipamentos para Eventos
*Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED*
"# ndconnect" 
