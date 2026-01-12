<?php

namespace Bombeiros\Controllers;

use App\Controllers\Security_Controller;

class Bombeiros extends Security_Controller
{
  /**
   * Helper para renderização de views
   */
  private function render_view($path, $data = [])
  {
    $path = str_replace('\\', '/', $path);
    return view($path, $data);
  }

  /**
   * Helper para obter erro do MySQL diretamente
   */
  private function getMysqlError($db)
  {
    try {
      // Tenta acessar via reflexão primeiro
      $reflection = new \ReflectionClass($db);
      if ($reflection->hasProperty('connID')) {
        $prop = $reflection->getProperty('connID');
        $prop->setAccessible(true);
        $connID = $prop->getValue($db);
        if ($connID && is_object($connID)) {
          if (isset($connID->error) && !empty($connID->error)) {
            return $connID->error . ' (Código: ' . ($connID->errno ?? 'N/A') . ')';
          }
        }
      }

      // Tenta método getError() se existir
      if (method_exists($db, 'getError')) {
        $error = $db->getError();
        if (!empty($error)) {
          return is_array($error) ? ($error['message'] ?? json_encode($error)) : $error;
        }
      }

      // Tenta error() do CodeIgniter
      $error = $db->error();
      if (!empty($error['message'])) {
        return $error['message'];
      }
      if (!empty($error['code'])) {
        return 'Erro SQL código ' . $error['code'];
      }
    } catch (\Exception $e) {
      // Se não conseguir acessar, tenta error() do CodeIgniter como fallback
      $error = $db->error();
      if (!empty($error['message'])) {
        return $error['message'];
      }
    }
    return 'N/A';
  }

  /**
   * Tela principal: Listagem de Matrículas
   */
  public function index()
  {
    $db = db_connect();

    // Busca unidades para o filtro
    $view_data['unidades'] = $db->table('siamesa_unidades')
      ->where(['status' => 'Ativo', 'deleted' => 0])
      ->orderBy('nome_unidade', 'ASC')
      ->get()->getResultArray();

    // Filtro de unidade (se fornecido)
    $unidade_id = $this->request->getGet('unidade_id');

    $query = $db->table('siamesa_alunos a')
      ->select('a.*, r.nome as responsavel_nome, r.whats as responsavel_whats, u.nome_unidade, u.cidade')
      ->join('siamesa_responsaveis r', 'r.id = a.responsavel_id', 'left')
      ->join('siamesa_unidades u', 'u.id = a.unidade_id', 'left')
      ->where('a.deleted', 0);

    // Aplica filtro de unidade se fornecido
    if ($unidade_id && $unidade_id != '') {
      $query->where('a.unidade_id', $unidade_id);
    }

    $view_data['alunos'] = $query->get()->getResultArray();
    $view_data['unidade_selecionada'] = $unidade_id;

    $view_data['total_ativos'] = $db->table('siamesa_alunos')
      ->where(['status' => 'Ativo', 'deleted' => 0])
      ->countAllResults();

    return $this->template->render("Bombeiros\Views\index", $view_data);
  }

