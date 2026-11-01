# Sistema de Avaliação Hospitalar — HRAV

Sistema web para coletar **avaliações espontâneas e anônimas** de pacientes e acompanhantes do **Hospital Regional Alto Vale (HRAV)**. Tablets ou totens instalados nos setores do hospital exibem um questionário com notas de 0 a 10. A equipe administrativa cadastra as perguntas, os setores e os dispositivos e acompanha as respostas num painel de monitoramento.

## Sobre o projeto

Este sistema foi desenvolvido em 2024 como **projeto de extensão do curso de Sistemas de Informação da [Unidavi](https://www.unidavi.edu.br/)** (Centro Universitário para o Desenvolvimento do Alto Vale do Itajaí). A proposta da extensão universitária é aplicar o que se aprende em sala de aula num problema real da comunidade. Aqui, o problema era oferecer ao hospital uma forma simples de ouvir a opinião dos usuários sobre o atendimento, setor por setor.

## Funcionalidades

### Avaliação (dispositivo do setor)
- Seleção do dispositivo que vai coletar as respostas. O setor é identificado automaticamente pelo vínculo do dispositivo.
- Questionário com **uma pergunta por vez**, navegação entre *Anterior* e *Próxima*, e notas de **0 a 10** em botões.
- Campo opcional de **observações** em cada pergunta.
- Validação no navegador e no servidor: nenhuma pergunta pode ficar sem nota, e só são aceitas notas de 0 a 10.
- Tela de agradecimento que volta sozinha ao questionário depois de 5 segundos.
- Nenhum dado pessoal é solicitado: a avaliação é anônima.

### Administração
- **Login** com senhas guardadas em hash bcrypt e redirecionamento por perfil: `ADMIN` vai para o painel; `TOTEM` vai para a seleção de dispositivo.
- **Páginas administrativas protegidas por sessão**: sem login de administrador, o acesso pela URL é redirecionado.
- **Perguntas**: cadastrar, editar, ativar e desativar. Só as perguntas ativas aparecem no questionário.
- **Setores**: cadastrar e listar.
- **Dispositivos**: cadastrar vinculando a um setor, ativar e desativar.
- **Monitoramento**: tabela com todas as respostas, das mais recentes para as mais antigas (data e hora, pergunta, nota, observações, dispositivo e setor), com filtros por dispositivo e por pergunta.

## Fluxo de uso

```
login_page.php
   ├── ADMIN ──► admin_dashboard.php
   │                ├── cadastrar_pergunta.php ──► editar_pergunta.php
   │                ├── cadastrar_setor.php
   │                ├── cadastrar_dispositivo.php
   │                └── monitorar_sistema.php
   └── TOTEM ──► selecionar_dispositivo.php
                    └── index.php (questionário)
                           └── processar_avaliacao.php ──► sucesso.html ──► index.php
```

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Back-end | PHP com PDO e sessões |
| Banco de dados | PostgreSQL, com a extensão pgcrypto para os hashes de senha |
| Front-end | HTML5, CSS3 e JavaScript puro (Fetch API para ativar e desativar registros) |

## Estrutura

```
hospital/
├── config.example.php         # modelo das credenciais do banco (copie para config.php)
├── db_connection.php          # conexão PDO (lê config.php ou variáveis de ambiente)
├── auth.php                   # sessão e controle de acesso
├── banco.SQL                  # criação das tabelas e usuários iniciais
├── login_page.php / logout.php
├── admin_dashboard.php        # menu do administrador
├── cadastrar_pergunta.php     # CRUD de perguntas
├── editar_pergunta.php
├── cadastrar_setor.php        # cadastro de setores
├── cadastrar_dispositivo.php  # cadastro e status de dispositivos
├── monitorar_sistema.php      # relatório de respostas com filtros
├── selecionar_dispositivo.php # vincula a sessão a um dispositivo e setor
├── index.php                  # questionário de avaliação
├── processar_avaliacao.php    # valida e grava as respostas (transação)
├── sucesso.html               # tela de agradecimento
├── css/                       # um arquivo de estilo por tela
└── js/                        # um script por tela
```

## Modelo de dados

| Tabela | Campos principais |
|---|---|
| `setor` | `id`, `nome` |
| `dispositivos` | `id`, `nome`, `status` (ATIVO/INATIVO), `id_setor` → setor |
| `perguntas` | `id`, `pergunta`, `status` (ATIVA/INATIVA) |
| `respostas` | `id`, `id_pergunta` → perguntas, `nota` (0–10), `observacoes`, `data_hora`, `id_dispositivo` → dispositivos, `id_setor` → setor |
| `tb_usuario` | `id`, `usuario`, `senha` (hash bcrypt), `perfil` (ADMIN/TOTEM) |

## Como executar

### Pré-requisitos
- PHP 8 ou superior, com a extensão `pdo_pgsql` habilitada
- PostgreSQL

### 1. Criar o banco

```bash
createdb hospital
psql -d hospital -f hospital/banco.SQL
```

O script cria as tabelas e dois usuários iniciais: `admin`/`admin` (perfil ADMIN) e `totem`/`totem` (perfil TOTEM). **Troque as senhas** antes de usar o sistema fora do ambiente local:

```sql
UPDATE tb_usuario SET senha = crypt('nova-senha', gen_salt('bf')) WHERE usuario = 'admin';
```

### 2. Configurar a conexão

```bash
cp hospital/config.example.php hospital/config.php
```

Preencha host, banco, usuário e senha em `hospital/config.php`. Esse arquivo está no `.gitignore` e **nunca é enviado ao repositório**. Se preferir, defina as variáveis de ambiente `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD`.

### 3. Subir o servidor

```bash
cd hospital
php -S localhost:8000
```

Acesse `http://localhost:8000/login_page.php`. Entre como `admin`, cadastre um setor, um dispositivo e algumas perguntas. Depois entre como `totem` para responder ao questionário.

## Melhorias futuras

- Proteção contra CSRF nos formulários administrativos.
- Gráficos e médias por setor e por pergunta no monitoramento.
- Tela para gerenciar usuários sem precisar de SQL.
