import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment';
import { NiveisAcessoService, NivelAcesso } from '../../services/niveis-acesso.service';
import { UsuariosService } from '../../services/usuarios.service';
import { Usuario } from '../../services/auth.service';
import { debounceTime, distinctUntilChanged, switchMap } from 'rxjs/operators';
import { Subject } from 'rxjs';
import { addIcons } from 'ionicons';
import {
  arrowBack, personAdd, create, trash, search, eye, checkmarkCircle, closeCircle,
  people, shield, mail, call, business, time, addCircle, refresh, close, briefcase, add, location } from 'ionicons/icons';
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
  IonSegment,
  IonSegmentButton,
  IonLabel,
  IonItem,
  IonBadge,
  IonSearchbar,
  IonModal,
  IonButtons,
  IonInput,
  IonSelect,
  IonSelectOption,
  IonFab,
  IonFabButton,
  IonChip,
  IonSpinner,
  IonRefresher,
  IonRefresherContent,
  IonGrid,
  IonRow,
  IonCol,
  IonList,
  IonThumbnail,
  IonToggle
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

// Interface removida - agora usando a do serviço

@Component({
  selector: 'app-gestao-usuarios',
  templateUrl: './gestao-usuarios.page.html',
  styleUrls: ['./gestao-usuarios.page.scss'],
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
    IonSegment,
    IonSegmentButton,
    IonLabel,
    IonItem,
    IonBadge,
    IonSearchbar,
    IonModal,
    IonButtons,
    IonInput,
    IonSelect,
    IonSelectOption,
    IonFab,
    IonFabButton,
    IonChip,
    IonSpinner,
    IonRefresher,
    IonRefresherContent,
    IonGrid,
    IonRow,
    IonCol,
    IonList,
    IonThumbnail,
    IonToggle,
    CommonModule,
    FormsModule
  ]
})
export class GestaoUsuariosPage implements OnInit {
  usuarios: Usuario[] = [];
  usuariosFiltrados: Usuario[] = [];
  niveisAcesso: NivelAcesso[] = [];
  statusFiltro: string = 'todos';
  termoPesquisa: string = '';
  loading = false;

  // Modal de criação/edição
  modalAberto: boolean = false;
  modoEdicao: boolean = false;
  usuarioEditando: Usuario | null = null;

  // Modal de pesquisa de funcionários
  modalFuncionariosAberto: boolean = false;
  funcionariosDisponiveis: any[] = [];
  termoPesquisaFuncionario: string = '';
  funcionarioSelecionado: any = null;

  // Validação de usuário em tempo real
  private verificarUsuarioSubject = new Subject<string>();
  validacaoUsuario = {
    verificando: false,
    existe: false,
    mensagem: '',
    valido: true
  };

  // Formulário
  formData = {
    usuario: '',
    senha: '',
    nivel_acesso: '',
    ativo: true,
    funcionario_id: null as number | null
  };

  // Contadores
  contadores = {
    total: 0,
    ativos: 0,
    inativos: 0,
    funcionarios: 0,
    admins: 0
  };


  private apiUrl = environment.apiUrl;

  constructor(
    private http: HttpClient,
    private router: Router,
    private niveisAcessoService: NiveisAcessoService,
    private usuariosService: UsuariosService
  ) {
    addIcons({arrowBack,refresh,people,checkmarkCircle,closeCircle,shield,briefcase,location,trash,add,close,addCircle,personAdd,create,search,eye,mail,call,business,time});
  }

  ngOnInit() {
    this.carregarNiveisAcesso();
    this.carregarUsuarios();
    this.configurarValidacaoUsuario();
  }