  /**
   * Salva ou Atualiza Aluno e Responsável
   */
  /**
   * Salva ou Atualiza Aluno e Responsável
   */
  public function salvar()
  {
    $db = db_connect();

    $converterData = function ($data) {
      if (empty($data))
        return null;
      if (strpos($data, '/') !== false) {
        $partes = explode('/', $data);
        if (count($partes) == 3)
          return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
      }
      return $data;
    };

    try {
      $db->transStart();

      $id = $this->request->getPost('id');
      $num_parcelas = $this->request->getPost('num_parcelas') ?: 6;

      // Tratamento Valor Mensalidade
      $valor_post = $this->request->getPost('valor_mensalidade');
      $valor_mensalidade = 150.00;
      if ($valor_post) {
        $valor_str = trim((string) $valor_post);
        $valor_limpo = str_replace(['.', ','], ['', '.'], $valor_str);
        $valor_mensalidade = floatval($valor_limpo);
      }

      $nasc_aluno = $converterData($this->request->getPost('nascimento_aluno'));
      $data_inicio = $converterData($this->request->getPost('data_inicio')) ?: date('Y-m-d');
      $tamanho_camisa = $this->request->getPost('tamanho_camisa');

      // --- PREPARAÇÃO DADOS DO RESPONSÁVEL (WHATSAPP É CHAVE FORTE) ---
      $whats_limpo = preg_replace('/\D/', '', $this->request->getPost('responsavel_whats'));

      $dados_resp = [
        'nome' => trim($this->request->getPost('responsavel_nome')),
        'nascimento' => $converterData($this->request->getPost('responsavel_nascimento')),
        'rg' => trim($this->request->getPost('responsavel_rg')),
        'cpf' => preg_replace('/\D/', '', $this->request->getPost('responsavel_cpf')),
        'endereco' => trim($this->request->getPost('responsavel_endereco')),
        'numero' => trim($this->request->getPost('responsavel_numero')),
        'complemento' => trim($this->request->getPost('responsavel_complemento')),
        'bairro' => trim($this->request->getPost('responsavel_bairro')),
        'cep' => preg_replace('/\D/', '', $this->request->getPost('responsavel_cep')),
        'cidade' => trim($this->request->getPost('responsavel_cidade')),
        'whats' => $whats_limpo,
        'celular' => preg_replace('/\D/', '', $this->request->getPost('responsavel_celular')),
        'recado' => trim($this->request->getPost('responsavel_recado')),
        'email' => trim($this->request->getPost('responsavel_email')) ?: null,
        'deleted' => 0
      ];

      // LOGICA DE "LOCALIZAR OU CRIAR" RESPONSÁVEL
      $res_existente = $db->table('siamesa_responsaveis')->where('whats', $whats_limpo)->get()->getRow();

      if ($res_existente) {
        // Se já existe esse WhatsApp, atualizamos os dados cadastrais
        $responsavel_id = $res_existente->id;
        $db->table('siamesa_responsaveis')->where('id', $responsavel_id)->update($dados_resp);
      } else {
        // Se é novo, insere
        $db->table('siamesa_responsaveis')->insert($dados_resp);
        $responsavel_id = $db->insertID();
      }

      // --- PREPARAÇÃO DADOS DO ALUNO ---
      $dados_aluno = [
        'responsavel_id' => $responsavel_id,
        'nome_aluno' => trim($this->request->getPost('nome_aluno')),
        'rg_aluno' => trim($this->request->getPost('rg_aluno')),
        'cpf_aluno' => preg_replace('/\D/', '', $this->request->getPost('cpf_aluno')),
        'nascimento_aluno' => $nasc_aluno,
        'turma' => $this->request->getPost('horario'),
        'valor_mensalidade' => $valor_mensalidade,
        'data_inicio' => $data_inicio,
        'tamanho_camisa' => $tamanho_camisa,
        'status' => $this->request->getPost('status') ?: 'Ativo'
      ];

      if ($id) {
        // Atualização de Aluno
        $db->table('siamesa_alunos')->where('id', $id)->update($dados_aluno);
        $mensagem = "Dados atualizados com sucesso!";
      } else {
        // Novo Aluno
        $dados_aluno['unidade_id'] = $this->request->getPost('unidade_id');
        $dados_aluno['data_matricula'] = date('Y-m-d');
        $dados_aluno['deleted'] = 0;

        $db->table('siamesa_alunos')->insert($dados_aluno);
        $aluno_id = $db->insertID();

        // Gera Parcelas
        for ($i = 0; $i < $num_parcelas; $i++) {
          $venc = date('Y-m-d', strtotime($data_inicio . " +$i month"));
          $db->table('siamesa_cobrancas')->insert([
            'aluno_id' => $aluno_id,
            'vencimento' => $venc,
            'valor' => $valor_mensalidade,
            'competencia' => date('m/Y', strtotime($venc)),
            'status' => 'Pendente',
            'tipo' => 'Mensalidade'
          ]);
        }

        // Gera Cobrança Camisa
        $db->table('siamesa_cobrancas')->insert([
          'aluno_id' => $aluno_id,
          'vencimento' => date('Y-m-d'),
          'valor' => 67.00,
          'competencia' => date('m/Y'),
          'status' => 'Pendente',
          'tipo' => 'Camiseta'
        ]);

        $mensagem = "Matrícula realizada com sucesso!";
      }

      $db->transComplete();

      if ($db->transStatus() === false) {
        return $this->response->setJSON(["success" => false, "message" => "Erro na transação bancária."]);
      }

      return $this->response->setJSON(["success" => true, "message" => $mensagem]);

    } catch (\Exception $e) {
      return $this->response->setJSON(["success" => false, "message" => "Erro: " . $e->getMessage()]);
    }
  }

  /**
   * Gera HTML da lista de chamada por turma
   */
  public function lista_chamada()
  {
    $db = db_connect();
    $data = $this->request->getPost('data');
    $turma = $this->request->getPost('turma');

    $alunos = $db->table('siamesa_alunos')
      ->where(['turma' => $turma, 'status' => 'Ativo', 'deleted' => 0])
      ->orderBy('nome_aluno', 'ASC')
      ->get()->getResultArray();

    $presencas = $db->table('siamesa_presenca')->where('data_aula', $data)->get()->getResultArray();
    $historico = [];
    foreach ($presencas as $p) {
      $historico[$p['aluno_id']] = $p['status'];
    }

    $html = '<table class="table table-bordered"><thead><tr><th>Aluno</th><th class="text-center">Presença</th></tr></thead><tbody>';

    if (empty($alunos)) {
      $html .= "<tr><td colspan='2' class='text-center'>Nenhum aluno ativo nesta turma.</td></tr>";
    } else {
      foreach ($alunos as $aluno) {
        $p_check = (isset($historico[$aluno['id']]) && $historico[$aluno['id']] == 1) ? 'checked' : '';
        $f_check = (isset($historico[$aluno['id']]) && $historico[$aluno['id']] == 0) ? 'checked' : '';

        if (!isset($historico[$aluno['id']])) {
          $f_check = 'checked';
        }

        $html .= "<tr>
                            <td>{$aluno['nome_aluno']}</td>
                            <td class='text-center'>
                                <label class='mr15' style='cursor:pointer'><input type='radio' name='p_{$aluno['id']}' value='1' $p_check> P</label>
                                <label style='cursor:pointer'><input type='radio' name='p_{$aluno['id']}' value='0' $f_check> F</label>
                            </td>
                        </tr>";
      }
    }
    $html .= '</tbody></table>';
    $html .= '<div class="text-right mt10"><button class="btn btn-primary" onclick="salvarChamada()"><i class="fa fa-save"></i> Salvar Chamada</button></div>';

    return $html;
  }

