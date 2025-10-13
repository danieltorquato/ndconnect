import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { Subscription, interval } from 'rxjs';
import {
  IonContent,
  IonHeader,
  IonTitle,
  IonToolbar,
  IonCard,
  IonCardContent,
  IonCardHeader,
  IonCardTitle,
  IonButton,
  IonIcon,
  IonGrid,
  IonRow,
  IonCol,
  IonBadge,
  IonButtons,
  IonChip,
  IonList,
  IonItem,
  IonLabel,
  IonRefresher,
  IonRefresherContent,
  IonSkeletonText, IonNote } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  calculator,
  cube,
  time,
  settings,
  statsChart,
  documentText,
  addCircle,
  list,
  analytics,
  cog,
  checkmarkCircle,
  trendingUp,
  home,
  logOut,
  people,
  notifications,
  refresh,
  call,
  mail,
  chatbubbles,
  eye,
  create,
  trash,
  checkmarkCircleOutline,
  closeCircleOutline,
  warningOutline, trophy, peopleOutline } from 'ionicons/icons';
import { LeadsService, Lead, LeadStats } from '../services/leads.service';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-painel-orcamento',
  templateUrl: './painel-orcamento.page.html',
  styleUrls: ['./painel-orcamento.page.scss'],
  standalone: true,
  imports: [IonNote,
    IonButtons,
    IonContent,
    IonHeader,
    IonTitle,
    IonToolbar,
    IonCard,
    IonCardContent,
    IonCardHeader,
    IonCardTitle,
    IonButton,
    IonIcon,
    IonGrid,
    IonRow,
    IonCol,
    IonBadge,
    IonChip,
    IonList,
    IonItem,
    IonLabel,
    IonRefresher,
    IonRefresherContent,
    IonSkeletonText,
    CommonModule,
    FormsModule
  ]
})
export class PainelOrcamentoPage implements OnInit, OnDestroy {
  // Dados dos leads
  leads: Lead[] = [];
  leadsRecentes: Lead[] = [];
  stats: LeadStats = {
    novos: 0,
    contatados: 0,
    qualificados: 0,
    convertidos: 0,
    perdidos: 0,
    total: 0
  };

  // Estados da interface
  loading = false;
  refreshing = false;
  showLeads = false;
  notificacoes = 0;
  leadsNaoLidos: Lead[] = [];
  primeiraVisualizacao = true;

  // Subscriptions
  private leadsSubscription?: Subscription;
  private statsSubscription?: Subscription;
  private notificacoesSubscription?: Subscription;
  private refreshInterval?: Subscription;

  constructor(
    private router: Router,
    private leadsService: LeadsService,
    private authService: AuthService
  ) {
    addIcons({calculator,people,home,logOut,addCircle,call,checkmarkCircle,trophy,time,eye,mail,documentText,peopleOutline,cube,settings,analytics,list,statsChart,trendingUp,cog,notifications,refresh,chatbubbles,create,trash,checkmarkCircleOutline,closeCircleOutline,warningOutline});
  }

  ngOnInit() {
    this.carregarDados();
    this.configurarAtualizacaoAutomatica();
    this.configurarVisualizacaoInicial();
    this.configurarNotificacoes();
  }

  ngOnDestroy() {
    if (this.leadsSubscription) {
      this.leadsSubscription.unsubscribe();
    }
    if (this.statsSubscription) {
      this.statsSubscription.unsubscribe();
    }
    if (this.notificacoesSubscription) {
      this.notificacoesSubscription.unsubscribe();
    }
    if (this.refreshInterval) {
      this.refreshInterval.unsubscribe();
    }
  }

  // Métodos de carregamento de dados
  carregarDados() {
    this.loading = true;
    this.carregarLeads();

    // Marcar leads como lidos na primeira visualização
    setTimeout(() => {
      this.marcarLeadsComoLidos();
    }, 1000);
  }

  carregarLeads() {
    this.leadsService.carregarLeads().subscribe({
      next: (response) => {
        if (response.success) {
          this.leads = response.data || [];

          this.leadsRecentes = this.leads
            .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
            .slice(0, 5);

          this.leadsService.atualizarLeads(this.leads);
          this.atualizarLeadsNaoLidos();
          this.calcularNotificacoes();

          // Calcular estatísticas localmente
          this.stats = this.leadsService.calcularStats(this.leads);
        }
        this.loading = false;
      },
      error: (error) => {
        console.error('Erro ao carregar leads:', error);
        this.loading = false;
      }
    });
  }

  carregarEstatisticas() {
    this.leadsService.carregarEstatisticas().subscribe({
      next: (response) => {
        if (response.success) {
          this.stats = response.data || this.stats;
          this.leadsService.atualizarStats(this.stats);
        }
      },
      error: (error) => {
        console.error('Erro ao carregar estatísticas:', error);
        // Calcular stats localmente se a API falhar
        this.stats = this.leadsService.calcularStats(this.leads);
      }
    });
  }

  configurarAtualizacaoAutomatica() {
    // Atualizar dados a cada 30 segundos
    this.refreshInterval = interval(30000).subscribe(() => {
      this.carregarDados();
    });
  }

  calcularNotificacoes() {
    this.notificacoes = this.leadsService.contarNotificacoes();
  }

