# Sistema de Gerenciamento de Níveis de Acesso - N.D Connect

## 📋 Visão Geral

Sistema completo para gerenciar níveis de acesso personalizados e permissões granulares por página. Permite criar níveis customizados e definir exatamente quais páginas cada nível pode acessar e quais ações pode realizar.

## 🚀 Funcionalidades

### ✅ Gerenciamento de Níveis
- **Criar níveis personalizados** com nomes, descrições e cores
- **Editar níveis existentes** mantendo compatibilidade
- **Excluir níveis** (apenas se não houver usuários associados)
- **Ordenar níveis** por prioridade
- **Ativar/desativar níveis**

### ✅ Sistema de Permissões Granulares
- **4 tipos de permissão por página:**
  - `pode_acessar` - Pode visualizar a página
  - `pode_editar` - Pode modificar dados
  - `pode_deletar` - Pode excluir registros
  - `pode_criar` - Pode criar novos registros
- **Permissões hierárquicas** - Editar/Deletar/Criar só funcionam se Acessar estiver ativo
- **Gerenciamento por categoria** - Organize páginas por grupos
- **Seleção em massa** - Selecione todas ou nenhuma permissão de uma categoria

### ✅ Interface Intuitiva
- **Dashboard visual** com cards coloridos para cada nível
- **Filtros e busca** para encontrar níveis e permissões rapidamente
- **Indicadores visuais** de status e quantidade de usuários
- **Modais responsivos** para criação e edição
- **Confirmações** antes de ações destrutivas

## 🗄️ Estrutura do Banco de Dados

### Novas Tabelas

#### 1. `niveis_acesso`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- nome (VARCHAR(50), UNIQUE, NOT NULL)
- descricao (TEXT)
- cor (VARCHAR(7)) - Cor em hexadecimal
- ordem (INT) - Para ordenação
- ativo (BOOLEAN, DEFAULT TRUE)
- data_criacao (TIMESTAMP)
- data_atualizacao (TIMESTAMP)
```

#### 2. `paginas_sistema`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- nome (VARCHAR(100), NOT NULL)
- rota (VARCHAR(200), NOT NULL)
- icone (VARCHAR(50), DEFAULT 'document')
- categoria (VARCHAR(50), DEFAULT 'Geral')
- descricao (TEXT)
- ativo (BOOLEAN, DEFAULT TRUE)
- data_criacao (TIMESTAMP)
```