  /**
   * Salva a presença com lógica de Sobrescrever (Upsert)
   */
  public function salvar_presenca()
  {
    $db = db_connect();
    $data_aula = $this->request->getPost('data_aula');
    $presencas = $this->request->getPost('presencas');

    if (!$data_aula || empty($presencas)) {
      return $this->response->setJSON(["success" => false, "message" => "Nenhum dado recebido."]);
    }

    try {
      $db->transStart();
      foreach ($presencas as $aluno_id => $status) {
        $where = ['aluno_id' => $aluno_id, 'data_aula' => $data_aula];
        $registro = $db->table('siamesa_presenca')->where($where)->get()->getRow();

        if ($registro) {
          $db->table('siamesa_presenca')
            ->where('id', $registro->id)
            ->update(['status' => (int) $status]);
        } else {
          $db->table('siamesa_presenca')->insert([
            'aluno_id' => $aluno_id,
            'data_aula' => $data_aula,
            'status' => (int) $status
          ]);
        }
      }
      $db->transComplete();

      // VERIFICAÇÃO REAL: Se a transação falhou (ex: erro de SQL), retorna erro
      if ($db->transStatus() === false) {
        return $this->response->setJSON(["success" => false, "message" => "Erro ao gravar no banco de dados."]);
      }

      return $this->response->setJSON(["success" => true, "message" => "Chamada salva com sucesso!"]);
    } catch (\Exception $e) {
      return $this->response->setJSON(["success" => false, "message" => $e->getMessage()]);
    }
  }

  /**
   * Importação via CSV com suporte a Camisetas
   */
  public function importar_csv()
  {
    $file = $this->request->getFile('file');
    if (!$file || !$file->isValid()) {
      return $this->response->setJSON(["success" => false, "message" => "Arquivo inválido."]);
    }

    $db = db_connect();
    $filePath = $file->getTempName();

    // Lida com encoding para evitar caracteres estranhos
    $content = file_get_contents($filePath);
    if (!mb_check_encoding($content, 'UTF-8')) {
      $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
      file_put_contents($filePath, $content);
    }

    $handle = fopen($filePath, "r");
    fgetcsv($handle, 2000, ";"); // Pula o cabeçalho do seu modelo

    $importados = 0;
    $unidade_padrao = 1;

    // Helpers de limpeza
    $converteData = function ($data) {
      if (empty(trim($data)))
        return null;
      $parts = explode('/', trim($data));
      return (count($parts) == 3) ? "{$parts[2]}-{$parts[1]}-{$parts[0]}" : null;
    };
    $limpaDoc = function ($val) {
      return preg_replace('/\D/', '', $val);
    };
    $limpaMoeda = function ($val) {
      return (float) str_replace(',', '.', str_replace(['R$', '.', ' '], '', $val));
    };

    try {
      while (($row = fgetcsv($handle, 2000, ";")) !== FALSE) {
        if (count($row) < 14)
          continue; // Pula linhas incompletas

        $db->transStart();

        // 1. RESPONSÁVEL (Colunas 0 a 12)
        $cpf_resp = $limpaDoc($row[3]);
        $resp_id = null;

        if (!empty($cpf_resp)) {
          $existente = $db->table('siamesa_responsaveis')->where('cpf', $cpf_resp)->get()->getRow();
          if ($existente)
            $resp_id = $existente->id;
        }

        $dados_resp = [
          'nome' => mb_convert_case(trim($row[0]), MB_CASE_TITLE, "UTF-8"),
          'nascimento' => $converteData($row[1]),
          'rg' => trim($row[2]),
          'cpf' => $cpf_resp,
          'endereco' => trim($row[4]),
          'numero' => trim($row[5]),
          'complemento' => trim($row[6]),
          'bairro' => trim($row[7]),
          'cep' => $limpaDoc($row[8]),
          'cidade' => trim($row[9]),
          'whats' => $limpaDoc($row[10]),
          'celular' => $limpaDoc($row[11]),
          'email' => strtolower(trim($row[12])),
          'status' => 'Ativo'
        ];

        if ($resp_id) {
          $db->table('siamesa_responsaveis')->where('id', $resp_id)->update($dados_resp);
        } else {
          $db->table('siamesa_responsaveis')->insert($dados_resp);
          $resp_id = $db->insertID();
        }

        // 2. ALUNO (Colunas 13 a 25)
        $dados_aluno = [
          'unidade_id' => $unidade_padrao,
          'responsavel_id' => $resp_id,
          'nome_aluno' => mb_convert_case(trim($row[13]), MB_CASE_TITLE, "UTF-8"),
          'nascimento_aluno' => $converteData($row[14]),
          'rg_aluno' => trim($row[15] ?? ''),
          'cpf_aluno' => $limpaDoc($row[16] ?? ''),
          'turma' => trim($row[18]), // Coluna "Horario"
          'valor_mensalidade' => $limpaMoeda($row[19]),
          'data_matricula' => date('Y-m-d'),
          'data_inicio' => $converteData($row[24]),
          'tamanho_camisa' => trim($row[25]),
          'status' => 'Ativo',
          'deleted' => 0
        ];

        $db->table('siamesa_alunos')->insert($dados_aluno);
        $db->transComplete();

        if ($db->transStatus())
          $importados++;
      }
      fclose($handle);
      return $this->response->setJSON(["success" => true, "message" => "$importados registros processados!"]);
    } catch (\Exception $e) {
      return $this->response->setJSON(["success" => false, "message" => "Erro: " . $e->getMessage()]);
    }
  }