  atualizarLeadsNaoLidos() {
    this.leadsNaoLidos = this.leadsService.obterLeadsNaoLidos();
  }

  configurarVisualizacaoInicial() {
    // Verificar se é a primeira visualização da sessão
    const ultimaVisualizacao = localStorage.getItem('ultima_visualizacao_painel');
    const agora = new Date().getTime();

    if (!ultimaVisualizacao || (agora - parseInt(ultimaVisualizacao)) > 300000) { // 5 minutos
      this.primeiraVisualizacao = true;
    } else {
      this.primeiraVisualizacao = false;
    }
  }

  configurarNotificacoes() {
    // Escutar mudanças nas notificações em tempo real
    this.notificacoesSubscription = this.leadsService.notificacoes$.subscribe(
      notificacoes => {
        this.notificacoes = notificacoes;
      }
    );
  }

  marcarLeadsComoLidos() {
    if (this.primeiraVisualizacao && this.notificacoes > 0) {
      this.leadsService.marcarTodosComoLidos().subscribe({
        next: (response) => {
          if (response.success) {
            this.primeiraVisualizacao = false;
            localStorage.setItem('ultima_visualizacao_painel', new Date().getTime().toString());

            // Atualizar estado local
            this.atualizarLeadsNaoLidos();
            this.calcularNotificacoes();
          }
        },
        error: (error) => {
          console.error('Erro ao marcar leads como lidos automaticamente:', error);
        }
      });
    }
  }

  // Refresh manual
  doRefresh(event: any) {
    this.refreshing = true;
    this.carregarDados();
    setTimeout(() => {
      this.refreshing = false;
      event.target.complete();
    }, 1000);
  }

  // Toggle para mostrar/ocultar leads
  toggleLeads() {
    this.showLeads = !this.showLeads;

    // Se está abrindo a seção de leads, marcar como lidos
    if (this.showLeads) {
      this.marcarLeadsComoLidos();
    }
  }

  // Marcar todos os leads novos como lidos (método público)
  marcarTodosComoLidos() {
    this.leadsService.marcarTodosComoLidos().subscribe({
      next: (response) => {
        if (response.success) {
          // Atualizar estado local
          this.atualizarLeadsNaoLidos();
          this.calcularNotificacoes();
          console.log('Leads marcados como lidos:', response);
        } else {
          console.error('Erro ao marcar leads como lidos:', response.message);
        }
      },
      error: (error) => {
        console.error('Erro ao marcar leads como lidos:', error);
      }
    });
  }

  // Ações com leads
  verDetalhesLead(lead: Lead) {
    this.router.navigate(['/admin/gestao-leads'], {
      queryParams: { leadId: lead.id }
    });
  }

  ligarPara(telefone: string) {
    window.open(`tel:${telefone}`, '_self');
  }

  enviarEmail(email: string) {
    window.open(`mailto:${email}`, '_self');
  }

  enviarWhatsApp(telefone: string, nome: string) {
    const mensagem = `Olá ${nome}, tudo bem?`;
    const url = `https://wa.me/55${telefone.replace(/\D/g, '')}?text=${encodeURIComponent(mensagem)}`;
    window.open(url, '_blank');
  }

  criarOrcamento(lead: Lead) {
    this.router.navigate(['/orcamento'], {
      queryParams: { leadId: lead.id, cliente: lead.nome }
    });
  }

  atualizarStatusLead(lead: Lead, novoStatus: string) {
    this.leadsService.atualizarStatus(lead.id, novoStatus).subscribe({
      next: (response) => {
        if (response.success) {
          this.carregarDados();
        }
      },
      error: (error) => {
        console.error('Erro ao atualizar status:', error);
      }
    });
  }

  // Navegação para diferentes seções
  irParaOrcamentos() {
    this.router.navigate(['/orcamento']);
  }

  irParaProdutos() {
    this.router.navigate(['/produtos']);
  }

  irParaHistoricoOrcamentos() {
    this.router.navigate(['/gestao-orcamentos']);
  }

  irParaGestaoOrcamentos() {
    this.router.navigate(['/admin/gestao-orcamentos']);
  }

  irParaGestaoLeads() {
    this.router.navigate(['/admin/gestao-leads']);
  }

  irParaRelatorios() {
    this.router.navigate(['/admin/relatorios']);
  }

  irParaConfiguracoes() {
    this.router.navigate(['/admin/gestao-orcamentos']);
  }

  navegarPara(rota: string) {
    this.router.navigate([rota]);
  }

  logout() {
    this.authService.logout().subscribe(() => {
      this.router.navigate(['/login']);
    });
  }

  // Utilitários
  getStatusColor(status: string): string {
    const cores: { [key: string]: string } = {
      'novo': 'primary',
      'contatado': 'warning',
      'qualificado': 'success',
      'convertido': 'tertiary',
      'perdido': 'danger'
    };
    return cores[status] || 'medium';
  }

  getStatusIcon(status: string): string {
    const icones: { [key: string]: string } = {
      'novo': 'add-circle',
      'contatado': 'call',
      'qualificado': 'checkmark-circle',
      'convertido': 'trophy',
      'perdido': 'close-circle'
    };
    return icones[status] || 'help-circle';
  }

  formatarData(data: string): string {
    return new Date(data).toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

}
