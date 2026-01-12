<div class="p20 filter-section">
  <div class="row mb20">
    <div class="col-md-4">
      <div class="input-group">
        <span class="input-group-text"><i class="fa fa-search"></i></span>
        <input type="text" id="busca-responsaveis" class="form-control" placeholder="Buscar por nome, CPF, telefone ou email...">
      </div>
    </div>
  </div>
</div>

<div class="table-responsive">
  <table id="tabela-responsaveis" class="table table-hover excel-view" cellspacing="0" width="100%">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>CPF</th>
        <th>WhatsApp</th>
        <th>Celular</th>
        <th>E-mail</th>
        <th>Endereço</th>
        <th class="text-center"><i class="fa fa-bars"></i></th>
      </tr>
    </thead>
    <tbody id="lista-responsaveis-body">
      <?php foreach ($responsaveis as $resp): ?>
        <tr data-id="<?= $resp['id']; ?>">
          <td>#<?= $resp['id']; ?></td>
          <td><input name="nome" value="<?= htmlspecialchars($resp['nome'] ?? ''); ?>" class="form-control-excel"></td>
          <td><input name="cpf" value="<?= htmlspecialchars($resp['cpf'] ?? ''); ?>" class="form-control-excel mask-cpf"></td>
          <td><input name="whats" value="<?= htmlspecialchars($resp['whats'] ?? ''); ?>" class="form-control-excel mask-tel"></td>
          <td><input name="celular" value="<?= htmlspecialchars($resp['celular'] ?? ''); ?>" class="form-control-excel mask-tel"></td>
          <td><input name="email" value="<?= htmlspecialchars($resp['email'] ?? ''); ?>" class="form-control-excel"></td>
          <td><input name="endereco" value="<?= htmlspecialchars($resp['endereco'] ?? ''); ?>" class="form-control-excel"></td>
          <td class="text-center">
            <button class="btn btn-xs btn-danger" onclick="confirmarExclusaoResponsavel(<?= $resp['id']; ?>, this)">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
  $(document).ready(function () {
    // Máscaras
    $('.mask-cpf').mask('000.000.000-00', { reverse: true });
    var SPMaskBehavior = function (val) {
      return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    };
    var spOptions = {
      onKeyPress: function (val, e, field, options) {
        field.mask(SPMaskBehavior.apply({}, arguments), options);
      }
    };
    $('.mask-tel').mask(SPMaskBehavior, spOptions);

    // Busca
    $('#busca-responsaveis').on('keyup', function () {
      var value = $(this).val().toLowerCase();
      $('#lista-responsaveis-body tr').each(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
      });
    });

    // Detecta alteração
    $(document).on('change input', '.form-control-excel', function () {
      $(this).closest('tr').addClass('linha-alterada');
      $('#btn-salvar-responsaveis').fadeIn(); // Certifique-se que este botão existe no seu HTML
    });
  });

  window.salvarAlteracoesResponsaveis = function () {
    let alterados = $('#lista-responsaveis-body .linha-alterada');

    if (alterados.length === 0) {
      appAlert.info("Nenhuma alteração para salvar.");
      return;
    }

    if (typeof appLoader !== 'undefined') appLoader.show();

    let promises = [];

    alterados.each(function () {
      let row = $(this);
      let data = {
        id: row.data('id'),
        nome: row.find('[name="nome"]').val(),
        cpf: row.find('[name="cpf"]').val(),
        whats: row.find('[name="whats"]').val(),
        celular: row.find('[name="celular"]').val(),
        email: row.find('[name="email"]').val(),
        endereco: row.find('[name="endereco"]').val(),
        '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
      };

      let request = $.ajax({
        url: '<?php echo get_uri("bombeiros/salvar_responsavel"); ?>',
        type: 'POST',
        data: data,
        dataType: 'json'
      }).then(
        res => res, // Sucesso
        err => {    // Erro de rede/servidor
          console.error("Erro crítico na linha " + data.id, err);
          return { success: false, message: "Erro de conexão no servidor (ID: " + data.id + ")" };
        }
      );

      promises.push(request);
    });

    Promise.all(promises).then(results => {
      if (typeof appLoader !== 'undefined') appLoader.hide();

      let erros = results.filter(r => !r || r.success === false);

      if (erros.length === 0) {
        appAlert.success("Todos os responsáveis foram salvos!");
        setTimeout(() => location.reload(), 1000);
      } else {
        let msg = erros.map(e => e.message || "Erro desconhecido").join("<br>");
        appAlert.error("Alguns erros ocorreram:<br>" + msg);
      }
    }).catch(fatal => {
      if (typeof appLoader !== 'undefined') appLoader.hide();
      console.error("Erro fatal no loop:", fatal);
      appAlert.error("Erro crítico ao processar salvamento.");
    });
  }
</script>