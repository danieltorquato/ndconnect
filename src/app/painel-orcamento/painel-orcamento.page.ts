import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import {
  IonContent,
  IonHeader,
  IonTitle,
  IonToolbar,
  IonCard,
  IonCardContent,
  IonCardHeader,
  IonCardTitle,
  IonButton,
  IonIcon,
  IonGrid,
  IonRow,
  IonCol,
  IonBadge, IonButtons } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  calculator,
  cube,
  time,
  settings,
  statsChart,
  documentText,
  addCircle,
  list,
  analytics,
  cog,
  checkmarkCircle,
  trendingUp, home, logOut } from 'ionicons/icons';

@Component({
  selector: 'app-painel-orcamento',
  templateUrl: './painel-orcamento.page.html',
  styleUrls: ['./painel-orcamento.page.scss'],
  standalone: true,
  imports: [IonButtons,
    IonContent,
    IonHeader,
    IonTitle,
    IonToolbar,
    IonCard,
    IonCardContent,
    IonCardHeader,
    IonCardTitle,
    IonButton,
    IonIcon,
    IonGrid,
    IonRow,
    IonCol,
    IonBadge,
    CommonModule,
    FormsModule
  ]
})
export class PainelOrcamentoPage implements OnInit {

  constructor(private router: Router) {
    addIcons({calculator,home,logOut,cube,time,settings,analytics,addCircle,list,statsChart,documentText,checkmarkCircle,trendingUp,cog});
  }

  ngOnInit() {
  }

  // Navegação para diferentes seções
  irParaOrcamentos() {
    this.router.navigate(['/orcamento']);
  }

  irParaProdutos() {
    this.router.navigate(['/produtos']);
  }

  irParaHistoricoOrcamentos() {
    this.router.navigate(['/gestao-orcamentos']);
  }

  irParaGestaoOrcamentos() {
    this.router.navigate(['/admin/gestao-orcamentos']);
  }

  irParaRelatorios() {
    this.router.navigate(['/admin/relatorios']);
  }

  irParaConfiguracoes() {
    this.router.navigate(['/admin/gestao-orcamentos']);
  }

  navegarPara(rota: string) {
    this.router.navigate([rota]);
  }

  logout() {
    // Limpar dados de autenticação
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_user');

    // Redirecionar para login
    this.router.navigate(['/login']);
  }

}
