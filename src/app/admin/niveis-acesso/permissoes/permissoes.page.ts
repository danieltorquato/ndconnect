import { Component, OnInit, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import {
  saveOutline,
  refreshOutline,
  shieldOutline,
  checkmarkOutline,
  closeOutline,
  eyeOutline,
  createOutline,
  trashOutline,
  addOutline, folderOutline, arrowBackOutline, peopleOutline, checkmarkCircleOutline, informationCircleOutline, linkOutline, settingsOutline, addCircleOutline, gridOutline, listOutline } from 'ionicons/icons';
import { addIcons } from 'ionicons';
import { NivelAcessoService, NivelAcesso, PermissaoNivel } from '../../../services/nivel-acesso.service';
import { AuthService } from '../../../services/auth.service';
import { ActivatedRoute, Router } from '@angular/router';

import { IonHeader, IonSkeletonText, IonSegmentButton, IonContent, IonTitle, IonToolbar, IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonButton, IonIcon, IonItem, IonLabel, IonToggle, IonChip, IonBadge, IonList, IonListHeader, IonSpinner, IonRefresher, IonRefresherContent, IonSearchbar, IonGrid, IonRow, IonCol, IonNote, IonText, IonCheckbox, IonSegment, IonFab, IonFabButton, IonButtons } from '@ionic/angular/standalone';
@Component({
  selector: 'app-permissoes',
  templateUrl: './permissoes.page.html',
  styleUrls: ['./permissoes.page.scss'],
  standalone: true,
  schemas: [CUSTOM_ELEMENTS_SCHEMA],
  imports: [
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
    IonToggle,
    IonChip,
    IonBadge,
    IonList,
    IonListHeader,
    IonSpinner,
    IonRefresher,
    IonRefresherContent,
    IonSearchbar,
    IonSkeletonText,
    IonSegment,
    IonSegmentButton,
    IonFab,
    IonFabButton,
    IonButtons
  ]
})
export class PermissoesPage implements OnInit {
  nivel: NivelAcesso | null = null;
  permissoes: PermissaoNivel[] = [];
  permissoesFiltradas: PermissaoNivel[] = [];
  loading = false;
  saving = false;
  searchTerm = '';
  categoriaFiltro = 'todas';
  viewMode: 'cards' | 'list' = 'cards';
  editMode = false;

  categorias: string[] = [];
  permissoesModificadas: { [key: number]: any } = {};

  constructor(
    private nivelAcessoService: NivelAcessoService,
    private authService: AuthService,
    private route: ActivatedRoute,
    private router: Router
  ) {
    addIcons({shieldOutline,arrowBackOutline,peopleOutline,checkmarkCircleOutline,informationCircleOutline,gridOutline,listOutline,createOutline,folderOutline,checkmarkOutline,closeOutline,linkOutline,settingsOutline,addCircleOutline,trashOutline,saveOutline,refreshOutline,eyeOutline,addOutline});
  }

  ngOnInit() {
    this.carregarNivel();
  }

  async carregarNivel() {
    const nivelId = this.route.snapshot.paramMap.get('id');
    if (!nivelId) return;

    this.loading = true;
    try {
      // Carregar dados do nível
      const nivelResponse = await this.nivelAcessoService.getNivel(+nivelId).toPromise();
      if (nivelResponse.success) {
        this.nivel = nivelResponse.data;
      }

      // Carregar permissões
      const permissoesResponse = await this.nivelAcessoService.getPermissoes(+nivelId).toPromise();
      if (permissoesResponse.success) {
        this.permissoes = permissoesResponse.data;
        this.filtrarPermissoes();
        this.extrairCategorias();
      }
    } catch (error) {
      console.error('Erro ao carregar dados:', error);
    } finally {
      this.loading = false;
    }
  }

  extrairCategorias() {
    const categoriasUnicas = [...new Set(this.permissoes.map(p => p.categoria))];
    this.categorias = categoriasUnicas.sort();
  }

  filtrarPermissoes() {
    let filtradas = [...this.permissoes];

    // Filtrar por categoria
    if (this.categoriaFiltro !== 'todas') {
      filtradas = filtradas.filter(p => p.categoria === this.categoriaFiltro);
    }

    // Filtrar por termo de busca
    if (this.searchTerm.trim()) {
      filtradas = filtradas.filter(p =>
        p.nome.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
        p.descricao.toLowerCase().includes(this.searchTerm.toLowerCase())
      );
    }

    this.permissoesFiltradas = filtradas;
  }

  onSearchChange(event: any) {
    this.searchTerm = event.detail.value;
    this.filtrarPermissoes();
  }

  onCategoriaChange(event: any) {
    this.categoriaFiltro = event.detail.value;
    this.filtrarPermissoes();
  }

  onViewModeChange(event: any) {
    this.viewMode = event.detail.value;
  }

  onEditModeChange(event: any) {
    this.editMode = event.detail.checked;
  }

  async doRefresh(event: any) {
    await this.carregarNivel();
    event.target.complete();
  }

  onPermissaoChange(permissao: PermissaoNivel, tipo: string, value: boolean) {
    const key = permissao.id;
    if (!this.permissoesModificadas[key]) {
      this.permissoesModificadas[key] = { ...permissao };
    }
    this.permissoesModificadas[key][tipo] = value;
  }

  temModificacoes(): boolean {
    return Object.keys(this.permissoesModificadas).length > 0;
  }

  async salvarPermissoes() {
    if (!this.nivel || !this.temModificacoes()) return;

    this.saving = true;
    try {
      const permissoesParaSalvar = Object.values(this.permissoesModificadas).map(p => ({
        pagina_id: p.id,
        pode_acessar: p.pode_acessar,
        pode_editar: p.pode_editar,
        pode_deletar: p.pode_deletar,
        pode_criar: p.pode_criar
      }));

      const response = await this.nivelAcessoService.updatePermissoes(
        this.nivel.id,
        permissoesParaSalvar
      ).toPromise();

      if (response.success) {
        this.permissoesModificadas = {};
        await this.carregarNivel();
      }
    } catch (error) {
      console.error('Erro ao salvar permissões:', error);
    } finally {
      this.saving = false;
    }
  }

  selecionarTodasPermissoes(categoria: string) {
    const permissoesCategoria = this.permissoesFiltradas.filter(p => p.categoria === categoria);

    permissoesCategoria.forEach(permissao => {
      this.onPermissaoChange(permissao, 'pode_acessar', true);
      this.onPermissaoChange(permissao, 'pode_editar', true);
      this.onPermissaoChange(permissao, 'pode_deletar', true);
      this.onPermissaoChange(permissao, 'pode_criar', true);
    });
  }

  removerTodasPermissoes(categoria: string) {
    const permissoesCategoria = this.permissoesFiltradas.filter(p => p.categoria === categoria);

    permissoesCategoria.forEach(permissao => {
      this.onPermissaoChange(permissao, 'pode_acessar', false);
      this.onPermissaoChange(permissao, 'pode_editar', false);
      this.onPermissaoChange(permissao, 'pode_deletar', false);
      this.onPermissaoChange(permissao, 'pode_criar', false);
    });
  }

  getPermissaoAtual(permissao: PermissaoNivel, tipo: string): boolean {
    const modificada = this.permissoesModificadas[permissao.id];
    if (modificada) {
      return modificada[tipo as keyof PermissaoNivel] as boolean;
    }
    return permissao[tipo as keyof PermissaoNivel] as boolean;
  }

  getCategoriasFiltradas(): string[] {
    return this.categorias.filter(cat =>
      this.permissoesFiltradas.some(p => p.categoria === cat)
    );
  }

  getTotalPermissoesCategoria(categoria: string): number {
    return this.permissoesFiltradas.filter(p => p.categoria === categoria).length;
  }

  getPermissoesAtivasCategoria(categoria: string): number {
    return this.permissoesFiltradas.filter(p =>
      p.categoria === categoria && this.getPermissaoAtual(p, 'pode_acessar')
    ).length;
  }

  getPermissoesPorCategoria(categoria: string): PermissaoNivel[] {
    return this.permissoesFiltradas.filter(p => p.categoria === categoria);
  }

  canEdit(): boolean {
    return this.authService.isDev() || this.authService.temNivel('admin');
  }

  voltar() {
    this.router.navigate(['/admin/niveis-acesso']);
  }
}
