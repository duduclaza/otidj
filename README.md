# SGQ OTI - DJ

Sistema 100% PHP com roteamento simples, Tailwind via CDN, e conexão MySQL via PDO. Sem cache no navegador.

## Requisitos
- PHP 8.0+
- Composer
- Apache com `mod_rewrite` habilitado

## Instalação
```bash
composer install
```

Copie `.env.example` para `.env` e ajuste variáveis se necessário (já preenchidas para produção conforme informado).

## Estrutura de Pastas
- `public/` ponto de entrada (`index.php`) e `.htaccess`
- `src/` código da aplicação
  - `Core/Router.php` roteador simples
  - `Controllers/` controladores
  - `Config/Database.php` conexão PDO
  - `Support/helpers.php` helpers globais
- `views/` layouts, parciais e páginas
- `vendor/` dependências (após `composer install`)

## Servidor local (opcional)
```bash
php -S 127.0.0.1:8000 -t public
```

## Produção
Aponte o DocumentRoot para a pasta `public/`. O `.htaccess` já direciona as rotas.

## Sem cache
Cabeçalhos HTTP são definidos em `public/index.php` e `public/.htaccess`.
