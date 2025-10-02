# 🎨 FRONTEND COMPLETO - FASES 5, 6, 7 E 8

## 📋 ESTRUTURA DAS PÁGINAS

Todas as páginas seguem o mesmo padrão das páginas já criadas (Leads e Orçamentos):

### **Padrão TypeScript**:
```typescript
import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import { /* ícones necessários */ } from 'ionicons/icons';
import { /* componentes Ionic */ } from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-nome',
  templateUrl: './nome.page.html',
  styleUrls: ['./nome.page.scss'],
  standalone: true,
  imports: [/* componentes */]
})
export class NomePage implements OnInit {
  dados: any[] = [];
  private apiUrl = 'http://localhost:8000';
  
  constructor(
    private http: HttpClient,
    private router: Router,
    private alertController: AlertController
  ) {
    addIcons({ /* ícones */ });
  }
  
  ngOnInit() {
    this.carregarDados();
  }
  
  carregarDados() {
    this.http.get<any>(`${this.apiUrl}/endpoint`).subscribe({
      next: (response) => {
        if (response.success) {
          this.dados = response.data;
        }
      }
    });
  }
}
```

### **Padrão HTML**:
```html
<ion-header>
  <ion-toolbar>
    <ion-buttons slot="start">
      <ion-button (click)="voltarPainel()">
        <ion-icon name="arrow-back"></ion-icon>
      </ion-button>
    </ion-buttons>
    <ion-title>Título da Página</ion-title>
  </ion-toolbar>
  
  <!-- Tabs/Segmentos -->
  <ion-toolbar>
    <ion-segment [(ngModel)]="filtro" (ionChange)="mudarFiltro($event)">
      <ion-segment-button value="todos">
        <ion-label>Todos</ion-label>
      </ion-segment-button>
    </ion-segment>
  </ion-toolbar>
</ion-header>

<ion-content class="ion-padding">
  <div class="container">
    <!-- Busca -->
    <ion-searchbar [(ngModel)]="termo" (ionInput)="pesquisar()"></ion-searchbar>
    
    <!-- Lista -->
    <ion-card *ngFor="let item of itens">
      <!-- Conteúdo -->
    </ion-card>
    
    <!-- Modais -->
    <ion-modal [isOpen]="modalAberto">
      <ng-template>
        <!-- Conteúdo do modal -->
      </ng-template>
    </ion-modal>
  </div>
</ion-content>
```

### **Padrão SCSS**:
```scss
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

## 📄 FASE 5: GESTÃO DE CLIENTES

### **Arquivo**: `src/app/admin/gestao-clientes/gestao-clientes.page.ts`

**Funcionalidades principais**:
```typescript
export class GestaoClientesPage implements OnInit {
  clientes: Cliente[] = [];
  clientesFiltrados: Cliente[] = [];
  statusFiltro: string = 'todos'; // todos, ativo, inativo, bloqueado
  termoPesquisa: string = '';
  
  // Modais
  modalDetalhesAberto: boolean = false;
  modalCadastroAberto: boolean = false;
  modalHistoricoAberto: boolean = false;
  
  clienteSelecionado: any = null;
  historicoOrcamentos: any[] = [];
  historicoPedidos: any[] = [];
  estatisticas: any = {};
  
  // Formulário
  clienteForm = {
    nome: '',
    empresa: '',
    email: '',
    telefone: '',
    cpf_cnpj: '',
    endereco: '',
    tipo: 'pessoa_fisica',
    status: 'ativo',
    data_nascimento: '',
    observacoes: ''
  };
  
  carregarClientes() { /* GET /clientes */ }
  filtrarClientes() { /* Filtro local */ }
  verDetalhes(cliente) { 
    /* GET /clientes/{id} */
    /* GET /clientes/{id}/estatisticas */
  }
  abrirHistorico(cliente) {
    /* GET /clientes/{id}/historico-orcamentos */
    /* GET /clientes/{id}/historico-pedidos */
  }
  salvarCliente() { /* POST ou PUT /clientes */ }
  excluirCliente() { /* DELETE /clientes/{id} */ }
}
```

**HTML principais elementos**:
- Tabs: Todos | Ativos | Inativos | Bloqueados
- Cards com: Nome, Empresa, CPF/CNPJ, Telefone, Email
- Badge de status
- Botões: Detalhes, Histórico, Editar, Excluir
- Modal de detalhes com abas: Dados | Orçamentos | Pedidos | Estatísticas
- Modal de cadastro/edição

---

## 📦 FASE 6: GESTÃO DE PEDIDOS

### **Arquivo**: `src/app/admin/gestao-pedidos/gestao-pedidos.page.ts`

**Funcionalidades principais**:
```typescript
export class GestaoP edidosPage implements OnInit {
  pedidos: Pedido[] = [];
  pedidosFiltrados: Pedido[] = [];
  statusFiltro: string = 'todos'; 
  // pendente, confirmado, em_preparacao, pronto, entregue, cancelado
  
