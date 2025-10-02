import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import {
  logoWhatsapp,
  mail,
  time,
  logoInstagram,
  logoFacebook,
  logoYoutube,
  logoLinkedin,
  send,
  lockClosed,
  helpCircle,
  arrowForward
} from 'ionicons/icons';
import {
  IonHeader,
  IonToolbar,
  IonContent,
  IonButton,
  IonIcon,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardContent,
  IonInput,
  IonTextarea,
  IonSelect,
  IonSelectOption
} from '@ionic/angular/standalone';
import { NavbarComponent } from "../shared/navbar/navbar.component";
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-contato',
  templateUrl: './contato.page.html',
  styleUrls: ['./contato.page.scss'],
  standalone: true,
  imports: [
    IonHeader,
    IonToolbar,
    IonContent,
    IonButton,
    IonIcon,
    IonCard,
    IonCardHeader,
    IonCardTitle,
    IonCardContent,
    IonInput,
    IonTextarea,
    IonSelect,
    IonSelectOption,
    NavbarComponent,
    CommonModule,
    FormsModule
]
})
export class ContatoPage {
  // Propriedades do formulário
  categoriaContato: string = 'orcamento';
  nomeContato: string = '';
  whatsappContato: string = '';
  emailContato: string = '';
  mensagemContato: string = '';

  constructor(private router: Router) {
    addIcons({
      logoWhatsapp,
      mail,
      time,
      logoInstagram,
      logoFacebook,
      logoYoutube,
      logoLinkedin,
      send,
      lockClosed,
      helpCircle,
      arrowForward
    });
  }

  irPara(rota: string) {
    this.router.navigate([rota]);
  }

  abrirWhatsApp() {
    const numero = '5511953898557';
    const mensagem = encodeURIComponent('Olá! Gostaria de entrar em contato com a N.D Connect.');
    window.open(`https://wa.me/${numero}?text=${mensagem}`, '_blank');
  }

  abrirEmail() {
    window.open('mailto:danieltorquato2009@gmail.com?subject=Contato via Site', '_blank');
  }

  enviarMensagem() {
    // Validação básica
    if (!this.nomeContato || !this.mensagemContato) {
      alert('Por favor, preencha pelo menos o nome e a mensagem.');
      return;
    }

    // Preparar dados para envio
    const categoriaTexto = this.getCategoriaTexto(this.categoriaContato);

    // Criar assunto do e-mail
    const assunto = `${categoriaTexto} - ${this.nomeContato}`;

    // Criar corpo do e-mail
    const corpoEmail =
      `Categoria: ${categoriaTexto}\n\n` +
      `Nome: ${this.nomeContato}\n` +
      `${this.whatsappContato ? `WhatsApp: ${this.whatsappContato}\n` : ''}` +
      `${this.emailContato ? `E-mail: ${this.emailContato}\n` : ''}` +
      `\nMensagem:\n${this.mensagemContato}\n\n` +
      `---\n` +
      `Enviado através do site N.D Connect`;

    // Codificar para URL
    const assuntoEncoded = encodeURIComponent(assunto);
    const corpoEncoded = encodeURIComponent(corpoEmail);

    // Abrir cliente de e-mail
    window.open(`mailto:danieltorquato2009@gmail.com?subject=${assuntoEncoded}&body=${corpoEncoded}`, '_blank');

    // Limpar formulário após envio
    this.limparFormulario();
  }

  getCategoriaTexto(categoria: string): string {
    const categorias: { [key: string]: string } = {
      'orcamento': 'Solicitar Orçamento',
      'sugestao': 'Sugestão',
      'elogio': 'Elogio',
      'duvida': 'Dúvida',
      'reclamacao': 'Reclamação',
      'parceria': 'Parceria',
      'outros': 'Outros'
    };
    return categorias[categoria] || 'Contato';
  }

  limparFormulario() {
    this.categoriaContato = 'orcamento';
    this.nomeContato = '';
    this.whatsappContato = '';
    this.emailContato = '';
    this.mensagemContato = '';
  }
}