  public function lista_pagamentos()
  {
    try {
      $db = db_connect();
      // Usa Query Builder para que o CodeIgniter adicione o prefixo 'rise_' automaticamente
      $cobrancas = $db->table('siamesa_cobrancas c')
        ->select('c.*, a.nome_aluno')
        ->join('siamesa_alunos a', 'a.id = c.aluno_id')
        ->where('a.deleted', 0)
        ->orderBy('a.nome_aluno', 'ASC')
        ->orderBy('c.vencimento', 'ASC')
        ->get()->getResultArray();

      // Agrupamos os pagamentos por ID do aluno para criar o efeito de dropdown
      $alunos_com_pagamentos = [];
      foreach ($cobrancas as $c) {
        $alunos_com_pagamentos[$c['aluno_id']]['nome_aluno'] = $c['nome_aluno'];
        $alunos_com_pagamentos[$c['aluno_id']]['parcelas'][] = [
          'id' => $c['id'],
          'vencimento' => $c['vencimento'],
          'valor' => $c['valor'],
          'status' => $c['status'],
          'tipo' => $c['tipo'] ?? 'Mensalidade',
          'competencia' => $c['competencia'] ?? ''
        ];
      }

      $view_data['alunos_com_pagamentos'] = $alunos_com_pagamentos;

      // Retorna apenas o conteúdo HTML (sem o template completo) para evitar duplicar a sidebar
      return view("Bombeiros\Views\/lista_pagamentos", $view_data);
    } catch (\Exception $e) {
      return "<div class='alert alert-danger'>Erro ao carregar pagamentos: " . $e->getMessage() . "</div>";
    }
  }

  public function baixar_pagamento()
  {
    $db = db_connect();
    $id = $this->request->getPost('id');
    if ($id) {
      $data_pagamento = date('Y-m-d H:i:s');
      $db->table('siamesa_cobrancas')->where('id', $id)->update([
        'status' => 'Pago',
        'data_pagamento' => $data_pagamento
      ]);
      return $this->response->setJSON(["success" => true, "message" => "Pagamento baixado com sucesso!"]);
    } else {
      return $this->response->setJSON(["success" => false, "message" => "Erro: ID não encontrado."]);
    }
  }

  public function financeiro_resumo()
  {
    try {
      $db = db_connect();
      $hoje = date('Y-m-d');

      $data = [];

      // Calcula TOTAL de todas as parcelas PAGAS (sem filtrar por mês)
      $data['total_pago'] = $db->table('siamesa_cobrancas')
        ->where('status', 'Pago')
        ->selectSum('valor')
        ->get()
        ->getRow()->valor ?? 0;

      // Calcula TOTAL de todas as parcelas PENDENTES (sem filtrar por mês)
      $data['total_pendente'] = $db->table('siamesa_cobrancas')
        ->where('status', 'Pendente')
        ->selectSum('valor')
        ->get()
        ->getRow()->valor ?? 0;

      // Busca TODAS as parcelas inadimplentes (vencidas e pendentes)
      // Cada parcela vencida aparece como uma linha separada
      $data['inadimplentes'] = $db->table('siamesa_cobrancas c')
        ->select('c.*, a.nome_aluno, r.nome as resp_nome, r.whats')
        ->join('siamesa_alunos a', 'a.id = c.aluno_id')
        ->join('siamesa_responsaveis r', 'r.id = a.responsavel_id')
        ->where('c.vencimento <', $hoje)
        ->where('c.status', 'Pendente')
        ->orderBy('c.vencimento', 'ASC')
        ->orderBy('a.nome_aluno', 'ASC')
        ->get()
        ->getResultArray();

      // Calcula o total de inadimplência (soma de todas as parcelas vencidas)
      $data['total_inadimplencia'] = 0;
      foreach ($data['inadimplentes'] as $inad) {
        $data['total_inadimplencia'] += floatval($inad['valor']);
      }

      // Retorna apenas o conteúdo HTML (sem o template completo) para evitar duplicar a sidebar
      return view("Bombeiros\Views\/financeiro_resumo", $data);
    } catch (\Exception $e) {
      return "<div class='alert alert-danger'>Erro ao carregar resumo financeiro: " . $e->getMessage() . "</div>";
    }
  }

