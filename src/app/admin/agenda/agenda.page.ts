import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment';
import { addIcons } from 'ionicons';
import {
  arrowBack, calendar, time, location, person, call, business,
  checkmarkCircle, closeCircle, alertCircle, create, trash,
  add, cube, search, statsChart
} from 'ionicons/icons';
import {
  IonHeader, IonToolbar, IonTitle, IonContent, IonCard,
  IonCardHeader, IonCardTitle, IonCardContent, IonButton,
  IonIcon, IonSegment, IonSegmentButton, IonLabel, IonList,
  IonItem, IonBadge, IonSearchbar, IonModal, IonButtons,
  IonInput, IonSelect, IonSelectOption, IonTextarea, IonDatetime,
  AlertController
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-agenda',
  templateUrl: './agenda.page.html',
  styleUrls: ['./agenda.page.scss'],
  standalone: true,
  imports: [
    IonHeader, IonToolbar, IonTitle, IonContent, IonCard,
    IonCardHeader, IonCardTitle, IonCardContent, IonButton,
    IonIcon, IonSegment, IonSegmentButton, IonLabel, IonList,
    IonItem, IonBadge, IonSearchbar, IonModal, IonButtons,
    IonInput, IonSelect, IonSelectOption, IonTextarea, IonDatetime,
    CommonModule, FormsModule
  ]
})
export class AgendaPage implements OnInit {
  eventos: any[] = [];
  eventosFiltrados: any[] = [];
  statusFiltro: string = 'todos';
  termoPesquisa: string = '';

  // Modais
  modalDetalhesAberto: boolean = false;
  modalCadastroAberto: boolean = false;
  modalStatusAberto: boolean = false;

  eventoSelecionado: any = null;
  novoStatus: string = '';

  // Formulário
  eventoForm = {
    id: 0,
    pedido_id: null,
    cliente_id: null,
    nome_evento: '',
    data_evento: '',
    hora_inicio: '',
    hora_fim: '',
    local_evento: '',
    endereco: '',
    cidade: '',
    estado: '',
    tipo_evento: 'Show',
    numero_participantes: 0,
    responsavel_local: '',
    telefone_local: '',
    observacoes: '',
    status: 'agendado'
  };

  // Contadores
  contadores = {
    agendados: 0,
    confirmados: 0,
    em_preparacao: 0,
    em_andamento: 0,
    concluidos: 0
  };

  private apiUrl = environment.apiUrl;

  constructor(
    private http: HttpClient,
    private router: Router,
    private alertController: AlertController
  ) {
    addIcons({
      arrowBack, calendar, time, location, person, call, business,
      checkmarkCircle, closeCircle, alertCircle, create, trash,
      add, cube, search, statsChart
    });
  }

  ngOnInit() {
    this.carregarEventos();
  }

  carregarEventos() {
    const endpoint = this.statusFiltro === 'todos'
      ? `${this.apiUrl}/agenda/eventos`
      : `${this.apiUrl}/agenda/eventos?status=${this.statusFiltro}`;

    this.http.get<any>(endpoint).subscribe({
      next: (response) => {
        if (response.success) {
          this.eventos = response.data;
          this.filtrarEventos();
          this.calcularContadores();
        }
      },
      error: (error) => {
        console.error('Erro ao carregar eventos:', error);
      }
    });
  }

  calcularContadores() {
    this.http.get<any>(`${this.apiUrl}/agenda/eventos`).subscribe({
      next: (response) => {
        if (response.success) {
          const todos = response.data;
          this.contadores.agendados = todos.filter((e: any) => e.status === 'agendado').length;
          this.contadores.confirmados = todos.filter((e: any) => e.status === 'confirmado').length;
          this.contadores.em_preparacao = todos.filter((e: any) => e.status === 'em_preparacao').length;
          this.contadores.em_andamento = todos.filter((e: any) => e.status === 'em_andamento').length;
          this.contadores.concluidos = todos.filter((e: any) => e.status === 'concluido').length;
        }
      }
    });
  }

  filtrarEventos() {
    let filtrados = this.eventos;

    if (this.termoPesquisa.trim()) {
      const termo = this.termoPesquisa.toLowerCase();
      filtrados = filtrados.filter(e =>
        e.nome_evento?.toLowerCase().includes(termo) ||
        e.cliente_nome?.toLowerCase().includes(termo) ||
        e.local_evento?.toLowerCase().includes(termo)
      );
    }

    this.eventosFiltrados = filtrados;
  }

  mudarFiltro(event: any) {
    this.statusFiltro = event.detail.value;
    this.carregarEventos();
  }

  pesquisar() {
    this.filtrarEventos();
  }

