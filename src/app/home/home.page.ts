import { Component, OnInit } from '@angular/core';
import { IonHeader, IonToolbar, IonTitle, IonContent, IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonButton, IonItem, IonLabel, IonInput, IonTextarea, IonSelect, IonSelectOption, IonList, IonIcon, IonGrid, IonRow, IonCol, IonBadge, IonDatetime, IonAlert, IonNote } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { add, remove, calculator, document, person, call, mail, location, search, warning, share, download, logoWhatsapp } from 'ionicons/icons';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

interface Produto {
  id: number;
  nome: string;
  descricao: string;
  preco: number | string;
  unidade: string;
  categoria_nome: string;
}

interface Categoria {
  id: number;
  nome: string;
  descricao: string;
}

interface ItemOrcamento {
  produto_id: number;
  produto_nome: string;
  categoria_nome: string;
  quantidade: number;
  preco_unitario: number;
  subtotal: number;
  unidade: string;
}

interface Cliente {
  nome: string;
  email: string;
  telefone: string;
  endereco: string;
  cpf_cnpj: string;
}

@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss'],
  imports: [IonHeader, IonToolbar, IonTitle, IonContent, IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonButton, IonItem, IonLabel, IonInput, IonTextarea, IonSelect, IonSelectOption, IonList, IonIcon, IonGrid, IonRow, IonCol, IonBadge, IonDatetime, IonAlert, IonNote, CommonModule, FormsModule],
})
export class HomePage implements OnInit {
  categorias: Categoria[] = [];
  produtos: Produto[] = [];
  produtosFiltrados: Produto[] = [];
  categoriaSelecionada: number = 0;
  termoPesquisa: string = '';

  itensOrcamento: ItemOrcamento[] = [];
  cliente: Cliente = {
    nome: '',
    email: '',
    telefone: '',
    endereco: '',
    cpf_cnpj: ''
  };

  observacoes: string = '';
  desconto: number = 0;
  subtotal: number = 0;
  total: number = 0;
  dataValidade: string = '';
  ultimoOrcamentoId: number | null = null;
  dataMinima: string = '';

  private apiUrl = 'http://localhost:8000';

  constructor(private http: HttpClient) {
    addIcons({ add, remove, calculator, document, person, call, mail, location, search, warning, share, download, logoWhatsapp });
  }

  ngOnInit() {
    this.carregarCategorias();
    this.carregarProdutos();
    this.definirDataValidadePadrao();
    this.definirDataMinima();
  }

  definirDataValidadePadrao() {
    const hoje = new Date();
    const validade = new Date(hoje);
    validade.setDate(hoje.getDate() + 10); // 10 dias a partir de hoje
    this.dataValidade = validade.toISOString();
  }

  definirDataMinima() {
    this.dataMinima = new Date().toISOString();
  }

  validarDataValidade() {
    const hoje = new Date();
    const dataValidade = new Date(this.dataValidade);
    const diferencaDias = Math.ceil((dataValidade.getTime() - hoje.getTime()) / (1000 * 60 * 60 * 24));

    if (diferencaDias < 10) {
      this.mostrarAlertaValidade(diferencaDias);
      return false;
    }
    return true;
  }

  mostrarAlertaValidade(dias: number) {
    const mensagem = `A data de validade selecionada é de apenas ${dias} dias.\n\nConforme a legislação brasileira, o prazo mínimo para orçamentos é de 10 dias.\n\nDeseja continuar mesmo assim?`;

    if (window.confirm(mensagem)) {
      // Usuário escolheu continuar
      return true;
    } else {
      // Usuário cancelou - restaurar data padrão
      this.definirDataValidadePadrao();
      return false;
    }
  }

  onDataValidadeChange() {
    // Validar quando a data for alterada
    setTimeout(() => {
      this.validarDataValidade();
    }, 100);
  }