#### 3. `permissoes_nivel` (Expandida)
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- nivel_id (INT, FOREIGN KEY)
- pagina_id (INT, FOREIGN KEY)
- pode_acessar (BOOLEAN, DEFAULT TRUE)
- pode_editar (BOOLEAN, DEFAULT FALSE)
- pode_deletar (BOOLEAN, DEFAULT FALSE)
- pode_criar (BOOLEAN, DEFAULT FALSE)
- data_criacao (TIMESTAMP)
- data_atualizacao (TIMESTAMP)
```

### Tabelas Modificadas

#### `usuarios`
- Adicionado campo `nivel_id` (INT, FOREIGN KEY)
- Mantida compatibilidade com `nivel_acesso` (ENUM)

## 📁 Arquivos Criados

### Backend (PHP)
- `api/database_niveis_acesso.sql` - Script de migração do banco
- `api/Controllers/NivelAcessoController.php` - CRUD de níveis
- `api/Controllers/PaginaSistemaController.php` - CRUD de páginas
- `api/migrar_niveis_acesso.php` - Script de migração
- `api/Routes/api.php` - Rotas atualizadas

### Frontend (Angular)
- `src/app/services/nivel-acesso.service.ts` - Serviço de API
- `src/app/admin/niveis-acesso/niveis-acesso.page.ts` - Página principal
- `src/app/admin/niveis-acesso/niveis-acesso.page.html` - Template
- `src/app/admin/niveis-acesso/niveis-acesso.page.scss` - Estilos
- `src/app/admin/niveis-acesso/permissoes/permissoes.page.ts` - Página de permissões
- `src/app/admin/niveis-acesso/permissoes/permissoes.page.html` - Template
- `src/app/admin/niveis-acesso/permissoes/permissoes.page.scss` - Estilos

## 🔧 Instalação

### 1. Executar Migração do Banco
```bash
# Acesse o arquivo de migração
php api/migrar_niveis_acesso.php
```

### 2. Verificar Rotas
As rotas já foram adicionadas ao `api/Routes/api.php`:
- `GET /api/niveis-acesso` - Listar níveis
- `POST /api/niveis-acesso` - Criar nível
- `GET /api/niveis-acesso/{id}` - Obter nível
- `PUT /api/niveis-acesso/{id}` - Atualizar nível
- `DELETE /api/niveis-acesso/{id}` - Deletar nível
- `GET /api/niveis-acesso/{id}/permissoes` - Obter permissões
- `PUT /api/niveis-acesso/{id}/permissoes` - Atualizar permissões

### 3. Adicionar Rotas no Frontend
Adicione as rotas no `app.routes.ts`:
```typescript
{
  path: 'admin/niveis-acesso',
  loadComponent: () => import('./admin/niveis-acesso/niveis-acesso.page').then(m => m.NiveisAcessoPage),
  canActivate: [AuthGuard]
},
{
  path: 'admin/niveis-acesso/:id/permissoes',
  loadComponent: () => import('./admin/niveis-acesso/permissoes/permissoes.page').then(m => m.PermissoesPage),
  canActivate: [AuthGuard]
}
```

## 🎯 Como Usar

### 1. Acessar o Sistema
- Faça login como administrador
- Navegue para **Admin > Gerenciar Níveis de Acesso**

### 2. Gerenciar Níveis
- **Criar novo nível:** Clique no botão "+" (FAB)
- **Editar nível:** Clique em "Editar" no card do nível
- **Excluir nível:** Clique em "Excluir" (apenas se não houver usuários)
- **Configurar permissões:** Clique no nome do nível para acessar permissões

### 3. Configurar Permissões
- **Filtrar por categoria:** Use o segmento no topo
- **Buscar páginas:** Use a barra de pesquisa
- **Configurar permissões:** Use os toggles para cada página
- **Seleção em massa:** Use "Todas" ou "Nenhuma" por categoria
- **Salvar alterações:** Clique no botão "Salvar" (aparece quando há mudanças)

### 4. Tipos de Permissão
- **Acessar (Azul):** Pode visualizar a página
- **Editar (Amarelo):** Pode modificar dados existentes
- **Deletar (Vermelho):** Pode excluir registros
- **Criar (Verde):** Pode criar novos registros

## 🔐 Segurança

### Recursos Implementados
- **Validação de permissões** no backend
- **Verificação de integridade** antes de excluir níveis
- **Transações de banco** para operações críticas
- **Validação de dados** em todas as operações
- **Logs de auditoria** (preparado para implementação)

### Níveis Padrão
O sistema mantém os níveis padrão:
- **Admin:** Acesso total a todas as funcionalidades
- **Gerente:** Acesso administrativo limitado (sem financeiro)
- **Vendedor:** Acesso básico (leads, orçamentos, painel)
- **Cliente:** Acesso limitado (apenas painel)

## 🎨 Personalização

### Cores Disponíveis
O sistema oferece 12 cores predefinidas para os níveis:
- Vermelho, Laranja, Amarelo, Verde, Verde água
- Azul claro, Azul, Roxo, Rosa, Cinza, Cinza escuro, Preto

### Categorias de Páginas
- **Administração:** Páginas administrativas do sistema
- **Sistema:** Páginas principais do sistema
- **Gerenciamento:** Páginas de gerenciamento
- **Relatórios:** Páginas de relatórios
- **Configurações:** Páginas de configuração
- **Geral:** Páginas gerais

## 📊 Monitoramento

### Indicadores Visuais
- **Contador de usuários** por nível
- **Status ativo/inativo** com cores
- **Ordem de exibição** configurável
- **Permissões ativas** por categoria

### Relatórios (Futuro)
- Relatório de uso de permissões
- Auditoria de alterações
- Estatísticas de acesso por nível

## 🔄 Migração e Compatibilidade

### Sistema Antigo
- Mantém compatibilidade com `nivel_acesso` (ENUM)
- Usuários existentes continuam funcionando
- Migração gradual para novo sistema

### Sistema Novo
- Níveis personalizados com `nivel_id`
- Permissões granulares por página
- Interface moderna e intuitiva

## 🚀 Próximos Passos

1. **Implementar verificação de permissões** no frontend
2. **Adicionar logs de auditoria** para mudanças
3. **Criar relatórios** de uso de permissões
4. **Implementar notificações** para mudanças de permissão
5. **Adicionar backup/restore** de configurações de níveis

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique os logs do servidor
2. Confirme se a migração foi executada corretamente
3. Verifique as permissões de arquivo
4. Teste as rotas da API diretamente

---

**Sistema desenvolvido para N.D Connect - Equipamentos para Eventos**