  public function deletar()
  {
    $db = db_connect();
    $id = $this->request->getPost('id');
    $db->table('siamesa_alunos')->where('id', $id)->update(['deleted' => 1]);
    return $this->response->setJSON(["success" => true]);
  }

  /**
   * Lista de Responsáveis
   */
  public function lista_responsaveis()
  {
    try {
      $db = db_connect();

      // Busca todos os responsáveis - o campo deleted existe na tabela conforme estrutura mostrada
      $view_data['responsaveis'] = $db->table('siamesa_responsaveis')
        ->where('deleted', 0)
        ->orderBy('nome', 'ASC')
        ->get()->getResultArray();

      // Retorna apenas o conteúdo HTML (sem o template completo) para evitar duplicar a sidebar
      return view("Bombeiros\Views\lista_responsaveis", $view_data);
    } catch (\Exception $e) {
      return "<div class='alert alert-danger'>Erro ao carregar responsáveis: " . $e->getMessage() . "</div>";
    }
  }

  /**
   * Salva ou Atualiza Responsável
   */
  public function salvar_responsavel()
  {
    $db = db_connect();
    try {
      $id = $this->request->getPost('id');

      // LIMPEZA OBRIGATÓRIA: Remove tudo que não é número
      $whats = preg_replace('/\D/', '', $this->request->getPost('whats'));
      $cpf = preg_replace('/\D/', '', $this->request->getPost('cpf'));
      $cel = preg_replace('/\D/', '', $this->request->getPost('celular'));

      if (empty($whats)) {
        return $this->response->setJSON(["success" => false, "message" => "O WhatsApp é obrigatório."]);
      }

      $dados = [
        'nome' => trim($this->request->getPost('nome')),
        'cpf' => $cpf,
        'whats' => $whats,
        'celular' => $cel,
        'email' => trim($this->request->getPost('email')),
        'endereco' => trim($this->request->getPost('endereco'))
      ];

      // Usa Query Builder para garantir que o prefixo da tabela seja aplicado corretamente
      $db->table('siamesa_responsaveis')->where('id', $id)->update($dados);

      return $this->response->setJSON(["success" => true]);
    } catch (\Exception $e) {
      // Log para você ver o erro no servidor
      log_message('error', 'Erro ao salvar responsável: ' . $e->getMessage());
      return $this->response->setJSON(["success" => false, "message" => "Erro no banco: " . $e->getMessage()]);
    }
  }

  /**
   * Deleta Responsável (soft delete)
   */
  public function deletar_responsavel()
  {
    $db = db_connect();
    $id = $this->request->getPost('id');

    // Verifica se o responsável tem alunos vinculados
    $tem_alunos = $db->table('siamesa_alunos')
      ->where(['responsavel_id' => $id, 'deleted' => 0])
      ->countAllResults();

    if ($tem_alunos > 0) {
      return $this->response->setJSON([
        "success" => false,
        "message" => "Não é possível excluir este responsável pois existem alunos vinculados a ele."
      ]);
    }

    // Marca como deletado (soft delete)
    $db->table('siamesa_responsaveis')->where('id', $id)->update(['deleted' => 1]);

    return $this->response->setJSON(["success" => true, "message" => "Responsável removido com sucesso!"]);
  }

  /**
   * Busca dados para preencher o formulário de comprovante
   */
  public function buscar_dados_comprovante()
  {
    try {
      $db = db_connect();
      $cobranca_id = $this->request->getPost('cobranca_id');
      $aluno_id = $this->request->getPost('aluno_id');

      if (!$cobranca_id || !$aluno_id) {
        return $this->response->setJSON([
          "success" => false,
          "message" => "Dados incompletos."
        ]);
      }

      // Busca dados da cobrança, aluno e responsável
      $cobranca = $db->table('siamesa_cobrancas c')
        ->select('c.*, a.nome_aluno, a.responsavel_id, r.nome as responsavel_nome, r.cpf as responsavel_cpf')
        ->join('siamesa_alunos a', 'a.id = c.aluno_id')
        ->join('siamesa_responsaveis r', 'r.id = a.responsavel_id')
        ->where('c.id', $cobranca_id)
        ->where('a.id', $aluno_id)
        ->get()->getRowArray();

      if (!$cobranca) {
        return $this->response->setJSON([
          "success" => false,
          "message" => "Cobrança não encontrada."
        ]);
      }

      // Formata CPF
      $cpf_formatado = $cobranca['responsavel_cpf'];
      if (strlen($cpf_formatado) == 11) {
        $cpf_formatado = substr($cpf_formatado, 0, 3) . '.' . substr($cpf_formatado, 3, 3) . '.' .
          substr($cpf_formatado, 6, 3) . '-' . substr($cpf_formatado, 9, 2);
      }

      // Determina o número da mensalidade baseado na competência ou posição
      $mensalidade_num = 1;
      if (!empty($cobranca['competencia'])) {
        // Tenta extrair o número da competência ou calcular baseado na data
        preg_match('/^(\d+)\//', $cobranca['competencia'], $matches);
        if (!empty($matches[1])) {
          $mensalidade_num = (int) $matches[1];
        }
      }

      return $this->response->setJSON([
        "success" => true,
        "data" => [
          "responsavel_nome" => $cobranca['responsavel_nome'],
          "responsavel_cpf" => $cpf_formatado,
          "aluno_nome" => $cobranca['nome_aluno'],
          "valor" => number_format($cobranca['valor'], 2, ',', '.'),
          "mensalidade_numero" => $mensalidade_num,
          "data_emissao" => date('Y-m-d'),
          "conferido_por" => "",
          "data_conferencia" => date('Y-m-d')
        ]
      ]);

    } catch (\Exception $e) {
      log_message('error', 'Erro ao buscar dados do comprovante: ' . $e->getMessage());
      return $this->response->setJSON([
        "success" => false,
        "message" => "Erro ao buscar dados: " . $e->getMessage()
      ]);
    }
  }

