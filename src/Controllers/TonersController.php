<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class TonersController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
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

            // Get pagination parameters
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 100;
            $offset = ($page - 1) * $perPage;
            
            // Get total count for pagination
            $countStmt = $this->db->query('SELECT COUNT(*) FROM retornados');
            $totalRecords = $countStmt->fetchColumn();
            $totalPages = ceil($totalRecords / $perPage);
            
            // Get paginated retornados for grid
            $stmt = $this->db->prepare('
                SELECT id, modelo, codigo_cliente, usuario, filial, destino, 
                       data_registro, modelo_cadastrado, valor_calculado, observacao
                FROM retornados 
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset
            ');
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $retornados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->render('toners/retornados', [
                'title' => 'Registro de Retornados',
                'toners' => $toners,
                'filiais' => $filiais,
                'retornados' => $retornados,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_records' => $totalRecords,
                    'per_page' => $perPage,
                    'has_prev' => $page > 1,
                    'has_next' => $page < $totalPages
                ]
            ]);
        } catch (\PDOException $e) {
            $this->render('toners/retornados', [
                'title' => 'Registro de Retornados',
                'toners' => [],
                'filiais' => [],
                'retornados' => [],
                'pagination' => [
                    'current_page' => 1,
                    'total_pages' => 1,
                    'total_records' => 0,
                    'per_page' => 100,
                    'has_prev' => false,
                    'has_next' => false
                ],
                'error' => 'Erro ao carregar dados: ' . $e->getMessage()
            ]);
        }
    }

    public function getTonerData(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
        
        // Se não há parâmetro modelo, retorna todos os toners para dropdown
        $modelo = $_GET['modelo'] ?? '';
        
        if (empty($modelo)) {
            try {
                // Tentar conectar ao banco
                if (!$this->db) {
                    echo json_encode(['error' => 'Conexão com banco não disponível']);
                    return;
                }
                
                $stmt = $this->db->query('SELECT id, modelo, gramatura, peso_cheio, peso_vazio, preco_toner as valor, capacidade_folhas as rendimento FROM toners ORDER BY modelo');
                $toners = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Log para debug
                error_log('API /api/toner: Retornando ' . count($toners) . ' toners');
                
                echo json_encode($toners);
                return;
            } catch (\PDOException $e) {
                error_log('Erro ao buscar toners: ' . $e->getMessage());
                
                // Retornar dados mock para teste local
                $mockToners = [
                    ['id' => 1, 'modelo' => 'HP CF280A', 'gramatura' => 300, 'peso_cheio' => 350, 'peso_vazio' => 50, 'valor' => 89.90, 'rendimento' => 2700],
                    ['id' => 2, 'modelo' => 'HP CE285A', 'gramatura' => 280, 'peso_cheio' => 330, 'peso_vazio' => 50, 'valor' => 79.90, 'rendimento' => 1600],
                    ['id' => 3, 'modelo' => 'Canon 728', 'gramatura' => 250, 'peso_cheio' => 300, 'peso_vazio' => 50, 'valor' => 69.90, 'rendimento' => 2100]
                ];
                
                error_log('API /api/toner: Usando dados mock - ' . count($mockToners) . ' toners');
                echo json_encode($mockToners);
                return;
            }
        }

        // Se há parâmetro modelo, retorna dados específicos do toner
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
            // Log dos dados recebidos para debug
            error_log('POST data received: ' . print_r($_POST, true));
            
            $modelo = trim($_POST['modelo'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');
            $filial = trim($_POST['filial'] ?? '');
            $codigo_cliente = trim($_POST['codigo_cliente'] ?? '');
            $modo = trim($_POST['modo'] ?? '');
            $peso_retornado = $_POST['peso_retornado'] ?? null;
            $percentual_chip = $_POST['percentual_chip'] ?? null;
            $destino = trim($_POST['destino'] ?? '');
            $observacao = trim($_POST['observacao'] ?? '');
            $data_registro = $_POST['data_registro'] ?? date('Y-m-d');

            // Debug dos campos
            error_log("Validação - modelo: '$modelo', usuario: '$usuario', filial: '$filial', codigo_cliente: '$codigo_cliente', modo: '$modo', destino: '$destino'");

            // Validate required fields
            if (empty($modelo) || empty($usuario) || empty($filial) || empty($codigo_cliente) || empty($modo) || empty($destino)) {
                $missing = [];
                if (empty($modelo)) $missing[] = 'modelo';
                if (empty($usuario)) $missing[] = 'usuario';
                if (empty($filial)) $missing[] = 'filial';
                if (empty($codigo_cliente)) $missing[] = 'codigo_cliente';
                if (empty($modo)) $missing[] = 'modo';
                if (empty($destino)) $missing[] = 'destino';
                
                error_log('Campos faltando: ' . implode(', ', $missing));
                echo json_encode(['success' => false, 'message' => 'Campos obrigatórios faltando: ' . implode(', ', $missing)]);
                return;
            }

            // Check if modelo exists in toners table
            $stmt = $this->db->prepare('SELECT peso_cheio, peso_vazio, gramatura, capacidade_folhas, custo_por_folha FROM toners WHERE id = :modelo');
            $stmt->execute([':modelo' => $modelo]);
            $tonerData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $modelo_cadastrado = $tonerData ? 1 : 0;
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
                                      destino, valor_calculado, observacao, data_registro) 
                VALUES (:modelo, :modelo_cadastrado, :usuario, :filial, :codigo_cliente, :modo, 
                        :peso_retornado, :percentual_chip, :gramatura_existente, :percentual_restante, 
                        :destino, :valor_calculado, :observacao, :data_registro)
            ');
            
            $stmt->execute([
                ':modelo' => $modelo,
                ':modelo_cadastrado' => (int)$modelo_cadastrado,
                ':usuario' => $usuario,
                ':filial' => $filial,
                ':codigo_cliente' => $codigo_cliente,
                ':modo' => $modo,
                ':peso_retornado' => $peso_retornado ?: null,
                ':percentual_chip' => $percentual_chip ?: null,
                ':gramatura_existente' => $gramatura_existente ?: null,
                ':percentual_restante' => $percentual_restante ?: null,
                ':destino' => $destino,
                ':valor_calculado' => $valor_calculado,
                ':observacao' => $observacao,
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
            
            // Update existing retornados records that have this model as "não cadastrado"
            $updateStmt = $this->db->prepare('UPDATE retornados SET modelo_cadastrado = 1 WHERE modelo = :modelo AND modelo_cadastrado = 0');
            $updateStmt->execute([':modelo' => $modelo]);
            
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
            // Log para debug
            error_log('Import method called. FILES: ' . print_r($_FILES, true));
            
            if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                $error = $_FILES['excel_file']['error'] ?? 'Arquivo não enviado';
                echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo. Código: ' . $error]);
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
            $delimiters = [',', ';', "\t"];
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

    public function deleteRetornado(): void
    {
        header('Content-Type: application/json');
        
        // Get ID from URL path
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));
        $id = end($pathParts);
        
        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        
        try {
            // Check if record exists
            $stmt = $this->db->prepare('SELECT id FROM retornados WHERE id = :id');
            $stmt->execute([':id' => $id]);
            
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado']);
                return;
            }
            
            // Delete the record
            $stmt = $this->db->prepare('DELETE FROM retornados WHERE id = :id');
            $stmt->execute([':id' => $id]);
            
            echo json_encode(['success' => true, 'message' => 'Registro excluído com sucesso']);
            
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir registro: ' . $e->getMessage()]);
        }
    }

    public function importRow(): void
    {
        header('Content-Type: application/json');
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }
        
        try {
            // Debug: Log received data (only in debug mode)
            if (filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN)) {
                error_log('Import data received: ' . json_encode($input));
            }
            
            // Skip empty rows (all fields empty or just empty strings)
            $hasData = false;
            foreach ($input as $key => $value) {
                if (!empty(trim($value))) {
                    $hasData = true;
                    break;
                }
            }
            
            if (!$hasData) {
                echo json_encode(['success' => true, 'message' => 'Linha vazia ignorada']);
                return;
            }
            
            // Validate only essential fields - allow empty values for historical records
            if (empty(trim($input['modelo'] ?? ''))) {
                echo json_encode(['success' => false, 'message' => "Campo modelo é obrigatório. Dados recebidos: " . json_encode($input)]);
                return;
            }
            
            // Validate and normalize destino field
            $destino = trim($input['destino'] ?? '');
            if (empty($destino)) {
                echo json_encode(['success' => false, 'message' => "Campo destino é obrigatório. Valor recebido: '" . ($input['destino'] ?? 'null') . "'"]);
                return;
            }
            
            // Normalize destino values to match database format
            $destinoNormalized = strtolower($destino);
            $destinoMap = [
                'uso interno' => 'uso_interno',
                'uso_interno' => 'uso_interno',
                'descarte' => 'descarte',
                'estoque' => 'estoque',
                'garantia' => 'garantia'
            ];
            
            if (!isset($destinoMap[$destinoNormalized])) {
                echo json_encode(['success' => false, 'message' => "Destino inválido: '$destino'. Use: descarte, estoque, uso interno ou garantia"]);
                return;
            }
            
            $destino = $destinoMap[$destinoNormalized];
            
            // Check if modelo exists in toners table
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM toners WHERE modelo = :modelo');
            $stmt->execute([':modelo' => $input['modelo']]);
            $modeloCadastrado = $stmt->fetchColumn() > 0;
            
            // Parse and validate date - prioritize DD/MM/YYYY format
            $dataRegistro = $input['data_registro'] ?? date('Y-m-d');
            
            // First try DD/MM/YYYY format (Brazilian standard)
            $date = \DateTime::createFromFormat('d/m/Y', $dataRegistro);
            if ($date) {
                $dataRegistro = $date->format('Y-m-d');
            } else {
                // Try YYYY-MM-DD format as fallback
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataRegistro)) {
                    // If neither format works, use current date
                    $dataRegistro = date('Y-m-d');
                }
            }
            
            // Insert record - modo is required by table schema
            $stmt = $this->db->prepare('
                INSERT INTO retornados (modelo, modelo_cadastrado, usuario, filial, codigo_cliente, 
                                      destino, valor_calculado, data_registro, modo, peso_retornado, 
                                      percentual_chip, gramatura_existente, percentual_restante) 
                VALUES (:modelo, :modelo_cadastrado, :usuario, :filial, :codigo_cliente, 
                        :destino, :valor_calculado, :data_registro, :modo, :peso_retornado, 
                        :percentual_chip, :gramatura_existente, :percentual_restante)
            ');
            
            $result = $stmt->execute([
                ':modelo' => $input['modelo'],
                ':modelo_cadastrado' => $modeloCadastrado ? 1 : 0,
                ':usuario' => $input['usuario'] ?: 'N/A',
                ':filial' => $input['filial'] ?: 'N/A',
                ':codigo_cliente' => $input['codigo_cliente'] ?: 'N/A',
                ':destino' => $input['destino'],
                ':valor_calculado' => $input['valor_calculado'] ?? 0,
                ':data_registro' => $dataRegistro,
                ':modo' => 'peso',
                ':peso_retornado' => null,
                ':percentual_chip' => null,
                ':gramatura_existente' => null,
                ':percentual_restante' => null
            ]);
            
            if (!$result) {
                echo json_encode(['success' => false, 'message' => 'Erro ao inserir registro no banco de dados']);
                return;
            }
            
            $message = 'Registro importado com sucesso';
            if (!$modeloCadastrado) {
                $message .= ' (modelo não cadastrado)';
            }
            
            echo json_encode(['success' => true, 'message' => $message]);
            
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao importar: ' . $e->getMessage()]);
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        $layout = __DIR__ . '/../../views/layouts/main.php';
        include $layout;
    }
}
