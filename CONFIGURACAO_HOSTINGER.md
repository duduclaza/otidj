# 🔧 CONFIGURAÇÃO HOSTINGER - Erro 403

## **PROBLEMA**

O site `https://djbr.sgqoti.com.br` está retornando **erro 403 Forbidden** ao ser acessado.

## **CAUSA PROVÁVEL**

O **DocumentRoot** do domínio no Hostinger está apontando para a **raiz do projeto** (`/sgqpro`) em vez de apontar para a pasta **`/sgqpro/public`**.

---

## ✅ **SOLUÇÃO 1: CONFIGURAR DOCUMENTROOT (RECOMENDADO)**

### **Passo a Passo no Hostinger:**

1. **Acesse o hPanel** do Hostinger
2. Vá em **Domínios** ou **Websites**
3. Encontre `djbr.sgqoti.com.br`
4. Clique em **Gerenciar** ou **Configurações**
5. Procure por **"Document Root"** ou **"Pasta Raiz"**
6. **Altere de:**
   ```
   /sgqpro
   ```
   **Para:**
   ```
   /sgqpro/public
   ```
7. **Salve as alterações**
8. **Aguarde 2-5 minutos** para propagação

### **Resultado Esperado:**
✅ Site acessível em `https://djbr.sgqoti.com.br`  
✅ Sem erro 403  
✅ Redirecionamento automático para login funcionando

---

## ✅ **SOLUÇÃO 2: USAR INDEX.PHP NA RAIZ (FALLBACK)**

Se você **NÃO conseguir alterar o DocumentRoot** no Hostinger, já implementamos um **fallback automático**:

### **Arquivos Criados:**
1. ✅ `/index.php` - Redireciona para `public/index.php`
2. ✅ `/.htaccess` - Configurado para rotear para `public/`

### **Como Funciona:**
```
Usuário acessa https://djbr.sgqoti.com.br
    ↓
Apache lê /.htaccess
    ↓
Redireciona internamente para /public/index.php
    ↓
Sistema carrega normalmente ✅
```

### **Teste:**
1. Faça upload dos arquivos atualizados:
   - `/index.php`
   - `/.htaccess`
2. Acesse: `https://djbr.sgqoti.com.br`
3. Deve carregar normalmente

---

## 🔍 **DIAGNÓSTICO: VERIFICAR CONFIGURAÇÃO ATUAL**

### **1. Verificar onde o Apache está procurando arquivos:**

Crie um arquivo `info.php` na raiz com:

```php
<?php
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
echo "PWD: " . getcwd() . "<br>";
phpinfo();
?>
```

Acesse: `https://djbr.sgqoti.com.br/info.php`

**Verifique:**
- `DOCUMENT_ROOT` deve ser `/home/usuario/public_html/sgqpro/public` (ideal)
- Ou se for `/home/usuario/public_html/sgqpro`, o fallback vai funcionar

**⚠️ IMPORTANTE:** Delete o arquivo `info.php` depois do teste (segurança)!

---

## 📋 **CHECKLIST DE VERIFICAÇÃO**

- [ ] DocumentRoot aponta para `/sgqpro/public`? (ideal)
- [ ] Arquivo `/index.php` existe na raiz? (fallback)
- [ ] Arquivo `/.htaccess` existe e está configurado?
- [ ] Permissões dos arquivos estão corretas? (644 para arquivos, 755 para pastas)
- [ ] Módulo `mod_rewrite` está ativo no Apache?
- [ ] Arquivo `/public/index.php` existe e é acessível?

---

## 🛠️ **SOLUÇÃO 3: PERMISSÕES DE ARQUIVO**

Se ainda houver erro 403, pode ser problema de **permissões**:

### **Via SSH ou Terminal do Hostinger:**

```bash
# Ir para a pasta do projeto
cd ~/public_html/sgqpro

# Ajustar permissões das pastas
find . -type d -exec chmod 755 {} \;

# Ajustar permissões dos arquivos
find . -type f -exec chmod 644 {} \;

# Garantir que index.php é executável
chmod 644 index.php
chmod 644 public/index.php

# Verificar ownership (deve ser seu usuário)
ls -la
```

### **Via Gerenciador de Arquivos do Hostinger:**

1. Selecione todas as **pastas**
2. Clique direito → **Permissões** → `755` (rwxr-xr-x)
3. Selecione todos os **arquivos**
4. Clique direito → **Permissões** → `644` (rw-r--r--)

---

## 🔄 **SOLUÇÃO 4: RECARREGAR CONFIGURAÇÕES**

### **Via SSH:**
```bash
# Se tiver acesso ao Apache
sudo systemctl reload apache2

# Ou
sudo service apache2 reload
```

### **Via hPanel:**
1. Vá em **Avançado** → **PHP Configuration**
2. Altere qualquer configuração e salve
3. Volte à configuração original e salve novamente
4. Isso força o Apache a recarregar

---

## ✅ **TESTE FINAL**

Após aplicar as soluções:

1. **Limpe o cache do navegador** (Ctrl + Shift + Delete)
2. **Modo anônimo:** Abra navegador em modo privado
3. **Acesse:** `https://djbr.sgqoti.com.br`
4. **Resultado esperado:**
   - ✅ Redireciona para `/login`
   - ✅ Mostra tela de login
   - ✅ Sem erro 403

---

## 📞 **SUPORTE HOSTINGER**

Se nenhuma solução funcionar, entre em contato com o **suporte do Hostinger** e informe:

> "Preciso alterar o **Document Root** do domínio `djbr.sgqoti.com.br` para apontar para a pasta `/sgqpro/public` em vez de `/sgqpro`. Como faço isso?"

Eles vão te guiar ou fazer a alteração por você.

---

## 📊 **RESUMO DAS SOLUÇÕES**

| Solução | Dificuldade | Eficácia | Recomendação |
|---------|-------------|----------|--------------|
| **1. Alterar DocumentRoot** | ⭐⭐⚪⚪⚪ | 100% | ✅ MELHOR |
| **2. Usar Fallback (index.php)** | ⭐⚪⚪⚪⚪ | 95% | ✅ BOM |
| **3. Ajustar Permissões** | ⭐⭐⭐⚪⚪ | 70% | ⚠️ Se for permissão |
| **4. Contatar Suporte** | ⭐⭐⭐⭐⚪ | 100% | ✅ Se não souber |

---

## 🎯 **AÇÃO IMEDIATA**

**Faça agora:**

1. ✅ Upload dos arquivos atualizados (já feito)
   - `/index.php`
   - `/.htaccess`

2. ✅ Teste se funciona
   - Acesse `https://djbr.sgqoti.com.br`

3. ❌ Se ainda der 403:
   - Tente **Solução 1** (DocumentRoot)
   - Ou **Solução 3** (Permissões)
   - Ou **Solução 4** (Contatar Suporte)

---

**Data:** 16/10/2025  
**Versão:** 2.5.1  
**Prioridade:** CRÍTICA 🔥
