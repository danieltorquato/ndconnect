# MODIFICAÇÕES IMPLEMENTADAS - SISTEMA DE ORÇAMENTOS

## Resumo das Alterações

Este documento descreve as modificações implementadas no sistema de orçamentos conforme solicitado:

### 1. Substituição dos Campos de Validade por Dados do Evento

**ANTES:**
- Campo: `data_validade` (data de validade do orçamento)

**DEPOIS:**
- Campo: `data_evento` (data do evento)
- Campo: `nome_evento` (nome do evento)
- Mantido: `data_orcamento` (data de criação do orçamento)

### 2. Alteração no PDF - Nome da Empresa

**ANTES:**
- PDF mostrava o nome do solicitante/cliente

**DEPOIS:**
- PDF mostra o nome da empresa (se disponível)
- Se não houver empresa cadastrada, mostra o nome do cliente
- Campo empresa agora é obrigatório no formulário

### 3. Opções de Geração de PDF

**ANTES:**
- Apenas uma opção de PDF com valores unitários

**DEPOIS:**
- **PDF Completo**: Com valores unitários e subtotais
- **PDF Simples**: Apenas com o total do orçamento

## Arquivos Modificados

### Backend (API)

1. **`api/Controllers/OrcamentoController.php`**
   - Atualizado para usar `data_evento` e `nome_evento`
   - Adicionado campo `empresa` na criação de clientes
   - Removido uso de `data_validade`

2. **`api/generate_pdf_real.php`**
   - Atualizado para mostrar nome da empresa
   - Implementado parâmetro `valores` para controlar exibição de valores unitários
   - Modificado layout para incluir dados do evento

3. **`api/generate_pdf_simples.php`** (NOVO)
   - Arquivo criado para gerar PDF sem valores unitários
   - Layout simplificado com apenas total

4. **`api/adicionar_campos_evento_orcamento.sql`** (NOVO)
   - Script SQL para adicionar novos campos ao banco de dados

5. **`api/migrar_campos_evento.php`** (NOVO)
   - Script PHP para executar a migração dos campos

### Frontend (Angular)

1. **`src/app/orcamento/orcamento.page.html`**
   - Substituída seção "Validade do Orçamento" por "Dados do Evento"
   - Adicionados campos: Nome do Evento e Data do Evento
   - Campo empresa agora é obrigatório
   - Adicionados botões para PDF Completo e PDF Simples

2. **`src/app/orcamento/orcamento.page.ts`**
   - Adicionadas propriedades: `dataEvento`, `nomeEvento`
   - Removida propriedade: `dataValidade`
   - Atualizada validação para incluir dados do evento e empresa
   - Adicionados métodos: `gerarPDFCompleto()` e `gerarPDFSimples()`
   - Atualizado método `gerarOrcamento()` para enviar novos campos

## Estrutura do Banco de Dados

### Novos Campos na Tabela `orcamentos`:
```sql
ALTER TABLE orcamentos 
ADD COLUMN data_evento DATE AFTER data_orcamento,
ADD COLUMN nome_evento VARCHAR(255) AFTER data_evento;
```

### Campo Adicionado na Tabela `clientes`:
```sql
ALTER TABLE clientes 
ADD COLUMN empresa VARCHAR(255);
```

## Como Usar as Novas Funcionalidades

### 1. Criar Orçamento
1. Preencha os dados do cliente (nome e empresa são obrigatórios)
2. Adicione os itens do orçamento
3. Preencha os dados do evento:
   - Nome do evento (obrigatório)
   - Data do evento (obrigatório)
4. Clique em "Gerar Orçamento"

### 2. Gerar PDFs
Após gerar o orçamento, aparecerão dois botões:

- **PDF Completo**: Gera PDF com valores unitários e subtotais
- **PDF Simples**: Gera PDF apenas com o total do orçamento

### 3. URLs dos PDFs
- PDF Completo: `api/generate_pdf_real.php?id={id}&valores=1`
- PDF Simples: `api/generate_pdf_simples.php?id={id}`

## Validações Implementadas

1. **Nome do evento**: Obrigatório
2. **Data do evento**: Obrigatória e não pode ser anterior a hoje
3. **Nome da empresa**: Obrigatório
4. **Nome do cliente**: Obrigatório

## Benefícios das Modificações

1. **Foco em Eventos**: Sistema agora é específico para empresas de eventos
2. **Flexibilidade de PDF**: Duas opções de visualização conforme necessidade
3. **Identificação Empresarial**: PDF mostra nome da empresa em vez do solicitante
4. **Melhor Organização**: Dados do evento facilitam identificação e planejamento

## Compatibilidade

- As modificações são retrocompatíveis
- Orçamentos existentes serão migrados automaticamente
- Campos antigos (`data_validade`) são mantidos para compatibilidade

## Próximos Passos Recomendados

1. Testar todas as funcionalidades em ambiente de desenvolvimento
2. Executar scripts de migração no banco de dados
3. Treinar usuários nas novas funcionalidades
4. Considerar remover campo `data_validade` após período de transição
