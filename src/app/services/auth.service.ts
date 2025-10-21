import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { tap, map } from 'rxjs/operators';
import { environment } from '../../environments/environment';

export interface Usuario {
  id: number;
  nome: string;
  usuario: string;
  email: string;
  nivel_acesso: 'dev' | 'admin' | 'gerente' | 'vendedor' | 'cliente';
  nivel_id?: number;
  funcionario_id?: number;
  ativo: boolean;
  data_criacao: string;
  data_atualizacao: string;
  funcionario?: {
    id: number;
    nome_completo: string;
    email?: string;
    cargo: string;
    departamento?: string;
    status: string;
    endereco?: string;
    numero_endereco?: string;
    cidade?: string;
    estado?: string;
  };
  nivel_info?: {
    id: number;
    nome: string;
    descricao: string;
    cor: string;
    ordem: number;
  };
}

export interface LoginResponse {
  success: boolean;
  message: string;
  usuario?: Usuario;
  token?: string;
}

export interface RegisterData {
  nome: string;
  email: string;
  senha: string;
  nivel_acesso?: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly API_URL = `${environment.apiUrl}/auth`;
  private readonly TOKEN_KEY = 'auth_token';
  private readonly USER_KEY = 'auth_user';

  private usuarioSubject = new BehaviorSubject<Usuario | null>(null);
  public usuario$ = this.usuarioSubject.asObservable();

  constructor(private http: HttpClient) {
    this.carregarUsuarioSalvo();
  }

  // Fazer login
  login(usuario: string, senha: string): Observable<LoginResponse> {
    console.log('AuthService: Iniciando login para usuário:', usuario);
    return this.http.post<LoginResponse>(`${this.API_URL}?action=login`, {
      usuario,
      senha
    }).pipe(
      tap(response => {
        console.log('AuthService: Resposta do login:', response);
        if (response.success && response.usuario && response.token) {
          console.log('AuthService: Salvando sessão do usuário:', response.usuario.nome);
          this.salvarSessao(response.usuario, response.token);
        } else {
          console.log('AuthService: Login falhou ou dados incompletos');
        }
      })
    );
  }

  // Registrar usuário
  register(data: RegisterData): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.API_URL}?action=register`, data);
  }

  // Fazer logout
  logout(): Observable<any> {
    const token = this.getToken();
    if (token) {
      this.http.post(`${this.API_URL}?action=logout`, { token }).subscribe();
    }
    this.limparSessao();
    return new Observable(observer => {
      observer.next({ success: true });
      observer.complete();
    });
  }

  // Verificar se usuário está logado
  isLoggedIn(): boolean {
    const token = this.getToken();
    const usuario = this.getUsuarioAtual();

    // Verificar se tem token e usuário
    if (!token || !usuario) {
      return false;
    }

    // Para tokens simples (não JWT), apenas verificar se existem
    // A validação real será feita no backend quando necessário
    return true;
  }

  // Obter usuário atual
  getUsuarioAtual(): Usuario | null {
    return this.usuarioSubject.value;
  }

  // Verificar permissão de acesso
  verificarPermissao(pagina: string): Observable<boolean> {
    const token = this.getToken();
    if (!token) {
      return new Observable(observer => {
        observer.next(false);
        observer.complete();
      });
    }

    return this.http.get<{success: boolean, pode_acessar: boolean}>(
      `${this.API_URL}?action=check-permission&token=${token}&pagina=${pagina}`
    ).pipe(
      tap(response => {
        if (!response.success) {
          this.limparSessao();
        }
      }),
      map(response => response.pode_acessar)
    );
  }

  // Verificar token no servidor
  verificarToken(): Observable<{success: boolean, usuario?: Usuario}> {
    const token = this.getToken();
    if (!token) {
      return new Observable(observer => {
        observer.next({ success: false });
        observer.complete();
      });
    }

    return this.http.get<{success: boolean, usuario?: Usuario}>(
      `${this.API_URL}?action=verify&token=${token}`
    ).pipe(
      tap(response => {
        if (response.success && response.usuario) {
          this.usuarioSubject.next(response.usuario);
        } else {
          this.limparSessao();
        }
      })
    );
  }

  // Obter token
  private getToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY);
  }

  // Salvar sessão
  private salvarSessao(usuario: Usuario, token: string): void {
    localStorage.setItem(this.TOKEN_KEY, token);
    localStorage.setItem(this.USER_KEY, JSON.stringify(usuario));
    this.usuarioSubject.next(usuario);
  }

  // Limpar sessão
  private limparSessao(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    localStorage.removeItem(this.USER_KEY);
    this.usuarioSubject.next(null);
  }

  // Carregar usuário salvo
  private carregarUsuarioSalvo(): void {
    const usuarioSalvo = localStorage.getItem(this.USER_KEY);
    const token = localStorage.getItem(this.TOKEN_KEY);

    if (usuarioSalvo && token) {
      try {
        const usuario = JSON.parse(usuarioSalvo);
        this.usuarioSubject.next(usuario);
        // Não verificar token automaticamente na inicialização
        // A verificação será feita quando necessário
      } catch (error) {
        this.limparSessao();
      }
    }
  }

  // Verificar se usuário tem nível específico
  temNivel(nivel: string): boolean {
    const usuario = this.getUsuarioAtual();
    if (!usuario) return false;

    // Se o usuário tem nivel_id, usar o novo sistema
    if (usuario.nivel_id && usuario.nivel_info) {
      // Usar a informação do nível do banco de dados
      const niveis = ['cliente', 'vendedor', 'gerente', 'admin', 'dev'];
      const nivelUsuario = niveis.indexOf(usuario.nivel_info.nome);
      const nivelRequerido = niveis.indexOf(nivel);
      return nivelUsuario >= nivelRequerido;
    }

    // Fallback para o sistema antigo
    const niveis = ['cliente', 'vendedor', 'gerente', 'admin', 'dev'];
    const nivelUsuario = niveis.indexOf(usuario.nivel_acesso);
    const nivelRequerido = niveis.indexOf(nivel);

    return nivelUsuario >= nivelRequerido;
  }

  // Verificar se é admin
  isAdmin(): boolean {
    return this.temNivel('admin');
  }

  // Verificar se é dev (acesso total)
  isDev(): boolean {
    return this.temNivel('dev');
  }

  // Verificar se tem acesso total (dev ou admin)
  hasFullAccess(): boolean {
    return this.isDev() || this.isAdmin();
  }

  // Verificar se é gerente ou superior
  isGerente(): boolean {
    return this.temNivel('gerente');
  }

  // Verificar se é vendedor ou superior
  isVendedor(): boolean {
    return this.temNivel('vendedor');
  }

  // Verificar se usuário tem nível específico por ID
  temNivelPorId(nivelId: number): boolean {
    const usuario = this.getUsuarioAtual();
    if (!usuario) return false;

    return usuario.nivel_id === nivelId;
  }

  // Obter informações do nível do usuário
  getNivelInfo(): any {
    const usuario = this.getUsuarioAtual();
    if (!usuario) return null;

    return usuario.nivel_info || null;
  }

  // Verificar se usuário tem permissão específica
  temPermissao(permissao: string): boolean {
    const usuario = this.getUsuarioAtual();
    if (!usuario) return false;

    // Se tem nivel_id, usar o sistema de permissões do banco
    if (usuario.nivel_id) {
      // Esta verificação será feita no backend
      return true; // O backend fará a verificação real
    }

    // Fallback para o sistema antigo
    return this.temNivel('admin');
  }
}
