import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { environment } from '../../environments/environment';
import { addIcons } from 'ionicons';
import { home, add, trash, close, save, cube, swapHorizontal, alertCircle, checkmarkCircle } from 'ionicons/icons';
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
  IonSelect,
  IonSelectOption,
  IonList,
  IonModal,
  IonButtons,
  IonTextarea,
  IonBadge,
  AlertController
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

interface Produto {
  id?: number;
  nome: string;
  descricao: string;
  preco_unitario: number;
  unidade: string;
  categoria_id: number;
  categoria_nome?: string;
  popularidade?: number;
}

interface Categoria {
  id: number;
  nome: string;
}

@Component({
  selector: 'app-produtos',
  templateUrl: './produtos.page.html',
  styleUrls: ['./produtos.page.scss'],
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
    IonSelect,
    IonSelectOption,
    IonList,
    IonModal,
    IonButtons,
    IonTextarea,
    IonBadge,
    CommonModule,
    FormsModule
  ]
})
export class ProdutosPage implements OnInit {
  produtos: Produto[] = [];
  produtosFiltrados: Produto[] = [];
  categorias: Categoria[] = [];

  categoriaFiltro: string = '';
  termoBusca: string = '';

  modalAberto: boolean = false;
  produtoEditando: Produto = {
    nome: '',
    descricao: '',
    preco_unitario: 0,
    unidade: '',
    categoria_id: 0,
    popularidade: 0
  };

  // ESTOQUE
  estoqueAtual: Map<number, any> = new Map();
  alertasEstoque: any[] = [];
  modalEstoqueAberto: boolean = false;
  modalMovimentacaoAberto: boolean = false;
  produtoSelecionadoEstoque: any = null;

  movimentacaoForm = {
    tipo: 'entrada',
    quantidade: 0,
    observacoes: ''
  };

  constructor(
    private http: HttpClient,
    private router: Router,
    private alertController: AlertController
  ) {
    addIcons({ home, add, trash, close, save, cube, swapHorizontal, alertCircle, checkmarkCircle });
  }

  ngOnInit() {
    this.carregarProdutos();
    this.carregarCategorias();
    this.carregarEstoqueAtual();
    this.carregarAlertasEstoque();
  }

  carregarProdutos() {
    this.http.get<any>(`${environment.apiUrl}/produtos`).subscribe(
      (response) => {
        if (response.success) {
          // Mapear preco para preco_unitario para compatibilidade com a interface
          this.produtos = response.data.map((produto: any) => ({
            ...produto,
            preco_unitario: produto.preco
          }));
          this.produtosFiltrados = this.produtos;
        } else {
          console.error('Erro na resposta da API:', response.message);
          this.mostrarNotificacao('Erro ao carregar produtos: ' + response.message, 'danger');
        }
      },
      (error) => {
        console.error('Erro ao carregar produtos:', error);
        this.mostrarNotificacao('Erro ao carregar produtos', 'danger');
      }
    );
  }

  carregarCategorias() {
    this.http.get<any>(`${environment.apiUrl}/categorias`).subscribe(
      (response) => {
        if (response.success) {
          this.categorias = response.data;
        } else {
          console.error('Erro na resposta da API:', response.message);
        }
      },
      (error) => {
        console.error('Erro ao carregar categorias:', error);
      }
    );
  }

  filtrarProdutos() {
    let filtrados = this.produtos;

    if (this.categoriaFiltro) {
      filtrados = filtrados.filter(p => p.categoria_id == parseInt(this.categoriaFiltro));
    }

    if (this.termoBusca.trim()) {
      filtrados = filtrados.filter(p =>
        p.nome.toLowerCase().includes(this.termoBusca.toLowerCase()) ||
        p.descricao.toLowerCase().includes(this.termoBusca.toLowerCase())
      );
    }

    this.produtosFiltrados = filtrados;
  }

  adicionarProduto() {
    this.produtoEditando = {
      nome: '',
      descricao: '',
      preco_unitario: 0,
      unidade: '',
      categoria_id: this.categorias.length > 0 ? this.categorias[0].id : 0,
      popularidade: 0
    };
    this.modalAberto = true;
  }

  editarProduto(produto: Produto) {
    this.produtoEditando = { ...produto };
    this.modalAberto = true;
  }

  fecharModal() {
    this.modalAberto = false;
    this.produtoEditando = {
      nome: '',
      descricao: '',
      preco_unitario: 0,
      unidade: '',
      categoria_id: 0,
      popularidade: 0
    };
  }

  validarProduto(): boolean {
    return !!(
      this.produtoEditando.nome.trim() &&
      this.produtoEditando.preco_unitario > 0 &&
      this.produtoEditando.unidade.trim() &&
      this.produtoEditando.categoria_id > 0
    );
  }

  salvarProduto() {
    if (!this.validarProduto()) {
      this.mostrarNotificacao('Preencha todos os campos obrigatórios', 'warning');
      return;
    }

    const url = this.produtoEditando.id
      ? `${environment.apiUrl}/produtos/${this.produtoEditando.id}`
      : `${environment.apiUrl}/produtos`;

    const method = this.produtoEditando.id ? 'PUT' : 'POST';

    this.http.request(method, url, {
      body: this.produtoEditando
    }).subscribe(
      (response) => {
        this.mostrarNotificacao(
          this.produtoEditando.id ? 'Produto atualizado!' : 'Produto adicionado!',
          'success'
        );
        this.fecharModal();
        this.carregarProdutos();
      },
      (error) => {
        console.error('Erro ao salvar produto:', error);
        this.mostrarNotificacao('Erro ao salvar produto', 'danger');
      }
    );
  }

