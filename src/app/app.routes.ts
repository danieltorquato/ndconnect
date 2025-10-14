import { Routes } from '@angular/router';
import { AuthGuard } from './guards/auth.guard';

export const routes: Routes = [
  {
    path: 'home',
    loadComponent: () => import('./home/home.page').then((m) => m.HomePage),
  },
  {
    path: 'login',
    loadComponent: () => import('./login/login.page').then((m) => m.LoginPage),
  },
  {
    path: 'unauthorized',
    loadComponent: () => import('./unauthorized/unauthorized.page').then((m) => m.UnauthorizedPage),
  },
  {
    path: 'sobre',
    loadComponent: () => import('./sobre/sobre.page').then((m) => m.SobrePage),
  },
  {
    path: 'produtos-catalogo',
    loadComponent: () => import('./produtos-catalogo/produtos-catalogo.page').then((m) => m.ProdutosCatalogoPage),
  },
  {
    path: 'orcamento-cliente',
    loadComponent: () => import('./orcamento-cliente/orcamento-cliente.page').then((m) => m.OrcamentoClientePage),
  },
  {
    path: 'portfolio',
    loadComponent: () => import('./portfolio/portfolio.page').then((m) => m.PortfolioPage),
  },
  {
    path: 'contato',
    loadComponent: () => import('./contato/contato.page').then((m) => m.ContatoPage),
  },
  {
    path: 'painel',
    loadComponent: () => import('./painel/painel.page').then((m) => m.PainelPage),
    canActivate: [AuthGuard],
  },
  {
    path: 'orcamento',
    loadComponent: () => import('./orcamento/orcamento.page').then((m) => m.OrcamentoPage),
    canActivate: [AuthGuard],
  },
  {
    path: 'produtos',
    loadComponent: () => import('./produtos/produtos.page').then((m) => m.ProdutosPage),
    canActivate: [AuthGuard],
  },
  {
    path: '',
    redirectTo: 'home',
    pathMatch: 'full',
  },
  {
    path: 'solicitar-orcamento',
    loadComponent: () => import('./solicitar-orcamento/solicitar-orcamento.page').then( m => m.SolicitarOrcamentoPage)
  },
  {
    path: 'admin/gestao-leads',
    loadComponent: () => import('./admin/gestao-leads/gestao-leads.page').then( m => m.GestaoLeadsPage),
    canActivate: [AuthGuard],
  },

  {
    path: 'admin/gestao-orcamentos',
    loadComponent: () => import('./admin/gestao-orcamentos/gestao-orcamentos.page').then( m => m.GestaoOrcamentosPage),
    canActivate: [AuthGuard],
  },
  {
    path: 'painel-orcamento',
    loadComponent: () => import('./painel-orcamento/painel-orcamento.page').then( m => m.PainelOrcamentoPage),
    canActivate: [AuthGuard],
  },
  {
    path: 'admin/gestao-clientes',
    loadComponent: () => import('./admin/gestao-clientes/gestao-clientes.page').then( m => m.GestaoClientesPage),
    canActivate: [AuthGuard],
  },
  {
    path: 'admin/gestao-pedidos',
    loadComponent: () => import('./admin/gestao-pedidos/gestao-pedidos.page').then( m => m.GestaoPedidosPage),
    canActivate: [AuthGuard],
  },
  {
    path: 'admin/financeiro',
    loadComponent: () => import('./admin/financeiro/financeiro.page').then( m => m.FinanceiroPage),
    canActivate: [AuthGuard],
  },
  {
    path: 'admin/agenda',
    loadComponent: () => import('./admin/agenda/agenda.page').then( m => m.AgendaPage),
    canActivate: [AuthGuard],
  },
  {
    path: 'admin/relatorios',
    loadComponent: () => import('./admin/relatorios/relatorios.page').then( m => m.RelatoriosPage),
    canActivate: [AuthGuard],
  },
  {
    path: 'admin/niveis-acesso',
    loadComponent: () => import('./admin/niveis-acesso/niveis-acesso.page').then( m => m.NiveisAcessoPage),
    canActivate: [AuthGuard],
  },
  {
    path: 'admin/niveis-acesso/:id/permissoes',
    loadComponent: () => import('./admin/niveis-acesso/permissoes/permissoes.page').then( m => m.PermissoesPage),
    canActivate: [AuthGuard],
  },
  {
    path: 'admin/gestao-usuarios',
    loadComponent: () => import('./admin/gestao-usuarios/gestao-usuarios.page').then( m => m.GestaoUsuariosPage),
    canActivate: [AuthGuard],
  },
  {
    path: 'admin/gestao-funcionarios',
    loadComponent: () => import('./admin/gestao-funcionarios/gestao-funcionarios.page').then( m => m.GestaoFuncionariosPage),
    canActivate: [AuthGuard],
  },


];
