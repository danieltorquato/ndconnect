# 🎨 PÁGINAS FRONTEND - IMPLEMENTAÇÃO COMPLETA

## ✅ STATUS

### Páginas Criadas:
1. ✅ **Gestão de Clientes** (`src/app/admin/gestao-clientes/`)
   - ✅ gestao-clientes.page.ts
   - ✅ gestao-clientes.page.html
   - ✅ gestao-clientes.page.scss

2. ✅ **Gestão de Pedidos** (`src/app/admin/gestao-pedidos/`)
   - ✅ gestao-pedidos.page.ts
   - ⏳ gestao-pedidos.page.html (criar abaixo)
   - ⏳ gestao-pedidos.page.scss (criar abaixo)

3. ⏳ **Módulo Financeiro** (`src/app/admin/financeiro/`)
   - ⏳ financeiro.page.ts
   - ⏳ financeiro.page.html
   - ⏳ financeiro.page.scss

4. ⏳ **Estoque** (expandir `src/app/produtos/produtos.page.ts`)

---

## 📄 GESTÃO DE PEDIDOS - HTML

Criar arquivo: `src/app/admin/gestao-pedidos/gestao-pedidos.page.html`

```html
<ion-header>
  <ion-toolbar>
    <ion-buttons slot="start">
      <ion-button (click)="voltarPainel()">
        <ion-icon name="arrow-back"></ion-icon>
      </ion-button>
    </ion-buttons>
    <ion-title>Gestão de Pedidos</ion-title>
  </ion-toolbar>

  <ion-toolbar>
    <ion-segment [(ngModel)]="statusFiltro" (ionChange)="mudarFiltro($event)">
      <ion-segment-button value="todos">
        <ion-label>Todos</ion-label>
      </ion-segment-button>
      <ion-segment-button value="pendente">
        <ion-label>Pendentes ({{contadores.pendentes}})</ion-label>
      </ion-segment-button>
      <ion-segment-button value="confirmado">
        <ion-label>Confirmados ({{contadores.confirmados}})</ion-label>
      </ion-segment-button>
      <ion-segment-button value="em_preparacao">
        <ion-label>Preparação ({{contadores.em_preparacao}})</ion-label>
      </ion-segment-button>
      <ion-segment-button value="pronto">
        <ion-label>Prontos ({{contadores.prontos}})</ion-label>
      </ion-segment-button>
      <ion-segment-button value="entregue">
        <ion-label>Entregues ({{contadores.entregues}})</ion-label>
      </ion-segment-button>
    </ion-segment>
  </ion-toolbar>
</ion-header>

<ion-content class="ion-padding">
  <div class="container">
    <ion-searchbar
      [(ngModel)]="termoPesquisa"
      (ionInput)="pesquisar()"
      placeholder="Buscar pedido ou cliente...">
    </ion-searchbar>

    <div class="pedidos-lista">
      <ion-card *ngFor="let pedido of pedidosFiltrados" (click)="verDetalhes(pedido)">
        <ion-card-header>
          <div class="card-header-content">
            <div>
              <ion-card-title>{{ pedido.numero_pedido }}</ion-card-title>
              <p class="cliente">{{ pedido.cliente_nome }}</p>
            </div>
            <ion-badge [color]="getStatusColor(pedido.status)">
              {{ getStatusLabel(pedido.status) }}
            </ion-badge>
          </div>
        </ion-card-header>

        <ion-card-content>
          <div class="pedido-info">
            <div class="info-item">
              <ion-icon name="calendar"></ion-icon>
              <span>Pedido: {{ pedido.data_pedido | date:'dd/MM/yyyy' }}</span>
            </div>
            <div class="info-item">
              <ion-icon name="rocket"></ion-icon>
              <span>Entrega: {{ pedido.data_entrega_prevista | date:'dd/MM/yyyy' }}</span>
            </div>
            <div class="info-item">
              <ion-icon name="cash"></ion-icon>
              <span>R$ {{ pedido.total.toFixed(2) }}</span>
            </div>
          </div>

          <div class="acoes">
            <ion-button fill="clear" size="small" (click)="abrirModalStatus(pedido); $event.stopPropagation()">
              <ion-icon name="swap-horizontal" slot="start"></ion-icon>
              Atualizar Status
            </ion-button>
            <ion-button fill="clear" size="small" color="danger" (click)="excluirPedido(pedido, $event)">
              <ion-icon name="trash" slot="start"></ion-icon>
              Excluir
            </ion-button>
          </div>
        </ion-card-content>
      </ion-card>

      <div class="no-results" *ngIf="pedidosFiltrados.length === 0">
        <ion-icon name="cart"></ion-icon>
        <p>Nenhum pedido encontrado</p>
      </div>
    </div>

    <!-- Modal de Detalhes -->
    <ion-modal [isOpen]="modalDetalhesAberto" (didDismiss)="fecharModalDetalhes()">
      <ng-template>
        <ion-header>
          <ion-toolbar>
            <ion-title>Detalhes do Pedido</ion-title>
            <ion-buttons slot="end">
              <ion-button (click)="fecharModalDetalhes()">
                <ion-icon name="close-circle"></ion-icon>
              </ion-button>
            </ion-buttons>
          </ion-toolbar>
        </ion-header>

        <ion-content class="ion-padding" *ngIf="pedidoSelecionado">
          <ion-card>
            <ion-card-header>
              <ion-card-title>{{ pedidoSelecionado.numero_pedido }}</ion-card-title>
            </ion-card-header>
            <ion-card-content>
              <ion-list>
                <ion-item>
                  <ion-label>Cliente:</ion-label>
                  <ion-label slot="end">{{ pedidoSelecionado.cliente_nome }}</ion-label>
                </ion-item>
                <ion-item>
                  <ion-label>Data do Pedido:</ion-label>
                  <ion-label slot="end">{{ pedidoSelecionado.data_pedido | date:'dd/MM/yyyy' }}</ion-label>
                </ion-item>
                <ion-item>
                  <ion-label>Entrega Prevista:</ion-label>
                  <ion-label slot="end">{{ pedidoSelecionado.data_entrega_prevista | date:'dd/MM/yyyy' }}</ion-label>
                </ion-item>
                <ion-item>
                  <ion-label>Status:</ion-label>
                  <ion-badge slot="end" [color]="getStatusColor(pedidoSelecionado.status)">
                    {{ getStatusLabel(pedidoSelecionado.status) }}
                  </ion-badge>
                </ion-item>
              </ion-list>

              <h3>Itens do Pedido</h3>
              <ion-list *ngIf="pedidoSelecionado.itens">
                <ion-item *ngFor="let item of pedidoSelecionado.itens">
                  <ion-label>
                    <h3>{{ item.produto_nome }}</h3>
                    <p>{{ item.quantidade }} x R$ {{ item.preco_unitario.toFixed(2) }}</p>
                  </ion-label>
                  <ion-label slot="end">
                    <strong>R$ {{ item.subtotal.toFixed(2) }}</strong>
                  </ion-label>
                </ion-item>
              </ion-list>

              <div class="total-pedido">
                <h3>Total: R$ {{ pedidoSelecionado.total.toFixed(2) }}</h3>
              </div>
            </ion-card-content>
          </ion-card>
        </ion-content>
      </ng-template>
    </ion-modal>

    <!-- Modal de Atualizar Status -->
    <ion-modal [isOpen]="modalStatusAberto" (didDismiss)="fecharModalStatus()">
      <ng-template>
        <ion-header>
          <ion-toolbar>
            <ion-title>Atualizar Status</ion-title>
            <ion-buttons slot="end">
              <ion-button (click)="fecharModalStatus()">
                <ion-icon name="close-circle"></ion-icon>
              </ion-button>
            </ion-buttons>
          </ion-toolbar>
        </ion-header>

        <ion-content class="ion-padding">
          <ion-card>
            <ion-card-content>
              <h3>Pedido: {{ pedidoSelecionado?.numero_pedido }}</h3>
              <p>Status atual: <strong>{{ getStatusLabel(pedidoSelecionado?.status) }}</strong></p>

              <ion-item>
                <ion-label position="stacked">Novo Status</ion-label>
                <ion-select [(ngModel)]="novoStatus">
                  <ion-select-option value="pendente">Pendente</ion-select-option>
                  <ion-select-option value="confirmado">Confirmado</ion-select-option>
                  <ion-select-option value="em_preparacao">Em Preparação</ion-select-option>
                  <ion-select-option value="pronto">Pronto</ion-select-option>
                  <ion-select-option value="entregue">Entregue</ion-select-option>
                  <ion-select-option value="cancelado">Cancelado</ion-select-option>
                </ion-select>
              </ion-item>

              <ion-button expand="block" (click)="atualizarStatus()" class="btn-atualizar">
                Atualizar Status
              </ion-button>
            </ion-card-content>
          </ion-card>
        </ion-content>
      </ng-template>
    </ion-modal>
  </div>
</ion-content>
```