  excluirProduto(produtoId: number | undefined, event: Event) {
    event.stopPropagation();

    if (!produtoId) return;

    if (confirm('Tem certeza que deseja excluir este produto?')) {
      this.http.delete(`${environment.apiUrl}/produtos/${produtoId}`).subscribe(
        (response) => {
          this.mostrarNotificacao('Produto excluído!', 'success');
          this.carregarProdutos();
        },
        (error) => {
          console.error('Erro ao excluir produto:', error);
          this.mostrarNotificacao('Erro ao excluir produto', 'danger');
        }
      );
    }
  }

  mostrarNotificacao(mensagem: string, tipo: string) {
    // Implementar sistema de notificação
    console.log(`${tipo}: ${mensagem}`);
  }

  voltarHome() {
    this.router.navigate(['/painel']);
  }

  // ============================================
  // MÉTODOS DE ESTOQUE
  // ============================================

  carregarEstoqueAtual() {
    this.http.get<any>(`${environment.apiUrl}/estoque`).subscribe({
      next: (response) => {
        if (response.success) {
          response.data.forEach((item: any) => {
            this.estoqueAtual.set(item.produto_id, item);
          });
        }
      },
      error: (error) => {
        console.error('Erro ao carregar estoque:', error);
      }
    });
  }

  carregarAlertasEstoque() {
    this.http.get<any>(`${environment.apiUrl}/estoque/alertas`).subscribe({
      next: (response) => {
        if (response.success) {
          this.alertasEstoque = response.data;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar alertas:', error);
      }
    });
  }

  getEstoqueProduto(produtoId: number | undefined): any {
    if (!produtoId) {
      return {
        quantidade_disponivel: 0,
        quantidade_reservada: 0,
        quantidade_minima: 0
      };
    }

    return this.estoqueAtual.get(produtoId) || {
      quantidade_disponivel: 0,
      quantidade_reservada: 0,
      quantidade_minima: 0
    };
  }

  temEstoqueBaixo(produtoId: number | undefined): boolean {
    if (!produtoId) return false;

    const estoque = this.getEstoqueProduto(produtoId);
    return estoque.quantidade_minima > 0 &&
           estoque.quantidade_disponivel <= estoque.quantidade_minima;
  }

  abrirModalEstoque(produto: Produto) {
    if (!produto.id) return;

    this.produtoSelecionadoEstoque = { ...produto };

    this.http.get<any>(`${environment.apiUrl}/estoque/produto/${produto.id}`).subscribe({
      next: (response) => {
        if (response.success) {
          this.produtoSelecionadoEstoque.estoque = response.data;
          this.modalEstoqueAberto = true;
        }
      },
      error: (error) => {
        console.error('Erro ao carregar estoque do produto:', error);
        this.mostrarNotificacao('Erro ao carregar estoque', 'danger');
      }
    });
  }

  fecharModalEstoque() {
    this.modalEstoqueAberto = false;
    this.produtoSelecionadoEstoque = null;
  }

  async atualizarEstoqueMinimo() {
    if (!this.produtoSelecionadoEstoque?.id) return;

    const quantidade_minima = this.produtoSelecionadoEstoque.estoque?.quantidade_minima || 0;

    this.http.put<any>(
      `${environment.apiUrl}/estoque/produto/${this.produtoSelecionadoEstoque.id}/estoque-minimo`,
      { quantidade_minima }
    ).subscribe({
      next: async (response) => {
        if (response.success) {
          this.mostrarNotificacao('Estoque mínimo atualizado!', 'success');
          this.carregarEstoqueAtual();
          this.carregarAlertasEstoque();
        } else {
          this.mostrarNotificacao('Erro ao atualizar', 'danger');
        }
      },
      error: (error) => {
        console.error('Erro ao atualizar estoque mínimo:', error);
        this.mostrarNotificacao('Erro ao atualizar', 'danger');
      }
    });
  }

  abrirModalMovimentacao(produto: Produto) {
    this.produtoSelecionadoEstoque = { ...produto };
    this.movimentacaoForm = {
      tipo: 'entrada',
      quantidade: 0,
      observacoes: ''
    };
    this.modalMovimentacaoAberto = true;
  }

  fecharModalMovimentacao() {
    this.modalMovimentacaoAberto = false;
    this.produtoSelecionadoEstoque = null;
    this.movimentacaoForm = {
      tipo: 'entrada',
      quantidade: 0,
      observacoes: ''
    };
  }

  async registrarMovimentacao() {
    if (!this.produtoSelecionadoEstoque?.id) return;

    if (this.movimentacaoForm.quantidade <= 0) {
      this.mostrarNotificacao('Quantidade deve ser maior que zero', 'warning');
      return;
    }

    const dados = {
      produto_id: this.produtoSelecionadoEstoque.id,
      tipo: this.movimentacaoForm.tipo,
      quantidade: this.movimentacaoForm.quantidade,
      observacoes: this.movimentacaoForm.observacoes,
      pedido_id: null,
      usuario: 'admin'
    };

    this.http.post<any>(`${environment.apiUrl}/estoque/movimentacoes`, dados).subscribe({
      next: async (response) => {
        if (response.success) {
          this.mostrarNotificacao('Movimentação registrada!', 'success');
          this.fecharModalMovimentacao();
          this.carregarProdutos();
          this.carregarEstoqueAtual();
          this.carregarAlertasEstoque();
        } else {
          this.mostrarNotificacao('Erro: ' + response.message, 'danger');
        }
      },
      error: (error) => {
        console.error('Erro ao registrar movimentação:', error);
        this.mostrarNotificacao('Erro ao registrar movimentação', 'danger');
      }
    });
  }
}
