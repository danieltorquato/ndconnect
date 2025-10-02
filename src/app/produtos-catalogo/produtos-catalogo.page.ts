import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { addIcons } from 'ionicons';
import {
  logoWhatsapp, mailOutline, construct, flash, musicalNotes,
  bulb, desktop, checkmarkCircle, closeCircle, sparkles, grid
} from 'ionicons/icons';
import {
  IonContent, IonButton, IonIcon, IonCard, IonCardContent
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { NavbarComponent } from "../shared/navbar/navbar.component";

interface Produto {
  id: number;
  nome: string;
  descricao: string;
  icone: string;
  disponivel: boolean;
}

@Component({
  selector: 'app-produtos-catalogo',
  templateUrl: './produtos-catalogo.page.html',
  styleUrls: ['./produtos-catalogo.page.scss'],
  standalone: true,
  imports: [
    CommonModule,
    IonContent,
    IonButton,
    IonIcon,
    IonCard,
    IonCardContent,
    NavbarComponent
]
})
export class ProdutosCatalogoPage implements OnInit {
  produtos: { id: number; nome: string; descricao: string; icone: string; disponivel: boolean; }[] = [];

  constructor(
    private router: Router,
    private http: HttpClient
  ) {
    addIcons({
      logoWhatsapp, mailOutline, construct, flash, musicalNotes,
      bulb, desktop, checkmarkCircle, closeCircle, sparkles, grid
    });
  }

  ngOnInit() {
    this.carregarProdutos();
  }

  carregarProdutos() {
    this.produtos = [
      {
        id: 1,
        nome: 'Palco',
        descricao: 'Estruturas modulares seguras e resistentes, desde pequenos eventos até grandes produções',
        icone: 'construct',
        disponivel: true
      },
      {
        id: 2,
        nome: 'Gerador',
        descricao: 'Geradores silenciosos e confiáveis para garantir energia do evento, independente das condições',
        icone: 'flash',
        disponivel: true
      },
      {
        id: 3,
        nome: 'Efeitos',
        descricao: 'Fumaça, bolhas, confetes, lasers e muito mais para momentos inesquecíveis',
        icone: 'sparkles',
        disponivel: true
      },
      {
        id: 4,
        nome: 'Stand Octanorme',
        descricao: 'Estruturas modulares profissionais para stands, exposições e eventos corporativos',
        icone: 'grid',
        disponivel: true
      },
      {
        id: 5,
        nome: 'Som',
        descricao: 'Sistemas de áudio de última geração com potência e qualidade cristalina para qualquer porte de evento',
        icone: 'musical-notes',
        disponivel: true
      },
      {
        id: 6,
        nome: 'Luz',
        descricao: 'Transforme ambientes com iluminação profissional, efeitos especiais e controle total de atmosfera',
        icone: 'bulb',
        disponivel: true
      },
      {
        id: 7,
        nome: 'Painel de LED',
        descricao: 'Telões de LED de alta definição para máximo impacto visual em apresentações e transmissões',
        icone: 'desktop',
        disponivel: true
      }
    ];
  }

  contatarWhatsApp(produto: Produto) {
    const numero = '5511953898557';
    const mensagem = encodeURIComponent(
      `Olá! Tenho interesse no produto: ${produto.nome}\n\n` +
      `Podem me enviar mais informações sobre disponibilidade e condições?`
    );
    window.open(`https://wa.me/${numero}?text=${mensagem}`, '_blank');
  }

  solicitarOrcamento() {
    this.router.navigate(['/orcamento-cliente']);
  }

  contatarWhatsAppPersonalizado() {
    const numero = '5511953898557';
    const mensagem = encodeURIComponent(
      `Olá! Tenho interesse em saber mais sobre produtos personalizados para meu evento.\n\n` +
      `Podem me enviar mais informações?`
    );
    window.open(`https://wa.me/${numero}?text=${mensagem}`, '_blank');
  }
}
