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
                  <td></td>
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

          <div class="form-group mb-4" style="padding: 15px; border-radius: 6px; border: 1px dashed #4a90e2;">
            <label style="font-weight: bold; color: #0d6efd; display:block; margin-bottom:5px;">
              <i class="fa fa-magic"></i> Preenchimento Automático com IA
            </label>
            <div class="d-flex align-items-center">
              <input type="file" id="arquivo_ia" name="arquivo_ia" class="form-control" accept="image/*,.pdf" style="margin-right: 10px;">
              <button type="button" id="btn-processar-ia" class="btn btn-primary" onclick="lerFichaIA()">
                <i class="fa fa-bolt"></i> Ler Ficha
              </button>
            </div>
            <small class="text-muted d-block mt-1">Tire uma foto da ficha de matrícula ou suba o PDF preenchido.</small>

            <div id="loading-ia" style="display:none; margin-top: 10px; color: #e67e22; font-weight:bold;">
              <i class="fa fa-spinner fa-spin"></i> A IA está lendo o documento... Aguarde (5-10s)
            </div>
          </div>
          <hr>
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
                <option value="6">6</option>
                <option value="8">8</option>
                <option value="10">10</option>
                <option value="12">12</option>
                <option value="14">14</option>
                <option value="16">16</option>
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

<div class="modal fade" id="modal-comprovante" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Gerar Comprovante de Pagamento</h4>
        <button type="button" id="btn-salvar-comprovante" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
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
  /* ESTILOS GERAIS */
  .rise-siamesa-wrapper {
    --siamesa-text: var(--bs-body-color);
    --siamesa-border: var(--bs-border-color);
    --siamesa-accent-rgb: var(--bs-primary-rgb);
    color: var(--siamesa-text);
  }

  .rise-siamesa-wrapper .panel,
  .rise-siamesa-wrapper .card {
    background-color: transparent !important;
    box-shadow: none !important;
    border-color: var(--siamesa-border);
  }

  /* TABELA TRANSPARENTE */
  .rise-siamesa-wrapper .table {
    --bs-table-bg: transparent;
    --bs-table-color: inherit;
    color: var(--siamesa-text);
  }

  .rise-siamesa-wrapper .table th,
  .rise-siamesa-wrapper .table td {
    color: inherit !important;
  }

  /* INPUTS ESTILO EXCEL NA TABELA */
  .rise-siamesa-wrapper .form-control-excel {
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
  }

  /* SELECTS DARK MODE FRIENDLY */
  select option {
    background-color: #0b1020 !important;
    color: #e5e7eb !important;
  }

  #unidade-status {
    background-color: #0b1020 !important;
    color: #e5e7eb !important;
    border: 1px solid #374151;
  }

  /* UTILITÁRIOS */
  .text-off {
    opacity: 0.6;
  }

  input[type="file"]::file-selector-button {
    background-color: #19223d !important;
    color: #ffffff !important;
    border: 1px solid #f7f7f8;
    border-radius: 4px;
    padding: 6px 12px;
    cursor: pointer;
    transition: background-color 0.2s;
  }

  /* Suporte para navegadores baseados em WebKit (Chrome, Edge, Safari) */
  input[type="file"]::-webkit-file-upload-button {
    background-color: #19223d !important;
    color: #ffffff !important;
    border: 1px solid #ffffff;
    border-radius: 4px;
    padding: 6px 12px;
    cursor: pointer;
  }

  /* Efeito ao passar o mouse (fica levemente mais claro ou escuro) */
  input[type="file"]::file-selector-button:hover,
  input[type="file"]::-webkit-file-upload-button:hover {
    background-color: #232f52 !important;
    /* Tom levemente diferente para feedback visual */
  }

  /* 2. Caso você esteja usando a classe 'custom-file-label' do Bootstrap */
  .custom-file-label::after {
    background-color: #19223d !important;
    color: #ffffff !important;
    content: "Procurar";
    /* Garante que o texto apareça */
  }

  /* 3. Caso seja um botão comum que abre o seletor (classe personalizada) */
  .btn-upload-custom {
    background-color: #19223d !important;
    color: #ffffff !important;
    border-color: #19223d !important;
  }

  select.form-control,
  select.form-select {
    background-color: #19223d !important;
    color: #ffffff !important;
    border: 1px solid #19223d;
    background-image: none;
    /* Remove seta padrão de alguns navegadores para evitar conflito de cor */
  }

  /* Garante que as opções ao abrir a lista também fiquem escuras */
  select.form-control option,
  select.form-select option {
    background-color: #19223d;
    color: #ffffff;
  }

  /* Se quiser estilizar a seta (apenas para navegadores modernos) */
  select.form-control:focus,
  select.form-select:focus {
    background-color: #19223d !important;
    color: #ffffff !important;
    box-shadow: 0 0 0 0.2rem rgba(25, 34, 61, 0.25);
  }
</style>

