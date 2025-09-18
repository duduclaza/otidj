<?php
// Debug controller para testar se o roteamento funciona

namespace App\Controllers;

class DebugSolicitacoesMelhoriasController
{
    public function index()
    {
        echo "<!DOCTYPE html>";
        echo "<html><head><title>Debug</title></head><body>";
        echo "<h1>Controller funcionando!</h1>";
        echo "<p>Se você está vendo esta mensagem, o controller está sendo chamado corretamente.</p>";
        echo "<p>Hora atual: " . date('Y-m-d H:i:s') . "</p>";
        echo "</body></html>";
    }
}
