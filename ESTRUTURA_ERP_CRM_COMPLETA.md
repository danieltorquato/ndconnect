# 📋 ESTRUTURA COMPLETA ERP/CRM - N.D CONNECT

## 🎯 Visão Geral

Este documento descreve toda a estrutura do sistema ERP/CRM desenvolvido para a N.D Connect. O sistema está **estruturado e pronto** para implementação futura, com todos os módulos planejados.

---

## 🗄️ BANCO DE DADOS

### ✅ Criado e Pronto para Uso

**Arquivo**: `api/database_erp_crm.sql`

Execute este arquivo para criar toda a estrutura do ERP/CRM:

```bash
mysql -u root -p ndconnect < api/database_erp_crm.sql
```

### Módulos do Banco de Dados:

#### 1. **CRM - Gestão de Clientes**
- ✅ `leads` - Solicitações de orçamento
- ✅ `clientes` - Cadastro completo de clientes
- ✅ `interacoes_cliente` - Histórico de contatos

#### 2. **Vendas e Orçamentos**
- ✅ `orcamentos` - Orçamentos (já existente, aprimorado)
- ✅ `orcamento_historico` - Histórico de alterações
- ✅ `pedidos` - Pedidos de venda
- ✅ `pedido_itens` - Itens dos pedidos

#### 3. **Financeiro**
- ✅ `contas_receber` - Contas a receber
- ✅ `contas_pagar` - Contas a pagar
- ✅ `fluxo_caixa` - Controle de caixa

#### 4. **Estoque**
- ✅ `estoque_atual` - Quantidade disponível
- ✅ `estoque_movimentacoes` - Histórico de movimentações

#### 5. **Agenda e Eventos**
- ✅ `agenda_eventos` - Eventos agendados
- ✅ `evento_equipamentos` - Equipamentos por evento

#### 6. **Sistema**
- ✅ `usuarios` - Usuários do sistema
- ✅ `log_atividades` - Log de ações
- ✅ `metas_vendas` - Metas por vendedor

#### 7. **Views de Relatórios**
- ✅ `vw_dashboard_vendas` - Dashboard de vendas
- ✅ `vw_produtos_mais_vendidos` - Top produtos
- ✅ `vw_top_clientes` - Melhores clientes
- ✅ `vw_contas_receber_aberto` - Contas em aberto

---

## 🔌 API - CONTROLLERS

### ✅ Criados e Funcionando

1. **LeadController.php** - Gestão de leads
   - `getAll()` - Listar todos os leads
   - `getByStatus($status)` - Filtrar por status
   - `create($data)` - Criar lead (detecta cliente existente)
   - `update($id, $data)` - Atualizar lead
   - `convertToClient($leadId)` - Converter em cliente
   - `delete($id)` - Excluir lead

2. **DashboardController.php** - Métricas do painel
   - `getDashboardData()` - Retorna métricas principais

### 📝 Pendentes de Criação (Estrutura Pronta)

Você precisará criar estes controllers seguindo o padrão dos existentes:

```php
api/Controllers/
├── ClienteController.php (expandir o existente)
├── PedidoController.php
├── FinanceiroController.php
├── EstoqueController.php
├── AgendaController.php
└── RelatorioController.php
```

---

## 🎨 PÁGINAS FRONTEND

### ✅ Páginas Criadas e Funcionando

1. **Solicitar Orçamento** (`src/app/solicitar-orcamento/`)
   - ✅ Formulário completo
   - ✅ Validação de telefone (formato brasileiro)
   - ✅ Integração com API de leads
   - ✅ Design responsivo com cores N.D Connect

2. **Painel Administrativo** (`src/app/painel/`)
   - ✅ Dashboard com métricas
   - ✅ Cards de resumo
   - ✅ Navegação para módulos
   - ✅ Integração com API

### 📝 Páginas Pendentes (Estrutura de Pastas Criada)

```
src/app/admin/
├── gestao-leads/          (CRIAR)
├── gestao-clientes/       (CRIAR)
├── gestao-orcamentos/     (CRIAR - expandir o existente)
├── gestao-pedidos/        (CRIAR)
├── financeiro/            (CRIAR)
└── relatorios/            (CRIAR)
```

---