  carregarCategorias() {
    this.http.get<any>(`${this.apiUrl}/categorias`).subscribe({
      next: (response) => {
        if (response.success) {
          this.categorias = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar categorias:', error);
      }
    });
  }

  carregarProdutos() {
    this.http.get<any>(`${this.apiUrl}/produtos`).subscribe({
      next: (response) => {
        if (response.success) {
          this.produtos = response.data;
          this.filtrarProdutos();
        }
      },
      error: (error) => {
        console.error('Erro ao carregar produtos:', error);
      }
    });
  }

  filtrarProdutos() {
    let produtosFiltrados = this.produtos;

    // Filtrar por categoria
    if (this.categoriaSelecionada !== 0) {
      produtosFiltrados = produtosFiltrados.filter(p =>
        p.categoria_nome === this.categorias.find(c => c.id === this.categoriaSelecionada)?.nome
      );
    }

    // Filtrar por termo de pesquisa
    if (this.termoPesquisa.trim()) {
      const termo = this.termoPesquisa.toLowerCase().trim();
      produtosFiltrados = produtosFiltrados.filter(p =>
        p.nome.toLowerCase().includes(termo) ||
        p.descricao.toLowerCase().includes(termo) ||
        p.categoria_nome.toLowerCase().includes(termo)
      );
    }

    this.produtosFiltrados = produtosFiltrados;
  }

  filtrarPorCategoria() {
    this.filtrarProdutos();
  }

  pesquisarProdutos() {
    this.filtrarProdutos();
  }

  adicionarItem(produto: Produto) {
    console.log('Adicionando produto:', produto);

    const itemExistente = this.itensOrcamento.find(item => item.produto_id === produto.id);

    if (itemExistente) {
      itemExistente.quantidade++;
      itemExistente.subtotal = Number(itemExistente.quantidade) * Number(itemExistente.preco_unitario);
      console.log('Item existente atualizado:', itemExistente);
    } else {
      const novoItem: ItemOrcamento = {
        produto_id: produto.id,
        produto_nome: produto.nome,
        categoria_nome: produto.categoria_nome,
        quantidade: 1,
        preco_unitario: Number(produto.preco),
        subtotal: Number(produto.preco),
        unidade: produto.unidade
      };
      // Criar nova referência do array para forçar atualização
      this.itensOrcamento = [...this.itensOrcamento, novoItem];
      console.log('Novo item adicionado:', novoItem);
    }

    this.calcularTotal();
    console.log('Itens no orçamento:', this.itensOrcamento);
  }

  removerItem(produtoId: number) {
    const index = this.itensOrcamento.findIndex(item => item.produto_id === produtoId);
    if (index > -1) {
      this.itensOrcamento = this.itensOrcamento.filter(item => item.produto_id !== produtoId);
      this.calcularTotal();
    }
  }

  atualizarQuantidade(produtoId: number, quantidade: number) {
    console.log('Atualizando quantidade:', produtoId, quantidade);
    const item = this.itensOrcamento.find(item => item.produto_id === produtoId);
    if (item) {
      if (quantidade <= 0) {
        this.removerItem(produtoId);
      } else {
        item.quantidade = quantidade;
        item.subtotal = Number(item.quantidade) * Number(item.preco_unitario);
        // Criar nova referência do array para forçar atualização
        this.itensOrcamento = [...this.itensOrcamento];
        this.calcularTotal();
        console.log('Quantidade atualizada:', item);
      }
    }
  }

  calcularTotal() {
    this.subtotal = this.itensOrcamento.reduce((total, item) => total + Number(item.subtotal), 0);
    this.total = Number(this.subtotal) - Number(this.desconto);
  }

  gerarOrcamento() {
    if (this.itensOrcamento.length === 0) {
      window.alert('Adicione pelo menos um item ao orçamento');
      return;
    }

    if (!this.cliente.nome.trim()) {
      window.alert('Informe o nome do cliente');
      return;
    }

    // Validar data de validade antes de gerar orçamento
    if (!this.validarDataValidade()) {
      return; // Se a validação falhar, não continuar
    }

    const orcamento = {
      cliente: this.cliente,
      itens: this.itensOrcamento,
      observacoes: this.observacoes,
      desconto: this.desconto,
      subtotal: this.subtotal,
      total: this.total,
      data_orcamento: new Date().toISOString().split('T')[0], // Data atual no formato YYYY-MM-DD
      data_validade: this.dataValidade.split('T')[0] // Data de validade no formato YYYY-MM-DD
    };

    console.log('Enviando orçamento:', orcamento);

    this.http.post<any>(`${this.apiUrl}/orcamentos`, orcamento).subscribe({
      next: (response) => {
        if (response.success) {
          this.ultimoOrcamentoId = response.data.id;
          window.alert('Orçamento gerado com sucesso!');
          this.gerarPDF(response.data.id);
        } else {
          window.alert('Erro ao gerar orçamento: ' + response.message);
        }
      },
      error: (error) => {
        console.error('Erro ao gerar orçamento:', error);
        window.alert('Erro ao gerar orçamento');
      }
    });
  }

  gerarPDF(orcamentoId: number) {
    const url = `${this.apiUrl}/simple_pdf.php?id=${orcamentoId}`;
    window.open(url, '_blank');
  }

  compartilharWhatsApp() {
    if (!this.ultimoOrcamentoId) {
      window.alert('Gere um orçamento primeiro');
      return;
    }

    const pdfUrl = `${this.apiUrl}/simple_pdf.php?id=${this.ultimoOrcamentoId}`;
    const mensagem = `Olá ${this.cliente.nome}!

Segue o orçamento solicitado da N.D Connect:

📋 *Orçamento Nº ${this.ultimoOrcamentoId}*
💰 *Total: R$ ${this.total.toFixed(2).replace('.', ',')}*
📅 *Válido até: ${new Date(this.dataValidade).toLocaleDateString('pt-BR')}*

Acesse o PDF completo: ${pdfUrl}

Agradecemos pela preferência!
N.D Connect - Equipamentos para Eventos`;

    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(mensagem)}`;
    window.open(whatsappUrl, '_blank');
  }

  salvarPDF() {
    if (!this.ultimoOrcamentoId) {
      window.alert('Gere um orçamento primeiro');
      return;
    }

    // Abrir PDF em nova aba para download
    const pdfUrl = `${this.apiUrl}/simple_pdf.php?id=${this.ultimoOrcamentoId}`;
    window.open(pdfUrl, '_blank');
  }

  compartilhar() {
    if (!this.ultimoOrcamentoId) {
      window.alert('Gere um orçamento primeiro');
      return;
    }

    const opcao = window.prompt('Escolha uma opção:\n1 - WhatsApp\n2 - Salvar PDF\n\nDigite o número da opção:');

    if (opcao === '1') {
      this.compartilharWhatsApp();
    } else if (opcao === '2') {
      this.salvarPDF();
    }
  }

  limparOrcamento() {
    this.itensOrcamento = [];
    this.cliente = {
      nome: '',
      email: '',
      telefone: '',
      endereco: '',
      cpf_cnpj: ''
    };
    this.observacoes = '';
    this.desconto = 0;
    this.subtotal = 0;
    this.total = 0;
    this.ultimoOrcamentoId = null;
    this.definirDataValidadePadrao();
  }

  trackByProdutoId(index: number, produto: Produto): number {
    return produto.id;
  }

  trackByItemId(index: number, item: ItemOrcamento): number {
    return item.produto_id;
  }
}
