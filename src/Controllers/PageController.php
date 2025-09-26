<?php
namespace App\Controllers;

class PageController
{
    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        $layout = __DIR__ . '/../../views/layouts/main.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($view);
            return;
        }
        include $layout;
    }

    public function home(): void { $this->render('home', ['title' => 'Início']); }
    // Módulos com páginas "Em Breve" - ainda não implementados
    public function homologacoes(): void { $this->render('coming-soon', ['title' => 'Homologações']); }
    public function fluxogramas(): void { $this->render('coming-soon', ['title' => 'Fluxogramas']); }
    public function controleDeRc(): void { $this->render('coming-soon', ['title' => 'Controle de RC']); }
    
    // Módulos já implementados - estes métodos não são mais usados
    // public function controleDeToners(): void { $this->render('blank', ['title' => 'Controle de Toners']); }
    // public function amostragens(): void { $this->render('blank', ['title' => 'Amostragens']); }
    // public function garantias(): void { $this->render('blank', ['title' => 'Garantias']); }
    // public function controleDeDescartes(): void { $this->render('blank', ['title' => 'Controle de Descartes']); }
    // public function femea(): void { $this->render('blank', ['title' => 'FEMEA']); }
    // public function popsEIts(): void { $this->render('blank', ['title' => 'POPs e ITs']); }
    // public function melhoriaContinua(): void { $this->render('blank', ['title' => 'Melhoria Contínua']); }
    // public function registrosGerais(): void { $this->render('blank', ['title' => 'Registros Gerais']); }
}
