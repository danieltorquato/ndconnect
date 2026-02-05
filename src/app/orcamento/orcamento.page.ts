import { Component, OnInit, Inject } from '@angular/core';
import { IonHeader, IonToolbar, IonTitle, IonContent, IonCard, IonCardContent, IonButton, IonItem, IonLabel, IonInput, IonTextarea, IonSelect, IonSelectOption, IonIcon, IonBadge, IonButtons, IonCheckbox } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { add, remove, calculator, document, person, call, mail, location, search, warning, share, download, logoWhatsapp, list, close, copy, checkmark, checkmarkCircle, informationCircle, star, home, calendar, documentText, trash, arrowBack, settings, addCircle, documentOutline, receipt } from 'ionicons/icons';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { DOCUMENT } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { environment } from '../../environments/environment';
import { Document as DocxDocument, Packer, Paragraph, TextRun, Table, TableCell, TableRow, WidthType, AlignmentType, BorderStyle, ShadingType, ImageRun } from 'docx';
import { saveAs } from 'file-saver';

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
  produto_id?: number;
  produto_nome: string;
  categoria_nome?: string;
  quantidade: number;
  preco_unitario: number;
  subtotal: number;
  unidade: string;
  desconto_porcentagem?: number;
  desconto_valor?: number;
  subtotal_com_desconto?: number;
  produto_customizado?: boolean;
  nome_customizado?: string;
  valor_unitario_customizado?: number;
  unidade_customizada?: string;
}

interface ProdutoCustomizado {
  nome: string;
  valorUnitario: number;
  valorTotal: number; // Novo campo para valor total manual
  unidade: string;
  quantidade: number;
  subtotal: number;
  usarValorTotal: boolean; // Flag para indicar se deve usar valor total em vez de unitário
}

interface Show {
  id: number;
  nome: string;
  datasEvento: string[];
  itensOrcamento: ItemOrcamento[];
  observacoes: string;
  subtotal: number;
  desconto: number;
  descontoTipo: 'porcentagem' | 'valor';
  total: number;
}

interface OrcamentoMultiShow {
  cliente: Cliente;
  nomeEvento: string;
  quantidadeShows: number;
  shows: Show[];
  observacoesGerais: string;
  totalGeral: number;
}

interface Cliente {
  nome: string;
  email: string;
  telefone: string;
  endereco: string;
  cpf_cnpj: string;
  empresa: string;
}

@Component({
  selector: 'app-orcamento',
  templateUrl: './orcamento.page.html',
  styleUrls: ['./orcamento.page.scss'],
  standalone: true,
  imports: [IonHeader, IonToolbar, IonTitle, IonContent, IonCard, IonCardContent, IonButton, IonItem, IonLabel, IonInput, IonTextarea, IonSelect, IonSelectOption, IonIcon, IonBadge, IonCheckbox, IonButtons, CommonModule, FormsModule],
})
export class OrcamentoPage implements OnInit {
  categorias: Categoria[] = [];
  produtos: Produto[] = [];
  produtosFiltrados: Produto[] = [];
  produtosIniciais: Produto[] = [];
  categoriaSelecionada: number = 0;
  termoPesquisa: string = '';
  mostrarTodosProdutos: boolean = false;

  itensOrcamento: ItemOrcamento[] = [];
  cliente: Cliente = {
    nome: '',
    email: '',
    telefone: '',
    endereco: '',
    cpf_cnpj: '',
    empresa: ''
  };

  observacoes: string = '';
  desconto: number = 0;
  descontoTipo: 'valor' | 'porcentagem' = 'valor';
  subtotal: number = 0;
  total: number = 0;
  valorTotalInformado: number = 0; // Campo para o usuário informar o valor total do orçamento
  incluirValorUnitario: boolean = false; // Por padrão, não inclui valor unitário
  dataEvento: string = '';
  nomeEvento: string = '';
  ultimoOrcamentoId: number | null = null;
  dataMinima: string = '';
  leadIdExistente: number | null = null; // Para controlar se veio da gestão de leads
  urlLogoOrcamento: string = 'https://ndconnect.com.br/assets/img/logo.jpeg'; // URL da logo para inserir no orçamento Word

  // Novas propriedades para produtos customizados
  produtoCustomizado: ProdutoCustomizado = {
    nome: '',
    valorUnitario: 0,
    valorTotal: 0,
    unidade: '',
    quantidade: 1,
    subtotal: 0,
    usarValorTotal: false
  };

  // Novas propriedades para múltiplas datas
  datasEvento: string[] = [];
  modalDatasAberto: boolean = false;
  dataSelecionada: string = '';

  // Propriedades para múltiplos shows
  quantidadeShows: number = 1;
  showAtual: number = 1;
  shows: Show[] = [];
  modoMultiShow: boolean = false;

  private apiUrl = environment.apiUrl;

  constructor(
    private http: HttpClient,
    @Inject(DOCUMENT) private documentRef: Document,
    private router: Router,
    private route: ActivatedRoute
  ) {
    addIcons({arrowBack,list,addCircle,add,documentText,trash,calendar,person,document,settings,documentOutline,calculator,receipt,remove,informationCircle,close,star,search,warning,call,mail,location,share,download,logoWhatsapp,copy,checkmark,checkmarkCircle,home});
  }

  ngOnInit() {
    this.carregarCategorias();
    this.carregarProdutosIniciais();
    this.carregarDadosDoLead();
    this.definirDataMinima();
    this.inicializarShows();
  }

  inicializarShows() {
    // Inicializar com um show padrão
    this.shows = [{
      id: 1,
      nome: 'Show 1',
      datasEvento: [],
      itensOrcamento: [],
      observacoes: '',
      subtotal: 0,
      desconto: 0,
      descontoTipo: 'valor',
      total: 0
    }];
    this.showAtual = 1;
    this.quantidadeShows = 1;
  }

  definirQuantidadeShows(quantidade: number) {
    this.quantidadeShows = quantidade;

    // Salvar o show atual antes de mudar
    this.salvarShowAtual();

    // Ajustar array de shows
    if (quantidade > this.shows.length) {
      // Adicionar novos shows
      for (let i = this.shows.length + 1; i <= quantidade; i++) {
        this.shows.push({
          id: i,
          nome: `Show ${i}`,
          datasEvento: [],
          itensOrcamento: [],
          observacoes: '',
          subtotal: 0,
          desconto: 0,
          descontoTipo: 'valor',
          total: 0
        });
      }
    } else if (quantidade < this.shows.length) {
      // Remover shows extras
      this.shows = this.shows.slice(0, quantidade);
      if (this.showAtual > quantidade) {
        this.showAtual = quantidade;
      }
    }

    // Carregar o show atual
    this.carregarShowAtual();
  }

  salvarShowAtual() {
    if (this.shows[this.showAtual - 1]) {
      this.shows[this.showAtual - 1] = {
        id: this.showAtual,
        nome: `Show ${this.showAtual}`,
        datasEvento: [...this.datasEvento],
        itensOrcamento: [...this.itensOrcamento],
        observacoes: this.observacoes,
        subtotal: this.subtotal,
        desconto: this.desconto,
        descontoTipo: this.descontoTipo,
        total: this.total
      };
    }
  }

  carregarShowAtual() {
    const show = this.shows[this.showAtual - 1];
    if (show) {
      this.datasEvento = [...show.datasEvento];
      this.itensOrcamento = [...show.itensOrcamento];
      this.observacoes = show.observacoes;
      this.subtotal = show.subtotal;
      this.desconto = show.desconto;
      this.descontoTipo = show.descontoTipo;
      this.total = show.total;
    }
  }

  navegarParaShow(showId: number) {
    if (showId >= 1 && showId <= this.quantidadeShows) {
      this.salvarShowAtual();
      this.showAtual = showId;
      this.carregarShowAtual();
    }
  }

