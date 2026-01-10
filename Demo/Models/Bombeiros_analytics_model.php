<?php

namespace Demo\Models;

class Bombeiros_analytics_model
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function get_settings()
    {
        // Retorna configurações padrão
        return (object) [
            'meta_faturamento' => 15000.00,
            'whatsapp_msg' => "Prezado(a) {nome}, identificamos uma pendência de {valor} na sua mensalidade. Gostaria de negociar?",
            'custo_clique_ads' => 1.50
        ];
    }

    public function save_settings($dados)
    {
        foreach ($dados as $key => $val) {
            // Implementar salvamento de configurações se necessário
        }
        return true;
    }

    public function get_kpi_metrics()
    {
        $settings = $this->get_settings();
        $meta = $settings->meta_faturamento;

        // Total de alunos ativos
        $total_alunos = $this->db->table('siamesa_alunos')
            ->where(['status' => 'Ativo', 'deleted' => 0])
            ->countAllResults();

        // Total de alunos cancelados/desistentes
        $total_desistentes = $this->db->table('siamesa_alunos')
            ->where(['status' => 'Cancelado', 'deleted' => 0])
            ->countAllResults();

        // Total de cobranças
        $total_cobrancas = $this->db->table('siamesa_cobrancas c')
            ->join('siamesa_alunos a', 'a.id = c.aluno_id')
            ->where('a.deleted', 0)
            ->countAllResults();

        // Cobranças pagas
        $cobrancas_pagas = $this->db->table('siamesa_cobrancas c')
            ->join('siamesa_alunos a', 'a.id = c.aluno_id')
            ->where(['c.status' => 'Pago', 'a.deleted' => 0])
            ->countAllResults();

        // Cobranças pendentes
        $cobrancas_pendentes = $this->db->table('siamesa_cobrancas c')
            ->join('siamesa_alunos a', 'a.id = c.aluno_id')
            ->where(['c.status' => 'Pendente', 'a.deleted' => 0])
            ->countAllResults();

        // Total faturado (soma de todas as cobranças pagas)
        $total_faturado_result = $this->db->table('siamesa_cobrancas')
            ->where('status', 'Pago')
            ->selectSum('valor')
            ->get()
            ->getRow();
        $total_faturado = $total_faturado_result->valor ?? 0;

        // Total em atraso (vencidas e pendentes)
        $hoje = date('Y-m-d');
        $total_atraso_result = $this->db->table('siamesa_cobrancas c')
            ->join('siamesa_alunos a', 'a.id = c.aluno_id')
            ->where('c.vencimento <', $hoje)
            ->where(['c.status' => 'Pendente', 'a.deleted' => 0])
            ->selectSum('c.valor')
            ->get()
            ->getRow();
        $total_atraso = $total_atraso_result->valor ?? 0;

        // Cálculos
        $total_geral = $total_alunos + $total_desistentes;
        $taxa_evasao = ($total_geral > 0) ? round(($total_desistentes / $total_geral) * 100, 1) : 0;
        $ticket_medio = ($total_alunos > 0) ? round($total_faturado / $total_alunos, 2) : 0;
        $taxa_renovacao = ($total_cobrancas > 0) ? round(($cobrancas_pagas / $total_cobrancas) * 100, 1) : 0;

        return [
            'meta_financeira' => $meta,
            'whatsapp_template' => $settings->whatsapp_msg,
            'total_alunos' => $total_alunos,
            'total_desistentes' => $total_desistentes,
            'taxa_evasao' => $taxa_evasao,
            'ticket_medio' => $ticket_medio,
            'taxa_renovacao' => $taxa_renovacao,
            'total_faturado' => $total_faturado,
            'total_atraso' => $total_atraso,
            'cobrancas_pagas' => $cobrancas_pagas,
            'cobrancas_pendentes' => $cobrancas_pendentes,
            'inadimplencia_qtd' => $this->db->table('siamesa_cobrancas c')
                ->join('siamesa_alunos a', 'a.id = c.aluno_id')
                ->where('c.vencimento <', $hoje)
                ->where(['c.status' => 'Pendente', 'a.deleted' => 0])
                ->countAllResults(),
            'funil' => [100, 50, 20, 10] // Mock para gráfico de funil
        ];
    }

    // Lista de alunos em risco (inadimplentes)
    public function get_alunos_risco()
    {
        $hoje = date('Y-m-d');
        
        $inadimplentes = $this->db->table('siamesa_cobrancas c')
            ->select('c.*, a.nome_aluno, a.turma, r.nome as responsavel_nome, r.whats as telefone, DATEDIFF("' . $hoje . '", c.vencimento) as dias_atraso')
            ->join('siamesa_alunos a', 'a.id = c.aluno_id')
            ->join('siamesa_responsaveis r', 'r.id = a.responsavel_id')
            ->where('c.vencimento <', $hoje)
            ->where(['c.status' => 'Pendente', 'a.deleted' => 0])
            ->orderBy('c.vencimento', 'ASC')
            ->get()
            ->getResultArray();

        $lista = [];
        foreach ($inadimplentes as $item) {
            $obj = new \stdClass();
            $obj->id_aluno = $item['aluno_id'];
            $obj->id_matricula = $item['id'];
            $obj->nome = $item['nome_aluno'];
            $obj->turma = $item['turma'] ?? 'Geral';
            $obj->dias_atraso = $item['dias_atraso'] ?? 0;
            $obj->valor = floatval($item['valor']);
            $obj->telefone = preg_replace('/\D/', '', $item['telefone'] ?? '');
            $obj->status = 'Atrasado';
            $lista[] = $obj;
        }

        return $lista;
    }

    // Lista de desistências recentes
    public function get_lista_desistencias()
    {
        $desistentes = $this->db->table('siamesa_alunos')
            ->where(['status' => 'Cancelado', 'deleted' => 0])
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        $lista = [];
        foreach ($desistentes as $item) {
            $lista[] = [
                'nome' => $item['nome_aluno'],
                'motivo' => 'Cancelado',
                'data_cancelamento' => $item['data_matricula'] ?? date('Y-m-d') // Usa data de matrícula como referência
            ];
        }

        return $lista;
    }

    // Lista de cobranças pendentes
    public function get_lista_cobrancas_pendentes()
    {
        $cobrancas = $this->db->table('siamesa_cobrancas c')
            ->select('c.*, a.nome_aluno, r.nome as responsavel_nome, r.whats as telefone')
            ->join('siamesa_alunos a', 'a.id = c.aluno_id')
            ->join('siamesa_responsaveis r', 'r.id = a.responsavel_id')
            ->where(['c.status' => 'Pendente', 'a.deleted' => 0])
            ->orderBy('c.vencimento', 'ASC')
            ->limit(10)
            ->get()
            ->getResultArray();

        $lista = [];
        foreach ($cobrancas as $item) {
            $lista[] = [
                'nome' => $item['nome_aluno'],
                'valor' => floatval($item['valor']),
                'telefone' => $item['telefone'] ?? ''
            ];
        }

        return $lista;
    }

    // Gráfico de crescimento mensal
    public function get_crescimento_mensal()
    {
        // Busca alunos criados nos últimos 6 meses
        $seis_meses_atras = date('Y-m-01', strtotime('-5 months'));
        
        try {
            $alunos_por_mes = $this->db->table('siamesa_alunos')
                ->select('DATE_FORMAT(data_matricula, "%b") as mes, COUNT(*) as total')
                ->where('data_matricula >=', $seis_meses_atras)
                ->where('deleted', 0)
                ->groupBy('mes')
                ->orderBy('data_matricula', 'ASC')
                ->get()
                ->getResultArray();

            $meses = [];
            $novos = [];
            $desistencias = [];

            foreach ($alunos_por_mes as $item) {
                $meses[] = $item['mes'];
                $novos[] = (int)$item['total'];
                
                // Busca desistências do mesmo período (aproximado)
                $desist_mes = $this->db->table('siamesa_alunos')
                    ->where('DATE_FORMAT(data_matricula, "%b")', $item['mes'])
                    ->where(['status' => 'Cancelado', 'deleted' => 0])
                    ->countAllResults();
                $desistencias[] = $desist_mes;
            }

            // Se não houver dados suficientes, preenche com zeros
            while (count($meses) < 6) {
                $meses[] = date('M', strtotime('-' . (5 - count($meses)) . ' months'));
                $novos[] = 0;
                $desistencias[] = 0;
            }

            return [
                'meses' => array_slice($meses, -6),
                'novos' => array_slice($novos, -6),
                'desistencias' => array_slice($desistencias, -6)
            ];
        } catch (\Exception $e) {
            // Retorna dados padrão em caso de erro
            return [
                'meses' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                'novos' => [0, 0, 0, 0, 0, 0],
                'desistencias' => [0, 0, 0, 0, 0, 0]
            ];
        }
    }

    public function get_patentes()
    {
        // Agrupa por turma (pode ser adaptado para patentes se houver campo específico)
        $patentes = $this->db->table('siamesa_alunos')
            ->select('turma, COUNT(*) as total')
            ->where(['status' => 'Ativo', 'deleted' => 0])
            ->groupBy('turma')
            ->get()
            ->getResultArray();

        $labels = [];
        $valores = [];
        foreach ($patentes as $item) {
            $labels[] = $item['turma'] ?? 'Geral';
            $valores[] = (int)$item['total'];
        }

        return [
            'labels' => $labels ?: ['Turma A', 'Turma B', 'Turma C'],
            'valores' => $valores ?: [0, 0, 0]
        ];
    }

    public function get_previsao_receita()
    {
        // Calcula previsão baseada nas mensalidades ativas
        $mensalidade_media = $this->db->table('siamesa_alunos')
            ->selectAvg('valor_mensalidade')
            ->where(['status' => 'Ativo', 'deleted' => 0])
            ->get()
            ->getRow();
        
        $media = floatval($mensalidade_media->valor_mensalidade ?? 150.00);
        $total_ativos = $this->db->table('siamesa_alunos')
            ->where(['status' => 'Ativo', 'deleted' => 0])
            ->countAllResults();

        $previsao_mensal = $media * $total_ativos;

        return [
            'meses' => ['Janeiro', 'Fevereiro', 'Março'],
            'valores' => [$previsao_mensal, $previsao_mensal, $previsao_mensal]
        ];
    }

    public function get_whatsapp_template()
    {
        try {
            $dbprefix = get_db_prefix();
            
            $result = $this->db->query("SELECT `setting_value` FROM `" . $dbprefix . "demo_settings` WHERE `setting_name` = 'whatsapp_template' AND `deleted` = 0 LIMIT 1");
            
            if ($result && $result->getNumRows() > 0) {
                $row = $result->getRow();
                if ($row && !empty($row->setting_value)) {
                    return $row->setting_value;
                }
            }
        } catch (\Exception $e) {
            // Se houver erro, retorna template padrão
            log_message('error', 'Erro ao buscar template WhatsApp: ' . $e->getMessage());
        }
        
        // Template padrão
        return "Bom dia, {nome} verificamos que há um pendência no valor de {valor} em sua mensalidade, gostaria de negociar ou ajustar a pendência?";
    }

    public function save_whatsapp_template($template)
    {
        try {
            $dbprefix = get_db_prefix();
            
            // Escapa o template
            $template_escaped = addslashes($template);
            
            // Verifica se já existe
            $exists = $this->db->query("SELECT COUNT(*) as total FROM `" . $dbprefix . "demo_settings` WHERE `setting_name` = 'whatsapp_template'");
            
            if ($exists && $exists->getRow()->total > 0) {
                // Atualiza
                $this->db->query("UPDATE `" . $dbprefix . "demo_settings` SET `setting_value` = '" . $template_escaped . "' WHERE `setting_name` = 'whatsapp_template'");
            } else {
                // Insere
                $this->db->query("INSERT INTO `" . $dbprefix . "demo_settings` (`setting_name`, `setting_value`, `deleted`) VALUES ('whatsapp_template', '" . $template_escaped . "', 0)");
            }
            
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Erro ao salvar template WhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    public function save_ads_data($dados)
    {
        try {
            $dbprefix = get_db_prefix();
            
            // Serializa os dados
            $dados_serializados = serialize($dados);
            $dados_escaped = addslashes($dados_serializados);
            
            // Verifica se já existe
            $exists = $this->db->query("SELECT COUNT(*) as total FROM `" . $dbprefix . "demo_settings` WHERE `setting_name` = 'ads_data'");
            
            if ($exists && $exists->getRow()->total > 0) {
                // Atualiza
                $this->db->query("UPDATE `" . $dbprefix . "demo_settings` SET `setting_value` = '" . $dados_escaped . "' WHERE `setting_name` = 'ads_data'");
            } else {
                // Insere
                $this->db->query("INSERT INTO `" . $dbprefix . "demo_settings` (`setting_name`, `setting_value`, `deleted`) VALUES ('ads_data', '" . $dados_escaped . "', 0)");
            }
            
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Erro ao salvar dados de ads: ' . $e->getMessage());
            return false;
        }
    }

    public function get_ads_data()
    {
        try {
            $dbprefix = get_db_prefix();
            
            $result = $this->db->query("SELECT `setting_value` FROM `" . $dbprefix . "demo_settings` WHERE `setting_name` = 'ads_data' AND `deleted` = 0 LIMIT 1");
            
            if ($result && $result->getNumRows() > 0) {
                $row = $result->getRow();
                if ($row && !empty($row->setting_value)) {
                    return unserialize($row->setting_value);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar dados de ads: ' . $e->getMessage());
        }
        
        // Retorna dados padrão
        return [
            'google_ads' => [
                'investimento' => 1450.00,
                'impressoes' => 12500,
                'cliques' => 850,
                'leads' => 120,
                'matriculas' => 28
            ],
            'organico' => [
                'leads_organico' => 50,
                'matriculas_organico' => 17
            ]
        ];
    }
}