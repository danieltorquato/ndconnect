# Correção: Coluna de Popularidade no Banco de Dados

## Problema Identificado

A coluna `popularidade` não foi criada no banco de dados existente, causando:
- 0 produtos sendo retornados na rota `/produtos/populares`
- Sistema de produtos mais populares não funcionando
- Frontend mostrando lista vazia

## Soluções Disponíveis

### 🚀 **Opção 1: Script PHP Automático (Recomendado)**

Execute o script PHP que criará a coluna e populará os dados automaticamente:

```bash
# No terminal, navegue até a pasta da API
cd api

# Execute o script PHP
php add_popularidade.php
```

**O que o script faz:**
- ✅ Verifica se a coluna já existe
- ✅ Cria a coluna `popularidade` se necessário
- ✅ Atualiza todos os produtos com valores de popularidade
- ✅ Mostra progresso e confirmação
- ✅ Exibe os top 10 produtos mais populares

### 🛠️ **Opção 2: SQL Manual**

Se preferir executar manualmente no MySQL:

```sql
-- 1. Conectar ao banco
USE ndconnect;

-- 2. Adicionar coluna
ALTER TABLE produtos ADD COLUMN popularidade INT DEFAULT 0;

-- 3. Executar o arquivo SQL
SOURCE add_popularidade_column.sql;
```

### 📋 **Opção 3: Via phpMyAdmin**

1. Acesse o phpMyAdmin
2. Selecione o banco `ndconnect`
3. Vá em "SQL" no menu superior
4. Cole e execute o conteúdo do arquivo `add_popularidade_column.sql`

## Valores de Popularidade Atribuídos

### **Top 5 Produtos Mais Populares:**
1. **Palco 3x3m** - 95 pontos
2. **Gerador 5KVA** - 90 pontos
3. **Sistema de som 2.1** - 88 pontos
4. **Palco 4x4m** - 85 pontos
5. **Microfone sem fio** - 82 pontos

### **Distribuição por Categoria:**
- **Palcos**: 95, 85, 70, 30 pontos
- **Geradores**: 90, 80, 60, 40 pontos
- **Efeitos**: 75, 65, 55, 35 pontos
- **Stands**: 70, 60, 45, 25 pontos
- **Som**: 88, 78, 82, 68 pontos
- **Luz**: 72, 62, 52, 42 pontos
- **Painéis LED**: 65, 55, 45, 35 pontos

## Melhorias Implementadas no Código

### **1. Verificação Automática de Coluna**
O `ProdutoController` agora verifica se a coluna existe antes de usá-la:

```php
// Verificar se a coluna popularidade existe
$checkColumn = "SHOW COLUMNS FROM produtos LIKE 'popularidade'";
$columnExists = $stmt->fetch();

if ($columnExists) {
    // Usar ordenação por popularidade
    ORDER BY p.popularidade DESC
} else {
    // Fallback para ordenação por nome/ID
    ORDER BY c.nome, p.nome
}
```

### **2. Fallback Inteligente**
- Se a coluna não existir, retorna produtos ordenados por nome
- Se existir, usa a ordenação por popularidade
- Sistema funciona independente do estado do banco

### **3. Compatibilidade Total**
- Funciona com bancos antigos (sem coluna)
- Funciona com bancos atualizados (com coluna)
- Transição suave entre os estados

## Verificação Pós-Execução

### **1. Teste da API**
```bash
# Testar rota de produtos populares
curl http://localhost:8000/produtos/populares?limit=5

# Deve retornar 5 produtos com valores de popularidade
```

### **2. Teste no Frontend**
1. Acesse a aplicação
2. Verifique se aparecem 5 produtos na seção "Produtos Mais Populares"
3. Teste o botão "Ver Todos os Produtos"
4. Teste filtros e pesquisa

### **3. Verificação no Banco**
```sql
-- Verificar se a coluna foi criada
DESCRIBE produtos;

-- Verificar valores de popularidade
SELECT nome, popularidade FROM produtos ORDER BY popularidade DESC LIMIT 10;
```

## Arquivos Criados/Modificados

### **Novos Arquivos:**
- `api/add_popularidade.php` - Script automático
- `api/add_popularidade_column.sql` - SQL manual
- `CORRECAO_POPULARIDADE.md` - Esta documentação

### **Arquivos Modificados:**
- `api/Controllers/ProdutoController.php` - Verificação de coluna
- `api/database.sql` - Schema atualizado (para novos bancos)

## Próximos Passos

1. **Execute o script** `add_popularidade.php`
2. **Teste a aplicação** para verificar se os produtos aparecem
3. **Verifique o banco** para confirmar os dados
4. **Monitore o desempenho** da funcionalidade

## Suporte

Se encontrar problemas:
1. Verifique se o banco `ndconnect` existe
2. Confirme se a tabela `produtos` tem dados
3. Execute o script em modo debug: `php -d display_errors=1 add_popularidade.php`
4. Verifique os logs do servidor web

---

**✅ Após executar a correção, o sistema de produtos mais populares funcionará perfeitamente!**
