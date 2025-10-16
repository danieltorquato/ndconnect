import { Component, OnInit, Inject } from '@angular/core';
import { IonHeader, IonToolbar, IonTitle, IonContent, IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonButton, IonItem, IonLabel, IonInput, IonTextarea, IonSelect, IonSelectOption, IonList, IonIcon, IonGrid, IonRow, IonCol, IonBadge, IonDatetime, IonNote, IonButtons } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { add, remove, calculator, document, person, call, mail, location, search, warning, share, download, logoWhatsapp, list, close, copy, checkmark, checkmarkCircle, informationCircle, star, home, calendar, documentText, trash } from 'ionicons/icons';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { DOCUMENT } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { environment } from '../../environments/environment';

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
  desconto_porcentagem?: number;
  desconto_valor?: number;
  subtotal_com_desconto?: number;
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
  imports: [IonHeader, IonToolbar, IonTitle, IonContent, IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonButton, IonItem, IonLabel, IonInput, IonTextarea, IonSelect, IonSelectOption, IonList, IonIcon, IonGrid, IonRow, IonCol, IonBadge, IonDatetime, IonNote, CommonModule, FormsModule],
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
  dataValidade: string = '';
  ultimoOrcamentoId: number | null = null;
  dataMinima: string = '';

  private apiUrl = environment.apiUrl;

  constructor(
    private http: HttpClient,
    @Inject(DOCUMENT) private document: Document,
    private router: Router,
    private route: ActivatedRoute
  ) {
    addIcons({list,search,close,add,remove,person,calendar,warning,documentText,calculator,trash,call,mail,location,share,download,logoWhatsapp,copy,checkmark,checkmarkCircle,informationCircle,star,home});
  }

  ngOnInit() {
    this.carregarCategorias();
    this.carregarProdutosIniciais();
    this.carregarDadosDoLead();
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

  aplicarDescontoItem(produtoId: number, tipo: 'porcentagem' | 'valor', valor: number) {
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
      desconto_tipo: this.descontoTipo,
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

Olá ${this.cliente.nome}! 👋

Segue o orçamento solicitado:

📋 *Orçamento Nº ${this.ultimoOrcamentoId}*
💰 *Valor Total: R$ ${this.total.toFixed(2).replace('.', ',')}*
📅 *Válido até: ${new Date(this.dataValidade).toLocaleDateString('pt-BR')}*

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
      const link = this.document.createElement('a');
      link.href = pdfUrl;
      const primeiroNome = this.cliente.nome.split(' ')[0].toLowerCase();
      link.download = `orcamento_${primeiroNome}_${this.ultimoOrcamentoId}.pdf`;
      link.target = '_blank';
      this.document.body.appendChild(link);
      link.click();
      this.document.body.removeChild(link);

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
      const texto = `Orçamento de R$ ${this.total.toFixed(2).replace('.', ',')} - Válido até ${new Date(this.dataValidade).toLocaleDateString('pt-BR')}`;

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
      const textoCompleto = `Orçamento N.D Connect - ${this.ultimoOrcamentoId}\nValor: R$ ${this.total.toFixed(2).replace('.', ',')}\nVálido até: ${new Date(this.dataValidade).toLocaleDateString('pt-BR')}\n\nVisualizar: ${pdfUrl}`;

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
    const textArea = this.document.createElement('textarea');
    textArea.value = textoCompleto;
    this.document.body.appendChild(textArea);
    textArea.select();
    this.document.execCommand('copy');
    this.document.body.removeChild(textArea);
    this.mostrarNotificacao('Link copiado para a área de transferência!', 'success');
  }

  mostrarAlerta(mensagem: string) {
    // Criar notificação customizada
    this.mostrarNotificacao(mensagem, 'success');
  }

  mostrarNotificacao(mensagem: string, tipo: 'success' | 'error' | 'info' = 'info') {
    // Criar elemento de notificação
    const notificacao = this.document.createElement('div');
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
    this.document.body.appendChild(notificacao);

    // Animar entrada
    setTimeout(() => {
      notificacao.style.transform = 'translateX(0)';
    }, 100);

    // Remover após 3 segundos
    setTimeout(() => {
      notificacao.style.transform = 'translateX(100%)';
      setTimeout(() => {
        if (this.document.body.contains(notificacao)) {
          this.document.body.removeChild(notificacao);
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
    this.definirDataValidadePadrao();
  }

  trackByProdutoId(index: number, produto: Produto): number {
    return produto.id;
  }

  trackByItemId(index: number, item: ItemOrcamento): number {
    return item.produto_id;
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

      const corpo = `Olá ${this.cliente.nome}! 👋

Esperamos que esteja bem! Segue em anexo o orçamento solicitado para seu evento.

📋 *DETALHES DO ORÇAMENTO*
• Número: ${this.ultimoOrcamentoId.toString().padStart(6, '0')}
• Valor Total: R$ ${this.total.toFixed(2).replace('.', ',')}
• Válido até: ${new Date(this.dataValidade).toLocaleDateString('pt-BR')}

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
        // Preencher dados do lead
        this.cliente.nome = params['nome'] || '';
        this.cliente.email = params['email'] || '';
        this.cliente.telefone = params['telefone'] || '';
        this.cliente.empresa = params['empresa'] || '';
        this.observacoes = params['mensagem'] || '';

        // Mostrar notificação de dados preenchidos
        this.mostrarNotificacao('Dados do lead carregados automaticamente!', 'success');
      }
    });
  }

  voltarHome() {
    this.router.navigate(['/home']);
  }
}
