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
    console.log('AuthGuard: Verificando acesso para rota:', state.url);

    // Verificar se usuário está logado
    if (!this.authService.isLoggedIn()) {
      console.log('AuthGuard: Usuário não está logado, redirecionando para login');
      this.router.navigate(['/login']);
      return of(false);
    }

    const usuario = this.authService.getUsuarioAtual();
    console.log('AuthGuard: Usuário logado:', usuario?.nome, 'Nível:', usuario?.nivel_acesso);

    // Verificar permissão específica da página
    const pagina = this.obterPaginaDaRota(route);
    console.log('AuthGuard: Página identificada:', pagina);

    if (pagina) {
      if (!usuario) {
        console.log('AuthGuard: Usuário não encontrado, redirecionando para login');
        this.router.navigate(['/login']);
        return of(false);
      }

      // DEV tem acesso total
      if (usuario.nivel_acesso === 'dev') {
        console.log('AuthGuard: Usuário DEV tem acesso total');
        return of(true);
      }

      // Para painel-orcamento, permitir acesso para admin, gerente e vendedor
      if (pagina === 'painel-orcamento') {
        const niveisPermitidos = ['admin', 'gerente', 'vendedor'];
        if (niveisPermitidos.includes(usuario.nivel_acesso)) {
          console.log('AuthGuard: Usuário tem acesso ao painel-orcamento');
          return of(true);
        } else {
          console.log('AuthGuard: Usuário não tem acesso ao painel-orcamento');
          this.router.navigate(['/unauthorized']);
          return of(false);
        }
      }

      // Para tutorial, permitir acesso apenas para dev e admin
      if (pagina === 'tutorial') {
        const niveisPermitidos = ['dev', 'admin'];
        if (niveisPermitidos.includes(usuario.nivel_acesso)) {
          console.log('AuthGuard: Usuário tem acesso ao tutorial');
          return of(true);
        } else {
          console.log('AuthGuard: Usuário não tem acesso ao tutorial');
          this.router.navigate(['/unauthorized']);
          return of(false);
        }
      }

      // Verificar permissão no backend para outras páginas
      return this.authService.verificarPermissao(pagina).pipe(
        map(podeAcessar => {
          console.log('AuthGuard: Verificação de permissão no backend:', podeAcessar);
          if (!podeAcessar) {
            this.router.navigate(['/unauthorized']);
            return false;
          }
          return true;
        }),
        catchError(error => {
          console.error('AuthGuard: Erro ao verificar permissão:', error);
          this.router.navigate(['/unauthorized']);
          return of(false);
        })
      );
    }

    console.log('AuthGuard: Página não requer verificação de permissão');
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
      'admin/niveis-acesso': 'admin/niveis-acesso',
      'painel': 'painel',
      'painel-orcamento': 'painel-orcamento',
      'orcamento': 'orcamento',
      'produtos': 'produtos',
      'tutorial': 'tutorial'
    };

    return mapeamentoRotas[url] || null;
  }
}
