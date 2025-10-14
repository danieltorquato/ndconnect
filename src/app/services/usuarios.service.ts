import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Usuario {
  id: number;
  nome: string;
  usuario: string;
  email: string;
  nivel_acesso: string;
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
}

export interface VerificacaoUsuario {
  success: boolean;
  existe: boolean;
  message: string;
}

@Injectable({
  providedIn: 'root'
})
export class UsuariosService {
  private readonly API_URL = `${environment.apiUrl}`;

  constructor(private http: HttpClient) { }

  // Verificar se usuário já existe
  verificarUsuario(nome: string, usuarioId?: number): Observable<VerificacaoUsuario> {
    return this.http.post<VerificacaoUsuario>(`${this.API_URL}/verificar-usuario.php`, {
      nome: nome.trim(),
      usuario_id: usuarioId
    });
  }

  // Listar usuários
  listarUsuarios(status?: string): Observable<{success: boolean, data: Usuario[]}> {
    const url = status ? `${this.API_URL}/usuarios.php?status=${status}` : `${this.API_URL}/usuarios.php`;
    return this.http.get<{success: boolean, data: Usuario[]}>(url);
  }

  // Criar usuário
  criarUsuario(usuario: Partial<Usuario>): Observable<{success: boolean, data: Usuario}> {
    return this.http.post<{success: boolean, data: Usuario}>(`${this.API_URL}/usuarios.php`, usuario);
  }

  // Atualizar usuário
  atualizarUsuario(id: number, usuario: Partial<Usuario>): Observable<{success: boolean, data: Usuario}> {
    return this.http.put<{success: boolean, data: Usuario}>(`${this.API_URL}/usuarios.php?id=${id}`, usuario);
  }

  // Excluir usuário
  excluirUsuario(id: number): Observable<{success: boolean}> {
    return this.http.delete<{success: boolean}>(`${this.API_URL}/usuarios.php?id=${id}`);
  }
}