  calcularTotalGeral() {
    return this.shows.reduce((total, show) => total + show.total, 0);
  }

  definirDataEventoPadrao() {
    const hoje = new Date();
    const dataEvento = new Date(hoje);
    dataEvento.setDate(hoje.getDate() + 30); // 30 dias a partir de hoje
    this.dataEvento = dataEvento.toISOString();
  }

  definirDataMinima() {
    this.dataMinima = new Date().toISOString();
  }

  validarDadosEvento() {
    if (!this.nomeEvento.trim()) {
      window.alert('Informe o nome do evento');
      return false;
    }

    if (this.quantidadeShows < 1) {
      window.alert('Informe uma quantidade válida de shows');
      return false;
    }

    return true;
  }

  // Método para formatar o valor total informado como moeda
  formatarValorTotal(event: any) {
    // O campo é do tipo number, então não precisa de formatação adicional
    // A formatação visual é feita com o pipe number no template
    const valor = event.target.value;
    if (valor !== '') {
      this.valorTotalInformado = parseFloat(valor);
      if (isNaN(this.valorTotalInformado)) {
        this.valorTotalInformado = 0;
      }
    } else {
      this.valorTotalInformado = 0;
    }
  }

  // Métodos para múltiplas datas removidos - não há mais seleção de datas

  // Método para obter imagem via backend (sem problemas de CORS)
  async obterImagemBase64(url: string): Promise<Uint8Array | null> {
    if (!url) return null;

    try {
      const endpointUrl = `${this.apiUrl}/get_imagem_base64.php?url=${encodeURIComponent(url)}`;
      console.log('Requisitando:', endpointUrl);

      const response = await fetch(endpointUrl);
      const texto = await response.text();

      console.log('Resposta do servidor:', texto);

      if (!response.ok) {
        throw new Error(`Erro HTTP: ${response.status}`);
      }

      if (!texto.includes('"success":true')) {
        console.warn('IMPORTANTE: Upload o arquivo get_imagem_base64.php para https://ndconnect.com.br/api/');
        return null;
      }

      const resultado = JSON.parse(texto);
      if (!resultado.data || !resultado.data.includes(',')) {
        return null;
      }

      // O backend retorna data URL, precisamos extrair o base64
      const base64Data = resultado.data.split(',')[1];
      const uint8Array = Uint8Array.from(atob(base64Data), c => c.charCodeAt(0));
      return uint8Array;
    } catch (erro) {
      console.error('Erro ao carregar imagem:', erro);
      console.warn('IMPORTANTE: Para usar imagens, faça upload de get_imagem_base64.php para https://ndconnect.com.br/api/');
      return null;
    }
  }

  // Método auxiliar para criar TextRun com fonte Arial profissional
  criarTextRun(props: any): TextRun {
    return new TextRun({
      ...props,
      font: 'Arial'
    });
  }

  calcularSubtotalCustomizado() {
    if (this.produtoCustomizado.usarValorTotal && this.produtoCustomizado.valorUnitario && this.produtoCustomizado.quantidade) {
      // Se marcou para informar valor unitário, calcular com quantidade
      this.produtoCustomizado.subtotal = this.produtoCustomizado.valorUnitario * this.produtoCustomizado.quantidade;
    } else {
      this.produtoCustomizado.subtotal = 0;
    }
  }

