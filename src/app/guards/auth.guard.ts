import { Injectable } from '@angular/core';
import { CanActivate, Router, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { AuthService } from '../services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {
  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): Observable<boolean> {
    // Verificar se usuário está logado
    if (!this.authService.isLoggedIn()) {
      this.router.navigate(['/login']);
      return of(false);
    }

    // Verificar permissão específica da página
    const pagina = this.obterPaginaDaRota(route);
    if (pagina) {
      return this.authService.verificarPermissao(pagina).pipe(
        map(podeAcessar => {
          if (!podeAcessar) {
            this.router.navigate(['/unauthorized']);
            return false;
          }
          return true;
        }),
        catchError(() => {
          this.router.navigate(['/login']);
          return of(false);
        })
      );
    }

    return of(true);
  }

  private obterPaginaDaRota(route: ActivatedRouteSnapshot): string | null {
    const url = route.url.map(segment => segment.path).join('/');

    // Mapear rotas para páginas do sistema
    const mapeamentoRotas: { [key: string]: string } = {
      'admin/gestao-leads': 'admin/gestao-leads',
      'admin/gestao-orcamentos': 'admin/gestao-orcamentos',
      'admin/gestao-clientes': 'admin/gestao-clientes',
      'admin/gestao-pedidos': 'admin/gestao-pedidos',
      'admin/financeiro': 'admin/financeiro',
      'admin/agenda': 'admin/agenda',
      'admin/relatorios': 'admin/relatorios',
      'painel': 'painel',
      'orcamento': 'orcamento',
      'produtos': 'produtos'
    };

    return mapeamentoRotas[url] || null;
  }
}