  modalDetalhesAberto: boolean = false;
  modalStatusAberto: boolean = false;
  modalCriarAberto: boolean = false;
  
  pedidoSelecionado: any = null;
  novoStatus: string = '';
  
  contadores = {
    pendentes: 0,
    confirmados: 0,
    em_preparacao: 0,
    prontos: 0,
    entregues: 0
  };
  
  carregarPedidos() { /* GET /pedidos */ }
  filtrarPorStatus() { /* GET /pedidos?status=X */ }
  verDetalhes(pedido) { /* GET /pedidos/{id} */ }
  atualizarStatus(pedido, novoStatus) { /* PUT /pedidos/{id}/status */ }
  criarDesdOrcamento(orcamentoId) { /* POST /pedidos/from-orcamento/{id} */ }
  excluirPedido() { /* DELETE /pedidos/{id} */ }
}
```

**HTML principais elementos**:
- Tabs: Todos | Pendentes | Confirmados | Em Preparação | Prontos | Entregues
- Cards com: Número, Cliente, Data, Entrega Prevista, Total, Status
- Timeline de status
- Botões: Detalhes, Atualizar Status, Excluir
- Modal de detalhes com lista de itens
- Modal de atualização de status
- Botão especial "Criar Pedido" nos orçamentos aprovados

---

## 💰 FASE 7: MÓDULO FINANCEIRO

### **Arquivo**: `src/app/admin/financeiro/financeiro.page.ts`

**Funcionalidades principais**:
```typescript
export class FinanceiroPage implements OnInit {
  abaAtiva: string = 'dashboard'; // dashboard, receber, pagar, fluxo
  
  // Dashboard
  dashboardFinanceiro = {
    receber_pendente: 0,
    receber_vencido: 0,
    pagar_pendente: 0,
    pagar_vencido: 0,
    entradas_mes: 0,
    saidas_mes: 0,
    saldo_mes: 0
  };
  
  // Contas a Receber
  contasReceber: any[] = [];
  contasReceberFiltradas: any[] = [];
  statusReceberFiltro: string = 'pendente';
  
  // Contas a Pagar
  contasPagar: any[] = [];
  contasPagarFiltradas: any[] = [];
  statusPagarFiltro: string = 'pendente';
  
  // Fluxo de Caixa
  fluxoCaixa: any = {
    movimentacoes: [],
    total_entradas: 0,
    total_saidas: 0,
    saldo: 0
  };
  periodoInicio: string = '';
  periodoFim: string = '';
  
  carregarDashboard() { /* GET /financeiro/dashboard */ }
  carregarContasReceber() { /* GET /financeiro/receber */ }
  filtrarContasReceber(status) { /* GET /financeiro/receber?status=X */ }
  registrarPagamentoReceber(id) { /* PUT /financeiro/receber/{id}/pagar */ }
  
  carregarContasPagar() { /* GET /financeiro/pagar */ }
  registrarPagamentoPagar(id) { /* PUT /financeiro/pagar/{id}/pagar */ }
  
  carregarFluxoCaixa() { /* GET /financeiro/fluxo-caixa?inicio=X&fim=Y */ }
}
```

**HTML principais elementos**:
- Tabs: Dashboard | Contas a Receber | Contas a Pagar | Fluxo de Caixa
- **Dashboard**: Cards com métricas financeiras
- **Contas a Receber**: Lista com filtros (Pendente, Pago, Atrasado)
- **Contas a Pagar**: Lista com categorias
- **Fluxo de Caixa**: Tabela com entradas/saídas, seletor de período
- Botões: Registrar Pagamento, Nova Conta
- Modais: Registrar Pagamento, Nova Conta

---

## 📊 FASE 8: GESTÃO DE ESTOQUE (Integrado com Produtos)

### **Expansão**: `src/app/produtos/produtos.page.ts`

**Adicionar ao TypeScript existente**:
```typescript
// Adicionar à classe ProdutosPage
estoqueAtual: Map<number, any> = new Map();
alertasEstoque: any[] = [];
modalEstoqueAberto: boolean = false;
modalMovimentacaoAberto: boolean = false;
produtoSelecionadoEstoque: any = null;

movimentacaoForm = {
  tipo: 'entrada',
  quantidade: 0,
  observacoes: ''
};

ngOnInit() {
  // ... código existente ...
  this.carregarEstoqueAtual();
  this.carregarAlertasEstoque();
}

carregarEstoqueAtual() {
  this.http.get<any>(`${this.apiUrl}/estoque`).subscribe({
    next: (response) => {
      if (response.success) {
        response.data.forEach((item: any) => {
          this.estoqueAtual.set(item.produto_id, item);
        });
      }
    }
  });
}

