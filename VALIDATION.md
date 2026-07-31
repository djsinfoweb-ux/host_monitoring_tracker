# Validação da versão 1.3.0

Validações executadas antes da entrega:

- Sintaxe PHP em todos os arquivos do módulo: OK
- Sintaxe JavaScript: OK
- Validação de `manifest.json` e `limits.json`: OK
- Teste automatizado do cenário `DJSINFOWEB/APPS = 4` + `DJSINFOWEB/VMs = 5`: pai calculado como 9
- Teste automatizado de consolidação recursiva com três níveis: OK
- Teste de preservação da contagem direta no campo interno `direct_current`: OK
- Teste de recálculo do status do pai usando o valor consolidado: OK
- Verificação de que grupos folha mantêm sua contagem direta: OK

Comando de teste incluído no pacote:

```bash
php tests/test_hierarchy.php
```

## Limitação do teste

O pacote não foi executado dentro de uma instalação Zabbix real neste ambiente. Após instalar, valide o carregamento do módulo, a API, o tema e a contagem dos grupos no seu frontend.
