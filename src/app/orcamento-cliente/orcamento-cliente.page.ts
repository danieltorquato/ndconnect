import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import { mail, send, checkmarkCircle, logoWhatsapp } from 'ionicons/icons';
import { IonHeader, IonToolbar, IonTitle, IonContent, IonButton, IonButtons, IonIcon } from '@ionic/angular/standalone';

@Component({
  selector: 'app-orcamento-cliente',
  templateUrl: './orcamento-cliente.page.html',
  styleUrls: ['./orcamento-cliente.page.scss'],
  standalone: true,
  imports: [IonHeader, IonToolbar, IonTitle, IonContent, IonButton, IonButtons, IonIcon]
})
export class OrcamentoClientePage {
  constructor(private router: Router) {
    addIcons({send,checkmarkCircle,logoWhatsapp,mail});
  }

  irPara(rota: string) {
    this.router.navigate([rota]);
  }
}
