CREATE DATABASE IF NOT EXISTS BD022;
USE BD022;

-- Tabela de administradores gerais (admin.php)
CREATE TABLE IF NOT EXISTS administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL
);

-- Tabelas de Domínios (admin.php e domain_functions.php)
CREATE TABLE IF NOT EXISTS domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS dominios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE
);

-- Usuários vinculados a domínios (domain_admin.php)
CREATE TABLE IF NOT EXISTS usuarios_dominio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    dominio_id INT NOT NULL,
    FOREIGN KEY (dominio_id) REFERENCES dominios(id) ON DELETE CASCADE
);

-- Usuários de FTP (ftp_functions.php)
CREATE TABLE IF NOT EXISTS ftpusers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255),
    login VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    uid INT DEFAULT 48,
    gid INT DEFAULT 48,
    dir VARCHAR(255) NOT NULL,
    shell VARCHAR(255) DEFAULT '/sbin/nologin',
    ativo CHAR(1) DEFAULT 's',
    email VARCHAR(255)
);

-- Tabela genérica de usuários (auth.php)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL
);

-- Dados iniciais de exemplo
INSERT INTO administradores (email, senha) VALUES ('admin@admin.com', 'admin');
