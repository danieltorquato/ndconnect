# Sistema de Autenticação e Níveis de Acesso - N.D Connect

## 📋 Visão Geral

Sistema completo de autenticação implementado para o projeto N.D Connect, com níveis de acesso hierárquicos e proteção de rotas.

## 🚀 Funcionalidades

### ✅ Autenticação
- **Login** com email e senha
- **Registro** de novos usuários
- **Logout** seguro
- **Verificação de token** automática
- **Sessões** com expiração (24 horas)

### ✅ Níveis de Acesso
- **Admin**: Acesso total ao sistema
- **Gerente**: Acesso administrativo limitado (sem financeiro)
- **Vendedor**: Acesso básico (leads, orçamentos, painel)
- **Cliente**: Acesso limitado (apenas painel)

### ✅ Proteção de Rotas
- **Guards** automáticos em todas as páginas protegidas
- **Verificação de permissões** por página
- **Redirecionamento** automático para login/unauthorized

## 🗄️ Estrutura do Banco de Dados

### Tabelas Criadas

#### 1. `usuarios`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- nome (VARCHAR(100), NOT NULL)
- email (VARCHAR(100), UNIQUE, NOT NULL)
- senha (VARCHAR(255), NOT NULL) - Hash bcrypt
- nivel_acesso (ENUM: 'admin', 'gerente', 'vendedor', 'cliente')
- ativo (BOOLEAN, DEFAULT TRUE)
- data_criacao (TIMESTAMP)
- data_atualizacao (TIMESTAMP)
```

#### 2. `sessoes`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- usuario_id (INT, FOREIGN KEY)
- token (VARCHAR(255), UNIQUE)
- expira_em (TIMESTAMP)
- ip_address (VARCHAR(45))
- user_agent (TEXT)
- ativo (BOOLEAN, DEFAULT TRUE)
- data_criacao (TIMESTAMP)
```

#### 3. `permissoes_nivel`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- nivel (VARCHAR(20), NOT NULL)
- pagina (VARCHAR(100), NOT NULL)
- pode_acessar (BOOLEAN, DEFAULT TRUE)
- UNIQUE KEY (nivel, pagina)
```

## 📁 Arquivos Criados/Modificados

### Backend (PHP)
- `api/database_auth.sql` - Script de criação das tabelas
- `api/AuthService.php` - Serviço de autenticação
- `api/auth.php` - Endpoints da API
- `api/Routes/api.php` - Rotas atualizadas

### Frontend (Angular)
- `src/app/services/auth.service.ts` - Serviço de autenticação
- `src/app/guards/auth.guard.ts` - Guard de proteção de rotas
- `src/app/login/` - Página de login
- `src/app/register/` - Página de registro
- `src/app/unauthorized/` - Página de acesso negado
- `src/app/shared/navbar/` - Navbar atualizada com menu do usuário
- `src/app/app.routes.ts` - Rotas protegidas

## 🔧 Instalação

### 1. Executar Script do Banco de Dados
```sql
-- Execute o arquivo api/database_auth.sql no seu banco MySQL
-- Isso criará as tabelas e inserirá as permissões padrão
```

### 2. Usuário Admin Padrão
```
Email: admin@ndconnect.com.br
Senha: admin123
Nível: Admin
```

### 3. Configurar CORS (se necessário)
O sistema já está configurado para aceitar requisições do frontend.

## 🎯 Como Usar

### 1. Acesso ao Sistema
- Acesse `/login` para fazer login
- Acesse `/register` para criar nova conta
- Usuários não logados são redirecionados para login

### 2. Níveis de Acesso

#### Admin
- ✅ Todas as páginas administrativas
- ✅ Gestão de leads, orçamentos, clientes, pedidos
- ✅ Financeiro, agenda, relatórios
- ✅ Painel e orçamentos

#### Gerente
- ✅ Gestão de leads, orçamentos, clientes, pedidos
- ❌ Financeiro
- ✅ Agenda e relatórios
- ✅ Painel e orçamentos

#### Vendedor
- ✅ Gestão de leads e orçamentos
- ❌ Gestão de clientes e pedidos
- ❌ Financeiro, agenda, relatórios
- ✅ Painel e orçamentos

#### Cliente
- ❌ Todas as páginas administrativas
- ✅ Apenas painel

### 3. Proteção de Rotas
Todas as rotas administrativas e do painel são automaticamente protegidas:
- `/painel` - Requer login
- `/orcamento` - Requer vendedor ou superior
- `/produtos` - Requer vendedor ou superior
- `/admin/*` - Requer vendedor ou superior

## 🔐 Segurança

### Recursos Implementados
- **Hash de senhas** com bcrypt
- **Tokens de sessão** únicos e com expiração
- **Validação de permissões** no backend e frontend
- **Limpeza automática** de sessões expiradas
- **Proteção CSRF** via tokens
- **Validação de entrada** em todos os endpoints

### Headers de Segurança
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

## 🎨 Interface do Usuário

### Páginas de Autenticação
- **Design moderno** com gradientes da marca
- **Validação em tempo real** dos formulários
- **Mensagens de erro** claras e informativas
- **Responsivo** para mobile e desktop

### Navbar Atualizada
- **Menu do usuário** com dropdown
- **Botões de login/registro** para usuários não logados
- **Navegação condicional** baseada no nível de acesso
- **Indicador visual** do nível do usuário

## 🚨 Tratamento de Erros

### Frontend
- **Redirecionamento automático** para login quando não autenticado
- **Página de acesso negado** para permissões insuficientes
- **Mensagens de erro** amigáveis ao usuário
- **Validação de formulários** em tempo real

### Backend
- **Logs de erro** detalhados
- **Respostas JSON** padronizadas
- **Códigos HTTP** apropriados
- **Limpeza de sessões** expiradas

## 📱 Responsividade

O sistema é totalmente responsivo e funciona em:
- ✅ Desktop
- ✅ Tablet
- ✅ Mobile
- ✅ PWA (Progressive Web App)

## 🔄 Fluxo de Autenticação

1. **Usuário acessa página protegida**
2. **AuthGuard verifica se está logado**
3. **Se não logado → redireciona para /login**
4. **Se logado → verifica permissão da página**
5. **Se sem permissão → redireciona para /unauthorized**
6. **Se com permissão → permite acesso**

## 🎯 Próximos Passos

### Melhorias Futuras
- [ ] **Recuperação de senha** via email
- [ ] **Verificação de email** no registro
- [ ] **Autenticação 2FA** (Two-Factor Authentication)
- [ ] **Logs de auditoria** detalhados
- [ ] **Gestão de usuários** pelo admin
- [ ] **Políticas de senha** configuráveis

### Integrações
- [ ] **SSO** (Single Sign-On)
- [ ] **LDAP/Active Directory**
- [ ] **OAuth** (Google, Facebook, etc.)
- [ ] **API externa** de autenticação

## 📞 Suporte

Para dúvidas ou problemas:
- Verifique os logs do servidor
- Confirme se as tabelas foram criadas corretamente
- Teste com o usuário admin padrão
- Verifique as permissões do banco de dados

---

**Sistema implementado com sucesso! 🎉**

O sistema de autenticação está pronto para uso e todas as páginas estão protegidas conforme os níveis de acesso definidos.
