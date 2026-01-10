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
  $(document).ready(function() {
    // Aplica máscaras
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

    // Barra de pesquisa
    $('#busca-responsaveis').on('keyup', function() {
      var value = $(this).val().toLowerCase();
      $('#lista-responsaveis-body tr').filter(function() {
        var texto = $(this).text().toLowerCase();
        $(this).toggle(texto.indexOf(value) > -1);
      });
    });

    // Marca linha como alterada ao editar
    $(document).on('change input', '.form-control-excel', function () {
      $(this).closest('tr').addClass('linha-alterada');
      // Mostra o botão de salvar que está no header
      $('#btn-salvar-responsaveis').fadeIn();
    });
  });

  window.salvarAlteracoesResponsaveis = function() {
    let promises = [];
    let hasChanges = false;
    
    $('#lista-responsaveis-body .linha-alterada').each(function () {
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
      
      hasChanges = true;
      
      console.log("Enviando dados do responsável:", data);
      
      promises.push(
        $.ajax({
          url: '<?php echo get_uri("bombeiros/salvar_responsavel"); ?>',
          type: 'POST',
          data: data,
          dataType: 'json',
          success: function(response) {
            console.log("Resposta do servidor:", response);
            return response;
          },
          error: function(xhr, status, error) {
            console.error("Erro na requisição:", error);
            console.error("Resposta:", xhr.responseText);
            
            let errorResponse = { success: false, message: "Erro na requisição" };
            try {
              if (xhr.responseText) {
                errorResponse = JSON.parse(xhr.responseText);
              }
            } catch (e) {
              errorResponse.message = "Erro na requisição: " + (xhr.responseText || error);
            }
            
            return errorResponse;
          }
        })
      );
    });

    if (!hasChanges) {
      appAlert.info("Nenhuma alteração para salvar.");
      return;
    }

    Promise.all(promises).then(function (results) {
      let allSuccess = true;
      let errorMessages = [];
      
      results.forEach(function (res, index) {
        if (typeof res === 'string') {
          try {
            res = JSON.parse(res);
          } catch (e) {
            console.error("Erro ao parsear resposta:", res);
            allSuccess = false;
            errorMessages.push("Erro ao processar resposta do servidor");
            return;
          }
        }
        
        if (!res || res.success === false) {
          allSuccess = false;
          let errorMsg = res && res.message ? res.message : "Erro desconhecido ao salvar linha " + (index + 1);
          errorMessages.push(errorMsg);
          console.error("Erro ao salvar linha " + (index + 1) + ":", res);
        }
      });

      if (allSuccess) {
        appAlert.success("Dados atualizados com sucesso!");
        setTimeout(function () {
          location.reload();
        }, 1000);
      } else {
        let errorMsg = "Alguns dados não puderam ser salvos.\n\n";
        if (errorMessages.length > 0) {
          errorMsg += "Erros encontrados:\n" + errorMessages.join("\n");
        }
        appAlert.error(errorMsg);
        console.error("Erros detalhados:", errorMessages);
      }
    }).catch(function (error) {
      console.error("Erro ao salvar:", error);
      appAlert.error("Erro ao salvar alterações. Verifique o console (F12) para mais detalhes.");
    });
  }

  window.confirmarExclusaoResponsavel = function(id, btn) {
    if (typeof confirmarAcao === 'function') {
      confirmarAcao("Excluir Responsável", "Tem certeza que deseja apagar este responsável permanentemente? Esta ação não pode ser desfeita.", function () {
        let row = $(btn).closest('tr');
        $.post('<?php echo get_uri("bombeiros/deletar_responsavel"); ?>', {
          id: id, 
          '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
        }, function (res) {
          if (res.success) {
            appAlert.warning("Responsável removido com sucesso.");
            row.fadeOut();
          } else {
            appAlert.error("Erro ao deletar.");
          }
        }, 'json');
      });
    } else {
      if (confirm("Tem certeza que deseja apagar este responsável permanentemente? Esta ação não pode ser desfeita.")) {
        let row = $(btn).closest('tr');
        $.post('<?php echo get_uri("bombeiros/deletar_responsavel"); ?>', {
          id: id, 
          '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
        }, function (res) {
          if (res.success) {
            appAlert.warning("Responsável removido com sucesso.");
            row.fadeOut();
          } else {
            appAlert.error("Erro ao deletar.");
          }
        }, 'json');
      }
    }
  };
</script>
