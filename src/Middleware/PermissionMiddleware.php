<?php

namespace App\Middleware;

use App\Services\PermissionService;

class PermissionMiddleware
{
    /**
     * Mapeamento de rotas para módulos de permissão
     */
    private static $routeModuleMap = [
        // Dashboard
        '/' => 'dashboard',
        
        // Toners
        '/toners/cadastro' => 'toners_cadastro',
        '/toners/import' => 'toners_cadastro',
        '/toners/export' => 'toners_cadastro',
        '/toners/retornados' => 'toners_retornados',
        '/toners/retornados/export' => 'toners_retornados',
        '/toners/retornados/import' => 'toners_retornados',
        '/toners/retornados/delete' => 'toners_retornados',
        
        // Módulos principais
        '/homologacoes' => 'homologacoes',
        '/toners/amostragens' => 'amostragens',
        '/garantias' => 'garantias',
        '/controle-de-descartes' => 'controle_descartes',
        '/femea' => 'femea',
        '/pops-e-its' => 'pops_its_visualizacao', // Rota principal vai para visualização (todos podem ver)
        '/fluxogramas' => 'fluxogramas',
        
        // POPs e ITs - Rotas específicas por aba
        '/pops-its/titulo/create' => 'pops_its_cadastro_titulos',
        '/pops-its/titulos/list' => 'pops_its_cadastro_titulos',
        '/pops-its/registro/create' => 'pops_its_meus_registros',
        '/pops-its/registros/meus' => 'pops_its_meus_registros',
        '/pops-its/registro/update' => 'pops_its_meus_registros',
        '/pops-its/registro/delete' => 'pops_its_meus_registros',
        '/pops-its/pendentes/list' => 'pops_its_pendente_aprovacao',
        '/pops-its/registro/aprovar' => 'pops_its_pendente_aprovacao',
        '/pops-its/registro/reprovar' => 'pops_its_pendente_aprovacao',
        '/pops-its/visualizacao/list' => 'pops_its_visualizacao',
        '/pops-its/arquivo' => 'pops_its_visualizacao', // Download de arquivos
        // Melhoria Contínua
        '/melhoria-continua' => 'melhoria_continua',
        '/melhoria-continua/list' => 'melhoria_continua',
        '/melhoria-continua/departamentos' => 'melhoria_continua',
        '/melhoria-continua/usuarios' => 'melhoria_continua',
        '/melhoria-continua/store' => 'melhoria_continua',
        '/controle-de-rc' => 'controle_rc',
        
        // 5W2H - Planos de Ação
        '/5w2h' => '5w2h',
        '/5w2h/list' => '5w2h',
        '/5w2h/create' => '5w2h',
        '/5w2h/update' => '5w2h',
        '/5w2h/delete' => '5w2h',
        '/5w2h/details' => '5w2h',
        '/5w2h/print' => '5w2h',
        '/5w2h/anexos' => '5w2h',
        '/5w2h/anexo' => '5w2h',
        '/5w2h/relatorios' => '5w2h',
        
        // Registros
        '/registros/filiais' => 'registros_filiais',
        '/registros/departamentos' => 'registros_departamentos',
        '/registros/fornecedores' => 'registros_fornecedores',
        '/registros/parametros' => 'registros_parametros',
        
        // Configurações
        '/configuracoes' => 'configuracoes_gerais',
        '/admin/users' => 'admin_usuarios',
        '/admin/profiles' => 'admin_perfis',
        '/admin/invitations' => 'admin_convites',
        '/admin' => 'admin_painel',
        
        // Perfil
        '/profile' => 'profile',
    ];
    
    /**
     * Verificar permissão para uma rota
     */
    public static function checkRoutePermission(string $route, string $method = 'GET'): bool
    {
        // Rotas que não precisam de verificação de permissão
        $publicRoutes = [
            '/login', '/auth/login', '/register', '/auth/register', '/logout',
            '/request-access', '/access-request/process',
            '/email/test-connection', '/email/send-test'
        ];
        
        // Rotas de API que têm verificação própria
        $apiRoutes = ['/api/', '/admin/users/create', '/admin/users/update', '/admin/users/delete', '/admin/users/send-credentials', '/admin/test-email', '/debug/'];
        
        // Verificar se é rota pública
        foreach ($publicRoutes as $publicRoute) {
            if (strpos($route, $publicRoute) === 0) {
                return true;
            }
        }
        
        // Verificar se é rota de API (tem verificação própria)
        foreach ($apiRoutes as $apiRoute) {
            if (strpos($route, $apiRoute) === 0) {
                return true;
            }
        }
        
        // Se não está logado, não tem permissão
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Encontrar o módulo correspondente à rota
        $module = self::getModuleForRoute($route);
        if (!$module) {
            // Se não encontrou módulo específico, permitir (pode ser uma rota dinâmica)
            return true;
        }
        
        // Determinar a ação baseada no método HTTP
        $action = self::getActionForMethod($method);
        
        // Verificar permissão
        return PermissionService::hasPermission($userId, $module, $action);
    }
    
