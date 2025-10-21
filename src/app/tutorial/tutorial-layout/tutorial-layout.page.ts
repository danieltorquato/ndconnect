import { Component, OnInit } from '@angular/core';
import { Router, NavigationEnd, RouterOutlet } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import {
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonButton,
  IonIcon,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardContent,
  IonButtons,
  IonBreadcrumbs,
  IonBreadcrumb
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  arrowBack,
  home,
  school,
  list,
  chevronForward, people, calculator, cube, analytics, settings } from 'ionicons/icons';

@Component({
  selector: 'app-tutorial-layout',
  templateUrl: './tutorial-layout.page.html',
  styleUrls: ['./tutorial-layout.page.scss'],
  standalone: true,
  imports: [
    IonHeader,
    IonToolbar,
    IonTitle,
    IonContent,
    IonButton,
    IonIcon,
    IonCard,
    IonCardHeader,
    IonCardTitle,
    IonCardContent,
    IonButtons,
    IonBreadcrumbs,
    IonBreadcrumb,
    CommonModule,
    FormsModule,
    RouterOutlet
  ]
})
export class TutorialLayoutPage implements OnInit {
  currentSection: string = '';
  breadcrumbs: Array<{label: string, path: string}> = [];

  constructor(private router: Router) {
    addIcons({arrowBack,school,home,chevronForward,list,people,calculator,cube,analytics,settings});
  }

  ngOnInit() {
    // Escutar mudanças de rota para atualizar breadcrumbs
    this.router.events.subscribe(event => {
      if (event instanceof NavigationEnd) {
        this.updateBreadcrumbs(event.url);
      }
    });
  }

  updateBreadcrumbs(url: string) {
    this.breadcrumbs = [
      { label: 'Tutorial', path: '/tutorial' }
    ];

    if (url.includes('/painel-principal')) {
      this.currentSection = 'painel-principal';
      this.breadcrumbs.push({ label: 'Painel Principal', path: '/tutorial/painel-principal' });
    } else if (url.includes('/gestao-leads')) {
      this.currentSection = 'gestao-leads';
      this.breadcrumbs.push({ label: 'Gestão de Leads', path: '/tutorial/gestao-leads' });
    } else if (url.includes('/orcamentos')) {
      this.currentSection = 'orcamentos';
      this.breadcrumbs.push({ label: 'Orçamentos', path: '/tutorial/orcamentos' });
    } else if (url.includes('/produtos')) {
      this.currentSection = 'produtos';
      this.breadcrumbs.push({ label: 'Produtos', path: '/tutorial/produtos' });
    } else if (url.includes('/relatorios')) {
      this.currentSection = 'relatorios';
      this.breadcrumbs.push({ label: 'Relatórios', path: '/tutorial/relatorios' });
    } else if (url.includes('/configuracoes')) {
      this.currentSection = 'configuracoes';
      this.breadcrumbs.push({ label: 'Configurações', path: '/tutorial/configuracoes' });
    } else {
      this.currentSection = 'home';
    }
  }

  voltarPainel() {
    this.router.navigate(['/painel-orcamento']);
  }

  irParaSecao(secao: string) {
    this.router.navigate([`/tutorial/${secao}`]);
  }

  voltarTutorial() {
    this.router.navigate(['/tutorial']);
  }
}
