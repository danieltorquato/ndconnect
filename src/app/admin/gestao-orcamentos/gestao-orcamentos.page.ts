import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment';
import { addIcons } from 'ionicons';
import {
  arrowBack, documentText, checkmarkCircle, closeCircle,
  timeOutline, cashOutline, search, filterOutline, eye,
  trash, create, cart, calendar
} from 'ionicons/icons';
import {
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardContent,
  IonButton,
  IonIcon,
  IonSegment,
  IonSegmentButton,
  IonLabel,
  IonList,
  IonItem,
  IonBadge,
  IonSearchbar,
  IonSelect,
  IonSelectOption,
  IonModal,
  IonButtons,
  IonInput,
  IonTextarea,
  AlertController
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

interface Orcamento {
  id: number;
  numero_orcamento: string;
  cliente_nome: string;
  email: string;
  telefone: string;
  data_orcamento: string;
  data_validade: string;
  subtotal: number;
  desconto: number;
  total: number;
  status: string;
  observacoes?: string;
}

@Component({
  selector: 'app-gestao-orcamentos',
  templateUrl: './gestao-orcamentos.page.html',
  styleUrls: ['./gestao-orcamentos.page.scss'],
  standalone: true,
  imports: [
    IonHeader,
    IonToolbar,
    IonTitle,
    IonContent,
    IonCard,
    IonCardHeader,
    IonCardTitle,
    IonCardContent,
    IonButton,
    IonIcon,
    IonSegment,
    IonSegmentButton,
    IonLabel,
    IonList,
    IonItem,
    IonBadge,
    IonSearchbar,
    IonSelect,
    IonSelectOption,
    IonModal,
    IonButtons,
    IonInput,
    IonTextarea,
    CommonModule,
    FormsModule
  ]
})
export class GestaoOrcamentosPage implements OnInit {
  orcamentos: Orcamento[] = [];
  orcamentosFiltrados: Orcamento[] = [];
  statusFiltro: string = 'todos';
  termoPesquisa: string = '';

  // Modal de detalhes
  modalDetalhesAberto: boolean = false;
  orcamentoSelecionado: any = null;

  // Modal de atualização de status
  modalStatusAberto: boolean = false;
  novoStatus: string = '';
  observacaoStatus: string = '';

  // Contadores por status
  contadores = {
    pendentes: 0,
    aprovados: 0,
    vendidos: 0,
    rejeitados: 0,
    expirados: 0
  };

  private apiUrl = environment.apiUrl;

  constructor(
    private http: HttpClient,
    private router: Router,
    private alertController: AlertController
  ) {
    addIcons({
      arrowBack, documentText, checkmarkCircle, closeCircle,
      timeOutline, cashOutline, search, filterOutline, eye,
      trash, create, cart, calendar
    });
  }

  ngOnInit() {
    this.carregarOrcamentos();
  }

  carregarOrcamentos() {
    const endpoint = this.statusFiltro === 'todos'
      ? `${this.apiUrl}/orcamentos`
      : `${this.apiUrl}/orcamentos?status=${this.statusFiltro}`;

    this.http.get<any>(endpoint).subscribe({
      next: (response) => {
        if (response.success) {
          this.orcamentos = response.data;
          this.filtrarOrcamentos();
          this.calcularContadores();
        }
      },
      error: (error) => {
        console.error('Erro ao carregar orçamentos:', error);
      }
    });
  }

  calcularContadores() {
    this.http.get<any>(`${this.apiUrl}/orcamentos`).subscribe({
      next: (response) => {
        if (response.success) {
          const todos = response.data;
          this.contadores.pendentes = todos.filter((o: Orcamento) => o.status === 'pendente').length;
          this.contadores.aprovados = todos.filter((o: Orcamento) => o.status === 'aprovado').length;
          this.contadores.vendidos = todos.filter((o: Orcamento) => o.status === 'vendido').length;
          this.contadores.rejeitados = todos.filter((o: Orcamento) => o.status === 'rejeitado').length;
          this.contadores.expirados = todos.filter((o: Orcamento) => o.status === 'expirado').length;
        }
      }
    });
  }

  filtrarOrcamentos() {
    let filtrados = this.orcamentos;

    if (this.termoPesquisa.trim()) {
      const termo = this.termoPesquisa.toLowerCase();
      filtrados = filtrados.filter(o =>
        o.numero_orcamento.toLowerCase().includes(termo) ||
        o.cliente_nome.toLowerCase().includes(termo) ||
        o.email?.toLowerCase().includes(termo) ||
        o.telefone?.includes(termo)
      );
    }

    this.orcamentosFiltrados = filtrados;
  }

  mudarFiltro(event: any) {
    this.statusFiltro = event.detail.value;
    this.carregarOrcamentos();
  }

  pesquisar() {
    this.filtrarOrcamentos();
  }

  verDetalhes(orcamento: Orcamento) {
    this.http.get<any>(`${this.apiUrl}/orcamentos/${orcamento.id}`).subscribe({
      next: (response) => {
        if (response.success) {
          this.orcamentoSelecionado = response.data;
          this.modalDetalhesAberto = true;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar detalhes:', error);
      }
    });
  }

  fecharModalDetalhes() {
    this.modalDetalhesAberto = false;
    this.orcamentoSelecionado = null;
  }

  abrirModalStatus(orcamento: Orcamento) {
    this.orcamentoSelecionado = orcamento;
    this.novoStatus = orcamento.status;
    this.observacaoStatus = '';
    this.modalStatusAberto = true;
  }

  fecharModalStatus() {
    this.modalStatusAberto = false;
    this.orcamentoSelecionado = null;
    this.novoStatus = '';
    this.observacaoStatus = '';
  }

  async atualizarStatus() {
    if (!this.orcamentoSelecionado || !this.novoStatus) {
      return;
    }

    const dados = {
      status: this.novoStatus,
      observacao: this.observacaoStatus
    };

    this.http.put<any>(`${this.apiUrl}/orcamentos/${this.orcamentoSelecionado.id}/status`, dados).subscribe({
      next: async (response) => {
        if (response.success) {
          await this.mostrarAlerta('Sucesso', 'Status atualizado com sucesso!');
          this.fecharModalStatus();
          this.carregarOrcamentos();
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

  async excluirOrcamento(orcamento: Orcamento, event: Event) {
    event.stopPropagation();

    const alert = await this.alertController.create({
      header: 'Confirmar Exclusão',
      message: `Tem certeza que deseja excluir o orçamento ${orcamento.numero_orcamento}?`,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel'
        },
        {
          text: 'Excluir',
          role: 'destructive',
          handler: () => {
            this.http.delete<any>(`${this.apiUrl}/orcamentos/${orcamento.id}`).subscribe({
              next: async (response) => {
                if (response.success) {
                  await this.mostrarAlerta('Sucesso', 'Orçamento excluído com sucesso!');
                  this.carregarOrcamentos();
                } else {
                  await this.mostrarAlerta('Erro', response.message);
                }
              },
              error: async (error) => {
                console.error('Erro ao excluir:', error);
                await this.mostrarAlerta('Erro', 'Não foi possível excluir o orçamento');
              }
            });
          }
        }
      ]
    });

    await alert.present();
  }

  visualizarPDF(orcamento: Orcamento) {
    const url = `${this.apiUrl}/simple_pdf.php?id=${orcamento.id}`;
    window.open(url, '_blank');
  }

  async vincularPedido(orcamento: Orcamento) {
    const alert = await this.alertController.create({
      header: 'Vincular Pedido',
      message: 'Digite o ID do pedido para vincular:',
      inputs: [
        {
          name: 'pedidoId',
          type: 'number',
          placeholder: 'ID do Pedido'
        }
      ],
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel'
        },
        {
          text: 'Vincular',
          handler: (data) => {
            if (data.pedidoId) {
              this.http.post<any>(`${this.apiUrl}/orcamentos/${orcamento.id}/vincular-pedido`, {
                pedido_id: data.pedidoId
              }).subscribe({
                next: async (response) => {
                  if (response.success) {
                    await this.mostrarAlerta('Sucesso', 'Pedido vinculado com sucesso!');
                    this.carregarOrcamentos();
                  } else {
                    await this.mostrarAlerta('Erro', response.message);
                  }
                },
                error: async (error) => {
                  console.error('Erro ao vincular pedido:', error);
                  await this.mostrarAlerta('Erro', 'Não foi possível vincular o pedido');
                }
              });
            }
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
      case 'aprovado': return 'success';
      case 'vendido': return 'primary';
      case 'rejeitado': return 'danger';
      case 'expirado': return 'medium';
      default: return 'medium';
    }
  }

  getStatusLabel(status: string): string {
    switch (status) {
      case 'pendente': return 'Pendente';
      case 'aprovado': return 'Aprovado';
      case 'vendido': return 'Vendido';
      case 'rejeitado': return 'Rejeitado';
      case 'expirado': return 'Expirado';
      default: return status;
    }
  }

  voltarPainel() {
    this.router.navigate(['/painel']);
  }
}

