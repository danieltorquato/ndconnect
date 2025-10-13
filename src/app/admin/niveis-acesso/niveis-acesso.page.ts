import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import {
  IonContent,
  IonHeader,
  IonTitle,
  IonToolbar,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardContent,
  IonButton,
  IonIcon,
  IonItem,
  IonLabel,
  IonChip,
  IonBadge,
  IonAlert,
  IonActionSheet,
  IonFab,
  IonFabButton,
  IonModal,
  IonInput,
  IonTextarea,
  IonSelect,
  IonSelectOption,
  IonToggle,
  IonRange,
  IonSpinner,
  IonRefresher,
  IonRefresherContent,
  IonSearchbar,
  IonGrid,
  IonRow,
  IonCol,
  IonList,
  IonThumbnail,
  IonSkeletonText,
  IonInfiniteScroll,
  IonInfiniteScrollContent,
  IonNote,
  IonText, IonButtons } from '@ionic/angular/standalone';
import { addOutline, createOutline, trashOutline, eyeOutline, shieldOutline, peopleOutline, listOutline, checkmarkCircleOutline, closeOutline, saveOutline } from 'ionicons/icons';
import { addIcons } from 'ionicons';
import { NivelAcessoService, NivelAcesso, CreateNivelRequest } from '../../services/nivel-acesso.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-niveis-acesso',
  templateUrl: './niveis-acesso.page.html',
  styleUrls: ['./niveis-acesso.page.scss'],
  standalone: true,
  imports: [IonButtons,
    CommonModule,
    FormsModule,
    IonContent,
    IonHeader,
    IonTitle,
    IonToolbar,
    IonCard,
    IonCardHeader,
    IonCardTitle,
    IonCardContent,
    IonButton,
    IonIcon,
    IonItem,
    IonLabel,
    IonChip,
    IonBadge,
    IonAlert,
    IonActionSheet,
    IonFab,
    IonFabButton,
    IonModal,
    IonInput,
    IonTextarea,
    IonSelect,
    IonSelectOption,
    IonToggle,
    IonRange,
    IonSpinner,
    IonRefresher,
    IonRefresherContent,
    IonSearchbar,
    IonGrid,
    IonRow,
    IonCol,
    IonList,
    IonThumbnail,
    IonSkeletonText,
    IonInfiniteScroll,
    IonInfiniteScrollContent,
    IonNote,
    IonText
  ]
})
export class NiveisAcessoPage implements OnInit {
  niveis: NivelAcesso[] = [];
  niveisFiltrados: NivelAcesso[] = [];
  loading = false;
  searchTerm = '';

  // Modal de criação/edição
  isModalOpen = false;
  isEditMode = false;
  nivelEditando: NivelAcesso | null = null;

  // Formulário
  formData: CreateNivelRequest = {
    nome: '',
    descricao: '',
    cor: '#6c757d',
    ordem: 0,
    ativo: true,
    permissoes: []
  };

  // Opções
  coresDisponiveis: string[] = [];
  iconesDisponiveis: string[] = [];

  // Alertas
  alertButtons = [
    {
      text: 'Cancelar',
      role: 'cancel'
    },
    {
      text: 'Confirmar',
      role: 'confirm'
    }
  ];

  constructor(
    private nivelAcessoService: NivelAcessoService,
    private authService: AuthService
  ) {
    addIcons({shieldOutline,peopleOutline,listOutline,checkmarkCircleOutline,createOutline,trashOutline,addOutline,closeOutline,saveOutline,eyeOutline});
  }

  ngOnInit() {
    this.carregarOpcoes();
    this.carregarNiveis();
  }

  carregarOpcoes() {
    this.coresDisponiveis = this.nivelAcessoService.getCoresDisponiveis();
    this.iconesDisponiveis = this.nivelAcessoService.getIconesDisponiveis();
  }

  async carregarNiveis() {
    this.loading = true;
    console.log('🔄 Carregando níveis de acesso...');
    try {
      const response = await this.nivelAcessoService.getNiveis().toPromise();
      console.log('📡 Resposta da API:', response);

      if (response && response.success) {
        this.niveis = response.data || [];
        console.log('✅ Níveis carregados:', this.niveis);
        this.filtrarNiveis();
      } else {
        console.warn('⚠️ Resposta da API não foi bem-sucedida:', response);
        this.niveis = [];
      }
    } catch (error) {
      console.error('❌ Erro ao carregar níveis:', error);
      this.niveis = [];
    } finally {
      this.loading = false;
      console.log('🏁 Carregamento finalizado. Total de níveis:', this.niveis.length);
    }
  }

  filtrarNiveis() {
    if (!this.searchTerm.trim()) {
      this.niveisFiltrados = [...this.niveis];
    } else {
      this.niveisFiltrados = this.niveis.filter(nivel =>
        nivel.nome.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
        nivel.descricao.toLowerCase().includes(this.searchTerm.toLowerCase())
      );
    }
  }

  onSearchChange(event: any) {
    this.searchTerm = event.detail.value;
    this.filtrarNiveis();
  }

  async doRefresh(event: any) {
    await this.carregarNiveis();
    event.target.complete();
  }

  abrirModalCriar() {
    this.isEditMode = false;
    this.nivelEditando = null;
    this.formData = {
      nome: '',
      descricao: '',
      cor: '#6c757d',
      ordem: 0,
      ativo: true,
      permissoes: []
    };
    this.isModalOpen = true;
  }

  abrirModalEditar(nivel: NivelAcesso) {
    this.isEditMode = true;
    this.nivelEditando = nivel;
    this.formData = {
      nome: nivel.nome,
      descricao: nivel.descricao,
      cor: nivel.cor,
      ordem: nivel.ordem,
      ativo: nivel.ativo,
      permissoes: []
    };
    this.isModalOpen = true;
  }

  fecharModal() {
    this.isModalOpen = false;
    this.nivelEditando = null;
  }

  async salvarNivel() {
    if (!this.formData.nome.trim()) {
      return;
    }

    this.loading = true;
    try {
      let response;
      if (this.isEditMode && this.nivelEditando) {
        response = await this.nivelAcessoService.updateNivel(this.nivelEditando.id, this.formData).toPromise();
      } else {
        response = await this.nivelAcessoService.createNivel(this.formData).toPromise();
      }

      if (response.success) {
        await this.carregarNiveis();
        this.fecharModal();
      }
    } catch (error) {
      console.error('Erro ao salvar nível:', error);
    } finally {
      this.loading = false;
    }
  }

  async excluirNivel(nivel: NivelAcesso) {
    if (nivel.total_usuarios > 0) {
      // Mostrar alerta de que não pode excluir
      return;
    }

    this.loading = true;
    try {
      const response = await this.nivelAcessoService.deleteNivel(nivel.id).toPromise();
      if (response.success) {
        await this.carregarNiveis();
      }
    } catch (error) {
      console.error('Erro ao excluir nível:', error);
    } finally {
      this.loading = false;
    }
  }

  getCorNivel(cor: string): string {
    return cor || '#6c757d';
  }

  getStatusNivel(ativo: boolean): string {
    return ativo ? 'Ativo' : 'Inativo';
  }

  getStatusColor(ativo: boolean): string {
    return ativo ? 'success' : 'danger';
  }

  canEdit(): boolean {
    return this.authService.isDev() || this.authService.temNivel('admin');
  }

  canDelete(): boolean {
    return this.authService.isDev() || this.authService.temNivel('admin');
  }
}
