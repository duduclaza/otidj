<?php

namespace App\Controllers;

use App\Config\Database;
use App\Controllers\AuthController;
use App\Services\PermissionService;
use App\Services\MasterUserService;

class ProfilesController
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Show profiles management page
     */
    public function index()
    {
        AuthController::requireAdmin();
        
        // Check if it's an AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            try {
                $stmt = $this->db->prepare("
                    SELECT p.*, 
                           COUNT(u.id) as users_count
                    FROM profiles p 
                    LEFT JOIN users u ON p.id = u.profile_id 
                    GROUP BY p.id 
                    ORDER BY p.is_admin DESC, p.name ASC
                ");
                $stmt->execute();
                $profiles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                // Ocultar perfil Super Administrador para quem não é MasterUser (proteção server-side)
                if (!\App\Services\MasterUserService::isMasterUser()) {
                    $profiles = array_values(array_filter($profiles, function($p){
                        return strtolower((string)($p['name'] ?? '')) !== 'super administrador';
                    }));
                }
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'profiles' => $profiles
                ]);
                return;
            } catch (\Exception $e) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
        }
        
        // Regular page load
        try {
            $title = 'Gerenciar Perfis - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/profiles.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            $error = 'Erro ao carregar perfis: ' . $e->getMessage();
            $title = 'Erro - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/profiles.php';
            include __DIR__ . '/../../views/layouts/main.php';
        }
    }
    
    /**
     * Create new profile
     */
    public function create()
    {
        // Clean output buffer to prevent HTML mixing with JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        AuthController::requireAdmin();
        
        // Set JSON headers
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $isDefault = isset($_POST['is_default']) ? 1 : 0;
            $permissions = $_POST['permissions'] ?? [];
            
            if (empty($name)) {
                echo json_encode(['success' => false, 'message' => 'Nome do perfil é obrigatório']);
                exit;
            }
            
            // Check if profile name already exists
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM profiles WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Este nome de perfil já existe']);
                exit;
            }
            
            // If setting as default, remove default from other profiles
            if ($isDefault) {
                $this->db->prepare("UPDATE profiles SET is_default = 0")->execute();
            }
            
            // Create profile
            $stmt = $this->db->prepare("INSERT INTO profiles (name, description, is_default) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $isDefault]);
            $profileId = $this->db->lastInsertId();
            
            // Save permissions
            $this->saveProfilePermissions($profileId, $permissions);
            
            echo json_encode(['success' => true, 'message' => 'Perfil criado com sucesso!']);
            
        } catch (\Exception $e) {
            error_log('Error creating profile: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor. Tente novamente.']);
        }
        
        exit;
    }
    
    /**
     * Update existing profile
     */
    public function update()
    {
        // Clean output buffer to prevent HTML mixing with JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        AuthController::requireAdmin();
        
        // Set JSON headers
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            $profileId = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $isDefault = isset($_POST['is_default']) ? 1 : 0;
            $permissions = $_POST['permissions'] ?? [];
            
            if (empty($profileId) || empty($name)) {
                echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não informados']);
                exit;
            }
            
            // Check if profile exists and is not admin profile
            $stmt = $this->db->prepare("SELECT is_admin FROM profiles WHERE id = ?");
            $stmt->execute([$profileId]);
            $profile = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$profile) {
                echo json_encode(['success' => false, 'message' => 'Perfil não encontrado']);
                exit;
            }
            
            // Master User (GOD MODE) pode editar qualquer perfil
            // Outros usuários não podem editar perfil Administrador
            if ($profile['is_admin'] && !MasterUserService::isMasterUser()) {
                echo json_encode(['success' => false, 'message' => 'Apenas o usuário Master pode editar o perfil de Administrador']);
                exit;
            }
            
            // Check if name is already used by another profile
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM profiles WHERE name = ? AND id != ?");
            $stmt->execute([$name, $profileId]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Este nome de perfil já está sendo usado']);
                exit;
            }
            
            // If setting as default, remove default from other profiles
            if ($isDefault) {
                $this->db->prepare("UPDATE profiles SET is_default = 0 WHERE id != ?")->execute([$profileId]);
            }
            
            // Update profile
            $stmt = $this->db->prepare("UPDATE profiles SET name = ?, description = ?, is_default = ? WHERE id = ?");
            $stmt->execute([$name, $description, $isDefault, $profileId]);
            
            // Update permissions
            $this->saveProfilePermissions($profileId, $permissions);
            
            echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso!']);
            
        } catch (\Exception $e) {
            error_log('Error updating profile: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor. Tente novamente.']);
        }
        
        exit;
    }
    
    /**
     * Delete profile
     */
    public function delete()
    {
        // Clean output buffer to prevent HTML mixing with JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        AuthController::requireAdmin();
        
        // Set JSON headers
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            $profileId = $_POST['profile_id'] ?? '';
            
            if (empty($profileId)) {
                echo json_encode(['success' => false, 'message' => 'ID do perfil não informado']);
                exit;
            }
            
            // Check if profile exists and is not admin profile
            $stmt = $this->db->prepare("SELECT is_admin, name FROM profiles WHERE id = ?");
            $stmt->execute([$profileId]);
            $profile = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$profile) {
                echo json_encode(['success' => false, 'message' => 'Perfil não encontrado']);
                exit;
            }
            
            // Master User (GOD MODE) pode excluir qualquer perfil
            // Outros usuários não podem excluir perfil Administrador
            if ($profile['is_admin'] && !MasterUserService::isMasterUser()) {
                echo json_encode(['success' => false, 'message' => 'Apenas o usuário Master pode excluir o perfil de Administrador']);
                exit;
            }
            
            // Check if profile has users assigned
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE profile_id = ?");
            $stmt->execute([$profileId]);
            $usersCount = $stmt->fetchColumn();
            
            if ($usersCount > 0) {
                echo json_encode(['success' => false, 'message' => "Não é possível excluir este perfil pois há $usersCount usuário(s) associado(s) a ele"]);
                exit;
            }
            
            // Delete profile (permissions will be deleted automatically due to foreign key constraint)
            $stmt = $this->db->prepare("DELETE FROM profiles WHERE id = ?");
            $stmt->execute([$profileId]);
            
            echo json_encode(['success' => true, 'message' => 'Perfil excluído com sucesso!']);
            
        } catch (\Exception $e) {
            error_log('Error deleting profile: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor. Tente novamente.']);
        }
        
        exit;
    }
    
    /**
     * Get profile permissions
     */
    public function getPermissions($profileId)
    {
        AuthController::requireAdmin();
        
        // Clean output buffer to prevent HTML mixing with JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set JSON headers
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            // Get profile
            $stmt = $this->db->prepare("SELECT * FROM profiles WHERE id = ?");
            $stmt->execute([$profileId]);
            $profile = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$profile) {
                echo json_encode(['success' => false, 'message' => 'Perfil não encontrado']);
                exit;
            }
            
            // Get permissions
            $stmt = $this->db->prepare("SELECT * FROM profile_permissions WHERE profile_id = ?");
            $stmt->execute([$profileId]);
            $dbPermissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            error_log('Loading permissions for profile ' . $profileId . ': ' . json_encode($dbPermissions));
            
            $permissions = [];
            foreach ($dbPermissions as $perm) {
                $permissions[$perm['module']] = $perm;
            }
            
            echo json_encode([
                'success' => true,
                'profile' => $profile,
                'permissions' => $permissions
            ]);
            
        } catch (\Exception $e) {
            error_log('Error getting profile permissions: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor. Tente novamente.']);
        }
        
        exit;
    }
    
    /**
     * Get all profiles for dropdown
     */
    public function getProfilesList()
    {
        // Clean output buffer to prevent HTML mixing with JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set JSON headers
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            $stmt = $this->db->prepare("SELECT id, name, description, is_default FROM profiles ORDER BY is_admin DESC, name ASC");
            $stmt->execute();
            $profiles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Ocultar perfil Super Administrador para quem não é MasterUser
            if (!\App\Services\MasterUserService::isMasterUser()) {
                $profiles = array_values(array_filter($profiles, function($p){
                    return strtolower((string)($p['name'] ?? '')) !== 'super administrador';
                }));
            }
            
            echo json_encode([
                'success' => true,
                'profiles' => $profiles
            ]);
            
        } catch (\Exception $e) {
            error_log('Error getting profiles list: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor. Tente novamente.']);
        }
        
        exit;
    }
    
    /**
     * Save profile permissions
     */
    private function saveProfilePermissions($profileId, $permissions)
    {
        error_log('Saving permissions for profile ' . $profileId . ': ' . json_encode($permissions));
        
        // Delete existing permissions
        $stmt = $this->db->prepare("DELETE FROM profile_permissions WHERE profile_id = ?");
        $stmt->execute([$profileId]);
        
        // Insert new permissions
        foreach ($permissions as $module => $perms) {
            $canView = isset($perms['view']) ? 1 : 0;
            $canEdit = isset($perms['edit']) ? 1 : 0;
            $canDelete = isset($perms['delete']) ? 1 : 0;
            $canImport = isset($perms['import']) ? 1 : 0;
            $canExport = isset($perms['export']) ? 1 : 0;
            
            error_log("Saving module $module: view=$canView, edit=$canEdit, delete=$canDelete, import=$canImport, export=$canExport");
            
            $stmt = $this->db->prepare("
                INSERT INTO profile_permissions 
                (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $profileId,
                $module,
                $canView,
                $canEdit,
                $canDelete,
                $canImport,
                $canExport
            ]);
        }
        
        // Verificar se foi salvo corretamente
        $checkStmt = $this->db->prepare("SELECT module, can_view, can_edit, can_delete FROM profile_permissions WHERE profile_id = ?");
        $checkStmt->execute([$profileId]);
        $saved = $checkStmt->fetchAll(\PDO::FETCH_ASSOC);
        error_log('Permissions saved in database: ' . json_encode($saved));
        
        // Salvar permissões de abas do dashboard (se houver)
        $this->saveDashboardTabPermissions($profileId, $_POST['dashboard_tabs'] ?? []);
    }
    
    /**
     * Save dashboard tab permissions
     */
    private function saveDashboardTabPermissions($profileId, $dashboardTabs)
    {
        error_log('Saving dashboard tab permissions for profile ' . $profileId . ': ' . json_encode($dashboardTabs));
        
        try {
            // Verificar se a tabela existe
            $tableExists = $this->db->query("SHOW TABLES LIKE 'dashboard_tab_permissions'")->rowCount() > 0;
            
            if (!$tableExists) {
                error_log('Table dashboard_tab_permissions does not exist yet');
                return;
            }
            
            // Delete existing dashboard tab permissions
            $stmt = $this->db->prepare("DELETE FROM dashboard_tab_permissions WHERE profile_id = ?");
            $stmt->execute([$profileId]);
            
            // Insert new dashboard tab permissions
            $availableTabs = ['retornados', 'amostragens', 'fornecedores', 'garantias', 'melhorias'];
            
            foreach ($availableTabs as $tabName) {
                $canView = isset($dashboardTabs[$tabName]) ? 1 : 0;
                
                error_log("Saving dashboard tab $tabName: can_view=$canView");
                
                $stmt = $this->db->prepare("
                    INSERT INTO dashboard_tab_permissions 
                    (profile_id, tab_name, can_view) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $profileId,
                    $tabName,
                    $canView
                ]);
            }
            
            error_log('Dashboard tab permissions saved successfully');
            
        } catch (\Exception $e) {
            error_log('Error saving dashboard tab permissions: ' . $e->getMessage());
            // Não falhar se tabela não existir
        }
    }
    
    /**
     * Get dashboard tab permissions for a profile
     */
    public function getDashboardTabPermissions($profileId)
    {
        AuthController::requireAdmin();
        
        // Clean output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set JSON headers
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            // Verificar se a tabela existe
            $tableExists = $this->db->query("SHOW TABLES LIKE 'dashboard_tab_permissions'")->rowCount() > 0;
            
            if (!$tableExists) {
                // Retornar todas as abas como permitidas por padrão
                echo json_encode([
                    'success' => true,
                    'dashboard_tabs' => [
                        'retornados' => true,
                        'amostragens' => true,
                        'fornecedores' => true,
                        'garantias' => true,
                        'melhorias' => true
                    ]
                ]);
                exit;
            }
            
            // Get dashboard tab permissions
            $stmt = $this->db->prepare("
                SELECT tab_name, can_view 
                FROM dashboard_tab_permissions 
                WHERE profile_id = ?
            ");
            $stmt->execute([$profileId]);
            $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $dashboardTabs = [
                'retornados' => true,
                'amostragens' => true,
                'fornecedores' => true,
                'garantias' => true,
                'melhorias' => true
            ];
            
            // Se há permissões salvas, aplicar
            if (!empty($permissions)) {
                // Resetar todas para false primeiro
                foreach ($dashboardTabs as $tab => $val) {
                    $dashboardTabs[$tab] = false;
                }
                
                // Aplicar permissões salvas
                foreach ($permissions as $perm) {
                    $dashboardTabs[$perm['tab_name']] = (bool)$perm['can_view'];
                }
            }
            
            echo json_encode([
                'success' => true,
                'dashboard_tabs' => $dashboardTabs
            ]);
            
        } catch (\Exception $e) {
            error_log('Error getting dashboard tab permissions: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar permissões de abas do dashboard'
            ]);
        }
        
        exit;
    }
}
