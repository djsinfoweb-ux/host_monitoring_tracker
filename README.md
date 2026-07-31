# Módulo de Acompanhamento de Host - Zabbix

## Descrição

Módulo frontend para controlar a quantidade de hosts monitorados por grupo/empresa e comparar com o limite contratado.

A partir da versão **1.2.0**, grupos que seguem uma nomenclatura hierárquica passam a ser exibidos em formato pai/filho recolhível. Na versão **1.3.0**, a quantidade exibida no grupo pai passou a ser consolidada automaticamente pela soma dos grupos filhos.

Exemplo:

```text
DJSINFOWEB
├── DJSINFOWEB/APPS
└── DJSINFOWEB/VMs
```

O relacionamento só é criado quando o grupo pai realmente existe no Zabbix. Portanto, um nome contendo `/` não será agrupado incorretamente caso o prefixo não exista como grupo.

## Recursos

- Visualização de hosts monitorados versus limite contratado
- Status **OK** ou **Acima do Limite**
- Hierarquia pai/filho com abertura e fechamento dos subgrupos
- Consolidação automática da quantidade de hosts no grupo pai
- Suporte a vários níveis de hierarquia
- Estado recolhido salvo no navegador
- Gerenciamento de limites também em formato hierárquico
- Relatório para impressão/PDF com identação dos subgrupos
- Compatibilidade visual com temas claro e escuro
- Botão de gerenciamento restrito a Admin e Super Admin
- Leitura otimizada da contagem de hosts, com fallback para instalações incompatíveis
- Gravação atômica do `limits.json`

## Regra para criar a hierarquia

O módulo utiliza o caractere `/` no nome dos grupos.

Para este cenário:

```text
DJSINFOWEB
DJSINFOWEB/APPS
DJSINFOWEB/VMs
```

`DJSINFOWEB` será o pai e os outros dois serão filhos.

Também funciona com mais níveis:

```text
EMPRESA
EMPRESA/APPS
EMPRESA/APPS/PRODUCAO
```

Se existir apenas `EMPRESA/APPS`, mas o grupo `EMPRESA` não existir, o grupo será exibido como principal.

## Regra de contabilização da versão 1.3.0

Quando um grupo possui filhos, a coluna **Atual** do pai deixa de mostrar a contagem direta daquele grupo e passa a mostrar a soma dos subgrupos.

Exemplo:

```text
DJSINFOWEB = 9
├── DJSINFOWEB/APPS = 4
└── DJSINFOWEB/VMs = 5
```

O cálculo é:

```text
4 + 5 = 9
```

A consolidação é recursiva e também funciona com vários níveis. Quando um grupo possui filhos, hosts associados diretamente ao próprio pai não entram no total consolidado; o pai funciona como agrupador da estrutura abaixo dele. Grupos sem filhos continuam exibindo sua contagem direta.

Cada grupo mantém seu próprio limite contratado. Portanto, o status do pai é calculado comparando o total consolidado dos filhos com o limite configurado para o pai.

## Instalação

1. Copie a pasta para o diretório de módulos do frontend Zabbix:

```bash
sudo cp -r host_monitoring_tracker /usr/share/zabbix/modules/
```

Em algumas instalações o caminho pode ser `/usr/share/zabbix/ui/modules/`.

2. Ajuste proprietário e permissões. Em Debian/Ubuntu com Apache:

```bash
sudo chown -R www-data:www-data /usr/share/zabbix/modules/host_monitoring_tracker
sudo chmod 666 /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
```

Em RHEL/AlmaLinux/Oracle Linux, o usuário do frontend costuma ser `apache`:

```bash
sudo chown -R apache:apache /usr/share/zabbix/modules/host_monitoring_tracker
sudo chmod 666 /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
```

3. No Zabbix:

- Acesse **Administration → General → Modules**
- Clique em **Scan directory**
- Habilite **Acompanhamento de Host**
- Abra **Monitoring → Acompanhamento de Host**

## Atualização para a versão 1.3.0

Faça backup dos limites antes de substituir a pasta:

```bash
sudo cp /usr/share/zabbix/modules/host_monitoring_tracker/limits.json /tmp/limits.json.backup
sudo rm -rf /usr/share/zabbix/modules/host_monitoring_tracker
sudo cp -r host_monitoring_tracker /usr/share/zabbix/modules/
sudo cp /tmp/limits.json.backup /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
sudo chmod 666 /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
```

Depois:

- Confirme que o módulo está habilitado
- Atualize o navegador com `Ctrl + F5`
- Se necessário, desabilite e habilite novamente o módulo

## Gerenciamento dos limites

1. Acesse **Monitoring → Acompanhamento de Host**
2. Clique em **Gerenciar Limites**
3. Abra a empresa pai para visualizar os filhos
4. Configure um limite individual para o pai e para cada subgrupo
5. Clique em **Salvar Configurações**

Os limites permanecem individuais. A quantidade monitorada do grupo pai é calculada automaticamente pela soma dos filhos, mas o limite contratado do pai continua sendo configurado separadamente.

## Formato do limits.json

```json
{
  "44": 100,
  "45": 100,
  "46": 100
}
```

A chave é o `groupid` do Zabbix e o valor é o limite contratado.

## Estrutura

```text
host_monitoring_tracker/
├── Module.php
├── manifest.json
├── limits.json
├── includes/
│   └── HostTrackerDataService.php
├── actions/
│   ├── HostTrackerView.php
│   ├── HostTrackerEdit.php
│   └── HostTrackerPdf.php
├── views/
│   ├── module.hosttracker.view.php
│   └── module.hosttracker.edit.php
└── assets/
    ├── css/hosttracker.css
    └── js/hosttracker.js
```

## Solução de problemas

### A seta não aparece

- Confirme que o grupo pai existe exatamente como grupo no Zabbix
- Use `/` para separar os níveis
- Atualize o navegador com `Ctrl + F5`

### O filho não ficou abaixo do pai

Exemplo correto:

```text
DJSINFOWEB
DJSINFOWEB/APPS
```

O módulo ignora espaços ao redor da barra, portanto `WEB / EQUIPE DIGITAL` pode ser associado ao grupo `WEB`.

### Erro ao salvar limites

```bash
ls -l /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
sudo chmod 666 /usr/share/zabbix/modules/host_monitoring_tracker/limits.json
```

Também verifique se o diretório do módulo permite que o usuário do frontend crie o arquivo temporário usado na gravação atômica.

## Versões

- **1.3.0**: grupo pai contabiliza automaticamente a soma dos hosts dos subgrupos
- **1.2.0**: hierarquia pai/filho, recolhimento, PDF hierárquico, otimização de consultas e melhorias de persistência
- **1.1.0**: temas e controle de acesso
- **1.0.0**: versão inicial
