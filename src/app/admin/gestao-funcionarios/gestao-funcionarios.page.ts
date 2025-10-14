import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { AlertController } from '@ionic/angular';
import { environment } from '../../../environments/environment';
import { addIcons } from 'ionicons';
import {
  arrowBack, personAdd, create, trash, search, eye, checkmarkCircle, closeCircle,
  people, shield, mail, call, business, time, addCircle, refresh, close, calendar,
  location, card, briefcase, cash, document, camera, add, person
} from 'ionicons/icons';
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
  IonButtons,
  IonInput,
  IonTextarea,
  IonSelect,
  IonSelectOption,
  IonFab,
  IonFabButton,
  IonSpinner,
  IonRefresher,
  IonRefresherContent,
  IonGrid,
  IonRow,
  IonCol,
  IonDatetime,
  IonDatetimeButton,
  IonPopover,
  IonModal,
  IonAlert
} from '@ionic/angular/standalone';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

interface Funcionario {
  id: number;
  usuario_id: number | null;
  nome_completo: string;
  email: string;
  cpf?: string;
  rg?: string;
  data_nascimento?: string;
  telefone?: string;
  celular?: string;
  endereco?: string;
  numero_endereco?: string;
  complemento?: string;
  cidade?: string;
  estado?: string;
  cep?: string;
  cargo: string;
  departamento?: string;
  data_admissao: string;
  data_demissao?: string;
  salario?: number;
  status: string;
  observacoes?: string;
  foto?: string;
  created_at: string;
  updated_at: string;
  nivel_acesso: string;
  usuario_ativo: boolean;
}

interface Usuario {
  id: number;
  nome: string;
  email: string;
  nivel_acesso: string;
  ativo: boolean;
  created_at: string;
}

@Component({
  selector: 'app-gestao-funcionarios',
  templateUrl: './gestao-funcionarios.page.html',
  styleUrls: ['./gestao-funcionarios.page.scss'],
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
  IonSearchbar,
  IonButtons,
    IonInput,
    IonTextarea,
    IonSelect,
    IonSelectOption,
    IonFab,
    IonFabButton,
    IonSpinner,
    IonRefresher,
    IonRefresherContent,
    IonGrid,
    IonRow,
    IonCol,
    IonModal,
    CommonModule,
    FormsModule
  ]
})
export class GestaoFuncionariosPage implements OnInit {
  funcionarios: Funcionario[] = [];
  funcionariosFiltrados: Funcionario[] = [];
  usuariosDisponiveis: Usuario[] = [];
  statusFiltro: string = 'todos';
  termoPesquisa: string = '';
  loading = false;
  buscandoCep = false;
  fotoPreview: string | null = null;
  arquivoFoto: File | null = null;

  // Modal e alertas
  isModalAcoesOpen = false;
  funcionarioSelecionado: Funcionario | null = null;

  // Sistema de páginas
  paginaAtual: 'listagem' | 'cadastro' = 'listagem';
  modoEdicao: boolean = false;
  funcionarioEditando: Funcionario | null = null;

  // Formulário
  formData = {
    usuario_id: '',
    nome_completo: '',
    email: '',
    cpf: '',
    rg: '',
    data_nascimento: '',
    telefone: '',
    celular: '',
    endereco: '',
    numero_endereco: '',
    complemento: '',
    cidade: '',
    estado: '',
    cep: '',
    cargo: '',
    departamento: '',
    data_admissao: '',
    data_demissao: '',
    salario: '',
    status: 'ativo',
    observacoes: '',
    foto: ''
  };

  // Contadores
  contadores = {
    total: 0,
    ativos: 0,
    inativos: 0,
    afastados: 0,
    demitidos: 0
  };

  // Opções
  cargos = [
    'Gerente', 'Supervisor', 'Vendedor', 'Técnico', 'Assistente',
    'Analista', 'Coordenador', 'Diretor', 'Consultor', 'Auxiliar'
  ];

  departamentos = [
    'Administrativo', 'Vendas', 'Técnico', 'Financeiro', 'RH',
    'Marketing', 'Operações', 'Suporte', 'Comercial', 'Produção'
  ];