    /**
     * Obter módulo para uma rota
     */
    private static function getModuleForRoute(string $route): ?string
    {
        // Normalizar a rota
        $route = rtrim($route, '/') ?: '/';
        
        // Verificar mapeamento direto
        if (isset(self::$routeModuleMap[$route])) {
            return self::$routeModuleMap[$route];
        }
        
        // Verificar rotas dinâmicas (com parâmetros)
        foreach (self::$routeModuleMap as $pattern => $module) {
            if (self::matchRoute($pattern, $route)) {
                return $module;
            }
        }
        
        return null;
    }
    
    /**
     * Verificar se uma rota corresponde a um padrão
     */
    private static function matchRoute(string $pattern, string $route): bool
    {
        // Converter padrão para regex
        $regex = preg_replace('/\{[^}]+\}/', '[^/]+', $pattern);
        $regex = '#^' . $regex . '$#';
        
        return preg_match($regex, $route);
    }
    
    /**
     * Obter ação baseada no método HTTP
     */
    private static function getActionForMethod(string $method): string
    {
        switch (strtoupper($method)) {
            case 'GET':
                return 'view';
            case 'POST':
                return 'edit';
            case 'PUT':
            case 'PATCH':
                return 'edit';
            case 'DELETE':
                return 'delete';
            default:
                return 'view';
        }
    }
    
    /**
     * Middleware para verificar permissões
     */
    public static function handle(string $route, string $method = 'GET'): void
    {
        if (!self::checkRoutePermission($route, $method)) {
            // Se é uma requisição AJAX, retornar JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Acesso negado - você não tem permissão para acessar esta funcionalidade',
                    'redirect' => '/login'
                ]);
                exit;
            }
            
            // Se não está logado, redirecionar para login
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }
            
            // Se está tentando acessar a raiz (/) e não tem permissão para dashboard,
            // redirecionar para o primeiro módulo que tem permissão
            if ($route === '/' || $route === '') {
                $redirectUrl = self::findFirstAllowedModule($_SESSION['user_id']);
                if ($redirectUrl) {
                    header('Location: ' . $redirectUrl);
                    exit;
                }
            }
            
            // Se está logado mas não tem permissão, mostrar erro 403
            http_response_code(403);
            $firstAllowedUrl = self::findFirstAllowedModule($_SESSION['user_id']);
            $dashboardButton = $firstAllowedUrl ? 
                '<a href="' . $firstAllowedUrl . '" class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">Ir para Módulos Permitidos</a>' :
                '<a href="/logout" class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">Fazer Logout</a>';
            
            echo '
            <!DOCTYPE html>
            <html lang="pt-br">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Acesso Negado - SGQ OTI DJ</title>
                <script src="https://cdn.tailwindcss.com"></script>
            </head>
            <body class="bg-gray-100 flex items-center justify-center min-h-screen">
                <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-red-500 text-6xl mb-4">🚫</div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Acesso Negado</h1>
                    <p class="text-gray-600 mb-6">Você não tem permissão para acessar esta funcionalidade.</p>
                    <div class="space-y-2">
                        ' . $dashboardButton . '
                        <a href="/logout" class="block w-full bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                            Fazer Logout
                        </a>
                    </div>
                </div>
            </body>
            </html>';
            exit;
        }
    }
    
    /**
     * Encontrar o primeiro módulo que o usuário tem permissão
     */
    private static function findFirstAllowedModule(int $userId): ?string
    {
        // Lista de módulos em ordem de prioridade
        $moduleUrls = [
            'toners_cadastro' => '/toners/cadastro',
            '5w2h_planos' => '/5w2h',
            'homologacoes' => '/homologacoes',
            'amostragens' => '/amostragens',
            'garantias' => '/garantias',
            'registros_fornecedores' => '/registros/fornecedores',
            'registros_parametros' => '/registros/parametros',
            'configuracoes_gerais' => '/configuracoes',
            'profile' => '/profile',
        ];
        
        foreach ($moduleUrls as $module => $url) {
            if (PermissionService::hasPermission($userId, $module, 'view')) {
                return $url;
            }
        }
        
        return null;
    }
}
