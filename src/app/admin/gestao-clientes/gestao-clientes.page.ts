import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment';
import { addIcons } from 'ionicons';
import {
  arrowBack, person, business, call, mail, calendar,
  locationOutline, document, cart, statsChart, create,
  trash, checkmarkCircle, closeCircle, search, addCircle, logoWhatsapp } from 'ionicons/icons';
import {
  IonHeader, IonToolbar, IonTitle, IonContent, IonCard,
  IonCardHeader, IonCardTitle, IonCardContent, IonButton,
  IonIcon, IonSegment, IonSegmentButton, IonLabel, IonList,
  IonItem, IonBadge, IonSearchbar, IonModal, IonButtons,
  IonInput, IonTextarea, IonSelect, IonSelectOption,
  AlertController, IonDatetime
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

interface Cliente {
  id: number;
  nome: string;
  email: string;
  telefone: string;
  empresa?: string;
  cpf_cnpj?: string;
  endereco?: string;
  cidade?: string;
  estado?: string;
  tipo: string;
  status: string;
  data_cadastro: string;
  observacoes?: string;
}

@Component({
  selector: 'app-gestao-clientes',
  templateUrl: './gestao-clientes.page.html',
  styleUrls: ['./gestao-clientes.page.scss'],
  standalone: true,
  imports: [
    IonHeader, IonToolbar, IonTitle, IonContent, IonCard,
    IonCardHeader, IonCardTitle, IonCardContent, IonButton,
    IonIcon, IonSegment, IonSegmentButton, IonLabel, IonList,
    IonItem, IonBadge, IonSearchbar, IonModal, IonButtons,
    IonInput, IonTextarea, IonSelect, IonSelectOption, IonDatetime,
    CommonModule, FormsModule
  ]
})
export class GestaoClientesPage implements OnInit {
  clientes: Cliente[] = [];
  clientesFiltrados: Cliente[] = [];
  statusFiltro: string = 'todos';
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
    id: 0,
    nome: '',
    empresa: '',
    email: '',
    telefone: '',
    cpf_cnpj: '',
    endereco: '',
    cidade: '',
    estado: '',
    tipo: 'pessoa_fisica',
    status: 'ativo',
    observacoes: ''
  };

  // Contadores
  contadores = {
    ativos: 0,
    inativos: 0,
    bloqueados: 0,
    total: 0
  };

  private apiUrl = environment.apiUrl;

  constructor(
    private http: HttpClient,
    private router: Router,
    private alertController: AlertController
  ) {
    addIcons({arrowBack,addCircle,document,call,mail,locationOutline,logoWhatsapp,statsChart,create,trash,person,closeCircle,business,calendar,cart,checkmarkCircle,search});
  }

  ngOnInit() {
    this.carregarClientes();
  }

  carregarClientes() {
    this.http.get<any>(`${this.apiUrl}/clientes`).subscribe({
      next: (response) => {
        if (response.success) {
          this.clientes = response.data;
          this.filtrarClientes();
          this.calcularContadores();
        }
      },
      error: (error) => {
        console.error('Erro ao carregar clientes:', error);
      }
    });
  }

  calcularContadores() {
    this.contadores.total = this.clientes.length;
    this.contadores.ativos = this.clientes.filter(c => c.status === 'ativo').length;
    this.contadores.inativos = this.clientes.filter(c => c.status === 'inativo').length;
    this.contadores.bloqueados = this.clientes.filter(c => c.status === 'bloqueado').length;
  }

  filtrarClientes() {
    let filtrados = this.clientes;

    if (this.statusFiltro !== 'todos') {
      filtrados = filtrados.filter(c => c.status === this.statusFiltro);
    }

    if (this.termoPesquisa.trim()) {
      const termo = this.termoPesquisa.toLowerCase();
      filtrados = filtrados.filter(c =>
        c.nome.toLowerCase().includes(termo) ||
        c.email?.toLowerCase().includes(termo) ||
        c.telefone?.includes(termo) ||
        c.empresa?.toLowerCase().includes(termo) ||
        c.cpf_cnpj?.includes(termo)
      );
    }

    this.clientesFiltrados = filtrados;
  }

  mudarFiltro(event: any) {
    this.statusFiltro = event.detail.value;
    this.filtrarClientes();
  }

  pesquisar() {
    this.filtrarClientes();
  }

  verDetalhes(cliente: Cliente) {
    this.clienteSelecionado = cliente;
    this.carregarEstatisticas(cliente.id);
    this.modalDetalhesAberto = true;
  }

  fecharModalDetalhes() {
    this.modalDetalhesAberto = false;
    this.clienteSelecionado = null;
  }

  abrirModalCadastro(cliente?: Cliente) {
    if (cliente) {
      this.clienteForm = {
        id: cliente.id,
        nome: cliente.nome,
        empresa: cliente.empresa || '',
        email: cliente.email,
        telefone: cliente.telefone,
        cpf_cnpj: cliente.cpf_cnpj || '',
        endereco: cliente.endereco || '',
        cidade: cliente.cidade || '',
        estado: cliente.estado || '',
        tipo: cliente.tipo,
        status: cliente.status,
        observacoes: cliente.observacoes || ''
      };
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
    this.clienteForm = {
      id: 0,
      nome: '',
      empresa: '',
      email: '',
      telefone: '',
      cpf_cnpj: '',
      endereco: '',
      cidade: '',
      estado: '',
      tipo: 'pessoa_fisica',
      status: 'ativo',
      observacoes: ''
    };
  }

  async salvarCliente() {
    if (!this.clienteForm.nome || !this.clienteForm.email || !this.clienteForm.telefone) {
      await this.mostrarAlerta('Atenção', 'Preencha os campos obrigatórios!');
      return;
    }

    const url = this.clienteForm.id
      ? `${this.apiUrl}/clientes/${this.clienteForm.id}`
      : `${this.apiUrl}/clientes`;

    const request = this.clienteForm.id
      ? this.http.put<any>(url, this.clienteForm)
      : this.http.post<any>(url, this.clienteForm);

    request.subscribe({
      next: async (response) => {
        if (response.success) {
          await this.mostrarAlerta('Sucesso',
            this.clienteForm.id ? 'Cliente atualizado!' : 'Cliente cadastrado!');
          this.fecharModalCadastro();
          this.carregarClientes();
        } else {
          // Verificar se é erro de duplicação
          if (response.data && response.data.id) {
            const clienteExistente = response.data;
            await this.mostrarAlertaDuplicacao(response.message, clienteExistente);
          } else {
            await this.mostrarAlerta('Erro', response.message);
          }
        }
      },
      error: async (error) => {
        console.error('Erro ao salvar cliente:', error);
        await this.mostrarAlerta('Erro', 'Não foi possível salvar o cliente');
      }
    });
  }

  carregarEstatisticas(clienteId: number) {
    this.http.get<any>(`${this.apiUrl}/clientes/${clienteId}/estatisticas`).subscribe({
      next: (response) => {
        if (response.success) {
          this.estatisticas = response.data;
        }
      }
    });
  }

  abrirHistorico(cliente: Cliente) {
    this.clienteSelecionado = cliente;

    // Carregar histórico de orçamentos
    this.http.get<any>(`${this.apiUrl}/clientes/${cliente.id}/historico-orcamentos`).subscribe({
      next: (response) => {
        if (response.success) {
          this.historicoOrcamentos = response.data;
        }
      }
    });

    // Carregar histórico de pedidos
    this.http.get<any>(`${this.apiUrl}/clientes/${cliente.id}/historico-pedidos`).subscribe({
      next: (response) => {
        if (response.success) {
          this.historicoPedidos = response.data;
        }
      }
    });

    this.modalHistoricoAberto = true;
  }

  fecharModalHistorico() {
    this.modalHistoricoAberto = false;
    this.clienteSelecionado = null;
    this.historicoOrcamentos = [];
    this.historicoPedidos = [];
  }

  async excluirCliente(cliente: Cliente, event: Event) {
    event.stopPropagation();

    const alert = await this.alertController.create({
      header: 'Confirmar Exclusão',
      message: `Tem certeza que deseja excluir o cliente ${cliente.nome}?`,
      buttons: [
        { text: 'Cancelar', role: 'cancel' },
        {
          text: 'Excluir',
          role: 'destructive',
          handler: () => {
            this.http.delete<any>(`${this.apiUrl}/clientes/${cliente.id}`).subscribe({
              next: async (response) => {
                if (response.success) {
                  await this.mostrarAlerta('Sucesso', 'Cliente excluído!');
                  this.carregarClientes();
                } else {
                  await this.mostrarAlerta('Erro', response.message);
                }
              },
              error: async (error) => {
                console.error('Erro ao excluir cliente:', error);
                await this.mostrarAlerta('Erro', 'Não foi possível excluir o cliente');
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
    const mensagem = `Olá ${nome}! Tudo bem?`;
    const whatsappUrl = `https://wa.me/${telefone.replace(/\D/g, '')}?text=${encodeURIComponent(mensagem)}`;
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

  async mostrarAlertaDuplicacao(message: string, clienteExistente: any) {
    const alert = await this.alertController.create({
      header: 'Cliente Duplicado',
      message: `${message}<br><br><strong>Cliente existente:</strong><br>
                Nome: ${clienteExistente.nome}<br>
                ${clienteExistente.cpf_cnpj ? `CPF/CNPJ: ${clienteExistente.cpf_cnpj}<br>` : ''}
                ${clienteExistente.telefone ? `Telefone: ${clienteExistente.telefone}<br>` : ''}
                ${clienteExistente.email ? `Email: ${clienteExistente.email}` : ''}`,
      buttons: [
        {
          text: 'Ver Cliente',
          handler: () => {
            this.fecharModalCadastro();
            this.verDetalhes(clienteExistente);
          }
        },
        {
          text: 'OK',
          role: 'cancel'
        }
      ]
    });
    await alert.present();
  }

  getStatusColor(status: string): string {
    switch (status) {
      case 'ativo': return 'success';
      case 'inativo': return 'medium';
      case 'bloqueado': return 'danger';
      default: return 'medium';
    }
  }

  getStatusLabel(status: string): string {
    switch (status) {
      case 'ativo': return 'Ativo';
      case 'inativo': return 'Inativo';
      case 'bloqueado': return 'Bloqueado';
      default: return status;
    }
  }

  getTipoLabel(tipo: string): string {
    return tipo === 'pessoa_fisica' ? 'Pessoa Física' : 'Pessoa Jurídica';
  }

  voltarPainel() {
    this.router.navigate(['/painel-orcamento']);
  }
}

