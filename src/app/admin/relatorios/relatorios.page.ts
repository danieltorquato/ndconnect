import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment';
import { addIcons } from 'ionicons';
import {
  arrowBack, statsChart, trendingUp, trendingDown,
  cart, person, cube, cash, calendar, pieChart, barChart, checkmarkCircle } from 'ionicons/icons';
import {
  IonHeader, IonToolbar, IonTitle, IonContent, IonCard,
  IonCardHeader, IonCardTitle, IonCardContent, IonButton,
  IonIcon, IonSegment, IonSegmentButton, IonLabel, IonList,
  IonItem, IonBadge, IonButtons, IonInput, IonSelect,
  IonSelectOption
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-relatorios',
  templateUrl: './relatorios.page.html',
  styleUrls: ['./relatorios.page.scss'],
  standalone: true,
  imports: [
    IonHeader, IonToolbar, IonTitle, IonContent, IonCard,
    IonCardHeader, IonCardTitle, IonCardContent, IonButton,
    IonIcon, IonSegment, IonSegmentButton, IonLabel, IonList,
    IonItem, IonBadge, IonButtons, IonInput, IonSelect,
    IonSelectOption, CommonModule, FormsModule
  ]
})
export class RelatoriosPage implements OnInit {
  abaAtiva: string = 'dashboard';

  // Dashboard Executivo
  dashboardExecutivo: any = {
    vendas_mes: {},
    variacao_mes_anterior: 0,
    taxa_conversao: {},
    top_produtos: [],
    top_clientes: [],
    funil_vendas: {}
  };

  // Vendas
  vendasPeriodo: any = {
    vendas_por_dia: [],
    resumo: {}
  };
  periodoInicio: string = '';
  periodoFim: string = '';

  // Produtos
  topProdutos: any[] = [];
  produtosPorCategoria: any[] = [];

  // Clientes
  topClientes: any[] = [];

  // Funil
  funilVendas: any = {};

  // Metas
  metasVsRealizado: any[] = [];
  mesRelatorio: number = new Date().getMonth() + 1;
  anoRelatorio: number = new Date().getFullYear();

  private apiUrl = environment.apiUrl;

  constructor(
    private http: HttpClient,
    private router: Router
  ) {
    addIcons({arrowBack,cart,cash,person,statsChart,checkmarkCircle,trendingUp,trendingDown,cube,calendar,pieChart,barChart});
  }

  ngOnInit() {
    this.inicializarPeriodo();
    this.carregarDashboardExecutivo();
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

    switch(this.abaAtiva) {
      case 'vendas':
        this.carregarVendasPeriodo();
        break;
      case 'produtos':
        this.carregarTopProdutos();
        this.carregarProdutosPorCategoria();
        break;
      case 'clientes':
        this.carregarTopClientes();
        break;
      case 'funil':
        this.carregarFunilVendas();
        break;
      case 'metas':
        this.carregarMetasVsRealizado();
        break;
    }
  }

  // DASHBOARD EXECUTIVO
  carregarDashboardExecutivo() {
    const url = `${this.apiUrl}/relatorios/dashboard-executivo?mes=${this.mesRelatorio}&ano=${this.anoRelatorio}`;

    this.http.get<any>(url).subscribe({
      next: (response) => {
        if (response.success) {
          this.dashboardExecutivo = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar dashboard:', error);
      }
    });
  }

  // VENDAS
  carregarVendasPeriodo() {
    const url = `${this.apiUrl}/relatorios/vendas/periodo?inicio=${this.periodoInicio}&fim=${this.periodoFim}`;

    this.http.get<any>(url).subscribe({
      next: (response) => {
        if (response.success) {
          this.vendasPeriodo = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar vendas:', error);
      }
    });
  }

  atualizarPeriodo() {
    this.carregarVendasPeriodo();
  }

  // PRODUTOS
  carregarTopProdutos() {
    const url = `${this.apiUrl}/relatorios/produtos/mais-vendidos?limite=10`;

    this.http.get<any>(url).subscribe({
      next: (response) => {
        if (response.success) {
          this.topProdutos = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar produtos:', error);
      }
    });
  }

  carregarProdutosPorCategoria() {
    const url = `${this.apiUrl}/relatorios/produtos/por-categoria`;

    this.http.get<any>(url).subscribe({
      next: (response) => {
        if (response.success) {
          this.produtosPorCategoria = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar categorias:', error);
      }
    });
  }

  // CLIENTES
  carregarTopClientes() {
    const url = `${this.apiUrl}/relatorios/clientes/top?limite=10`;

    this.http.get<any>(url).subscribe({
      next: (response) => {
        if (response.success) {
          this.topClientes = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar clientes:', error);
      }
    });
  }

  // FUNIL
  carregarFunilVendas() {
    const url = `${this.apiUrl}/relatorios/funil-vendas`;

    this.http.get<any>(url).subscribe({
      next: (response) => {
        if (response.success) {
          this.funilVendas = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar funil:', error);
      }
    });
  }

  // METAS
  carregarMetasVsRealizado() {
    const url = `${this.apiUrl}/relatorios/metas?mes=${this.mesRelatorio}&ano=${this.anoRelatorio}`;

    this.http.get<any>(url).subscribe({
      next: (response) => {
        if (response.success) {
          this.metasVsRealizado = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar metas:', error);
      }
    });
  }

  atualizarMetas() {
    this.carregarMetasVsRealizado();
  }

  getVariacaoColor(variacao: number): string {
    return variacao >= 0 ? 'success' : 'danger';
  }

  getVariacaoIcon(variacao: number): string {
    return variacao >= 0 ? 'trending-up' : 'trending-down';
  }

  voltarPainel() {
    this.router.navigate(['/painel']);
  }
}

