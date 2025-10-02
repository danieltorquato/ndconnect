import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import {
  calculator,
  cube,
  time,
  grid,
  link,
  mail,
  globe,
  statsChart,
  flash,
  addCircle,
  search,
  folder,
  document, logoInstagram, logoWhatsapp, logoFacebook, logoYoutube, logoLinkedin, logoGoogle } from 'ionicons/icons';
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
  IonIcon
} from '@ionic/angular/standalone';

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
    IonIcon
  ]
})
export class PainelPage implements OnInit {
  totalProdutos: number = 0;
  totalCategorias: number = 0;
  totalOrcamentos: number = 0;

  // Configuração dos links úteis
  private linksUteis = {
    email: 'https://mail.google.com',
    instagram: 'https://instagram.com/ndconnect_oficial',
    site: 'https://www.ndconnect.com.br',
    whatsapp: 'https://wa.me/5511999999999',
    facebook: 'https://facebook.com/ndconnect',
    youtube: 'https://youtube.com/@ndconnect',
    linkedin: 'https://linkedin.com/company/ndconnect',
    google: 'https://business.google.com'
  };

  constructor(
    private http: HttpClient,
    private router: Router
  ) {
    addIcons({grid,calculator,cube,time,link,mail,logoInstagram,globe,logoWhatsapp,logoFacebook,logoYoutube,logoLinkedin,logoGoogle,statsChart,folder,document,flash,addCircle,search});
  }

  ngOnInit() {
    this.carregarEstatisticas();
  }

  carregarEstatisticas() {
    // Carregar total de produtos
    this.http.get<any[]>('http://localhost:8000/api/produtos').subscribe(
      (data) => {
        this.totalProdutos = data.length;
      },
      (error) => {
        console.error('Erro ao carregar produtos:', error);
      }
    );

    // Carregar total de categorias
    this.http.get<any[]>('http://localhost:8000/api/categorias').subscribe(
      (data) => {
        this.totalCategorias = data.length;
      },
      (error) => {
        console.error('Erro ao carregar categorias:', error);
      }
    );

    // Carregar total de orçamentos
    this.http.get<any[]>('http://localhost:8000/api/orcamentos').subscribe(
      (data) => {
        this.totalOrcamentos = data.length;
      },
      (error) => {
        console.error('Erro ao carregar orçamentos:', error);
      }
    );
  }

  irParaOrcamento() {
    this.router.navigate(['/orcamento']);
  }

  irParaProdutos() {
    this.router.navigate(['/produtos']);
  }

  abrirHistorico() {
    window.open('http://localhost:8000/historico_orcamentos.php', '_blank');
  }

  abrirLink(tipo: string) {
    const url = this.linksUteis[tipo as keyof typeof this.linksUteis];
    if (url) {
      window.open(url, '_blank');
    }
  }
}
