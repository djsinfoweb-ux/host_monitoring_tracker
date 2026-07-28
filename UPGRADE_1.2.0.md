# Atualização para 1.2.0

## Objetivo

Exibir grupos do Zabbix como uma árvore recolhível quando houver uma relação de nome pai/filho.

```text
DJSINFOWEB
├── DJSINFOWEB/APPS
└── DJSINFOWEB/VMs
```

## Passos

```bash
# Backup dos limites atuais
cp /usr/share/zabbix/modules/host_monitoring_tracker/limits.json /tmp/limits.json.backup

# Substituição do módulo
rm -rf /usr/share/zabbix/modules/host_monitoring_tracker
cp -r host_monitoring_tracker /usr/share/zabbix/modules/

# Restauração dos limites
cp /tmp/limits.json.backup /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
chmod 666 /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
```

Depois faça `Ctrl + F5` no navegador. Se os assets antigos continuarem em cache, desabilite e habilite novamente o módulo no frontend.

## Observação

O limite do grupo pai e o limite de cada filho continuam independentes. A versão 1.2.0 altera a organização visual, não soma os valores dos filhos no pai.
