import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: 'home',
    loadComponent: () => import('./home/home.page').then((m) => m.HomePage),
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
  },
  {
    path: 'orcamento',
    loadComponent: () => import('./orcamento/orcamento.page').then((m) => m.OrcamentoPage),
  },
  {
    path: 'produtos',
    loadComponent: () => import('./produtos/produtos.page').then((m) => m.ProdutosPage),
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
    path: 'gestao-leads',
    loadComponent: () => import('./admin/gestao-leads/gestao-leads.page').then( m => m.GestaoLeadsPage)
  },

  {
    path: 'gestao-orcamentos',
    loadComponent: () => import('./admin/gestao-orcamentos/gestao-orcamentos.page').then( m => m.GestaoOrcamentosPage)
  },
  {
    path: 'painel-orcamento',
    loadComponent: () => import('./painel-orcamento/painel-orcamento.page').then( m => m.PainelOrcamentoPage)
  },
  {
    path: 'admin/gestao-clientes',
    loadComponent: () => import('./admin/gestao-clientes/gestao-clientes.page').then( m => m.GestaoClientesPage)
  },
  {
    path: 'admin/gestao-pedidos',
    loadComponent: () => import('./admin/gestao-pedidos/gestao-pedidos.page').then( m => m.GestaoPedidosPage)
  },
  {
    path: 'admin/financeiro',
    loadComponent: () => import('./admin/financeiro/financeiro.page').then( m => m.FinanceiroPage)
  },
  {
    path: 'admin/agenda',
    loadComponent: () => import('./admin/agenda/agenda.page').then( m => m.AgendaPage)
  },
  {
    path: 'admin/relatorios',
    loadComponent: () => import('./admin/relatorios/relatorios.page').then( m => m.RelatoriosPage)
  },

];
