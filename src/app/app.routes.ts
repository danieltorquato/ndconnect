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
];