  /**
   * Gera o comprovante de pagamento
   */
  public function gerar_comprovante()
  {
    try {
      $db = db_connect();

      // Recebe dados do formulário
      $cobranca_id = $this->request->getPost('cobranca_id');
      $aluno_id = $this->request->getPost('aluno_id');
      $responsavel_nome = trim($this->request->getPost('responsavel_nome'));
      $responsavel_cpf = preg_replace('/\D/', '', $this->request->getPost('responsavel_cpf'));
      $aluno_nome = trim($this->request->getPost('aluno_nome'));
      $aluno_nome_adicional = trim($this->request->getPost('aluno_nome_adicional')) ?: null;
      $mensalidade_numero = (int) $this->request->getPost('mensalidade_numero');
      $valor_str = $this->request->getPost('valor');
      $forma_pagamento = $this->request->getPost('forma_pagamento');
      $conferido_por = trim($this->request->getPost('conferido_por')) ?: null;
      $data_emissao = $this->request->getPost('data_emissao');
      $data_conferencia = $this->request->getPost('data_conferencia') ?: null;

      // Validações
      if (!$cobranca_id || !$aluno_id || !$responsavel_nome || !$aluno_nome || !$valor_str || !$forma_pagamento) {
        return $this->response->setJSON([
          "success" => false,
          "message" => "Por favor, preencha todos os campos obrigatórios."
        ]);
      }

      // Converte valor de string (ex: "150,00") para float
      $valor = str_replace('.', '', $valor_str);
      $valor = str_replace(',', '.', $valor);
      $valor = floatval($valor);

      // Busca dados do responsável e aluno para validar
      $aluno = $db->table('siamesa_alunos')
        ->where('id', $aluno_id)
        ->get()->getRowArray();

      if (!$aluno) {
        return $this->response->setJSON([
          "success" => false,
          "message" => "Aluno não encontrado."
        ]);
      }

      $responsavel_id = $aluno['responsavel_id'];

      // Gera número do comprovante (formato: COMP-YYYYMMDD-XXXX)
      $numero_comprovante = 'COMP-' . date('Ymd') . '-' . str_pad($cobranca_id, 4, '0', STR_PAD_LEFT);

      // Formata CPF
      $cpf_formatado = $responsavel_cpf;
      if (strlen($cpf_formatado) == 11) {
        $cpf_formatado = substr($cpf_formatado, 0, 3) . '.' . substr($cpf_formatado, 3, 3) . '.' .
          substr($cpf_formatado, 6, 3) . '-' . substr($cpf_formatado, 9, 2);
      }

      // Prepara dados para inserção
      $db->transStart();

      $dados_comprovante = [
        'numero_comprovante' => $numero_comprovante,
        'data_emissao' => $data_emissao ?: date('Y-m-d'),
        'responsavel_id' => $responsavel_id,
        'responsavel_nome' => $responsavel_nome,
        'responsavel_cpf' => $cpf_formatado,
        'aluno_id' => $aluno_id,
        'aluno_nome' => $aluno_nome,
        'aluno_nome_adicional' => $aluno_nome_adicional,
        'mensalidade_numero' => $mensalidade_numero,
        'valor' => $valor,
        'forma_pagamento' => $forma_pagamento,
        'conferido_por' => $conferido_por,
        'data_conferencia' => $data_conferencia,
        'cobranca_id' => $cobranca_id,
        'deleted' => 0
      ];

      $db->table('siamesa_comprovantes')->insert($dados_comprovante);
      $comprovante_id = $db->insertID();

      if (!$comprovante_id) {
        $db->transRollback();
        return $this->response->setJSON([
          "success" => false,
          "message" => "Erro ao salvar comprovante no banco de dados."
        ]);
      }

      // Prepara dados para o template
      $view_data = [
        'numero_comprovante' => $numero_comprovante,
        'data_emissao' => date('d/m/Y', strtotime($data_emissao ?: date('Y-m-d'))),
        'responsavel_nome' => $responsavel_nome,
        'responsavel_cpf' => $cpf_formatado,
        'aluno_nome' => $aluno_nome,
        'aluno_nome_adicional' => $aluno_nome_adicional,
        'mensalidade_numero' => $mensalidade_numero,
        'valor' => $valor,
        'forma_pagamento' => $forma_pagamento,
        'conferido_por' => $conferido_por ?: '',
        'data_conferencia' => $data_conferencia ? date('d/m/Y', strtotime($data_conferencia)) : ''
      ];

      // Gera HTML do comprovante
      $html = view("Bombeiros\Views\comprovante_template", $view_data);

      // Salva HTML em arquivo temporário
      $upload_path = WRITEPATH . 'uploads/comprovantes/';
      if (!is_dir($upload_path)) {
        @mkdir($upload_path, 0755, true);
        // Cria arquivo .htaccess para proteger o diretório se necessário
        if (file_exists($upload_path)) {
          file_put_contents($upload_path . '.htaccess', 'deny from all');
        }
      }

      $filename = 'comprovante_' . $comprovante_id . '_' . time() . '.html';
      $filepath = $upload_path . $filename;
      file_put_contents($filepath, $html);

      // Atualiza o caminho do arquivo no banco
      $db->table('siamesa_comprovantes')
        ->where('id', $comprovante_id)
        ->update(['arquivo_path' => 'uploads/comprovantes/' . $filename]);

      $db->transComplete();

      if ($db->transStatus() === false) {
        return $this->response->setJSON([
          "success" => false,
          "message" => "Erro na transação do banco de dados."
        ]);
      }

      // URLs para visualizar e baixar o comprovante
      $download_url = get_uri("bombeiros/baixar_comprovante/" . $comprovante_id);
      $visualizar_url = get_uri("bombeiros/visualizar_comprovante/" . $comprovante_id);

      return $this->response->setJSON([
        "success" => true,
        "message" => "Comprovante gerado com sucesso!",
        "comprovante_id" => $comprovante_id,
        "numero_comprovante" => $numero_comprovante,
        "download_url" => $download_url,
        "pdf_url" => $visualizar_url
      ]);

    } catch (\Exception $e) {
      log_message('error', 'Erro ao gerar comprovante: ' . $e->getMessage());
      log_message('error', 'Trace: ' . $e->getTraceAsString());

      if (isset($db) && $db->transStatus() !== false) {
        $db->transRollback();
      }

      return $this->response->setJSON([
        "success" => false,
        "message" => "Erro ao gerar comprovante: " . $e->getMessage()
      ]);
    }
  }