## 🛠️ PRÓXIMOS PASSOS PARA IMPLEMENTAÇÃO

### Fase 1: Estrutura Básica (CONCLUÍDA ✅)
- ✅ Banco de dados completo
- ✅ Controllers principais (Lead e Dashboard)
- ✅ Página de solicitação de orçamento
- ✅ Painel administrativo
- ✅ Rotas da API configuradas

### Fase 2: Gestão de Leads (60% CONCLUÍDO)
- ✅ Backend (API + Controller)
- ⏳ Criar página `admin/gestao-leads`
  - Lista de leads com filtros (novo, contatado, qualificado, etc.)
  - Modal de detalhes do lead
  - Botão de converter para cliente
  - Histórico de interações

### Fase 3: Gestão de Clientes
- ⏳ Expandir `ClienteController.php`
- ⏳ Criar página `admin/gestao-clientes`
  - Lista de clientes
  - Cadastro completo
  - Histórico de pedidos
  - Histórico de interações
  - Vincular leads anteriores

### Fase 4: Gestão de Orçamentos (Melhorar Existente)
- ⏳ Expandir página de orçamentos
  - Tabs: Pendentes | Aprovados | Vendidos | Rejeitados
  - Opção de vincular com pedido
  - Histórico de status
  - Filtros avançados

### Fase 5: Gestão de Pedidos
- ⏳ Criar `PedidoController.php`
- ⏳ Criar página `admin/gestao-pedidos`
  - Criar pedido a partir de orçamento
  - Acompanhar status (pendente → entregue)
  - Vincular equipamentos
  - Gerar nota de entrega

### Fase 6: Módulo Financeiro
- ⏳ Criar `FinanceiroController.php`
- ⏳ Criar página `admin/financeiro`
  - Contas a receber
  - Contas a pagar
  - Fluxo de caixa
  - Dashboard financeiro

### Fase 7: Estoque e Inventário
- ⏳ Criar `EstoqueController.php`
- ⏳ Integrar com produtos
  - Controle de quantidade
  - Movimentações
  - Alertas de estoque mínimo

### Fase 8: Agenda de Eventos
- ⏳ Criar `AgendaController.php`
- ⏳ Criar visualização de calendário
  - Eventos agendados
  - Equipamentos alocados
  - Conflitos de agenda

### Fase 9: Relatórios e Análises
- ⏳ Criar `RelatorioController.php`
- ⏳ Criar página `admin/relatorios`
  - Vendas por período
  - Top produtos
  - Top clientes
  - Metas vs Realizado
  - Gráficos interativos

---

## 📊 ENDPOINTS DA API

### ✅ Funcionando

```
GET    /dashboard               - Métricas do painel
GET    /leads                   - Listar leads
GET    /leads?status=novo       - Filtrar leads
POST   /leads                   - Criar lead
PUT    /leads/{id}              - Atualizar lead
DELETE /leads/{id}              - Excluir lead
POST   /leads/{id}/converter    - Converter em cliente
```

### 📝 A Criar

```
GET    /clientes                - Listar clientes
POST   /clientes                - Criar cliente
PUT    /clientes/{id}           - Atualizar cliente
GET    /clientes/{id}/historico - Histórico do cliente

GET    /pedidos                 - Listar pedidos
POST   /pedidos                 - Criar pedido
PUT    /pedidos/{id}/status     - Atualizar status

GET    /financeiro/receber      - Contas a receber
GET    /financeiro/pagar        - Contas a pagar
GET    /financeiro/fluxo-caixa  - Fluxo de caixa

GET    /estoque                 - Estoque atual
POST   /estoque/movimentacao    - Registrar movimentação

GET    /agenda/eventos          - Eventos agendados
POST   /agenda/eventos          - Criar evento

GET    /relatorios/vendas       - Relatório de vendas
GET    /relatorios/produtos     - Produtos mais vendidos
GET    /relatorios/clientes     - Top clientes
```

---

## 🎨 TEMPLATE DAS PÁGINAS ADMIN

Todas as páginas admin devem seguir este padrão:

