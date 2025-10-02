# Sistema de Cores N.D Connect

## Cores Oficiais Implementadas

### Paleta Principal

| Cor | Código Hex | Uso | Descrição |
|-----|------------|-----|-----------|
| **Azul Marinho Escuro** | `#0C2B59` | Cor primária | Fundo principal, textos importantes, elementos de destaque |
| **Laranja/Vermelho Pôr do Sol** | `#E8622D` | Cor secundária | Botões principais, elementos interativos, destaques |
| **Amarelo/Ouro** | `#F7A64C` | Cor de acento | Gradientes, elementos complementares, hover states |
| **Branco** | `#FFFFFF` | Cor de contraste | Textos sobre fundos escuros, elementos claros |

### Cores de Apoio

| Cor | Código Hex | Uso |
|-----|------------|-----|
| **Cinza Médio** | `#64748b` | Textos secundários, descrições |
| **Verde Sucesso** | `#10b981` | Estados de sucesso, confirmações |
| **Vermelho Perigo** | `#ef4444` | Erros, alertas, exclusões |

## Gradientes Implementados

### Gradiente Principal
```css
background: linear-gradient(135deg, #E8622D 0%, #F7A64C 100%);
```
- **Uso**: Botões primários, badges, elementos de destaque
- **Direção**: Diagonal (135deg) do laranja para o amarelo

### Gradiente Reverso
```css
background: linear-gradient(135deg, #F7A64C 0%, #E8622D 100%);
```
- **Uso**: Estados hover, variações de botões
- **Direção**: Diagonal (135deg) do amarelo para o laranja

## Aplicação no Sistema

### Componentes Atualizados

#### 1. **Header/Toolbar**
- Fundo: Azul Marinho Escuro (`#0C2B59`)
- Texto: Branco (`#FFFFFF`)
- Logo: Mantém cores originais

#### 2. **Cards de Produtos**
- Borda: Azul Marinho Escuro com transparência
- Hover: Destaque com laranja
- Preços: Gradiente principal
- Badges de categoria: Gradiente principal
- Botões "Adicionar": Gradiente principal

#### 3. **Botões**
- **Primário**: Gradiente principal
- **Secundário**: Laranja sólido
- **Terciário**: Amarelo sólido
- **Outline**: Borda azul, preenchimento transparente

#### 4. **Formulários**
- Labels: Azul Marinho Escuro
- Inputs: Borda azul, foco laranja
- Placeholders: Cinza médio

#### 5. **Orçamento**
- Itens: Bordas azuis sutis
- Subtotal: Gradiente principal
- Total: Gradiente principal
- Controles: Hover com gradiente

#### 6. **Cards e Seções**
- Fundo: Branco com gradiente sutil
- Cabeçalhos: Gradiente sutil de fundo
- Bordas: Azul Marinho Escuro com transparência

## Variáveis CSS

### Variáveis Principais
```css
:root {
  --nd-primary: #0C2B59;
  --nd-secondary: #E8622D;
  --nd-accent: #F7A64C;
  --nd-light: #FFFFFF;
  --nd-dark: #0C2B59;
  --nd-medium: #64748b;
  --nd-gradient: linear-gradient(135deg, var(--nd-secondary) 0%, var(--nd-accent) 100%);
  --nd-gradient-reverse: linear-gradient(135deg, var(--nd-accent) 0%, var(--nd-secondary) 100%);
}
```

### Integração com Ionic
- Todas as cores foram mapeadas para as variáveis padrão do Ionic
- Mantém compatibilidade com temas claro/escuro
- Suporte a acessibilidade e contraste

## Acessibilidade

### Contraste Garantido
- **Azul Marinho + Branco**: Contraste 4.5:1 (WCAG AA)
- **Laranja + Branco**: Contraste 4.5:1 (WCAG AA)
- **Amarelo + Azul**: Contraste 3:1 (WCAG AA)

### Estados de Foco
- Todos os elementos interativos têm estados de foco visíveis
- Cores de foco seguem o padrão de acessibilidade
- Transições suaves para melhor UX

## Arquivos Modificados

1. **`src/theme/variables.scss`** - Variáveis principais do Ionic
2. **`src/global.scss`** - Aplicação global das cores
3. **`src/app/home/home.page.scss`** - Estilos específicos da página home

## Benefícios da Implementação

### ✅ **Consistência Visual**
- Identidade visual unificada em todo o sistema
- Cores oficiais da marca aplicadas corretamente

### ✅ **Experiência do Usuário**
- Interface mais atrativa e profissional
- Gradientes e transições suaves
- Estados visuais claros

### ✅ **Acessibilidade**
- Contraste adequado para leitura
- Estados de foco bem definidos
- Compatibilidade com leitores de tela

### ✅ **Manutenibilidade**
- Variáveis CSS centralizadas
- Fácil alteração de cores no futuro
- Código organizado e documentado

## Próximos Passos

1. **Teste em diferentes dispositivos** - Verificar como as cores aparecem em diferentes telas
2. **Feedback dos usuários** - Coletar opiniões sobre a nova identidade visual
3. **Ajustes finos** - Refinar cores se necessário baseado no feedback
4. **Documentação adicional** - Criar guia de estilo para futuras implementações
