import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import { send, person, mail, call, business, chatbubbles, checkmarkCircle } from 'ionicons/icons';
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
  IonItem,
  IonLabel,
  IonInput,
  IonTextarea,
  IonSelect,
  IonSelectOption,
  AlertController
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

interface SolicitacaoOrcamento {
  nome: string;
  email: string;
  telefone: string;
  empresa?: string;
  mensagem: string;
  origem: string;
}

@Component({
  selector: 'app-solicitar-orcamento',
  templateUrl: './solicitar-orcamento.page.html',
  styleUrls: ['./solicitar-orcamento.page.scss'],
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
    IonItem,
    IonLabel,
    IonInput,
    IonTextarea,
    IonSelect,
    IonSelectOption,
    CommonModule,
    FormsModule
  ]
})
export class SolicitarOrcamentoPage implements OnInit {
  solicitacao: SolicitacaoOrcamento = {
    nome: '',
    email: '',
    telefone: '',
    empresa: '',
    mensagem: '',
    origem: 'site'
  };

  enviando: boolean = false;
  enviado: boolean = false;

  private apiUrl = 'http://localhost:8000';

  constructor(
    private http: HttpClient,
    private router: Router,
    private alertController: AlertController
  ) {
    addIcons({ send, person, mail, call, business, chatbubbles, checkmarkCircle });
  }

  ngOnInit() {}

  formatarTelefone(event: any) {
    let valor = event.target.value.replace(/\D/g, '');

    if (valor.length <= 11) {
      if (valor.length > 6) {
        valor = valor.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
      } else if (valor.length > 2) {
        valor = valor.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
      } else if (valor.length > 0) {
        valor = valor.replace(/^(\d*)/, '($1');
      }
    }

    this.solicitacao.telefone = valor;
  }

  validarFormulario(): boolean {
    if (!this.solicitacao.nome.trim()) {
      this.mostrarAlerta('Atenção', 'Por favor, informe seu nome');
      return false;
    }

    if (!this.solicitacao.telefone.trim()) {
      this.mostrarAlerta('Atenção', 'Por favor, informe seu telefone');
      return false;
    }

    if (!this.solicitacao.mensagem.trim()) {
      this.mostrarAlerta('Atenção', 'Por favor, descreva o que você precisa');
      return false;
    }

    return true;
  }

  async enviarSolicitacao() {
    if (!this.validarFormulario()) {
      return;
    }

    this.enviando = true;

    this.http.post<any>(`${this.apiUrl}/leads`, this.solicitacao).subscribe({
      next: async (response) => {
        this.enviando = false;
        if (response.success) {
          this.enviado = true;
          await this.mostrarSucesso();
          this.limparFormulario();
        } else {
          await this.mostrarAlerta('Erro', response.message || 'Erro ao enviar solicitação');
        }
      },
      error: async (error) => {
        this.enviando = false;
        console.error('Erro ao enviar solicitação:', error);
        await this.mostrarAlerta('Erro', 'Não foi possível enviar sua solicitação. Tente novamente mais tarde.');
      }
    });
  }

  async mostrarSucesso() {
    const alert = await this.alertController.create({
      header: 'Solicitação Enviada!',
      message: 'Obrigado pelo contato! Entraremos em contato em breve.',
      buttons: ['OK'],
      cssClass: 'alert-success'
    });
    await alert.present();
  }

  async mostrarAlerta(header: string, message: string) {
    const alert = await this.alertController.create({
      header: header,
      message: message,
      buttons: ['OK']
    });
    await alert.present();
  }

  limparFormulario() {
    this.solicitacao = {
      nome: '',
      email: '',
      telefone: '',
      empresa: '',
      mensagem: '',
      origem: 'site'
    };
  }

  voltarHome() {
    this.router.navigate(['/home']);
  }
}
