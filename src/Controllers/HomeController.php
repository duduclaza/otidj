<?php

namespace App\Controllers;

class HomeController
{
    /**
     * Página inicial do sistema - acessível a todos os usuários autenticados
     */
    public function index()
    {
        // Verificar se está autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        // Obter informações do usuário
        $userName = $_SESSION['user_name'] ?? 'Usuário';
        $userProfile = $_SESSION['user_profile']['name'] ?? 'Usuário';
        
        // Informações do sistema
        $systemVersion = '2.3.0';
        $lastUpdate = '06/10/2025';
        
        // Últimas atualizações do sistema - apenas Melhorias e Ajustes
        $allUpdates = [
            [
                'version' => '2.3.0',
                'date' => '06/10/2025',
                'type' => 'Melhoria',
                'title' => 'Sistema Completo de Edição em Amostragens 2.0',
                'description' => 'Implementada funcionalidade completa de edição de amostragens com suporte a anexos',
                'items' => [
                    'Botão "Editar" carrega todos os dados da amostragem no formulário',
                    'Formulário inline pré-preenchido com dados existentes',
                    'Exibição de anexo NF atual com opção de substituir',
                    'Exibição de evidências existentes (fotos) com detalhes',
                    'Suporte para adicionar novas evidências às existentes',
                    'Atualização de anexo NF opcional (mantém existente se não enviar novo)',
                    'Seleção múltipla de responsáveis pré-selecionados',
                    'Validação completa de campos e arquivos',
                    'Feedback visual claro indicando modo de edição',
                    'Logs detalhados para troubleshooting'
                ]
            ],
            [
                'version' => '2.2.9',
                'date' => '01/10/2025',
                'type' => 'Melhoria',
                'title' => 'Botão Limpar Filtros em Registro de Retornados',
                'description' => 'Adicionado botão para limpar filtros rapidamente no módulo de retornados',
                'items' => [
                    'Novo botão "Limpar" ao lado do botão "Filtrar"',
                    'Limpa automaticamente busca por texto, data inicial e data final',
                    'Mostra todas as linhas da tabela novamente',
                    'Feedback visual com notificação de sucesso',
                    'Layout responsivo ajustado para 6 colunas',
                    'Ícone de lixeira para identificação visual clara'
                ]
            ],
            [
                'version' => '2.2.8',
                'date' => '01/10/2025',
                'type' => 'Correção',
                'title' => 'Correção do Erro 404 na Edição de Toners',
                'description' => 'Corrigido erro 404 Not Found ao tentar salvar edição de toners no cadastro',
                'items' => [
                    'Corrigida rota de edição no JavaScript: /toners/cadastro/edit → /toners/update',
                    'Rota backend /toners/update já existia e funcionava corretamente',
                    'Problema era inconsistência entre frontend e backend',
                    'Edição inline de toners agora funciona normalmente',
                    'Validação de campos obrigatórios mantida'
                ]
            ],
            [
                'version' => '2.2.7',
                'date' => '30/09/2025',
                'type' => 'Correção',
                'title' => 'Correção do Cálculo de Valor para Destino Estoque',
                'description' => 'Melhorado cálculo de valor em R$ quando destino é "estoque" nos retornados',
                'items' => [
                    'Aprimorada validação de campos capacidade_folhas e custo_por_folha',
                    'Melhorado cálculo de percentual_restante com limites (0-100%)',
                    'Adicionados logs detalhados para diagnóstico do cálculo',
                    'Corrigida condição para percentual_chip >= 0 (aceita 0%)',
                    'Sistema agora calcula valor corretamente: folhas_restantes × custo_por_folha',
                    'Logs mostram cada etapa do cálculo para facilitar troubleshooting'
                ]
            ],
            [
                'version' => '2.2.6',
                'date' => '30/09/2025',
                'type' => 'Correção',
                'title' => 'Correção do Erro 404 na Exclusão de Toners',
                'description' => 'Corrigido erro 404 Not Found ao tentar excluir modelos de toner no cadastro',
                'items' => [
                    'Corrigida rota de exclusão no JavaScript: /toners/cadastro/delete → /toners/delete',
                    'Rota backend /toners/delete já existia e funcionava corretamente',
                    'Problema era inconsistência entre frontend e backend',
                    'Exclusão de toners agora funciona normalmente',
                    'Confirmação de exclusão mantida para segurança'
                ]
            ],
            [
                'version' => '2.2.5',
                'date' => '30/09/2025',
                'type' => 'Correção',
                'title' => 'Correção "Modelo não cadastrado" em Retornados',
                'description' => 'Corrigido problema onde modelos cadastrados apareciam como "não cadastrados"',
                'items' => [
                    'Corrigida busca de modelo no backend para aceitar ID ou nome',
                    'Adicionado campo hidden modelo_id no frontend',
                    'Melhorada seleção de modelo com logs detalhados',
                    'Sistema agora detecta corretamente modelos cadastrados',
                    'Compatibilidade mantida com busca por nome como fallback',
                    'Logs de debug para facilitar diagnóstico de problemas'
                ]
            ],
            [
                'version' => '2.2.4',
                'date' => '30/09/2025',
                'type' => 'Correção',
                'title' => 'Correção para Usar Parâmetros Configurados',
                'description' => 'Sistema agora usa APENAS as orientações configuradas nos parâmetros de retornados',
                'items' => [
                    'Removidas orientações inventadas pelo sistema',
                    'Prioridade total para parâmetros configurados em Configurações',
                    'Sistema busca orientações nas faixas de percentual configuradas',
                    'Fallback apenas para recarregar parâmetros se necessário',
                    'Mensagem clara quando percentual está fora das faixas configuradas',
                    'Logs detalhados para verificar uso dos parâmetros corretos'
                ]
            ],
            [
                'version' => '2.2.3',
                'date' => '30/09/2025',
                'type' => 'Melhoria',
                'title' => 'Aprimoramento Completo do Modo Peso em Retornados',
                'description' => 'Melhorado modo peso físico com cálculo automático, orientações inteligentes e detecção de casos especiais',
                'items' => [
                    'Adicionado evento oninput para cálculo automático conforme digita',
                    'Melhorada exibição da gramatura restante com percentual',
                    'Implementada detecção automática de toner vazio/cheio',
                    'Orientações detalhadas com emojis e instruções específicas',
                    'Cálculo robusto com fallbacks para modelos sem dados completos',
                    'Criada função de teste testarModoPeso() para diagnóstico',
                    'Logs detalhados para facilitar troubleshooting'
                ]
            ],
            [
                'version' => '2.2.2',
                'date' => '30/09/2025',
                'type' => 'Correção',
                'title' => 'Correção Específica do Modo Percentual em Retornados',
                'description' => 'Corrigido problema específico onde o modo percentual não mostrava orientações e botões de destino',
                'items' => [
                    'Corrigido evento oninput no campo percentual do chip',
                    'Melhorada função calcularPercentual() com logs detalhados',
                    'Implementada busca de modelo por ID e nome',
                    'Adicionada validação robusta para percentuais (0-100%)',
                    'Criada função de teste testarModoPercentual()',
                    'Garantidos valores padrão para modelos sem dados completos'
                ]
            ],
            [
                'version' => '2.2.1',
                'date' => '30/09/2025',
                'type' => 'Correção',
                'title' => 'Correção do Sistema de Orientações em Retornados',
                'description' => 'Corrigido problema onde orientações e botões de destino não apareciam no módulo de retornados',
                'items' => [
                    'Corrigida função mostrarResultados() para sempre exibir botões de destino',
                    'Implementado sistema de orientações padrão quando parâmetros não carregam',
                    'Adicionada função forcarExibicaoDestinos() como fallback',
                    'Melhorado carregamento de parâmetros com retry automático',
                    'Adicionados logs detalhados para diagnóstico de problemas',
                    'Sistema agora funciona mesmo com falha na API de parâmetros'
                ]
            ],
            [
                'version' => '2.2.0',
                'date' => '29/09/2025',
                'type' => 'Melhoria',
                'title' => 'Aprimoramento Completo do Sistema POPs e ITs',
                'description' => 'Melhorias significativas no módulo POPs e ITs com correções de acesso e otimizações',
                'items' => [
                    'Corrigido sistema de acesso para páginas "Em Breve"',
                    'Fluxogramas agora mostra interface amigável em vez de erro',
                    'Otimizado PermissionMiddleware para rotas públicas',
                    'Melhorada experiência do usuário em módulos em desenvolvimento',
                    'Sistema de diagnóstico e correção automática implementado'
                ]
            ],
            [
                'version' => '2.1.9',
                'date' => '26/09/2025',
                'type' => 'Melhoria',
                'title' => 'Aprimoramento do Sistema de Dashboard',
                'description' => 'Melhorado sistema de acesso ao dashboard com verificações mais precisas de permissões',
                'items' => [
                    'Sistema de permissões mais flexível para diferentes perfis',
                    'Dashboard acessível para supervisores e perfis autorizados',
                    'Melhorado fluxo de navegação após login',
                    'Interface otimizada para diferentes tipos de usuários'
                ]
            ],
            [
                'version' => '2.1.8',
                'date' => '26/09/2025',
                'type' => 'Melhoria',
                'title' => 'Aprimoramento do Sistema de Perfis',
                'description' => 'Melhorado acesso ao perfil próprio e sistema de diagnóstico para supervisores',
                'items' => [
                    'Acesso facilitado ao perfil próprio para todos os usuários',
                    'Usuários podem gerenciar foto e senha de forma autônoma',
                    'Sistema de diagnóstico avançado para administradores',
                    'Otimização das permissões de dashboard'
                ]
            ],
            [
                'version' => '2.1.7',
                'date' => '26/09/2025',
                'type' => 'Melhoria',
                'title' => 'Sistema de Diagnóstico Avançado',
                'description' => 'Implementado sistema integrado de diagnóstico para análise de POPs em produção',
                'items' => [
                    'Nova ferramenta de diagnóstico integrada ao sistema',
                    'Interface moderna para análise completa de dados',
                    'Verificação abrangente de registros e permissões',
                    'Otimização da experiência do usuário logado'
                ]
            ],
            [
                'version' => '2.1.6',
                'date' => '26/09/2025',
                'type' => 'Melhoria',
                'title' => 'Otimização de URLs e Navegação',
                'description' => 'Melhorado sistema de URLs e navegação do dashboard para melhor experiência',
                'items' => [
                    'URLs mais intuitivas e amigáveis',
                    'Navegação otimizada no menu Dashboard',
                    'Sistema de diagnóstico aprimorado',
                    'Verificações de permissão mais eficientes'
                ]
            ],
            [
                'version' => '2.1.5',
                'date' => '26/09/2025',
                'type' => 'Melhoria',
                'title' => 'Histórico Completo de Atualizações',
                'description' => 'Seção "Últimas Atualizações" agora mostra todo o histórico desde o início',
                'items' => [
                    'Histórico completo de todas as versões disponível',
                    'Interface mais limpa sem limitações de visualização',
                    'Navegação simplificada entre atualizações',
                    'Ícones e cores aprimorados para melhor organização'
                ]
            ],
            [
                'version' => '2.1.4',
                'date' => '26/09/2025',
                'type' => 'Ajuste',
                'title' => 'Padronização do Redirecionamento de Login',
                'description' => 'Todos os usuários são direcionados para a página Início após login, independente de permissões',
                'items' => [
                    'Login sempre redireciona para /inicio para todos os usuários',
                    'Comportamento uniforme independente de permissões',
                    'Dashboard acessível apenas via menu para quem tem permissão',
                    'Experiência de login consistente e previsível'
                ]
            ],
            [
                'version' => '2.1.3',
                'date' => '26/09/2025',
                'type' => 'Melhoria',
                'title' => 'Aprimoramento do Sistema de Dashboard',
                'description' => 'Melhorado sistema de redirecionamento e acesso ao dashboard',
                'items' => [
                    'Sistema de verificação de permissões mais inteligente',
                    'Acesso otimizado ao dashboard para usuários autorizados',
                    'Redirecionamento inteligente baseado em perfil',
                    'Experiência de navegação aprimorada'
                ]
            ],
            [
                'version' => '2.1.2',
                'date' => '26/09/2025',
                'type' => 'Melhoria',
                'title' => 'Sistema de Diagnóstico de Permissões',
                'description' => 'Implementadas ferramentas avançadas de diagnóstico para análise de permissões',
                'items' => [
                    'Ferramentas de análise de permissões implementadas',
                    'Sistema de diagnóstico automático criado',
                    'Testes específicos para validação de usuários',
                    'Otimização do sistema de permissões'
                ]
            ],
            [
                'version' => '2.1.1',
                'date' => '26/09/2025',
                'type' => 'Melhoria',
                'title' => 'Interface da Página Início Otimizada',
                'description' => 'Removidos cards desnecessários para interface mais limpa e focada',
                'items' => [
                    'Interface mais limpa e focada no essencial',
                    'Removidos elementos visuais desnecessários',
                    'Mantida seção de boas-vindas personalizada',
                    'Preservada seção "Últimas Atualizações" otimizada'
                ]
            ],
            [
                'version' => '2.1.0',
                'date' => '26/09/2025',
                'type' => 'Melhoria',
                'title' => 'Sistema de Redirecionamento Inteligente',
                'description' => 'Implementado sistema inteligente de redirecionamento baseado em permissões',
                'items' => [
                    'Redirecionamento inteligente baseado em permissões',
                    'Padronização de nomes de módulos para consistência',
                    'Módulo "Início" acessível a todos os usuários',
                    'Experiência de login otimizada para diferentes perfis'
                ]
            ],
            [
                'version' => '2.0.5',
                'date' => '26/09/2025',
                'type' => 'Ajuste',
                'title' => 'Otimização do Menu POPs e ITs',
                'description' => 'Aprimorado sistema de exibição do menu POPs e ITs',
                'items' => [
                    'Padronização de nomenclatura de módulos',
                    'Sistema de exibição de menus otimizado',
                    'Melhor integração com sistema de permissões'
                ]
            ],
            [
                'version' => '2.0.4',
                'date' => '26/09/2025',
                'type' => 'Ajuste',
                'title' => 'Padronização do Módulo 5W2H',
                'description' => 'Padronizado sistema de nomenclatura e permissões do módulo 5W2H',
                'items' => [
                    'Nomenclatura padronizada para "5w2h"',
                    'Sistema de permissões otimizado e consistente',
                    'Melhor integração com middleware de segurança'
                ]
            ],
            [
                'version' => '2.0.3',
                'date' => '26/09/2025',
                'type' => 'Melhoria',
                'title' => 'Aprimoramento da Edição de Perfis',
                'description' => 'Melhorado sistema de edição de perfis com usuários associados',
                'items' => [
                    'Sistema de rotas otimizado para edição de perfis',
                    'Edição de perfis mais fluida e intuitiva',
                    'Mantidas proteções adequadas para exclusão'
                ]
            ],
            [
                'version' => '2.0.2',
                'date' => '25/09/2025',
                'type' => 'Melhoria',
                'title' => 'Sistema de Amostragens MEDIUMBLOB',
                'description' => 'Implementado armazenamento de arquivos no banco de dados',
                'items' => [
                    'PDF e evidências salvos como MEDIUMBLOB',
                    'Eliminada dependência do filesystem',
                    'Backup completo incluindo arquivos',
                    'Sistema de notificações por email implementado'
                ]
            ],
            [
                'version' => '2.0.1',
                'date' => '24/09/2025',
                'type' => 'Melhoria',
                'title' => 'Sistema de Permissões Granular',
                'description' => 'Implementado sistema completo de permissões por módulo',
                'items' => [
                    '23+ módulos com permissões granulares',
                    '5 perfis pré-configurados especializados',
                    'Middleware automático de verificação',
                    'Interface intuitiva para gerenciamento'
                ]
            ]
        ];
        
        // Filtrar apenas Melhorias e Ajustes
        $updates = array_filter($allUpdates, function($update) {
            return in_array($update['type'], ['Melhoria', 'Ajuste']);
        });
        
        $title = 'Início - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/home.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
}
