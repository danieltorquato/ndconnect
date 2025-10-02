# Botão "Orçamento" na Gestão de Leads

## 📋 Resumo da Implementação

Implementei um botão "Orçamento" na página de gestão de leads que redireciona diretamente para criar orçamento com os dados do lead já preenchidos automaticamente.

## 🎯 Funcionalidade

### 1. Botão "Orçamento" na Gestão de Leads
- **Localização**: Cada card de lead na página "Gestão de Leads"
- **Cor**: Warning (laranja) para destacar a ação
- **Ícone**: Document-text
- **Ação**: Redireciona para página de orçamento com dados preenchidos

### 2. Preenchimento Automático
Quando o botão é clicado, os seguintes dados são automaticamente preenchidos:
- ✅ **Nome do cliente**
- ✅ **Email**
- ✅ **Telefone**
- ✅ **Empresa** (se disponível)
- ✅ **Mensagem** (nas observações)

## 🔧 Implementação Técnica

### Frontend - Gestão de Leads (`src/app/admin/gestao-leads/`)

#### HTML (`gestao-leads.page.html`)
```html
<ion-button
  fill="clear"
  size="small"
  color="warning"
  (click)="criarOrcamento(lead)">
  <ion-icon name="document-text" slot="start"></ion-icon>
  Orçamento
</ion-button>
```

#### TypeScript (`gestao-leads.page.ts`)
```typescript
criarOrcamento(lead: any) {
  // Navegar para a página de orçamento com os dados do lead
  this.router.navigate(['/orcamento'], {
    queryParams: {
      leadId: lead.id,
      nome: lead.nome,
      email: lead.email,
      telefone: lead.telefone,
      empresa: lead.empresa || '',
      mensagem: lead.mensagem || ''
    }
  });
}
```

### Frontend - Página de Orçamento (`src/app/orcamento/`)

#### TypeScript (`orcamento.page.ts`)
```typescript
// Interface Cliente atualizada
interface Cliente {
  nome: string;
  email: string;
  telefone: string;
  endereco: string;
  cpf_cnpj: string;
  empresa: string; // ✅ Adicionada
}

// Importações adicionadas
import { Router, ActivatedRoute } from '@angular/router';

// Construtor atualizado
constructor(
  private http: HttpClient,
  @Inject(DOCUMENT) private document: Document,
  private router: Router,
  private route: ActivatedRoute
) { }

// Método para carregar dados do lead
carregarDadosDoLead() {
  this.route.queryParams.subscribe(params => {
    if (params['leadId']) {
      // Preencher dados do lead
      this.cliente.nome = params['nome'] || '';
      this.cliente.email = params['email'] || '';
      this.cliente.telefone = params['telefone'] || '';
      this.cliente.empresa = params['empresa'] || ''; // ✅ Funcionando
      this.observacoes = params['mensagem'] || '';
      
      // Mostrar notificação de dados preenchidos
      this.mostrarNotificacao('Dados do lead carregados automaticamente!', 'success');
    }
  });
}
```

#### HTML (`orcamento.page.html`)
```html
<!-- Campo empresa adicionado no formulário -->
<ion-item>
  <ion-label position="stacked">Empresa</ion-label>
  <ion-input [(ngModel)]="cliente.empresa" placeholder="Nome da empresa"></ion-input>
</ion-item>
```

## 🚀 Como Usar

### 1. Acessar Gestão de Leads
1. Vá para "Admin" → "Gestão de Leads"
2. Visualize a lista de leads

### 2. Criar Orçamento para Lead
1. **Clique no botão "Orçamento"** (laranja) em qualquer lead
2. **Será redirecionado** para a página de orçamento
3. **Dados preenchidos automaticamente**:
   - Nome, email, telefone, empresa
   - Mensagem do lead nas observações
4. **Notificação de sucesso** aparece confirmando o carregamento

### 3. Completar Orçamento
1. Adicione produtos desejados
2. Ajuste quantidades e preços
3. Salve o orçamento
4. O lead será convertido automaticamente quando aprovado

## ✨ Benefícios

- **⚡ Agilidade**: Criação rápida de orçamentos
- **🎯 Precisão**: Dados corretos do lead
- **🔄 Integração**: Fluxo completo lead → orçamento → cliente
- **📱 UX**: Interface intuitiva e responsiva
- **🔔 Feedback**: Notificações de confirmação

## 🎨 Interface

### Botão na Gestão de Leads
- **Cor**: Warning (laranja)
- **Ícone**: Document-text
- **Posição**: Entre "Atualizar" e "Converter"
- **Tamanho**: Small
- **Estilo**: Clear (transparente)

### Notificação na Página de Orçamento
- **Tipo**: Success (verde)
- **Mensagem**: "Dados do lead carregados automaticamente!"
- **Aparece**: Automaticamente ao carregar a página

## 🔄 Fluxo Completo

1. **Lead criado** → Aparece na gestão
2. **Botão "Orçamento"** → Clica para criar orçamento
3. **Dados preenchidos** → Automaticamente na página de orçamento
4. **Orçamento criado** → Com dados corretos do lead
5. **Orçamento aprovado** → Lead vira cliente automaticamente

Agora você pode criar orçamentos rapidamente para qualquer lead com apenas um clique! 🚀