  estados = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
    'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
    'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
  ];

  private apiUrl = environment.apiUrl;

  constructor(
    private http: HttpClient,
    private router: Router,
    private alertController: AlertController
  ) {
    addIcons({arrowBack,refresh,people,checkmarkCircle,time,closeCircle,add,camera,close,person,create,trash,personAdd,search,eye,shield,mail,call,business,addCircle,calendar,location,card,briefcase,cash,document});
  }

  ngOnInit() {
    this.carregarFuncionarios();
    this.carregarUsuariosDisponiveis();
  }

  async carregarFuncionarios() {
    this.loading = true;
    try {
      const endpoint = this.statusFiltro === 'todos'
        ? `${this.apiUrl}/funcionarios.php`
        : `${this.apiUrl}/funcionarios.php?status=${this.statusFiltro}`;

      console.log('🔍 Carregando funcionários de:', endpoint);
      const response = await this.http.get<any>(endpoint).toPromise();
      console.log('📡 Resposta da API funcionários:', response);

      if (response?.success) {
        this.funcionarios = response.data || [];
        console.log('✅ Funcionários carregados:', this.funcionarios);
        this.filtrarFuncionarios();
        this.calcularContadores();
      } else {
        console.warn('⚠️ Resposta da API não foi bem-sucedida:', response);
        this.funcionarios = [];
      }
    } catch (error) {
      console.error('❌ Erro ao carregar funcionários:', error);
      this.funcionarios = [];
    } finally {
      this.loading = false;
    }
  }

  async carregarUsuariosDisponiveis() {
    try {
      const response = await this.http.get<any>(`${this.apiUrl}/usuarios_sem_funcionario.php`).toPromise();
      if (response?.success) {
        this.usuariosDisponiveis = response.data || [];
      }
    } catch (error) {
      console.error('Erro ao carregar usuários disponíveis:', error);
    }
  }

  calcularContadores() {
    this.contadores.total = this.funcionarios.length;
    this.contadores.ativos = this.funcionarios.filter(f => f.status === 'ativo').length;
    this.contadores.inativos = this.funcionarios.filter(f => f.status === 'inativo').length;
    this.contadores.afastados = this.funcionarios.filter(f => f.status === 'afastado').length;
    this.contadores.demitidos = this.funcionarios.filter(f => f.status === 'demitido').length;
  }

  filtrarFuncionarios() {
    let filtrados = this.funcionarios;

    if (this.termoPesquisa.trim()) {
      const termo = this.termoPesquisa.toLowerCase();
      filtrados = filtrados.filter(f =>
        f.nome_completo.toLowerCase().includes(termo) ||
        f.cpf?.includes(termo) ||
        f.cargo.toLowerCase().includes(termo) ||
        f.departamento?.toLowerCase().includes(termo) ||
        f.email.toLowerCase().includes(termo)
      );
    }

    this.funcionariosFiltrados = filtrados;
  }

  mudarFiltro(event: any) {
    this.statusFiltro = event.detail.value;
    this.carregarFuncionarios();
  }

  pesquisar() {
    this.filtrarFuncionarios();
  }

  async doRefresh(event: any) {
    await this.carregarFuncionarios();
    await this.carregarUsuariosDisponiveis();
    event.target.complete();
  }

  abrirPaginaCadastro() {
    this.modoEdicao = false;
    this.funcionarioEditando = null;
    this.paginaAtual = 'cadastro';
    this.formData = {
      usuario_id: '',
      nome_completo: '',
      email: '',
      cpf: '',
      rg: '',
      data_nascimento: '',
      telefone: '',
      celular: '',
      endereco: '',
      numero_endereco: '',
      complemento: '',
      cidade: '',
      estado: '',
      cep: '',
      cargo: '',
      departamento: '',
      data_admissao: '',
      data_demissao: '',
      salario: '',
      status: 'ativo',
      observacoes: '',
      foto: ''
    };
  }

  abrirPaginaEditar(funcionario: Funcionario) {
    this.modoEdicao = true;
    this.funcionarioEditando = funcionario;
    this.paginaAtual = 'cadastro';
    this.formData = {
      usuario_id: funcionario.usuario_id ? funcionario.usuario_id.toString() : '',
      nome_completo: funcionario.nome_completo,
      email: funcionario.email || '',
      cpf: funcionario.cpf || '',
      rg: funcionario.rg || '',
      data_nascimento: funcionario.data_nascimento || '',
      telefone: funcionario.telefone || '',
      celular: funcionario.celular || '',
      endereco: funcionario.endereco || '',
      numero_endereco: funcionario.numero_endereco || '',
      complemento: funcionario.complemento || '',
      cidade: funcionario.cidade || '',
      estado: funcionario.estado || '',
      cep: funcionario.cep || '',
      cargo: funcionario.cargo,
      departamento: funcionario.departamento || '',
      data_admissao: funcionario.data_admissao,
      data_demissao: funcionario.data_demissao || '',
      salario: funcionario.salario ? this.formatarSalarioParaExibicao(funcionario.salario) : '',
      status: funcionario.status,
      observacoes: funcionario.observacoes || '',
      foto: funcionario.foto || ''
    };

    // Carregar foto existente se houver
    if (funcionario.foto) {
      this.fotoPreview = funcionario.foto;
    } else {
      this.fotoPreview = null;
    }
    this.arquivoFoto = null;
  }

  // Modal de ações
  abrirModalAcoes(funcionario: Funcionario) {
    this.funcionarioSelecionado = funcionario;
    this.isModalAcoesOpen = true;
  }

  fecharModalAcoes() {
    this.isModalAcoesOpen = false;
    this.funcionarioSelecionado = null;
  }

  // Ações do funcionário
  editarFuncionario() {
    if (this.funcionarioSelecionado) {
      const funcionarioParaEditar = { ...this.funcionarioSelecionado }; // Criar uma cópia
      this.fecharModalAcoes();

      // Usar setTimeout para garantir que o modal seja fechado antes de abrir a página de edição
      setTimeout(() => {
        this.abrirPaginaEditar(funcionarioParaEditar);
      }, 100);
    }
  }

  async inativarFuncionario() {
    if (!this.funcionarioSelecionado) return;

    const alert = await this.alertController.create({
      header: 'Confirmar Inativação',
      message: `Tem certeza que deseja inativar o funcionário ${this.funcionarioSelecionado.nome_completo}?`,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel'
        },
        {
          text: 'Inativar',
          handler: () => {
            this.executarInativacao();
          }
        }
      ]
    });

    await alert.present();
  }

  async deletarFuncionario() {
    if (!this.funcionarioSelecionado) return;

    const alert = await this.alertController.create({
      header: 'Confirmar Exclusão',
      message: `Tem certeza que deseja EXCLUIR permanentemente o funcionário ${this.funcionarioSelecionado.nome_completo}? Esta ação não pode ser desfeita.`,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel'
        },
        {
          text: 'Excluir',
          cssClass: 'danger',
          handler: () => {
            this.executarExclusao();
          }
        }
      ]
    });

    await alert.present();
  }

  async executarInativacao() {
    if (!this.funcionarioSelecionado) return;

    try {
      this.loading = true;
      const response = await this.http.put(`${environment.apiUrl}/funcionarios.php?id=${this.funcionarioSelecionado.id}`, {
        ...this.funcionarioSelecionado,
        status: 'inativo'
      }).toPromise();

      if (response && (response as any).success) {
        // Atualizar a lista local
        const index = this.funcionarios.findIndex(f => f.id === this.funcionarioSelecionado!.id);
        if (index !== -1) {
          this.funcionarios[index].status = 'inativo';
          this.aplicarFiltros();
        }

        this.fecharModalAcoes();

        // Mostrar mensagem de sucesso
        const alert = await this.alertController.create({
          header: 'Sucesso',
          message: 'Funcionário inativado com sucesso!',
          buttons: ['OK']
        });
        await alert.present();
      }
    } catch (error) {
      console.error('Erro ao inativar funcionário:', error);

      const alert = await this.alertController.create({
        header: 'Erro',
        message: 'Erro ao inativar funcionário. Tente novamente.',
        buttons: ['OK']
      });
      await alert.present();
    } finally {
      this.loading = false;
    }
  }

  async executarExclusao() {
    if (!this.funcionarioSelecionado) return;

    try {
      this.loading = true;
      const response = await this.http.delete(`${environment.apiUrl}/funcionarios.php?id=${this.funcionarioSelecionado.id}`).toPromise();

      if (response && (response as any).success) {
        // Remover da lista local
        this.funcionarios = this.funcionarios.filter(f => f.id !== this.funcionarioSelecionado!.id);
        this.aplicarFiltros();

        this.fecharModalAcoes();

        // Mostrar mensagem de sucesso
        const alert = await this.alertController.create({
          header: 'Sucesso',
          message: 'Funcionário excluído com sucesso!',
          buttons: ['OK']
        });
        await alert.present();
      }
    } catch (error) {
      console.error('Erro ao excluir funcionário:', error);

      const alert = await this.alertController.create({
        header: 'Erro',
        message: 'Erro ao excluir funcionário. Tente novamente.',
        buttons: ['OK']
      });
      await alert.present();
    } finally {
      this.loading = false;
    }
  }

  voltarParaListagem() {
    this.paginaAtual = 'listagem';
    this.funcionarioEditando = null;
    this.modoEdicao = false;
    this.fotoPreview = null;
    this.arquivoFoto = null;
  }

  async salvarFuncionario() {
    if (!this.formData.nome_completo || !this.formData.email || !this.formData.cargo || !this.formData.data_admissao) {
      alert('Preencha os campos obrigatórios: Nome Completo, E-mail, Cargo e Data de Admissão');
      return;
    }

    // Validar se a data de admissão é válida
    if (this.formData.data_admissao && new Date(this.formData.data_admissao) > new Date()) {
      alert('A data de admissão não pode ser futura');
      return;
    }

    // Validar se a data de demissão é posterior à admissão
    if (this.formData.data_demissao && this.formData.data_admissao) {
      if (new Date(this.formData.data_demissao) <= new Date(this.formData.data_admissao)) {
        alert('A data de demissão deve ser posterior à data de admissão');
        return;
      }
    }

    this.loading = true;
    try {
      // Processar upload de imagem se houver
      if (this.arquivoFoto) {
        this.formData.foto = await this.converterImagemParaBase64(this.arquivoFoto);
      }

      const dadosRequest: any = {
        ...this.formData,
        usuario_id: this.formData.usuario_id ? parseInt(this.formData.usuario_id, 10) : null,
        salario: this.formData.salario ? this.converterSalarioParaNumero(this.formData.salario) : null,
        // Garantir que campos vazios sejam null em vez de string vazia
        cpf: this.formData.cpf && this.formData.cpf.trim() ? this.formData.cpf : null,
        rg: this.formData.rg && this.formData.rg.trim() ? this.formData.rg : null,
        telefone: this.formData.telefone && this.formData.telefone.trim() ? this.formData.telefone : null,
        celular: this.formData.celular && this.formData.celular.trim() ? this.formData.celular : null,
        endereco: this.formData.endereco && this.formData.endereco.trim() ? this.formData.endereco : null,
        numero_endereco: this.formData.numero_endereco && this.formData.numero_endereco.trim() ? this.formData.numero_endereco : null,
        complemento: this.formData.complemento && this.formData.complemento.trim() ? this.formData.complemento : null,
        cidade: this.formData.cidade && this.formData.cidade.trim() ? this.formData.cidade : null,
        estado: this.formData.estado && this.formData.estado.trim() ? this.formData.estado : null,
        cep: this.formData.cep && this.formData.cep.trim() ? this.formData.cep : null,
        departamento: this.formData.departamento && this.formData.departamento.trim() ? this.formData.departamento : null,
        data_demissao: this.formData.data_demissao && this.formData.data_demissao.trim() ? this.formData.data_demissao : null,
        observacoes: this.formData.observacoes && this.formData.observacoes.trim() ? this.formData.observacoes : null,
        foto: this.formData.foto && this.formData.foto.trim() ? this.formData.foto : null
      };

      let response;
      if (this.modoEdicao && this.funcionarioEditando) {
        response = await this.http.put<any>(`${this.apiUrl}/funcionarios.php?id=${this.funcionarioEditando.id}`, dadosRequest).toPromise();
      } else {
        response = await this.http.post<any>(`${this.apiUrl}/funcionarios.php`, dadosRequest).toPromise();
      }

      if (response?.success) {
        await this.carregarFuncionarios();
        await this.carregarUsuariosDisponiveis();
        this.voltarParaListagem();
      }
    } catch (error) {
      console.error('Erro ao salvar funcionário:', error);
    } finally {
      this.loading = false;
    }
  }

  aplicarFiltros() {
    let funcionariosFiltrados = [...this.funcionarios];

    // Filtrar por status
    if (this.statusFiltro !== 'todos') {
      funcionariosFiltrados = funcionariosFiltrados.filter(f => f.status === this.statusFiltro);
    }

    // Filtrar por termo de pesquisa
    if (this.termoPesquisa.trim()) {
      const termo = this.termoPesquisa.toLowerCase();
      funcionariosFiltrados = funcionariosFiltrados.filter(f =>
        f.nome_completo.toLowerCase().includes(termo) ||
        f.cargo.toLowerCase().includes(termo) ||
        f.departamento?.toLowerCase().includes(termo) ||
        f.cpf?.includes(termo) ||
        f.telefone?.includes(termo) ||
        f.celular?.includes(termo)
      );
    }

    this.funcionariosFiltrados = funcionariosFiltrados;
  }

  ligarPara(telefone: string) {
    window.open(`tel:${telefone}`, '_system');
  }

  enviarEmail(email: string) {
    window.open(`mailto:${email}`, '_system');
  }

  getStatusColor(status: string): string {
    switch (status) {
      case 'ativo': return 'success';
      case 'inativo': return 'warning';
      case 'afastado': return 'primary';
      case 'demitido': return 'danger';
      default: return 'medium';
    }
  }

  getStatusLabel(status: string): string {
    switch (status) {
      case 'ativo': return 'Ativo';
      case 'inativo': return 'Inativo';
      case 'afastado': return 'Afastado';
      case 'demitido': return 'Demitido';
      default: return status;
    }
  }

  formatarMoeda(valor: number): string {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(valor);
  }

  formatarCPF(event: any) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length >= 11) {
      value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    } else if (value.length >= 9) {
      value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
    } else if (value.length >= 6) {
      value = value.replace(/(\d{3})(\d{3})/, '$1.$2');
    } else if (value.length >= 3) {
      value = value.replace(/(\d{3})/, '$1');
    }
    this.formData.cpf = value;
  }

  formatarTelefone(event: any) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length >= 10) {
      value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    } else if (value.length >= 6) {
      value = value.replace(/(\d{2})(\d{4})/, '($1) $2');
    } else if (value.length >= 2) {
      value = value.replace(/(\d{2})/, '($1)');
    }
    this.formData.telefone = value;
  }

  formatarCelular(event: any) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length >= 11) {
      value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else if (value.length >= 7) {
      value = value.replace(/(\d{2})(\d{5})/, '($1) $2');
    } else if (value.length >= 2) {
      value = value.replace(/(\d{2})/, '($1)');
    }
    this.formData.celular = value;
  }

  formatarSalario(event: any) {
    let value = event.target.value.replace(/\D/g, '');
    if (value) {
      // Converter para centavos e formatar como moeda
      const valor = parseInt(value) / 100;
      this.formData.salario = valor.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      });
    } else {
      this.formData.salario = '';
    }
  }

  converterSalarioParaNumero(salarioFormatado: string): number {
    // Remove todos os caracteres não numéricos exceto vírgula e ponto
    const valorLimpo = salarioFormatado.replace(/[^\d,]/g, '');

    // Substitui vírgula por ponto para conversão
    const valorNumerico = parseFloat(valorLimpo.replace(',', '.'));

    return isNaN(valorNumerico) ? 0 : valorNumerico;
  }

  formatarSalarioParaExibicao(salario: number): string {
    return salario.toLocaleString('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    });
  }

  voltarPainel() {
    this.router.navigate(['/painel-orcamento']);
  }

  async buscarEnderecoPorCep() {
    const cep = this.formData.cep?.replace(/\D/g, '');

    if (!cep || cep.length !== 8) {
      return;
    }

    this.buscandoCep = true;
    try {
      const response = await this.http.get<any>(`https://viacep.com.br/ws/${cep}/json/`).toPromise();

      if (response && !response.erro) {
        this.formData.endereco = response.logradouro || '';
        this.formData.cidade = response.localidade || '';
        this.formData.estado = response.uf || '';

        // Se não há logradouro, usar o bairro
        if (!response.logradouro && response.bairro) {
          this.formData.endereco = response.bairro;
        }
      } else {
        console.warn('CEP não encontrado');
      }
    } catch (error) {
      console.error('Erro ao buscar CEP:', error);
    } finally {
      this.buscandoCep = false;
    }
  }

  formatarCep(event: any) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length >= 5) {
      value = value.replace(/(\d{5})(\d)/, '$1-$2');
    }
    this.formData.cep = value;

    // Buscar endereço quando CEP estiver completo
    if (value.length === 9) { // 00000-000
      this.buscarEnderecoPorCep();
    }
  }

  onFileSelected(event: any) {
    const file = event.target.files[0];
    if (file) {
      // Validar tipo de arquivo
      if (!file.type.startsWith('image/')) {
        alert('Por favor, selecione apenas arquivos de imagem.');
        return;
      }

      // Validar tamanho (máximo 5MB)
      if (file.size > 5 * 1024 * 1024) {
        alert('A imagem deve ter no máximo 5MB.');
        return;
      }

      this.arquivoFoto = file;

      // Criar preview
      const reader = new FileReader();
      reader.onload = (e: any) => {
        this.fotoPreview = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  }

  removerFoto() {
    this.arquivoFoto = null;
    this.fotoPreview = null;
    this.formData.foto = '';
  }

  async converterImagemParaBase64(file: File): Promise<string> {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result as string);
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  }

  getImagemSrc(foto: string | null | undefined): string | null {
    if (!foto) return null;

    // Se já é uma URL válida (http/https), retorna como está
    if (foto.startsWith('http://') || foto.startsWith('https://')) {
      return foto;
    }

    // Se é base64, retorna como está (já está no formato correto)
    if (foto.startsWith('data:image/')) {
      return foto;
    }

    // Se não é nem URL nem base64, retorna null
    return null;
  }
}