  configurarValidacaoUsuario() {
    this.verificarUsuarioSubject
      .pipe(
        debounceTime(500), // Aguarda 500ms após parar de digitar
        distinctUntilChanged(), // Só executa se o valor mudou
        switchMap(nome => {
          if (!nome || nome.trim().length < 2) {
            this.validacaoUsuario.verificando = false;
            this.validacaoUsuario.valido = true;
            this.validacaoUsuario.mensagem = '';
            return [];
          }
          this.validacaoUsuario.verificando = true;
          return this.usuariosService.verificarUsuario(nome, this.usuarioEditando?.id);
        })
      )
      .subscribe({
        next: (response) => {
          this.validacaoUsuario.verificando = false;
          if (response) {
            this.validacaoUsuario.existe = response.existe;
            this.validacaoUsuario.mensagem = response.message;
            this.validacaoUsuario.valido = !response.existe;
          }
        },
        error: (error) => {
          this.validacaoUsuario.verificando = false;
          this.validacaoUsuario.valido = false;
          this.validacaoUsuario.mensagem = 'Erro ao verificar usuário';
          console.error('Erro na validação:', error);
        }
      });
  }

  async carregarNiveisAcesso() {
    try {
      const response = await this.niveisAcessoService.listarNiveis().toPromise();
      if (response?.success) {
        this.niveisAcesso = response.data || [];
      }
    } catch (error) {
      console.error('Erro ao carregar níveis de acesso:', error);
    }
  }

  async carregarUsuarios() {
    this.loading = true;
    try {
      const endpoint = this.statusFiltro === 'todos'
        ? `${this.apiUrl}/usuarios.php`
        : `${this.apiUrl}/usuarios.php?status=${this.statusFiltro}`;

      const response = await this.http.get<any>(endpoint).toPromise();
      if (response?.success) {
        // Processar dados para incluir funcionário se existir
        this.usuarios = (response.data || []).map((usuario: any) => {
          if (usuario.funcionario_id_fk && usuario.funcionario_nome) {
            usuario.funcionario = {
              id: usuario.funcionario_id_fk,
              nome_completo: usuario.funcionario_nome,
              cargo: usuario.funcionario_cargo,
              departamento: usuario.funcionario_departamento,
              status: usuario.funcionario_status
            };
          } else {
            // Se não há funcionário associado, limpar dados do funcionário
            usuario.funcionario = null;
          }
          return usuario;
        });

        this.filtrarUsuarios();
        this.calcularContadores();
      }
    } catch (error) {
      console.error('Erro ao carregar usuários:', error);
    } finally {
      this.loading = false;
    }
  }


  calcularContadores() {
    this.contadores.total = this.usuarios.length;
    this.contadores.ativos = this.usuarios.filter(u => u.ativo).length;
    this.contadores.inativos = this.usuarios.filter(u => !u.ativo).length;

    // Corrigido para contar funcionários conforme os tipos permitidos
    this.contadores.funcionarios = this.usuarios.filter(u =>
      u.nivel_acesso === 'gerente' ||
      u.nivel_acesso === 'vendedor' ||
      u.nivel_acesso === 'dev'
    ).length;

    this.contadores.admins = this.usuarios.filter(u => u.nivel_acesso === 'admin').length;
  }

  filtrarUsuarios() {
    let filtrados = this.usuarios;

    if (this.termoPesquisa.trim()) {
      const termo = this.termoPesquisa.toLowerCase();
      filtrados = filtrados.filter(u =>
        u.nome.toLowerCase().includes(termo) ||
        u.email.toLowerCase().includes(termo) ||
        u.nivel_acesso.toLowerCase().includes(termo)
      );
    }

    this.usuariosFiltrados = filtrados;
  }

  mudarFiltro(event: any) {
    this.statusFiltro = event.detail.value;
    this.carregarUsuarios();
  }

  pesquisar() {
    this.filtrarUsuarios();
  }

  // Validação em tempo real do nome do usuário
  onNomeChange(nome: string) {
    if (nome !== undefined && nome !== null) {
      this.verificarUsuarioSubject.next(nome);
    }
  }