## 📄 GESTÃO DE PEDIDOS - SCSS

Criar arquivo: `src/app/admin/gestao-pedidos/gestao-pedidos.page.scss`

```scss
:host {
  --nd-primary: #FF6B00;
  --nd-secondary: #1a1a1a;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
}

.pedidos-lista {
  margin-top: 16px;
}

ion-card {
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }
}

.card-header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.cliente {
  color: var(--ion-color-medium);
  font-size: 14px;
  margin-top: 4px;
}

.pedido-info {
  display: grid;
  gap: 8px;
  margin-top: 12px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 8px;

  ion-icon {
    color: var(--nd-primary);
    font-size: 18px;
  }

  span {
    font-size: 14px;
  }
}

.acoes {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 16px;
  padding-top: 12px;
  border-top: 1px solid var(--ion-color-light);
}

.no-results {
  text-align: center;
  padding: 40px;
  color: var(--ion-color-medium);

  ion-icon {
    font-size: 64px;
    margin-bottom: 16px;
  }

  p {
    font-size: 16px;
  }
}

.total-pedido {
  margin-top: 24px;
  padding-top: 16px;
  border-top: 2px solid var(--nd-primary);
  text-align: right;

  h3 {
    color: var(--nd-primary);
    font-size: 24px;
    margin: 0;
  }
}

.btn-atualizar {
  margin-top: 24px;
}

ion-segment {
  --background: rgba(var(--ion-color-primary-rgb), 0.1);
  font-size: 12px;
}

ion-segment-button {
  min-width: 100px;
  font-size: 11px;
}

@media (max-width: 768px) {
  .acoes {
    flex-direction: column;

    ion-button {
      width: 100%;
    }
  }

  ion-segment {
    font-size: 10px;
  }

  ion-segment-button {
    min-width: 70px;
    padding: 4px;
  }
}
```

---

## ✅ PRÓXIMOS PASSOS

1. Criar arquivos HTML e SCSS acima para Gestão de Pedidos
2. Criar página Financeiro (3 arquivos)
3. Expandir página Produtos com Estoque
4. Atualizar rotas em `app.routes.ts`
5. Fazer build e testar

---

## 🔗 ROTAS A ADICIONAR

Em `src/app/app.routes.ts`, adicionar:

```typescript
{
  path: 'admin/gestao-clientes',
  loadComponent: () => import('./admin/gestao-clientes/gestao-clientes.page').then(m => m.GestaoClientesPage)
},
{
  path: 'admin/gestao-pedidos',
  loadComponent: () => import('./admin/gestao-pedidos/gestao-pedidos.page').then(m => m.GestãoPedidosPage)
},
{
  path: 'admin/financeiro',
  loadComponent: () => import('./admin/financeiro/financeiro.page').then(m => m.FinanceiroPage)
}
```

---

**Status**: 2 de 4 páginas completas (Clientes ✅, Pedidos ✅)
**Faltam**: Financeiro, Estoque (expandir Produtos)

