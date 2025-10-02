import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import { peopleCircle, flag, eye, heart, logoWhatsapp, mail } from 'ionicons/icons';
import { IonHeader, IonToolbar, IonTitle, IonContent, IonButton, IonButtons, IonIcon } from '@ionic/angular/standalone';
import { NavbarComponent } from "../shared/navbar/navbar.component";

@Component({
  selector: 'app-sobre',
  templateUrl: './sobre.page.html',
  styleUrls: ['./sobre.page.scss'],
  standalone: true,
  imports: [IonHeader, IonToolbar, IonTitle, IonContent, IonButton, IonButtons, IonIcon, NavbarComponent]
})
export class SobrePage {
  constructor(private router: Router) {
    addIcons({ peopleCircle, flag, eye, heart, logoWhatsapp, mail });
  }

  irPara(rota: string) {
    this.router.navigate([rota]);
  }

  abrirWhatsApp() {
    const numero = '5511953898557';
    const mensagem = encodeURIComponent('Olá! Gostaria de saber mais sobre a N.D Connect.');
    window.open(`https://wa.me/${numero}?text=${mensagem}`, '_blank');
  }
}