  // Limpar validação
  limparValidacaoUsuario() {
    this.validacaoUsuario = {
      verificando: false,
      existe: false,
      mensagem: '',
      valido: true
    };
  }

  async doRefresh(event: any) {
    await this.carregarUsuarios();
    event.target.complete();
  }

  abrirModalCriar() {
    this.modoEdicao = false;
    this.usuarioEditando = null;
    this.funcionarioSelecionado = null;
    this.limparValidacaoUsuario();
    this.formData = {
      usuario: '',
      senha: '',
      nivel_acesso: '',
      ativo: true,
      funcionario_id: null as number | null
    };
    this.modalAberto = true;
  }

  abrirModalEditar(usuario: Usuario) {
    console.log('🔍 Abrindo modal de edição para usuário:', usuario);
    console.log('👤 Funcionário associado:', usuario.funcionario);

    this.modoEdicao = true;
    this.usuarioEditando = usuario;
    this.funcionarioSelecionado = usuario.funcionario || null;
    this.limparValidacaoUsuario();

    console.log('✅ Funcionário selecionado definido como:', this.funcionarioSelecionado);

    this.formData = {
      usuario: usuario.usuario || usuario.nome,
      senha: '', // Não preencher senha na edição
      nivel_acesso: usuario.nivel_acesso,
      ativo: usuario.ativo,
      funcionario_id: usuario.funcionario_id || null as number | null
    };
    this.modalAberto = true;
  }

  fecharModal() {
    this.modalAberto = false;
    this.usuarioEditando = null;
  }

  // Modal de funcionários
  abrirModalFuncionarios() {
    this.modalFuncionariosAberto = true;
    this.carregarFuncionariosDisponiveis();
  }

  fecharModalFuncionarios() {
    this.modalFuncionariosAberto = false;
    this.funcionarioSelecionado = null;
    this.termoPesquisaFuncionario = '';
  }

  async carregarFuncionariosDisponiveis() {
    try {
      const response = await this.http.get<any>(`${this.apiUrl}/funcionarios.php`).toPromise();
      if (response?.success) {
        // Filtrar apenas funcionários que não estão associados a nenhum usuário
        // Verificar se o funcionário não está sendo usado por nenhum usuário
        const funcionariosDisponiveis = [];

        for (const funcionario of response.data) {
          // Verificar se este funcionário não está associado a nenhum usuário
          const usuarioComFuncionario = this.usuarios.find(u => u.funcionario_id === funcionario.id);
          if (!usuarioComFuncionario) {
            funcionariosDisponiveis.push(funcionario);
          }
        }

        this.funcionariosDisponiveis = funcionariosDisponiveis;
      }
    } catch (error) {
      console.error('Erro ao carregar funcionários:', error);
    }
  }

  pesquisarFuncionarios() {
    if (!this.termoPesquisaFuncionario.trim()) {
      this.carregarFuncionariosDisponiveis();
      return;
    }

    const termo = this.termoPesquisaFuncionario.toLowerCase();
    this.funcionariosDisponiveis = this.funcionariosDisponiveis.filter(f =>
      f.nome_completo.toLowerCase().includes(termo) ||
      f.cargo.toLowerCase().includes(termo) ||
      f.departamento?.toLowerCase().includes(termo)
    );
  }

  selecionarFuncionario(funcionario: any) {
    console.log('🔍 Funcionário selecionado:', funcionario);
    console.log('📋 Dados completos do funcionário:', {
      id: funcionario.id,
      nome_completo: funcionario.nome_completo,
      cargo: funcionario.cargo,
      departamento: funcionario.departamento,
      email: funcionario.email,
      status: funcionario.status,
      endereco: funcionario.endereco,
      numero_endereco: funcionario.numero_endereco,
      cidade: funcionario.cidade,
      estado: funcionario.estado
    });

    this.funcionarioSelecionado = funcionario;
    this.formData.funcionario_id = funcionario.id;

    // Se estiver criando um novo usuário, usar dados do funcionário
    if (!this.modoEdicao) {
      // Sugerir o nome do funcionário como usuario
      if (!this.formData.usuario || this.formData.usuario === '') {
        this.formData.usuario = funcionario.nome_completo;
      }
    }

    console.log('✅ Funcionário associado ao usuário. funcionario_id:', this.formData.funcionario_id);
    this.fecharModalFuncionarios();
  }

