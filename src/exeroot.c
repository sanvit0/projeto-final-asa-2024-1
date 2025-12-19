#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <unistd.h>

int main(int argc, char *argv[]) {
    if (argc < 3) {
        printf("Uso: %s {add|remove} dominio\n", argv[0]);
        return 1;
    }

    // Define o UID como root
    setuid(0);
    // Constrói o comando para executar o sb.sc
    char command[256];
    snprintf(command, sizeof(command), "/var/www/projeto/sb.sc %s %s", argv[1], argv[2]);
    // Executa o comando e captura a saída
    FILE *pipe = popen(command, "r");
    if (!pipe) {
        return 1;
    }

    char buffer[128];
    while (fgets(buffer, sizeof(buffer), pipe) != NULL) {
        printf("%s", buffer);
        // Envia a saída para o PHP
    }

    // Fecha o pipe e verifica o status de saída
    int status = pclose(pipe);
    if (status != 0) {
        return 1;
    }

    return 0;
}
