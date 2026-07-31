# Atualização para Host Monitoring Tracker 1.3.0

## Melhoria principal

A quantidade de hosts do grupo pai agora é calculada pela soma de seus grupos filhos.

```text
DJSINFOWEB = 9
├── DJSINFOWEB/APPS = 4
└── DJSINFOWEB/VMs = 5
```

O limite do grupo pai continua independente. No exemplo, se o limite de `DJSINFOWEB` for 8, o pai ficará com status **Acima do Limite**, pois o total consolidado é 9.

## Regra importante

Quando um grupo possui filhos, os hosts cadastrados diretamente no próprio grupo pai não entram no total consolidado. O pai representa a soma da estrutura abaixo dele.

Grupos sem filhos continuam mostrando sua contagem direta.

## Procedimento de atualização

Preserve o arquivo atual de limites:

```bash
sudo cp /usr/share/zabbix/modules/host_monitoring_tracker/limits.json \
  /tmp/limits.json.backup
```

Substitua a versão anterior:

```bash
sudo rm -rf /usr/share/zabbix/modules/host_monitoring_tracker
sudo unzip host_monitoring_tracker_v1.3.0.zip -d /usr/share/zabbix/modules/
```

Restaure os limites:

```bash
sudo cp /tmp/limits.json.backup \
  /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
sudo chmod 666 \
  /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
```

Em instalações nas quais o módulo fica em `/usr/share/zabbix/ui/modules/`, ajuste o caminho nos comandos.

Depois, atualize o navegador com `Ctrl + F5`. Caso necessário, desabilite e habilite novamente o módulo em **Administration → General → Modules**.