  removerFuncionarioSelecionado() {
    this.funcionarioSelecionado = null;
    this.formData.funcionario_id = null;
  }

  async salvarUsuario() {
    if (!this.formData.usuario.trim() || !this.formData.nivel_acesso) {
      return;
    }

    this.loading = true;
    try {
      if (this.modoEdicao && this.usuarioEditando) {
        // Edição: atualizar usuário
        const dadosUsuario: any = {
          usuario: this.formData.usuario,
          nivel_acesso: this.formData.nivel_acesso,
          ativo: this.formData.ativo,
          funcionario_id: this.formData.funcionario_id
        };

        // Remover senha vazia na edição
        if (this.formData.senha && this.formData.senha.trim()) {
          dadosUsuario.senha = this.formData.senha;
        }

        const response = await this.http.put<any>(`${this.apiUrl}/usuarios.php?id=${this.usuarioEditando.id}`, dadosUsuario).toPromise();

        if (response?.success) {
          await this.carregarUsuarios();
          this.fecharModal();
        }
      } else {
        // Criação: criar usuário
        const dadosUsuario = {
          usuario: this.formData.usuario,
          senha: this.formData.senha,
          nivel_acesso: this.formData.nivel_acesso,
          ativo: this.formData.ativo,
          funcionario_id: this.formData.funcionario_id
        };

        const response = await this.http.post<any>(`${this.apiUrl}/usuarios.php`, dadosUsuario).toPromise();

        if (response?.success) {
          await this.carregarUsuarios();
          this.fecharModal();
        }
      }
    } catch (error) {
      console.error('Erro ao salvar usuário:', error);
    } finally {
      this.loading = false;
    }
  }


  async excluirUsuario(usuario: Usuario) {
    try {
      const response = await this.http.delete<any>(`${this.apiUrl}/usuarios.php?id=${usuario.id}`).toPromise();
      if (response?.success) {
        await this.carregarUsuarios();
      }
    } catch (error) {
      console.error('Erro ao excluir usuário:', error);
    }
  }

  async toggleStatusUsuario(usuario: Usuario) {
    try {
      const response = await this.http.put<any>(`${this.apiUrl}/usuarios.php?id=${usuario.id}`, { ativo: !usuario.ativo }).toPromise();
      if (response?.success) {
        await this.carregarUsuarios();
      }
    } catch (error) {
      console.error('Erro ao alterar status do usuário:', error);
    }
  }

  ligarPara(telefone: string) {
    window.open(`tel:${telefone}`, '_system');
  }

  enviarEmail(email: string) {
    window.open(`mailto:${email}`, '_system');
  }

  getNivelColor(nivel: string): string {
    switch (nivel) {
      case 'admin': return 'danger';
      case 'funcionario': return 'primary';
      case 'cliente': return 'success';
      default: return 'medium';
    }
  }

  getNivelLabel(nivel: string): string {
    switch (nivel) {
      case 'admin': return 'Administrador';
      case 'funcionario': return 'Funcionário';
      case 'cliente': return 'Cliente';
      default: return nivel;
    }
  }

  getStatusColor(ativo: boolean): string {
    return ativo ? 'success' : 'danger';
  }

  getStatusLabel(ativo: boolean): string {
    return ativo ? 'Ativo' : 'Inativo';
  }

  voltarPainel() {
    this.router.navigate(['/painel-orcamento']);
  }

  irParaFuncionarios() {
    this.router.navigate(['/admin/gestao-funcionarios']);
  }

}