```typescript
// Estrutura TypeScript
import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { IonHeader, IonToolbar, IonTitle, ... } from '@ionic/angular/standalone';

@Component({
  selector: 'app-nome-pagina',
  templateUrl: './nome-pagina.page.html',
  styleUrls: ['./nome-pagina.page.scss'],
  standalone: true,
  imports: [...]
})
export class NomePaginaPage implements OnInit {
  dados: any[] = [];
  private apiUrl = 'http://localhost:8000';

  constructor(private http: HttpClient, private router: Router) {}

  ngOnInit() {
    this.carregarDados();
  }

  carregarDados() {
    this.http.get<any>(`${this.apiUrl}/endpoint`).subscribe({
      next: (response) => {
        if (response.success) {
          this.dados = response.data;
        }
      },
      error: (error) => console.error(error)
    });
  }
}
```

```html
<!-- Estrutura HTML -->
<ion-header>
  <ion-toolbar>
    <ion-title>Título da Página</ion-title>
    <ion-button slot="end" (click)="voltarPainel()">
      <ion-icon name="arrow-back"></ion-icon>
    </ion-button>
  </ion-toolbar>
</ion-header>

<ion-content class="ion-padding">
  <div class="container">
    <!-- Conteúdo -->
  </div>
</ion-content>
```

```scss
// Estrutura SCSS
:host {
  --nd-primary: #FF6B00;
  --nd-secondary: #1a1a1a;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
}

ion-card {
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
```

---

## 🔐 AUTENTICAÇÃO (Futuro)

Para implementação futura:

1. Criar `AuthController.php`
2. Sistema de login com JWT
3. Middleware de autenticação
4. Níveis de permissão (admin, vendedor, operador)

---

## 📱 FUNCIONALIDADES ESPECIAIS

### Vinculação Automática de Clientes

O sistema detecta automaticamente se um lead é de um cliente existente (por email ou telefone) e vincula automaticamente.

**Implementado em**: `LeadController.php` → método `buscarClienteExistente()`

### Status de Orçamentos

- **Pendente**: Aguardando aprovação do cliente
- **Aprovado**: Cliente aprovou, aguardando conversão em pedido
- **Vendido**: Convertido em pedido/venda
- **Rejeitado**: Cliente recusou
- **Expirado**: Passou da validade

---

## 🚀 COMO INICIAR A IMPLEMENTAÇÃO

### 1. Executar SQL do ERP/CRM

```bash
mysql -u root -p ndconnect < api/database_erp_crm.sql
```

### 2. Testar Endpoints Existentes

```bash
# Testar criação de lead
curl -X POST http://localhost:8000/leads \
  -H "Content-Type: application/json" \
  -d '{"nome":"Teste","email":"teste@email.com","telefone":"(11) 99999-9999","mensagem":"Quero um orçamento","origem":"site"}'

# Testar dashboard
curl http://localhost:8000/dashboard
```

### 3. Criar Primeira Página Admin

Comece por `admin/gestao-leads`:

```bash
ionic generate page admin/gestao-leads --standalone
```

Depois implemente seguindo o template fornecido acima.

---

## 📚 RECURSOS ADICIONAIS

### Bibliotecas Recomendadas

- **Chart.js** - Para gráficos nos relatórios
- **FullCalendar** - Para agenda de eventos
- **Moment.js** - Manipulação de datas
- **Export2Excel** - Exportar relatórios

### Comandos Úteis

```bash
# Gerar nova página
ionic generate page nome-pagina --standalone

# Gerar serviço
ionic generate service services/nome-servico

# Build para produção
ionic build --prod

# Executar testes
npm test
```

---

## ✅ CHECKLIST DE IMPLEMENTAÇÃO

- [x] Estrutura do banco de dados
- [x] Controllers básicos (Lead, Dashboard)
- [x] Página de solicitação de orçamento
- [x] Painel administrativo
- [x] Rotas da API
- [ ] Página gestão de leads
- [ ] Página gestão de clientes
- [ ] Página gestão de orçamentos (melhorar)
- [ ] Página gestão de pedidos
- [ ] Página financeiro
- [ ] Página relatórios
- [ ] Sistema de autenticação
- [ ] Testes automatizados
- [ ] Documentação completa da API

---

## 📞 SUPORTE

Toda a estrutura está pronta e documentada. Para implementar cada módulo, siga o padrão estabelecido e use este documento como referência.

**Desenvolvido para N.D Connect** 🎉

