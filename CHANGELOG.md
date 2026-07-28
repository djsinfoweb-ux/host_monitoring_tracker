# 📝 Changelog - Host Monitoring Tracker

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

---

## [1.2.0] - 2026-07-28

### Adicionado

- Exibição hierárquica de grupos pai/filho usando `/` no nome
- Botão para abrir e fechar subgrupos
- Suporte a múltiplos níveis de hierarquia
- Persistência do estado recolhido no navegador
- Hierarquia na tela de gerenciamento de limites
- Identação hierárquica no relatório para impressão/PDF
- Serviço centralizado `HostTrackerDataService`

### Melhorado

- Contagem de hosts em consulta consolidada, com fallback por grupo
- Leitura do `limits.json` realizada uma única vez por requisição
- Gravação atômica dos limites para evitar JSON parcial
- Resumo separado entre grupos principais, subgrupos e total
- Escopo do CSS limitado ao módulo
- Acessibilidade do controle de expansão com botão e `aria-expanded`

### Corrigido

- Versão do `manifest.json` alinhada com a versão distribuída
- Tratamento de exceções do relatório PDF
- Classes de status agora correspondem ao CSS e aos tooltips

---

## [1.1.0] - 2025-01-07

### ✨ Adicionado

#### 🎨 Suporte a Temas
- **Tema Blue (Padrão)**: Mantido o visual original com cores claras
- **Tema Dark**: Novo suporte completo ao tema escuro do Zabbix
  - Cores adaptadas para melhor contraste em fundo escuro
  - Status OK: Verde escuro (#1b5e20) com texto claro (#a5d6a7)
  - Status Acima do Limite: Vermelho escuro (#b71c1c) com texto claro (#ef9a9a)
  - Background do container de info: Cinza escuro (#2b2b2b)
  - Hover nas tabelas: Cinza (#3a3a3a)

#### 🔐 Controle de Acesso por Nível de Usuário
- **Restrição do botão "Gerenciar Limites"**:
  - Agora visível APENAS para Admin e Super Admin
  - Usuários comuns podem visualizar o dashboard mas não alterar limites
- **Verificação de Permissões**:
  - Implementada em `HostTrackerView.php`
  - Implementada em `HostTrackerEdit.php`
  - Proteção tanto no front-end quanto no back-end

#### 📚 Documentação
- Criado `README.md` completo com toda a documentação do módulo
- Criado `INSTALL.md` com instruções detalhadas de instalação
- Criado `CHANGELOG.md` (este arquivo) para rastrear mudanças

### 🔄 Modificado

#### Arquivos Alterados

**1. `/actions/HostTrackerView.php`**
- Adicionado import de `CWebUser`
- Modificado método `doAction()` para verificar tipo de usuário
- Adicionada variável `canManageLimits` no array de dados

**2. `/actions/HostTrackerEdit.php`**
- Adicionado imports de `CWebUser` e `CRoleHelper`
- Modificado método `checkPermissions()` para verificar nível de usuário
- Agora bloqueia acesso direto via URL para usuários sem permissão

**3. `/views/module.hosttracker.view.php`**
- Modificada criação de botões para ser condicional
- Botão "Gerenciar Limites" só é criado se `$data['canManageLimits']` for true
- Botão "Gerar PDF" continua disponível para todos

**4. `/views/module.hosttracker.edit.php`**
- Atualizado div de ajuda para usar classe CSS dinâmica
- Removido estilo inline de background para usar classe `info-container`

**5. `/assets/css/hosttracker.css`**
- **Reestruturação completa para suportar múltiplos temas**
- Estilos agora usam seletores condicionais baseados no atributo `data-theme`
- Padrão: `body:not([data-theme="dark"])` para tema Blue
- Dark: `body[data-theme="dark"]` e `body[data-theme="hc-dark"]` para temas escuros
- Todas as classes principais foram duplicadas com versões para cada tema:
  - `.status-ok`
  - `.status-overlimit`
  - `.atual-overlimit`
  - `.info-container`
  - `.info-text`
  - `table.list-table tbody tr:hover`

### 🛡️ Segurança

- Implementada verificação de tipo de usuário em múltiplas camadas
- Prevenção de acesso direto a URLs administrativas
- Validação tanto no controller quanto na view

### 🎯 Detalhes Técnicos

#### Verificação de Usuário
```php
$userType = CWebUser::getType();
$isAdmin = ($userType == USER_TYPE_ZABBIX_ADMIN || $userType == USER_TYPE_SUPER_ADMIN);
```

#### Níveis de Usuário no Zabbix
- `USER_TYPE_ZABBIX_USER = 1` - Usuário comum (sem acesso à edição)
- `USER_TYPE_ZABBIX_ADMIN = 2` - Administrador (com acesso à edição)
- `USER_TYPE_SUPER_ADMIN = 3` - Super Administrador (com acesso à edição)

#### Detecção de Tema
O CSS usa o atributo `data-theme` do elemento `<body>` para detectar o tema:
- Ausência de atributo ou valor diferente de "dark" = Tema Blue
- `data-theme="dark"` = Tema Dark
- `data-theme="hc-dark"` = Tema Dark de Alto Contraste

---

## [1.0.0] - Data da Versão Original

### ✨ Recursos Iniciais

- Dashboard de visualização de hosts por grupo
- Contagem de hosts monitorados vs limite contratado
- Sistema de status (OK / Acima do Limite)
- Edição de limites para cada grupo
- Exportação de relatórios em PDF
- Suporte ao tema Blue do Zabbix
- Persistência de configurações em arquivo JSON

---

## 🔮 Planejamento Futuro

### Versão 1.3.0 (Planejada)
- [ ] Gráficos de evolução de hosts ao longo do tempo
- [ ] Alertas por email quando limites forem ultrapassados
- [ ] Dashboard com estatísticas agregadas
- [ ] Histórico de mudanças de limites
- [ ] Exportação em múltiplos formatos (Excel, CSV)

### Versão 2.0.0 (Planejada)
- [ ] API REST para integração com outros sistemas
- [ ] Workflow de aprovação para mudanças de limites
- [ ] Sistema de notificações no Zabbix
- [ ] Previsão de crescimento baseada em histórico
- [ ] Multi-tenant com isolamento de dados

---

## 📊 Resumo de Mudanças

| Versão | Data       | Arquivos Modificados | Novos Arquivos | Linhas Adicionadas | Linhas Removidas |
|--------|------------|---------------------|----------------|-------------------|------------------|
| 1.2.0  | 2026-07-28 | 8                   | 2              | -                 | -                |
| 1.1.0  | 2025-01-07 | 5                   | 3              | ~200              | ~50              |
| 1.0.0  | -          | -                   | 12             | ~800              | 0                |

---

**Legenda**:
- ✨ Adicionado: Novas funcionalidades
- 🔄 Modificado: Mudanças em funcionalidades existentes
- 🛡️ Segurança: Melhorias de segurança
- 🐛 Corrigido: Correção de bugs
- 🗑️ Removido: Funcionalidades removidas
- 📚 Documentação: Mudanças apenas em documentação
