<?php
// =================================================================================
// 1. LÓGICA DE DADOS (PHP) - MOCKUP
// =================================================================================

$kpi = $kpi ?? [];
$risco = $risco ?? [];
// Dados de gráfico
$crescimento = $crescimento ?? ['novos' => [5, 12, 18, 15, 22, 28], 'meses' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun']];

// Template Padrão do WhatsApp
$msg_padrao = $kpi['whatsapp_template'] ?? "Olá {nome}, identificamos uma pendência de {valor} referente à sua mensalidade. Gostaria de receber o link para regularização?";

$lista_desistencias = $lista_desistencias ?? [];
$lista_cobrancas = $lista_cobrancas ?? [];

// Populando dados se estiverem vazios (Para teste)
if (empty($risco)) {
    $risco = [
        (object) ['id_aluno' => 101, 'nome' => 'Carlos Silva', 'turma' => 'Inglês A1', 'dias_atraso' => 15, 'valor' => 350.00, 'telefone' => '43999999999', 'status' => 'Atrasado'],
        (object) ['id_aluno' => 102, 'nome' => 'Ana Souza', 'turma' => 'Inglês B2', 'dias_atraso' => 5, 'valor' => 1200.00, 'telefone' => '43988888888', 'status' => 'Negociando'],
        (object) ['id_aluno' => 103, 'nome' => 'Roberto Firmino', 'turma' => 'Espanhol', 'dias_atraso' => 45, 'valor' => 450.00, 'telefone' => '43977777777', 'status' => 'Jurídico'],
    ];
}

if (empty($lista_cobrancas)) {
    foreach (array_slice($risco, 0, 3) as $r) {
        $lista_cobrancas[] = [
            'nome' => $r->nome,
            'valor' => $r->valor,
            'telefone' => $r->telefone
        ];
    }
}

$ads_data = $ads_data ?? [
    'investimento' => 1450.00,
    'impressoes' => 12500,
    'cliques' => 850,
    'leads_ads' => 120,
    'matriculas_ads' => 28,
    'leads_organico' => 50,
    'matriculas_organico' => 17
];
?>

<style>
    /* ============================================================
       CORREÇÃO GERAL DE ESTILO & MODAL DARK
       ============================================================ */

    /* 1. Wrapper Principal Isolado */
    .rise-siamesa-wrapper {
        font-family: "Inter", "Helvetica Neue", sans-serif;
        background-color: transparent;
        height: calc(100vh - 120px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* 2. FORÇA BRUTA: Textos Escuros dentro do Painel Branco */
    .rise-siamesa-wrapper .card-panel,
    .rise-siamesa-wrapper .kpi-card,
    .rise-siamesa-wrapper .sheet-toolbar,
    .rise-siamesa-wrapper .custom-table,
    .rise-siamesa-wrapper h1,
    .rise-siamesa-wrapper h2,
    .rise-siamesa-wrapper h3,
    .rise-siamesa-wrapper h4,
    .rise-siamesa-wrapper h5,
    .rise-siamesa-wrapper strong,
    .rise-siamesa-wrapper span,
    .rise-siamesa-wrapper div,
    .rise-siamesa-wrapper label {
        color: #333333 !important;
    }

    /* 3. Exceções: Itens que devem permanecer Brancos */
    .rise-siamesa-wrapper .btn-sheet,
    .rise-siamesa-wrapper .header-colored-red,
    .rise-siamesa-wrapper .header-colored-orange,
    .rise-siamesa-wrapper .btn-cobrar,
    .rise-siamesa-wrapper .badge {
        color: #ffffff !important;
    }

    /* --- Toolbar --- */
    .sheet-toolbar {
        display: flex;
        gap: 10px;
        padding: 15px;
        align-items: center;
        background-color: #ffffff;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 20px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    /* Botões da Toolbar (Escuros Nativos) */
    .btn-sheet {
        background-color: #1e293b;
        border: 1px solid #0f172a;
        color: #ffffff !important;
        padding: 8px 16px;
        font-size: 13px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .btn-sheet:hover {
        background-color: #0f172a;
        transform: translateY(-1px);
    }

    .btn-sheet i {
        color: #cbd5e1;
    }

    /* Input de Busca */
    #globalSearch {
        background-color: #ffffff !important;
        color: #333333 !important;
        border: 1px solid #ccc !important;
    }

    /* --- Cards e Estrutura --- */
    .kpi-card,
    .card-panel {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
    }

    .card-panel {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 20px;
    }

    /* KPIs */
    .kpi-row {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .kpi-card {
        flex: 1;
        min-width: 200px;
        padding: 20px;
        border-top: 4px solid #ccc;
    }

    .kpi-card.border-blue {
        border-top-color: #0984e3;
    }

    .kpi-card.border-red {
        border-top-color: #d63031;
    }

    .kpi-card.border-green {
        border-top-color: #00b894;
    }

    .kpi-card.border-orange {
        border-top-color: #e17055;
    }

    .kpi-value {
        font-size: 28px;
        font-weight: 800;
        display: block;
        margin-bottom: 5px;
    }

    .kpi-label {
        font-size: 11px;
        text-transform: uppercase;
        color: #64748b !important;
        font-weight: 600;
    }

    .text-danger-custom {
        color: #d63031 !important;
    }

    .text-success-custom {
        color: #00b894 !important;
    }

    /* --- Tabelas --- */
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .custom-table th {
        text-align: left;
        padding: 12px;
        background-color: #f1f5f9;
        color: #1e293b !important;
        border-bottom: 2px solid #e2e8f0;
        font-weight: 700;
    }

    .custom-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .custom-table tr:hover td {
        background-color: #f8fafc;
    }

    /* Headers Coloridos */
    .header-colored-red {
        background: #d63031;
        padding: 12px 15px;
        font-weight: bold;
        border-radius: 4px 4px 0 0;
        color: #fff !important;
    }

    .header-colored-orange {
        background: #e17055;
        padding: 12px 15px;
        font-weight: bold;
        border-radius: 4px 4px 0 0;
        color: #fff !important;
    }

    /* Botão Cobrar */
    .btn-cobrar {
        background-color: #00b894;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        color: #fff !important;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-cobrar:hover {
        background-color: #01a382;
    }

    /* --- Tabs --- */
    .sheet-tabs {
        display: flex;
        border-top: 1px solid #e0e0e0;
        background: #ffffff;
        margin-top: auto;
    }

    .sheet-tab {
        flex: 1;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        color: #64748b !important;
        border-right: 1px solid #f1f5f9;
        font-weight: 500;
    }

    .sheet-tab:hover {
        background-color: #f8fafc;
    }

    .sheet-tab.active {
        color: #0984e3 !important;
        font-weight: 700;
        border-top: 3px solid #0984e3;
        background-color: #ffffff;
        margin-top: -1px;
    }

    .sheet-content {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        padding-bottom: 50px;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }

    /* ============================================================
       MODAL CUSTOMIZADO (DARK MODE - Estilo "Hacker/Dev")
       ============================================================ */
    .custom-dark-modal .modal-content {
        background-color: #1e293b;
        /* Azul noturno fundo */
        color: #f1f5f9;
        /* Texto claro */
        border: 1px solid #334155;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        border-radius: 8px;
    }

    .custom-dark-modal .modal-header {
        border-bottom: 1px solid #334155;
        padding: 20px;
    }

    .custom-dark-modal .modal-title {
        font-weight: 600;
        font-size: 18px;
        color: #fff;
    }

    .custom-dark-modal .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    /* X Branco */
    .custom-dark-modal .modal-body {
        padding: 25px;
    }

    .custom-dark-modal label {
        font-weight: 500;
        margin-bottom: 10px;
        display: block;
        color: #cbd5e1;
    }

    /* TextArea Escuro e Grande */
    .custom-dark-modal textarea.form-control {
        background-color: #0f172a !important;
        /* Mais escuro que o modal */
        border: 1px solid #334155 !important;
        color: #ffffff !important;
        font-size: 14px;
        padding: 15px;
        resize: vertical;
    }

    .custom-dark-modal textarea.form-control:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
    }

    .custom-dark-modal .text-muted {
        color: #94a3b8 !important;
        font-size: 12px;
        margin-top: 8px;
        display: block;
    }

    .custom-dark-modal .modal-footer {
        border-top: 1px solid #334155;
        padding: 15px 25px;
    }

    /* Botões do Modal */
    .btn-dark-secondary {
        background-color: #334155;
        color: #fff;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
    }

    .btn-dark-secondary:hover {
        background-color: #475569;
        color: #fff;
    }

    .btn-dark-primary {
        background-color: #2563eb;
        color: #fff;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        font-weight: 600;
    }

    .btn-dark-primary:hover {
        background-color: #1d4ed8;
        color: #fff;
    }
</style>

<div class="rise-siamesa-wrapper">

    <div class="sheet-toolbar">
        <div style="font-weight: bold; margin-right: 15px; font-size: 18px;">
            <i class="fa fa-table"></i> Controle Master
        </div>
        <div class="toolbar-group">
            <button class="btn-sheet" onclick="window.print()"><i class="fa fa-print"></i> Imprimir</button>
            <button class="btn-sheet" onclick="$('#modalTemplateConfig').modal('show')"><i class="fa fa-cog"></i> Configurar</button>
            <button class="btn-sheet" onclick="location.reload()"><i class="fa fa-refresh"></i> Atualizar</button>
            <button class="btn-sheet" onclick="exportStyledExcel('financeiro.xls')"><i class="fa fa-file-excel-o"></i> Excel</button>
        </div>
        <div style="flex-grow: 1; display: flex; justify-content: flex-end;">
            <input type="text" id="globalSearch" class="form-control input-sm" style="width: 200px;" placeholder="🔍 Buscar...">
        </div>
    </div>

    <div class="sheet-content">

        <div id="tab-overview" class="sheet-pane active">
            <div class="kpi-row">
                <div class="kpi-card border-blue">
                    <span class="kpi-value"><?php echo $kpi['total_alunos'] ?? 0; ?></span>
                    <span class="kpi-label">Total Alunos Ativos</span>
                </div>
                <div class="kpi-card border-red">
                    <span class="kpi-value text-danger-custom">R$ <?php echo number_format($kpi['total_atraso'] ?? 0, 2, ',', '.'); ?></span>
                    <span class="kpi-label">Total em Atraso</span>
                </div>
                <div class="kpi-card border-green">
                    <span class="kpi-value text-success-custom">R$ <?php echo number_format($kpi['total_faturado'] ?? 0, 2, ',', '.'); ?></span>
                    <span class="kpi-label">Total Faturado</span>
                </div>
                <div class="kpi-card border-orange">
                    <span class="kpi-value"><?php echo $kpi['taxa_renovacao'] ?? 0; ?>%</span>
                    <span class="kpi-label">Taxa Renovação</span>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="card-panel">
                    <strong style="margin-bottom: 10px; display:block;">Fluxo de Caixa (Novos Alunos)</strong>
                    <div id="finance-chart"></div>
                </div>
                <div class="card-panel">
                    <strong style="margin-bottom: 10px; display:block;">Funil de Conversão</strong>
                    <div id="funnel-chart"></div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="card-panel" style="padding:0; overflow:hidden;">
                    <div class="header-colored-red"><i class="fa fa-user-times"></i> Últimas Desistências</div>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Motivo</th>
                                    <th class="text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lista_desistencias)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center" style="opacity:0.7;">Nenhuma desistência recente.</td>
                                    </tr>
                                <?php else:
                                    foreach ($lista_desistencias as $d): ?>
                                        <tr>
                                            <td><?php echo $d['nome']; ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $d['motivo']; ?></span></td>
                                            <td class="text-right"><button class="btn btn-xs btn-default"><i class="fa fa-eye"></i></button></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-panel" style="padding:0; overflow:hidden;">
                    <div class="header-colored-orange"><i class="fa fa-money"></i> Cobranças Pendentes</div>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Valor</th>
                                    <th class="text-right">WhatsApp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lista_cobrancas)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center" style="opacity:0.7;">Nenhuma cobrança pendente.</td>
                                    </tr>
                                <?php else:
                                    foreach ($lista_cobrancas as $c):
                                        $nome = $c['nome'];
                                        $val = number_format($c['valor'], 2, ',', '.');
                                        $tel = $c['telefone'];
                                        ?>
                                        <tr>
                                            <td><strong><?php echo $nome; ?></strong></td>
                                            <td class="text-danger-custom">R$ <?php echo $val; ?></td>
                                            <td class="text-right">
                                                <button class="btn-cobrar btn-whatsapp-action" data-nome="<?php echo $nome; ?>" data-telefone="<?php echo $tel; ?>" data-valor="<?php echo $val; ?>">
                                                    <i class="fa fa-whatsapp"></i> Cobrar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-finance" class="sheet-pane" style="display: none;">
            <div class="card-panel" style="border-top: 0; border-radius: 0;">
                <div class="table-responsive">
                    <table class="custom-table" id="financeTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Aluno / Responsável</th>
                                <th>Turma</th>
                                <th>Status</th>
                                <th>Valor</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($risco)):
                                foreach ($risco as $idx => $aluno): ?>
                                    <tr>
                                        <td><?php echo $idx + 1; ?></td>
                                        <td>
                                            <strong><?php echo $aluno->nome; ?></strong><br>
                                            <small style="opacity:0.7"><?php echo $aluno->telefone; ?></small>
                                        </td>
                                        <td><?php echo $aluno->turma; ?></td>
                                        <td>
                                            <span class="badge" style="background: <?php echo ($aluno->status == 'Negociando' ? '#0984e3' : '#d63031'); ?>;">
                                                <?php echo $aluno->status; ?> (-<?php echo $aluno->dias_atraso; ?>d)
                                            </span>
                                        </td>
                                        <td class="text-danger-custom font-weight-bold">R$ <?php echo number_format($aluno->valor, 2, ',', '.'); ?></td>
                                        <td class="text-center">
                                            <button class="btn-cobrar btn-whatsapp-action" data-nome="<?php echo $aluno->nome; ?>" data-telefone="<?php echo $aluno->telefone; ?>" data-valor="<?php echo number_format($aluno->valor, 2, ',', '.'); ?>">
                                                <i class="fa fa-whatsapp"></i> Cobrar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhuma pendência encontrada.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="tab-marketing" class="sheet-pane" style="display: none;">
            <div class="card-panel">
                <div class="p-3">
                    <h4>Controle de Ads</h4>
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Canal</th>
                                <th>Investimento</th>
                                <th>Leads</th>
                                <th>Matrículas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Google Ads</td>
                                <td>R$ <?php echo number_format($ads_data['investimento'], 2, ',', '.'); ?></td>
                                <td><?php echo $ads_data['leads_ads']; ?></td>
                                <td><?php echo $ads_data['matriculas_ads']; ?></td>
                            </tr>
                            <tr>
                                <td>Orgânico</td>
                                <td>R$ 0,00</td>
                                <td><?php echo $ads_data['leads_organico']; ?></td>
                                <td><?php echo $ads_data['matriculas_organico']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div class="sheet-tabs">
        <div class="sheet-tab active" onclick="switchTab('tab-overview', this)"><i class="fa fa-pie-chart"></i> Visão Geral</div>
        <div class="sheet-tab" onclick="switchTab('tab-finance', this)"><i class="fa fa-dollar"></i> Financeiro</div>
        <div class="sheet-tab" onclick="switchTab('tab-marketing', this)"><i class="fa fa-bullhorn"></i> Marketing</div>
    </div>

</div>

<div class="modal fade" id="modalTemplateConfig" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered custom-dark-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configurar Mensagem WhatsApp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="template-whatsapp">Template da Mensagem</label>
                    <textarea class="form-control" id="template-whatsapp" rows="6" placeholder="Digite sua mensagem aqui..."><?php echo $msg_padrao; ?></textarea>
                    <small class="text-muted">
                        <i class="fa fa-info-circle"></i> Use <b>{nome}</b> e <b>{valor}</b> como variáveis que serão substituídas automaticamente.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-dark-primary" id="btn-salvar-template">Salvar Alterações</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    // ==========================================================================
    // SCRIPTS DO PAINEL
    // ==========================================================================

    document.addEventListener('DOMContentLoaded', function () {

        // --- 1. Inicializa Variáveis do LocalStorage ---
        const savedTemplate = localStorage.getItem('whatsapp_template_custom');
        if (savedTemplate) {
            const txtArea = document.getElementById('template-whatsapp');
            if (txtArea) txtArea.value = savedTemplate;
        }

        // --- 2. Busca Global na Tabela ---
        const searchInput = document.getElementById('globalSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function () {
                const value = this.value.toLowerCase();
                const activePane = Array.from(document.querySelectorAll('.sheet-pane')).find(el => el.style.display !== 'none');
                if (!activePane) return;

                activePane.querySelectorAll("tbody tr").forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.indexOf(value) > -1 ? "" : "none";
                });
            });
        }

        // --- 3. Gráficos (Com Correção de Cor para #333) ---

        // Gráfico Financeiro
        const chartFin = document.querySelector("#finance-chart");
        if (chartFin) {
            new ApexCharts(chartFin, {
                series: [{ name: 'Novos', data: <?php echo json_encode($crescimento['novos']); ?> }],
                chart: {
                    type: 'area',
                    height: 250,
                    toolbar: { show: false },
                    background: 'transparent',
                    foreColor: '#333333', // <--- FORÇA O TEXTO ESCURO
                    fontFamily: 'Inter, sans-serif'
                },
                colors: ['#00b894'],
                theme: { mode: 'light' },
                xaxis: {
                    categories: <?php echo json_encode($crescimento['meses']); ?>,
                    labels: { style: { colors: '#333333' } }
                },
                grid: { borderColor: '#e0e0e0' }
            }).render();
        }

        // Gráfico Funil
        const chartFun = document.querySelector("#funnel-chart");
        if (chartFun) {
            new ApexCharts(chartFun, {
                series: [{ name: "Conversão", data: <?php echo json_encode(array_values($kpi['funil'] ?? [100, 50, 20, 10])); ?> }],
                chart: {
                    type: 'bar',
                    height: 250,
                    toolbar: { show: false },
                    background: 'transparent',
                    foreColor: '#333333', // <--- FORÇA O TEXTO ESCURO
                    fontFamily: 'Inter, sans-serif'
                },
                plotOptions: { bar: { borderRadius: 4, horizontal: true } },
                colors: ['#0984e3'],
                xaxis: {
                    categories: ['Impressões', 'Cliques', 'Leads', 'Matrículas'],
                },
                grid: { borderColor: '#e0e0e0' }
            }).render();
        }

        // --- 4. Lógica de Cobrança (WhatsApp) ---
        // Delegação de eventos para funcionar em qualquer botão .btn-whatsapp-action
        document.body.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-whatsapp-action');
            if (btn) {
                e.preventDefault();

                // Pega o template ATUAL (seja do textarea, do localStorage ou padrão)
                let tmpl = document.getElementById('template-whatsapp')?.value ||
                    localStorage.getItem('whatsapp_template_custom') ||
                    "Olá {nome}, pendência de {valor}.";

                const nome = btn.dataset.nome;
                const valor = btn.dataset.valor;
                const telefone = btn.dataset.telefone.replace(/\D/g, '');

                const msg = tmpl.replace(/{nome}/g, nome).replace(/{valor}/g, "R$ " + valor);

                // Abre WhatsApp
                const link = `https://web.whatsapp.com/send?phone=55${telefone}&text=${encodeURIComponent(msg)}`;
                window.open(link, '_blank');
            }
        });

        // --- 5. Salvar Template no LocalStorage ---
        document.getElementById('btn-salvar-template')?.addEventListener('click', function () {
            const novoTemplate = document.getElementById('template-whatsapp').value;
            localStorage.setItem('whatsapp_template_custom', novoTemplate);

            alert("Template salvo localmente com sucesso! Agora clique em 'Cobrar' para testar.");

            // Fecha Modal
            const modalEl = document.getElementById('modalTemplateConfig');
            if (typeof $ !== 'undefined') { $(modalEl).modal('hide'); }
            else {
                const m = bootstrap.Modal.getInstance(modalEl);
                if (m) m.hide();
            }
        });

        // --- 6. Controle de Abas ---
        window.switchTab = function (tabId, element) {
            document.querySelectorAll('.sheet-pane').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.sheet-tab').forEach(el => el.classList.remove('active'));

            const target = document.getElementById(tabId);
            if (target) {
                target.style.display = 'block';
                window.dispatchEvent(new Event('resize')); // Ajusta gráficos
            }
            if (element) element.classList.add('active');
        };

        // --- 7. Exportar Excel ---
        window.exportStyledExcel = function (filename) {
            const activePane = Array.from(document.querySelectorAll('.sheet-pane')).find(el => el.style.display !== 'none');
            const table = activePane ? activePane.querySelector('table') : null;
            if (!table) { alert("Nenhuma tabela visível para exportar."); return; }

            const dataType = 'application/vnd.ms-excel';
            const tableHTML = table.outerHTML.replace(/ /g, '%20');
            const downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
            downloadLink.download = filename || 'tabela.xls';
            downloadLink.click();
            document.body.removeChild(downloadLink);
        };
    });
</script>