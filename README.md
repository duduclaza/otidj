# SGQ PRO - Sistema de Gestão da Qualidade

## 🚀 Detecção Automática de Ambiente

Este sistema foi configurado para detectar automaticamente se está rodando em:

- **Produção**: `djbr.sgqoti.com.br` (Hostinger)
- **Desenvolvimento local**: `localhost`, `127.0.0.1`, `::1`

## 📋 Configurações por Ambiente

### Ambiente de Produção (Hostinger)
- **URL**: `https://djbr.sgqoti.com.br`
- **Banco de dados**: `srv1890.hstgr.io` (Hostinger MySQL)
- **Debug**: Desabilitado
- **Sessões**: Configurações de segurança máxima
- **Logs**: Apenas erros críticos

### Ambiente Local (Desenvolvimento)
- **URL**: `http://localhost/sgq-pro`
- **Banco de dados**: `srv1890.hstgr.io` (mesmo banco remoto)
- **Debug**: Habilitado
- **Sessões**: Menos restritivas
- **Logs**: Detalhados

## 🔧 Instalação

1. **Clone o projeto**
2. **Instale as dependências**:
   ```bash
   composer install
   ```
3. **Pronto para usar**:
   - O sistema usa o mesmo banco remoto em ambos os ambientes
   - Não é necessário configurar banco local

## 💻 Como Usar

### Inicialização Automática
O sistema se inicializa automaticamente através do `bootstrap.php`:

```php
<?php
require_once __DIR__ . '/bootstrap.php';

// O ambiente já foi detectado automaticamente!
```

### Conexão com Banco de Dados
```php
// Obter conexão (detecta ambiente automaticamente)
$db = getDB();

// Fazer consultas
$usuarios = $db->fetchAll("SELECT * FROM usuarios");

// Inserir dados
$id = $db->insert('usuarios', [
    'nome' => 'João',
    'email' => 'joao@email.com'
]);
```

### Verificar Ambiente
```php
if (isProduction()) {
    // Código específico para produção
} else {
    // Código para desenvolvimento
}
```

### Obter Configurações
```php
// Todas as configurações
$config = config();

// Configuração específica
$dbHost = config('db.host');
$appUrl = config('app.url');
```

## 🔒 Segurança

- ✅ Credenciais separadas por ambiente
- ✅ Conexões PDO seguras
- ✅ Prepared statements automáticos
- ✅ Configurações de sessão por ambiente
- ✅ Logs de erro controlados
- ✅ Autoloader otimizado

## 📁 Estrutura do Projeto

```
SGQ PRO/
├── src/
│   ├── Config/
│   │   └── Environment.php     # Detecção de ambiente
│   └── Database/
│       └── Connection.php      # Conexão segura com BD
├── pages/                      # Páginas do sistema
├── assets/                     # CSS, JS, imagens
├── bootstrap.php               # Inicialização do sistema
├── composer.json               # Dependências
├── .env.example               # Template de configuração
└── README.md                  # Este arquivo
```

## 🧪 Teste o Sistema

Execute o arquivo `example_usage.php` para testar:
- Detecção de ambiente
- Conexão com banco
- Configurações automáticas

## 🚀 Deploy no Servidor Hostinger

Para fazer deploy em produção:

1. **Suba os arquivos** para o servidor da Hostinger (`djbr.sgqoti.com.br`)
2. **Execute** `composer install --no-dev` no servidor
3. **Execute** `https://djbr.sgqoti.com.br/setup_database.php` para criar as tabelas
4. **O sistema detectará automaticamente** que está em produção
5. **Todas as configurações** serão aplicadas automaticamente para o ambiente Hostinger

## 📞 Suporte

O sistema está configurado para funcionar automaticamente em qualquer ambiente. Não é necessário alterar configurações manualmente!
