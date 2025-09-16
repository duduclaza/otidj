<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class TonersController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function cadastro(): void
    {
        try {
            $toners = $this->db->query('SELECT * FROM toners ORDER BY modelo')->fetchAll();
        } catch (\PDOException $e) {
            $toners = [];
        }
        $this->render('toners/cadastro', ['title' => 'Cadastro de Toners', 'toners' => $toners]);
    }

    public function retornados(): void
    {
        try {
            // Get all toners for modelo dropdown
            $stmt = $this->db->query('SELECT modelo FROM toners ORDER BY modelo');
            $toners = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Get all filiais for filial dropdown
            $stmt = $this->db->query('SELECT nome FROM filiais ORDER BY nome');
            $filiais = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Get all retornados for grid
            $stmt = $this->db->query('
                SELECT id, modelo, codigo_cliente, usuario, filial, destino, 
                       data_registro, modelo_cadastrado, valor_calculado
                FROM retornados 
                ORDER BY created_at DESC
            ');
            $retornados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->render('toners/retornados', [
                'title' => 'Registro de Retornados',
                'toners' => $toners,
                'filiais' => $filiais,
                'retornados' => $retornados
            ]);
        } catch (\PDOException $e) {
            $this->render('toners/retornados', [
                'title' => 'Registro de Retornados',
                'toners' => [],
                'filiais' => [],
                'retornados' => [],
                'error' => 'Erro ao carregar dados: ' . $e->getMessage()
            ]);
        }
    }

    public function getTonerData(): void
    {
        header('Content-Type: application/json');
        
        $modelo = $_GET['modelo'] ?? '';
        
        if (empty($modelo)) {
            echo json_encode(['success' => false, 'error' => 'Modelo não informado']);
            return;
        }

        try {
            $stmt = $this->db->prepare('SELECT gramatura, peso_vazio, preco_toner as preco FROM toners WHERE modelo = ?');
            $stmt->execute([$modelo]);
            $toner = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($toner) {
                echo json_encode([
                    'success' => true,
                    'toner' => [
                        'gramatura' => (float)$toner['gramatura'],
                        'peso_vazio' => (float)$toner['peso_vazio'],
                        'preco' => (float)$toner['preco']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Toner não encontrado']);
            }
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Erro ao buscar dados do toner']);
        }
    }

    public function getParameters(): void
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->query('SELECT nome, faixa_min as percentual_min, faixa_max as percentual_max, orientacao FROM parametros_retornados ORDER BY faixa_min');
            $parameters = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'parameters' => $parameters
            ]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Erro ao buscar parâmetros']);
        }
    }

    public function storeRetornado(): void
    {
        header('Content-Type: application/json');
        
        try {
            $modelo = trim($_POST['modelo'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');
            $filial = trim($_POST['filial'] ?? '');
            $codigo_cliente = trim($_POST['codigo_cliente'] ?? '');
            $modo = trim($_POST['modo'] ?? '');
            $peso_retornado = $_POST['peso_retornado'] ?? null;
            $percentual_chip = $_POST['percentual_chip'] ?? null;
            $destino = trim($_POST['destino'] ?? '');
            $data_registro = $_POST['data_registro'] ?? date('Y-m-d');

            // Validate required fields
            if (empty($modelo) || empty($usuario) || empty($filial) || empty($codigo_cliente) || empty($modo) || empty($destino)) {
                echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos']);
                return;
            }

            // Check if modelo exists in toners table
            $stmt = $this->db->prepare('SELECT peso_cheio, peso_vazio, gramatura, capacidade_folhas, custo_por_folha FROM toners WHERE modelo = :modelo');
            $stmt->execute([':modelo' => $modelo]);
            $tonerData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $modelo_cadastrado = $tonerData ? true : false;
            $gramatura_existente = null;
            $percentual_restante = null;
            $valor_calculado = 0.00;

            // Calculate based on mode
            if ($modo === 'peso' && $peso_retornado > 0 && $tonerData) {
                $gramatura_existente = $peso_retornado - $tonerData['peso_vazio'];
                $percentual_restante = ($gramatura_existente / $tonerData['gramatura']) * 100;
            } elseif ($modo === 'chip' && $percentual_chip > 0) {
                $percentual_restante = $percentual_chip;
                if ($tonerData) {
                    $gramatura_existente = ($percentual_chip / 100) * $tonerData['gramatura'];
                }
            }

            // Calculate value if destino is estoque
            if ($destino === 'estoque' && $percentual_restante > 0 && $tonerData) {
                $folhas_restantes = ($percentual_restante / 100) * $tonerData['capacidade_folhas'];
                $valor_calculado = $folhas_restantes * $tonerData['custo_por_folha'];
            }

            // Insert into database
            $stmt = $this->db->prepare('
                INSERT INTO retornados (modelo, modelo_cadastrado, usuario, filial, codigo_cliente, modo, 
                                      peso_retornado, percentual_chip, gramatura_existente, percentual_restante, 
                                      destino, valor_calculado, data_registro) 
                VALUES (:modelo, :modelo_cadastrado, :usuario, :filial, :codigo_cliente, :modo, 
                        :peso_retornado, :percentual_chip, :gramatura_existente, :percentual_restante, 
                        :destino, :valor_calculado, :data_registro)
            ');
            
            $stmt->execute([
                ':modelo' => $modelo,
                ':modelo_cadastrado' => $modelo_cadastrado,
                ':usuario' => $usuario,
                ':filial' => $filial,
                ':codigo_cliente' => $codigo_cliente,
                ':modo' => $modo,
                ':peso_retornado' => $peso_retornado,
                ':percentual_chip' => $percentual_chip,
                ':gramatura_existente' => $gramatura_existente,
                ':percentual_restante' => $percentual_restante,
                ':destino' => $destino,
                ':valor_calculado' => $valor_calculado,
                ':data_registro' => $data_registro
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Retornado registrado com sucesso!',
                'data' => [
                    'percentual_restante' => $percentual_restante,
                    'valor_calculado' => $valor_calculado,
                    'modelo_cadastrado' => $modelo_cadastrado
                ]
            ]);

        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar: ' . $e->getMessage()]);
        }
    }

    public function store(): void
    {
        $modelo = trim($_POST['modelo'] ?? '');
        $peso_cheio = (float)($_POST['peso_cheio'] ?? 0);
        $peso_vazio = (float)($_POST['peso_vazio'] ?? 0);
        $capacidade_folhas = (int)($_POST['capacidade_folhas'] ?? 0);
        $preco_toner = (float)($_POST['preco_toner'] ?? 0);
        $cor = $_POST['cor'] ?? '';
        $tipo = $_POST['tipo'] ?? '';

        if ($modelo === '' || $peso_cheio <= 0 || $peso_vazio <= 0 || $capacidade_folhas <= 0 || $preco_toner <= 0 || $cor === '' || $tipo === '') {
            flash('error', 'Todos os campos são obrigatórios e devem ter valores válidos.');
            redirect('/toners/cadastro');
            return;
        }

        if ($peso_cheio <= $peso_vazio) {
            flash('error', 'O peso cheio deve ser maior que o peso vazio.');
            redirect('/toners/cadastro');
            return;
        }

        try {
            $stmt = $this->db->prepare('INSERT INTO toners (modelo, peso_cheio, peso_vazio, capacidade_folhas, preco_toner, cor, tipo) VALUES (:modelo, :peso_cheio, :peso_vazio, :capacidade_folhas, :preco_toner, :cor, :tipo)');
            $stmt->execute([
                ':modelo' => $modelo,
                ':peso_cheio' => $peso_cheio,
                ':peso_vazio' => $peso_vazio,
                ':capacidade_folhas' => $capacidade_folhas,
                ':preco_toner' => $preco_toner,
                ':cor' => $cor,
                ':tipo' => $tipo
            ]);
            flash('success', 'Toner cadastrado com sucesso.');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao cadastrar toner: ' . $e->getMessage());
        }

        redirect('/toners/cadastro');
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $modelo = trim($_POST['modelo'] ?? '');
        $peso_cheio = (float)($_POST['peso_cheio'] ?? 0);
        $peso_vazio = (float)($_POST['peso_vazio'] ?? 0);
        $capacidade_folhas = (int)($_POST['capacidade_folhas'] ?? 0);
        $preco_toner = (float)($_POST['preco_toner'] ?? 0);
        $cor = $_POST['cor'] ?? '';
        $tipo = $_POST['tipo'] ?? '';

        if ($id <= 0 || $modelo === '' || $peso_cheio <= 0 || $peso_vazio <= 0 || $capacidade_folhas <= 0 || $preco_toner <= 0 || $cor === '' || $tipo === '') {
            flash('error', 'Dados inválidos.');
            redirect('/toners/cadastro');
            return;
        }

        if ($peso_cheio <= $peso_vazio) {
            flash('error', 'O peso cheio deve ser maior que o peso vazio.');
            redirect('/toners/cadastro');
            return;
        }

        try {
            $stmt = $this->db->prepare('UPDATE toners SET modelo = :modelo, peso_cheio = :peso_cheio, peso_vazio = :peso_vazio, capacidade_folhas = :capacidade_folhas, preco_toner = :preco_toner, cor = :cor, tipo = :tipo WHERE id = :id');
            $stmt->execute([
                ':modelo' => $modelo,
                ':peso_cheio' => $peso_cheio,
                ':peso_vazio' => $peso_vazio,
                ':capacidade_folhas' => $capacidade_folhas,
                ':preco_toner' => $preco_toner,
                ':cor' => $cor,
                ':tipo' => $tipo,
                ':id' => $id
            ]);
            flash('success', 'Toner atualizado com sucesso.');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao atualizar toner: ' . $e->getMessage());
        }

        redirect('/toners/cadastro');
    }

    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'ID inválido.');
            redirect('/toners/cadastro');
            return;
        }

        try {
            $stmt = $this->db->prepare('DELETE FROM toners WHERE id = :id');
            $stmt->execute([':id' => $id]);
            flash('success', 'Toner excluído com sucesso.');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao excluir toner: ' . $e->getMessage());
        }

        redirect('/toners/cadastro');
    }

    public function import(): void
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo']);
                return;
            }

            $uploadedFile = $_FILES['excel_file']['tmp_name'];
            $originalFileName = $_FILES['excel_file']['name'];
            
            // Check file type by MIME type and extension
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $uploadedFile);
            finfo_close($finfo);
            
            $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
            
            // Accept CSV files (converted from Excel) and Excel files
            $validExtensions = ['xlsx', 'xls', 'csv'];
            $validMimeTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                'application/vnd.ms-excel', // .xls
                'text/csv', // .csv
                'text/plain', // sometimes CSV is detected as plain text
                'application/csv'
            ];
            
            if (!in_array($fileExtension, $validExtensions) && !in_array($mimeType, $validMimeTypes)) {
                echo json_encode([
                    'success' => false, 
                    'message' => "Formato de arquivo inválido. Extensão: $fileExtension, MIME: $mimeType. Use .xlsx, .xls ou .csv"
                ]);
                return;
            }

            // Read Excel data (simulate reading - in real implementation you'd use a library like PhpSpreadsheet)
            $excelData = $this->readExcelFile($uploadedFile);
            
            if (empty($excelData)) {
                echo json_encode(['success' => false, 'message' => 'Arquivo vazio ou formato inválido']);
                return;
            }

            $totalRows = count($excelData);
            $imported = 0;
            $errors = [];

            foreach ($excelData as $index => $row) {
                try {
                    // Skip header row
                    if ($index === 0) continue;
                    
                    // Skip empty rows
                    if (empty(array_filter($row))) continue;

                    $modelo = trim($row[0] ?? '');
                    $peso_cheio = (float)($row[1] ?? 0);
                    $peso_vazio = (float)($row[2] ?? 0);
                    $capacidade_folhas = (int)($row[3] ?? 0);
                    $preco_toner = (float)($row[4] ?? 0);
                    $cor = trim($row[5] ?? '');
                    $tipo = trim($row[6] ?? '');

                    // Validate required fields
                    if (empty($modelo) || $peso_cheio <= 0 || $peso_vazio <= 0 || $capacidade_folhas <= 0 || $preco_toner <= 0 || empty($cor) || empty($tipo)) {
                        $errors[] = "Linha " . ($index + 1) . ": Dados incompletos ou inválidos";
                        continue;
                    }

                    if ($peso_cheio <= $peso_vazio) {
                        $errors[] = "Linha " . ($index + 1) . ": Peso cheio deve ser maior que peso vazio";
                        continue;
                    }

                    // Validate enum values
                    if (!in_array($cor, ['Yellow', 'Magenta', 'Cyan', 'Black'])) {
                        $errors[] = "Linha " . ($index + 1) . ": Cor inválida (use: Yellow, Magenta, Cyan, Black)";
                        continue;
                    }

                    if (!in_array($tipo, ['Original', 'Compativel', 'Remanufaturado'])) {
                        $errors[] = "Linha " . ($index + 1) . ": Tipo inválido (use: Original, Compativel, Remanufaturado)";
                        continue;
                    }

                    // Insert into database
                    $stmt = $this->db->prepare('INSERT INTO toners (modelo, peso_cheio, peso_vazio, capacidade_folhas, preco_toner, cor, tipo) VALUES (:modelo, :peso_cheio, :peso_vazio, :capacidade_folhas, :preco_toner, :cor, :tipo)');
                    $stmt->execute([
                        ':modelo' => $modelo,
                        ':peso_cheio' => $peso_cheio,
                        ':peso_vazio' => $peso_vazio,
                        ':capacidade_folhas' => $capacidade_folhas,
                        ':preco_toner' => $preco_toner,
                        ':cor' => $cor,
                        ':tipo' => $tipo
                    ]);

                    $imported++;

                } catch (\PDOException $e) {
                    $errors[] = "Linha " . ($index + 1) . ": Erro no banco - " . $e->getMessage();
                }
            }

            $message = "Importação concluída! $imported registros importados";
            if (!empty($errors)) {
                $message .= ". Erros encontrados: " . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " (e mais " . (count($errors) - 3) . " erros)";
                }
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'total' => $totalRows - 1, // Exclude header
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    private function readExcelFile(string $filePath): array
    {
        $data = [];
        
        // Try to read as CSV first (most common case from our frontend conversion)
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            // Try different delimiters
            $delimiters = [',', ';', '\t'];
            $firstLine = fgets($handle);
            rewind($handle);
            
            // Detect delimiter
            $delimiter = ',';
            foreach ($delimiters as $del) {
                if (substr_count($firstLine, $del) > 0) {
                    $delimiter = $del;
                    break;
                }
            }
            
            // Read with detected delimiter
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                // Clean up the row data
                $cleanRow = array_map('trim', $row);
                $data[] = $cleanRow;
            }
            fclose($handle);
        }
        
        return $data;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        $layout = __DIR__ . '/../../views/layouts/main.php';
        include $layout;
    }
}
