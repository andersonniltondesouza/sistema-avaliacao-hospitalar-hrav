-- Banco do Sistema de Avaliação Hospitalar (PostgreSQL)
-- Uso: createdb hospital && psql -d hospital -f banco.SQL

-- pgcrypto gera hashes bcrypt compatíveis com password_verify() do PHP
CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE setor (
    id   SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

CREATE TABLE dispositivos (
    id       SERIAL PRIMARY KEY,
    nome     VARCHAR(100) NOT NULL,
    status   VARCHAR(10) DEFAULT 'ATIVO' NOT NULL,
    id_setor INT NOT NULL REFERENCES setor(id)
);

CREATE TABLE perguntas (
    id       SERIAL PRIMARY KEY,
    pergunta TEXT NOT NULL,
    status   VARCHAR(10) DEFAULT 'ATIVA' NOT NULL
);

CREATE TABLE respostas (
    id             SERIAL PRIMARY KEY,
    id_pergunta    INT NOT NULL REFERENCES perguntas(id),
    data_hora      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observacoes    TEXT,
    nota           NUMERIC(3, 1) NOT NULL CHECK (nota BETWEEN 0 AND 10),
    id_dispositivo INT REFERENCES dispositivos(id),
    id_setor       INT REFERENCES setor(id)
);

CREATE TABLE tb_usuario (
    id      SERIAL PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    senha   VARCHAR(255) NOT NULL, -- hash bcrypt, nunca a senha em texto
    perfil  VARCHAR(10) DEFAULT 'TOTEM' NOT NULL CHECK (perfil IN ('ADMIN', 'TOTEM'))
);

-- Usuários iniciais. TROQUE AS SENHAS antes de usar fora do ambiente local:
-- UPDATE tb_usuario SET senha = crypt('nova-senha', gen_salt('bf')) WHERE usuario = 'admin';
INSERT INTO tb_usuario (usuario, senha, perfil) VALUES
    ('admin', crypt('admin', gen_salt('bf')), 'ADMIN'),
    ('totem', crypt('totem', gen_salt('bf')), 'TOTEM');
