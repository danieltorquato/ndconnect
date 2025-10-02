import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import {
  arrowBack, cash, card, trendingUp, trendingDown, wallet,
  calendar, checkmarkCircle, closeCircle, search, statsChart,
  addCircle, documentText
} from 'ionicons/icons';
import {
  IonHeader, IonToolbar, IonTitle, IonContent, IonCard,
  IonCardHeader, IonCardTitle, IonCardContent, IonButton,
  IonIcon, IonSegment, IonSegmentButton, IonLabel, IonList,
  IonItem, IonBadge, IonSearchbar, IonModal, IonButtons,
  IonInput, IonSelect, IonSelectOption, IonDatetime,
  AlertController
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-financeiro',
  templateUrl: './financeiro.page.html',
  styleUrls: ['./financeiro.page.scss'],
  standalone: true,
  imports: [
    IonHeader, IonToolbar, IonTitle, IonContent, IonCard,
    IonCardHeader, IonCardTitle, IonCardContent, IonButton,
    IonIcon, IonSegment, IonSegmentButton, IonLabel, IonList,
    IonItem, IonBadge, IonSearchbar, IonModal, IonButtons,
    IonInput, IonSelect, IonSelectOption, IonDatetime,
    CommonModule, FormsModule
  ]
})
export class FinanceiroPage implements OnInit {
  abaAtiva: string = 'dashboard';

  // Dashboard
  dashboardFinanceiro: any = {
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

  // Modais
  modalPagamentoReceberAberto: boolean = false;
  modalPagamentoPagarAberto: boolean = false;
  modalNovaContaReceberAberto: boolean = false;
  modalNovaContaPagarAberto: boolean = false;

  contaSelecionada: any = null;

  // Formulários
  pagamentoForm = {
    data_pagamento: '',
    valor_pago: 0,
    forma_pagamento: 'Dinheiro'
  };

  novaContaReceberForm = {
    cliente_id: 0,
    descricao: '',
    valor: 0,
    data_vencimento: '',
    forma_pagamento: 'Boleto',
    observacoes: ''
  };

  novaContaPagarForm = {
    fornecedor: '',
    descricao: '',
    categoria: 'fornecedor',
    valor: 0,
    data_vencimento: '',
    forma_pagamento: 'Boleto',
    observacoes: ''
  };

  private apiUrl = 'http://localhost:8000';

  constructor(
    private http: HttpClient,
    private router: Router,
    private alertController: AlertController
  ) {
    addIcons({
      arrowBack, cash, card, trendingUp, trendingDown, wallet,
      calendar, checkmarkCircle, closeCircle, search, statsChart,
      addCircle, documentText
    });
  }

  ngOnInit() {
    this.inicializarPeriodo();
    this.carregarDashboard();
    this.carregarContasReceber();
    this.carregarContasPagar();
    this.carregarFluxoCaixa();
  }

  inicializarPeriodo() {
    const hoje = new Date();
    const primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    const ultimoDia = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);

    this.periodoInicio = primeiroDia.toISOString().split('T')[0];
    this.periodoFim = ultimoDia.toISOString().split('T')[0];
  }

  mudarAba(event: any) {
    this.abaAtiva = event.detail.value;
  }

