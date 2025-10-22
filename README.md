## Leme Task Management - Instalação Local

Este projeto é um sistema de gestão de tarefas desenvolvido com **Laravel**.

---

## Requisitos Prévios

-   **PHP** \>= 8.2
-   **Composer**
-   **MySQL** ou outro motor de base de dados compatível
-   **Node.js** e **npm** (para gerenciar _assets_ frontend)
-   Servidor web (**Apache**, **Nginx**, ou **Laravel Sail**)

---

## Passos para a Instalação

### 1\. Clonar o repositório

```bash
git clone https://github.com/yesther10/leme-task-management.git
cd leme-task-management
```

### 2\. Instalar dependências PHP com Composer

```bash
composer install
```

### 3\. Copiar arquivo de configuração de ambiente

```bash
cp .env.example .env
```

### 4\. Configurar o arquivo `.env`

Abra o `.env` e configure os dados da base de dados de acordo com seu ambiente local.

### 5\. Gerar a chave da aplicação

```bash
php artisan key:generate
```

### 6\. limpar e Otimizar o Cache

```bash
php artisan optimize
```

### 7\. Executar migrações e _seeders_

```bash
php artisan migrate:fresh --seed
```

### 8\. Use o nome de usuário email:user@correo.com e a senha:password para efetuar login no sistema e ter uma visão geral rápida s\_

### 9\. Crie um link simbólico para a pasta de armazenamento para acessar os arquivos armazenados s\_

```bash
php artisan storage:link
```

### 10\. Executar o servidor local do Laravel

```bash
php artisan serve
```

Geralmente estará disponível em `http://localhost:8000`.

---

### 11\. Para executar testes (se desejado)l

```bash
php artisan test
```

## Acesso

Pode criar um usuário através do registro ou use os dados fornecidos pelos _seeders_ para testar o sistema.

---

## Notas

-   Certifique-se de ter as permissões corretas nas pastas `storage/` e `bootstrap/cache`.
-   Use **Laravel Sail** ou **Docker** se preferir um ambiente isolado.
-   Consulte a documentação oficial do Laravel para mais detalhes.
