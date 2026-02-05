import { Component, Inject, OnInit } from '@angular/core';
import { CommonModule, DOCUMENT } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import {
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonButtons,
  IonButton,
  IonIcon,
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { arrowBack, share, logoWhatsapp, mail, download, print } from 'ionicons/icons';
import { environment } from '../../environments/environment';

interface ReciboDados {
  id: number | null;
  numero: string;
  clienteNome: string;
  email: string;
  telefone: string;
  total: number;
  dataOrcamento: string | null;
  observacoes: string;
}

@Component({
  selector: 'app-recibo-orcamento',
  templateUrl: './recibo-orcamento.page.html',
  styleUrls: ['./recibo-orcamento.page.scss'],
  standalone: true,
  imports: [
    IonHeader,
    IonToolbar,
    IonTitle,
    IonContent,
    IonButtons,
    IonButton,
    IonIcon,
    CommonModule,
  ],
})
export class ReciboOrcamentoPage implements OnInit {
  dados: ReciboDados = {
    id: null,
    numero: '',
    clienteNome: '',
    email: '',
    telefone: '',
    total: 0,
    dataOrcamento: null,
    observacoes: '',
  };

  today: Date = new Date();
  private apiUrl = environment.apiUrl;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    @Inject(DOCUMENT) private document: Document
  ) {
    addIcons({ arrowBack, share, logoWhatsapp, mail, download, print });
  }

  ngOnInit() {
    this.route.queryParams.subscribe((params) => {
      this.dados = {
        id: params['id'] ? Number(params['id']) : null,
        numero: params['numero'] || '',
        clienteNome: params['cliente_nome'] || '',
        email: params['email'] || '',
        telefone: params['telefone'] || '',
        total: params['total'] ? Number(params['total']) : 0,
        dataOrcamento: params['data_orcamento'] || null,
        observacoes: params['observacoes'] || '',
      };
    });
  }

  voltar() {
    this.router.navigate(['/admin/gestao-orcamentos']);
  }

  get valorFormatado(): string {
    return `R$ ${this.dados.total.toFixed(2).replace('.', ',')}`;
  }

  get dataOrcamentoFormatada(): string | null {
    if (!this.dados.dataOrcamento) return null;
    const d = new Date(this.dados.dataOrcamento);
    return d.toLocaleDateString('pt-BR');
  }

  imprimirRecibo() {
    window.print();
  }

  salvarComoPDF() {
    if (!this.dados.id) {
      return;
    }

    const pdfUrl = `${this.apiUrl}/recibo_pdf.php?id=${this.dados.id}&action=download`;

    const link = this.document.createElement('a');
    link.href = pdfUrl;
    const primeiroNome = (this.dados.clienteNome || '').split(' ')[0].toLowerCase() || 'cliente';
    link.download = `recibo_${primeiroNome}_${this.dados.id}.pdf`;
    link.target = '_blank';
    this.document.body.appendChild(link);
    link.click();
    this.document.body.removeChild(link);
  }

  compartilharWhatsApp() {
    if (!this.dados.id) {
      return;
    }

    const numero = this.dados.telefone?.replace(/\D/g, '') || '';
    const numeroComDDI = numero ? `+55${numero}` : '';

    const pdfUrl = `${this.apiUrl}/recibo_pdf.php?id=${this.dados.id}&action=view`;

    const mensagem = `🏢 *N.D CONNECT - EQUIPAMENTOS PARA EVENTOS*

Cliente: *${this.dados.clienteNome || '---'}*
Orçamento Nº: *${this.dados.numero || '---'}*
Valor Recebido: *${this.valorFormatado}*
${this.dataOrcamentoFormatada ? 'Data do Orçamento: *' + this.dataOrcamentoFormatada + '*\n' : ''}

📄 *Visualizar Recibo (PDF):* ${pdfUrl}

Este recibo confirma o pagamento referente aos serviços prestados pela N.D Connect.

✨ *Agradecemos pela preferência!*`;

    const urlBase = 'https://wa.me/';
    const url = numeroComDDI
      ? `${urlBase}${numeroComDDI}?text=${encodeURIComponent(mensagem)}`
      : `${urlBase}?text=${encodeURIComponent(mensagem)}`;

    window.open(url, '_blank');
  }

  compartilharEmail() {
    const assunto = `Recibo N.D Connect - Orçamento Nº ${this.dados.numero || ''}`;

    const pdfUrl = this.dados.id
      ? `${this.apiUrl}/recibo_pdf.php?id=${this.dados.id}&action=download`
      : '';
    const orcamentoUrl = this.dados.id
      ? `${this.apiUrl}/recibo_pdf.php?id=${this.dados.id}&action=view`
      : '';

    const corpo = `Olá ${this.dados.clienteNome || ''},

Segue o recibo referente ao orçamento Nº ${this.dados.numero || '---'}.

Valor recebido: ${this.valorFormatado}
${this.dataOrcamentoFormatada ? 'Data do orçamento: ' + this.dataOrcamentoFormatada + '\n' : ''}

Este recibo confirma o pagamento referente aos serviços prestados pela N.D Connect.

📄 PDF do recibo: ${pdfUrl || 'gerar na área de gestão de orçamentos'}
🔗 Visualização online do recibo: ${orcamentoUrl || 'gerar na área de gestão de orçamentos'}

Agradecemos pela preferência.

N.D CONNECT - EQUIPAMENTOS PARA EVENTOS
Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED`;

    const mailtoUrl = `mailto:${encodeURIComponent(
      this.dados.email || ''
    )}?subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`;

    window.open(mailtoUrl, '_blank');
  }
}

