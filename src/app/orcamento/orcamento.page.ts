import { Component, OnInit, Inject } from '@angular/core';
import { IonHeader, IonToolbar, IonTitle, IonContent, IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonButton, IonItem, IonLabel, IonInput, IonTextarea, IonSelect, IonSelectOption, IonList, IonIcon, IonGrid, IonRow, IonCol, IonBadge, IonDatetime, IonNote, IonButtons, IonModal, IonChip, IonSegmentButton } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { add, remove, calculator, document, person, call, mail, location, search, warning, share, download, logoWhatsapp, list, close, copy, checkmark, checkmarkCircle, informationCircle, star, home, calendar, documentText, trash, arrowBack } from 'ionicons/icons';
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
  imports: [IonSegmentButton, IonChip, IonModal, IonHeader, IonToolbar, IonTitle, IonContent, IonCard, IonCardHeader, IonCardTitle, IonCardContent, IonButton, IonItem, IonLabel, IonInput, IonTextarea, IonSelect, IonSelectOption, IonList, IonIcon, IonGrid, IonRow, IonCol, IonBadge, IonDatetime, IonNote, IonButtons, CommonModule, FormsModule],
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
  dataEvento: string = '';
  nomeEvento: string = '';
  ultimoOrcamentoId: number | null = null;
  dataMinima: string = '';
  leadIdExistente: number | null = null; // Para controlar se veio da gestão de leads

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
    @Inject(DOCUMENT) private document: Document,
    private router: Router,
    private route: ActivatedRoute
  ) {
    addIcons({arrowBack,list,add,remove,person,star,informationCircle,calendar,close,documentText,calculator,trash,search,warning,call,mail,location,share,download,logoWhatsapp,copy,checkmark,checkmarkCircle,home});
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

    if (this.datasEvento.length === 0) {
      window.alert('Selecione pelo menos uma data para o evento');
      return false;
    }

    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0); // Zerar horas para comparação apenas de data

    for (let dataStr of this.datasEvento) {
      const dataEvento = new Date(dataStr);
      dataEvento.setHours(0, 0, 0, 0);

      if (dataEvento < hoje) {
        window.alert('Nenhuma data do evento pode ser anterior a hoje');
        return false;
      }
    }

    return true;
  }

  // Métodos para múltiplas datas
  abrirSeletorDatas() {
    this.modalDatasAberto = true;
  }

  fecharModalDatas() {
    this.modalDatasAberto = false;
  }

  adicionarDataSelecionada(event: any) {
    let dataParaAdicionar = '';

    if (event && event.detail && event.detail.value) {
      dataParaAdicionar = event.detail.value;
    } else if (this.dataSelecionada) {
      dataParaAdicionar = this.dataSelecionada;
    }

    if (dataParaAdicionar) {
      // Verificar se a data já não foi adicionada
      if (!this.datasEvento.includes(dataParaAdicionar)) {
        this.datasEvento.push(dataParaAdicionar);
        console.log('Data adicionada:', dataParaAdicionar);
        console.log('Datas atuais:', this.datasEvento);
      } else {
        this.mostrarNotificacao('Esta data já foi selecionada', 'info');
      }
    }
  }

  onDataSelecionadaChange(event: any) {
    // Este método será chamado quando o ion-datetime com multiple=true for alterado
    // O valor será um array de strings no formato ISO
    console.log('Evento de mudança de data:', event);

    if (event && event.detail && event.detail.value) {
      const valor = event.detail.value;
      console.log('Valor recebido:', valor);

      if (Array.isArray(valor)) {
        this.datasEvento = [...valor];
      } else {
        this.datasEvento = [valor];
      }

      console.log('Datas atualizadas:', this.datasEvento);
    }
  }

  confirmarDatas() {
    if (this.datasEvento.length > 0) {
      this.fecharModalDatas();
      this.mostrarNotificacao(`${this.datasEvento.length} data(s) selecionada(s)`, 'success');
    }
  }

  removerData(index: number) {
    this.datasEvento.splice(index, 1);
  }

  formatarData(dataStr: string): string {
    const data = new Date(dataStr);
    return data.toLocaleDateString('pt-BR');
  }

  formatarDatasParaPDF(): string {
    if (this.datasEvento.length === 0) return '---';

    const datasFormatadas = this.datasEvento.map(data => {
      const d = new Date(data);
      return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    });

    if (datasFormatadas.length === 1) {
      return datasFormatadas[0];
    } else if (datasFormatadas.length === 2) {
      return `${datasFormatadas[0]} e ${datasFormatadas[1]}`;
    } else {
      const ultimaData = datasFormatadas.pop();
      return `${datasFormatadas.join(', ')} e ${ultimaData}`;
    }
  }

  calcularSubtotalCustomizado() {
    if (this.produtoCustomizado.usarValorTotal) {
      // Se está usando valor total, usar o valor total diretamente
      this.produtoCustomizado.subtotal = this.produtoCustomizado.valorTotal;
    } else if (this.produtoCustomizado.valorUnitario && this.produtoCustomizado.quantidade) {
      // Se está usando valor unitário, calcular subtotal
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

    // Validar se tem algum valor definido
    if (!this.produtoCustomizado.usarValorTotal && !this.produtoCustomizado.valorUnitario) {
      window.alert('Informe o valor unitário ou marque para usar valor total');
      return;
    }

    if (this.produtoCustomizado.usarValorTotal && !this.produtoCustomizado.valorTotal) {
      window.alert('Informe o valor total');
      return;
    }

    // Gerar ID único para produto customizado (negativo para diferenciar)
    const customId = -(Date.now() + Math.random() * 1000);

    const novoItem: ItemOrcamento = {
      produto_id: customId, // ID único negativo para produtos customizados
      produto_nome: this.produtoCustomizado.nome,
      categoria_nome: 'Customizado',
      quantidade: this.produtoCustomizado.quantidade,
      preco_unitario: this.produtoCustomizado.usarValorTotal ? 0 : (this.produtoCustomizado.valorUnitario || 0),
      subtotal: this.produtoCustomizado.subtotal,
      unidade: this.produtoCustomizado.unidade,
      produto_customizado: true,
      nome_customizado: this.produtoCustomizado.nome,
      valor_unitario_customizado: this.produtoCustomizado.usarValorTotal ? undefined : this.produtoCustomizado.valorUnitario,
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
      usarValorTotal: false
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

    const orcamento = {
      cliente: this.cliente,
      itens: this.itensOrcamento,
      observacoes: this.observacoes,
      desconto: this.desconto,
      desconto_tipo: this.descontoTipo,
      subtotal: this.subtotal,
      total: this.total,
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
📅 *Data do Evento: ${this.formatarDatasParaPDF()}*

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
      const primeiroNome = (this.cliente.nome || this.cliente.empresa).split(' ')[0].toLowerCase();
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
      const texto = `Orçamento de R$ ${this.total.toFixed(2).replace('.', ',')} - Evento: ${this.nomeEvento || 'Evento'} em ${this.formatarDatasParaPDF()}`;

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
      const textoCompleto = `Orçamento N.D Connect - ${this.ultimoOrcamentoId}\nValor: R$ ${this.total.toFixed(2).replace('.', ',')}\nEvento: ${this.nomeEvento || 'Evento'} em ${this.formatarDatasParaPDF()}\n\nVisualizar: ${pdfUrl}`;

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
• Data do Evento: ${this.formatarDatasParaPDF()}

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