  adicionarProdutoCustomizado() {
    if (!this.produtoCustomizado.nome.trim()) {
      window.alert('Informe o nome do produto');
      return;
    }

    if (!this.produtoCustomizado.unidade.trim()) {
      window.alert('Informe a unidade do produto');
      return;
    }

    if (!this.produtoCustomizado.quantidade || this.produtoCustomizado.quantidade <= 0) {
      window.alert('Informe uma quantidade válida');
      return;
    }

    // Validar se tem valor unitário definido quando checkbox está marcado
    if (this.produtoCustomizado.usarValorTotal && !this.produtoCustomizado.valorUnitario) {
      window.alert('Informe o valor unitário');
      return;
    }

    // Gerar ID único para produto customizado (negativo para diferenciar)
    const customId = -(Date.now() + Math.random() * 1000);

    const novoItem: ItemOrcamento = {
      produto_id: customId, // ID único negativo para produtos customizados
      produto_nome: this.produtoCustomizado.nome,
      categoria_nome: 'Customizado',
      quantidade: this.produtoCustomizado.quantidade,
      preco_unitario: this.produtoCustomizado.usarValorTotal ? (this.produtoCustomizado.valorUnitario || 0) : 0,
      subtotal: this.produtoCustomizado.subtotal,
      unidade: this.produtoCustomizado.unidade,
      produto_customizado: true,
      nome_customizado: this.produtoCustomizado.nome,
      valor_unitario_customizado: this.produtoCustomizado.usarValorTotal ? this.produtoCustomizado.valorUnitario : undefined,
      unidade_customizada: this.produtoCustomizado.unidade
    };

    this.itensOrcamento.push(novoItem);
    this.calcularTotal();

    // Limpar formulário
    this.produtoCustomizado = {
      nome: '',
      valorUnitario: 0,
      valorTotal: 0,
      unidade: '',
      quantidade: 1,
      subtotal: 0,
      usarValorTotal: false // Por padrão, sem checkbox (não informar valor unitário)
    };

    this.mostrarNotificacao('Produto customizado adicionado!', 'success');
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

  carregarProdutosIniciais() {
    this.http.get<any>(`${this.apiUrl}/produtos/populares?limit=5`).subscribe({
      next: (response) => {
        if (response.success) {
          this.produtosIniciais = response.data;
          this.produtosFiltrados = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar produtos iniciais:', error);
      }
    });
  }

  carregarTodosProdutos() {
    this.http.get<any>(`${this.apiUrl}/produtos`).subscribe({
      next: (response) => {
        if (response.success) {
          this.produtos = response.data;
          this.mostrarTodosProdutos = true;
          // Mostrar todos os produtos sem aplicar filtros
          this.produtosFiltrados = this.produtos;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar todos os produtos:', error);
      }
    });
  }

  filtrarProdutos() {
    // Se não há pesquisa ou filtro E não está mostrando todos os produtos, mostrar apenas os produtos iniciais
    if (!this.termoPesquisa.trim() && this.categoriaSelecionada === 0 && !this.mostrarTodosProdutos) {
      this.produtosFiltrados = this.produtosIniciais;
      return;
    }

    // Se há pesquisa ou filtro, carregar todos os produtos se ainda não foram carregados
    if (!this.mostrarTodosProdutos) {
      this.carregarTodosProdutos();
      return;
    }

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

  limparFiltros() {
    this.termoPesquisa = '';
    this.categoriaSelecionada = 0;
    this.mostrarTodosProdutos = false;
    this.produtosFiltrados = this.produtosIniciais;
  }

  alternarVisualizacao() {
    if (this.mostrarTodosProdutos) {
      // Atualmente mostrando todos, voltar para populares
      this.mostrarTodosProdutos = false;
      this.produtosFiltrados = this.produtosIniciais;
    } else {
      // Atualmente mostrando populares, mostrar todos
      if (this.produtos.length === 0) {
        // Se ainda não carregou todos os produtos, carregar agora
        this.carregarTodosProdutos();
      } else {
        // Se já carregou, apenas alternar a visualização
        this.mostrarTodosProdutos = true;
        this.produtosFiltrados = this.produtos;
      }
    }
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
        unidade: produto.unidade,
        desconto_porcentagem: 0,
        desconto_valor: 0,
        subtotal_com_desconto: Number(produto.preco)
      };
      // Criar nova referência do array para forçar atualização
      this.itensOrcamento = [...this.itensOrcamento, novoItem];
      console.log('Novo item adicionado:', novoItem);
    }

    this.calcularTotal();
    console.log('Itens no orçamento:', this.itensOrcamento);
  }

  removerItem(produtoId: number | undefined) {
    if (produtoId) {
      const index = this.itensOrcamento.findIndex(item => item.produto_id === produtoId);
      if (index > -1) {
        this.itensOrcamento = this.itensOrcamento.filter(item => item.produto_id !== produtoId);
        this.calcularTotal();
      }
    }
  }

  atualizarQuantidade(produtoId: number | undefined, quantidade: number) {
    console.log('Atualizando quantidade:', produtoId, quantidade);
    const item = this.itensOrcamento.find(item => item.produto_id === produtoId);
    if (item) {
      if (quantidade <= 0) {
        this.removerItem(produtoId);
      } else {
        item.quantidade = quantidade;
        item.subtotal = Number(item.quantidade) * Number(item.preco_unitario);
        this.calcularDescontoItem(item);
        // Criar nova referência do array para forçar atualização
        this.itensOrcamento = [...this.itensOrcamento];
        this.calcularTotal();
        console.log('Quantidade atualizada:', item);
      }
    }
  }

  calcularDescontoItem(item: ItemOrcamento) {
    const subtotalBase = item.subtotal;

    if (item.desconto_porcentagem && item.desconto_porcentagem > 0) {
      const valorDesconto = (subtotalBase * item.desconto_porcentagem) / 100;
      item.desconto_valor = valorDesconto;
      item.subtotal_com_desconto = subtotalBase - valorDesconto;
    } else if (item.desconto_valor && item.desconto_valor > 0) {
      item.desconto_porcentagem = (item.desconto_valor / subtotalBase) * 100;
      item.subtotal_com_desconto = subtotalBase - item.desconto_valor;
    } else {
      item.desconto_valor = 0;
      item.desconto_porcentagem = 0;
      item.subtotal_com_desconto = subtotalBase;
    }
  }

  aplicarDescontoItem(produtoId: number | undefined, tipo: 'porcentagem' | 'valor', valor: number) {
    const item = this.itensOrcamento.find(item => item.produto_id === produtoId);
    if (item) {
      if (tipo === 'porcentagem') {
        item.desconto_porcentagem = valor;
        item.desconto_valor = 0;
      } else {
        item.desconto_valor = valor;
        item.desconto_porcentagem = 0;
      }
      this.calcularDescontoItem(item);
      this.calcularTotal();
    }
  }

  calcularTotal() {
    // Calcular subtotal com descontos dos itens
    this.subtotal = this.itensOrcamento.reduce((total, item) => {
      return total + (item.subtotal_com_desconto || item.subtotal);
    }, 0);

    // Aplicar desconto geral
    if (this.descontoTipo === 'porcentagem') {
      const valorDescontoGeral = (this.subtotal * this.desconto) / 100;
      this.total = this.subtotal - valorDescontoGeral;
    } else {
      this.total = this.subtotal - this.desconto;
    }
  }

  gerarRecibo() {
    if (this.itensOrcamento.length === 0) {
      this.mostrarNotificacao('Adicione itens ao orçamento primeiro', 'error');
      return;
    }

    if (!this.ultimoOrcamentoId) {
      this.mostrarNotificacao('Gere um orçamento primeiro', 'error');
      return;
    }

    const valorTotal = this.total.toFixed(2).replace('.', ',');
    const hoje = new Date();
    const dataHoje = hoje.toLocaleDateString('pt-BR');
    const clienteNome = this.cliente.nome || this.cliente.empresa || '____________________';
    const empresa = this.cliente.empresa || '';
    const cpfCnpj = this.cliente.cpf_cnpj || '';

    const origem = window.location.origin;

    const html = `
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Recibo - N.D Connect</title>
  <style>
    :root {
      --nd-primary: #0C2B59;
      --nd-secondary: #E8622D;
      --nd-accent: #F7A64C;
      --nd-light: #FFFFFF;
      --nd-dark: #0C2B59;
      --nd-medium: #64748b;
    }
    * {
      box-sizing: border-box;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    body {
      margin: 0;
      padding: 24px;
      background: #f3f4f6;
      color: var(--nd-dark);
    }
    .recibo-container {
      max-width: 800px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(12, 43, 89, 0.15);
      overflow: hidden;
      border: 1px solid rgba(12, 43, 89, 0.1);
    }
    .recibo-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 24px;
      background: linear-gradient(135deg, var(--nd-secondary) 0%, var(--nd-accent) 100%);
      color: #ffffff;
    }
    .recibo-header-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .recibo-logo {
      width: 56px;
      height: 56px;
      border-radius: 8px;
      object-fit: cover;
      border: 2px solid rgba(255,255,255,0.7);
    }
    .recibo-header-title h1 {
      margin: 0;
      font-size: 20px;
      letter-spacing: 0.5px;
    }
    .recibo-header-title p {
      margin: 2px 0 0 0;
      font-size: 12px;
      opacity: 0.9;
    }
    .recibo-header-right {
      text-align: right;
      font-size: 12px;
    }
    .recibo-header-right span.label {
      display: block;
      text-transform: uppercase;
      font-weight: 600;
      letter-spacing: 0.08em;
      font-size: 10px;
      opacity: 0.8;
    }
    .recibo-header-right span.valor {
      display: block;
      margin-top: 4px;
      font-size: 18px;
      font-weight: 700;
    }
    .recibo-body {
      padding: 24px;
    }
    .secao {
      margin-bottom: 18px;
    }
    .secao-titulo {
      font-size: 13px;
      font-weight: 600;
      text-transform: uppercase;
      color: var(--nd-primary);
      letter-spacing: 0.08em;
      margin-bottom: 6px;
    }
    .linha {
      font-size: 13px;
      color: var(--nd-medium);
      margin: 2px 0;
    }
    .linha strong {
      color: var(--nd-dark);
    }
    .valor-destaque {
      font-size: 16px;
      font-weight: 700;
      color: var(--nd-secondary);
      margin-top: 4px;
    }
    .texto-principal {
      font-size: 13px;
      line-height: 1.6;
      color: var(--nd-dark);
      margin-top: 8px;
    }
    .rodape {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-top: 32px;
      font-size: 12px;
      color: var(--nd-medium);
    }
    .assinatura {
      text-align: center;
      margin-top: 40px;
    }
    .linha-assinatura {
      border-top: 1px solid rgba(12, 43, 89, 0.4);
      width: 260px;
      margin: 0 auto 6px auto;
    }
    .assinatura-nome {
      font-size: 12px;
      font-weight: 600;
      color: var(--nd-dark);
    }
    .assinatura-label {
      font-size: 11px;
      color: var(--nd-medium);
    }
    .recibo-footer {
      padding: 10px 24px 14px 24px;
      background: linear-gradient(135deg, rgba(12,43,89,0.03) 0%, rgba(232,98,45,0.06) 100%);
      border-top: 1px solid rgba(12, 43, 89, 0.08);
      font-size: 11px;
      color: var(--nd-medium);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .footer-left {
      font-weight: 500;
      color: var(--nd-primary);
    }
    .footer-right {
      text-align: right;
    }
    @media print {
      body {
        background: #ffffff;
        padding: 0;
      }
      .recibo-container {
        box-shadow: none;
        border-radius: 0;
        border: none;
      }
    }
  </style>
</head>
<body>
  <div class="recibo-container">
    <div class="recibo-header">
      <div class="recibo-header-left">
        <img src="${origem}/assets/img/logo.jpeg" alt="N.D Connect" class="recibo-logo">
        <div class="recibo-header-title">
          <h1>N.D CONNECT</h1>
          <p>Equipamentos para eventos • Palcos • Geradores • Som • Luz • Painéis LED</p>
        </div>
      </div>
      <div class="recibo-header-right">
        <span class="label">Recibo</span>
        <span class="valor">R$ ${valorTotal}</span>
        <span style="margin-top:4px;">Orçamento nº ${String(this.ultimoOrcamentoId).padStart(6, '0')}</span>
      </div>
    </div>
    <div class="recibo-body">
      <div class="secao">
        <div class="secao-titulo">Dados do Pagador</div>
        <div class="linha"><strong>Nome / Empresa:</strong> ${empresa || clienteNome}</div>
        ${cpfCnpj ? `<div class="linha"><strong>CPF/CNPJ:</strong> ${cpfCnpj}</div>` : ''}
        ${this.cliente.endereco ? `<div class="linha"><strong>Endereço:</strong> ${this.cliente.endereco}</div>` : ''}
      </div>

      <div class="secao">
        <div class="secao-titulo">Detalhes do Recebimento</div>
        <div class="linha"><strong>Data do Recibo:</strong> ${dataHoje}</div>
        ${this.nomeEvento ? `<div class="linha"><strong>Evento:</strong> ${this.nomeEvento}</div>` : ''}
        <div class="texto-principal">
          Recebemos de <strong>${empresa || clienteNome}</strong> a importância de
          <strong>R$ ${valorTotal}</strong>, referente aos serviços de locação/fornecimento de equipamentos e/ou
          estrutura para evento, conforme Orçamento nº <strong>${String(this.ultimoOrcamentoId).padStart(6, '0')}</strong>.
        </div>
      </div>

      <div class="secao">
        <div class="secao-titulo">Valor Recebido</div>
        <div class="valor-destaque">R$ ${valorTotal}</div>
        ${this.observacoes ? `<div class="linha" style="margin-top:8px;"><strong>Observações:</strong> ${this.observacoes}</div>` : ''}
      </div>

      <div class="assinatura">
        <div class="linha-assinatura"></div>
        <div class="assinatura-nome">N.D CONNECT - EQUIPAMENTOS PARA EVENTOS</div>
        <div class="assinatura-label">Responsável</div>
      </div>

      <div class="rodape">
        <div>Este recibo é válido como comprovante de pagamento.</div>
        <div>${dataHoje}</div>
      </div>
    </div>
    <div class="recibo-footer">
      <div class="footer-left">N.D CONNECT - Equipamentos para Eventos</div>
      <div class="footer-right">
        <div>Contato: (11) 99999-9999</div>
        <div>E-mail: contato@ndconnect.com.br</div>
      </div>
    </div>
  </div>
  <script>
    window.onload = function() {
      window.print();
    };
  </script>
</body>
</html>
    `;

    const reciboWindow = window.open('', '_blank', 'width=900,height=1000');
    if (!reciboWindow) {
      this.mostrarNotificacao('Permita pop-ups para visualizar o recibo em PDF.', 'error');
      return;
    }

    reciboWindow.document.open();
    reciboWindow.document.write(html);
    reciboWindow.document.close();
  }

  gerarOrcamento() {
    // Salvar o show atual antes de gerar
    this.salvarShowAtual();

    if (this.itensOrcamento.length === 0) {
      window.alert('Adicione pelo menos um item ao orçamento');
      return;
    }

    if (!this.cliente.empresa.trim()) {
      window.alert('Informe o nome da empresa');
      return;
    }

    // Validar dados do evento antes de gerar orçamento
    if (!this.validarDadosEvento()) {
      return; // Se a validação falhar, não continuar
    }

    // Usar valorTotalInformado se preenchido, senão usar total calculado
    const totalFinal = this.valorTotalInformado > 0 ? this.valorTotalInformado : this.total;

    const orcamento = {
      cliente: this.cliente,
      itens: this.itensOrcamento,
      observacoes: this.observacoes,
      desconto: this.desconto,
      desconto_tipo: this.descontoTipo,
      subtotal: this.subtotal,
      total: totalFinal,
      valor_total_informado: this.valorTotalInformado,
      data_orcamento: new Date().toISOString().split('T')[0], // Data atual no formato YYYY-MM-DD
      data_evento: JSON.stringify(this.datasEvento), // Múltiplas datas como JSON
      nome_evento: this.nomeEvento,
      quantidade_shows: this.quantidadeShows,
      shows: this.shows // Incluir todos os shows
    };

    console.log('Enviando orçamento:', orcamento);

    this.http.post<any>(`${this.apiUrl}/orcamentos`, orcamento).subscribe({
      next: (response) => {
        if (response.success) {
          this.ultimoOrcamentoId = response.data.id;
          // Atualizar o total com o valor informado se foi fornecido
          if (this.valorTotalInformado > 0) {
            this.total = this.valorTotalInformado;
          }

          // Criar lead automaticamente apenas se NÃO veio da gestão de leads
          if (!this.leadIdExistente) {
            console.log('OrcamentoPage: Criando novo lead do orçamento');
            this.criarLeadDoOrcamento(response.data.id);
          } else {
            console.log('OrcamentoPage: Veio da gestão de leads, não criando novo lead. LeadId existente:', this.leadIdExistente);
            this.mostrarNotificacao('Orçamento gerado para lead existente!', 'success');
          }

          window.alert('Orçamento gerado com sucesso!');
          // PDF será gerado manualmente pelos botões específicos
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

  gerarPDFCompleto() {
    if (!this.ultimoOrcamentoId) {
      this.mostrarNotificacao('Gere um orçamento primeiro', 'error');
      return;
    }

    // Redirecionar para simple_pdf.php com parâmetro para PDF completo
    const url = `${this.apiUrl}/simple_pdf.php?id=${this.ultimoOrcamentoId}&tipo=completo`;
    window.open(url, '_blank');
  }

  gerarPDFSimples() {
    if (!this.ultimoOrcamentoId) {
      this.mostrarNotificacao('Gere um orçamento primeiro', 'error');
      return;
    }

    // Redirecionar para simple_pdf.php com parâmetro para PDF simples
    const url = `${this.apiUrl}/simple_pdf.php?id=${this.ultimoOrcamentoId}&tipo=simples`;
    window.open(url, '_blank');
  }

  async gerarWord() {
    if (!this.ultimoOrcamentoId) {
      this.mostrarNotificacao('Gere um orçamento primeiro', 'error');
      return;
    }

    try {
      this.mostrarNotificacao('Gerando arquivo Word...', 'info');

      const children: any[] = [];

      // Carregar imagem se URL estiver preenchida
      let imagemBuffer: Uint8Array | null = null;
      if (this.urlLogoOrcamento) {
        console.log('Carregando imagem de:', this.urlLogoOrcamento);
        imagemBuffer = await this.obterImagemBase64(this.urlLogoOrcamento);
        console.log('Imagem carregada:', imagemBuffer ? 'Sucesso' : 'Falhou');
      }

      // CABEÇALHO COM TABELA ÚNICA
      children.push(
        new Table({
          width: { size: 100, type: WidthType.PERCENTAGE },
          borders: {
            top: { style: BorderStyle.SINGLE, size: 6, color: "0b2b58" },
            bottom: { style: BorderStyle.SINGLE, size: 6, color: "0b2b58" },
            left: { style: BorderStyle.SINGLE, size: 6, color: "0b2b58" },
            right: { style: BorderStyle.SINGLE, size: 6, color: "0b2b58" },
            insideHorizontal: { style: BorderStyle.NONE },
            insideVertical: { style: BorderStyle.NONE }
          },
          rows: [
            new TableRow({
              children: [
                new TableCell({
                  children: [
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: `ORÇAMENTO - ${String(this.ultimoOrcamentoId).padStart(5, '0')}/${new Date().getFullYear()}`,
                          bold: true,
                          color: "FFFFFF",
                          size: 26,
                          font: 'Arial'
                        })
                      ],
                      alignment: AlignmentType.CENTER
                    })
                  ],
                  shading: { type: ShadingType.SOLID, color: "0b2b58" },
                  margins: { top: 100, bottom: 50, left: 100, right: 100 }
                })
              ]
            }),
            // Linha para a logo - após número do orçamento
            new TableRow({
              children: [
                new TableCell({
                  children: [
                    new Paragraph({
                      children: imagemBuffer ? [
                        new ImageRun({
                          data: imagemBuffer,
                          transformation: {
                            width: 89,
                            height: 40
                          }
                        })
                      ] : [
                        new TextRun({
                          text: "[Logo]",
                          color: "FFFFFF",
                          size: 18,
                          font: 'Arial'
                        })
                      ],
                      alignment: AlignmentType.CENTER
                    })
                  ],
                  shading: { type: ShadingType.SOLID, color: "0b2b58" },
                  margins: { top: 50, bottom: 50, left: 100, right: 100 }
                })
              ]
            }),
            new TableRow({
              children: [
                new TableCell({
                  children: [
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "N.D CONNECT",
                          bold: true,
                          color: "FFFFFF",
                          size: 32,
                          font: 'Arial'
                        })
                      ],
                      alignment: AlignmentType.CENTER
                    })
                  ],
                  shading: { type: ShadingType.SOLID, color: "0b2b58" },
                  margins: { top: 100, bottom: 50, left: 100, right: 100 }
                })
              ]
            }),
            new TableRow({
              children: [
                new TableCell({
                  children: [
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "N.D CONNECT LTDA - SOLUÇÕES EM EVENTOS",
                          bold: true,
                          color: "FFFFFF",
                          size: 24,
                          font: 'Arial'
                        })
                      ],
                      alignment: AlignmentType.CENTER
                    })
                  ],
                  shading: { type: ShadingType.SOLID, color: "0b2b58" },
                  margins: { top: 50, bottom: 50, left: 100, right: 100 }
                })
              ]
            }),
            new TableRow({
              children: [
                new TableCell({
                  children: [
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "Contato: (11) 98147-0530 | Email: contato@ndconnect.com.br",
                          color: "FFFFFF",
                          size: 20,
                          font: 'Arial'
                        })
                      ],
                      alignment: AlignmentType.CENTER
                    })
                  ],
                  shading: { type: ShadingType.SOLID, color: "0b2b58" },
                  margins: { top: 50, bottom: 20, left: 100, right: 100 }
                })
              ]
            }),
            new TableRow({
              children: [
                new TableCell({
                  children: [
                    new Paragraph({
                      children: [
                        new TextRun({
                          text: "CNPJ: 62.496.094/0001-99",
                          color: "FFFFFF",
                          size: 20,
                          font: 'Arial'
                        })
                      ],
                      alignment: AlignmentType.CENTER
                    })
                  ],
                  shading: { type: ShadingType.SOLID, color: "0b2b58" },
                  margins: { top: 20, bottom: 100, left: 100, right: 100 }
                })
              ]
            })
          ]
        })
      );

      children.push(new Paragraph({ text: "", spacing: { after: 200 } }));

      // TABELA DE PRODUTOS
      const produtosTableRows: TableRow[] = [];

      // Cabeçalho da tabela
      produtosTableRows.push(
        new TableRow({
          children: [
            new TableCell({
              children: [new Paragraph({ children: [new TextRun({ text: "Descrição", bold: true, font: 'Arial' })], alignment: AlignmentType.LEFT })],
              shading: { type: ShadingType.SOLID, color: "0b2b58" }
            }),
            new TableCell({
              children: [new Paragraph({ children: [new TextRun({ text: "Qtd", bold: true, font: 'Arial' })], alignment: AlignmentType.CENTER })],
              shading: { type: ShadingType.SOLID, color: "0b2b58" }
            }),
            new TableCell({
              children: [new Paragraph({ children: [new TextRun({ text: "Valor", bold: true, font: 'Arial' })], alignment: AlignmentType.CENTER })],
              shading: { type: ShadingType.SOLID, color: "0b2b58" }
            })
          ]
        })
      );

      // Linhas de produtos
      this.itensOrcamento.forEach((item) => {
        produtosTableRows.push(
          new TableRow({
            children: [
              new TableCell({
                children: [new Paragraph({ children: [new TextRun({ text: item.produto_nome, font: 'Arial' })] })]
              }),
              new TableCell({
                children: [new Paragraph({ children: [new TextRun({ text: `${item.quantidade} ${item.unidade}`, font: 'Arial' })], alignment: AlignmentType.CENTER })]
              }),
              new TableCell({
                children: [new Paragraph({ children: [new TextRun({ text: item.preco_unitario > 0 ? `R$ ${(item.subtotal).toFixed(2).replace('.', ',')}` : "", font: 'Arial' })], alignment: AlignmentType.CENTER })]
              })
            ]
          })
        );
      });

      children.push(
        new Table({
          width: { size: 100, type: WidthType.PERCENTAGE },
          borders: {
            top: { style: BorderStyle.SINGLE, size: 1, color: "000000" },
            bottom: { style: BorderStyle.SINGLE, size: 1, color: "000000" },
            left: { style: BorderStyle.SINGLE, size: 1, color: "000000" },
            right: { style: BorderStyle.SINGLE, size: 1, color: "000000" },
            insideHorizontal: { style: BorderStyle.SINGLE, size: 1, color: "D3D3D3" },
            insideVertical: { style: BorderStyle.SINGLE, size: 1, color: "D3D3D3" }
          },
          rows: produtosTableRows
        })
      );

      // LINHA DIVISÓRIA LARANJA
      children.push(
        new Paragraph({
          children: [new TextRun({ text: "" })],
          border: {
            bottom: {
              color: "E8622D",
              space: 1,
              style: BorderStyle.SINGLE,
              size: 24
            }
          },
          spacing: { before: 150, after: 150 }
        })
      );

      // TOTAL
      const totalParaExibir = this.valorTotalInformado > 0 ? this.valorTotalInformado : this.total;
      const totalFormatado = this.formatarMoeda(totalParaExibir);
      children.push(
        new Paragraph({
          children: [
            new TextRun({
              text: `TOTAL DO ORÇAMENTO: R$ ${totalFormatado} (${this.converterParaPalavras(Math.floor(totalParaExibir))} reais.)`,
              bold: true,
              size: 24,
              color: "666666",
              font: 'Arial'
            })
          ],
          alignment: AlignmentType.CENTER,
          spacing: { before: 100, after: 300 }
        })
      );

      // CRIAR BOXES PARA CADA SHOW
      for (let i = 1; i <= this.quantidadeShows; i++) {
        // Título do cronograma
        children.push(
          new Paragraph({
            children: [
              new TextRun({
                text: `Cronograma - ${this.nomeEvento || 'EVENTO'} - SHOW ${i}`,
                bold: true,
                size: 24,
                color: "0b2b58"
              })
            ],
            spacing: { before: 200, after: 100 }
          })
        );

        // Box amarelo com informações estruturadas
        const boxTexto: any[] = [];

        boxTexto.push(
          new Paragraph({
            children: [
              new TextRun({
                text: `Data do evento: ___/___/______ - das ___:___ às ___:___`,
                size: 22,
                font: 'Arial'
              })
            ],
            spacing: { after: 100 }
          })
        );

        boxTexto.push(
          new Paragraph({
            children: [
              new TextRun({
                text: `Local: ________________________________________________________`,
                size: 22,
                font: 'Arial'
              })
            ],
            spacing: { after: 150 }
          })
        );

        boxTexto.push(
          new Paragraph({
            children: [
              new TextRun({
                text: `Cronograma de montagem:`,
                bold: true,
                size: 22,
                font: 'Arial'
              })
            ],
            spacing: { after: 100 }
          })
        );

        // Linhas em branco para os itens
        const categorias = [...new Set(this.itensOrcamento.map(item => item.categoria_nome || 'Outros'))];
        categorias.forEach((categoria) => {
          boxTexto.push(
            new Paragraph({
              children: [
                new TextRun({
                  text: `• ${categoria}: ________________________________________________________`,
                  size: 20,
                  font: 'Arial'
                })
              ],
              spacing: { after: 80 }
            })
          );
        });

        boxTexto.push(
          new Paragraph({
            children: [new TextRun({ text: "" })],
            spacing: { after: 150 }
          })
        );

        children.push(
          new Table({
            width: { size: 100, type: WidthType.PERCENTAGE },
            borders: {
              top: { style: BorderStyle.NONE },
              bottom: { style: BorderStyle.NONE },
              left: { style: BorderStyle.NONE },
              right: { style: BorderStyle.NONE },
              insideHorizontal: { style: BorderStyle.NONE },
              insideVertical: { style: BorderStyle.NONE }
            },
            rows: [
              new TableRow({
                children: [
                  new TableCell({
                    children: boxTexto,
                    shading: { type: ShadingType.SOLID, color: "fff8dc" },
                    margins: { top: 150, bottom: 150, left: 150, right: 150 }
                  })
                ]
              })
            ]
          })
        );

        children.push(new Paragraph({ text: "", spacing: { after: 300 } }));
      }

      // OBSERVAÇÕES
      if (this.observacoes) {
        children.push(new Paragraph({ text: "", spacing: { after: 150 } }));
        children.push(
          new Paragraph({
            children: [
              new TextRun({
                text: "OBSERVAÇÕES:",
                bold: true,
                size: 24,
                color: "0b2b58",
                font: 'Arial'
              })
            ],
            spacing: { after: 150 }
          })
        );

        const observacoesFormatadas = this.observacoes.split('\n').map((linha, index) =>
          new Paragraph({
            children: [
              new TextRun({
                text: linha || "",
                size: 22
              })
            ],
            spacing: { before: index === 0 ? 0 : 50, after: 50 }
          })
        );

        children.push(
          new Table({
            width: { size: 100, type: WidthType.PERCENTAGE },
            borders: {
              top: { style: BorderStyle.NONE },
              bottom: { style: BorderStyle.NONE },
              left: { style: BorderStyle.NONE },
              right: { style: BorderStyle.NONE },
              insideHorizontal: { style: BorderStyle.NONE },
              insideVertical: { style: BorderStyle.NONE }
            },
            rows: [
              new TableRow({
                children: [
                  new TableCell({
                    children: observacoesFormatadas,
                    shading: { type: ShadingType.SOLID, color: "fff8dc" },
                    margins: { top: 150, bottom: 150, left: 150, right: 150 }
                  })
                ]
              })
            ]
          })
        );
      }

      // ASSINATURA E FECHAMENTO
      children.push(new Paragraph({ text: "", spacing: { after: 400 } }));

      // Linha de assinatura
      children.push(
        new Paragraph({
          children: [
            new TextRun({
              text: "_".repeat(80),
              size: 20,
              font: 'Arial'
            })
          ],
          spacing: { after: 50 }
        })
      );

      // Texto assinatura contratante
      children.push(
        new Paragraph({
          children: [
            new TextRun({
              text: "Assinatura do contratante",
              size: 20,
              font: 'Arial'
            })
          ],
          alignment: AlignmentType.CENTER,
          spacing: { after: 300 }
        })
      );

      // Linha em branco
      children.push(new Paragraph({ text: "", spacing: { after: 300 } }));

      // Fechamento
      children.push(
        new Paragraph({
          children: [
            new TextRun({
              text: "Atenciosamente,",
              size: 22,
              font: 'Arial'
            })
          ],
          alignment: AlignmentType.RIGHT,
          spacing: { after: 100 }
        })
      );

      children.push(new Paragraph({ text: "", spacing: { after: 100 } }));

      children.push(
        new Paragraph({
          children: [
            new TextRun({
              text: "N.D Connect",
              bold: true,
              size: 22,
              font: 'Arial'
            })
          ],
          alignment: AlignmentType.RIGHT,
          spacing: { after: 0 }
        })
      );

      // Criar documento
      const doc = new DocxDocument({
        sections: [{
          properties: {
            page: {
              margin: {
                top: 720,
                right: 720,
                bottom: 720,
                left: 720
              }
            }
          },
          children: children
        }]
      });

      // Gerar e baixar o arquivo
      const blob = await Packer.toBlob(doc);
      const nomeCliente = (this.cliente.empresa || this.cliente.nome || 'cliente').split(' ')[0].toLowerCase();
      saveAs(blob, `orcamento_${nomeCliente}_${this.ultimoOrcamentoId}.docx`);

      this.mostrarNotificacao('Arquivo Word gerado com sucesso!', 'success');

    } catch (error) {
      console.error('Erro ao gerar Word:', error);
      this.mostrarNotificacao('Erro ao gerar arquivo Word', 'error');
    }
  }

  // Formatar valor em moeda brasileira (R$ 1.234,56)
  formatarMoeda(valor: number): string {
    return valor.toLocaleString('pt-BR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  converterParaPalavras(numero: number): string {
    const unidades = ['', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'];
    const dezenas = ['dez', 'onze', 'doze', 'treze', 'quatorze', 'quinze', 'dezesseis', 'dezessete', 'dezoito', 'dezenove'];
    const dezenasRedondas = ['', '', 'vinte', 'trinta', 'quarenta', 'cinquenta', 'sessenta', 'setenta', 'oitenta', 'noventa'];
    const centenas = ['', 'cento', 'duzentos', 'trezentos', 'quatrocentos', 'quinhentos', 'seiscentos', 'setecentos', 'oitocentos', 'novecentos'];
    const escalas = ['', 'mil', 'milhão', 'bilhão', 'trilhão'];

    if (numero === 0) return 'zero';
    if (numero === 100) return 'cem';

    const grupos = [];
    let grupoIdx = 0;

    while (numero > 0) {
      const grupo = numero % 1000;
      if (grupo !== 0) {
        const partes = [];
        const c = Math.floor(grupo / 100);
        const d = Math.floor((grupo % 100) / 10);
        const u = grupo % 10;

        if (c > 0) partes.push(centenas[c]);

        if (d === 1) {
          partes.push(dezenas[u]); // 10-19
        } else {
          if (d > 0) partes.push(dezenasRedondas[d]);
          if (u > 0) partes.push(unidades[u]);
        }

        const textoGrupo = partes.join(' e ');
        if (grupoIdx > 0) {
          grupos.unshift(textoGrupo + ' ' + escalas[grupoIdx]);
        } else {
          grupos.unshift(textoGrupo);
        }
      }
      numero = Math.floor(numero / 1000);
      grupoIdx++;
    }

    return grupos.join(' e ').trim();
  }

  criarLeadDoOrcamento(orcamentoId: number) {
    // Preparar dados do lead baseado no cliente do orçamento
    const dadosLead = {
      nome: this.cliente.nome?.trim() || this.cliente.empresa.trim(),
      email: this.cliente.email?.trim() || '',
      telefone: this.cliente.telefone?.trim() || '',
      empresa: this.cliente.empresa?.trim() || '',
      origem: 'orcamento', // Origem específica para leads criados a partir de orçamentos
      mensagem: `Lead criado automaticamente a partir do orçamento #${orcamentoId}. ${this.observacoes ? 'Observações: ' + this.observacoes : ''}`,
      status: 'contatado', // Já marcar como contatado
      orcamento_id: orcamentoId, // Referência ao orçamento
      observacoes: `Cliente interessado em orçamento de R$ ${this.total.toFixed(2).replace('.', ',')}. ${this.observacoes || ''}`
    };

    console.log('Criando lead do orçamento:', dadosLead);

    this.http.post<any>(`${this.apiUrl}/leads`, dadosLead).subscribe({
      next: (response) => {
        if (response.success) {
          console.log('Lead criado com sucesso:', response.data);
          this.mostrarNotificacao(`Lead "${this.cliente.nome || this.cliente.empresa}" criado e marcado como contatado!`, 'success');
        } else {
          console.error('Erro ao criar lead:', response.message);
          this.mostrarNotificacao('Orçamento gerado, mas houve erro ao criar lead', 'error');
        }
      },
      error: (error) => {
        console.error('Erro ao criar lead do orçamento:', error);
        this.mostrarNotificacao('Orçamento gerado, mas houve erro ao criar lead', 'error');
      }
    });
  }

  compartilharWhatsApp() {
    if (this.itensOrcamento.length === 0) {
      this.mostrarNotificacao('Adicione itens ao orçamento primeiro', 'error');
      return;
    }

    // Se não tem orçamento gerado, gerar um primeiro
    if (!this.ultimoOrcamentoId) {
      this.gerarOrcamento();
      return;
    }

    try {
      const pdfUrl = `${this.apiUrl}/simple_pdf.php?id=${this.ultimoOrcamentoId}`;
      const mensagem = `🏢 *N.D CONNECT - EQUIPAMENTOS PARA EVENTOS*

Olá ${this.cliente.nome || this.cliente.empresa}! 👋

Segue o orçamento solicitado:

📋 *Orçamento Nº ${this.ultimoOrcamentoId}*
💰 *Valor Total: R$ ${this.total.toFixed(2).replace('.', ',')}*
📅 *Data do Evento: ${new Date().toLocaleDateString('pt-BR')}*

📄 *Visualizar PDF:* ${pdfUrl}

${this.itensOrcamento.length > 0 ? `\n📦 *Itens incluídos:*\n${this.itensOrcamento.map(item => `• ${item.produto_nome} (${item.quantidade}x)`).join('\n')}` : ''}

${this.observacoes ? `\n📝 *Observações:*\n${this.observacoes}` : ''}

✨ *Agradecemos pela preferência!*
🎉 *N.D Connect - Sua parceira em eventos inesquecíveis*`;

      // Verificar se tem telefone válido para envio direto
      if (this.cliente.telefone && this.validarTelefone(this.cliente.telefone)) {
        const numeroWhatsApp = this.obterNumeroWhatsApp(this.cliente.telefone);
        const whatsappUrl = `https://wa.me/${numeroWhatsApp}?text=${encodeURIComponent(mensagem)}`;
        this.mostrarNotificacao(`Enviando para ${this.cliente.telefone}...`, 'info');
        window.open(whatsappUrl, '_blank');
      } else {
        // Fallback: abrir WhatsApp sem número específico
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(mensagem)}`;
        this.mostrarNotificacao('Abrindo WhatsApp...', 'info');
        window.open(whatsappUrl, '_blank');
      }
    } catch (error) {
      this.mostrarNotificacao('Erro ao abrir WhatsApp', 'error');
    }
  }

  salvarPDF() {
    if (this.itensOrcamento.length === 0) {
      this.mostrarNotificacao('Adicione itens ao orçamento primeiro', 'error');
      return;
    }

    // Se não tem orçamento gerado, gerar um primeiro
    if (!this.ultimoOrcamentoId) {
      this.gerarOrcamento();
      return;
    }

    try {
      this.mostrarNotificacao('Iniciando download do PDF...', 'info');

      // Usar pdf_real.php para download real do PDF
      const pdfUrl = `${this.apiUrl}/pdf_real.php?id=${this.ultimoOrcamentoId}`;

      // Criar link temporário para download
      const link = this.documentRef.createElement('a');
      link.href = pdfUrl;
      const primeiroNome = (this.cliente.nome || this.cliente.empresa).split(' ')[0].toLowerCase();
      link.download = `orcamento_${primeiroNome}_${this.ultimoOrcamentoId}.pdf`;
      link.target = '_blank';
      this.documentRef.body.appendChild(link);
      link.click();
      this.documentRef.body.removeChild(link);

      // Feedback de sucesso após um pequeno delay
      setTimeout(() => {
        this.mostrarNotificacao('Download iniciado com sucesso!', 'success');
      }, 1000);
    } catch (error) {
      this.mostrarNotificacao('Erro ao baixar PDF', 'error');
    }
  }

  compartilhar() {
    if (this.itensOrcamento.length === 0) {
      this.mostrarNotificacao('Adicione itens ao orçamento primeiro', 'error');
      return;
    }

    // Se não tem orçamento gerado, gerar um primeiro
    if (!this.ultimoOrcamentoId) {
      this.gerarOrcamento();
      return;
    }

    // Verificar se o navegador suporta Web Share API
    if (navigator && 'share' in navigator) {
      this.compartilharNativo();
    } else {
      // Fallback para navegadores que não suportam Web Share API
      this.compartilharFallback();
    }
  }

  async compartilharNativo() {
    try {
      const pdfUrl = `${this.apiUrl}/simple_pdf.php?id=${this.ultimoOrcamentoId}`;
      const titulo = `Orçamento N.D Connect - ${this.ultimoOrcamentoId}`;
      const texto = `Orçamento de R$ ${this.total.toFixed(2).replace('.', ',')} - Evento: ${this.nomeEvento || 'Evento'}`;

      await navigator.share({
        title: titulo,
        text: texto,
        url: pdfUrl
      });
    } catch (error) {
      console.log('Compartilhamento cancelado ou erro:', error);
      // Se o usuário cancelar, não fazer nada
    }
  }

  compartilharFallback() {
    const opcao = window.prompt('Escolha uma opção:\n1 - WhatsApp\n2 - Salvar PDF\n3 - Copiar Link\n\nDigite o número da opção:');

    if (opcao === '1') {
      this.compartilharWhatsApp();
    } else if (opcao === '2') {
      this.salvarPDF();
    } else if (opcao === '3') {
      this.copiarLink();
    }
  }

  copiarLink() {
    if (this.itensOrcamento.length === 0) {
      this.mostrarNotificacao('Adicione itens ao orçamento primeiro', 'error');
      return;
    }

    // Se não tem orçamento gerado, gerar um primeiro
    if (!this.ultimoOrcamentoId) {
      this.gerarOrcamento();
      return;
    }

    try {
      const pdfUrl = `${this.apiUrl}/simple_pdf.php?id=${this.ultimoOrcamentoId}`;
      const textoCompleto = `Orçamento N.D Connect - ${this.ultimoOrcamentoId}\nValor: R$ ${this.total.toFixed(2).replace('.', ',')}\nEvento: ${this.nomeEvento || 'Evento'}\n\nVisualizar: ${pdfUrl}`;

      if (navigator.clipboard) {
        navigator.clipboard.writeText(textoCompleto).then(() => {
          this.mostrarNotificacao('Link copiado para a área de transferência!', 'success');
        }).catch(() => {
          this.copiarLinkFallback(textoCompleto);
        });
      } else {
        this.copiarLinkFallback(textoCompleto);
      }
    } catch (error) {
      this.mostrarNotificacao('Erro ao copiar link', 'error');
    }
  }

  copiarLinkFallback(textoCompleto: string) {
    // Fallback para navegadores mais antigos
    const textArea = this.documentRef.createElement('textarea');
    textArea.value = textoCompleto;
    this.documentRef.body.appendChild(textArea);
    textArea.select();
    this.documentRef.execCommand('copy');
    this.documentRef.body.removeChild(textArea);
    this.mostrarNotificacao('Link copiado para a área de transferência!', 'success');
  }

  mostrarAlerta(mensagem: string) {
    // Criar notificação customizada
    this.mostrarNotificacao(mensagem, 'success');
  }

  mostrarNotificacao(mensagem: string, tipo: 'success' | 'error' | 'info' = 'info') {
    // Criar elemento de notificação
    const notificacao = this.documentRef.createElement('div');
    notificacao.className = `notificacao notificacao-${tipo}`;
    notificacao.innerHTML = `
      <div class="notificacao-content">
        <ion-icon name="${tipo === 'success' ? 'checkmark-circle' : tipo === 'error' ? 'warning' : 'information-circle'}" class="notificacao-icon"></ion-icon>
        <span class="notificacao-texto">${mensagem}</span>
      </div>
    `;

    // Adicionar estilos inline
    notificacao.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: ${tipo === 'success' ? '#10b981' : tipo === 'error' ? '#ef4444' : '#3b82f6'};
      color: white;
      padding: 16px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 10000;
      max-width: 300px;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      font-size: 14px;
      font-weight: 500;
      transform: translateX(100%);
      transition: transform 0.3s ease;
    `;

    // Adicionar ao DOM
    this.documentRef.body.appendChild(notificacao);

    // Animar entrada
    setTimeout(() => {
      notificacao.style.transform = 'translateX(0)';
    }, 100);

    // Remover após 3 segundos
    setTimeout(() => {
      notificacao.style.transform = 'translateX(100%)';
      setTimeout(() => {
        if (this.documentRef.body.contains(notificacao)) {
          this.documentRef.body.removeChild(notificacao);
        }
      }, 300);
    }, 3000);
  }

  limparOrcamento() {
    this.itensOrcamento = [];
    this.cliente = {
      nome: '',
      email: '',
      telefone: '',
      endereco: '',
      cpf_cnpj: '',
      empresa: ''
    };
    this.observacoes = '';
    this.desconto = 0;
    this.subtotal = 0;
    this.total = 0;
    this.ultimoOrcamentoId = null;
    this.leadIdExistente = null; // Resetar o leadId existente

    // Limpar produtos customizados
    this.produtoCustomizado = {
      nome: '',
      valorUnitario: 0,
      valorTotal: 0,
      unidade: '',
      quantidade: 1,
      subtotal: 0,
      usarValorTotal: false
    };

    // Limpar múltiplas datas
    this.datasEvento = [];

    // Reinicializar shows
    this.inicializarShows();
  }

  trackByProdutoId(index: number, produto: Produto): number {
    return produto.id;
  }

  trackByItemId(index: number, item: ItemOrcamento): number {
    return item.produto_id || index;
  }

  abrirHistorico() {
    const url = `${this.apiUrl}/historico_orcamentos.php`;
    window.open(url, '_blank');
  }

  formatarTelefone(event: any) {
    let value = event.target.value.replace(/\D/g, '');

    if (value.length <= 11) {
      if (value.length <= 2) {
        this.cliente.telefone = value;
      } else if (value.length <= 6) {
        this.cliente.telefone = `(${value.slice(0, 2)}) ${value.slice(2)}`;
      } else if (value.length <= 10) {
        this.cliente.telefone = `(${value.slice(0, 2)}) ${value.slice(2, 6)}-${value.slice(6)}`;
      } else {
        this.cliente.telefone = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`;
      }
    }
  }

  validarTelefone(telefone: string): boolean {
    // Remove todos os caracteres não numéricos
    const numero = telefone.replace(/\D/g, '');

    // Verifica se tem 10 ou 11 dígitos (com DDD)
    return numero.length === 10 || numero.length === 11;
  }

  obterNumeroWhatsApp(telefone: string): string {
    // Remove todos os caracteres não numéricos
    const numero = telefone.replace(/\D/g, '');

    // Adiciona código do país (+55) se não tiver
    if (numero.length === 10 || numero.length === 11) {
      return `+55${numero}`;
    }

    return numero;
  }

  validarEmail(email: string): boolean {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  validarEmailInput(event: any) {
    const email = event.target.value;
    if (email && !this.validarEmail(email)) {
      // Opcional: mostrar feedback visual de e-mail inválido
      console.log('E-mail inválido:', email);
    }
  }

  compartilharEmail() {
    if (this.itensOrcamento.length === 0) {
      this.mostrarNotificacao('Adicione itens ao orçamento primeiro', 'error');
      return;
    }

    // Se não tem orçamento gerado, gerar um primeiro
    if (!this.ultimoOrcamentoId) {
      this.gerarOrcamento();
      return;
    }

    try {
      const pdfUrl = `${this.apiUrl}/pdf_real.php?id=${this.ultimoOrcamentoId}`;
      const orcamentoUrl = `${this.apiUrl}/simple_pdf.php?id=${this.ultimoOrcamentoId}`;

      const assunto = `Orçamento N.D Connect - Nº ${this.ultimoOrcamentoId.toString().padStart(6, '0')}`;

      const corpo = `Olá ${this.cliente.nome || this.cliente.empresa}! 👋

Esperamos que esteja bem! Segue em anexo o orçamento solicitado para seu evento.

📋 *DETALHES DO ORÇAMENTO*
• Número: ${this.ultimoOrcamentoId.toString().padStart(6, '0')}
• Valor Total: R$ ${this.total.toFixed(2).replace('.', ',')}
• Evento: ${this.nomeEvento || 'Evento'}

📦 *ITENS INCLUÍDOS*
${this.itensOrcamento.map(item => `• ${item.produto_nome} (${item.quantidade}x) - R$ ${(item.preco_unitario * item.quantidade).toFixed(2).replace('.', ',')}`).join('\n')}

${this.observacoes ? `\n📝 *OBSERVAÇÕES*\n${this.observacoes}` : ''}

📄 *ARQUIVOS ANEXOS*
• PDF para impressão: ${pdfUrl}
• Visualização online: ${orcamentoUrl}

✨ *Agradecemos pela preferência!*
🎉 *N.D Connect - Sua parceira em eventos inesquecíveis*

---
N.D CONNECT - EQUIPAMENTOS PARA EVENTOS
Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED
Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br`;

      // Verificar se tem e-mail válido para envio direto
      if (this.cliente.email && this.validarEmail(this.cliente.email)) {
        const emailUrl = `mailto:${this.cliente.email}?subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`;
        this.mostrarNotificacao(`Enviando e-mail para ${this.cliente.email}...`, 'info');
        window.open(emailUrl, '_blank');
      } else {
        // Fallback: abrir cliente de e-mail sem destinatário específico
        const emailUrl = `mailto:?subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`;
        this.mostrarNotificacao('Abrindo cliente de e-mail...', 'info');
        window.open(emailUrl, '_blank');
      }
    } catch (error) {
      this.mostrarNotificacao('Erro ao abrir cliente de e-mail', 'error');
    }
  }

  carregarDadosDoLead() {
    this.route.queryParams.subscribe(params => {
      if (params['leadId']) {
        // Capturar o ID do lead existente
        this.leadIdExistente = parseInt(params['leadId']);

        // Preencher dados do lead
        this.cliente.nome = params['nome'] || '';
        this.cliente.email = params['email'] || '';
        this.cliente.telefone = params['telefone'] || '';
        this.cliente.empresa = params['empresa'] || '';
        this.observacoes = params['mensagem'] || '';

        // Mostrar notificação de dados preenchidos
        this.mostrarNotificacao('Dados do lead carregados automaticamente!', 'success');

        console.log('OrcamentoPage: Veio da gestão de leads, leadId:', this.leadIdExistente);
      } else {
        console.log('OrcamentoPage: Não veio da gestão de leads, criará novo lead se necessário');
      }
    });
  }

  voltarHome() {
    this.router.navigate(['/home']);
  }

  voltarPainel() {
    this.router.navigate(['/painel-orcamento']);
  }
}