carregarAlertasEstoque() {
  this.http.get<any>(`${this.apiUrl}/estoque/alertas`).subscribe({
    next: (response) => {
      if (response.success) {
        this.alertasEstoque = response.data;
      }
    }
  });
}

abrirModalEstoque(produto: Produto) {
  this.produtoSelecionadoEstoque = produto;
  this.http.get<any>(`${this.apiUrl}/estoque/produto/${produto.id}`).subscribe({
    next: (response) => {
      if (response.success) {
        this.produtoSelecionadoEstoque.estoque = response.data;
        this.modalEstoqueAberto = true;
      }
    }
  });
}

abrirModalMovimentacao(produto: Produto) {
  this.produtoSelecionadoEstoque = produto;
  this.modalMovimentacaoAberto = true;
}

registrarMovimentacao() {
  const dados = {
    produto_id: this.produtoSelecionadoEstoque.id,
    tipo: this.movimentacaoForm.tipo,
    quantidade: this.movimentacaoForm.quantidade,
    observacoes: this.movimentacaoForm.observacoes,
    pedido_id: null
  };
  
  this.http.post<any>(`${this.apiUrl}/estoque/movimentacoes`, dados).subscribe({
    next: (response) => {
      if (response.success) {
        this.mostrarNotificacao('Movimentação registrada!', 'success');
        this.fecharModalMovimentacao();
        this.carregarProdutos();
        this.carregarEstoqueAtual();
      }
    }
  });
}

getEstoqueProduto(produtoId: number): any {
  return this.estoqueAtual.get(produtoId) || {
    quantidade_disponivel: 0,
    quantidade_reservada: 0,
    quantidade_minima: 0
  };
}

temEstoqueBaixo(produtoId: number): boolean {
  const estoque = this.getEstoqueProduto(produtoId);
  return estoque.quantidade_minima > 0 && 
         estoque.quantidade_disponivel <= estoque.quantidade_minima;
}
```

**Adicionar ao HTML de produtos**:
```html
<!-- Adicionar na lista de produtos, após nome/preço -->
<div class="estoque-info">
  <ion-badge [color]="temEstoqueBaixo(produto.id) ? 'danger' : 'success'">
    Estoque: {{ getEstoqueProduto(produto.id).quantidade_disponivel }}
  </ion-badge>
  <ion-badge color="medium" *ngIf="getEstoqueProduto(produto.id).quantidade_reservada > 0">
    Reservado: {{ getEstoqueProduto(produto.id).quantidade_reservada }}
  </ion-badge>
</div>

<!-- Adicionar botões -->
<ion-button fill="clear" size="small" (click)="abrirModalEstoque(produto)">
  <ion-icon name="cube" slot="start"></ion-icon>
  Estoque
</ion-button>

<ion-button fill="clear" size="small" (click)="abrirModalMovimentacao(produto)">
  <ion-icon name="swap-horizontal" slot="start"></ion-icon>
  Movimentar
</ion-button>

<!-- Modal de Estoque -->
<ion-modal [isOpen]="modalEstoqueAberto" (didDismiss)="fecharModalEstoque()">
  <ng-template>
    <ion-header>
      <ion-toolbar>
        <ion-title>Estoque: {{ produtoSelecionadoEstoque?.nome }}</ion-title>
        <ion-buttons slot="end">
          <ion-button (click)="fecharModalEstoque()">
            <ion-icon name="close-circle"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>
    
    <ion-content class="ion-padding" *ngIf="produtoSelecionadoEstoque">
      <ion-card>
        <ion-card-content>
          <h3>Quantidade Disponível</h3>
          <p class="quantidade-grande">{{ produtoSelecionadoEstoque.estoque?.quantidade_disponivel || 0 }}</p>
          
          <h3>Quantidade Reservada</h3>
          <p>{{ produtoSelecionadoEstoque.estoque?.quantidade_reservada || 0 }}</p>
          
          <h3>Estoque Mínimo</h3>
          <ion-item>
            <ion-input 
              type="number"
              [(ngModel)]="produtoSelecionadoEstoque.estoque.quantidade_minima"
              placeholder="Definir mínimo">
            </ion-input>
            <ion-button slot="end" (click)="atualizarEstoqueMinimo()">
              Salvar
            </ion-button>
          </ion-item>
        </ion-card-content>
      </ion-card>
    </ion-content>
  </ng-template>
</ion-modal>