  verDetalhes(evento: any) {
    this.http.get<any>(`${this.apiUrl}/agenda/eventos/${evento.id}`).subscribe({
      next: (response) => {
        if (response.success) {
          this.eventoSelecionado = response.data;
          this.modalDetalhesAberto = true;
        }
      }
    });
  }

  fecharModalDetalhes() {
    this.modalDetalhesAberto = false;
    this.eventoSelecionado = null;
  }

  abrirModalCadastro(evento?: any) {
    if (evento) {
      this.eventoForm = { ...evento };
    } else {
      this.limparFormulario();
    }
    this.modalCadastroAberto = true;
  }

  fecharModalCadastro() {
    this.modalCadastroAberto = false;
    this.limparFormulario();
  }

  limparFormulario() {
    this.eventoForm = {
      id: 0,
      pedido_id: null,
      cliente_id: null,
      nome_evento: '',
      data_evento: '',
      hora_inicio: '',
      hora_fim: '',
      local_evento: '',
      endereco: '',
      cidade: '',
      estado: '',
      tipo_evento: 'Show',
      numero_participantes: 0,
      responsavel_local: '',
      telefone_local: '',
      observacoes: '',
      status: 'agendado'
    };
  }

  async salvarEvento() {
    if (!this.eventoForm.nome_evento || !this.eventoForm.data_evento) {
      await this.mostrarAlerta('Atenção', 'Preencha os campos obrigatórios!');
      return;
    }

    const url = this.eventoForm.id
      ? `${this.apiUrl}/agenda/eventos/${this.eventoForm.id}`
      : `${this.apiUrl}/agenda/eventos`;

    const request = this.eventoForm.id
      ? this.http.put<any>(url, this.eventoForm)
      : this.http.post<any>(url, this.eventoForm);

    request.subscribe({
      next: async (response) => {
        if (response.success) {
          await this.mostrarAlerta('Sucesso',
            this.eventoForm.id ? 'Evento atualizado!' : 'Evento criado!');
          this.fecharModalCadastro();
          this.carregarEventos();
        } else {
          await this.mostrarAlerta('Erro', response.message);
        }
      },
      error: async (error) => {
        console.error('Erro ao salvar evento:', error);
        await this.mostrarAlerta('Erro', 'Não foi possível salvar o evento');
      }
    });
  }

  abrirModalStatus(evento: any) {
    this.eventoSelecionado = evento;
    this.novoStatus = evento.status;
    this.modalStatusAberto = true;
  }

  fecharModalStatus() {
    this.modalStatusAberto = false;
    this.eventoSelecionado = null;
    this.novoStatus = '';
  }

  async atualizarStatus() {
    if (!this.eventoSelecionado) return;

    this.http.put<any>(
      `${this.apiUrl}/agenda/eventos/${this.eventoSelecionado.id}/status`,
      { status: this.novoStatus }
    ).subscribe({
      next: async (response) => {
        if (response.success) {
          await this.mostrarAlerta('Sucesso', 'Status atualizado!');
          this.fecharModalStatus();
          this.carregarEventos();
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

  async excluirEvento(evento: any, event: Event) {
    event.stopPropagation();

    const alert = await this.alertController.create({
      header: 'Confirmar Exclusão',
      message: `Tem certeza que deseja excluir o evento "${evento.nome_evento}"?`,
      buttons: [
        { text: 'Cancelar', role: 'cancel' },
        {
          text: 'Excluir',
          role: 'destructive',
          handler: () => {
            this.http.delete<any>(`${this.apiUrl}/agenda/eventos/${evento.id}`).subscribe({
              next: async (response) => {
                if (response.success) {
                  await this.mostrarAlerta('Sucesso', 'Evento excluído!');
                  this.carregarEventos();
                } else {
                  await this.mostrarAlerta('Erro', response.message);
                }
              },
              error: async (error) => {
                console.error('Erro ao excluir evento:', error);
                await this.mostrarAlerta('Erro', 'Não foi possível excluir o evento');
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
      case 'agendado': return 'primary';
      case 'confirmado': return 'success';
      case 'em_preparacao': return 'warning';
      case 'em_andamento': return 'tertiary';
      case 'concluido': return 'success';
      case 'cancelado': return 'danger';
      default: return 'medium';
    }
  }

  getStatusLabel(status: string): string {
    switch (status) {
      case 'agendado': return 'Agendado';
      case 'confirmado': return 'Confirmado';
      case 'em_preparacao': return 'Em Preparação';
      case 'em_andamento': return 'Em Andamento';
      case 'concluido': return 'Concluído';
      case 'cancelado': return 'Cancelado';
      default: return status;
    }
  }

  voltarPainel() {
    this.router.navigate(['/painel']);
  }
}

