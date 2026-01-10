<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<div id="page-content" class="page-wrapper clearfix">
  <div class="card rise-siamesa-wrapper">
    <div class="page-title clearfix">
      <h1>SIAMESA - Sistema Administrativo</h1>
      <div class="title-button-group">
        <button class="btn btn-info" onclick="abrirModalImportar()">
          <i class="fa fa-upload"></i> Importar Excel
        </button>
        <button class="btn btn-primary" id="btn-salvar-geral" style="display: none;" onclick="salvarAlteracoes()">
          <i class="fa fa-check"></i> Salvar Alterações
        </button>
        <button class="btn btn-warning" id="btn-salvar-responsaveis" style="display: none;" onclick="salvarAlteracoesResponsaveis()">
          <i class="fa fa-check"></i> Salvar Responsáveis
        </button>
        <button class="btn btn-primary" onclick="abrirModalNovoAluno()">
          <i class="fa fa-plus-circle"></i> Novo Aluno
        </button>
        <button class="btn btn-success" onclick="abrirModalUnidade()">
          <i class="fa fa-building"></i> Cadastrar Unidade
        </button>
      </div>
    </div>

    <ul id="siamesa-tabs" class="nav nav-tabs" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-alunos"><i class="fa fa-users"></i> Alunos (Matrículas)</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-responsaveis"><i class="fa fa-user"></i> Responsáveis</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-presenca"><i class="fa fa-check-square"></i> Lista de Presença</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-pagamentos"><i class="fa fa-money"></i> Pagamentos do Mês</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-financeiro-geral"><i class="fa fa-pie-chart"></i> Relatório de Inadimplência</a>
      </li>
    </ul>

    <div class="tab-content">
      <div role="tabpanel" class="tab-pane fade show active" id="tab-alunos">
        <div class="p20 filter-section">
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Filtrar por Unidade</label>
              <select id="filtro-unidade" class="form-control form-select" onchange="filtrarPorUnidade()">
                <option value="">Todas as Unidades</option>
                <?php foreach ($unidades as $unidade): ?>
                  <option value="<?= $unidade['id']; ?>" <?= (isset($unidade_selecionada) && $unidade_selecionada == $unidade['id']) ? 'selected' : ''; ?>>
                    <?= esc($unidade['nome_unidade']); ?> - <?= esc($unidade['cidade']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Buscar Aluno</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
                <input type="text" id="busca-alunos" class="form-control" placeholder="Buscar por nome, responsável, telefone ou matrícula...">
              </div>
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <table id="tabela-alunos" class="table table-hover excel-view" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th>Matrícula</th>
                <th>Unidade</th>
                <th>Aluno</th>
                <th>Responsável</th>
                <th>Whats/Celular</th>
                <th>Turma (Horário)</th>
                <th>Camisa?</th>
                <th>Tam.</th>
                <th>Status</th>
                <th class="text-center"><i class="fa fa-bars"></i></th>
              </tr>
            </thead>
            <tbody id="lista-alunos-body">
              <?php foreach ($alunos as $aluno): ?>
                <tr data-id="<?= $aluno['id']; ?>">
                  <td>#<?= $aluno['id']; ?></td>
                  <td><small class="text-muted"><?= esc($aluno['nome_unidade'] ?? 'N/A'); ?><br><?= esc($aluno['cidade'] ?? ''); ?></small></td>
                  <td><input name="nome_aluno" value="<?= $aluno['nome_aluno']; ?>" class="form-control-excel"></td>
                  <td><input name="responsavel_nome" value="<?= $aluno['responsavel_nome']; ?>" class="form-control-excel"></td>
                  <td><input name="responsavel_whats" value="<?= $aluno['responsavel_whats']; ?>" class="form-control-excel mask-tel"></td>
                  <td>
                    <select name="horario" class="form-control-excel">
                      <option value="08:30-11:00" <?= $aluno['turma'] == '08:30-11:00' ? 'selected' : ''; ?>>08:30–11:00</option>
                      <option value="11:30-14:00" <?= $aluno['turma'] == '11:30-14:00' ? 'selected' : ''; ?>>11:30–14:00</option>
                      <option value="14:30-17:00" <?= $aluno['turma'] == '14:30-17:00' ? 'selected' : ''; ?>>14:30–17:00</option>
                    </select>
                  </td>
                  <td>
                    <select name="quer_camisa" class="form-control-excel">
                      <option value="1" <?= $aluno['quer_camisa'] ? 'selected' : ''; ?>>Sim</option>
                      <option value="0" <?= !$aluno['quer_camisa'] ? 'selected' : ''; ?>>Não</option>
                    </select>
                  </td>
                  <td><input name="tamanho_camisa" value="<?= $aluno['tamanho_camisa']; ?>" class="form-control-excel input-tamanho"></td>
                  <td>
                    <select name="status" class="form-control-excel">
                      <option value="Ativo" <?= $aluno['status'] == 'Ativo' ? 'selected' : ''; ?>>Ativo</option>
                      <option value="Cancelado" <?= $aluno['status'] == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                  </td>
                  <td class="text-center">
                    <button class="btn btn-xs btn-danger" onclick="confirmarExclusao(<?= $aluno['id']; ?>, this)"><i class="fa fa-trash"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div role="tabpanel" class="tab-pane fade" id="tab-responsaveis">
        <div id="area-responsaveis" class="p20">
          <p class="text-center text-off"><i class="fa fa-spinner fa-spin"></i> Carregando responsáveis...</p>
        </div>
      </div>

      <div role="tabpanel" class="tab-pane fade" id="tab-presenca">
        <div class="p20">
          <div class="row justify-content-center mb20">
            <div class="col-md-3">
              <label class="form-label">Data da Aula</label>
              <input type="date" id="data-chamada" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Turma</label>
              <select id="filtro-turma-chamada" class="form-select">
                <option value="">Selecione...</option>
                <option value="08:30-11:00">08:30–11:00 (Alfa)</option>
                <option value="11:30-14:00">11:30–14:00 (Bravo)</option>
                <option value="14:30-17:00">14:30–17:00 (Charlie)</option>
              </select>
            </div>
          </div>
          <div id="area-chamada" class="mt20 border-top pt20 text-center">
            <p class="text-off">Selecione a turma para carregar a lista de chamada.</p>
          </div>
        </div>
      </div>

      <div role="tabpanel" class="tab-pane fade" id="tab-pagamentos">
        <div class="p20" id="area-pagamentos">
          <p class="text-center text-off"><i class="fa fa-spinner fa-spin"></i> Carregando financeiro...</p>
        </div>
      </div>

      <div role="tabpanel" class="tab-pane fade" id="tab-financeiro-geral">
        <div id="area-financeiro-geral" class="p20">
          <p class="text-center text-off"><i class="fa fa-spinner fa-spin"></i> Gerando relatórios...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modal-aluno" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="form-siamesa-aluno">
        <input type="hidden" name="<?= csrf_token(); ?>" value="<?= csrf_hash(); ?>" />
        <div class="modal-header">
          <h4>Novo Cadastro - SIAMESA</h4>
        </div>
        <div class="modal-body">
          <h5><strong>Grupo A: Responsável</strong></h5>
          <div class="row">
            <div class="col-md-6 mb-2"><label class="form-label">Nome do Responsável *</label><input type="text" name="responsavel_nome" class="form-control" required></div>
            <div class="col-md-6 mb-2"><label class="form-label">CPF *</label><input type="text" name="responsavel_cpf" class="form-control mask-cpf" required></div>
            <div class="col-md-6 mb-2"><label class="form-label">WhatsApp/Celular *</label><input type="text" name="responsavel_whats" class="form-control mask-tel" required></div>
            <div class="col-md-6 mb-2"><label class="form-label">E-mail</label><input type="email" name="responsavel_email" class="form-control"></div>
          </div>
          <hr>
          <h5><strong>Grupo B: Dados do Aluno</strong></h5>
          <div class="row">
            <div class="col-md-8 mb-2"><label class="form-label">Nome do Aluno *</label><input type="text" name="nome_aluno" class="form-control" required></div>
            <div class="col-md-4 mb-2"><label class="form-label">Data Nascimento *</label><input type="date" name="nascimento_aluno" class="form-control" required></div>
            <div class="col-md-6 mt-3">
              <label><input type="checkbox" name="quer_camisa" value="1" id="comprar_camiseta_check"> Comprar Camiseta (R$ 67,00)</label>
            </div>
            <div class="col-md-6 mt-1" id="div_tamanho" style="display: none;">
              <label class="form-label">Tamanho</label>
              <select name="tamanho_camisa" class="form-select">
                <option value="">Selecione...</option>
                <option value="PP">PP</option>
                <option value="P">P</option>
                <option value="M">M</option>
                <option value="G">G</option>
                <option value="GG">GG</option>
              </select>
            </div>
          </div>
          <hr>
          <h5><strong>Grupo C: Curso e Pagamento</strong></h5>
          <div class="row">
            <div class="col-md-4 mb-2">
              <label>Unidade *</label>
              <select name="unidade_id" class="form-select" required>
                <option value="">Selecione a Unidade...</option>
                <?php foreach ($unidades as $unidade): ?>
                  <option value="<?= $unidade['id']; ?>"><?= esc($unidade['nome_unidade']); ?> - <?= esc($unidade['cidade']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Horário (Turma) *</label>
              <select name="horario" class="form-select" required>
                <option value="08:30-11:00">08:30–11:00</option>
                <option value="11:30-14:00">11:30–14:00</option>
                <option value="14:30-17:00">14:30–17:00</option>
              </select>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Data de Início (Matrícula) *</label>
              <input type="date" name="data_inicio" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Mensalidade (R$) *</label>
              <input type="text" name="valor_mensalidade" class="form-control mask-money" value="150,00" required>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Quantidade de Parcelas *</label>
              <input type="number" name="num_parcelas" class="form-control" value="6" min="1" max="24" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-bs-dismiss="modal">Fechar</button>
          <button type="submit" class="btn btn-primary">Finalizar Matrícula</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modal-confirmacao-siamesa" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document" style="max-width: 400px;">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="confirm-title">Confirmar Ação</h4>
      </div>
      <div class="modal-body" id="confirm-body">
        Deseja realmente prosseguir?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="confirm-btn-ok" class="btn btn-danger">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Cadastrar/Editar Unidade -->
<div class="modal fade" id="modal-unidade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Cadastrar Unidade</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form id="form-unidade">
        <input type="hidden" name="<?= csrf_token(); ?>" value="<?= csrf_hash(); ?>" />
        <input type="hidden" name="id" id="unidade-id">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Nome da Unidade <span class="text-danger">*</span></label>
              <input type="text" name="nome_unidade" id="unidade-nome" class="form-control" required>
            </div>
            <div class="col-md-12 mb-3">
              <label class="form-label">Cidade <span class="text-danger">*</span></label>
              <input type="text" name="cidade" id="unidade-cidade" class="form-control" required>
            </div>
            <div class="col-md-12 mb-3">
              <label class="form-label">Endereço</label>
              <input type="text" name="endereco" id="unidade-endereco" class="form-control">
            </div>
            <div class="col-md-12 mb-3">
              <label class="form-label">Status</label>
              <select name="status" id="unidade-status" class="form-select" required>
                <option value="Ativo" selected>Ativo</option>
                <option value="Inativo">Inativo</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Salvar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal para Gerar Comprovante -->
<div class="modal fade" id="modal-comprovante" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Gerar Comprovante de Pagamento</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="comprovante-cobranca-id">
        <input type="hidden" id="comprovante-aluno-id">

        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Responsável <span class="text-danger">*</span></label>
            <input type="text" id="comprovante-responsavel-nome" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label>CPF do Responsável <span class="text-danger">*</span></label>
            <input type="text" id="comprovante-responsavel-cpf" class="form-control mask-cpf" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Aluno(a) <span class="text-danger">*</span></label>
            <input type="text" id="comprovante-aluno-nome" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label>Aluno(a) Adicional (opcional)</label>
            <input type="text" id="comprovante-aluno-nome-adicional" class="form-control">
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label>Mensalidade Referente <span class="text-danger">*</span></label>
            <select id="comprovante-mensalidade" class="form-control" required>
              <option value="1">1º Mensalidade</option>
              <option value="2">2º Mensalidade</option>
              <option value="3">3º Mensalidade</option>
              <option value="4">4º Mensalidade</option>
              <option value="5">5º Mensalidade</option>
              <option value="6">6º Mensalidade</option>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label>Valor (R$) <span class="text-danger">*</span></label>
            <input type="text" id="comprovante-valor" class="form-control mask-money" required>
          </div>
          <div class="col-md-4 mb-3">
            <label>Forma de Pagamento <span class="text-danger">*</span></label>
            <select id="comprovante-forma-pagamento" class="form-control" required>
              <option value="">Selecione...</option>
              <option value="BOLETO">BOLETO</option>
              <option value="CRÉDITO">CRÉDITO</option>
              <option value="DÉBITO">DÉBITO</option>
              <option value="PIX">PIX</option>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Data de Emissão <span class="text-danger">*</span></label>
            <input type="date" id="comprovante-data-emissao" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label>Conferido por</label>
            <input type="text" id="comprovante-conferido-por" class="form-control">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Data de Conferência</label>
            <input type="date" id="comprovante-data-conferencia" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btn-salvar-comprovante" class="btn btn-primary" onclick="salvarEgerarComprovante()">
          <i class="fa fa-file-pdf-o"></i> Gerar PDF
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  /* WRAPPER PRINCIPAL - GARANTE O ESCOPO */
  .rise-siamesa-wrapper {
    --siamesa-text: var(--bs-body-color);
    --siamesa-border: var(--bs-border-color);
    --siamesa-accent-rgb: var(--bs-primary-rgb);

    color: var(--siamesa-text);
  }

  /* RESET DE PAINÉIS E CARDS (Resolve o fundo branco da Image 5 e 6) */
  .rise-siamesa-wrapper .panel,
  .rise-siamesa-wrapper .panel-body,
  .rise-siamesa-wrapper .card,
  .rise-siamesa-wrapper .card-body {
    background-color: transparent !important;
    border-color: var(--siamesa-border);
    color: var(--siamesa-text);
    box-shadow: none !important;
  }

  /* RESET DE TABELAS (Neutraliza o Bootstrap 5 nativo da Image 4) */
  .rise-siamesa-wrapper .table {
    --bs-table-bg: transparent;
    /* Remove bloqueio de cor da Image 62d1a7 */
    --bs-table-color: inherit;
    --bs-table-border-color: var(--siamesa-border);
    --bs-table-hover-bg: rgba(var(--siamesa-accent-rgb), 0.05);
    margin-bottom: 0;
    color: var(--siamesa-text);
  }

  /* GARANTE QUE O TEXTO NÃO FIQUE PRETO (Image 4/6) */
  .rise-siamesa-wrapper .table th,
  .rise-siamesa-wrapper .table td,
  .rise-siamesa-wrapper strong,
  .rise-siamesa-wrapper b,
  .rise-siamesa-wrapper span:not(.badge) {
    color: inherit !important;
  }

  /* CABEÇALHO DA TABELA */
  .rise-siamesa-wrapper .table thead th {
    background-color: transparent;
    border-bottom: 2px solid var(--siamesa-border);
    font-weight: 600;
  }

  /* LINHA DE DETALHE E SUB-NÍVEL (Image 6) */
  .rise-siamesa-wrapper .bombeiros-detail-row {
    background-color: rgba(var(--siamesa-accent-rgb), 0.03) !important;
  }

  .rise-siamesa-wrapper .detail-container {
    padding: 15px 15px 15px 40px;
    border-left: 3px solid var(--primary-color);
    /* Cor do tema da Image 1 */
    background-color: transparent;
  }

  select option {
    background-color: #0b1020 !important;
    /* Mesma cor escura do seu fundo */
    color: #e5e7eb !important;
    /* Garante que o texto fique claro */
  }

  /* INPUTS EDITÁVEIS (EXCEL STYLE) */
  .rise-siamesa-wrapper .form-control-excel,
  .rise-siamesa-wrapper .table td input:not([type="checkbox"]),
  .rise-siamesa-wrapper .table td select {
    background-color: transparent !important;
    border: 1px solid transparent;
    color: var(--siamesa-text) !important;
    padding: 4px 8px;
    transition: all 0.2s ease;
  }

  .rise-siamesa-wrapper .form-control-excel:hover {
    border-color: var(--siamesa-border);
  }

  .rise-siamesa-wrapper .form-control-excel:focus {
    background-color: var(--bs-body-bg) !important;
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 2px rgba(var(--siamesa-accent-rgb), 0.2);
  }

  /* INPUTS PADRÃO (FILTROS) */
  .rise-siamesa-wrapper .form-control,
  .rise-siamesa-wrapper .form-select {
    background-color: rgba(var(--bs-emphasis-color-rgb), 0.05) !important;
    border-color: var(--siamesa-border);
    color: var(--siamesa-text) !important;
  }

  /* DESTAQUES DE LINHA */
  .rise-siamesa-wrapper .linha-alterada {
    background-color: rgba(var(--bs-warning-rgb), 0.1) !important;
  }

  .rise-siamesa-wrapper tr.total-row {
    background-color: rgba(var(--bs-info-rgb), 0.1) !important;
    font-weight: bold;
  }

  /* UTILITÁRIOS */
  .rise-siamesa-wrapper .text-off {
    opacity: 0.6;
    color: var(--siamesa-text) !important;
  }

  .rise-siamesa-wrapper .ml5 {
    margin-left: 0.5rem;
  }

  /* CORREÇÃO PARA BADGES */
  .rise-siamesa-wrapper .badge {
    color: #fff !important;
    /* Badges mantêm contraste branco */
  }

  /* Estiliza o campo de seleção de Status */
  #unidade-status {
    background-color: #0b1020 !important;
    /* Fundo escuro */
    color: #e5e7eb !important;
    /* Texto claro */
    border: 1px solid #374151;
    /* Borda cinza escura para combinar */
  }

  /* Garante que a lista de opções, ao abrir, também seja escura */
  #unidade-status option {
    background-color: #0b1020 !important;
    color: #e5e7eb !important;
  }
