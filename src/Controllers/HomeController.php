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
        $systemVersion = '2.7.0';
        $lastUpdate = '12/11/2025';
        
        // Últimas atualizações do sistema - apenas Melhorias e Ajustes
        $allUpdates = [
            [
                'version' => '2.7.0',
                'date' => '12/11/2025',
                'type' => 'Melhoria',
                'title' => 'Sistema Completo de Formulários NPS Personalizados',
                'description' => 'Criação de formulários personalizados com link público para resposta sem login',
                'items' => [
                    'Criação de formulários personalizados com título e descrição',
                    'Suporte a 4 tipos de pergunta: Texto, Número (0-10), Múltipla Escolha, Sim/Não',
                    'Link público gerado automaticamente para cada formulário',
                    'Respostas sem necessidade de login no sistema',
                    'Armazenamento em arquivos JSON (pasta storage/formularios/)',
                    'Gerenciamento completo: criar, editar, ativar/desativar, excluir',
                    'Proteção: formulários com respostas NÃO podem ser excluídos',
                    'Visualização de todas as respostas recebidas',
                    'Cálculo automático de NPS Score (Promotores, Neutros, Detratores)',
                    'Estatísticas em tempo real com gráficos visuais',
                    'Coleta de dados do respondente: nome, email, IP, User-Agent',
                    'Copiar link público com um clique',
                    'Interface responsiva para desktop e mobile',
                    'Múltiplos formulários por usuário',
                    'Admin visualiza todos os formulários do sistema',
                    'Documentação completa em SISTEMA_NPS_README.md'
                ]
            ],
            [
                'version' => '2.6.4',
                'date' => '12/11/2025',
                'type' => 'Melhoria',
                'title' => 'Sistema de Permissões por Aba do Dashboard',
                'description' => 'Implementado controle granular de visualização das abas do dashboard por perfil de usuário',
                'items' => [
                    'Nova tabela dashboard_tab_permissions para armazenar permissões',
                    'Interface de configuração em Gerenciar Perfis com toggles por aba',
                    '5 abas controláveis: Retornados, Amostragens, Fornecedores, Garantias, Melhorias',
                    'Ocultação automática de abas sem permissão no dashboard',
                    'Ocultação de botões E conteúdo das abas restritas',
                    'Fallback seguro: libera todas as abas se não houver configuração',
                    'Administradores sempre veem todas as abas (bypass de permissões)',
                    'API getDashboardTabPermissions para carregar permissões no frontend',
                    'Método saveDashboardTabPermissions para salvar ao criar/editar perfil',
                    'Permissões padrão configuradas para perfis existentes via SQL',
                    'Graceful: sistema funciona mesmo se tabela não existir',
                    'Mensagem amigável quando usuário não tem nenhuma aba permitida',
                    'Documentação completa em DASHBOARD_ABAS_PERMISSOES_README.md',
                    'Script SQL de instalação: SQL_DASHBOARD_ABAS_PERMISSOES.sql',
                    'Logs detalhados para debugging e monitoramento'
                ]
            ],
            [
                'version' => '2.6.3',
                'date' => '07/11/2025',
                'type' => 'Melhoria',
                'title' => 'Paginação em Melhoria Contínua 2.0',
                'description' => 'Sistema de paginação completo implementado no módulo Melhoria Contínua 2.0 com seletor de registros',
                'items' => [
                    'Seletor de registros por página: 10, 50 ou 100 melhorias',
                    'Navegação completa com botões Primeira, Anterior, Próxima e Última',
                    'Contador de registros exibindo intervalo atual',
                    'Paginação diferenciada para Admin (vê todas) e Usuário (vê apenas suas melhorias)',
                    'Queries COUNT otimizadas com DISTINCT para contagem precisa',
                    'LIMIT e OFFSET com bindValue PDO::PARAM_INT',
                    'Controles de paginação acima e abaixo da tabela',
                    'Mensagem amigável quando não há melhorias',
                    'Função JavaScript alterarPorPagina() integrada',
                    'Performance otimizada: carrega apenas registros da página atual',
                    'Interface consistente com Amostragens 2.0',
                    'Responsivo para desktop e mobile'
                ]
            ],
            [
                'version' => '2.6.2',
                'date' => '07/11/2025',
                'type' => 'Melhoria',
                'title' => 'Paginação Completa em Amostragens 2.0',
                'description' => 'Implementado sistema de paginação no grid de Amostragens 2.0 com seletor de registros por página',
                'items' => [
                    'Seletor de registros por página: 10, 50 ou 100 amostragens',
                    'Navegação completa: Primeira, Anterior, números de página, Próxima, Última',
                    'Contador de registros: "Mostrando X até Y de Z registros"',
                    'Paginação mantém todos os filtros ativos ao navegar',
                    'Controles de paginação acima e abaixo da tabela',
                    'Interface responsiva para desktop e mobile',
                    'Página atual destacada em azul',
                    'Exibição de 5 números de página por vez',
                    'Mensagem amigável quando não há registros',
                    'Performance otimizada: carrega apenas registros da página atual',
                    'Query COUNT para total de registros',
                    'LIMIT e OFFSET para paginação no SQL',
                    'Função JavaScript para alterar quantidade por página',
                    'Padrão de 10 registros por página'
                ]
            ],
            [
                'version' => '2.6.1',
                'date' => '08/10/2025',
                'type' => 'Melhoria',
                'title' => 'Dashboard de Qualidade de Fornecedores',
                'description' => 'Nova aba no Dashboard comparando Amostragens 2.0 com Garantias para análise de qualidade dos fornecedores',
                'items' => [
                    'Nova aba "Fornecedores" no Dashboard com análise completa de qualidade',
                    'Cálculo automático de % de qualidade: (Comprados - Garantias) / Comprados × 100',
                    'Gráfico de barras com ranking de fornecedores (do pior para o melhor)',
                    'Gráficos de pizza comparando comprados vs garantias (Toners, Máquinas, Peças)',
                    'Tabela detalhada com qualidade por tipo de produto e geral',
                    'Filtros por filial, origem (multi-seleção), e período',
                    'Correção: Garantias agora somam quantidades (SUM) ao invés de contar registros (COUNT)',
                    'Formulário de Garantias corrigido para salvar tipo_produto automaticamente',
                    'Queries otimizadas comparando amostragens_2 com garantias_itens',
                    'Sistema de cores para qualidade (Verde: ≥95%, Amarelo: ≥80%, Vermelho: <70%)',
                    'Documentação completa em test_dashboard_fornecedores.html',
                    'Scripts SQL de diagnóstico e correção de dados'
                ]
            ],
            [
                'version' => '2.6.0',
                'date' => '07/10/2025',
                'type' => 'Melhoria',
                'title' => 'Sistema Completo de Notificações e Melhorias em Amostragens',
                'description' => 'Implementado sistema completo de notificações por email em Fluxogramas e melhorias visuais em Amostragens',
                'items' => [
                    'Sistema de notificações automáticas para admins em Fluxogramas',
                    'Email automático para criador quando fluxograma é aprovado/reprovado',
                    'Templates HTML profissionais com gradientes (roxo, verde, vermelho)',
                    'Permissões granulares: pode_aprovar_fluxogramas e pode_aprovar_amostragens',
                    '3 checkboxes de aprovação para admins (POPs/ITs, Fluxogramas, Amostragens)',
                    'Admins com permissão recebem emails de amostragens + responsáveis',
                    'Sistema de controle de acesso por SETOR do usuário (users.setor)',
                    'Usuários veem fluxogramas públicos + do seu setor + criados por eles',
                    'Layout facilitado para preencher resultados em Amostragens 2.0',
                    'Cards de gráficos visuais para análise de dados de amostragens',
                    'Interface otimizada para entrada rápida de dados',
                    'Visualização de resultados com gráficos profissionais',
                    'Método getUserSetor() para buscar setor do usuário',
                    'Logs detalhados para monitoramento de envios',
                    'Removidos selos BETA de Amostragens 2.0, Melhoria Contínua 2.0 e Fluxogramas'
                ]
            ],
            [
                'version' => '2.5.0',
                'date' => '06/10/2025',
                'type' => 'Melhoria',
                'title' => 'Módulo Completo de Fluxogramas',
                'description' => 'Implementado módulo completo de Fluxogramas com workflow de aprovação e controle por departamento',
                'items' => [
                    'Sistema de cadastro de títulos de fluxogramas',
                    'Upload de arquivos (PDF, PNG, JPG, PPT) até 10MB',
                    'Versionamento automático de documentos (v1, v2, v3...)',
                    'Workflow de aprovação/reprovação por administradores',
                    'Controle de acesso por departamento (público ou restrito)',
                    'Sistema de solicitação de exclusão com aprovação',
                    'Edição de documentos reprovados',
                    'Visualização protegida de fluxogramas aprovados',
                    'Log de auditoria de visualizações',
                    'Armazenamento MEDIUMBLOB no banco de dados',
                    '5 abas funcionais: Cadastro, Registros, Aprovação, Visualização, Logs',
                    'Segurança com múltiplas camadas de validação'
                ]
            ],
            [
                'version' => '2.4.1',
                'date' => '06/10/2025',
                'type' => 'Melhoria',
                'title' => 'Sistema Completo de Emails para Criadores de POPs e ITs',
                'description' => 'Implementado envio automático de emails em todas as etapas do ciclo de vida dos documentos',
                'items' => [
                    'Email automático quando POP/IT é aprovado',
                    'Email automático quando POP/IT é reprovado (com motivo)',
                    'Email automático quando solicitação de exclusão é aprovada',
                    'Email automático quando solicitação de exclusão é reprovada',
                    'Template verde para aprovações (parabéns!)',
                    'Template vermelho para reprovações (com orientações)',
                    'Motivo/observação destacado em cada email',
                    'Link direto para o sistema em todos os emails',
                    'Próximos passos explicados em reprovações',
                    'Sistema completo de notificação do ciclo de vida dos documentos'
                ]
            ],
            [
                'version' => '2.4.0',
                'date' => '06/10/2025',
                'type' => 'Melhoria',
                'title' => 'Sistema de Emails para Aprovadores de POPs e ITs',
                'description' => 'Implementado sistema completo de notificação por email para administradores aprovadores',
                'items' => [
                    'Nova permissão "Pode Aprovar POPs e ITs" no cadastro de usuários',
                    'Checkbox específico para admins que devem receber notificações',
                    'Email automático enviado quando há POPs/ITs pendentes',
                    'Template HTML profissional com gradiente roxo',
                    'Notificação apenas para admins com permissão ativada',
                    'Link direto para aba "Pendente Aprovação"',
                    'Lista de próximos passos no email',
                    'Campo no banco: pode_aprovar_pops_its',
                    'Compatibilidade retroativa (funciona sem a coluna)',
                    'Script de migração SQL incluído'
                ]
            ],
            [
                'version' => '2.3.3',
                'date' => '06/10/2025',
                'type' => 'Melhoria',
                'title' => 'Página de Detalhes Completos em Amostragens 2.0',
                'description' => 'Criada página visual para exibir detalhes completos da amostragem',
                'items' => [
                    'Página dedicada de detalhes com layout profissional',
                    'Cards separados por seção (Básicas, Produto, Quantidades)',
                    'Exibição visual de todos os responsáveis com avatares',
                    'Download de anexo NF com informações de tamanho',
                    'Galeria de evidências (fotos) com preview',
                    'Badge de status com cores diferenciadas',
                    'Botão de impressão para documentação',
                    'Layout responsivo para desktop e mobile',
                    'Estatísticas de quantidades com destaque visual',
                    'Link "Ver Detalhes Completos" nos emails agora funcional'
                ]
            ],
            [
                'version' => '2.3.2',
                'date' => '06/10/2025',
                'type' => 'Melhoria',
                'title' => 'Alteração de Status Rápida com Email em Amostragens 2.0',
                'description' => 'Adicionado dropdown de status no grid para alteração rápida com email automático',
                'items' => [
                    'Dropdown de status diretamente na tabela (grid)',
                    'Alteração de status com apenas 1 clique',
                    'Confirmação antes de alterar status',
                    'Email automático enviado aos responsáveis a cada mudança',
                    'Cores dinâmicas no dropdown por status',
                    'Feedback visual de sucesso/erro',
                    'Recarga automática da página após alteração',
                    'Validação de status permitidos (Pendente, Aprovado, Aprovado Parcialmente, Reprovado)',
                    'Logs detalhados de cada alteração',
                    'Padrão seguindo Melhoria Contínua 2.0'
                ]
            ],
            [
                'version' => '2.3.1',
                'date' => '06/10/2025',
                'type' => 'Melhoria',
                'title' => 'Sistema de Emails Automáticos em Amostragens 2.0',
                'description' => 'Ativado envio automático de emails para responsáveis em amostragens',
                'items' => [
                    'Email automático ao criar nova amostragem',
                    'Email automático ao atualizar status da amostragem',
                    'Notificação para todos os responsáveis designados',
                    'Templates HTML profissionais com gradientes',
                    'Detalhes completos da amostragem no email',
                    'Link direto para visualizar no sistema',
                    'Cores diferenciadas por status (Aprovado, Reprovado, etc)',
                    'Logs detalhados do envio de emails',
                    'Tratamento de erros que não bloqueia operação',
                    'Mesmo padrão usado em Melhoria Contínua 2.0'
                ]
            ],
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
