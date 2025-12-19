# Projeto Final ASA 2024.1

Este repositório contém um **painel de controle de hospedagem web** desenvolvido como projeto acadêmico inicial no curso de Redes de Computadores. O objetivo era criar um sistema automatizado capaz de gerenciar domínios, usuários FTP e configurações de DNS/Apache em um ambiente Linux.

> **⚠️ AVISO DE CONTEXTO:** Este projeto foi desenvolvido para um ambiente controlado e isolado (**LXC Container** com acesso via **VPN**) e com recursos limitados na época (**PHP 5.2** / CentOS).
>
> **O código contém vulnerabilidades de segurança intencionais ou por limitações do cenário de aprendizado** e NÃO deve ser utilizado em produção. Ele serve apenas como "Legacy Code" para demonstrar a lógica de automação de sistemas via web e a evolução de conceitos.

##  Funcionalidades

* **Automação de Infraestrutura:** Criação automática de VirtualHosts (Apache) e Zonas de DNS (Bind9).
* **Gestão de Domínios:** Adicionar e remover domínios via interface web.
* **Integração PHP/Shell:** O PHP interage com o sistema operacional para criar pastas e definir permissões.
* **Wrapper C (SUID):** Utilização de um binário em C para elevar privilégios (root) e executar scripts de manutenção.
* **Gestão de Usuários:** Níveis de acesso para Admin Geral e Admin de Domínio.

##  Arquitetura do Sistema

O fluxo de funcionamento do projeto segue a lógica "Full Stack" raiz (Sistema Operacional + Web):

1.  **Interface Web (PHP):** Recebe o comando do usuário (ex: criar domínio).
2.  **Banco de Dados (MySQL):** Registra as informações do domínio e usuários.
3.  **Wrapper SUID (`exeroot`):** Um executável compilado em C (`src/exeroot.c`) que possui a flag `setuid(0)`. O PHP chama este arquivo para conseguir permissão de root.
4.  **Shell Script (`sb.sc`):** O wrapper chama este script bash, que executa os comandos pesados de sistema (mkdir, chown, restart services, reload bind).

##  Análise Post-Mortem e Refatoração (O que faria diferente hoje?)

Como este é um projeto legado, existem várias práticas que seriam inaceitáveis em um ambiente moderno. Abaixo listo as principais vulnerabilidades e como elas seriam corrigidas atualmente:

### 1. Segurança (Crítico)
* **Vulnerabilidade:** O uso de um binário C com SUID (`exeroot`) passando argumentos diretamente para um shell script é extremamente perigoso (risco de Injeção de Comandos). O PHP rodando no Apache nunca deve ter acesso direto ao root.
    * *Solução Moderna:* Utilizar filas de mensagens (**RabbitMQ**, **Redis**) ou Jobs. O PHP postaria uma mensagem ("criar site X") na fila, e um "Worker" isolado no servidor (rodando com privilégios controlados) consumiria essa mensagem e executaria a tarefa.
* **Vulnerabilidade:** Permissões `chmod 777` utilizadas no script `sb.sc`.
    * *Solução Moderna:* Definir corretamente o owner/group (ex: `chown www-data:client-group`) e usar permissões restritas (755 para pastas, 644 para arquivos).
* **Vulnerabilidade:** Credenciais de banco de dados "hardcoded" no código (`db_connect.php`).
    * *Solução Moderna:* Uso de variáveis de ambiente (`.env`) não versionadas.

### 2. Infraestrutura como Código
* **Legado:** Manipulação direta de arquivos `.conf` e reinicialização de serviços via script bash (`sed`, `echo`).
    * *Solução Moderna:* Uso de ferramentas de Orquestração como **Ansible** ou containers **Docker** para isolar cada cliente/site em seu próprio ambiente, evitando que um cliente derrube o servidor Apache inteiro.

### 3. Banco de Dados
* **Legado:** Uso de driver `mysqli` procedural e estrutura de tabelas redundante.
    * *Solução Moderna:* Uso de **PDO** (PHP Data Objects) para abstração e segurança, além de normalização do esquema do banco de dados.

##  Estrutura de Arquivos

* `src/admin.php`: Painel principal do administrador.
* `src/exeroot.c`: Código fonte do wrapper de elevação de privilégio.
* `src/sb.sc`: Script Bash que realiza a configuração de arquivos `.conf` e `.zone`.
* `src/includes/`: Conexões de banco e funções de autenticação.
* `database/schema.sql`: Estrutura do banco de dados recuperada.

##  Como (teoricamente) rodar este projeto

**Requisitos:**
* Linux (CentOS/RHEL com estrutura Apache antiga ou Debian adaptado).
* Apache 2.4 & Bind9 configurados nos caminhos do script.
* MySQL/MariaDB.

**Compilação do Wrapper:**
```bash
gcc -o src/exeroot src/exeroot.c
# Passos perigosos (apenas didático):
chown root:root src/exeroot
chmod 4755 src/exeroot  # Bit SUID