<!-- Modal de Movimentação -->
<ion-modal [isOpen]="modalMovimentacaoAberto" (didDismiss)="fecharModalMovimentacao()">
  <ng-template>
    <ion-header>
      <ion-toolbar>
        <ion-title>Movimentar Estoque</ion-title>
        <ion-buttons slot="end">
          <ion-button (click)="fecharModalMovimentacao()">
            <ion-icon name="close-circle"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>
    
    <ion-content class="ion-padding">
      <ion-card>
        <ion-card-content>
          <ion-item>
            <ion-label position="stacked">Tipo de Movimentação</ion-label>
            <ion-select [(ngModel)]="movimentacaoForm.tipo">
              <ion-select-option value="entrada">Entrada</ion-select-option>
              <ion-select-option value="saida">Saída</ion-select-option>
              <ion-select-option value="ajuste">Ajuste</ion-select-option>
              <ion-select-option value="devolucao">Devolução</ion-select-option>
            </ion-select>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">Quantidade</ion-label>
            <ion-input 
              type="number"
              [(ngModel)]="movimentacaoForm.quantidade"
              min="0">
            </ion-input>
          </ion-item>
          
          <ion-item>
            <ion-label position="stacked">Observações</ion-label>
            <ion-textarea
              [(ngModel)]="movimentacaoForm.observacoes"
              rows="3">
            </ion-textarea>
          </ion-item>
          
          <ion-button expand="block" (click)="registrarMovimentacao()">
            Registrar Movimentação
          </ion-button>
        </ion-card-content>
      </ion-card>
    </ion-content>
  </ng-template>
</ion-modal>
```

---

## 🔗 INTEGRAÇÃO: Criar Pedido a partir de Orçamento

### **Adicionar em**: `src/app/admin/gestao-orcamentos/gestao-orcamentos.page.ts`

```typescript
async criarPedidoDeOrcamento(orcamento: Orcamento) {
  const alert = await this.alertController.create({
    header: 'Criar Pedido',
    message: `Deseja criar um pedido a partir do orçamento #${orcamento.numero_orcamento}?`,
    buttons: [
      {
        text: 'Cancelar',
        role: 'cancel'
      },
      {
        text: 'Criar Pedido',
        handler: () => {
          this.http.post<any>(`${this.apiUrl}/pedidos/from-orcamento/${orcamento.id}`, {}).subscribe({
            next: async (response) => {
              if (response.success) {
                await this.mostrarAlerta('Sucesso', 
                  `Pedido ${response.data.numero_pedido} criado com sucesso!`);
                this.router.navigate(['/admin/gestao-pedidos']);
              }
            },
            error: async (error) => {
              await this.mostrarAlerta('Erro', 'Não foi possível criar o pedido');
            }
          });
        }
      }
    ]
  });
  
  await alert.present();
}
```

### **Adicionar no HTML**:
```html
<!-- No card de orçamento, quando status = 'aprovado' -->
<ion-button 
  *ngIf="orcamento.status === 'aprovado'"
  fill="clear" 
  size="small" 
  color="success"
  (click)="criarPedidoDeOrcamento(orcamento)">
  <ion-icon name="cart" slot="start"></ion-icon>
  Criar Pedido
</ion-button>
```

---

## 📊 RESUMO DE IMPLEMENTAÇÃO

### **Páginas a criar/expandir**:
1. ✅ `/admin/gestao-clientes` - Nova página completa
2. ✅ `/admin/gestao-pedidos` - Nova página completa
3. ✅ `/admin/financeiro` - Nova página com 4 abas
4. ✅ `/produtos` - Expandir com controle de estoque

### **Total de funcionalidades frontend a implementar**:
- 📄 **4 páginas novas/expandidas**
- 🔘 **12 modais** interativos
- 📊 **15 tabs/segmentos**
- 🔍 **8 filtros/buscas**
- ⚡ **50+ botões de ação**
- 📈 **Dashboard financeiro completo**

### **Componentes Ionic necessários**:
- IonCard, IonList, IonItem
- IonSegment, IonSegmentButton
- IonModal, IonAlert
- IonSearchbar, IonBadge
- IonSelect, IonInput, IonTextarea
- IonDatetime, IonButton, IonIcon

---

## 🎨 DESIGN SYSTEM

Todas as páginas usam:
- **Cores**: `--nd-primary: #FF6B00`, `--nd-secondary: #1a1a1a`
- **Border radius**: 12px
- **Shadow**: `0 2px 8px rgba(0,0,0,0.1)`
- **Max-width**: 1400px
- **Gap**: 16px entre elementos

---

## ✅ PRÓXIMOS PASSOS

1. Copiar código das páginas de Leads/Orçamentos como base
2. Adaptar para cada módulo específico
3. Adicionar endpoints corretos da API
4. Implementar validações de formulário
5. Testar todas as funcionalidades
6. Ajustar responsividade

**Tempo estimado**: 4-6 horas de desenvolvimento

---

## 🚀 COMANDO PARA TESTAR

```bash
# Build
ionic build

# Servir aplicação
ionic serve

# Acessar
http://localhost:8100/painel
```

---

**Sistema frontend completo pronto para implementação!** 🎉