  /**
   * Baixa o comprovante gerado (força download)
   */
  public function baixar_comprovante($comprovante_id)
  {
    try {
      $db = db_connect();

      $comprovante = $db->table('siamesa_comprovantes')
        ->where('id', $comprovante_id)
        ->where('deleted', 0)
        ->get()->getRowArray();

      if (!$comprovante) {
        return "Comprovante não encontrado.";
      }

      // Prepara dados para o template
      $view_data = [
        'numero_comprovante' => $comprovante['numero_comprovante'],
        'data_emissao' => $comprovante['data_emissao'] ? date('d/m/Y', strtotime($comprovante['data_emissao'])) : '',
        'responsavel_nome' => $comprovante['responsavel_nome'],
        'responsavel_cpf' => $comprovante['responsavel_cpf'],
        'aluno_nome' => $comprovante['aluno_nome'],
        'aluno_nome_adicional' => $comprovante['aluno_nome_adicional'],
        'mensalidade_numero' => $comprovante['mensalidade_numero'],
        'valor' => floatval($comprovante['valor']),
        'forma_pagamento' => $comprovante['forma_pagamento'],
        'conferido_por' => $comprovante['conferido_por'] ?: '',
        'data_conferencia' => $comprovante['data_conferencia'] ? date('d/m/Y', strtotime($comprovante['data_conferencia'])) : ''
      ];

      // Renderiza o template
      $html = view("Bombeiros\Views\comprovante_template", $view_data);

      // Força download do arquivo
      $filename = 'Comprovante_SIAMESA_' . $comprovante['numero_comprovante'] . '.html';

      $this->response->setHeader('Content-Type', 'text/html; charset=utf-8');
      $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
      $this->response->setBody($html);

      return $this->response;

    } catch (\Exception $e) {
      log_message('error', 'Erro ao baixar comprovante: ' . $e->getMessage());
      return "Erro ao baixar comprovante: " . $e->getMessage();
    }
  }

  /**
   * Busca dados de uma unidade
   */
  public function buscar_unidade()
  {
    try {
      $db = db_connect();
      $id = $this->request->getPost('id');

      if (!$id) {
        return $this->response->setJSON([
          "success" => false,
          "message" => "ID não informado."
        ]);
      }

      $unidade = $db->table('siamesa_unidades')
        ->where('id', $id)
        ->where('deleted', 0)
        ->get()->getRowArray();

      if (!$unidade) {
        return $this->response->setJSON([
          "success" => false,
          "message" => "Unidade não encontrada."
        ]);
      }

      return $this->response->setJSON([
        "success" => true,
        "data" => $unidade
      ]);

    } catch (\Exception $e) {
      log_message('error', 'Erro ao buscar unidade: ' . $e->getMessage());
      return $this->response->setJSON([
        "success" => false,
        "message" => "Erro ao buscar unidade: " . $e->getMessage()
      ]);
    }
  }

