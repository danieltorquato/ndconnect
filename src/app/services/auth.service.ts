import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { tap, map } from 'rxjs/operators';
import { environment } from '../../environments/environment';

export interface Usuario {
  id: number;
  nome: string;
  email: string;
  nivel_acesso: 'dev' | 'admin' | 'gerente' | 'vendedor' | 'cliente';
  nivel_id?: number;
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
  login(email: string, senha: string): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.API_URL}?action=login`, {
      email,
      senha
    }).pipe(
      tap(response => {
        if (response.success && response.usuario && response.token) {
          this.salvarSessao(response.usuario, response.token);
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
    if (usuario.nivel_id) {
      // Por enquanto, manter compatibilidade com o sistema antigo
      // TODO: Implementar verificação por nivel_id
      const niveis = ['cliente', 'vendedor', 'gerente', 'admin', 'dev'];
      const nivelUsuario = niveis.indexOf(usuario.nivel_acesso);
      const nivelRequerido = niveis.indexOf(nivel);
      return nivelUsuario >= nivelRequerido;
    }

    // Sistema antigo - incluir dev
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
}
