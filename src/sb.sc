#!/bin/bash

# Log de depuração
LOG_FILE="/opt/rh/httpd24/root/var/www/projeto/sb_sc.log"
echo "Executando sb.sc com argumentos: $@" >> $LOG_FILE

# Verifica se o domínio foi passado
if [ -z "$2" ];
then
    echo "Erro: Domínio não especificado." >> $LOG_FILE
    exit 1
fi

DOMAIN=$2
ZONE_FILE="/opt/rh/httpd24/root/var/www/projeto/configs/bind/$DOMAIN.zone"
APACHE_CONF="/opt/rh/httpd24/root/var/www/projeto/configs/apache/$DOMAIN.conf"
DOMAIN_DIR="/opt/rh/httpd24/root/var/www/projeto/domains/$DOMAIN"
PUBLIC_HTML="$DOMAIN_DIR/public_html"
INDEX_FILE="$PUBLIC_HTML/index.php"
LOGS_DIR="/opt/rh/httpd24/root/var/www/logs"
ACCESS_LOG="$LOGS_DIR/$DOMAIN-access.log"
ERROR_LOG="$LOGS_DIR/$DOMAIN-error.log"
NAMED_CONF_PROJETO="/etc/named.conf.projeto"

# Cria o domínio
if [ "$1" == "add" ];
then
    echo "Adicionando domínio: $DOMAIN" >> $LOG_FILE

    # Cria o diretório do domínio e o public_html
    echo "Criando diretório do domínio: $DOMAIN_DIR" >> $LOG_FILE
    mkdir -p "$PUBLIC_HTML" ||
    { echo "Erro ao criar diretório do domínio." >> $LOG_FILE; exit 1;
    }
    chown -R apache:apache "$DOMAIN_DIR" || { echo "Erro ao alterar permissões do diretório." >> $LOG_FILE;
    exit 1; }
	
    # Define as permissões do diretório public_html
    chmod -R 777 "$PUBLIC_HTML" ||
    { echo "Erro ao definir permissões do diretório." >> $LOG_FILE; exit 1;
    }	

    # Cria um arquivo index.php padrão
    echo "Criando arquivo index.php padrão" >> $LOG_FILE
    echo "<?php
    echo '<!DOCTYPE html>
<html>
<head>
    <title>Bem-vindo ao $DOMAIN</title>
</head>
<body>
    <h1>Bem-vindo ao $DOMAIN!</h1>
    <p>Este é o site padrão para o domínio $DOMAIN.</p>
</body>
</html>';
?>" > "$INDEX_FILE" ||
    { echo "Erro ao criar arquivo index.php." >> $LOG_FILE; exit 1;
    }

    # Cria o arquivo de zona DNS
    echo "Criando arquivo de zona: $ZONE_FILE" >> $LOG_FILE
    echo "\$TTL 86400
@ IN SOA ns1.$DOMAIN. admin.$DOMAIN. (
    $(date +%Y%m%d)01 ; Serial
    3600              ; Refresh
    1800              ; Retry
    1209600           ; Expire
    86400 )  
          ; Minimum TTL

@       IN NS  ns1.$DOMAIN.
@       IN A   192.168.102.122
ns1     IN A   192.168.102.122
www     IN A   192.168.102.122" > "$ZONE_FILE" ||
    { echo "Erro ao criar arquivo de zona." >> $LOG_FILE; exit 1;
    }

    # Adiciona a zona ao arquivo named.conf.projeto
    echo "Adicionando zona ao named.conf.projeto" >> $LOG_FILE
    echo "zone \"$DOMAIN\" {
    type master;
    file \"$ZONE_FILE\";
};"
    >> "$NAMED_CONF_PROJETO" || { echo "Erro ao modificar named.conf.projeto." >> $LOG_FILE; exit 1;
    }

    # Cria o arquivo de configuração do Apache
    echo "Criando arquivo de configuração do Apache: $APACHE_CONF" >> $LOG_FILE
    echo "<VirtualHost 192.168.102.122:80>
    <Directory /opt/rh/httpd24/root/var/www/projeto/domains/$DOMAIN/public_html/>
        AllowOverride all
        Require all granted
        Options -Indexes
    </Directory>
    ServerAdmin root@$DOMAIN
    DocumentRoot \"/opt/rh/httpd24/root/var/www/projeto/domains/$DOMAIN/public_html\"
    ServerName $DOMAIN
    ServerAlias www.$DOMAIN
    ErrorLog $ERROR_LOG
    CustomLog $ACCESS_LOG combined
