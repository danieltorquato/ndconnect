import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject, of } from 'rxjs';
import { tap, catchError } from 'rxjs/operators';
import { environment } from '../../environments/environment';

export interface Lead {
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

export interface LeadStats {
  novos: number;
  contatados: number;
  qualificados: number;
  convertidos: number;
  perdidos: number;
  total: number;
}

@Injectable({
  providedIn: 'root'
})
export class LeadsService {
  private readonly API_URL = `${environment.apiUrl}/leads`;
  private leadsSubject = new BehaviorSubject<Lead[]>([]);
  private statsSubject = new BehaviorSubject<LeadStats>({
    novos: 0,
    contatados: 0,
    qualificados: 0,
    convertidos: 0,
    perdidos: 0,
    total: 0
  });
  private notificacoesSubject = new BehaviorSubject<number>(0);

  public leads$ = this.leadsSubject.asObservable();
  public stats$ = this.statsSubject.asObservable();
  public notificacoes$ = this.notificacoesSubject.asObservable();

  constructor(private http: HttpClient) {}

  // Carregar todos os leads
  carregarLeads(): Observable<any> {
    return this.http.get<any>(this.API_URL);
  }

  // Carregar leads por status
  carregarLeadsPorStatus(status: string): Observable<any> {
    const url = status === 'todos' ? this.API_URL : `${this.API_URL}?status=${status}`;
    return this.http.get<any>(url);
  }

  // Carregar estatísticas dos leads
  carregarEstatisticas(): Observable<any> {
    return this.http.get<any>(`${this.API_URL}?action=stats`);
  }

  // Atualizar status de um lead
  atualizarStatus(leadId: number, status: string, observacoes?: string): Observable<any> {
    return this.http.put<any>(`${this.API_URL}/${leadId}`, {
      status,
      observacoes
    });
  }

  // Excluir lead
  excluirLead(leadId: number): Observable<any> {
    return this.http.delete<any>(`${this.API_URL}/${leadId}`);
  }

  // Criar orçamento a partir de lead
  criarOrcamento(leadId: number): Observable<any> {
    return this.http.post<any>(`${this.API_URL}/${leadId}/orcamento`, {});
  }

  // Converter lead em cliente
  converterEmCliente(leadId: number): Observable<any> {
    return this.http.post<any>(`${this.API_URL}/${leadId}/converter`, {});
  }

  // Atualizar dados locais
  atualizarLeads(leads: Lead[]) {
    this.leadsSubject.next(leads);
  }

  // Atualizar estatísticas locais
  atualizarStats(stats: LeadStats) {
    this.statsSubject.next(stats);
  }

  // Obter leads atuais
  getLeadsAtuais(): Lead[] {
    return this.leadsSubject.value;
  }

  // Obter estatísticas atuais
  getStatsAtuais(): LeadStats {
    return this.statsSubject.value;
  }

  // Filtrar leads por termo de pesquisa
  filtrarLeads(leads: Lead[], termo: string): Lead[] {
    if (!termo.trim()) {
      return leads;
    }

    const termoLower = termo.toLowerCase();
    return leads.filter(lead =>
      lead.nome.toLowerCase().includes(termoLower) ||
      lead.email.toLowerCase().includes(termoLower) ||
      lead.telefone.includes(termo) ||
      (lead.empresa && lead.empresa.toLowerCase().includes(termoLower)) ||
      lead.mensagem.toLowerCase().includes(termoLower)
    );
  }

  // Calcular estatísticas a partir dos leads
  calcularStats(leads: Lead[]): LeadStats {
    const stats: LeadStats = {
      novos: 0,
      contatados: 0,
      qualificados: 0,
      convertidos: 0,
      perdidos: 0,
      total: leads.length
    };

    leads.forEach(lead => {
      switch (lead.status) {
        case 'novo':
          stats.novos++;
          break;
        case 'contatado':
          stats.contatados++;
          break;
        case 'qualificado':
          stats.qualificados++;
          break;
        case 'convertido':
          stats.convertidos++;
          break;
        case 'perdido':
          stats.perdidos++;
          break;
      }
    });

    return stats;
  }

  // Marcar leads como lidos
  marcarLeadsComoLidos(leadIds: number[]): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/marcar_leads_lidos.php`, {
      lead_ids: leadIds
    }).pipe(
      catchError((error: any) => {
        console.error('Erro ao marcar leads como lidos:', error);
        // Retornar uma resposta de erro estruturada
        return of({
          success: false,
          message: 'Erro ao marcar leads como lidos',
          error: error.message || 'Erro desconhecido'
        });
      })
    );
  }

  // Marcar lead individual como lido
  marcarLeadComoLido(leadId: number): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/marcar_lead_lido.php`, {
      lead_id: leadId
    });
  }

  // Obter leads não lidos
  obterLeadsNaoLidos(): Lead[] {
    return this.leadsSubject.value.filter(lead => lead.lido !== true);
  }

  // Contar leads não lidos
  contarLeadsNaoLidos(): number {
    return this.obterLeadsNaoLidos().length;
  }

  // Contar notificações (apenas leads novos não lidos)
  contarNotificacoes(): number {
    const leadsNaoLidos = this.obterLeadsNaoLidos();
    const notificacoes = leadsNaoLidos.filter(lead =>
      lead.status === 'novo'
    ).length;

    // Atualizar o BehaviorSubject
    this.notificacoesSubject.next(notificacoes);

    return notificacoes;
  }

  // Marcar todos os leads novos como lidos
  marcarTodosComoLidos(): Observable<any> {
    const leadsNovosNaoLidos = this.obterLeadsNaoLidos().filter(lead => lead.status === 'novo');

    if (leadsNovosNaoLidos.length > 0) {
      const leadIds = leadsNovosNaoLidos.map(lead => lead.id);
      console.log('LeadsService: Marcando leads como lidos:', leadIds);

      return this.marcarLeadsComoLidos(leadIds).pipe(
        tap((response: any) => {
          console.log('LeadsService: Resposta ao marcar leads como lidos:', response);
          if (response.success) {
            // Atualizar estado local
            const leadsAtuais = this.leadsSubject.value.map(lead => {
              if (leadIds.includes(lead.id)) {
                return {
                  ...lead,
                  lido: true,
                  data_leitura: new Date().toISOString()
                };
              }
              return lead;
            });
            this.atualizarLeads(leadsAtuais);
            console.log('LeadsService: Estado local atualizado com sucesso');
          } else {
            console.error('LeadsService: Falha ao marcar leads como lidos:', response.message);
          }
        }),
        catchError((error: any) => {
          console.error('LeadsService: Erro ao marcar leads como lidos:', error);
          return of({
            success: false,
            message: 'Erro ao marcar leads como lidos',
            error: error.message || 'Erro desconhecido'
          });
        })
      );
    }

    console.log('LeadsService: Nenhum lead novo para marcar como lido');
    return of({ success: true, message: 'Nenhum lead novo para marcar como lido' });
  }
}
