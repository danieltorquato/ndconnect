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
  data_criacao: string;
  data_atualizacao: string;
}

@Injectable({
  providedIn: 'root'
})
export class NiveisAcessoService {
  private readonly API_URL = `${environment.apiUrl}/niveis-acesso`;

  constructor(private http: HttpClient) { }

  // Listar todos os níveis de acesso
  listarNiveis(): Observable<{success: boolean, data: NivelAcesso[]}> {
    return this.http.get<{success: boolean, data: NivelAcesso[]}>(this.API_URL);
  }

  // Obter nível por ID
  obterNivel(id: number): Observable<{success: boolean, data: NivelAcesso}> {
    return this.http.get<{success: boolean, data: NivelAcesso}>(`${this.API_URL}/${id}`);
  }

  // Criar novo nível
  criarNivel(nivel: Partial<NivelAcesso>): Observable<{success: boolean, data: NivelAcesso}> {
    return this.http.post<{success: boolean, data: NivelAcesso}>(this.API_URL, nivel);
  }

  // Atualizar nível
  atualizarNivel(id: number, nivel: Partial<NivelAcesso>): Observable<{success: boolean, data: NivelAcesso}> {
    return this.http.put<{success: boolean, data: NivelAcesso}>(`${this.API_URL}/${id}`, nivel);
  }

  // Excluir nível
  excluirNivel(id: number): Observable<{success: boolean}> {
    return this.http.delete<{success: boolean}>(`${this.API_URL}/${id}`);
  }
}