</style>

<script>
  // Função para filtrar por unidade
  function filtrarPorUnidade() {
    var unidadeId = $('#filtro-unidade').val();
    var url = '<?php echo get_uri("bombeiros"); ?>';

    if (unidadeId && unidadeId != '') {
      url += '?unidade_id=' + unidadeId;
    }

    window.location.href = url;
  }

  // Função para abrir modal de unidade
  function abrirModalUnidade(id) {
    $('#form-unidade')[0].reset();
    $('#unidade-id').val('');
    $('#modal-unidade .modal-title').text('Cadastrar Unidade');

    if (id) {
      // Editar - buscar dados
      $.ajax({
        url: '<?php echo get_uri("bombeiros/buscar_unidade"); ?>',
        type: 'POST',
        data: {
          id: id,
          '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
        },
        dataType: 'json',
        success: function (res) {
          if (res.success) {
            var unidade = res.data;
            $('#unidade-id').val(unidade.id);
            $('#unidade-nome').val(unidade.nome_unidade);
            $('#unidade-cidade').val(unidade.cidade);
            $('#unidade-endereco').val(unidade.endereco || '');
            $('#unidade-status').val(unidade.status);
            $('#modal-unidade .modal-title').text('Editar Unidade');
            $('#modal-unidade').modal('show');
          }
        }
      });
    } else {
      $('#modal-unidade').modal('show');
    }
  }

  // Salvar unidade
  $(document).ready(function () {
    $('#form-unidade').on('submit', function (e) {
      e.preventDefault();

      var btn = $(this).find('button[type="submit"]');
      var textoOriginal = btn.html();
      btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');

      $.ajax({
        url: '<?php echo get_uri("bombeiros/salvar_unidade"); ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function (res) {
          if (res.success) {
            appAlert.success(res.message);
            $('#modal-unidade').modal('hide');
            location.reload();
          } else {
            appAlert.error(res.message || 'Erro ao salvar unidade.');
            btn.prop('disabled', false).html(textoOriginal);
          }
        },
        error: function (xhr, status, error) {
          console.error("Erro ao salvar unidade:", error);
          appAlert.error("Erro ao salvar unidade. Tente novamente.");
          btn.prop('disabled', false).html(textoOriginal);
        }
      });
    });
  });
