<div class="rise-siamesa-wrapper">
  <div class="row">
    <div class="col-md-6 col-sm-6">
      <div class="panel panel-success">
        <div class="panel-body">
          <div class="row">
            <div class="col-md-3">
              <i class="fa fa-check-circle fa-4x text-success"></i>
            </div>
            <div class="col-md-9 text-right">
              <h4 class="text-success">Total Recebido</h4>
              <h2 style="margin:0;">R$
                <?= number_format($total_pago, 2, ',', '.'); ?>
              </h2>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-sm-6">
      <div class="panel panel-warning">
        <div class="panel-body">
          <div class="row">
            <div class="col-md-3">
              <i class="fa fa-clock-o fa-4x text-warning"></i>
            </div>
            <div class="col-md-9 text-right">
              <h4 class="text-warning">Total Pendente</h4>
              <h2 style="margin:0;">R$
                <?= number_format($total_pendente, 2, ',', '.'); ?>
              </h2>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <hr />

  <div class="card">
    <div class="card-header">
      <div class="row">
        <div class="col-md-8">
          <h4 class="m0"><i class="fa fa-warning text-danger"></i> Lista de Inadimplência (Vencidos)</h4>
          <small class="text-off">Pagamentos não identificados após a data de vencimento. Cada parcela aparece individualmente.</small>
        </div>
        <div class="col-md-4 text-right">
          <strong class="text-danger">Total em Atraso: R$ <?= isset($total_inadimplencia) ? number_format($total_inadimplencia, 2, ',', '.') : '0,00'; ?></strong>
        </div>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover table-striped mb0">
        <thead>
          <tr>
            <th>Aluno</th>
            <th>Responsável</th>
            <th>Vencimento</th>
            <th>Competência</th>
            <th>Valor</th>
            <th class="text-center">Cobrança</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($inadimplentes)): ?>
            <tr>
              <td colspan="6" class="text-center p20 text-off">
                <i class="fa fa-smile-o"></i> Nenhum pagamento vencido no momento.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($inadimplentes as $inad): ?>
              <?php
              // Limpa o número para o link do WhatsApp
              $whats_limpo = preg_replace('/\D/', '', $inad['whats']);
              $mensagem = "Olá " . $inad['resp_nome'] . ", notamos que a parcela de " . ($inad['competencia'] ?? date('m/Y', strtotime($inad['vencimento']))) . " do aluno " . $inad['nome_aluno'] . " está em aberto. Podemos ajudar?";
              $link_wa = "https://wa.me/55" . $whats_limpo . "?text=" . urlencode($mensagem);
              ?>
              <tr>
                <td><strong>
                    <?= $inad['nome_aluno']; ?>
                  </strong></td>
                <td>
                  <?= $inad['resp_nome']; ?>
                </td>
                <td><span class="text-danger">
                    <?= date('d/m/Y', strtotime($inad['vencimento'])); ?>
                  </span></td>
                <td>
                  <?= $inad['competencia'] ?? date('m/Y', strtotime($inad['vencimento'])); ?>
                </td>
                <td><strong>R$
                    <?= number_format($inad['valor'], 2, ',', '.'); ?>
                  </strong></td>
                <td class="text-center">
                  <a href="<?= $link_wa; ?>" target="_blank" class="btn btn-xs btn-default" title="Cobrar via WhatsApp">
                    <i class="fa fa-whatsapp text-success"></i> Notificar
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            <tr class="total-row">
              <td colspan="4" class="text-right">
                <strong>Total de Parcelas em Atraso: <?= count($inadimplentes); ?></strong>
              </td>
              <td><strong class="text-danger">R$ <?= isset($total_inadimplencia) ? number_format($total_inadimplencia, 2, ',', '.') : '0,00'; ?></strong></td>
              <td></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <style>
    /* ============================================
       RISE CRM - NATIVE THEME INTEGRATION
       Arquiteto de Software Sênior - Bootstrap 5
       ============================================ */

    /* ===== PANELS (COMPATIBILIDADE COM BOOTSTRAP 4) ===== */
    .rise-siamesa-wrapper .panel {
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      border-color: var(--bs-border-color);
      background-color: var(--card-bg, var(--bs-body-bg));
    }

    .rise-siamesa-wrapper .panel-body {
      padding: 20px;
      background-color: transparent;
    }

    /* ===== CARDS ===== */
    .rise-siamesa-wrapper .card {
      background-color: var(--card-bg, var(--bs-body-bg));
      border-color: var(--bs-border-color);
    }

    .rise-siamesa-wrapper .card-header {
      background-color: var(--bs-secondary-bg);
      border-bottom-color: var(--bs-border-color);
    }

    /* Remove estilo inline do card-header se existir */
    .rise-siamesa-wrapper .card-header[style*="background"] {
      background-color: var(--bs-secondary-bg);
    }

    /* ===== TABELAS - TRANSPARÊNCIA TOTAL ===== */
    .rise-siamesa-wrapper .table {
      --bs-table-bg: transparent;
      --bs-table-color: var(--bs-body-color);
      --bs-table-border-color: var(--bs-border-color);
      --bs-table-hover-bg: rgba(var(--bs-primary-rgb), 0.05);
      --bs-table-hover-color: var(--bs-body-color);
    }

    .rise-siamesa-wrapper .table thead {
      --bs-table-head-bg: transparent;
      --bs-table-head-color: var(--bs-body-color);
    }

    .rise-siamesa-wrapper .table thead th {
      font-weight: 600;
      border-bottom-color: var(--bs-border-color);
    }

    /* Hover das linhas - usa cor primária do tema */
    .rise-siamesa-wrapper .table-hover tbody tr:hover {
      background-color: rgba(var(--bs-primary-rgb), 0.05);
      color: var(--bs-body-color);
    }

    /* Tabela striped - fundo alternado sutil */
    .rise-siamesa-wrapper .table-striped tbody tr:nth-of-type(odd) {
      background-color: rgba(var(--bs-primary-rgb), 0.02);
    }

    /* ===== LINHA DE TOTAL ===== */
    .rise-siamesa-wrapper tr.total-row {
      font-weight: bold;
      background-color: rgba(var(--bs-warning-rgb), 0.1);
    }

    /* Remove estilo inline da linha de total se existir */
    .rise-siamesa-wrapper tr[style*="background-color: #fff3cd"] {
      background-color: rgba(var(--bs-warning-rgb), 0.1);
    }

    /* ===== UTILITÁRIOS ===== */
    .rise-siamesa-wrapper .text-off {
      opacity: 0.7;
      color: var(--bs-secondary-color);
    }
  </style>
</div>