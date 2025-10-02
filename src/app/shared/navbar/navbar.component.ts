import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import {
  menu, close, mailOutline, logoWhatsapp, home, informationCircle,
  cube, images, call, logoInstagram, logoFacebook, logoYoutube, logoLinkedin
} from 'ionicons/icons';
import {
  IonHeader, IonToolbar, IonButton, IonIcon
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss'],
  standalone: true,
  imports: [IonHeader, IonToolbar, IonButton, IonIcon, CommonModule]
})
export class NavbarComponent {
  menuOpen = false;

  private linksUteis = {
    instagram: 'https://instagram.com/ndconnect_oficial',
    facebook: 'https://facebook.com/ndconnect',
    youtube: 'https://youtube.com/@ndconnect',
    linkedin: 'https://linkedin.com/company/ndconnect'
  };

  constructor(public router: Router) {
    addIcons({
      menu, close, mailOutline, logoWhatsapp, home, informationCircle,
      cube, images, call, logoInstagram, logoFacebook, logoYoutube, logoLinkedin
    });
  }

  toggleMenu() {
    this.menuOpen = !this.menuOpen;
  }

  irPara(rota: string) {
    this.router.navigate([rota]);
    this.menuOpen = false;
  }

  abrirWhatsApp() {
    const numero = '5511953898557';
    const mensagem = encodeURIComponent('Olá! Gostaria de saber mais sobre os equipamentos da N.D Connect.');
    window.open(`https://wa.me/${numero}?text=${mensagem}`, '_blank');
  }

  abrirLink(tipo: string) {
    const url = this.linksUteis[tipo as keyof typeof this.linksUteis];
    if (url) {
      window.open(url, '_blank');
    }
  }
}