</script>

<script>
  function aplicarMascaras() {
    $('.mask-cpf').mask('000.000.000-00', { reverse: true });
    var SPMaskBehavior = function (val) { return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009'; },
      spOptions = { onKeyPress: function (val, e, field, options) { field.mask(SPMaskBehavior.apply({}, arguments), options); } };
    $('.mask-tel').mask(SPMaskBehavior, spOptions);
    // Máscara de moeda brasileira (R$ 1.234,56)
    $('.mask-money').mask('#.##0,00', { reverse: true });
  }

  window.abrirModalNovoAluno = function () { $("#modal-aluno").modal("show"); setTimeout(aplicarMascaras, 500); };
  window.abrirModalImportar = function () { $("#modal-importacao-siamesa").modal("show"); };

  // Funcao para substituir o confirm() do navegador
  function confirmarAcao(titulo, mensagem, callback) {
    $("#confirm-title").text(titulo);
    $("#confirm-body").text(mensagem);
    $("#modal-confirmacao-siamesa").modal("show");

    $("#confirm-btn-ok").off("click").on("click", function () {
      $("#modal-confirmacao-siamesa").modal("hide");
      callback();
    });
  }

  $(document).ready(function () {
    aplicarMascaras();

    $('#comprar_camiseta_check').on('change', function () {
      if ($(this).is(':checked')) { $('#div_tamanho').fadeIn(); $('select[name="tamanho_camisa"]').attr('required', true); }
      else { $('#div_tamanho').fadeOut(); $('select[name="tamanho_camisa"]').attr('required', false).val(''); }
    });

    $('a[href="#tab-responsaveis"]').on('shown.bs.tab', carregarResponsaveis);
    $('a[href="#tab-pagamentos"]').on('shown.bs.tab', carregarPagamentos);
    $('a[href="#tab-financeiro-geral"]').on('shown.bs.tab', carregarRelatorioFinanceiro);
    $('#data-chamada, #filtro-turma-chamada').on('change', carregarListaChamada);

    // Barra de busca de alunos
    $('#busca-alunos').on('keyup', function () {
      var value = $(this).val().toLowerCase();
      $('#lista-alunos-body tr').filter(function () {
        var texto = $(this).text().toLowerCase();
        $(this).toggle(texto.indexOf(value) > -1);
      });
    });

    $(document).on('change input', '.form-control-excel', function () {
      $(this).closest('tr').addClass('linha-alterada');
      $('#btn-salvar-geral').fadeIn();
    });

    // ============================================
    // OBSERVER PARA MUDANÇAS DE TEMA DINÂMICAS
    // Garante que o plugin se adapta quando o tema muda sem recarregar a página
    // ============================================
    function adaptarAoTema() {
      // Remove qualquer estilo inline que possa sobrescrever o tema
      var wrapper = $('.rise-siamesa-wrapper');
      if (wrapper.length) {
        // Remove estilos inline que possam interferir
        wrapper.find('[style*="background-color"], [style*="color"]').each(function () {
          var $el = $(this);
          var style = $el.attr('style') || '';
          // Remove apenas propriedades de cor, mantendo outras propriedades
          style = style.replace(/background-color[^;]*;?/gi, '');
          style = style.replace(/color[^;]*;?/gi, '');
          if (style.trim()) {
            $el.attr('style', style);
          } else {
            $el.removeAttr('style');
          }
        });
      }
    }

    // Observa mudanças no atributo data-theme e classes do body
    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.type === 'attributes' && (mutation.attributeName === 'class' || mutation.attributeName === 'data-theme')) {
          adaptarAoTema();
        }
      });
    });

    // Inicia observação do body para mudanças de tema
    if (document.body) {
      observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class', 'data-theme']
      });
    }
    adaptarAoTema();
    function limparEstilosInline() {
      var wrapper = $('.rise-siamesa-wrapper');
      if (wrapper.length) {
        // Remove apenas propriedades de cor de estilos inline, mantendo width, height, etc
        wrapper.find('[style*="background-color"], [style*="color"]').each(function () {
          var $el = $(this);
          var style = $el.attr('style') || '';
          style = style.replace(/background-color\s*:\s*[^;]+;?/gi, '');
          style = style.replace(/color\s*:\s*[^;]+;?/gi, '');
          if (style.trim()) {
            $el.attr('style', style.trim());
          } else {
            $el.removeAttr('style');
          }
        });
      }
    }
    $(document).on("themeChange", function () {
      limparEstilosInline();
      if (typeof $.fn.select2 !== 'undefined') {
        $('.rise-siamesa-wrapper .form-select').each(function () {
        });
      }
    });
    if (window.MutationObserver && document.body) {
      var themeObserver = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
          if (mutation.type === 'attributes' &&
            (mutation.attributeName === 'class' || mutation.attributeName === 'data-theme')) {
            // Pequeno delay para garantir que o Rise aplicou as novas variáveis CSS
            setTimeout(limparEstilosInline, 100);
          }
        });
      });

      themeObserver.observe(document.body, {
        attributes: true,
        attributeFilter: ['class', 'data-theme']
      });
    }

    // Executa uma vez no carregamento
    limparEstilosInline();

    // Escuta também eventos alternativos caso o Rise use nomes diferentes
    $(document).on('theme-changed risethemechange', function () {
      setTimeout(limparEstilosInline, 100);
    });

    // Formulário de novo aluno com tratamento de erros melhorado
    $("#form-siamesa-aluno").on("submit", function (e) {
      e.preventDefault();

      // Bloqueia o botão para evitar clique duplo
      var btn = $(this).find('button[type="submit"]');
      var textoOriginal = btn.html();
      btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');

      $.ajax({
        url: '<?php echo get_uri("bombeiros/salvar"); ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function (res) {
          if (res.success) {
            appAlert.success("Matrícula realizada com sucesso!");
            location.reload();
          } else {
            appAlert.error(res.message || "Erro ao salvar. Verifique os dados.");
            btn.prop('disabled', false).html(textoOriginal);
          }
        },
        error: function (xhr, status, error) {
          // Aqui vamos pegar o erro real do PHP
          console.error("Erro na requisição:", error);
          console.error("Resposta do servidor:", xhr.responseText);

          // Tenta parsear a resposta como JSON para mostrar mensagem mais clara
          var errorMsg = "Ocorreu um erro no servidor.";
          try {
            var response = JSON.parse(xhr.responseText);
            if (response.message) {
              errorMsg = response.message;
            }
          } catch (e) {
            // Se não for JSON, mostra a resposta bruta
            errorMsg = "Erro: " + (xhr.responseText || error);
          }

          appAlert.error(errorMsg);
          alert("Erro ao salvar. Verifique o console (F12) para mais detalhes. Mensagem: " + errorMsg);
          btn.prop('disabled', false).html(textoOriginal);
        }
      });
    });
  });

  function carregarListaChamada() {
    let d = $('#data-chamada').val(); let t = $('#filtro-turma-chamada').val();
    if (!d || !t) return;
    $("#area-chamada").html('<i class="fa fa-spinner fa-spin"></i> Carregando...');
    $.post('<?php echo get_uri("bombeiros/lista_chamada"); ?>', { data: d, turma: t, '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>' },
      function (html) { $("#area-chamada").html(html); });
  }

  function salvarChamada() {
    let data_aula = $("#data-chamada").val();
    let presencas = {};
    $('input[type="radio"]:checked').each(function () {
      let id = $(this).attr('name').replace('p_', '');
      presencas[id] = $(this).val();
    });

    $.post('<?php echo get_uri("bombeiros/salvar_presenca"); ?>', { data_aula: data_aula, presencas: presencas, '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>' },
      function (res) {
        if (res.success) appAlert.success("Chamada salva!");
        else appAlert.error(res.message);
      }, 'json');
  }

  function carregarResponsaveis() {
    // Coloca o spinner
    $("#area-responsaveis").html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Carregando responsáveis...</div>');

    // Faz a requisição com tratamento de erros
    $.ajax({
      url: '<?php echo get_uri("bombeiros/lista_responsaveis"); ?>',
      type: 'GET',
      success: function (html) {
        $("#area-responsaveis").html(html);
      },
      error: function (xhr, status, error) {
        console.error("Erro ao carregar responsáveis:", error);
        console.error("Resposta do servidor:", xhr.responseText);
        $("#area-responsaveis").html('<div class="alert alert-danger text-center p20">Erro ao carregar responsáveis. Tente recarregar a página.<br><small>' + error + '</small></div>');
      }
    });
  }

  function carregarPagamentos() {
    // Coloca o spinner
    $("#area-pagamentos").html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Carregando pagamentos...</div>');

    // Faz a requisição com tratamento de erros
    $.ajax({
      url: '<?php echo get_uri("bombeiros/lista_pagamentos"); ?>',
      type: 'GET',
      success: function (html) {
        $("#area-pagamentos").html(html);
      },
      error: function (xhr, status, error) {
        // Se der erro, mostra mensagem vermelha e detalhes no console
        console.error("Erro ao carregar pagamentos:", error);
        console.error("Resposta do servidor:", xhr.responseText);
        $("#area-pagamentos").html('<div class="alert alert-danger text-center p20">Erro ao carregar pagamentos. Tente recarregar a página.<br><small>' + error + '</small></div>');
      }
    });
  }

  function carregarRelatorioFinanceiro() {
    // Coloca o spinner
    $("#area-financeiro-geral").html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Gerando relatório...</div>');

    // Faz a requisição com tratamento de erros
    $.ajax({
      url: '<?php echo get_uri("bombeiros/financeiro_resumo"); ?>',
      type: 'GET',
      success: function (html) {
        $("#area-financeiro-geral").html(html);
      },
      error: function (xhr, status, error) {
        // Se der erro, mostra mensagem vermelha e detalhes no console
        console.error("Erro ao carregar relatório financeiro:", error);
        console.error("Resposta do servidor:", xhr.responseText);
        $("#area-financeiro-geral").html('<div class="alert alert-danger text-center p20">Erro ao carregar relatório financeiro. Tente recarregar a página.<br><small>' + error + '</small></div>');
      }
    });
  }

  function salvarAlteracoes() {
    let promises = [];
    let hasChanges = false;

    $('.linha-alterada').each(function () {
      let row = $(this);

      // Prepara dados apenas com campos que existem e foram alterados
      let data = {
        id: row.data('id'),
        '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
      };

      // Adiciona apenas campos que existem e têm valor
      let nomeAluno = row.find('[name="nome_aluno"]').val();
      if (nomeAluno) data.nome_aluno = nomeAluno;

      let responsavelNome = row.find('[name="responsavel_nome"]').val();
      if (responsavelNome) data.responsavel_nome = responsavelNome;

      let responsavelWhats = row.find('[name="responsavel_whats"]').val();
      if (responsavelWhats) data.responsavel_whats = responsavelWhats;

      let horario = row.find('[name="horario"]').val();
      if (horario) data.horario = horario;

      let querCamisa = row.find('[name="quer_camisa"]').val();
      if (querCamisa !== undefined) data.quer_camisa = querCamisa;

      let tamanhoCamisa = row.find('[name="tamanho_camisa"]').val();
      if (tamanhoCamisa !== undefined) data.tamanho_camisa = tamanhoCamisa;

      let status = row.find('[name="status"]').val();
      if (status) data.status = status;

      hasChanges = true;

      // Log dos dados que serão enviados
      console.log("Enviando dados para salvar:", data);

      promises.push(
        $.ajax({
          url: '<?php echo get_uri("bombeiros/salvar"); ?>',
          type: 'POST',
          data: data,
          dataType: 'json',
          success: function (response) {
            console.log("Resposta do servidor:", response);
            return response;
          },
          error: function (xhr, status, error) {
            console.error("Erro na requisição:", error);
            console.error("Status:", status);
            console.error("Resposta completa:", xhr.responseText);

            // Tenta parsear a resposta como JSON
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
        // Verifica se a resposta é um objeto JSON ou string
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

  // Substituicao do deletar com confirmacao estilizada
  function confirmarExclusao(id, btn) {
    confirmarAcao("Excluir Aluno", "Tem certeza que deseja apagar este aluno permanentemente? Esta ação não pode ser desfeita.", function () {
      let row = $(btn).closest('tr');
      $.post('<?php echo get_uri("bombeiros/deletar"); ?>', {
        id: id, '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
      }, function (res) {
        if (res.success) {
          appAlert.warning("Aluno removido com sucesso.");
          row.fadeOut();
        } else {
          appAlert.error("Erro ao deletar.");
        }
      }, 'json');
    });
  }

  // Função para expandir/colapsar parcelas do aluno (usada na aba de pagamentos)
  function toggleParcelas(alunoId) {
    var detailRow = $("#detail-" + alunoId);
    var icon = $("#icon-" + alunoId);

    if (detailRow.is(":visible")) {
      detailRow.hide();
      icon.removeClass("fa-minus-circle text-danger").addClass("fa-plus-circle text-primary");
    } else {
      detailRow.show();
      icon.removeClass("fa-plus-circle text-primary").addClass("fa-minus-circle text-danger");
    }
  }

  // Função para marcar pagamento como pago (usada na aba de pagamentos)
  function marcarComoPago(idPagamento) {
    confirmarAcao(
      "Confirmar Recebimento",
      "Deseja marcar esta parcela como PAGA?",
      function () {
        $.ajax({
          url: '<?php echo get_uri("bombeiros/baixar_pagamento"); ?>',
          type: 'POST',
          data: {
            id: idPagamento,
            '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
          },
          dataType: 'json',
          success: function (res) {
            if (res.success) {
              appAlert.success(res.message);
              carregarPagamentos(); // Recarrega a lista de pagamentos
            } else {
              appAlert.error(res.message);
            }
          },
          error: function (xhr, status, error) {
            console.error("Erro ao baixar pagamento:", error);
            appAlert.error("Erro ao processar pagamento. Tente novamente.");
          }
        });
      }
    );
  }

  // Função para gerar comprovante de pagamento
  function gerarComprovante(cobrancaId, alunoId) {
    // Abre modal para preencher dados do comprovante
    $('#modal-comprovante').modal('show');
    $('#comprovante-cobranca-id').val(cobrancaId);
    $('#comprovante-aluno-id').val(alunoId);

    // Busca dados da cobrança para pré-preencher
    $.ajax({
      url: '<?php echo get_uri("bombeiros/buscar_dados_comprovante"); ?>',
      type: 'POST',
      data: {
        cobranca_id: cobrancaId,
        aluno_id: alunoId,
        '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
      },
      dataType: 'json',
      success: function (res) {
        if (res.success && res.data) {
          var dados = res.data;
          $('#comprovante-responsavel-nome').val(dados.responsavel_nome || '');
          $('#comprovante-responsavel-cpf').val(dados.responsavel_cpf || '');
          $('#comprovante-aluno-nome').val(dados.aluno_nome || '');
          $('#comprovante-valor').val(dados.valor || '');
          $('#comprovante-mensalidade').val(dados.mensalidade_numero || '1');
          $('#comprovante-data-emissao').val(dados.data_emissao || '<?php echo date('Y-m-d'); ?>');
          $('#comprovante-conferido-por').val(dados.conferido_por || '');
          $('#comprovante-data-conferencia').val(dados.data_conferencia || '<?php echo date('Y-m-d'); ?>');

          // Aplica máscaras
          if (dados.responsavel_cpf) {
            $('#comprovante-responsavel-cpf').mask('000.000.000-00');
          }
          if (dados.valor) {
            $('#comprovante-valor').mask('#.##0,00', { reverse: true });
          }
        }
      },
      error: function () {
        // Se der erro, deixa os campos vazios para preenchimento manual
        $('#comprovante-data-emissao').val('<?php echo date('Y-m-d'); ?>');
        $('#comprovante-data-conferencia').val('<?php echo date('Y-m-d'); ?>');
      }
    });
  }

  // Salvar e gerar comprovante
  function salvarEgerarComprovante() {
    var btn = $('#btn-salvar-comprovante');
    var textoOriginal = btn.html();
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Gerando...');

    var formData = {
      cobranca_id: $('#comprovante-cobranca-id').val(),
      aluno_id: $('#comprovante-aluno-id').val(),
      responsavel_nome: $('#comprovante-responsavel-nome').val(),
      responsavel_cpf: $('#comprovante-responsavel-cpf').val(),
      aluno_nome: $('#comprovante-aluno-nome').val(),
      aluno_nome_adicional: $('#comprovante-aluno-nome-adicional').val(),
      mensalidade_numero: $('#comprovante-mensalidade').val(),
      valor: $('#comprovante-valor').val(),
      forma_pagamento: $('#comprovante-forma-pagamento').val(),
      conferido_por: $('#comprovante-conferido-por').val(),
      data_emissao: $('#comprovante-data-emissao').val(),
      data_conferencia: $('#comprovante-data-conferencia').val(),
      '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
    };

    // Validação básica
    if (!formData.responsavel_nome || !formData.aluno_nome || !formData.valor || !formData.forma_pagamento) {
      appAlert.error('Por favor, preencha todos os campos obrigatórios.');
      btn.prop('disabled', false).html(textoOriginal);
      return;
    }

    $.ajax({
      url: '<?php echo get_uri("bombeiros/gerar_comprovante"); ?>',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          appAlert.success(res.message);
          $('#modal-comprovante').modal('hide');

          // Faz download do comprovante
          if (res.download_url) {
            // Cria link temporário para download
            var link = document.createElement('a');
            link.href = res.download_url;
            link.download = 'Comprovante_SIAMESA_' + res.numero_comprovante + '.html';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            appAlert.success('Comprovante baixado com sucesso!');
          } else if (res.pdf_url) {
            // Fallback: abre em nova aba se não houver download_url
            window.open(res.pdf_url, '_blank');
          }
        } else {
          appAlert.error(res.message || 'Erro ao gerar comprovante.');
          btn.prop('disabled', false).html(textoOriginal);
        }
      },
      error: function (xhr, status, error) {
        console.error("Erro ao gerar comprovante:", error);
        appAlert.error("Erro ao gerar comprovante. Tente novamente.");
        btn.prop('disabled', false).html(textoOriginal);
      }
    });
  }
</script>