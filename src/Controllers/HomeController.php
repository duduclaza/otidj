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
        $systemVersion = '2.1.4';
        $lastUpdate = '26/09/2025';
        
        // Últimas atualizações do sistema
        $updates = [
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
                'type' => 'Correção',
                'title' => 'Correção do Redirecionamento do Dashboard',
                'description' => 'Corrigido problema onde menu Dashboard redirecionava para Início em vez do dashboard real',
                'items' => [
                    'Rota "/" agora verifica permissão antes de redirecionar',
                    'Usuários com permissão de dashboard acessam dashboard real',
                    'Usuários sem permissão são redirecionados para /inicio',
                    'Sistema de login ajustado para redirecionamento inteligente'
                ]
            ],
            [
                'version' => '2.1.2',
                'date' => '26/09/2025',
                'type' => 'Investigação',
                'title' => 'Diagnóstico de Permissões do Dashboard',
                'description' => 'Criados scripts de diagnóstico para investigar problemas de acesso ao dashboard',
                'items' => [
                    'Criado script debug_dashboard_permissions.php para análise',
                    'Criado script fix_dashboard_permissions.php para correções',
                    'Criado script test_user_dashboard.php para testes específicos',
                    'Investigação de inconsistências no sistema de permissões'
                ]
            ],
            [
                'version' => '2.1.1',
                'date' => '26/09/2025',
                'type' => 'Melhoria',
                'title' => 'Interface da Página Início Otimizada',
                'description' => 'Removidos cards desnecessários para interface mais limpa e focada',
                'items' => [
                    'Removidos cards "Status do Sistema" e "Acesso Rápido"',
                    'Interface mais limpa e focada no essencial',
                    'Mantida seção de boas-vindas personalizada',
                    'Preservada seção "Últimas Atualizações" com changelog'
                ]
            ],
            [
                'version' => '2.1.0',
                'date' => '26/09/2025',
                'type' => 'Correção Crítica',
                'title' => 'Correção de Loop de Redirecionamento',
                'description' => 'Corrigido erro ERR_TOO_MANY_REDIRECTS que impedia login de usuários não-admin',
                'items' => [
                    'Implementado redirecionamento inteligente baseado em permissões',
                    'Corrigidas inconsistências nos nomes de módulos',
                    'Adicionado módulo "Início" acessível a todos os usuários',
                    'Melhorada experiência de login para diferentes perfis'
                ]
            ],
            [
                'version' => '2.0.5',
                'date' => '26/09/2025',
                'type' => 'Correção',
                'title' => 'Correção Menu POPs e ITs',
                'description' => 'Menu POPs e ITs voltou a aparecer no sidebar',
                'items' => [
                    'Corrigida inconsistência nos nomes de módulos POPs e ITs',
                    'Padronizado uso de pops_its_visualizacao',
                    'Menu agora aparece para usuários com permissões adequadas'
                ]
            ],
            [
                'version' => '2.0.4',
                'date' => '26/09/2025',
                'type' => 'Correção',
                'title' => 'Correção Módulo 5W2H',
                'description' => 'Resolvido erro HTTP 403 no módulo 5W2H',
                'items' => [
                    'Padronizado nome do módulo para "5w2h"',
                    'Corrigidas 12 verificações de permissão no controller',
                    'Sistema de permissões agora 100% consistente'
                ]
            ],
            [
                'version' => '2.0.3',
                'date' => '26/09/2025',
                'type' => 'Correção',
                'title' => 'Correção Edição de Perfis',
                'description' => 'Corrigido erro ao editar perfis com usuários associados',
                'items' => [
                    'Adicionada rota faltante /admin/profiles/{id}/permissions',
                    'Edição de perfis agora funciona normalmente',
                    'Mantida restrição apenas para exclusão de perfis'
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
        
        $title = 'Início - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/home.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
}
