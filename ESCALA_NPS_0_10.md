# 📊 NPS - Escala 0 a 10 (Padrão Mundial)

**Data:** 17/11/2025  
**Status:** ✅ Atualizado para Escala Padrão NPS

---

## 🎯 Mudança Aplicada

### **Antes:**
- ❌ Escala 0-5
- ❌ Não é padrão NPS

### **Depois:**
- ✅ Escala 0-10
- ✅ Padrão NPS mundial
- ✅ Compatível com benchmarks

---

## 📐 Escala NPS Padrão (0-10)

### **Classificação:**

```
┌─────────────────────────────────────┐
│  0  1  2  3  4  5  6  7  8  9  10  │
├─────────────────────────────────────┤
│  DETRATORES   │ NEUTROS │PROMOTORES│
│   (0-6)       │  (7-8)  │  (9-10)  │
└─────────────────────────────────────┘
```

### **Detratores (0-6):**
- ❌ Clientes insatisfeitos
- ❌ Podem falar mal da empresa
- ❌ Risco de cancelamento

### **Neutros (7-8):**
- 😐 Satisfeitos mas não entusiasmados
- 😐 Podem trocar por concorrente
- 😐 Não promovem ativamente

### **Promotores (9-10):**
- ✅ Muito satisfeitos
- ✅ Recomendam a empresa
- ✅ Clientes leais

---

## 🧮 Cálculo do NPS

### **Fórmula:**
```
NPS = % Promotores - % Detratores
```

### **Exemplo:**
```
100 respostas:
- 60 promotores (9-10) = 60%
- 20 neutros (7-8) = 20%
- 20 detratores (0-6) = 20%

NPS = 60% - 20% = 40

Resultado: NPS de 40 pontos
```

---

## 📊 Interpretação do NPS

### **Escala de Classificação:**

```
 -100 a -1   = ❌ Zona Crítica
    0 a 30   = ⚠️ Zona de Aperfeiçoamento
   31 a 50   = 😊 Zona de Qualidade
   51 a 75   = ✅ Zona de Excelência
   76 a 100  = 🏆 Zona de Perfeição (raro)
```

### **Benchmarks Mundiais:**

- **Apple:** ~70
- **Amazon:** ~60
- **Netflix:** ~65
- **Média Brasil:** ~40-50
- **Excelente:** >75

---

## 🔧 Mudanças no Código

### **1. Frontend (responder.php)**

**JÁ ESTAVA CORRETO:**
```html
<input type="range" 
       name="resposta_0" 
       min="0" 
       max="10" 
       value="5">
```

✅ Já usava escala 0-10!

---

### **2. Backend (NpsController.php)**

**Distribuição de Notas:**
```php
// ANTES:
'distribuicao_notas' => array_fill(0, 6, 0), // 0-5

// DEPOIS:
'distribuicao_notas' => array_fill(0, 11, 0), // 0-10
```

**Validação:**
```php
// ANTES:
if ($r['resposta'] >= 0 && $r['resposta'] <= 5)

// DEPOIS:
if ($r['resposta'] >= 0 && $r['resposta'] <= 10)
```

**Classificação:**
```php
// ANTES:
// Escala 0-5: Promotores (4-5), Neutros (3), Detratores (0-2)
if ($nota >= 4) {
    $stats['promotores']++;
} elseif ($nota == 3) {
    $stats['neutros']++;
} else {
    $stats['detratores']++;
}

// DEPOIS:
// Escala 0-10 padrão NPS: Promotores (9-10), Neutros (7-8), Detratores (0-6)
if ($nota >= 9) {
    $stats['promotores']++;
} elseif ($nota >= 7) {
    $stats['neutros']++;
} else {
    $stats['detratores']++;
}
```

---

## 📈 Exemplo de Cálculo Real

### **Cenário 1: Empresa com NPS +50**
```
200 clientes responderam:
- 120 deram 9 ou 10 (Promotores) = 60%
- 60 deram 7 ou 8 (Neutros) = 30%
- 20 deram 0-6 (Detratores) = 10%

NPS = 60% - 10% = +50

Resultado: ✅ Excelente! Zona de Excelência
```

