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
    public function controleDeToners(): void { $this->render('blank', ['title' => 'Controle de Toners']); }
    public function homologacoes(): void { $this->render('blank', ['title' => 'Homologações']); }
    public function amostragens(): void { $this->render('blank', ['title' => 'Amostragens']); }
    public function garantias(): void { $this->render('blank', ['title' => 'Garantias']); }
    public function controleDeDescartes(): void { $this->render('blank', ['title' => 'Controle de Descartes']); }
    public function femea(): void { $this->render('blank', ['title' => 'FEMEA']); }
    public function popsEIts(): void { $this->render('blank', ['title' => 'POPs e ITs']); }
    public function fluxogramas(): void { $this->render('blank', ['title' => 'Fluxogramas']); }
    public function melhoriaContinua(): void { $this->render('blank', ['title' => 'Melhoria Contínua']); }
    public function controleDeRc(): void { $this->render('blank', ['title' => 'Controle de RC']); }
    public function registrosGerais(): void { $this->render('blank', ['title' => 'Registros Gerais']); }
}
