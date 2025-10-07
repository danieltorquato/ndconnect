# Configuração do Servidor para N.D Connect

## 🚀 **Arquivos de Configuração Criados:**

### 1. **`.htaccess` (Apache/Linux)**
- Configuração principal para roteamento Angular SPA
- Redireciona todas as requisições para `index.html`
- Configurações de cache e compressão
- Configurações de segurança

### 2. **`api/.htaccess` (API PHP)**
- Configuração específica para a pasta da API
- CORS habilitado para desenvolvimento
- Proteção de arquivos sensíveis
- Roteamento para `index.php`

### 3. **`web.config` (IIS/Windows Server)**
- Configuração para servidores Windows
- Mesma funcionalidade do `.htaccess`
- Configurações específicas do IIS

### 4. **`index.html` (Raiz do Projeto)**
- Arquivo principal da aplicação Angular
- Copiado da pasta `src/` para a raiz

## 🔧 **Como Usar:**

### **Para Desenvolvimento Local:**
```bash
# Usar o servidor de desenvolvimento do Angular
ng serve

# Ou usar o servidor PHP (se necessário)
cd api && php -S localhost:8000
```

### **Para Produção (Apache/Linux):**
1. Fazer upload de todos os arquivos para o servidor
2. O `.htaccess` será aplicado automaticamente
3. Acessar via navegador

### **Para Produção (IIS/Windows):**
1. Fazer upload de todos os arquivos para o servidor
2. O `web.config` será aplicado automaticamente
3. Acessar via navegador

## 📁 **Estrutura de Arquivos Necessária:**

```
projeto/
├── .htaccess              # Configuração Apache
├── web.config             # Configuração IIS
├── index.html             # Arquivo principal
├── assets/                # Assets da aplicação
├── www/                   # Build do Angular
│   ├── index.html
│   ├── main.js
│   ├── polyfills.js
│   └── styles.css
└── api/                   # API PHP
    ├── .htaccess          # Configuração da API
    ├── index.php
    ├── auth.php
    └── ...
```

## ⚙️ **Configurações Aplicadas:**

### **Roteamento Angular:**
- Todas as rotas (`/login`, `/painel`, etc.) redirecionam para `index.html`
- Angular gerencia o roteamento no frontend

### **API PHP:**
- Rotas da API (`/api/*`) são processadas diretamente
- CORS habilitado para desenvolvimento
- Proteção de arquivos sensíveis

### **Cache e Performance:**
- Cache de 1 ano para assets estáticos
- Compressão GZIP habilitada
- Configurações de MIME types

### **Segurança:**
- Proteção contra clickjacking
- Prevenção de MIME type sniffing
- XSS protection habilitado
- Arquivos sensíveis protegidos

## 🐛 **Resolução de Problemas:**

### **Erro 404:**
- Verificar se o `.htaccess` está na raiz
- Verificar se o `index.html` está na raiz
- Verificar permissões do servidor

### **API não funciona:**
- Verificar se o `api/.htaccess` está na pasta da API
- Verificar se o `api/index.php` existe
- Verificar configurações de CORS

### **Assets não carregam:**
- Verificar se a pasta `www/` existe
- Verificar se o build do Angular foi feito
- Verificar configurações de MIME types

## 🔄 **Comandos Úteis:**

```bash
# Build para produção
ng build --configuration=production

# Build para desenvolvimento
ng build --configuration=development

# Servir localmente
ng serve

# Testar API localmente
cd api && php -S localhost:8000
```

## 📝 **Notas Importantes:**

1. **Desenvolvimento**: Use `ng serve` para desenvolvimento local
2. **Produção**: Faça o build e faça upload dos arquivos
3. **API**: A API deve estar acessível via `/api/`
4. **Rotas**: Todas as rotas Angular funcionam com o `.htaccess`
5. **CORS**: Configurado para desenvolvimento, ajustar para produção
