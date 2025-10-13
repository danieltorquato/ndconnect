import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment';
import { addIcons } from 'ionicons';
import {
  arrowBack, personAdd, call, mail, business, chatbubbles,
  checkmarkCircle, closeCircle, time, search, create, trash,
  swapHorizontal, documentText, addCircle } from 'ionicons/icons';
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
  IonItem,
  IonBadge,
  IonSearchbar,
  IonModal,
  IonButtons,
  IonTextarea,
  IonSelect,
  IonSelectOption,
  AlertController
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

interface Lead {
  id: number;
  nome: string;
  email: string;
  telefone: string;
  empresa?: string;
  mensagem: string;
  origem: string;
  status: string;
  created_at: string;
  observacoes?: string;
  lido?: boolean;
  data_leitura?: string;
}

@Component({
  selector: 'app-gestao-leads',
  templateUrl: './gestao-leads.page.html',
  styleUrls: ['./gestao-leads.page.scss'],
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
    IonItem,
    IonBadge,
    IonSearchbar,
    IonModal,
    IonButtons,
    IonTextarea,
    IonSelect,
    IonSelectOption,
    CommonModule,
    FormsModule
  ]
})
export class GestaoLeadsPage implements OnInit {
  leads: Lead[] = [];
  leadsFiltrados: Lead[] = [];
  statusFiltro: string = 'novo';
  termoPesquisa: string = '';

  // Modal de detalhes
  modalDetalhesAberto: boolean = false;
  leadSelecionado: Lead | null = null;

  // Modal de atualização
  modalAtualizarAberto: boolean = false;
  novoStatus: string = '';
  observacoes: string = '';

  // Contadores
  contadores = {
    novos: 0,
    contatados: 0,
    qualificados: 0,
    convertidos: 0,
    perdidos: 0
  };

  private apiUrl = environment.apiUrl;

  constructor(
    private http: HttpClient,
    private router: Router,
    private alertController: AlertController
  ) {
    addIcons({arrowBack,personAdd,addCircle,call,mail,time,chatbubbles,create,documentText,swapHorizontal,checkmarkCircle,trash,closeCircle,business,search});
  }

  ngOnInit() {
    this.carregarLeads();
  }

  carregarLeads() {
    const endpoint = this.statusFiltro === 'todos'
      ? `${this.apiUrl}/leads`
      : `${this.apiUrl}/leads?status=${this.statusFiltro}`;

    this.http.get<any>(endpoint).subscribe({
      next: (response) => {
        if (response.success) {
          this.leads = response.data;
          this.filtrarLeads();
          this.calcularContadores();
        }
      },
      error: (error) => {
        console.error('Erro ao carregar leads:', error);
      }
    });
  }

  calcularContadores() {
    this.http.get<any>(`${this.apiUrl}/leads`).subscribe({
      next: (response) => {
        if (response.success) {
          const todos = response.data;
          this.contadores.novos = todos.filter((l: Lead) => l.status === 'novo').length;
          this.contadores.contatados = todos.filter((l: Lead) => l.status === 'contatado').length;
          this.contadores.qualificados = todos.filter((l: Lead) => l.status === 'qualificado').length;
          this.contadores.convertidos = todos.filter((l: Lead) => l.status === 'convertido').length;
          this.contadores.perdidos = todos.filter((l: Lead) => l.status === 'perdido').length;
        }
      }
    });
  }

  filtrarLeads() {
    let filtrados = this.leads;

    if (this.termoPesquisa.trim()) {
      const termo = this.termoPesquisa.toLowerCase();
      filtrados = filtrados.filter(l =>
        l.nome.toLowerCase().includes(termo) ||
        l.email?.toLowerCase().includes(termo) ||
        l.telefone?.includes(termo) ||
        l.empresa?.toLowerCase().includes(termo)
      );
    }

    this.leadsFiltrados = filtrados;
  }

  mudarFiltro(event: any) {
    this.statusFiltro = event.detail.value;
    this.carregarLeads();
  }

  pesquisar() {
    this.filtrarLeads();
  }

  verDetalhes(lead: Lead) {
    this.leadSelecionado = lead;
    this.modalDetalhesAberto = true;
  }

  fecharModalDetalhes() {
    this.modalDetalhesAberto = false;
    this.leadSelecionado = null;
  }

  abrirModalAtualizar(lead: Lead) {
    this.leadSelecionado = lead;
    this.novoStatus = lead.status;
    this.observacoes = lead.observacoes || '';
    this.modalAtualizarAberto = true;
  }

  fecharModalAtualizar() {
    this.modalAtualizarAberto = false;
    this.leadSelecionado = null;
    this.novoStatus = '';
    this.observacoes = '';
  }

  async atualizarLead() {
    if (!this.leadSelecionado) return;

    const dados = {
      status: this.novoStatus,
      observacoes: this.observacoes
    };

    this.http.put<any>(`${this.apiUrl}/leads/${this.leadSelecionado.id}`, dados).subscribe({
      next: async (response) => {
        if (response.success) {
          await this.mostrarAlerta('Sucesso', 'Lead atualizado com sucesso!');
          this.fecharModalAtualizar();
          this.carregarLeads();
        } else {
          await this.mostrarAlerta('Erro', response.message);
        }
      },
      error: async (error) => {
        console.error('Erro ao atualizar lead:', error);
        await this.mostrarAlerta('Erro', 'Não foi possível atualizar o lead');
      }
    });
  }

