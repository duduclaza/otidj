<?php
/**
 * Script de Verificação: Setor de Compras x POPs e ITs
 * 
 * Este script verifica:
 * 1. Como está o nome do setor "Compras" na tabela departamentos
 * 2. Como está o setor dos usuários que deveriam ver POPs de Compras
 * 3. Quais POPs estão configurados para o setor de Compras
 * 4. Se há diferença de case ou formato causando o problema
 */

require __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

try {
    $db = Database::getInstance();
    
    echo "=== VERIFICAÇÃO: SETOR DE COMPRAS x POPs e ITs ===\n\n";
    
    // 1. Verificar departamentos com nome similar a "Compras"
    echo "1. DEPARTAMENTOS (procurando 'Compras'):\n";
    echo str_repeat("-", 60) . "\n";
    
    $stmt = $db->prepare("
        SELECT id, nome 
        FROM departamentos 
        WHERE nome LIKE '%compra%' 
        OR LOWER(nome) LIKE '%compra%'
        ORDER BY nome
    ");
    $stmt->execute();
    $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($departamentos)) {
        echo "❌ NENHUM departamento encontrado com 'compra' no nome\n";
    } else {
        foreach ($departamentos as $dept) {
            echo "✓ ID: {$dept['id']} | Nome: '{$dept['nome']}'\n";
        }
    }
    
    echo "\n";
    
    // 2. Verificar usuários com setor "Compras"
    echo "2. USUÁRIOS COM SETOR 'COMPRAS':\n";
    echo str_repeat("-", 60) . "\n";
    
    $stmt = $db->prepare("
        SELECT id, name, email, setor 
        FROM users 
        WHERE setor LIKE '%compra%' 
        OR LOWER(setor) LIKE '%compra%'
        ORDER BY name
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($usuarios)) {
        echo "❌ NENHUM usuário encontrado com 'compra' no setor\n";
    } else {
        foreach ($usuarios as $user) {
            echo "✓ ID: {$user['id']} | Nome: {$user['name']}\n";
            echo "  Email: {$user['email']}\n";
            echo "  Setor: '{$user['setor']}'\n\n";
        }
    }
    
    echo "\n";
    
    // 3. Verificar POPs configurados para Compras
    echo "3. POPs/ITs CONFIGURADOS PARA 'COMPRAS':\n";
    echo str_repeat("-", 60) . "\n";
    
    $stmt = $db->prepare("
        SELECT 
            r.id,
            r.versao,
            r.status,
            r.publico,
            t.tipo,
            t.titulo,
            GROUP_CONCAT(d.nome ORDER BY d.nome SEPARATOR ', ') as departamentos
        FROM pops_its_registros r
        LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
        LEFT JOIN pops_its_registros_departamentos rd ON r.id = rd.registro_id
        LEFT JOIN departamentos d ON rd.departamento_id = d.id
        WHERE EXISTS (
            SELECT 1 
            FROM pops_its_registros_departamentos rd2
            INNER JOIN departamentos d2 ON rd2.departamento_id = d2.id
            WHERE rd2.registro_id = r.id 
            AND (
                d2.nome LIKE '%compra%' 
                OR LOWER(d2.nome) LIKE '%compra%'
            )
        )
        GROUP BY r.id, r.versao, r.status, r.publico, t.tipo, t.titulo
        ORDER BY r.id DESC
    ");
    $stmt->execute();
    $pops = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($pops)) {
        echo "❌ NENHUM POP/IT configurado para departamentos com 'compra'\n";
    } else {
        foreach ($pops as $pop) {
            $publico = $pop['publico'] ? 'Público' : 'Restrito';
            echo "✓ ID: {$pop['id']} | {$pop['tipo']} | Status: {$pop['status']} | {$publico}\n";
            echo "  Título: {$pop['titulo']}\n";
            echo "  Versão: {$pop['versao']}\n";
            echo "  Departamentos: {$pop['departamentos']}\n\n";
        }
    }
    
    echo "\n";
    
    // 4. Teste de comparação
    echo "4. TESTE DE COMPARAÇÃO (case-sensitive vs case-insensitive):\n";
    echo str_repeat("-", 60) . "\n";
    
    if (!empty($departamentos) && !empty($usuarios)) {
        $dept_nome = $departamentos[0]['nome'];
        $user_setor = $usuarios[0]['setor'];
        
        echo "Departamento: '{$dept_nome}'\n";
        echo "Setor Usuário: '{$user_setor}'\n\n";
        
        // Comparação exata
        $match_exato = ($dept_nome === $user_setor);
        echo "Match Exato (===): " . ($match_exato ? "✅ SIM" : "❌ NÃO") . "\n";
        
        // Comparação case-insensitive
        $match_lower = (strtolower(trim($dept_nome)) === strtolower(trim($user_setor)));
        echo "Match Case-insensitive: " . ($match_lower ? "✅ SIM" : "❌ NÃO") . "\n";
        
        // Verificar espaços extras
        $dept_length = strlen($dept_nome);
        $user_length = strlen($user_setor);
        echo "Tamanho do nome departamento: {$dept_length} caracteres\n";
        echo "Tamanho do setor usuário: {$user_length} caracteres\n";
        
        if ($dept_length !== $user_length) {
            echo "⚠️ TAMANHOS DIFERENTES - pode ter espaços extras!\n";
        }
    }
    
    echo "\n";
    
    // 5. Recomendações
    echo "5. DIAGNÓSTICO E RECOMENDAÇÕES:\n";
    echo str_repeat("-", 60) . "\n";
    
    if (empty($departamentos)) {
        echo "❌ PROBLEMA: Não existe departamento 'Compras' cadastrado\n";
        echo "   SOLUÇÃO: Cadastrar departamento 'Compras' no sistema\n";
    }
    
    if (empty($usuarios)) {
        echo "❌ PROBLEMA: Nenhum usuário tem setor 'Compras'\n";
        echo "   SOLUÇÃO: Configurar o setor dos usuários para 'Compras'\n";
    }
    
    if (empty($pops)) {
        echo "❌ PROBLEMA: Nenhum POP/IT configurado para o setor Compras\n";
        echo "   SOLUÇÃO: Ao criar POPs, selecionar o departamento 'Compras'\n";
    }
    
    if (!empty($departamentos) && !empty($usuarios) && !empty($pops)) {
        if (!$match_exato && $match_lower) {
            echo "⚠️ PROBLEMA: Diferença de MAIÚSCULAS/minúsculas\n";
            echo "   SOLUÇÃO: A correção aplicada no código resolve isso\n";
            echo "   (comparação case-insensitive já implementada)\n";
        } elseif (!$match_exato && !$match_lower) {
            echo "❌ PROBLEMA: Nomes não batem nem com case-insensitive\n";
            echo "   Departamento: '{$dept_nome}'\n";
            echo "   Setor Usuário: '{$user_setor}'\n";
            echo "   SOLUÇÃO: Padronizar os nomes\n";
        } else {
            echo "✅ TUDO CONFIGURADO CORRETAMENTE!\n";
            echo "   Se ainda assim não está funcionando, limpar cache do navegador\n";
        }
    }
    
    echo "\n=== FIM DA VERIFICAÇÃO ===\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}
