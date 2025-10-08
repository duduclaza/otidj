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
            // Get ALL toners (sem paginação)
            $stmt = $this->db->query('
                SELECT * FROM toners 
                ORDER BY modelo
            ');
            $toners = $stmt->fetchAll();
            
        } catch (\PDOException $e) {
            $toners = [];
        }
        
        $this->render('toners/cadastro', [
            'title' => 'Cadastro de Toners', 
            'toners' => $toners
        ]);
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
                       data_registro, modelo_cadastrado, valor_calculado, observacao, quantidade
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

            // Check if modelo exists in toners table (prefer ID if available, fallback to name)
            $modelo_id = $_POST['modelo_id'] ?? null;
            
            if ($modelo_id) {
                // Use ID if provided
                $stmt = $this->db->prepare('SELECT peso_cheio, peso_vazio, gramatura, capacidade_folhas, custo_por_folha FROM toners WHERE id = :modelo_id');
                $stmt->execute([':modelo_id' => $modelo_id]);
                error_log('Buscando modelo por ID: ' . $modelo_id);
            } else {
                // Fallback to search by name
                $stmt = $this->db->prepare('SELECT peso_cheio, peso_vazio, gramatura, capacidade_folhas, custo_por_folha FROM toners WHERE modelo = :modelo');
                $stmt->execute([':modelo' => $modelo]);
                error_log('Buscando modelo por nome: ' . $modelo);
            }
            
            $tonerData = $stmt->fetch(PDO::FETCH_ASSOC);
            $modelo_cadastrado = $tonerData ? 1 : 0;
            
            // Log para debug
            error_log('Verificando modelo cadastrado: ' . $modelo . ' (ID: ' . ($modelo_id ?: 'N/A') . ') - Encontrado: ' . ($modelo_cadastrado ? 'SIM' : 'NÃO'));
            $gramatura_existente = null;
            $percentual_restante = null;
            $valor_calculado = 0.00;

            // Calculate based on mode
            if ($modo === 'peso' && $peso_retornado > 0 && $tonerData) {
                $gramatura_existente = max(0, $peso_retornado - $tonerData['peso_vazio']);
                $percentual_restante = $tonerData['gramatura'] > 0 ? 
                    min(100, max(0, ($gramatura_existente / $tonerData['gramatura']) * 100)) : 0;
                    
                error_log('Cálculo por peso: Peso=' . $peso_retornado . 'g, Vazio=' . $tonerData['peso_vazio'] . 'g, Gramatura=' . $gramatura_existente . 'g, Percentual=' . $percentual_restante . '%');
            } elseif ($modo === 'chip' && $percentual_chip >= 0) {
                $percentual_restante = max(0, min(100, $percentual_chip));
                if ($tonerData && $tonerData['gramatura'] > 0) {
                    $gramatura_existente = ($percentual_restante / 100) * $tonerData['gramatura'];
                }
                
                error_log('Cálculo por chip: Percentual=' . $percentual_restante . '%, Gramatura=' . ($gramatura_existente ?? 'N/A') . 'g');
            }

            // Calculate value if destino is estoque
            if ($destino === 'estoque' && $percentual_restante > 0 && $tonerData) {
                $capacidade_folhas = $tonerData['capacidade_folhas'] ?? 0;
                $custo_por_folha = $tonerData['custo_por_folha'] ?? 0;
                
                if ($capacidade_folhas > 0 && $custo_por_folha > 0) {
                    $folhas_restantes = ($percentual_restante / 100) * $capacidade_folhas;
                    $valor_calculado = $folhas_restantes * $custo_por_folha;
                    
                    error_log('Cálculo de valor para estoque: ' . 
                        'Percentual: ' . $percentual_restante . '% | ' .
                        'Capacidade: ' . $capacidade_folhas . ' folhas | ' .
                        'Custo por folha: R$ ' . $custo_por_folha . ' | ' .
                        'Folhas restantes: ' . $folhas_restantes . ' | ' .
                        'Valor calculado: R$ ' . $valor_calculado
                    );
                } else {
                    error_log('Não foi possível calcular valor - dados faltando: ' .
                        'Capacidade: ' . $capacidade_folhas . ' | ' .
                        'Custo: ' . $custo_por_folha
                    );
                }
            } else {
                error_log('Cálculo de valor não executado - Condições: ' .
                    'Destino: ' . $destino . ' | ' .
                    'Percentual: ' . $percentual_restante . ' | ' .
                    'TonerData: ' . ($tonerData ? 'OK' : 'NULL')
                );
            }

            // Get quantidade from POST
            $quantidade = max(1, (int)($_POST['quantidade'] ?? 1));

            // Multiplicar valor calculado pela quantidade
            $valor_total = $valor_calculado * $quantidade;

            error_log('Valor final: Unitário R$ ' . number_format($valor_calculado, 2, ',', '.') . 
                      ' x ' . $quantidade . ' = R$ ' . number_format($valor_total, 2, ',', '.'));

            // Insert into database
            $stmt = $this->db->prepare('
                INSERT INTO retornados (modelo, modelo_cadastrado, usuario, filial, codigo_cliente, modo, 
                                      peso_retornado, percentual_chip, gramatura_existente, percentual_restante, 
                                      destino, valor_calculado, observacao, data_registro, quantidade) 
                VALUES (:modelo, :modelo_cadastrado, :usuario, :filial, :codigo_cliente, :modo, 
                        :peso_retornado, :percentual_chip, :gramatura_existente, :percentual_restante, 
                        :destino, :valor_calculado, :observacao, :data_registro, :quantidade)
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
                ':quantidade' => $quantidade,
                ':destino' => $destino,
                ':valor_calculado' => $valor_total,
                ':observacao' => $observacao,
                ':data_registro' => $data_registro
            ]);

            error_log('Retornado inserido - Destino: ' . $destino . ' | Quantidade: ' . $quantidade . ' | Valor Total: R$ ' . number_format($valor_total, 2, ',', '.'));

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
            error_log('POST data: ' . print_r($_POST, true));
            
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
                echo json_encode(['success' => false, 'message' => 'Arquivo vazio ou formato inválido. Verifique se o arquivo contém dados.']);
                return;
            }
            
            // Log the data structure for debugging
            error_log("Excel data structure: " . json_encode([
                'total_rows' => count($excelData),
                'first_row' => $excelData[0] ?? 'empty',
                'second_row' => $excelData[1] ?? 'empty'
            ]));

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
                    
                    // Parse numbers with better handling
                    $peso_cheio_str = trim($row[1] ?? '0');
                    $peso_vazio_str = trim($row[2] ?? '0');
                    $capacidade_folhas_str = trim($row[3] ?? '0');
                    $preco_toner_str = trim($row[4] ?? '0');
                    
                    // Convert comma to dot for decimal numbers
                    $peso_cheio = (float)str_replace(',', '.', $peso_cheio_str);
                    $peso_vazio = (float)str_replace(',', '.', $peso_vazio_str);
                    $capacidade_folhas = (int)$capacidade_folhas_str;
                    $preco_toner = (float)str_replace(',', '.', $preco_toner_str);
                    
                    $cor = trim($row[5] ?? '');
                    $tipo = trim($row[6] ?? '');

                    // Log row data for debugging
                    error_log("Processing row " . ($index + 1) . ": " . json_encode($row));

                    // Validate required fields
                    if (empty($modelo) || $peso_cheio <= 0 || $peso_vazio <= 0 || $capacidade_folhas <= 0 || $preco_toner <= 0 || empty($cor) || empty($tipo)) {
                        $errors[] = "Linha " . ($index + 1) . ": Dados incompletos ou inválidos - Modelo: '$modelo', Peso Cheio: $peso_cheio, Peso Vazio: $peso_vazio, Cap: $capacidade_folhas, Preço: $preco_toner, Cor: '$cor', Tipo: '$tipo'";
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
            // Try different delimiters - prioritize comma since frontend uses it
            $delimiters = [',', ';', "\t"];
            $firstLine = fgets($handle);
            rewind($handle);
            
            // Detect delimiter by counting occurrences
            $delimiter = ','; // Default to comma (used by frontend)
            $maxCount = 0;
            
            foreach ($delimiters as $del) {
                $count = substr_count($firstLine, $del);
                if ($count > $maxCount) {
                    $maxCount = $count;
                    $delimiter = $del;
                }
            }
            
            // Log for debugging
            error_log("Detected delimiter: '$delimiter' in first line: " . trim($firstLine));
            
            // Read with detected delimiter
            while (($row = fgetcsv($handle, 2000, $delimiter)) !== FALSE) {
                // Clean up the row data and handle empty cells
                $cleanRow = array_map(function($cell) {
                    return trim($cell ?? '');
                }, $row);
                
                // Ensure we have at least 7 columns (pad with empty strings if needed)
                while (count($cleanRow) < 7) {
                    $cleanRow[] = '';
                }
                
                $data[] = $cleanRow;
            }
            fclose($handle);
        }
        
        // Log the parsed data for debugging
        error_log("Parsed CSV data: " . json_encode(array_slice($data, 0, 3))); // First 3 rows
        
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

    public function exportRetornados(): void
    {
        try {
            // Get filter parameters
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            $search = $_GET['search'] ?? null;

            // Build query with filters
            $sql = 'SELECT * FROM retornados WHERE 1=1';
            $params = [];

            if ($dateFrom) {
                $sql .= ' AND DATE(data_registro) >= :date_from';
                $params[':date_from'] = $dateFrom;
            }

            if ($dateTo) {
                $sql .= ' AND DATE(data_registro) <= :date_to';
                $params[':date_to'] = $dateTo;
            }

            if ($search) {
                $sql .= ' AND (modelo LIKE :search OR codigo_cliente LIKE :search OR usuario LIKE :search)';
                $params[':search'] = '%' . $search . '%';
            }

            $sql .= ' ORDER BY data_registro DESC';

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $retornados = $stmt->fetchAll();

            if (empty($retornados)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Nenhum registro encontrado para exportar']);
                return;
            }

            // Generate filename with current date
            $filename = 'retornados_' . date('Y-m-d_H-i-s') . '.csv';

            // Set headers for CSV download
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');

            // Open output stream
            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8 (helps with Excel encoding)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Headers (in Portuguese)
            $headers = [
                'Modelo',
                'Código Cliente',
                'Usuário',
                'Filial',
                'Modo',
                'Peso Retornado (g)',
                'Percentual Chip (%)',
                'Quantidade',
                'Destino',
                'Valor Calculado (R$)',
                'Observação',
                'Data Registro'
            ];

            fputcsv($output, $headers, ';');

            // Add data rows
            foreach ($retornados as $retornado) {
                $row = [
                    $retornado['modelo'],
                    $retornado['codigo_cliente'],
                    $retornado['usuario'],
                    $retornado['filial'],
                    $retornado['modo'],
                    $retornado['peso_retornado'] ? number_format($retornado['peso_retornado'], 2, ',', '.') : '',
                    $retornado['percentual_chip'] ? number_format($retornado['percentual_chip'], 2, ',', '.') : '',
                    $retornado['quantidade'] ?? '1',
                    ucfirst(str_replace('_', ' ', $retornado['destino'])),
                    $retornado['valor_calculado'] ? 'R$ ' . number_format($retornado['valor_calculado'], 2, ',', '.') : '',
                    $retornado['observacao'] ?? '',
                    date('d/m/Y H:i', strtotime($retornado['data_registro']))
                ];

                fputcsv($output, $row, ';');
            }

            fclose($output);
            exit;

        } catch (\PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao exportar: ' . $e->getMessage()]);
        }
    }

    public function importRetornados(): void
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo']);
                return;
            }

            $uploadedFile = $_FILES['import_file']['tmp_name'];
            $data = $this->readCSVFile($uploadedFile);
            
            if (empty($data)) {
                echo json_encode(['success' => false, 'message' => 'Arquivo vazio ou formato inválido']);
                return;
            }

            $imported = 0;
            $errors = [];

            foreach ($data as $index => $row) {
                if ($index === 0) continue; // Skip header

                try {
                    // Validate and insert data
                    $stmt = $this->db->prepare('
                        INSERT INTO retornados (usuario, filial, codigo_cliente, modelo, modo, peso_retornado, percentual_chip, destino, observacao) 
                        VALUES (:usuario, :filial, :codigo_cliente, :modelo, :modo, :peso_retornado, :percentual_chip, :destino, :observacao)
                    ');
                    
                    $stmt->execute([
                        ':usuario' => $row[2] ?? 'Importado',
                        ':filial' => $row[3] ?? 'Jundiaí',
                        ':codigo_cliente' => $row[1] ?? '',
                        ':modelo' => $row[0] ?? '',
                        ':modo' => 'peso', // Default
                        ':peso_retornado' => !empty($row[5]) ? (float)str_replace(',', '.', $row[5]) : null,
                        ':percentual_chip' => !empty($row[6]) ? (float)str_replace(',', '.', $row[6]) : null,
                        ':destino' => strtolower(str_replace(' ', '_', $row[7] ?? 'descarte')),
                        ':observacao' => $row[9] ?? ''
                    ]);

                    $imported++;
                } catch (\PDOException $e) {
                    $errors[] = "Linha " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "Importação concluída! $imported registros importados",
                'imported' => $imported,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    private function readCSVFile(string $filePath): array
    {
        $data = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $data[] = array_map('trim', $row);
            }
            fclose($handle);
        }
        return $data;
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

    public function exportExcel(): void
    {
        try {
            // Get all toners for export
            $stmt = $this->db->query('
                SELECT 
                    modelo,
                    peso_cheio,
                    peso_vazio,
                    gramatura,
                    capacidade_folhas,
                    preco_toner,
                    gramatura_por_folha,
                    custo_por_folha,
                    cor,
                    tipo,
                    created_at,
                    updated_at
                FROM toners 
                ORDER BY modelo
            ');
            $toners = $stmt->fetchAll();

            if (empty($toners)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Nenhum toner encontrado para exportar']);
                return;
            }

            // Generate filename with current date
            $filename = 'toners_cadastro_' . date('Y-m-d_H-i-s') . '.csv';

            // Set headers for CSV download
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');

            // Open output stream
            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8 (helps with Excel encoding)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Headers (in Portuguese)
            $headers = [
                'Modelo',
                'Peso Cheio (g)',
                'Peso Vazio (g)', 
                'Gramatura (g)',
                'Capacidade Folhas',
                'Preço Toner (R$)',
                'Gramatura por Folha (g)',
                'Custo por Folha (R$)',
                'Cor',
                'Tipo',
                'Data Cadastro',
                'Última Atualização'
            ];

            fputcsv($output, $headers, ';'); // Using semicolon for better Excel compatibility

            // Add data rows
            foreach ($toners as $toner) {
                $row = [
                    $toner['modelo'],
                    number_format($toner['peso_cheio'], 2, ',', '.'),
                    number_format($toner['peso_vazio'], 2, ',', '.'),
                    number_format($toner['gramatura'], 2, ',', '.'),
                    number_format($toner['capacidade_folhas'], 0, ',', '.'),
                    'R$ ' . number_format($toner['preco_toner'], 2, ',', '.'),
                    number_format($toner['gramatura_por_folha'], 4, ',', '.'),
                    'R$ ' . number_format($toner['custo_por_folha'], 4, ',', '.'),
                    $toner['cor'],
                    $toner['tipo'],
                    date('d/m/Y H:i', strtotime($toner['created_at'])),
                    date('d/m/Y H:i', strtotime($toner['updated_at']))
                ];

                fputcsv($output, $row, ';');
            }

            fclose($output);
            exit;

        } catch (\PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao exportar: ' . $e->getMessage()]);
        }
    }

    public function exportExcelAdvanced(): void
    {
        try {
            // Get all toners with additional statistics
            $stmt = $this->db->query('
                SELECT 
                    t.*,
                    COALESCE(r.total_retornados, 0) as total_retornados,
                    COALESCE(r.valor_total_recuperado, 0) as valor_total_recuperado
                FROM toners t
                LEFT JOIN (
                    SELECT 
                        modelo,
                        SUM(quantidade) as total_retornados,
                        SUM(valor_calculado) as valor_total_recuperado
                    FROM retornados 
                    WHERE modelo_cadastrado = 1
                    GROUP BY modelo
                ) r ON t.modelo = r.modelo
                ORDER BY t.modelo
            ');
            $toners = $stmt->fetchAll();

            if (empty($toners)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Nenhum toner encontrado para exportar']);
                return;
            }

            // Generate filename with current date
            $filename = 'toners_relatorio_completo_' . date('Y-m-d_H-i-s') . '.csv';

            // Set headers for CSV download
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');

            // Open output stream
            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Headers (Extended)
            $headers = [
                'Modelo',
                'Peso Cheio (g)',
                'Peso Vazio (g)', 
                'Gramatura (g)',
                'Capacidade Folhas',
                'Preço Toner (R$)',
                'Gramatura por Folha (g)',
                'Custo por Folha (R$)',
                'Cor',
                'Tipo',
                'Total Retornados',
                'Valor Total Recuperado (R$)',
                'Data Cadastro',
                'Última Atualização'
            ];

            fputcsv($output, $headers, ';');

            // Add summary row
            $totalToners = count($toners);
            $totalRetornados = array_sum(array_column($toners, 'total_retornados'));
            $valorTotalRecuperado = array_sum(array_column($toners, 'valor_total_recuperado'));

            $summaryRow = [
                "RESUMO - {$totalToners} Toners Cadastrados",
                '', '', '', '', '', '', '',
                "Total Retornados: {$totalRetornados}",
                "Valor Total: R$ " . number_format($valorTotalRecuperado, 2, ',', '.'),
                '', '', '', ''
            ];
            fputcsv($output, $summaryRow, ';');
            fputcsv($output, [''], ';'); // Empty row

            // Add data rows
            foreach ($toners as $toner) {
                $row = [
                    $toner['modelo'],
                    number_format($toner['peso_cheio'], 2, ',', '.'),
                    number_format($toner['peso_vazio'], 2, ',', '.'),
                    number_format($toner['gramatura'], 2, ',', '.'),
                    number_format($toner['capacidade_folhas'], 0, ',', '.'),
                    'R$ ' . number_format($toner['preco_toner'], 2, ',', '.'),
                    number_format($toner['gramatura_por_folha'], 4, ',', '.'),
                    'R$ ' . number_format($toner['custo_por_folha'], 4, ',', '.'),
                    $toner['cor'],
                    $toner['tipo'],
                    $toner['total_retornados'],
                    'R$ ' . number_format($toner['valor_total_recuperado'], 2, ',', '.'),
                    date('d/m/Y H:i', strtotime($toner['created_at'])),
                    date('d/m/Y H:i', strtotime($toner['updated_at']))
                ];

                fputcsv($output, $row, ';');
            }

            fclose($output);
            exit;

        } catch (\PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao exportar: ' . $e->getMessage()]);
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        $layout = __DIR__ . '/../../views/layouts/main.php';
        include $layout;
    }
    
    /**
     * API: Lista todos os toners para seleção em dropdowns
     * Usado em: Amostragens 2.0, Garantias
     */
    public function apiListToners(): void
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->query("
                SELECT 
                    id,
                    modelo as codigo,
                    modelo,
                    CONCAT(modelo, ' - ', fabricante) as nome
                FROM toners
                ORDER BY modelo
            ");
            
            $toners = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($toners);
            
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erro ao buscar toners',
                'message' => $e->getMessage()
            ]);
        }
    }
}
