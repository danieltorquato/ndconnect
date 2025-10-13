import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface NivelAcesso {
  id: number;
  nome: string;
  descricao: string;
  cor: string;
  ordem: number;
  ativo: boolean;
  total_usuarios: number;
  data_criacao: string;
  data_atualizacao: string;
}

export interface PaginaSistema {
  id: number;
  nome: string;
  rota: string;
  icone: string;
  categoria: string;
  descricao: string;
  ativo: boolean;
  total_permissoes: number;
}

export interface PermissaoNivel {
  id: number;
  nome: string;
  rota: string;
  icone: string;
  categoria: string;
  descricao: string;
  pode_acessar: boolean;
  pode_editar: boolean;
  pode_deletar: boolean;
  pode_criar: boolean;
}

export interface CreateNivelRequest {
  nome: string;
  descricao?: string;
  cor?: string;
  ordem?: number;
  ativo?: boolean;
  permissoes?: any[];
}

export interface UpdateNivelRequest {
  nome?: string;
  descricao?: string;
  cor?: string;
  ordem?: number;
  ativo?: boolean;
  permissoes?: any[];
}

@Injectable({
  providedIn: 'root'
})
export class NivelAcessoService {
  private readonly API_URL = `${environment.apiUrl}`;

  constructor(private http: HttpClient) { }

  // Níveis de Acesso
  getNiveis(): Observable<any> {
    return this.http.get(`${this.API_URL}/niveis-acesso`);
  }

  getNivel(id: number): Observable<any> {
    return this.http.get(`${this.API_URL}/niveis-acesso/${id}`);
  }

  createNivel(nivel: CreateNivelRequest): Observable<any> {
    return this.http.post(`${this.API_URL}/niveis-acesso`, nivel);
  }

  updateNivel(id: number, nivel: UpdateNivelRequest): Observable<any> {
    return this.http.put(`${this.API_URL}/niveis-acesso/${id}`, nivel);
  }

  deleteNivel(id: number): Observable<any> {
    return this.http.delete(`${this.API_URL}/niveis-acesso/${id}`);
  }

  // Permissões
  getPermissoes(nivelId: number): Observable<any> {
    return this.http.get(`${this.API_URL}/niveis-acesso/${nivelId}/permissoes`);
  }

  updatePermissoes(nivelId: number, permissoes: any[]): Observable<any> {
    return this.http.put(`${this.API_URL}/niveis-acesso/${nivelId}/permissoes`, permissoes);
  }

  // Páginas do Sistema
  getPaginas(): Observable<any> {
    return this.http.get(`${this.API_URL}/paginas-sistema`);
  }

  getPaginasByCategoria(categoria: string): Observable<any> {
    return this.http.get(`${this.API_URL}/paginas-sistema?categoria=${categoria}`);
  }

  getPagina(id: number): Observable<any> {
    return this.http.get(`${this.API_URL}/paginas-sistema/${id}`);
  }

  createPagina(pagina: any): Observable<any> {
    return this.http.post(`${this.API_URL}/paginas-sistema`, pagina);
  }

  updatePagina(id: number, pagina: any): Observable<any> {
    return this.http.put(`${this.API_URL}/paginas-sistema/${id}`, pagina);
  }

  deletePagina(id: number): Observable<any> {
    return this.http.delete(`${this.API_URL}/paginas-sistema/${id}`);
  }

  getCategorias(): Observable<any> {
    return this.http.get(`${this.API_URL}/paginas-sistema/categorias`);
  }

  // Utilitários
  getCoresDisponiveis(): string[] {
    return [
      '#dc3545', // Vermelho
      '#fd7e14', // Laranja
      '#ffc107', // Amarelo
      '#28a745', // Verde
      '#20c997', // Verde água
      '#17a2b8', // Azul claro
      '#007bff', // Azul
      '#6f42c1', // Roxo
      '#e83e8c', // Rosa
      '#6c757d', // Cinza
      '#343a40', // Cinza escuro
      '#000000'  // Preto
    ];
  }

  getIconesDisponiveis(): string[] {
    return [
      'shield',
      'people',
      'person',
      'people-circle',
      'star',
      'diamond',
      'crown',
      'ribbon',
      'medal',
      'trophy',
      'flag',
      'bookmark',
      'heart',
      'thumbs-up',
      'checkmark-circle',
      'lock-closed',
      'key',
      'settings',
      'cog',
      'construct',
      'build',
      'hammer',
      'wrench',
      'nut',
      'gear'
    ];
  }

  getCategoriasPadrao(): string[] {
    return [
      'Administração',
      'Sistema',
      'Gerenciamento',
      'Relatórios',
      'Configurações',
      'Geral'
    ];
  }
}
