import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import {
  home, people, document, cart, cash, statsChart,
  calendar, cube, settings, notifications, search,
  personAdd, documentText, checkmarkCircle, closeCircle,
  timeOutline, trendingUp
} from 'ionicons/icons';
import {
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardContent,
  IonButton,
  IonIcon,
  IonGrid,
  IonRow,
  IonCol,
  IonBadge,
  IonList,
  IonItem,
  IonLabel
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';

interface DashboardData {
  leads_novos: number;
  orcamentos_pendentes: number;
  pedidos_abertos: number;
  contas_receber_vencidas: number;
  vendas_mes: number;
  ticket_medio: number;
}

@Component({
  selector: 'app-painel',
  templateUrl: './painel.page.html',
  styleUrls: ['./painel.page.scss'],
  standalone: true,
  imports: [
    IonHeader,
    IonToolbar,
    IonTitle,
    IonContent,
    IonCard,
    IonCardHeader,
    IonCardTitle,
    IonCardContent,
    IonButton,
    IonIcon,
    IonGrid,
    IonRow,
    IonCol,
    IonBadge,
    IonList,
    IonItem,
    IonLabel,
    CommonModule
  ]
})
export class PainelPage implements OnInit {
  dashboard: DashboardData = {
    leads_novos: 0,
    orcamentos_pendentes: 0,
    pedidos_abertos: 0,
    contas_receber_vencidas: 0,
    vendas_mes: 0,
    ticket_medio: 0
  };

  private apiUrl = 'http://localhost:8000';

  constructor(
    private http: HttpClient,
    private router: Router
  ) {
    addIcons({
      home, people, document, cart, cash, statsChart,
      calendar, cube, settings, notifications, search,
      personAdd, documentText, checkmarkCircle, closeCircle,
      timeOutline, trendingUp
    });
  }

  ngOnInit() {
    this.carregarDashboard();
  }

  carregarDashboard() {
    this.http.get<any>(`${this.apiUrl}/dashboard`).subscribe({
      next: (response) => {
        if (response.success) {
          this.dashboard = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar dashboard:', error);
      }
    });
  }

  navegarPara(rota: string) {
    this.router.navigate([rota]);
  }
}
