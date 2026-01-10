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
  public function salvar()
  {
    $db = db_connect();

    // FUNÇÃO AUXILIAR: Converte Data BR (06/01/2026) para SQL (2026-01-06)
    $converterData = function ($data) {
      if (empty($data))
        return null;
      if (strpos($data, '/') !== false) {
        // Explode a data em pedaços e inverte
        $partes = explode('/', $data);
        if (count($partes) == 3) {
          return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
        }
      }
      return $data; // Retorna normal se já estiver certa
    };

    try {
      // Log dos dados recebidos para debug
      log_message('info', 'Dados POST recebidos no salvar: ' . json_encode($this->request->getPost()));
      
      $db->transStart();

      $id = $this->request->getPost('id');
      $num_parcelas = $this->request->getPost('num_parcelas') ?: 6;

      // 1. TRATAMENTO DE DINHEIRO (150,00 ou 150.00 -> 150.00)
      $valor_post = $this->request->getPost('valor_mensalidade');
      $valor_mensalidade = 150.00;

      if ($valor_post) {
        // Converte para string e remove espaços
        $valor_str = trim((string) $valor_post);

        // Se contém vírgula, assume formato brasileiro (ex: 150,00 ou 1.500,00)
        if (strpos($valor_str, ',') !== false) {
          // Remove pontos (separadores de milhar) e troca vírgula por ponto
          $valor_limpo = str_replace('.', '', $valor_str); // Remove pontos de milhar
          $valor_limpo = str_replace(',', '.', $valor_limpo); // Troca vírgula decimal por ponto
        } elseif (strpos($valor_str, '.') !== false) {
          // Se tem ponto mas não tem vírgula, precisa verificar se é milhar ou decimal
          // Conta quantos pontos existem
          $partes = explode('.', $valor_str);
          if (count($partes) == 2 && strlen($partes[1]) <= 2) {
            // Tem apenas 1 ponto e a parte depois tem 2 dígitos ou menos = é decimal (ex: 150.00)
            $valor_limpo = $valor_str; // Mantém como está
          } else {
            // Tem múltiplos pontos ou parte decimal grande = são separadores de milhar (ex: 1.500.00)
            $valor_limpo = str_replace('.', '', $valor_str); // Remove todos os pontos
          }
        } else {
          // Não tem ponto nem vírgula, é um número inteiro
          $valor_limpo = $valor_str;
        }

        $valor_mensalidade = floatval($valor_limpo);

        // Validação: se o valor for muito alto (provavelmente erro de conversão)
        // Exemplo: usuário digitou "150,00" mas campo number enviou "15000"
        if ($valor_mensalidade > 1000 && $valor_mensalidade < 100000) {
          // Se o valor original tinha vírgula ou era esperado ter decimais, pode ter sido multiplicado por 100
          // Tenta dividir por 100 para corrigir
          $valor_corrigido = $valor_mensalidade / 100;
          if ($valor_corrigido >= 50 && $valor_corrigido <= 1000) {
            // Valor corrigido está em faixa razoável, usa ele
            log_message('info', 'Valor corrigido: ' . $valor_post . ' -> ' . $valor_mensalidade . ' -> ' . $valor_corrigido);
            $valor_mensalidade = $valor_corrigido;
          } else {
            log_message('warning', 'Valor de mensalidade muito alto: ' . $valor_post . ' -> ' . $valor_mensalidade . '. Usando valor padrão.');
            $valor_mensalidade = 150.00;
          }
        } elseif ($valor_mensalidade >= 100000) {
          log_message('warning', 'Valor de mensalidade extremamente alto: ' . $valor_post . ' -> ' . $valor_mensalidade . '. Usando valor padrão.');
          $valor_mensalidade = 150.00;
        }
      }

      // 2. TRATAMENTO DE DATAS
      $nasc_aluno_post = $this->request->getPost('nascimento_aluno');
      $nasc_aluno = null;
      if ($nasc_aluno_post) {
        $nasc_aluno = $converterData($nasc_aluno_post);
      }

      $data_inicio_post = $this->request->getPost('data_inicio');
      $data_inicio = null;
      if ($data_inicio_post) {
        $data_inicio = $converterData($data_inicio_post);
      }
      if (!$data_inicio)
        $data_inicio = date('Y-m-d'); // Padrão hoje se vazio

      // --- LOGICA DE GRAVAÇÃO ---

      // Se é atualização (tem ID)
      if ($id) {
        $aluno = $db->table('siamesa_alunos')->where('id', $id)->get()->getRowArray();
        
        if (!$aluno) {
          $db->transRollback();
          return $this->response->setJSON([
            "success" => false,
            "message" => "Aluno não encontrado com ID: " . $id
          ]);
        }
        
        $responsavel_id = $aluno['responsavel_id'] ?? null;

        if ($responsavel_id) {
          // Atualiza responsável apenas se os dados foram enviados
          $dados_responsavel = [];
          
          if ($this->request->getPost('responsavel_nome')) {
            $dados_responsavel['nome'] = trim($this->request->getPost('responsavel_nome'));
          }
          
          if ($this->request->getPost('responsavel_cpf')) {
            $dados_responsavel['cpf'] = preg_replace('/\D/', '', $this->request->getPost('responsavel_cpf'));
          }
          
          if ($this->request->getPost('responsavel_whats')) {
            $dados_responsavel['whats'] = preg_replace('/\D/', '', $this->request->getPost('responsavel_whats'));
          }
          
          if ($this->request->getPost('responsavel_email')) {
            $dados_responsavel['email'] = trim($this->request->getPost('responsavel_email'));
          }
          
          // Só atualiza se houver dados
          if (!empty($dados_responsavel)) {
            log_message('info', 'Atualizando responsável ID ' . $responsavel_id . ' com dados: ' . json_encode($dados_responsavel));
            
            $db->table('siamesa_responsaveis')->where('id', $responsavel_id)->update($dados_responsavel);
            
            // Verifica erro na atualização do responsável
            $error_resp = $db->error();
            if (!empty($error_resp) && isset($error_resp['code']) && $error_resp['code'] != 0) {
              $db->transRollback();
              $mysql_error = $this->getMysqlError($db);
              log_message('error', 'Erro ao atualizar responsável ID ' . $responsavel_id . ': ' . json_encode($error_resp));
              log_message('error', 'MySQL Error: ' . $mysql_error);
              return $this->response->setJSON([
                "success" => false,
                "message" => "Erro ao atualizar responsável: " . ($error_resp['message'] ?? $mysql_error ?? 'Erro desconhecido'),
                "debug" => [
                  "error" => $error_resp,
                  "mysql_error" => $mysql_error,
                  "dados_tentados" => $dados_responsavel
                ]
              ]);
            }
          }
        }

        // Prepara dados de atualização do aluno (apenas campos que existem na tabela)
        $dados_atualizacao = [];
        
        // Campos básicos do aluno
        if ($this->request->getPost('nome_aluno')) {
          $dados_atualizacao['nome_aluno'] = trim($this->request->getPost('nome_aluno'));
        }
        
        if ($nasc_aluno) {
          $dados_atualizacao['nascimento_aluno'] = $nasc_aluno;
        }
        
        // Turma/horário - verifica se o campo existe
        $horario_post = $this->request->getPost('horario');
        if ($horario_post) {
          $dados_atualizacao['turma'] = $horario_post;
        }
        
        // Campos de camisa
        $quer_camisa_post = $this->request->getPost('quer_camisa');
        if ($quer_camisa_post !== null) {
          $dados_atualizacao['quer_camisa'] = ($quer_camisa_post == '1' || $quer_camisa_post === true) ? 1 : 0;
        }
        
        $tamanho_camisa_post = $this->request->getPost('tamanho_camisa');
        if ($tamanho_camisa_post !== null) {
          $dados_atualizacao['tamanho_camisa'] = $tamanho_camisa_post ?: null;
        }

        // Valida e salva o status (deve ser exatamente "Ativo" ou "Cancelado")
        $status_post = trim($this->request->getPost('status'));
        if (!empty($status_post) && in_array($status_post, ['Ativo', 'Cancelado'], true)) {
          $dados_atualizacao['status'] = $status_post;
        }

        // Só atualiza se houver dados para atualizar
        if (!empty($dados_atualizacao)) {
          log_message('info', 'Atualizando aluno ID ' . $id . ' com dados: ' . json_encode($dados_atualizacao));
          
          // Executa a atualização
          $db->table('siamesa_alunos')->where('id', $id)->update($dados_atualizacao);
          
          // Verifica se houve erro na atualização
          $error_update = $db->error();
          if (!empty($error_update) && isset($error_update['code']) && $error_update['code'] != 0) {
            $db->transRollback();
            log_message('error', 'Erro ao atualizar aluno ID ' . $id . ': ' . json_encode($error_update));
            log_message('error', 'Dados tentados: ' . json_encode($dados_atualizacao));
            log_message('error', 'Dados POST recebidos: ' . json_encode($this->request->getPost()));
            
            // Tenta obter mais detalhes do erro MySQL
            $mysql_error = $this->getMysqlError($db);
            
            return $this->response->setJSON([
              "success" => false,
              "message" => "Erro ao atualizar aluno: " . ($error_update['message'] ?? $mysql_error ?? 'Erro desconhecido'),
              "debug" => [
                "error" => $error_update,
                "mysql_error" => $mysql_error,
                "dados_tentados" => $dados_atualizacao,
                "aluno_id" => $id
              ]
            ]);
          }
        } else {
          log_message('info', 'Nenhum dado para atualizar no aluno ID ' . $id);
        }

      } else {
        // NOVO CADASTRO - Primeiro insere o responsável
        $db->table('siamesa_responsaveis')->insert([
          'nome' => trim($this->request->getPost('responsavel_nome')),
          'cpf' => preg_replace('/\D/', '', $this->request->getPost('responsavel_cpf')),
          'whats' => preg_replace('/\D/', '', $this->request->getPost('responsavel_whats')),
          'email' => trim($this->request->getPost('responsavel_email')) ?: null
        ]);
        $responsavel_id = $db->insertID();

        // Verifica se houve erro ao inserir responsável
        if (!$responsavel_id) {
          $error = $db->error();
          $db->transRollback();
          log_message('error', 'Erro ao inserir responsável: ' . json_encode($error));
          log_message('error', 'Dados POST: ' . json_encode($this->request->getPost()));
          return $this->response->setJSON([
            "success" => false,
            "message" => "Erro ao salvar responsável: " . ($error['message'] ?? 'Erro desconhecido - verifique os logs'),
            "debug" => [
              "error" => $error,
              "data_post" => $this->request->getPost()
            ]
          ]);
        }

        // Depois insere o aluno
        $unidade_id = $this->request->getPost('unidade_id');
        if (!$unidade_id) {
          $db->transRollback();
          return $this->response->setJSON([
            "success" => false,
            "message" => "Unidade é obrigatória."
          ]);
        }

        $db->table('siamesa_alunos')->insert([
          'responsavel_id' => $responsavel_id,
          'unidade_id' => $unidade_id,
          'nome_aluno' => trim($this->request->getPost('nome_aluno')),
          'nascimento_aluno' => $nasc_aluno,
          'turma' => $this->request->getPost('horario'),
          'valor_mensalidade' => $valor_mensalidade,
          'data_inicio' => $data_inicio,
          'data_matricula' => date('Y-m-d'),
          'quer_camisa' => ($this->request->getPost('quer_camisa') == '1' || $this->request->getPost('quer_camisa') === true) ? 1 : 0,
          'tamanho_camisa' => $this->request->getPost('tamanho_camisa') ?: null,
          'status' => 'Ativo',
          'deleted' => 0
        ]);
        $aluno_id = $db->insertID();

        // Verifica se houve erro ao inserir aluno
        if (!$aluno_id) {
          $error = $db->error();
          $db->transRollback();
          log_message('error', 'Erro ao inserir aluno: ' . json_encode($error));
          log_message('error', 'Responsável ID: ' . $responsavel_id);
          return $this->response->setJSON([
            "success" => false,
            "message" => "Erro ao salvar aluno: " . ($error['message'] ?? 'Erro desconhecido - verifique os logs'),
            "debug" => [
              "error" => $error,
              "responsavel_id" => $responsavel_id
            ]
          ]);
        }

        // GERAR PARCELAS
        try {
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

            // Verifica se houve erro ao inserir parcela
            $error = $db->error();
            if (!empty($error['code']) || !empty($error['message'])) {
              $db->transRollback();
              $mysql_error = $this->getMysqlError($db);
              log_message('error', 'Erro ao inserir parcela ' . ($i + 1) . ': ' . json_encode($error));
              log_message('error', 'MySQL Error: ' . $mysql_error);
              return $this->response->setJSON([
                "success" => false,
                "message" => "Erro ao gerar parcela " . ($i + 1) . ": " . ($error['message'] ?? $mysql_error ?? 'Erro desconhecido'),
                "debug" => [
                  "error" => $error,
                  "mysql_error" => $mysql_error,
                  "parcela_num" => $i + 1,
                  "vencimento" => $venc,
                  "tabela" => "siamesa_cobrancas"
                ]
              ]);
            }
          }
        } catch (\Exception $e) {
          $db->transRollback();
          $mysql_error = $this->getMysqlError($db);
          log_message('error', 'Exception ao inserir parcelas: ' . $e->getMessage());
          return $this->response->setJSON([
            "success" => false,
            "message" => "Erro ao gerar parcelas: " . $e->getMessage(),
            "debug" => [
              "exception" => $e->getMessage(),
              "mysql_error" => $mysql_error,
              "file" => $e->getFile(),
              "line" => $e->getLine()
            ]
          ]);
        }

        // COBRANÇA CAMISA
        if ($this->request->getPost('quer_camisa') == '1' || $this->request->getPost('quer_camisa') === true) {
          try {
            $tamanho_camisa = $this->request->getPost('tamanho_camisa') ?: '';
            $db->table('siamesa_cobrancas')->insert([
              'aluno_id' => $aluno_id,
              'vencimento' => date('Y-m-d'),
              'valor' => 67.00,
              'competencia' => date('m/Y'),
              'status' => 'Pendente',
              'tipo' => 'Camiseta'
              // Nota: A informação do tamanho da camisa está salva na tabela siamesa_alunos no campo tamanho_camisa
            ]);

            // Verifica se houve erro ao inserir cobrança da camisa
            $error = $db->error();
            if (!empty($error['code']) || !empty($error['message'])) {
              $db->transRollback();
              $mysql_error = $this->getMysqlError($db);
              log_message('error', 'Erro ao inserir cobrança de camisa: ' . json_encode($error));
              return $this->response->setJSON([
                "success" => false,
                "message" => "Erro ao gerar cobrança de camisa: " . ($error['message'] ?? $mysql_error ?? 'Erro desconhecido'),
                "debug" => [
                  "error" => $error,
                  "mysql_error" => $mysql_error
                ]
              ]);
            }
          } catch (\Exception $e) {
            $db->transRollback();
            $mysql_error = $this->getMysqlError($db);
            log_message('error', 'Exception ao inserir cobrança de camisa: ' . $e->getMessage());
            return $this->response->setJSON([
              "success" => false,
              "message" => "Erro ao gerar cobrança de camisa: " . $e->getMessage(),
              "debug" => [
                "exception" => $e->getMessage(),
                "mysql_error" => $mysql_error
              ]
            ]);
          }
        }
      }

      // Verifica se há erro ANTES de completar a transação
      $error_before_complete = $db->error();
      if (!empty($error_before_complete) && isset($error_before_complete['code']) && $error_before_complete['code'] != 0) {
        $db->transRollback();
        $mysql_error = $this->getMysqlError($db);
        log_message('error', 'Erro detectado ANTES de completar transação: ' . json_encode($error_before_complete));
        log_message('error', 'MySQL Error: ' . $mysql_error);
        return $this->response->setJSON([
          "success" => false,
          "message" => "Erro antes de completar transação: " . ($error_before_complete['message'] ?? $mysql_error ?? 'Erro desconhecido'),
          "debug" => [
            "error" => $error_before_complete,
            "mysql_error" => $mysql_error
          ]
        ]);
      }

      // Completa a transação
      $db->transComplete();

      // Verifica se a transação foi bem-sucedida DEPOIS de completar
      if ($db->transStatus() === false) {
        $error_after_complete = $db->error();
        $mysql_error = $this->getMysqlError($db);

        // Log detalhado do erro
        log_message('error', 'Erro na transação - Status: ' . ($db->transStatus() ? 'true' : 'false'));
        log_message('error', 'Erro antes de completar: ' . json_encode($error_before_complete));
        log_message('error', 'Erro depois de completar: ' . json_encode($error_after_complete));
        log_message('error', 'MySQL Error direto: ' . $mysql_error);
        log_message('error', 'Dados recebidos: ' . json_encode($this->request->getPost()));

        // Prioriza mensagens de erro na seguinte ordem:
        $error_message = '';
        if (!empty($error_after_complete['message'])) {
          $error_message = $error_after_complete['message'];
        } elseif (!empty($error_before_complete['message'])) {
          $error_message = $error_before_complete['message'];
        } elseif (!empty($mysql_error) && $mysql_error !== 'N/A') {
          $error_message = $mysql_error;
        } elseif (!empty($error_after_complete['code'])) {
          $error_message = 'Erro SQL código ' . $error_after_complete['code'];
        } elseif (!empty($error_before_complete['code'])) {
          $error_message = 'Erro SQL código ' . $error_before_complete['code'];
        } else {
          // Último recurso: verifica se alguma tabela não existe
          $error_message = 'Erro desconhecido na transação. Possíveis causas: tabela não existe, campo inválido ou constraint violada. Verifique os logs do servidor.';
        }

        return $this->response->setJSON([
          "success" => false,
          "message" => "Erro SQL: " . $error_message,
          "debug" => [
            "error_before_complete" => $error_before_complete,
            "error_after_complete" => $error_after_complete,
            "mysql_error" => $mysql_error,
            "trans_status" => $db->transStatus(),
            "data_recebida" => $this->request->getPost(),
            "aluno_id" => isset($aluno_id) ? $aluno_id : null,
            "responsavel_id" => isset($responsavel_id) ? $responsavel_id : null
          ]
        ]);
      }

      // Verifica se houve erro antes de retornar sucesso
      $error_final = $db->error();
      if (!empty($error_final) && isset($error_final['code']) && $error_final['code'] != 0) {
        log_message('error', 'Erro após transação: ' . json_encode($error_final));
        return $this->response->setJSON([
          "success" => false,
          "message" => "Erro ao salvar: " . ($error_final['message'] ?? 'Erro desconhecido')
        ]);
      }

      // Mensagem diferente para atualização vs novo cadastro
      $mensagem = $id ? "Dados atualizados com sucesso!" : "Matrícula realizada com sucesso!";
      return $this->response->setJSON(["success" => true, "message" => $mensagem]);

    } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
      // Captura erros específicos do banco de dados
      $error = $db->error();
      log_message('error', 'DatabaseException: ' . $e->getMessage());
      log_message('error', 'Trace: ' . $e->getTraceAsString());

      return $this->response->setJSON([
        "success" => false,
        "message" => "Erro no banco de dados: " . $e->getMessage(),
        "debug" => [
          "exception" => $e->getMessage(),
          "file" => $e->getFile(),
          "line" => $e->getLine(),
          "error" => $error,
          "mysql_error" => $this->getMysqlError($db)
        ]
      ]);
    } catch (\Exception $e) {
      // Captura qualquer outro erro
      log_message('error', 'Exception: ' . $e->getMessage());
      log_message('error', 'File: ' . $e->getFile() . ' Line: ' . $e->getLine());
      log_message('error', 'Trace: ' . $e->getTraceAsString());

      return $this->response->setJSON([
        "success" => false,
        "message" => "Erro: " . $e->getMessage(),
        "debug" => [
          "exception" => $e->getMessage(),
          "file" => $e->getFile(),
          "line" => $e->getLine(),
          "class" => get_class($e)
        ]
      ]);
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
    if (!$file || !$file->isValid() || $file->getExtension() !== 'csv') {
      return $this->response->setJSON(["success" => false, "message" => "Arquivo inválido."]);
    }

    $db = db_connect();
    $handle = fopen($file->getTempName(), "r");
    fgetcsv($handle, 1000, ";"); // Pula cabeçalho

    $importados = 0;
    try {
      while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
        if (count($row) < 10)
          continue;

        $db->transStart();

        // 1. Responsável
        // Insere responsável usando SQL direto
        $db->table('siamesa_responsaveis')->insert([
          'nome' => $row[0],
          'cpf' => preg_replace('/\D/', '', $row[1]),
          'whats' => $row[2],
          'email' => $row[3]
        ]);
        $resp_id = $db->insertID();

        // 2. Aluno (Colunas 10 e 11 para camisa)
        $db->table('siamesa_alunos')->insert([
          'responsavel_id' => $resp_id,
          'nome_aluno' => $row[4],
          'nascimento_aluno' => $row[5],
          'turma' => $row[6],
          'valor_mensalidade' => $row[7],
          'data_inicio' => $row[9],
          'quer_camisa' => $row[10] ?? 0,
          'tamanho_camisa' => $row[11] ?? null,
          'status' => 'Ativo',
          'deleted' => 0
        ]);
        $aluno_id = $db->insertID();

        // 3. Financeiro
        $parcelas = (int) $row[8];
        for ($i = 0; $i < $parcelas; $i++) {
          $venc = date('Y-m-d', strtotime($row[9] . " +$i month"));
          $db->table('siamesa_cobrancas')->insert([
            'aluno_id' => $aluno_id,
            'vencimento' => $venc,
            'valor' => $row[7],
            'competencia' => date('m/Y', strtotime($venc)),
            'status' => 'Pendente',
            'tipo' => 'Mensalidade'
          ]);
        }

        $db->transComplete();
        $importados++;
      }
      fclose($handle);
      return $this->response->setJSON(["success" => true, "message" => "$importados alunos importados!"]);
    } catch (\Exception $e) {
      return $this->response->setJSON(["success" => false, "message" => $e->getMessage()]);
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
      $db->transStart();
      
      $id = $this->request->getPost('id');
      
      $dados = [
        'nome' => trim($this->request->getPost('nome')),
        'cpf' => preg_replace('/\D/', '', $this->request->getPost('cpf')),
        'whats' => preg_replace('/\D/', '', $this->request->getPost('whats')),
        'celular' => preg_replace('/\D/', '', $this->request->getPost('celular')),
        'email' => trim($this->request->getPost('email')),
        'endereco' => trim($this->request->getPost('endereco'))
      ];
      
      // Remove campos vazios
      $dados = array_filter($dados, function($value) {
        return $value !== null && $value !== '';
      });
      
      if ($id) {
        // Atualização
        $db->table('siamesa_responsaveis')->where('id', $id)->update($dados);
      } else {
        // Novo (não deveria acontecer aqui, mas por segurança)
        $dados['deleted'] = 0;
        $db->table('siamesa_responsaveis')->insert($dados);
      }
      
      $db->transComplete();
      
      if ($db->transStatus() === false) {
        $error = $db->error();
        log_message('error', 'Erro ao salvar responsável: ' . json_encode($error));
        return $this->response->setJSON([
          "success" => false,
          "message" => "Erro ao salvar: " . ($error['message'] ?? 'Erro desconhecido')
        ]);
      }
      
      return $this->response->setJSON(["success" => true, "message" => "Responsável salvo com sucesso!"]);
      
    } catch (\Exception $e) {
      log_message('error', 'Exception ao salvar responsável: ' . $e->getMessage());
      return $this->response->setJSON([
        "success" => false,
        "message" => "Erro: " . $e->getMessage()
      ]);
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
          $mensalidade_num = (int)$matches[1];
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
      $mensalidade_numero = (int)$this->request->getPost('mensalidade_numero');
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