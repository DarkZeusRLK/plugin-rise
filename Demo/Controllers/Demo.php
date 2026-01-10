<?php

namespace Demo\Controllers;

use App\Controllers\Security_Controller;

class Demo extends Security_Controller
{

    protected $Bombeiros_analytics_model;

    function __construct()
    {
        parent::__construct();
        $this->Bombeiros_analytics_model = new \Demo\Models\Bombeiros_analytics_model();
    }

    public function index()
    {
        $db = db_connect();

        // Busca dados reais das tabelas do plugin Bombeiros
        $kpi = $this->Bombeiros_analytics_model->get_kpi_metrics();
        $risco = $this->Bombeiros_analytics_model->get_alunos_risco();
        $crescimento = $this->Bombeiros_analytics_model->get_crescimento_mensal();
        $lista_desistencias = $this->Bombeiros_analytics_model->get_lista_desistencias();
        $lista_cobrancas = $this->Bombeiros_analytics_model->get_lista_cobrancas_pendentes();

        // Busca template do WhatsApp
        $template_whatsapp = $this->Bombeiros_analytics_model->get_whatsapp_template();
        
        // Busca dados de ads salvos ou usa padrão
        $ads_data_salvo = $this->Bombeiros_analytics_model->get_ads_data();
        
        // Combina dados salvos com dados calculados
        $ads_data_final = [
            'investimento' => floatval($ads_data_salvo['google_ads']['investimento'] ?? 1450.00),
            'impressoes' => intval($ads_data_salvo['google_ads']['impressoes'] ?? 12500),
            'cliques' => intval($ads_data_salvo['google_ads']['cliques'] ?? 850),
            'leads_ads' => intval($ads_data_salvo['google_ads']['leads'] ?? 120),
            'matriculas_ads' => intval($ads_data_salvo['google_ads']['matriculas'] ?? 28),
            'matriculas_total' => intval($kpi['total_alunos'] ?? 45),
            'leads_organico' => intval($ads_data_salvo['organico']['leads_organico'] ?? 50),
            'matriculas_organico' => intval($ads_data_salvo['organico']['matriculas_organico'] ?? 17)
        ];

        // Envia dados para a view
        $view_data['kpi'] = $kpi;
        $view_data['risco'] = $risco;
        $view_data['crescimento'] = $crescimento;
        $view_data['lista_desistencias'] = $lista_desistencias;
        $view_data['lista_cobrancas'] = $lista_cobrancas;
        $view_data['template_whatsapp'] = $template_whatsapp;
        $view_data['ads_data'] = $ads_data_final;

        return $this->template->render("Demo\Views\index", $view_data);
    }

    public function salvar_template()
    {
        $template = $this->request->getPost('template');
        
        if (empty($template)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Template não pode estar vazio."
            ]);
        }

        $this->Bombeiros_analytics_model->save_whatsapp_template($template);

        return $this->response->setJSON([
            "success" => true,
            "message" => "Template salvo com sucesso!"
        ]);
    }

    public function obter_template()
    {
        $template = $this->Bombeiros_analytics_model->get_whatsapp_template();
        
        return $this->response->setJSON([
            "success" => true,
            "template" => $template
        ]);
    }

    public function salvar_ads()
    {
        $dados = $this->request->getPost('dados');
        
        if (empty($dados)) {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Nenhum dado recebido."
            ]);
        }

        $resultado = $this->Bombeiros_analytics_model->save_ads_data($dados);

        if ($resultado) {
            return $this->response->setJSON([
                "success" => true,
                "message" => "Dados de marketing salvos com sucesso!"
            ]);
        } else {
            return $this->response->setJSON([
                "success" => false,
                "message" => "Erro ao salvar dados. Verifique os logs do servidor."
            ]);
        }
    }
}