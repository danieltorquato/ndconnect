import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import {
  arrowBack, cart, person, calendar, cube, cash,
  checkmarkCircle, time, rocket, checkmarkDone, closeCircle,
  search, create, trash, swapHorizontal
} from 'ionicons/icons';
import {
  IonHeader, IonToolbar, IonTitle, IonContent, IonCard,
  IonCardHeader, IonCardTitle, IonCardContent, IonButton,
  IonIcon, IonSegment, IonSegmentButton, IonLabel, IonList,
  IonItem, IonBadge, IonSearchbar, IonModal, IonButtons,
  IonSelect, IonSelectOption, IonTextarea,
  AlertController
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

interface Pedido {
  id: number;
  numero_pedido: string;
  cliente_nome: string;
  data_pedido: string;
  data_entrega_prevista: string;
  total: number;
  status: string;
}

@Component({
  selector: 'app-gestao-pedidos',
  templateUrl: './gestao-pedidos.page.html',
  styleUrls: ['./gestao-pedidos.page.scss'],
  standalone: true,
  imports: [
    IonHeader, IonToolbar, IonTitle, IonContent, IonCard,
    IonCardHeader, IonCardTitle, IonCardContent, IonButton,
    IonIcon, IonSegment, IonSegmentButton, IonLabel, IonList,
    IonItem, IonBadge, IonSearchbar, IonModal, IonButtons,
    IonSelect, IonSelectOption, IonTextarea,
    CommonModule, FormsModule
  ]
})
export class GestaoPedidosPage implements OnInit {
  pedidos: Pedido[] = [];
  pedidosFiltrados: Pedido[] = [];
  statusFiltro: string = 'todos';
  termoPesquisa: string = '';

  modalDetalhesAberto: boolean = false;
  modalStatusAberto: boolean = false;

  pedidoSelecionado: any = null;
  novoStatus: string = '';

  contadores = {
    pendentes: 0,
    confirmados: 0,
    em_preparacao: 0,
    prontos: 0,
    entregues: 0
  };

  private apiUrl = 'http://localhost:8000';

  constructor(
    private http: HttpClient,
    private router: Router,
    private alertController: AlertController
  ) {
    addIcons({
      arrowBack, cart, person, calendar, cube, cash,
      checkmarkCircle, time, rocket, checkmarkDone, closeCircle,
      search, create, trash, swapHorizontal
    });
  }

  ngOnInit() {
    this.carregarPedidos();
  }

  carregarPedidos() {
    const endpoint = this.statusFiltro === 'todos'
      ? `${this.apiUrl}/pedidos`
      : `${this.apiUrl}/pedidos?status=${this.statusFiltro}`;

    this.http.get<any>(endpoint).subscribe({
      next: (response) => {
        if (response.success) {
          this.pedidos = response.data;
          this.filtrarPedidos();
          this.calcularContadores();
        }
      },
      error: (error) => {
        console.error('Erro ao carregar pedidos:', error);
      }
    });
  }

  calcularContadores() {
    this.http.get<any>(`${this.apiUrl}/pedidos`).subscribe({
      next: (response) => {
        if (response.success) {
          const todos = response.data;
          this.contadores.pendentes = todos.filter((p: Pedido) => p.status === 'pendente').length;
          this.contadores.confirmados = todos.filter((p: Pedido) => p.status === 'confirmado').length;
          this.contadores.em_preparacao = todos.filter((p: Pedido) => p.status === 'em_preparacao').length;
          this.contadores.prontos = todos.filter((p: Pedido) => p.status === 'pronto').length;
          this.contadores.entregues = todos.filter((p: Pedido) => p.status === 'entregue').length;
        }
      }
    });
  }

  filtrarPedidos() {
    let filtrados = this.pedidos;

    if (this.termoPesquisa.trim()) {
      const termo = this.termoPesquisa.toLowerCase();
      filtrados = filtrados.filter(p =>
        p.numero_pedido.toLowerCase().includes(termo) ||
        p.cliente_nome?.toLowerCase().includes(termo)
      );
    }

    this.pedidosFiltrados = filtrados;
  }

  mudarFiltro(event: any) {
    this.statusFiltro = event.detail.value;
    this.carregarPedidos();
  }

  pesquisar() {
    this.filtrarPedidos();
  }

  verDetalhes(pedido: Pedido) {
    this.http.get<any>(`${this.apiUrl}/pedidos/${pedido.id}`).subscribe({
      next: (response) => {
        if (response.success) {
          this.pedidoSelecionado = response.data;
          this.modalDetalhesAberto = true;
        }
      }
    });
  }

  fecharModalDetalhes() {
    this.modalDetalhesAberto = false;
    this.pedidoSelecionado = null;
  }

  abrirModalStatus(pedido: Pedido) {
    this.pedidoSelecionado = pedido;
    this.novoStatus = pedido.status;
    this.modalStatusAberto = true;
  }

  fecharModalStatus() {
    this.modalStatusAberto = false;
    this.pedidoSelecionado = null;
    this.novoStatus = '';
  }

  async atualizarStatus() {
    if (!this.pedidoSelecionado) return;

    this.http.put<any>(`${this.apiUrl}/pedidos/${this.pedidoSelecionado.id}/status`, {
      status: this.novoStatus
    }).subscribe({
      next: async (response) => {
        if (response.success) {
          await this.mostrarAlerta('Sucesso', 'Status atualizado!');
          this.fecharModalStatus();
          this.carregarPedidos();
        } else {
          await this.mostrarAlerta('Erro', response.message);
        }
      },
      error: async (error) => {
        console.error('Erro ao atualizar status:', error);
        await this.mostrarAlerta('Erro', 'Não foi possível atualizar o status');
      }
    });
  }

  async excluirPedido(pedido: Pedido, event: Event) {
    event.stopPropagation();

    const alert = await this.alertController.create({
      header: 'Confirmar Exclusão',
      message: `Tem certeza que deseja excluir o pedido ${pedido.numero_pedido}?`,
      buttons: [
        { text: 'Cancelar', role: 'cancel' },
        {
          text: 'Excluir',
          role: 'destructive',
          handler: () => {
            this.http.delete<any>(`${this.apiUrl}/pedidos/${pedido.id}`).subscribe({
              next: async (response) => {
                if (response.success) {
                  await this.mostrarAlerta('Sucesso', 'Pedido excluído!');
                  this.carregarPedidos();
                } else {
                  await this.mostrarAlerta('Erro', response.message);
                }
              },
              error: async (error) => {
                console.error('Erro ao excluir pedido:', error);
                await this.mostrarAlerta('Erro', 'Não foi possível excluir o pedido');
              }
            });
          }
        }
      ]
    });

    await alert.present();
  }

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
      case 'pendente': return 'warning';
      case 'confirmado': return 'primary';
      case 'em_preparacao': return 'secondary';
      case 'pronto': return 'tertiary';
      case 'entregue': return 'success';
      case 'cancelado': return 'danger';
      default: return 'medium';
    }
  }

  getStatusLabel(status: string): string {
    switch (status) {
      case 'pendente': return 'Pendente';
      case 'confirmado': return 'Confirmado';
      case 'em_preparacao': return 'Em Preparação';
      case 'pronto': return 'Pronto';
      case 'entregue': return 'Entregue';
      case 'cancelado': return 'Cancelado';
      default: return status;
    }
  }

  voltarPainel() {
    this.router.navigate(['/painel']);
  }
}