<script>
  // ==========================================
  // FUNÇÕES DE NAVEGAÇÃO E CRUD
  // ==========================================

  function filtrarPorUnidade() {
    var unidadeId = $('#filtro-unidade').val();
    var url = '<?php echo get_uri("bombeiros"); ?>';
    if (unidadeId && unidadeId != '') {
      url += '?unidade_id=' + unidadeId;
    }
    window.location.href = url;
  }

  function abrirModalUnidade(id) {
    $('#form-unidade')[0].reset();
    $('#unidade-id').val('');
    $('#modal-unidade .modal-title').text('Cadastrar Unidade');

    if (id) {
      $.ajax({
        url: '<?php echo get_uri("bombeiros/buscar_unidade"); ?>',
        type: 'POST',
        data: { id: id, '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>' },
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

  // ==========================================
  // FUNÇÕES DA INTELIGÊNCIA ARTIFICIAL (IA)
  // ==========================================

  function lerFichaIA() {
    var fileInput = document.getElementById('arquivo_ia');

    if (fileInput.files.length === 0) {
      alert("Por favor, selecione uma foto ou PDF da ficha primeiro.");
      return;
    }

    // Mostra loading e bloqueia botão
    $('#loading-ia').slideDown();
    $('#btn-processar-ia').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Lendo...');

    var formData = new FormData();
    formData.append('arquivo_ia', fileInput.files[0]);
    // Token de segurança do CodeIgniter (Obrigatório)
    formData.append('<?php echo csrf_token(); ?>', '<?php echo csrf_hash(); ?>');

    $.ajax({
      url: '<?php echo get_uri("bombeiros/upload_e_ler_ia"); ?>',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        // Restaura botões
        $('#loading-ia').slideUp();
        $('#btn-processar-ia').prop('disabled', false).html('<i class="fa fa-bolt"></i> Ler Ficha');

        // Tenta parsear se vier string (segurança extra)
        if (typeof response === 'string') {
          try { response = JSON.parse(response); } catch (e) { }
        }

        if (response.success) {
          appAlert.success("Dados extraídos com sucesso!");
          preencherCamposIA(response.data);
        } else {
          appAlert.error(response.message || "Erro ao ler o arquivo.");
          console.error(response);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        $('#loading-ia').slideUp();
        $('#btn-processar-ia').prop('disabled', false).html('<i class="fa fa-bolt"></i> Ler Ficha');
        appAlert.error("Erro de conexão com o servidor.");
      }
    });
  }

  function preencherCamposIA(data) {
    // Helper para preencher apenas se o dado existir
    const setVal = (id, valor) => {
      if (valor && valor !== "null") $(id).val(valor).trigger('change');
    };

    // --- DADOS DO RESPONSÁVEL ---
    setVal('#resp_nome', data.responsavel_nome);
    setVal('#resp_nasc', data.responsavel_nascimento);
    setVal('#resp_rg', data.responsavel_rg);
    setVal('#resp_cpf', data.responsavel_cpf);
    setVal('#resp_email', data.responsavel_email);
    setVal('#resp_whats', data.responsavel_whats);
    setVal('#resp_celular', data.responsavel_celular);
    setVal('#resp_recado', data.responsavel_recado);

    // Endereço
    setVal('#resp_cep', data.responsavel_cep);
    setVal('#resp_endereco', data.responsavel_endereco);
    setVal('#resp_numero', data.responsavel_numero);
    setVal('#resp_bairro', data.responsavel_bairro);
    setVal('#resp_cidade', data.responsavel_cidade);
    setVal('#resp_complemento', data.responsavel_complemento);

    // --- DADOS DO ALUNO ---
    setVal('#nome_aluno', data.nome_aluno);
    setVal('#cpf_aluno', data.cpf_aluno);
    setVal('#rg_aluno', data.rg_aluno);
    setVal('#nasc_aluno', data.nascimento_aluno);

    // --- SELECTS ESPECIAIS ---

    // Tamanho da Camisa
    if (data.tamanho_camisa) {
      let tam = data.tamanho_camisa.toString().trim().toUpperCase();
      $('#tamanho_camisa').val(tam);
    }

    // Horário (Turma)
    if (data.horario) {
      let horarioIA = data.horario.toString();
      // Procura no texto das options se contém o que a IA mandou
      $("#horario option").each(function () {
        if ($(this).text().includes(horarioIA) || $(this).val().includes(horarioIA)) {
          $(this).prop("selected", true);
        }
      });
    }

    if (typeof aplicarMascaras === 'function') aplicarMascaras();
  }

  // ==========================================
  // SETUP E EVENT LISTENERS
  // ==========================================

  $(document).ready(function () {
    aplicarMascaras();

    // Carrega as abas quando são clicadas
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
      var target = $(e.target).attr("href");
      var htmlContent = '';

      // Carrega Responsáveis
      if (target === '#tab-responsaveis') {
        htmlContent = $('#area-responsaveis').html();
        if (htmlContent.indexOf('Carregando responsáveis') !== -1) {
          $('#area-responsaveis').load('<?php echo get_uri("bombeiros/lista_responsaveis"); ?>');
        }
      }

      // Carrega Pagamentos
      if (target === '#tab-pagamentos') {
        htmlContent = $('#area-pagamentos').html();
        if (htmlContent.indexOf('Carregando financeiro') !== -1) {
          $('#area-pagamentos').load('<?php echo get_uri("bombeiros/lista_pagamentos"); ?>');
        }
      }

      // Carrega Financeiro Resumo
      if (target === '#tab-financeiro-geral') {
        htmlContent = $('#area-financeiro-geral').html();
        if (htmlContent.indexOf('Gerando relatórios') !== -1) {
          $('#area-financeiro-geral').load('<?php echo get_uri("bombeiros/financeiro_resumo"); ?>');
        }
      }
    });

    // Submit Form Unidade
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
        error: function () {
          appAlert.error("Erro interno.");
          btn.prop('disabled', false).html(textoOriginal);
        }
      });
    });

    // Submit Form Aluno (Novo ou Edição)
    $("#form-siamesa-aluno").submit(function (e) {
      e.preventDefault();
      var btn = $(this).find('button[type="submit"]');
      btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Processando...');

      $.ajax({
        url: '<?php echo get_uri("bombeiros/salvar"); ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function (result) {
          if (result.success) {
            appAlert.success(result.message);
            $("#modal-aluno").modal("hide");
            location.reload();
          } else {
            appAlert.error(result.message);
            btn.prop("disabled", false).html('<i class="fa fa-save"></i> Finalizar Matrícula');
          }
        },
        error: function () {
          appAlert.error("Erro de comunicação.");
          btn.prop("disabled", false).html('<i class="fa fa-save"></i> Finalizar Matrícula');
        }
      });
    });

    // Carregar lista de chamada quando turma é selecionada
    $('#filtro-turma-chamada').on('change', function () {
      var turma = $(this).val();
      var data = $('#data-chamada').val();

      if (!turma) {
        $('#area-chamada').html('<p class="text-off">Selecione a turma para carregar a lista de chamada.</p>');
        return;
      }

      if (!data) {
        appAlert.error("Selecione uma data primeiro.");
        return;
      }

      $('#area-chamada').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Carregando lista de chamada...</p>');

      $.ajax({
        url: '<?php echo get_uri("bombeiros/lista_chamada"); ?>',
        type: 'POST',
        data: {
          data: data,
          turma: turma,
          '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
        },
        success: function (html) {
          $('#area-chamada').html(html);
        },
        error: function () {
          appAlert.error("Erro ao carregar lista de chamada.");
          $('#area-chamada').html('<p class="text-off">Erro ao carregar.</p>');
        }
      });
    });

    // Submit Importação
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
          appAlert.error("Erro interno no servidor.");
          btn.prop('disabled', false).html('Processar Importação');
        }
      });
    });
  });

  function aplicarMascaras() {
    $('.mask-cpf').mask('000.000.000-00', { reverse: true });
    var SPMaskBehavior = function (val) { return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009'; },
      spOptions = { onKeyPress: function (val, e, field, options) { field.mask(SPMaskBehavior.apply({}, arguments), options); } };
    $('.mask-tel, .mask-cel').mask(SPMaskBehavior, spOptions);
    $('.mask-money').mask('#.##0,00', { reverse: true });
    $('.mask-cep').mask('00000-000');
  }

  window.abrirModalNovoAluno = function () {
    $("#form-siamesa-aluno")[0].reset();
    $("#aluno_id").val("");
    $("#modal-titulo").text("Novo Cadastro - SIAMESA");
    $("#modal-aluno").modal("show");
  }

  window.abrirModalImportar = function () {
    $('#modal-importacao-siamesa').modal('show');
  }

  // Função para deletar aluno
  window.confirmarExclusao = function (id, btn) {
    if (confirm("Tem certeza que deseja excluir este aluno?")) {
      $.post('<?php echo get_uri("bombeiros/deletar"); ?>', { id: id, '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>' }, function (res) {
        if (res.success) {
          $(btn).closest('tr').fadeOut();
        } else {
          appAlert.error("Erro ao excluir.");
        }
      }, 'json');
    }
  }

  // Função para salvar chamada
  window.salvarChamada = function () {
    var data = $('#data-chamada').val();
    var turma = $('#filtro-turma-chamada').val();

    if (!data || !turma) {
      appAlert.error("Selecione a data e a turma primeiro.");
      return;
    }

    var presencas = {};
    $('#area-chamada input[type="radio"]:checked').each(function () {
      var name = $(this).attr('name');
      var aluno_id = name.replace('p_', '');
      var status = $(this).val();
      presencas[aluno_id] = status;
    });

    if (Object.keys(presencas).length === 0) {
      appAlert.error("Nenhuma presença selecionada.");
      return;
    }

    $.ajax({
      url: '<?php echo get_uri("bombeiros/salvar_presenca"); ?>',
      type: 'POST',
      data: {
        data_aula: data,
        presencas: presencas,
        '<?php echo csrf_token(); ?>': '<?php echo csrf_hash(); ?>'
      },
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          appAlert.success(res.message);
        } else {
          appAlert.error(res.message || "Erro ao salvar chamada.");
        }
      },
      error: function () {
        appAlert.error("Erro de conexão.");
      }
    });
  }
</script>