  async converterEmCliente(lead: Lead) {
    const alert = await this.alertController.create({
      header: 'Converter em Cliente',
      message: `Deseja converter ${lead.nome} em cliente?`,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel'
        },
        {
          text: 'Converter',
          handler: () => {
            this.http.post<any>(`${this.apiUrl}/leads/${lead.id}/converter`, {}).subscribe({
              next: async (response) => {
                if (response.success) {
                  await this.mostrarAlerta('Sucesso', 'Lead convertido em cliente com sucesso!');
                  this.carregarLeads();
                } else {
                  await this.mostrarAlerta('Erro', response.message);
                }
              },
              error: async (error) => {
                console.error('Erro ao converter lead:', error);
                await this.mostrarAlerta('Erro', 'Não foi possível converter o lead');
              }
            });
          }
        }
      ]
    });

    await alert.present();
  }

  async excluirLead(lead: Lead, event: Event) {
    event.stopPropagation();

    const alert = await this.alertController.create({
      header: 'Confirmar Exclusão',
      message: `Tem certeza que deseja excluir o lead de ${lead.nome}?`,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel'
        },
        {
          text: 'Excluir',
          role: 'destructive',
          handler: () => {
            this.http.delete<any>(`${this.apiUrl}/leads/${lead.id}`).subscribe({
              next: async (response) => {
                if (response.success) {
                  await this.mostrarAlerta('Sucesso', 'Lead excluído com sucesso!');
                  this.carregarLeads();
                } else {
                  await this.mostrarAlerta('Erro', response.message);
                }
              },
              error: async (error) => {
                console.error('Erro ao excluir lead:', error);
                await this.mostrarAlerta('Erro', 'Não foi possível excluir o lead');
              }
            });
          }
        }
      ]
    });

    await alert.present();
  }

  ligarPara(telefone: string) {
    window.open(`tel:${telefone}`, '_system');
  }

  enviarEmail(email: string) {
    window.open(`mailto:${email}`, '_system');
  }

  enviarWhatsApp(telefone: string, nome: string) {
    const mensagem = `Olá ${nome}! Recebi sua solicitação de orçamento pela N.D Connect. Como posso ajudar?`;
    const whatsappUrl = `https://wa.me/+55${telefone.replace(/\D/g, '')}?text=${encodeURIComponent(mensagem)}`;
    window.open(whatsappUrl, '_blank');
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
      case 'novo': return 'danger';
      case 'contatado': return 'warning';
      case 'qualificado': return 'primary';
      case 'convertido': return 'success';
      case 'perdido': return 'medium';
      default: return 'medium';
    }
  }

  getStatusLabel(status: string): string {
    switch (status) {
      case 'novo': return 'Novo';
      case 'contatado': return 'Contatado';
      case 'qualificado': return 'Qualificado';
      case 'convertido': return 'Convertido';
      case 'perdido': return 'Perdido';
      default: return status;
    }
  }

  getOrigemLabel(origem: string): string {
    switch (origem) {
      case 'site': return 'Site';
      case 'whatsapp': return 'WhatsApp';
      case 'email': return 'E-mail';
      case 'telefone': return 'Telefone';
      case 'indicacao': return 'Indicação';
      case 'outros': return 'Outros';
      default: return origem;
    }
  }

  criarOrcamento(lead: any) {
    console.log('Criando orçamento para lead:', lead);

    // Criar orçamento a partir do lead
    this.http.post<any>(`${this.apiUrl}/orcamentos/from-lead`, { lead_id: lead.id }).subscribe({
      next: async (response) => {
        console.log('Resposta da API:', response);

        // Sempre redirecionar, mesmo se houver erro na resposta
        console.log('Redirecionando para página de orçamento...');
        this.router.navigate(['/orcamento'], {
          queryParams: {
            leadId: lead.id,
            orcamentoId: response?.data?.id || '',
            nome: lead.nome,
            email: lead.email,
            telefone: lead.telefone,
            empresa: lead.empresa || '',
            mensagem: lead.mensagem || ''
          }
        });
      },
      error: async (error) => {
        console.error('Erro HTTP ao criar orçamento:', error);

        // Mesmo com erro, redirecionar para página de orçamento
        console.log('Erro na API, mas redirecionando mesmo assim...');
        this.router.navigate(['/orcamento'], {
          queryParams: {
            leadId: lead.id,
            nome: lead.nome,
            email: lead.email,
            telefone: lead.telefone,
            empresa: lead.empresa || '',
            mensagem: lead.mensagem || ''
          }
        });
      }
    });
  }

  voltarPainel() {
    this.router.navigate(['/painel-orcamento']);
  }

  marcarComoLido(lead: Lead) {
    this.http.post<any>(`${this.apiUrl}/marcar_lead_lido.php`, {
      lead_id: lead.id
    }).subscribe({
      next: (response) => {
        if (response.success) {
          // Marcar como lido localmente
          lead.lido = true;
          lead.data_leitura = new Date().toISOString();

          // Recarregar contadores para atualizar badges
          this.calcularContadores();

          console.log('Lead marcado como lido:', response);
        } else {
          console.error('Erro ao marcar lead como lido:', response.message);
        }
      },
      error: (error) => {
        console.error('Erro ao marcar lead como lido:', error);
      }
    });
  }
}
