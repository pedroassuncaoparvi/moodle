# Moodle EAD Parvi

Plataforma de ensino a distância da **Parvi**, baseada no [Moodle 5.0.1](https://moodle.org), containerizada com Docker e com plugins e integrações customizadas.

---

## Stack

| Componente | Tecnologia |
|---|---|
| Linguagem | PHP 8.2 |
| Servidor | Apache (mod_rewrite) |
| Banco de dados | MariaDB 10.11 |
| Armazenamento de arquivos | ObjectFS (S3 / MinIO) |
| Containerização | Docker + Docker Compose |

---

## Estrutura do projeto

```
moodle-ead/
├── public/               # Código-fonte do Moodle (document root)
│   ├── theme/moove/      # Tema Moove (externo, instalado via install-plugins.sh)
│   ├── mod/hvp/          # Plugin HVP/H5P
│   ├── mod/simplecertificate/
│   ├── admin/tool/objectfs/
│   ├── local/aws/        # SDK AWS (dependência do ObjectFS)
│   └── enrol/coursecompleted/
├── local/
│   ├── middleware_trigger/  # Plugin customizado — gatilho de conclusão de curso
│   └── kopere_dashboard/    # Dashboard administrativo
├── Dockerfile
├── docker-compose.yml
├── apache-moodle.conf
├── config.php            # Configuração principal do Moodle
├── .env                  # Variáveis de ambiente (não commitar valores reais)
└── install-plugins.sh    # Script para clonar plugins externos
```

---

## Plugins

### Externos (instalados via `install-plugins.sh`)

| Plugin | Descrição |
|---|---|
| `theme/moove` | Tema visual responsivo |
| `mod/hvp` | Conteúdo interativo H5P |
| `mod/simplecertificate` | Emissão de certificados |
| `admin/tool/objectfs` | Armazenamento de arquivos em S3/MinIO |
| `local/aws` | SDK AWS (dependência do ObjectFS) |
| `enrol/coursecompleted` | Matrícula automática ao concluir outro curso |

### Customizados (neste repositório)

#### `local/middleware_trigger`

Escuta o evento `\core\event\course_completed` e dispara um webhook POST para o middleware Node.js, enviando `userid` e `courseid`. Usado para acionar automações externas (ex.: emissão de certificados, notificações, integração com CRM).

Configuração via `config.php` (lido do `.env`):
- `$CFG->middleware_url` → URL do endpoint webhook
- `$CFG->middleware_token` → Token Bearer para autenticação

#### `local/kopere_dashboard`

Dashboard administrativo com relatórios e visão gerencial da plataforma.

---

## Configuração de ambiente

Crie ou edite o arquivo `.env` na raiz do projeto:

```env
MIDDLEWARE_URL=http://host.docker.internal:4040/webhook/moodle-completion
MIDDLEWARE_BEARER_TOKEN=seu_token_aqui
```

> O `config.php` lê essas variáveis com `getenv()` e as expõe em `$CFG->middleware_url` e `$CFG->middleware_token`.

---

## Setup inicial

### 1. Instalar plugins externos

```bash
bash install-plugins.sh
```

Clona os plugins para dentro de `public/` (theme, mod, admin/tool, enrol, local).

### 2. Subir os containers

```bash
docker-compose up -d --build
```

Serviços iniciados:
- `moodle-web` — Apache + PHP 8.2 na porta **8080**
- `moodle-db` — MariaDB 10.11 na porta **3306**

### 3. Instalar o banco de dados

Aguarde ~10 segundos para o MariaDB inicializar, depois execute:

```bash
docker exec -it moodle-web php /var/www/html/admin/cli/install_database.php \
  --adminpass=Admin@123 \
  --adminemail=admin@parvi.com.br \
  --fullname='EAD Parvi' \
  --shortname='EAD Parvi' \
  --agree-license
```

### 4. Acessar a plataforma

Abra [http://localhost:8080](http://localhost:8080) no navegador.

---

## Comandos úteis

```bash
# Ver logs do servidor web
docker logs -f moodle-web

# Rodar CLI do Moodle
docker exec -it moodle-web php /var/www/html/admin/cli/cron.php

# Recriar containers do zero
docker-compose down -v && docker-compose up -d --build
```

---

## 🗄️ Banco de Dados e Inicialização (Seed)

O projeto usa um arquivo de inicialização estática em `db-init/seed.sql` (~13MB) para que o ambiente local suba já configurado, sem precisar reconfigurar o Moodle do zero a cada novo `docker compose up`.

O arquivo armazena:
* **Identidade Visual:** Ativação e configurações customizadas do tema **Moove** (cores institucionais, CSS e comportamento de blocos).
* **Campos Customizados:** Estrutura de perfil necessária para a integração com o middleware (ex: campo `cargo`).

> O seed **não contém dados sensíveis** — usuários, cursos, matrículas e logs são expurgados antes de ser versionado (veja os comandos abaixo).

### 🔄 Como gerar um novo Seed Higienizado
Caso você faça alterações estruturais na identidade visual ou configurações do Moodle e precise atualizar o `seed.sql` para o time, utilize o fluxo seguro abaixo direto no terminal:

```bash
# 1. Remove cursos e estruturas de aula fictícias
docker exec -i moodle-db mysql -u root -p"root" moodle -e "DELETE FROM mdl_course WHERE id > 1; TRUNCATE TABLE mdl_course_sections; TRUNCATE TABLE mdl_course_modules; TRUNCATE TABLE mdl_course_modules_completion;"

# 2. Remove usuários de teste (preserva apenas Guest e Admin)
docker exec -i moodle-db mysql -u root -p"root" moodle -e "DELETE FROM mdl_user WHERE id > 2; TRUNCATE TABLE mdl_user_enrolments;"

# 3. Exporta o dump sanitizado aplicando permissão de escrita
docker exec -i moodle-db mysqldump -u root -p"root" moodle | sudo tee moodle-ead/db-init/seed.sql > /dev/null

# 4. Ajusta a propriedade do arquivo para o seu usuário do Linux (evita travas no Git)
sudo chown $(whoami):$(whoami) moodle-ead/db-init/seed.sql
```

---

## Licença

Este projeto é uma customização do [Moodle](https://moodle.org), distribuído sob a [GNU GPL v3](https://moodledev.io/general/license). As customizações Parvi seguem a mesma licença.