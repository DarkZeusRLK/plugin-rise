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

<div class="modal fade" id="modal-aluno" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form id="form-siamesa-aluno" autocomplete="off">
        <input type="hidden" name="<?= csrf_token(); ?>" value="<?= csrf_hash(); ?>" />
        <input type="hidden" name="id" id="aluno_id">

        <div class="modal-header">
          <h4 class="modal-title" id="modal-titulo">Cadastro de Matrícula</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <h5 class="text-primary border-bottom pb-2"><strong><i class="fa fa-user"></i> Dados do Responsável</strong></h5>
          <div class="row">
            <div class="col-md-6 mb-2">
              <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
              <input type="text" name="responsavel_nome" id="resp_nome" class="form-control" required>
            </div>
            <div class="col-md-3 mb-2">
              <label class="form-label">Data Nasc.</label>
              <input type="date" name="responsavel_nascimento" id="resp_nasc" class="form-control">
            </div>
            <div class="col-md-3 mb-2">
              <label class="form-label">CPF <span class="text-danger">*</span></label>
              <input type="text" name="responsavel_cpf" id="resp_cpf" class="form-control mask-cpf" required>
            </div>
            <div class="col-md-3 mb-2">
              <label class="form-label">RG</label>
              <input type="text" name="responsavel_rg" id="resp_rg" class="form-control">
            </div>
            <div class="col-md-5 mb-2">
              <label class="form-label">E-mail</label>
              <input type="email" name="responsavel_email" id="resp_email" class="form-control">
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-2">
              <label class="form-label">WhatsApp <span class="text-danger">*</span></label>
              <input type="text" name="responsavel_whats" id="resp_whats" class="form-control mask-cel" required>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Celular Auxiliar</label>
              <input type="text" name="responsavel_celular" id="resp_celular" class="form-control mask-cel">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Telefone Recado</label>
              <input type="text" name="responsavel_recado" id="resp_recado" class="form-control">
            </div>
          </div>

          <p class="mt-2 mb-1"><strong>Endereço do Responsável:</strong></p>
          <div class="row">
            <div class="col-md-3 mb-2">
              <label class="form-label">CEP</label>
              <input type="text" name="responsavel_cep" id="resp_cep" class="form-control mask-cep">
            </div>
            <div class="col-md-7 mb-2">
              <label class="form-label">Logradouro (Rua/Av)</label>
              <input type="text" name="responsavel_endereco" id="resp_endereco" class="form-control">
            </div>
            <div class="col-md-2 mb-2">
              <label class="form-label">Nº</label>
              <input type="text" name="responsavel_numero" id="resp_numero" class="form-control">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Bairro</label>
              <input type="text" name="responsavel_bairro" id="resp_bairro" class="form-control">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Cidade</label>
              <input type="text" name="responsavel_cidade" id="resp_cidade" class="form-control">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Complemento</label>
              <input type="text" name="responsavel_complemento" id="resp_complemento" class="form-control">
            </div>
          </div>

          <h5 class="text-primary border-bottom pb-2 mt-3"><strong><i class="fa fa-graduation-cap"></i> Dados do Aluno</strong></h5>
          <div class="row">
            <div class="col-md-6 mb-2">
              <label class="form-label">Nome do Aluno <span class="text-danger">*</span></label>
              <input type="text" name="nome_aluno" id="nome_aluno" class="form-control" required>
            </div>
            <div class="col-md-3 mb-2">
              <label class="form-label">CPF Aluno</label>
              <input type="text" name="cpf_aluno" id="cpf_aluno" class="form-control mask-cpf">
            </div>
            <div class="col-md-3 mb-2">
              <label class="form-label">RG Aluno</label>
              <input type="text" name="rg_aluno" id="rg_aluno" class="form-control">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Nascimento <span class="text-danger">*</span></label>
              <input type="date" name="nascimento_aluno" id="nasc_aluno" class="form-control" required>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Camiseta <span class="text-danger">*</span></label>
              <select name="tamanho_camisa" id="tamanho_camisa" class="form-select" required>
                <option value="">Tamanho...</option>
                <option value="PP">PP</option>
                <option value="P">P</option>
                <option value="M">M</option>
                <option value="G">G</option>
                <option value="GG">GG</option>
              </select>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Horário <span class="text-danger">*</span></label>
              <select name="horario" id="horario" class="form-select" required>
                <option value="">Selecione...</option>
                <option value="08:30-11:00">08:30 – 11:00</option>
                <option value="11:30-14:00">11:30 – 14:00</option>
                <option value="14:30-17:00">14:30 – 17:00</option>
              </select>
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-md-4">
              <label class="form-label">Unidade <span class="text-danger">*</span></label>
              <select name="unidade_id" id="unidade_id" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach ($unidades as $u): ?>
                  <option value="<?= $u['id']; ?>"><?= $u['nome_unidade']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Início Aulas</label>
              <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Mensalidade (R$)</label>
              <input type="text" name="valor_mensalidade" id="valor_mensalidade" class="form-control mask-money" value="150,00">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Finalizar Matrícula</button>
        </div>
      </form>
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
<div class="modal fade" id="modal-importacao-siamesa" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Importar Alunos via Excel</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form id="form-importar-excel" enctype="multipart/form-data">
        <div class="modal-body">
          <p>Selecione um arquivo .xlsx ou .csv seguindo o padrão de colunas.</p>
          <div class="form-group">
            <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
          </div>
          <div id="import-msg" class="mt-2"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Processar Importação</button>
        </div>
      </form>
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
    border-color: var(--siamesa-border);
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
  $('#form-importar-excel').on('submit', function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append('<?php echo csrf_token(); ?>', '<?php echo csrf_hash(); ?>');

    var btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Importando...');

    $.ajax({
      url: '<?php echo get_uri("bombeiros/importar_csv"); ?>',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          appAlert.success(res.message);
          location.reload();
        } else {
          appAlert.error(res.message);
          btn.prop('disabled', false).html('Processar Importação');
        }
      },
      error: function () {
        appAlert.error("Erro interno no servidor ao processar arquivo.");
        btn.prop('disabled', false).html('Processar Importação');
      }
    });
  });
  function aplicarMascaras() {
    $('.mask-cpf').mask('000.000.000-00', { reverse: true });
    var SPMaskBehavior = function (val) { return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009'; },
      spOptions = { onKeyPress: function (val, e, field, options) { field.mask(SPMaskBehavior.apply({}, arguments), options); } };
    $('.mask-tel, .mask-cel').mask(SPMaskBehavior, spOptions);
    // Máscara de moeda brasileira
    $('.mask-money').mask('#.##0,00', { reverse: true });
  }

  window.abrirModalNovoAluno = function () {
    $("#form-siamesa-aluno")[0].reset();
    $("#aluno_id").val("");
    $("#modal-titulo").text("Novo Cadastro - SIAMESA");
    $("#div_status").hide();
    $("#modal-aluno").modal("show");
    setTimeout(aplicarMascaras, 500);
  };

  window.abrirModalImportar = function () { $("#modal-importacao-siamesa").modal("show"); };

  // Função para carregar dados no modal para EDIÇÃO
  window.editarAluno = function (aluno) {
    $("#form-siamesa-aluno")[0].reset();
    $("#aluno_id").val(aluno.id);
    $("#modal-titulo").text("Editar Aluno: " + aluno.nome_aluno);

    // Preenchimento dos campos
    $("#resp_nome").val(aluno.responsavel_nome);
    $("#resp_cpf").val(aluno.responsavel_cpf);
    $("#resp_whats").val(aluno.responsavel_whats);
    $("#resp_email").val(aluno.responsavel_email);

    $("#nome_aluno").val(aluno.nome_aluno);
    $("#nasc_aluno").val(aluno.nascimento_aluno);
    $("#tamanho_camisa").val(aluno.tamanho_camisa);

    $("#unidade_id").val(aluno.unidade_id);
    $("#horario").val(aluno.turma);
    $("#data_inicio").val(aluno.data_inicio);

    // Formata valor para a máscara
    let valor = parseFloat(aluno.valor_mensalidade).toFixed(2).replace('.', ',');
    $("#valor_mensalidade").val(valor);

    // Exibe status apenas na edição
    $("#div_status").show();
    $("#status_aluno").val(aluno.status);

    $("#modal-aluno").modal("show");
    setTimeout(aplicarMascaras, 500);
  };

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

    // REMOVIDO: Lógica do checkbox 'comprar_camiseta_check' (Camiseta agora é obrigatória)

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

    // Formulário de salvar/editar aluno
    $("#form-siamesa-aluno").on("submit", function (e) {
      e.preventDefault();

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
            appAlert.success(res.message || "Dados salvos com sucesso!");
            location.reload();
          } else {
            appAlert.error(res.message || "Erro ao salvar.");
            btn.prop('disabled', false).html(textoOriginal);
          }
        },
        error: function (xhr, status, error) {
          console.error("Resposta do servidor:", xhr.responseText);
          appAlert.error("Erro no servidor. Verifique o console.");
          btn.prop('disabled', false).html(textoOriginal);
        }
      });
    });

    // --- Lógica de Tema (Mantida) ---
    function limparEstilosInline() {
      var wrapper = $('.rise-siamesa-wrapper');
      if (wrapper.length) {
        wrapper.find('[style*="background-color"], [style*="color"]').each(function () {
          var $el = $(this);
          var style = $el.attr('style') || '';
          style = style.replace(/background-color\s*:\s*[^;]+;?/gi, '').replace(/color\s*:\s*[^;]+;?/gi, '');
          style.trim() ? $el.attr('style', style.trim()) : $el.removeAttr('style');
        });
      }
    }
    $(document).on("themeChange theme-changed risethemechange", function () { setTimeout(limparEstilosInline, 100); });
    limparEstilosInline();
  });

  // --- Funções de Carga de Dados (Mantidas) ---
  function carregarListaChamada() { /* ... */ }
  function salvarChamada() { /* ... */ }
  function carregarResponsaveis() { /* ... AJAX lista_responsaveis ... */ }
  function carregarPagamentos() { /* ... AJAX lista_pagamentos ... */ }
  function carregarRelatorioFinanceiro() { /* ... AJAX financeiro_resumo ... */ }

  function salvarAlteracoes() {
    // Esta função lida com a edição rápida na tabela (estilo Excel)
    let promises = [];
    let hasChanges = false;

    $('.linha-alterada').each(function () {
      let row = $(this);
      let data = {
        id: row.data('id'),
        nome_aluno: row.find('[name="nome_aluno"]').val(),
        responsavel_nome: row.find('[name="responsavel_nome"]').val(),
        responsavel_whats: row.find('[name="responsavel_whats"]').val(),
        horario: row.find('[name="horario"]').val(),
        tamanho_camisa: row.find('[name="tamanho_camisa"]').val(),
        status: row.find('[name="status"]').val(),
        '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
      };

      hasChanges = true;
      promises.push($.ajax({ url: '<?php echo get_uri("bombeiros/salvar"); ?>', type: 'POST', data: data, dataType: 'json' }));
    });

    if (!hasChanges) return appAlert.info("Nenhuma alteração.");

    Promise.all(promises).then(function () {
      appAlert.success("Atualizado!");
      location.reload();
    }).catch(function (err) { appAlert.error("Erro ao salvar lote."); });
  }

  function confirmarExclusao(id, btn) {
    confirmarAcao("Excluir Aluno", "Deseja apagar permanentemente?", function () {
      $.post('<?php echo get_uri("bombeiros/deletar"); ?>', { id: id, '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>' },
        function (res) { res.success ? $(btn).closest('tr').fadeOut() : appAlert.error("Erro."); }, 'json');
    });
  }

  // Funções de Pagamento e Comprovante (Mantidas conforme seu original)
  function toggleParcelas(alunoId) { /* ... */ }
  function marcarComoPago(idPagamento) { /* ... */ }
  function gerarComprovante(cobrancaId, alunoId) { /* ... */ }
  function salvarEgerarComprovante() { /* ... */ }
</script>