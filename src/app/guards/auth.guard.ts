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
      // Verificação local para DEV e ADMIN
      const usuario = this.authService.getUsuarioAtual();
      if (!usuario) {
        this.router.navigate(['/login']);
        return of(false);
      }

      // DEV tem acesso total
      if (usuario.nivel_acesso === 'dev') {
        return of(true);
      }

      // Verificação básica por nível
      const podeAcessar = this.verificarAcessoLocal(usuario, pagina);
      if (!podeAcessar) {
        this.router.navigate(['/unauthorized']);
        return of(false);
      }
    }

    return of(true);
  }

  private verificarAcessoLocal(usuario: any, pagina: string): boolean {
    const niveis = ['cliente', 'vendedor', 'gerente', 'admin', 'dev'];
    const nivelUsuario = niveis.indexOf(usuario.nivel_acesso);

    // Páginas administrativas
    if (pagina.startsWith('admin/')) {
      return nivelUsuario >= 1; // vendedor ou superior
    }

    // Páginas específicas
    switch (pagina) {
      case 'orcamento':
        return nivelUsuario >= 1; // vendedor ou superior
      case 'produtos':
        return nivelUsuario >= 1; // vendedor ou superior
      case 'painel':
        return true; // todos podem acessar
      default:
        return true;
    }
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
      'orcamento': 'orcamento',
      'produtos': 'produtos'
    };

    return mapeamentoRotas[url] || null;
  }
}
