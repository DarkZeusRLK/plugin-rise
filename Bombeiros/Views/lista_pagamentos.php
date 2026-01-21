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
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th class="text-center">Ação</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($dados['parcelas'] as $p): ?>
                        <tr>
                          <td><?= date('d/m/Y', strtotime($p['vencimento'])); ?></td>
                          <td><?= $p['tipo']; ?> <small class="text-muted">(<?= $p['competencia']; ?>)</small></td>
                          <td>R$ <?= number_format($p['valor'], 2, ',', '.'); ?></td>
                          <td>
                            <span class="badge <?= $p['status'] == 'Pago' ? 'bg-success' : 'bg-warning'; ?>">
                              <?= $p['status']; ?>
                            </span>
                          </td>
                          <td class="text-center">

                            <?php if ($p['status'] != 'Pago'): ?>
                              <button class="btn btn-xs btn-success" onclick="marcarComoPago(<?= $p['id']; ?>)">
                                <i class="fa fa-check"></i> Baixar
                              </button>
                            <?php else: ?>
                              <button class="btn btn-xs btn-default" disabled><i class="fa fa-check-circle"></i> Pago</button>
                            <?php endif; ?>

                            <button class="btn btn-xs btn-primary ml5" onclick="gerarComprovante(<?= $p['id']; ?>, <?= $aluno_id; ?>)">
                              <i class="fa fa-file-pdf-o"></i> Comprovante
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
</div>

<script>
  // 1. Abre e fecha os detalhes (Sanfona)
  window.toggleParcelas = function (id) {
    // Evita erro se clicar no botão e propagar para a tr
    if (event.target.tagName === 'BUTTON' || event.target.tagName === 'I' && event.target.parentNode.tagName === 'BUTTON') {
      return;
    }

    var detailRow = $('#detail-' + id);
    var icon = $('#icon-' + id);

    if (detailRow.is(':visible')) {
      detailRow.hide();
      icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
    } else {
      $('.bombeiros-detail-row').hide();
      $('.fa-minus-circle').removeClass('fa-minus-circle').addClass('fa-plus-circle');
      detailRow.fadeIn();
      icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
    }
  };

  // 2. Marca parcela como Paga
  window.marcarComoPago = function (cobrancaId) {
    if (!confirm("Confirmar recebimento desta parcela?")) return;

    $.ajax({
      url: '<?php echo get_uri("bombeiros/baixar_pagamento"); ?>',
      type: 'POST',
      data: {
        id: cobrancaId,
        '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
      },
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          appAlert.success(res.message);
          // Recarrega a aba para atualizar status visualmente
          $('a[href="#tab-pagamentos"]').trigger('click');
        } else {
          appAlert.error(res.message);
        }
      },
      error: function () {
        appAlert.error("Erro ao processar.");
      }
    });
  };

  // 3. Busca dados para abrir o Modal de Comprovante
  window.gerarComprovante = function (cobrancaId, alunoId) {
    // Debug para ver se os IDs chegaram
    console.log("ID Cobrança:", cobrancaId, "ID Aluno:", alunoId);

    if (!cobrancaId || !alunoId) {
      appAlert.error("Erro: Identificadores inválidos.");
      return;
    }

    appLoader.show();

    $.ajax({
      url: '<?php echo get_uri("bombeiros/buscar_dados_comprovante"); ?>',
      type: 'POST',
      data: {
        cobranca_id: cobrancaId, // TEM QUE SER EXATAMENTE ESTE NOME NO PHP
        aluno_id: alunoId,       // TEM QUE SER EXATAMENTE ESTE NOME NO PHP
        '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
      },
      dataType: 'json',
      success: function (res) {
        appLoader.hide();
        if (res.success) {
          var dados = res.data;

          // Preenche os Inputs Hidden do Modal (Essenciais para salvar depois)
          $('#comprovante-cobranca-id').val(cobrancaId);
          $('#comprovante-aluno-id').val(alunoId);

          // Preenche os campos visuais
          $('#comprovante-responsavel-nome').val(dados.responsavel_nome);
          $('#comprovante-responsavel-cpf').val(dados.responsavel_cpf);
          $('#comprovante-aluno-nome').val(dados.aluno_nome);
          $('#comprovante-valor').val(dados.valor);
          $('#comprovante-mensalidade').val(dados.mensalidade_numero);
          $('#comprovante-data-emissao').val(dados.data_emissao);

          // Abre o modal que está no index.php
          $('#modal-comprovante').modal('show');
        } else {
          appAlert.error(res.message);
        }
      },
      error: function () {
        appLoader.hide();
        appAlert.error("Erro ao buscar dados do comprovante.");
      }
    });
  };

  // 4. Salva e Gera o PDF (Função chamada pelo botão do Modal)
  // 4. Salva, Baixa Parcela e Faz Download Automático
  window.salvarEgerarComprovante = function () {
    var dados = {
      cobranca_id: $('#comprovante-cobranca-id').val(),
      aluno_id: $('#comprovante-aluno-id').val(),
      responsavel_nome: $('#comprovante-responsavel-nome').val(),
      responsavel_cpf: $('#comprovante-responsavel-cpf').val(),
      aluno_nome: $('#comprovante-aluno-nome').val(),
      aluno_nome_adicional: $('#comprovante-aluno-nome-adicional').val(),
      mensalidade_numero: $('#comprovante-mensalidade').val(),
      valor: $('#comprovante-valor').val(),
      forma_pagamento: $('#comprovante-forma-pagamento').val(),
      data_emissao: $('#comprovante-data-emissao').val(),
      conferido_por: $('#comprovante-conferido-por').val(),
      data_conferencia: $('#comprovante-data-conferencia').val(),
      '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
    };

    if (!dados.forma_pagamento) {
      appAlert.error("Selecione a forma de pagamento.");
      return;
    }

    var btn = $('#btn-salvar-comprovante'); // ID do botão no seu modal (verifique se tem id="btn-salvar-comprovante")
    var textoOriginal = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Gerando...');

    $.ajax({
      url: '<?php echo get_uri("bombeiros/gerar_comprovante"); ?>',
      type: 'POST',
      data: dados,
      dataType: 'json',
      success: function (res) {
        btn.prop('disabled', false).html(textoOriginal);

        if (res.success) {
          $('#modal-comprovante').modal('hide');

          // --- LÓGICA DE DOWNLOAD AUTOMÁTICO ---
          var link = document.createElement('a');
          link.href = res.download_url;
          link.download = ''; // Deixe vazio para usar o nome do servidor
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          // -------------------------------------

          appAlert.success(res.message);

          // Atualiza a tela para mostrar que foi pago (sai do vermelho, fica verde)
          // Pequeno delay para garantir que o banco atualizou
          setTimeout(function () {
            $('a[href="#tab-pagamentos"]').trigger('click');
            // Se tiver aba de financeiro resumo aberta, recarrega ela também
            if ($('#tab-financeiro-geral').hasClass('active')) {
              $('#area-financeiro-geral').load('<?php echo get_uri("bombeiros/financeiro_resumo"); ?>');
            }
          }, 500);

        } else {
          appAlert.error(res.message);
        }
      },
      error: function () {
        btn.prop('disabled', false).html(textoOriginal);
        appAlert.error("Erro de comunicação.");
      }
    });
  };
</script>