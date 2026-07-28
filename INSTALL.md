# 🚀 Instruções de Instalação - Host Monitoring Tracker

## Pré-requisitos

- Zabbix 6.0 ou superior
- Acesso SSH ao servidor Zabbix
- Permissões de administrador do sistema (sudo)
- Usuário com privilégios de Super Admin no Zabbix

## Passo a Passo

### 1. Upload do Módulo

Copie a pasta do módulo para o diretório de módulos do Zabbix:

```bash
sudo cp -r host_monitoring_tracker /usr/share/zabbix/modules/
```

### 2. Configuração de Permissões

Configure as permissões corretas para o módulo funcionar:

```bash
# Definir proprietário correto
sudo chown -R www-data:www-data /usr/share/zabbix/modules/host_monitoring_tracker

# Dar permissão de escrita ao arquivo de limites
sudo chmod 666 /usr/share/zabbix/modules/host_monitoring_tracker/limits.json

# Verificar as permissões
ls -la /usr/share/zabbix/modules/host_monitoring_tracker/
```

**Importante**: O arquivo `limits.json` precisa ter permissão de escrita para que o módulo possa salvar as configurações.

### 3. Ativar o Módulo no Zabbix

1. Faça login no Zabbix como **Super Admin**
2. Navegue até: **Administration → General → Modules**
3. Clique no botão **Scan directory**
4. Localize o módulo **Host Monitoring Tracker** na lista
5. Clique em **Enable** (Ativar)
6. Aguarde a mensagem de confirmação

### 4. Verificar Instalação

1. Atualize a página do Zabbix (F5)
2. Vá para o menu **Monitoring**
3. Você deve ver a opção **Acompanhamento de Host**
4. Clique nela para acessar o módulo

## 🔧 Configuração Inicial

### Definir Limites de Hosts

Após a instalação, configure os limites para cada grupo:

1. Acesse **Monitoring → Acompanhamento de Host**
2. Clique em **Gerenciar Limites** (somente Admin/Super Admin)
3. Para cada grupo/empresa, defina o número máximo de hosts
4. Clique em **Salvar Configurações**

**Limite Padrão**: Se não configurado, o limite padrão é **100 hosts** por grupo.

## 🎨 Configuração do Tema

O módulo detecta automaticamente o tema do usuário. Para mudar o tema:

1. Clique no seu nome de usuário (canto superior direito)
2. Selecione **User settings**
3. Na aba **Profile**, escolha o **Theme**:
   - **Blue theme** (padrão - claro)
   - **Dark theme** (escuro)
4. Clique em **Update**

## ⚠️ Resolução de Problemas Comuns

### Problema: "Module not found" ou "Scan directory" não encontra o módulo

**Solução**:
```bash
# Verificar se o módulo está no local correto
ls -la /usr/share/zabbix/modules/host_monitoring_tracker/

# Verificar o arquivo manifest.json
cat /usr/share/zabbix/modules/host_monitoring_tracker/manifest.json
```

### Problema: "Erro ao salvar limites"

**Solução**:
```bash
# Verificar permissões do arquivo limits.json
ls -la /usr/share/zabbix/modules/host_monitoring_tracker/limits.json

# Corrigir permissões se necessário
sudo chmod 666 /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
sudo chown www-data:www-data /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
```

### Problema: Botão "Gerenciar Limites" não aparece

**Possíveis causas**:
1. Usuário não tem privilégios de Admin ou Super Admin
2. Módulo não foi atualizado corretamente

**Solução**:
```bash
# Reinstalar o módulo
sudo rm -rf /usr/share/zabbix/modules/host_monitoring_tracker
sudo cp -r host_monitoring_tracker /usr/share/zabbix/modules/
sudo chown -R www-data:www-data /usr/share/zabbix/modules/host_monitoring_tracker
sudo chmod 666 /usr/share/zabbix/modules/host_monitoring_tracker/limits.json

# No Zabbix, desabilite e reabilite o módulo
```

### Problema: CSS não carrega ou tema não funciona

**Solução**:
```bash
# Limpar cache do Zabbix
sudo systemctl restart zabbix-server
sudo systemctl restart apache2  # ou nginx

# No navegador, limpe o cache (Ctrl + Shift + R)
```

## 📊 Teste de Funcionamento

Após a instalação, faça estes testes:

1. **Teste de Visualização**:
   - Acesse o módulo
   - Verifique se todos os grupos aparecem
   - Confirme que os números de hosts estão corretos

2. **Teste de Edição** (Admin/Super Admin):
   - Clique em "Gerenciar Limites"
   - Altere um limite
   - Salve e volte para a visualização
   - Confirme que o status foi atualizado

3. **Teste de Tema**:
   - Mude para o tema Dark
   - Verifique se as cores se adaptaram
   - Volte para o tema Blue
   - Confirme que está funcionando

4. **Teste de Permissões**:
   - Faça login com usuário comum
   - Verifique que o botão "Gerenciar Limites" NÃO aparece
   - Confirme que ainda pode visualizar o dashboard

## 🔄 Atualização

Para atualizar o módulo para uma nova versão:

```bash
# 1. Faça backup do arquivo de limites
sudo cp /usr/share/zabbix/modules/host_monitoring_tracker/limits.json ~/limits_backup.json

# 2. Remova a versão antiga
sudo rm -rf /usr/share/zabbix/modules/host_monitoring_tracker

# 3. Copie a nova versão
sudo cp -r host_monitoring_tracker /usr/share/zabbix/modules/

# 4. Restaure o arquivo de limites
sudo cp ~/limits_backup.json /usr/share/zabbix/modules/host_monitoring_tracker/limits.json

# 5. Configure permissões
sudo chown -R www-data:www-data /usr/share/zabbix/modules/host_monitoring_tracker
sudo chmod 666 /usr/share/zabbix/modules/host_monitoring_tracker/limits.json

# 6. Reinicie o Zabbix
sudo systemctl restart zabbix-server
```

## 📞 Suporte

Se encontrar problemas não listados aqui:

1. Verifique os logs do Zabbix: `/var/log/zabbix/zabbix_server.log`
2. Verifique os logs do Apache/Nginx
3. Entre em contato com o administrador do sistema

## ✅ Checklist de Instalação

- [ ] Módulo copiado para `/usr/share/zabbix/modules/`
- [ ] Permissões configuradas corretamente
- [ ] Módulo habilitado no Zabbix
- [ ] Menu "Acompanhamento de Host" aparece em Monitoring
- [ ] Visualização funciona corretamente
- [ ] Limites podem ser editados (Admin/Super Admin)
- [ ] Usuários comuns NÃO veem o botão "Gerenciar Limites"
- [ ] Temas Blue e Dark funcionam corretamente
- [ ] Exportação PDF funciona

---

**Instalação completa!** 🎉
