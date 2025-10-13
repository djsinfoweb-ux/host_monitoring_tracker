# 📊 Zabbix Host Monitoring Tracker

![Zabbix](https://img.shields.io/badge/Zabbix-6.0%2B-red)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)
![License](https://img.shields.io/badge/License-MIT-green)

Módulo para Zabbix que permite acompanhar e gerenciar limites de hosts contratados por empresa/grupo.

## 🎯 Funcionalidades

- ✅ **Visualização de Status** - Acompanhe em tempo real quantos hosts cada empresa está monitorando
- ✅ **Comparação com Limites** - Compare automaticamente com o limite contratado
- ✅ **Alertas Visuais** - Status colorido (verde/vermelho) indica se está dentro ou acima do limite
- ✅ **Gerenciamento Fácil** - Interface web para configurar limites por grupo
- ✅ **Exportação PDF** - Gere relatórios em PDF para apresentações
- ✅ **Persistência de Dados** - Configurações salvas em arquivo JSON

## 📸 Screenshots

### Tela Principal
![Tela Principal](docs/images/main-view.png)

### Gerenciar Limites
![Gerenciar Limites](docs/images/edit-limits.png)

### Relatório PDF
![PDF](docs/images/pdf-report.png)

## 🚀 Instalação Rápida

### Pré-requisitos

- Zabbix 6.0+ ou 7.0+
- PHP 7.4+ com php-fpm
- Nginx ou Apache
- Acesso SSH ao servidor frontend

### Instalação

```bash
# 1. Clonar repositório
cd /usr/share/zabbix/modules
sudo git clone https://github.com/seu-usuario/zabbix-host-tracker.git host_monitoring_tracker

# 2. Ajustar permissões
sudo chown -R nginx:nginx host_monitoring_tracker
sudo chmod -R 755 host_monitoring_tracker
sudo chmod 666 host_monitoring_tracker/limits.json

# 3. Reiniciar serviços
sudo systemctl restart nginx php-fpm

# 4. Ativar no Zabbix
# Administração → Geral → Módulos → Scan directory → Enable
```

Para instalação detalhada, consulte [INSTALL.md](INSTALL.md)

## 📖 Como Usar

### 1. Acessar o Módulo

No menu do Zabbix: **Monitoring → Acompanhamento de Host**

### 2. Configurar Limites

1. Clique em **"Gerenciar Limites"**
2. Defina o limite contratado para cada grupo/empresa
3. Clique em **"Salvar Configurações"**

### 3. Visualizar Status

- **Verde (OK)**: Dentro do limite
- **Vermelho (Acima do Limite)**: Excedeu o contratado

### 4. Gerar PDF

Clique em **"Gerar PDF"** para exportar relatório completo

## ⚙️ Configuração

### Método 1: Via Interface (Recomendado)

Use o botão "Gerenciar Limites" na interface do módulo.

### Método 2: Via Macros do Zabbix

Para cada grupo de hosts:

1. **Configuração → Grupos de hosts**
2. Selecione o grupo
3. Aba **Macros**
4. Adicione: `{$HOST_LIMIT}` = `100`

### Método 3: Editar JSON Manualmente

```bash
sudo nano /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
```

```json
{
    "2": "100",
    "5": "50",
    "7": "200"
}
```

## 🏗️ Estrutura do Projeto

```
host_monitoring_tracker/
├── manifest.json                 # Manifesto do módulo
├── Module.php                    # Classe principal
├── limits.json                   # Configurações de limites
├── actions/
│   ├── HostTrackerView.php      # Visualização principal
│   ├── HostTrackerEdit.php      # Gerenciamento de limites
│   └── HostTrackerPdf.php       # Geração de PDF
├── views/
│   ├── module.hosttracker.view.php  # Interface principal
│   └── module.hosttracker.edit.php  # Interface de edição
└── assets/
    ├── css/
    │   └── hosttracker.css      # Estilos personalizados
    └── js/
        └── hosttracker.js       # Scripts JavaScript
```

## 🔧 Compatibilidade

| Zabbix Version | Status | Notas |
|----------------|--------|-------|
| 7.0 | ✅ Testado | Totalmente compatível |
| 6.4 LTS | ✅ Testado | Totalmente compatível |
| 6.0 LTS | ✅ Testado | Totalmente compatível |
| 5.x | ⚠️ Parcial | Requer ajustes |
| 4.x | ❌ Não suportado | Sistema de módulos diferente |

## 🐛 Solução de Problemas

### Módulo não aparece na lista

```bash
# Verificar permissões
ls -la /usr/share/zabbix/modules/host_monitoring_tracker/

# Verificar logs
sudo tail -50 /var/log/nginx/error.log
```

### Erro ao salvar configurações

```bash
# Verificar permissões do limits.json
sudo chmod 666 /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
sudo chown nginx:nginx /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
```

### Página em branco

```bash
# Verificar sintaxe PHP
find /usr -name php -type f 2>/dev/null | head -n 1 | xargs -I {} {} -l actions/HostTrackerView.php
```

Para mais problemas, consulte [TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md)

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor:

1. Faça um Fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

## 📝 Changelog

### [1.0.0] - 2025-10-12

#### Adicionado
- Visualização de hosts monitorados por grupo
- Comparação com limites contratados
- Interface de gerenciamento de limites
- Exportação para PDF
- Status visual (verde/vermelho)
- Persistência em arquivo JSON

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 👤 Autor

**Seu Nome**
- GitHub: [https://github.com/djsinfoweb-ux)]
- Email: djsinfoweb.com.br

## 🙏 Agradecimentos

- Comunidade Zabbix
- Contribuidores do projeto
- [Zabbix SIA](https://www.zabbix.com/)

## 📞 Suporte

- 📖 [Documentação Completa](docs/)
- 🐛 [Reportar Bug](https://github.com/seu-usuario/zabbix-host-tracker/issues)
- 💬 [Discussões](https://github.com/seu-usuario/zabbix-host-tracker/discussions)

## ⭐ Star History

Se este projeto foi útil, considere dar uma estrela!

[![Star History Chart](https://api.star-history.com/svg?repos=seu-usuario/zabbix-host-tracker&type=Date)](https://star-history.com/#seu-usuario/zabbix-host-tracker&Date)

---

**Desenvolvido com ❤️ para a comunidade Zabbix**
