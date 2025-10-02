# Funcionalidade: Produtos Mais Populares

## Resumo das Alterações

Implementei uma funcionalidade que separa os produtos mais comuns para aparecer primeiro, mostrando apenas 5 produtos de amostra inicialmente. Quando o usuário pesquisar ou selecionar uma categoria, todos os produtos são exibidos.

## Alterações Realizadas

### 1. Banco de Dados
- **Arquivo**: `api/database.sql`
- **Mudança**: Adicionado campo `popularidade` na tabela `produtos`
- **Valores**: Atribuídos valores de 25 a 95 para os produtos existentes

### 2. Backend (API)
- **Arquivo**: `api/Controllers/ProdutoController.php`
- **Mudanças**:
  - Modificado método `getAll()` para ordenar por popularidade (DESC)
  - Adicionado método `getMaisPopulares($limit = 5)` para buscar apenas os produtos mais populares

- **Arquivo**: `api/Routes/api.php`
- **Mudança**: Adicionada nova rota `produtos/populares` com parâmetro opcional `limit`

### 3. Frontend (Angular/Ionic)
- **Arquivo**: `src/app/home/home.page.ts`
- **Mudanças**:
  - Adicionadas propriedades: `produtosIniciais`, `mostrarTodosProdutos`
  - Modificado `ngOnInit()` para carregar apenas produtos iniciais
  - Adicionados métodos: `carregarProdutosIniciais()`, `carregarTodosProdutos()`, `limparFiltros()`
  - Atualizado `filtrarProdutos()` para gerenciar a lógica de exibição

- **Arquivo**: `src/app/home/home.page.html`
- **Mudanças**:
  - Adicionado botão "Limpar Filtros" quando há pesquisa/filtro ativo
  - Adicionado botão "Ver Todos os Produtos" quando mostrando apenas produtos iniciais
  - Atualizado título da seção para mostrar "Produtos Mais Populares" ou "Produtos Disponíveis"

- **Arquivo**: `src/app/home/home.page.scss`
- **Mudança**: Adicionados estilos para o container do botão "Ver todos os produtos"

## Como Funciona

### Comportamento Inicial
1. Ao carregar a página, são exibidos apenas os 5 produtos mais populares
2. O título da seção mostra "Produtos Mais Populares"
3. Aparece um botão "Ver Todos os Produtos" para carregar todos os produtos

### Comportamento com Pesquisa/Filtro
1. Quando o usuário digita algo no campo de pesquisa OU seleciona uma categoria
2. Automaticamente carrega todos os produtos do banco de dados
3. Aplica os filtros de pesquisa e categoria
4. O título muda para "Produtos Disponíveis"
5. Aparece um botão "Limpar Filtros" para voltar aos produtos iniciais

### Botão "Limpar Filtros"
- Limpa o campo de pesquisa
- Reseta a seleção de categoria
- Volta a mostrar apenas os 5 produtos mais populares
- Remove o botão "Limpar Filtros"

## Arquivos de Atualização do Banco

- **`api/update_database.sql`**: Script para atualizar bancos existentes com o campo de popularidade

## Produtos Mais Populares (Valores de Popularidade)

1. **Palco 3x3m** - 95 pontos
2. **Gerador 5KVA** - 90 pontos  
3. **Sistema de som 2.1** - 88 pontos
4. **Palco 4x4m** - 85 pontos
5. **Microfone sem fio** - 82 pontos

## Benefícios

- **Performance**: Carrega apenas 5 produtos inicialmente, reduzindo o tempo de carregamento
- **UX Melhorada**: Usuário vê imediatamente os produtos mais relevantes
- **Flexibilidade**: Fácil acesso a todos os produtos quando necessário
- **Escalabilidade**: Sistema pode ser facilmente ajustado para diferentes quantidades de produtos iniciais

## Próximos Passos Sugeridos

1. Executar o script `api/update_database.sql` no banco de dados existente
2. Testar a funcionalidade no ambiente de desenvolvimento
3. Considerar implementar um sistema de tracking de popularidade baseado em cliques/vendas
4. Adicionar configuração para ajustar a quantidade de produtos iniciais via admin