  /**
   * Salva ou atualiza unidade
   */
  public function salvar_unidade()
  {
    try {
      $db = db_connect();

      $id = $this->request->getPost('id');
      $nome_unidade = trim($this->request->getPost('nome_unidade'));
      $cidade = trim($this->request->getPost('cidade'));
      $endereco = trim($this->request->getPost('endereco')) ?: null;
      $status = $this->request->getPost('status') ?: 'Ativo';

      if (!$nome_unidade || !$cidade) {
        return $this->response->setJSON([
          "success" => false,
          "message" => "Nome da unidade e cidade são obrigatórios."
        ]);
      }

      $dados = [
        'nome_unidade' => $nome_unidade,
        'cidade' => $cidade,
        'endereco' => $endereco,
        'status' => $status
      ];

      $db->transStart();

      if ($id) {
        // Atualização
        $db->table('siamesa_unidades')
          ->where('id', $id)
          ->update($dados);

        $mensagem = "Unidade atualizada com sucesso!";
      } else {
        // Novo cadastro
        $dados['deleted'] = 0;
        $db->table('siamesa_unidades')->insert($dados);

        $mensagem = "Unidade cadastrada com sucesso!";
      }

      $db->transComplete();

      if ($db->transStatus() === false) {
        $error = $db->error();
        log_message('error', 'Erro ao salvar unidade: ' . json_encode($error));
        return $this->response->setJSON([
          "success" => false,
          "message" => "Erro ao salvar: " . ($error['message'] ?? 'Erro desconhecido')
        ]);
      }

      return $this->response->setJSON([
        "success" => true,
        "message" => $mensagem
      ]);

    } catch (\Exception $e) {
      log_message('error', 'Exception ao salvar unidade: ' . $e->getMessage());
      return $this->response->setJSON([
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
      ]);
    }
  }

  /**
   * Deleta unidade (soft delete)
   */
  public function deletar_unidade()
  {
    try {
      $db = db_connect();
      $id = $this->request->getPost('id');

      if (!$id) {
        return $this->response->setJSON([
          "success" => false,
          "message" => "ID não informado."
        ]);
      }

      // Verifica se a unidade tem alunos vinculados
      $tem_alunos = $db->table('siamesa_alunos')
        ->where(['unidade_id' => $id, 'deleted' => 0])
        ->countAllResults();

      if ($tem_alunos > 0) {
        return $this->response->setJSON([
          "success" => false,
          "message" => "Não é possível excluir esta unidade pois existem alunos vinculados a ela."
        ]);
      }

      // Marca como deletado (soft delete)
      $db->table('siamesa_unidades')
        ->where('id', $id)
        ->update(['deleted' => 1]);

      return $this->response->setJSON([
        "success" => true,
        "message" => "Unidade removida com sucesso!"
      ]);

    } catch (\Exception $e) {
      log_message('error', 'Exception ao deletar unidade: ' . $e->getMessage());
      return $this->response->setJSON([
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
      ]);
    }
  }

  /**
   * Visualiza o comprovante gerado
   */
  public function visualizar_comprovante($comprovante_id)
  {
    try {
      $db = db_connect();

      $comprovante = $db->table('siamesa_comprovantes')
        ->where('id', $comprovante_id)
        ->where('deleted', 0)
        ->get()->getRowArray();

      if (!$comprovante) {
        return "Comprovante não encontrado.";
      }

      // Prepara dados para o template
      $view_data = [
        'numero_comprovante' => $comprovante['numero_comprovante'],
        'data_emissao' => $comprovante['data_emissao'] ? date('d/m/Y', strtotime($comprovante['data_emissao'])) : '',
        'responsavel_nome' => $comprovante['responsavel_nome'],
        'responsavel_cpf' => $comprovante['responsavel_cpf'],
        'aluno_nome' => $comprovante['aluno_nome'],
        'aluno_nome_adicional' => $comprovante['aluno_nome_adicional'],
        'mensalidade_numero' => $comprovante['mensalidade_numero'],
        'valor' => floatval($comprovante['valor']),
        'forma_pagamento' => $comprovante['forma_pagamento'],
        'conferido_por' => $comprovante['conferido_por'] ?: '',
        'data_conferencia' => $comprovante['data_conferencia'] ? date('d/m/Y', strtotime($comprovante['data_conferencia'])) : ''
      ];

      // Renderiza o template
      return view("Bombeiros\Views\comprovante_template", $view_data);

    } catch (\Exception $e) {
      log_message('error', 'Erro ao visualizar comprovante: ' . $e->getMessage());
      return "Erro ao carregar comprovante: " . $e->getMessage();
    }
  }
}