### **Cenário 2: Empresa com NPS -10**
```
100 clientes responderam:
- 20 deram 9 ou 10 (Promotores) = 20%
- 50 deram 7 ou 8 (Neutros) = 50%
- 30 deram 0-6 (Detratores) = 30%

NPS = 20% - 30% = -10

Resultado: ❌ Crítico! Precisa melhorar urgente
```

### **Cenário 3: Empresa com NPS +80**
```
150 clientes responderam:
- 135 deram 9 ou 10 (Promotores) = 90%
- 10 deram 7 ou 8 (Neutros) = 7%
- 5 deram 0-6 (Detratores) = 3%

NPS = 90% - 3% = +87

Resultado: 🏆 Perfeição! Excelência Mundial
```

---

## 🎯 Vantagens da Escala 0-10

### **1. ✅ Padrão Mundial**
- Usado por empresas globais
- Permite comparar com benchmarks
- Relatórios e estudos usam 0-10

### **2. ✅ Mais Granular**
- 11 opções vs 6 opções
- Maior precisão na resposta
- Identifica nuances melhor

### **3. ✅ Intuitivo**
- Escala decimal familiar
- Mais fácil de entender
- Padrão em notas escolares

### **4. ✅ Classificação Clara**
```
0-6  = Detrator (70% da escala)
7-8  = Neutro (20% da escala)
9-10 = Promotor (20% da escala)

Critério rigoroso para ser Promotor!
```

---

## 📊 Gráficos e Visualizações

### **Distribuição Típica:**
```
Nota | Quantidade | Percentual
-----|-----------|------------
  0  |    2      |   2%    ████
  1  |    1      |   1%    ██
  2  |    3      |   3%    ██████
  3  |    5      |   5%    ██████████
  4  |    8      |   8%    ████████████████
  5  |   10      |  10%    ████████████████████
  6  |   12      |  12%    ████████████████████████
  7  |   15      |  15%    ██████████████████████████████
  8  |   18      |  18%    ████████████████████████████████████
  9  |   14      |  14%    ████████████████████████████
 10  |   12      |  12%    ████████████████████████

Promotores (9-10): 26% ✅
Neutros (7-8):     33% 😐
Detratores (0-6):  41% ❌

NPS = 26% - 41% = -15 (Precisa melhorar!)
```

---

## 🧪 Como Testar

### **1. Criar Formulário com Escala:**
```
Pergunta: "De 0 a 10, quanto você recomendaria nossa empresa?"
Tipo: Numérico (slider)
```

### **2. Responder com Notas Diferentes:**
```
Teste 1: Nota 10 → Deve contar como Promotor
Teste 2: Nota 8 → Deve contar como Neutro
Teste 3: Nota 5 → Deve contar como Detrator
```

### **3. Ver Dashboard:**
```
- Total de respostas
- % Promotores
- % Neutros
- % Detratores
- Score NPS calculado
```

---

## 📁 Arquivos Modificados

**1. src/Controllers/NpsController.php**
- Linha 767: array_fill(0, 11, 0) - 11 posições (0-10)
- Linha 804: comentário atualizado
- Linha 807: validação 0-10
- Linhas 811-818: nova classificação NPS

**2. views/pages/nps/responder.php**
- ✅ JÁ ESTAVA com escala 0-10!

**3. Documentação:**
- ✅ ESCALA_NPS_0_10.md (este arquivo)

---

## ✅ Checklist

```
□ Escala 0-10 no formulário
□ Validação 0-10 no backend
□ Classificação correta (9-10, 7-8, 0-6)
□ Array com 11 posições (0-10)
□ Cálculo NPS usando fórmula padrão
□ Dashboard mostrando dados corretos
□ Testado com diferentes notas
```

---

## 🎉 Resultado Final

**Agora o sistema:**
- ✅ Usa escala NPS padrão mundial (0-10)
- ✅ Classificação correta: Promotores (9-10), Neutros (7-8), Detratores (0-6)
- ✅ Cálculo: % Promotores - % Detratores
- ✅ Comparável com benchmarks internacionais
- ✅ Mais preciso e intuitivo

---

**Versão:** 2.0  
**Status:** ✅ Escala Padrão Implementada  
**Sistema:** SGQ-OTI DJ