</VirtualHost>" > 
    "$APACHE_CONF" || { echo "Erro ao criar arquivo do Apache." >> $LOG_FILE; exit 1;
    }

    # Cria os arquivos de log
    touch "$ACCESS_LOG" "$ERROR_LOG"
    chown apache:apache "$ACCESS_LOG" "$ERROR_LOG"

    # Recarrega o BIND e reinicia o Apache
    echo "Recarregando BIND e reiniciando Apache..." >> $LOG_FILE
    rndc reload >> $LOG_FILE 2>&1 ||
    { echo "Erro ao recarregar BIND." >> $LOG_FILE; exit 1;
    }
    /opt/rh/httpd24/root/usr/sbin/apachectl -k graceful >> $LOG_FILE 2>&1 || { echo "Erro ao reiniciar Apache." >> $LOG_FILE;
    exit 1; }

    echo "Domínio adicionado com sucesso: $DOMAIN" >> $LOG_FILE

# Remove o domínio
elif [ "$1" == "remove" ];
then
    echo "Removendo domínio: $DOMAIN" >> $LOG_FILE

    # Remove o arquivo de zona DNS
    echo "Removendo arquivo de zona: $ZONE_FILE" >> $LOG_FILE
    rm -f "$ZONE_FILE" ||
    { echo "Erro ao remover arquivo de zona." >> $LOG_FILE; exit 1;
    }

    # Remove a zona do arquivo named.conf.projeto
    echo "Removendo zona do named.conf.projeto" >> $LOG_FILE
    sed -i "/zone \"$DOMAIN\" {/,/};/d" "$NAMED_CONF_PROJETO" ||
    { echo "Erro ao modificar named.conf.projeto." >> $LOG_FILE; exit 1;
    }

    # Remove o arquivo de configuração do Apache
    echo "Removendo arquivo de configuração do Apache: $APACHE_CONF" >> $LOG_FILE
    rm -f "$APACHE_CONF" ||
    { echo "Erro ao remover arquivo do Apache." >> $LOG_FILE; exit 1;
    }

    # Remove o diretório do domínio
    echo "Removendo diretório do domínio: $DOMAIN_DIR" >> $LOG_FILE
    rm -rf "$DOMAIN_DIR" ||
    { echo "Erro ao remover diretório do domínio." >> $LOG_FILE; exit 1;
    }

    # Remove os arquivos de log
    echo "Removendo arquivos de log: $ACCESS_LOG e $ERROR_LOG" >> $LOG_FILE
    rm -f "$ACCESS_LOG" "$ERROR_LOG"

    # Recarrega o BIND e reinicia o Apache
    echo "Recarregando BIND e reiniciando Apache..." >> $LOG_FILE
    rndc reload >> $LOG_FILE 2>&1 ||
    { echo "Erro ao recarregar BIND." >> $LOG_FILE; exit 1;
    }
    /opt/rh/httpd24/root/usr/sbin/apachectl -k graceful >> $LOG_FILE 2>&1 || { echo "Erro ao reiniciar Apache." >> $LOG_FILE;
    exit 1; }

    echo "Domínio removido com sucesso: $DOMAIN" >> $LOG_FILE

else
    echo "Erro: Comando inválido."
    >> $LOG_FILE
    exit 1
fi

exit 0