  // DASHBOARD
  carregarDashboard() {
    this.http.get<any>(`${this.apiUrl}/financeiro/dashboard`).subscribe({
      next: (response) => {
        if (response.success) {
          this.dashboardFinanceiro = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar dashboard:', error);
      }
    });
  }

  // CONTAS A RECEBER
  carregarContasReceber() {
    const endpoint = this.statusReceberFiltro === 'todos'
      ? `${this.apiUrl}/financeiro/receber`
      : `${this.apiUrl}/financeiro/receber?status=${this.statusReceberFiltro}`;

    this.http.get<any>(endpoint).subscribe({
      next: (response) => {
        if (response.success) {
          this.contasReceber = response.data;
          this.contasReceberFiltradas = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar contas a receber:', error);
      }
    });
  }

  filtrarContasReceber(event: any) {
    this.statusReceberFiltro = event.detail.value;
    this.carregarContasReceber();
  }

  abrirModalPagamentoReceber(conta: any) {
    this.contaSelecionada = conta;
    this.pagamentoForm = {
      data_pagamento: new Date().toISOString().split('T')[0],
      valor_pago: conta.valor,
      forma_pagamento: conta.forma_pagamento || 'PIX'
    };
    this.modalPagamentoReceberAberto = true;
  }

  fecharModalPagamentoReceber() {
    this.modalPagamentoReceberAberto = false;
    this.contaSelecionada = null;
  }

  async registrarPagamentoReceber() {
    if (!this.contaSelecionada) return;

    this.http.put<any>(
      `${this.apiUrl}/financeiro/receber/${this.contaSelecionada.id}/pagar`,
      this.pagamentoForm
    ).subscribe({
      next: async (response) => {
        if (response.success) {
          await this.mostrarAlerta('Sucesso', 'Pagamento registrado!');
          this.fecharModalPagamentoReceber();
          this.carregarContasReceber();
          this.carregarDashboard();
          this.carregarFluxoCaixa();
        } else {
          await this.mostrarAlerta('Erro', response.message);
        }
      },
      error: async (error) => {
        console.error('Erro ao registrar pagamento:', error);
        await this.mostrarAlerta('Erro', 'Não foi possível registrar o pagamento');
      }
    });
  }

  // CONTAS A PAGAR
  carregarContasPagar() {
    this.http.get<any>(`${this.apiUrl}/financeiro/pagar`).subscribe({
      next: (response) => {
        if (response.success) {
          this.contasPagar = response.data;
          this.filtrarContasPagar();
        }
      },
      error: (error) => {
        console.error('Erro ao carregar contas a pagar:', error);
      }
    });
  }

  filtrarContasPagar() {
    if (this.statusPagarFiltro === 'todos') {
      this.contasPagarFiltradas = this.contasPagar;
    } else {
      this.contasPagarFiltradas = this.contasPagar.filter(c => c.status === this.statusPagarFiltro);
    }
  }

  mudarFiltroPagar(event: any) {
    this.statusPagarFiltro = event.detail.value;
    this.filtrarContasPagar();
  }

  abrirModalPagamentoPagar(conta: any) {
    this.contaSelecionada = conta;
    this.pagamentoForm = {
      data_pagamento: new Date().toISOString().split('T')[0],
      valor_pago: conta.valor,
      forma_pagamento: conta.forma_pagamento || 'PIX'
    };
    this.modalPagamentoPagarAberto = true;
  }

  fecharModalPagamentoPagar() {
    this.modalPagamentoPagarAberto = false;
    this.contaSelecionada = null;
  }

  async registrarPagamentoPagar() {
    if (!this.contaSelecionada) return;

    this.http.put<any>(
      `${this.apiUrl}/financeiro/pagar/${this.contaSelecionada.id}/pagar`,
      this.pagamentoForm
    ).subscribe({
      next: async (response) => {
        if (response.success) {
          await this.mostrarAlerta('Sucesso', 'Pagamento registrado!');
          this.fecharModalPagamentoPagar();
          this.carregarContasPagar();
          this.carregarDashboard();
          this.carregarFluxoCaixa();
        } else {
          await this.mostrarAlerta('Erro', response.message);
        }
      },
      error: async (error) => {
        console.error('Erro ao registrar pagamento:', error);
        await this.mostrarAlerta('Erro', 'Não foi possível registrar o pagamento');
      }
    });
  }

  // FLUXO DE CAIXA
  carregarFluxoCaixa() {
    const url = `${this.apiUrl}/financeiro/fluxo-caixa?inicio=${this.periodoInicio}&fim=${this.periodoFim}`;

    this.http.get<any>(url).subscribe({
      next: (response) => {
        if (response.success) {
          this.fluxoCaixa = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar fluxo de caixa:', error);
      }
    });
  }

  atualizarPeriodo() {
    this.carregarFluxoCaixa();
  }

  // HELPERS
  async mostrarAlerta(header: string, message: string) {
    const alert = await this.alertController.create({
      header: header,
      message: message,
      buttons: ['OK']
    });
    await alert.present();
  }

  getStatusColor(status: string): string {
    switch (status) {
      case 'pago': return 'success';
      case 'pendente': return 'warning';
      case 'atrasado': return 'danger';
      default: return 'medium';
    }
  }

  getStatusLabel(status: string): string {
    switch (status) {
      case 'pago': return 'Pago';
      case 'pendente': return 'Pendente';
      case 'atrasado': return 'Atrasado';
      default: return status;
    }
  }

  getTipoColor(tipo: string): string {
    return tipo === 'entrada' ? 'success' : 'danger';
  }

  getTipoIcon(tipo: string): string {
    return tipo === 'entrada' ? 'trending-up' : 'trending-down';
  }

  voltarPainel() {
    this.router.navigate(['/painel']);
  }
}

