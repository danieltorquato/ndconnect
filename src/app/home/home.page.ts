import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import {
  menu,
  close,
  star,
  cubeOutline,
  arrowForward,
  logoWhatsapp,
  chevronDown,
  musicalNotes,
  checkmarkCircle,
  bulb,
  flash,
  construct,
  desktop,
  sparkles,
  shieldCheckmark,
  people,
  time,
  cash,
  trophy,
  mailOutline,
  logoInstagram,
  logoFacebook,
  logoYoutube,
  logoLinkedin,
  mail
} from 'ionicons/icons';
import {
  IonHeader,
  IonToolbar,
  IonContent,
  IonButton,
  IonIcon
} from '@ionic/angular/standalone';
import { NavbarComponent } from '../shared/navbar/navbar.component';

@Component({
  selector: 'app-home',
  templateUrl: './home.page.html',
  styleUrls: ['./home.page.scss'],
  standalone: true,
  imports: [IonHeader, IonToolbar, IonContent, IonButton, IonIcon, NavbarComponent]
})
export class HomePage implements OnInit {
  menuOpen = false;

  private linksUteis = {
    instagram: 'https://instagram.com/ndconnect_oficial',
    facebook: 'https://facebook.com/ndconnect',
    youtube: 'https://youtube.com/@ndconnect',
    linkedin: 'https://linkedin.com/company/ndconnect'
  };

  constructor(private router: Router) {
    addIcons({
      menu,
      close,
      star,
      cubeOutline,
      arrowForward,
      logoWhatsapp,
      chevronDown,
      musicalNotes,
      checkmarkCircle,
      bulb,
      flash,
      construct,
      desktop,
      sparkles,
      shieldCheckmark,
      people,
      time,
      cash,
      trophy,
      mailOutline,
      logoInstagram,
      logoFacebook,
      logoYoutube,
      logoLinkedin,
      mail
    });
  }

  ngOnInit() {
    // Simular AOS animations
    this.initAnimations();
  }

  initAnimations() {
    // Adicionar observer para animações ao scroll
    if (typeof window !== 'undefined') {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('aos-animate');
          }
        });
      });

      setTimeout(() => {
        document.querySelectorAll('[data-aos]').forEach(el => {
          observer.observe(el);
        });
      }, 100);
    }
  }

  toggleMenu() {
    this.menuOpen = !this.menuOpen;
  }

  irPara(rota: string) {
    this.router.navigate([rota]);
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
