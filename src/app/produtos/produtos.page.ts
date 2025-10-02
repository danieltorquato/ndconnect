import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { addIcons } from 'ionicons';
import { home, add, trash, close, save } from 'ionicons/icons';
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
  IonTextarea
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

  constructor(
    private http: HttpClient,
    private router: Router
  ) {
    addIcons({ home, add, trash, close, save });
  }

  ngOnInit() {
    this.carregarProdutos();
    this.carregarCategorias();
  }

  carregarProdutos() {
    this.http.get<any[]>('http://localhost:8000/api/produtos').subscribe(
      (data) => {
        this.produtos = data;
        this.produtosFiltrados = data;
      },
      (error) => {
        console.error('Erro ao carregar produtos:', error);
        this.mostrarNotificacao('Erro ao carregar produtos', 'danger');
      }
    );
  }

  carregarCategorias() {
    this.http.get<any[]>('http://localhost:8000/api/categorias').subscribe(
      (data) => {
        this.categorias = data;
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
      ? `http://localhost:8000/api/produtos/${this.produtoEditando.id}`
      : 'http://localhost:8000/api/produtos';

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
      this.http.delete(`http://localhost:8000/api/produtos/${produtoId}`).subscribe(
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
    this.router.navigate(['/home']);
  }
}
