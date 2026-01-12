<div class="rise-siamesa-wrapper">
  <div class="table-responsive">
    <table class="table table-hover siamesa-table-main">
      <thead>
        <tr>
          <th width="30"></th>
          <th>Aluno</th>
          <th>Total de Parcelas</th>
          <th class="text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($alunos_com_pagamentos)): ?>
          <tr>
            <td colspan="4" class="text-center p20 text-off">Nenhum registro encontrado.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($alunos_com_pagamentos as $aluno_id => $dados): ?>
            <tr class="student-row" style="cursor: pointer;" onclick="toggleParcelas(<?= $aluno_id; ?>)">
              <td><i class="fa fa-plus-circle text-primary" id="icon-<?= $aluno_id; ?>"></i></td>
              <td><strong class="text-emphasis"><?= $dados['nome_aluno']; ?></strong></td>
              <td><?= count($dados['parcelas']); ?> parcelas</td>
              <td class="text-center">
                <?php
                $pendentes = array_filter($dados['parcelas'], function ($p) {
                  return $p['status'] != 'Pago';
                });
                echo count($pendentes) > 0 ? '<span class="badge bg-warning">Pendências</span>' : '<span class="badge bg-success">Tudo Pago</span>';
                ?>
              </td>
            </tr>
            <tr id="detail-<?= $aluno_id; ?>" class="bombeiros-detail-row" style="display: none;">
              <td colspan="4" class="p-0">
                <div class="detail-container">
                  <table class="table table-sm table-bordered m-0">
                    <thead>
                      <tr>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th class="text-center">Ação</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($dados['parcelas'] as $p): ?>
                        <tr>
                          <td><?= date('d/m/Y', strtotime($p['vencimento'])); ?></td>
                          <td>R$ <?= number_format($p['valor'], 2, ',', '.'); ?></td>
                          <td>
                            <span class="badge <?= $p['status'] == 'Pago' ? 'bg-success' : 'bg-warning'; ?>">
                              <?= $p['status']; ?>
                            </span>
                          </td>
                          <td class="text-center">
                            <button class="btn btn-xs <?= $p['status'] == 'Pago' ? 'btn-default' : 'btn-success' ?>" onclick="marcarComoPago(<?= $p['id']; ?>)">
                              <i class="fa <?= $p['status'] == 'Pago' ? 'fa-check-circle' : 'fa-check' ?>"></i>
                              <?= $p['status'] == 'Pago' ? 'Recebido' : 'Baixar' ?>
                            </button>
                            <button class="btn btn-xs btn-primary ml5" onclick="gerarComprovante(<?= $p['id']; ?>, <?= $aluno_id; ?>)">
                              <i class="fa fa-file-pdf-o">Gerar Comprovante</i>
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <style>
    /* ============================================
       RISE CRM - NATIVE THEME INTEGRATION
       Arquiteto de Software Sênior - Bootstrap 5
       ============================================ */

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

    /* Linha de detalhe - sub-nível sutil */
    .rise-siamesa-wrapper .bombeiros-detail-row {
      background-color: rgba(var(--bs-primary-rgb), 0.03);
    }

    .rise-siamesa-wrapper .detail-container {
      padding: 15px 15px 15px 40px;
      border-left: 3px solid var(--primary-color, var(--bs-primary));
      background-color: rgba(var(--bs-primary-rgb), 0.02);
    }

    /* Tabelas internas dentro de detalhes */
    .rise-siamesa-wrapper .detail-container .table {
      border-radius: 4px;
      overflow: hidden;
      --bs-table-bg: transparent;
    }

    /* Utilitários */
    .rise-siamesa-wrapper .text-off {
      opacity: 0.7;
      color: var(--bs-secondary-color);
    }

    .rise-siamesa-wrapper .ml5 {
      margin-left: 0.5rem;
    }
  </style>
</div>