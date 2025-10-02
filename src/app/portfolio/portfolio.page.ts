import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import {
  logoWhatsapp,
  mail,
  musicalNotes,
  business,
  pizza,
  school,
  mic,
  heart
} from 'ionicons/icons';
import {
  IonHeader,
  IonToolbar,
  IonContent,
  IonButton,
  IonIcon
} from '@ionic/angular/standalone';
import { NavbarComponent } from "../shared/navbar/navbar.component";

@Component({
  selector: 'app-portfolio',
  templateUrl: './portfolio.page.html',
  styleUrls: ['./portfolio.page.scss'],
  standalone: true,
  imports: [IonHeader, IonToolbar, IonContent, IonButton, IonIcon, NavbarComponent]
})
export class PortfolioPage {
  constructor(private router: Router) {
    addIcons({
      logoWhatsapp,
      mail,
      musicalNotes,
      business,
      pizza,
      school,
      mic,
      heart
    });
  }

  irPara(rota: string) {
    this.router.navigate([rota]);
  }

  abrirWhatsApp() {
    const numero = '5511953898557';
    const mensagem = encodeURIComponent('Olá! Gostaria de saber mais sobre os serviços da N.D Connect.');
    window.open(`https://wa.me/${numero}?text=${mensagem}`, '_blank');
  }
}
