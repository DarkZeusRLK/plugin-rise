<?php

namespace Bombeiros\Controllers;

use App\Controllers\Security_Controller;

class Bombeiros extends Security_Controller
{
  /**
   * Construtor
   */
  public function __construct()
  {
    parent::__construct();
    // CORREÇÃO: No CodeIgniter 4 usamos a função model() e não $this->load
    $this->General_files_model = model("App\Models\General_files_model");
  }
  private function get_api_key()
  {
    // Tenta pegar do .env (CodeIgniter 4 usa getenv ou env())
    $key = getenv('GEMINI_API_KEY');

    // Fallback: Se getenv falhar, tenta $_ENV (alguns servidores precisam disso)
    if (!$key && isset($_ENV['GEMINI_API_KEY'])) {
      $key = $_ENV['GEMINI_API_KEY'];
    }

    if (empty($key)) {
      log_message('error', 'CRÍTICO: Chave da API não encontrada no arquivo .env');
      throw new \Exception('Configuração de API ausente no servidor.');
    }

    return $key;
  }
  public function upload_e_ler_ia()
  {
    $this->carregar_env_plugin();

    if (!empty($_FILES['arquivo_ia']['name'])) {
      $file = $this->request->getFile('arquivo_ia');

      if ($file->isValid() && !$file->hasMoved()) {
        $path = getcwd() . '/files/';
        $newName = $file->getRandomName();
        $file->move($path, $newName);
        $fullPath = $path . $newName;
        $db = db_connect();
        $data_file = [
          'file_name' => $newName,
          'file_size' => $file->getSize(),
          'created_at' => date('Y-m-d H:i:s'),
          'uploaded_by' => $this->login_user->id ?? 1,
          'context' => 'bombeiros_ia',
          'context_id' => 0
        ];
        $db->table('general_files')->insert($data_file);
        $file_id = $db->insertID();

        // (Pode usar DOCX ou PDF para extrair as informações)
        $ext = strtolower(pathinfo($newName, PATHINFO_EXTENSION));

        if ($ext === 'docx') {
          $textoWord = $this->readDocx($fullPath);

          if (!$textoWord || strpos($textoWord, 'Erro') !== false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Erro ao ler DOCX: ' . $textoWord]);
          }
          return $this->analisar_texto_com_gemini($textoWord);
        } else {
          return $this->extrair_com_gemini($file_id);
        }
      }
    }

    return $this->response->setJSON(['success' => false, 'message' => 'Nenhum arquivo enviado.']);
  }
  private function analisar_texto_com_gemini($texto)
  {
    try {
      $api_key = $this->get_api_key();
    } catch (\Exception $e) {
      return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $api_key;

    $prompt_text = "Você é um assistente. Analise o texto da ficha de matrícula.
      Retorne APENAS um JSON válido.
      REGRAS DE FORMATAÇÃO:
      1. Datas: Use SEMPRE o formato 'AAAA-MM-DD'.
      2. Horário: Retorne apenas a hora.
      3. Unidade: Identifique a cidade ou unidade citada.
      Campos JSON:
      responsavel_nome, responsavel_nascimento, responsavel_rg, responsavel_cpf,
      responsavel_endereco, responsavel_numero, responsavel_complemento, responsavel_bairro,
      responsavel_cep, responsavel_cidade, responsavel_whats, responsavel_celular,
      responsavel_recado, responsavel_email, nome_aluno, nascimento_aluno, rg_aluno,
      cpf_aluno, horario, tamanho_camisa, unidade.
      TEXTO DA FICHA: " . substr($texto, 0, 15000);

    $payload = [
      "contents" => [["parts" => [["text" => $prompt_text]]]],
      "generationConfig" => ["response_mime_type" => "application/json"]
    ];

    $resposta_json = $this->executar_curl_gemini($url, $payload);
    return $this->formatar_resposta_para_frontend($resposta_json);
  }
  public function extrair_com_gemini($file_id)
  {
    try {
      $api_key = $this->get_api_key();
    } catch (\Exception $e) {
      return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
    }


    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $api_key;

    $file_info = $this->General_files_model->get_one($file_id);
    if (!$file_info)
      return $this->response->setJSON(['success' => false, 'message' => 'Arquivo não encontrado.']);


    $path = getcwd() . '/files/' . $file_info->file_name;
    if (!file_exists($path))
      $path = getcwd() . '/files/timeline_files/' . $file_info->file_name;
    if (!file_exists($path))
      return $this->response->setJSON(['success' => false, 'message' => 'Arquivo físico sumiu.']);

    $mime_type = mime_content_type($path);
    $base64_data = base64_encode(file_get_contents($path));

    $prompt_text = "Você é um assistente administrativo. Analise esta imagem de matrícula.
       Retorne APENAS um JSON válido.
       REGRAS DE FORMATAÇÃO:
       1. Datas: Use SEMPRE o formato 'AAAA-MM-DD' (Ex: 2014-05-20).
       2. Horário: Retorne apenas a hora (Ex: '08:30', '13:30').
       3. Unidade: Copie exatamente o nome da cidade ou unidade escrita na ficha.
       Campos JSON:
       responsavel_nome, responsavel_nascimento, responsavel_rg, responsavel_cpf,
       responsavel_endereco, responsavel_numero, responsavel_complemento, responsavel_bairro,
       responsavel_cep, responsavel_cidade, responsavel_whats, responsavel_celular,
       responsavel_recado, responsavel_email, nome_aluno, nascimento_aluno, rg_aluno,
       cpf_aluno, horario, tamanho_camisa, unidade.";

    $payload = [
      "contents" => [
        [
          "parts" => [
            ["text" => $prompt_text],
            ["inline_data" => ["mime_type" => $mime_type, "data" => $base64_data]]
          ]
        ]
      ],
      "generationConfig" => ["response_mime_type" => "application/json"]
    ];

    $resposta_json = $this->executar_curl_gemini($url, $payload);
    return $this->formatar_resposta_para_frontend($resposta_json);
  }
  private function formatar_resposta_para_frontend($json_response)
  {
    $body = $json_response->getBody();
    $data = json_decode($body, true);

    if (!isset($data['success']) || $data['success'] === false) {
      return $json_response;
    }


    $dados_ia = $data['data'];


    $campos_data = ['nascimento_aluno', 'responsavel_nascimento'];

    foreach ($campos_data as $campo) {
      if (!empty($dados_ia[$campo])) {
        // Tenta converter qualquer formato (20/05/2010 ou 20-05-2010) para Y-m-d
        $timestamp = strtotime(str_replace('/', '-', $dados_ia[$campo]));
        if ($timestamp) {
          $dados_ia[$campo] = date('Y-m-d', $timestamp);
        }
      }
    }


    // $dados_ia['responsavel_celular'] = preg_replace('/[^0-9]/', '', $dados_ia['responsavel_celular']);

    // Atualiza o JSON final
    return $this->response->setJSON([
      'success' => true,
      'data' => $dados_ia
    ]);
  }
  private function executar_curl_gemini($url, $payload)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      $erro = curl_error($ch);
      curl_close($ch);
      return $this->response->setJSON(['success' => false, 'message' => 'Erro cURL: ' . $erro]);
    }
    curl_close($ch);

    $json_response = json_decode($response, true);

    if (isset($json_response['candidates'][0]['content']['parts'][0]['text'])) {
      $raw_text = $json_response['candidates'][0]['content']['parts'][0]['text'];
      $clean_json = str_replace(['```json', '```'], '', $raw_text);
      $dados = json_decode($clean_json, true);

      if ($dados)
        return $this->response->setJSON(['success' => true, 'data' => $dados]);
      return $this->response->setJSON(['success' => false, 'message' => 'JSON inválido.', 'raw' => $raw_text]);
    }

    return $this->response->setJSON(['success' => false, 'message' => 'Erro IA.', 'debug' => $json_response]);
  }

  private function carregar_env_plugin()
  {
    // Caminho relativo: 
    // __DIR__ é .../plugins/Bombeiros/Controllers
    // Subindo um nível (../) chegamos em .../plugins/Bombeiros/.env
    $path = __DIR__ . '/../.env';

    if (file_exists($path)) {
      $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      foreach ($lines as $line) {
        // Ignora comentários
        if (strpos(trim($line), '#') === 0)
          continue;

        if (strpos($line, '=') !== false) {
          list($name, $value) = explode('=', $line, 2);
          $name = trim($name);
          $value = trim($value);

          // Remove aspas extras se houver
          $value = trim($value, '"\'');

          // Força o carregamento
          putenv(sprintf('%s=%s', $name, $value));
          $_ENV[$name] = $value;
          $_SERVER[$name] = $value;
        }
      }
    } else {
      // Grava no log do CodeIgniter (writable/logs) se não achar
      log_message('error', 'Arquivo .env do plugin NÃO encontrado em: ' . $path);
    }
  }

  private function render_view($path, $data = [])
  {
    $path = str_replace('\\', '/', $path);
    return view($path, $data);
  }

  // Mantive sua função auxiliar de erro
  private function getMysqlError($db)
  {
    try {
      $reflection = new \ReflectionClass($db);
      if ($reflection->hasProperty('connID')) {
        $prop = $reflection->getProperty('connID');
        $prop->setAccessible(true);
        $connID = $prop->getValue($db);
        if ($connID && is_object($connID) && isset($connID->error) && !empty($connID->error)) {
          return $connID->error . ' (Código: ' . ($connID->errno ?? 'N/A') . ')';
        }
      }
      if (method_exists($db, 'getError')) {
        $error = $db->getError();
        if (!empty($error))
          return is_array($error) ? ($error['message'] ?? json_encode($error)) : $error;
      }
      $error = $db->error();
      if (!empty($error['message']))
        return $error['message'];
    } catch (\Exception $e) {
      $error = $db->error();
      if (!empty($error['message']))
        return $error['message'];
    }
    return 'N/A';
  }

  public function index()
  {
    $db = db_connect();
    $view_data['unidades'] = $db->table('siamesa_unidades')
      ->where(['status' => 'Ativo', 'deleted' => 0])
      ->orderBy('nome_unidade', 'ASC')
      ->get()->getResultArray();

    $unidade_id = $this->request->getGet('unidade_id');

    $query = $db->table('siamesa_alunos a')
      ->select('a.*, r.nome as responsavel_nome, r.whats as responsavel_whats, u.nome_unidade, u.cidade')
      ->join('siamesa_responsaveis r', 'r.id = a.responsavel_id', 'left')
      ->join('siamesa_unidades u', 'u.id = a.unidade_id', 'left')
      ->where('a.deleted', 0);

    if ($unidade_id && $unidade_id != '') {
      $query->where('a.unidade_id', $unidade_id);
    }

    $view_data['alunos'] = $query->get()->getResultArray();
    $view_data['unidade_selecionada'] = $unidade_id;
    $view_data['total_ativos'] = $db->table('siamesa_alunos')->where(['status' => 'Ativo', 'deleted' => 0])->countAllResults();

    return $this->template->render("Bombeiros\Views\index", $view_data);
  }

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

      // --- DADOS DO RESPONSÁVEL ---
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

      $res_existente = $db->table('siamesa_responsaveis')->where('whats', $whats_limpo)->get()->getRow();

      if ($res_existente) {
        $responsavel_id = $res_existente->id;
        $db->table('siamesa_responsaveis')->where('id', $responsavel_id)->update($dados_resp);
      } else {
        $db->table('siamesa_responsaveis')->insert($dados_resp);
        $responsavel_id = $db->insertID();
      }

      // --- DADOS DO ALUNO ---
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
        $db->table('siamesa_alunos')->where('id', $id)->update($dados_aluno);
        $mensagem = "Dados atualizados com sucesso!";
      } else {
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
        return $this->response->setJSON(["success" => false, "message" => "Erro na transação."]);
      }

      return $this->response->setJSON(["success" => true, "message" => $mensagem]);

    } catch (\Exception $e) {
      return $this->response->setJSON(["success" => false, "message" => "Erro: " . $e->getMessage()]);
    }
  }

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
        if (!isset($historico[$aluno['id']]))
          $f_check = 'checked';

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

  public function salvar_presenca()
  {
    $db = db_connect();
    $data_aula = $this->request->getPost('data_aula');
    $presencas = $this->request->getPost('presencas');

    if (!$data_aula || empty($presencas))
      return $this->response->setJSON(["success" => false, "message" => "Nenhum dado recebido."]);

    try {
      $db->transStart();
      foreach ($presencas as $aluno_id => $status) {
        $where = ['aluno_id' => $aluno_id, 'data_aula' => $data_aula];
        $registro = $db->table('siamesa_presenca')->where($where)->get()->getRow();

        if ($registro) {
          $db->table('siamesa_presenca')->where('id', $registro->id)->update(['status' => (int) $status]);
        } else {
          $db->table('siamesa_presenca')->insert(['aluno_id' => $aluno_id, 'data_aula' => $data_aula, 'status' => (int) $status]);
        }
      }
      $db->transComplete();
      if ($db->transStatus() === false)
        return $this->response->setJSON(["success" => false, "message" => "Erro ao gravar no banco."]);

      return $this->response->setJSON(["success" => true, "message" => "Chamada salva!"]);
    } catch (\Exception $e) {
      return $this->response->setJSON(["success" => false, "message" => $e->getMessage()]);
    }
  }

  public function importar_csv()
  {
    $file = $this->request->getFile('file');
    if (!$file || !$file->isValid())
      return $this->response->setJSON(["success" => false, "message" => "Arquivo inválido."]);

    $db = db_connect();
    $filePath = $file->getTempName();

    $content = file_get_contents($filePath);
    if (!mb_check_encoding($content, 'UTF-8')) {
      $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
      file_put_contents($filePath, $content);
    }

    $handle = fopen($filePath, "r");
    fgetcsv($handle, 2000, ";");

    $importados = 0;
    $unidade_padrao = 1;

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
          continue;

        $db->transStart();
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

        $dados_aluno = [
          'unidade_id' => $unidade_padrao,
          'responsavel_id' => $resp_id,
          'nome_aluno' => mb_convert_case(trim($row[13]), MB_CASE_TITLE, "UTF-8"),
          'nascimento_aluno' => $converteData($row[14]),
          'rg_aluno' => trim($row[15] ?? ''),
          'cpf_aluno' => $limpaDoc($row[16] ?? ''),
          'turma' => trim($row[18]),
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
      $cobrancas = $db->table('siamesa_cobrancas c')
        ->select('c.*, a.nome_aluno')
        ->join('siamesa_alunos a', 'a.id = c.aluno_id')
        ->where('a.deleted', 0)
        ->orderBy('a.nome_aluno', 'ASC')
        ->orderBy('c.vencimento', 'ASC')
        ->get()->getResultArray();

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
      $db->table('siamesa_cobrancas')->where('id', $id)->update(['status' => 'Pago', 'data_pagamento' => date('Y-m-d H:i:s')]);
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

      $data['total_pago'] = $db->table('siamesa_cobrancas')->where('status', 'Pago')->selectSum('valor')->get()->getRow()->valor ?? 0;
      $data['total_pendente'] = $db->table('siamesa_cobrancas')->where('status', 'Pendente')->selectSum('valor')->get()->getRow()->valor ?? 0;

      $data['inadimplentes'] = $db->table('siamesa_cobrancas c')
        ->select('c.*, a.nome_aluno, r.nome as resp_nome, r.whats')
        ->join('siamesa_alunos a', 'a.id = c.aluno_id')
        ->join('siamesa_responsaveis r', 'r.id = a.responsavel_id')
        ->where('c.vencimento <', $hoje)
        ->where('c.status', 'Pendente')
        ->orderBy('c.vencimento', 'ASC')
        ->orderBy('a.nome_aluno', 'ASC')
        ->get()->getResultArray();

      $data['total_inadimplencia'] = 0;
      foreach ($data['inadimplentes'] as $inad) {
        $data['total_inadimplencia'] += floatval($inad['valor']);
      }

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

  public function lista_responsaveis()
  {
    try {
      $db = db_connect();
      $view_data['responsaveis'] = $db->table('siamesa_responsaveis')->where('deleted', 0)->orderBy('nome', 'ASC')->get()->getResultArray();
      return view("Bombeiros\Views\lista_responsaveis", $view_data);
    } catch (\Exception $e) {
      return "<div class='alert alert-danger'>Erro ao carregar responsáveis: " . $e->getMessage() . "</div>";
    }
  }

  public function salvar_responsavel()
  {
    $db = db_connect();
    try {
      $id = $this->request->getPost('id');
      $whats = preg_replace('/\D/', '', $this->request->getPost('whats'));
      $cpf = preg_replace('/\D/', '', $this->request->getPost('cpf'));
      $cel = preg_replace('/\D/', '', $this->request->getPost('celular'));

      if (empty($whats))
        return $this->response->setJSON(["success" => false, "message" => "O WhatsApp é obrigatório."]);

      $dados = [
        'nome' => trim($this->request->getPost('nome')),
        'cpf' => $cpf,
        'whats' => $whats,
        'celular' => $cel,
        'email' => trim($this->request->getPost('email')),
        'endereco' => trim($this->request->getPost('endereco'))
      ];

      $db->table('siamesa_responsaveis')->where('id', $id)->update($dados);
      return $this->response->setJSON(["success" => true]);
    } catch (\Exception $e) {
      return $this->response->setJSON(["success" => false, "message" => "Erro no banco: " . $e->getMessage()]);
    }
  }

  public function deletar_responsavel()
  {
    $db = db_connect();
    $id = $this->request->getPost('id');
    $tem_alunos = $db->table('siamesa_alunos')->where(['responsavel_id' => $id, 'deleted' => 0])->countAllResults();
    if ($tem_alunos > 0)
      return $this->response->setJSON(["success" => false, "message" => "Existem alunos vinculados a este responsável."]);
    $db->table('siamesa_responsaveis')->where('id', $id)->update(['deleted' => 1]);
    return $this->response->setJSON(["success" => true, "message" => "Responsável removido com sucesso!"]);
  }

  public function buscar_dados_comprovante()
  {
    try {
      $db = db_connect();
      $cobranca_id = $this->request->getPost('cobranca_id');
      $aluno_id = $this->request->getPost('aluno_id');
      if (!$cobranca_id || !$aluno_id)
        return $this->response->setJSON(["success" => false, "message" => "Dados incompletos."]);

      $cobranca = $db->table('siamesa_cobrancas c')
        ->select('c.*, a.nome_aluno, a.responsavel_id, r.nome as responsavel_nome, r.cpf as responsavel_cpf')
        ->join('siamesa_alunos a', 'a.id = c.aluno_id')
        ->join('siamesa_responsaveis r', 'r.id = a.responsavel_id')
        ->where('c.id', $cobranca_id)
        ->where('a.id', $aluno_id)
        ->get()->getRowArray();

      if (!$cobranca)
        return $this->response->setJSON(["success" => false, "message" => "Cobrança não encontrada."]);

      $cpf_formatado = $cobranca['responsavel_cpf'];
      if (strlen($cpf_formatado) == 11) {
        $cpf_formatado = substr($cpf_formatado, 0, 3) . '.' . substr($cpf_formatado, 3, 3) . '.' . substr($cpf_formatado, 6, 3) . '-' . substr($cpf_formatado, 9, 2);
      }

      $mensalidade_num = 1;
      if (!empty($cobranca['competencia'])) {
        preg_match('/^(\d+)\//', $cobranca['competencia'], $matches);
        if (!empty($matches[1]))
          $mensalidade_num = (int) $matches[1];
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
      return $this->response->setJSON(["success" => false, "message" => "Erro ao buscar dados: " . $e->getMessage()]);
    }
  }

  public function gerar_comprovante()
  {
    try {
      $db = db_connect();

      // 1. Recebimento dos dados
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

      if (!$cobranca_id || !$aluno_id || !$responsavel_nome || !$aluno_nome || !$valor_str || !$forma_pagamento) {
        return $this->response->setJSON(["success" => false, "message" => "Preencha todos os campos obrigatórios."]);
      }

      $valor = floatval(str_replace(',', '.', str_replace('.', '', $valor_str)));

      // Formatação CPF e Número
      $numero_comprovante = 'COMP-' . date('Ymd') . '-' . str_pad($cobranca_id, 4, '0', STR_PAD_LEFT);
      $cpf_formatado = $responsavel_cpf;
      if (strlen($cpf_formatado) == 11) {
        $cpf_formatado = substr($cpf_formatado, 0, 3) . '.' . substr($cpf_formatado, 3, 3) . '.' . substr($cpf_formatado, 6, 3) . '-' . substr($cpf_formatado, 9, 2);
      }

      $db->transStart();

      // 2. Inserir Comprovante
      $dados_comprovante = [
        'numero_comprovante' => $numero_comprovante,
        'data_emissao' => $data_emissao ?: date('Y-m-d'),
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

      // 3. BAIXA AUTOMÁTICA DA COBRANÇA (Isso garante que saia do Financeiro Resumo)
      if ($cobranca_id) {
        $db->table('siamesa_cobrancas')->where('id', $cobranca_id)->update([
          'status' => 'Pago',
          'data_pagamento' => date('Y-m-d H:i:s')
        ]);
      }

      // 4. Gerar Arquivo HTML
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

      $html = view("Bombeiros\Views\comprovante_template", $view_data);
      $upload_path = WRITEPATH . 'uploads/comprovantes/';
      if (!is_dir($upload_path))
        @mkdir($upload_path, 0755, true);

      $filename = 'comprovante_' . $comprovante_id . '_' . time() . '.html';
      file_put_contents($upload_path . $filename, $html);

      $db->table('siamesa_comprovantes')->where('id', $comprovante_id)->update(['arquivo_path' => 'uploads/comprovantes/' . $filename]);

      $db->transComplete();

      if ($db->transStatus() === false) {
        return $this->response->setJSON(["success" => false, "message" => "Erro na transação."]);
      }

      // RETORNO JSON COM A URL DE DOWNLOAD
      return $this->response->setJSON([
        "success" => true,
        "message" => "Pagamento baixado e comprovante gerado!",
        "download_url" => get_uri("bombeiros/baixar_comprovante/" . $comprovante_id) // URL específica para forçar download
      ]);

    } catch (\Exception $e) {
      return $this->response->setJSON(["success" => false, "message" => "Erro: " . $e->getMessage()]);
    }
  }

  public function baixar_comprovante($comprovante_id)
  {
    try {
      $db = db_connect();
      $comprovante = $db->table('siamesa_comprovantes')->where('id', $comprovante_id)->where('deleted', 0)->get()->getRowArray();
      if (!$comprovante)
        return "Comprovante não encontrado.";

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

      $html = view("Bombeiros\Views\comprovante_template", $view_data);
      $filename = 'Comprovante_SIAMESA_' . $comprovante['numero_comprovante'] . '.html';

      $this->response->setHeader('Content-Type', 'text/html; charset=utf-8');
      $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
      $this->response->setBody($html);
      return $this->response;

    } catch (\Exception $e) {
      return "Erro: " . $e->getMessage();
    }
  }

  public function buscar_unidade()
  {
    try {
      $db = db_connect();
      $id = $this->request->getPost('id');
      if (!$id)
        return $this->response->setJSON(["success" => false, "message" => "ID não informado."]);
      $unidade = $db->table('siamesa_unidades')->where('id', $id)->where('deleted', 0)->get()->getRowArray();
      if (!$unidade)
        return $this->response->setJSON(["success" => false, "message" => "Unidade não encontrada."]);
      return $this->response->setJSON(["success" => true, "data" => $unidade]);
    } catch (\Exception $e) {
      return $this->response->setJSON(["success" => false, "message" => "Erro: " . $e->getMessage()]);
    }
  }

  public function salvar_unidade()
  {
    try {
      $db = db_connect();
      $id = $this->request->getPost('id');
      $nome_unidade = trim($this->request->getPost('nome_unidade'));
      $cidade = trim($this->request->getPost('cidade'));
      $endereco = trim($this->request->getPost('endereco')) ?: null;
      $status = $this->request->getPost('status') ?: 'Ativo';

      if (!$nome_unidade || !$cidade)
        return $this->response->setJSON(["success" => false, "message" => "Nome e cidade são obrigatórios."]);

      $dados = ['nome_unidade' => $nome_unidade, 'cidade' => $cidade, 'endereco' => $endereco, 'status' => $status];
      $db->transStart();
      if ($id) {
        $db->table('siamesa_unidades')->where('id', $id)->update($dados);
        $mensagem = "Unidade atualizada!";
      } else {
        $dados['deleted'] = 0;
        $db->table('siamesa_unidades')->insert($dados);
        $mensagem = "Unidade cadastrada!";
      }
      $db->transComplete();
      if ($db->transStatus() === false)
        return $this->response->setJSON(["success" => false, "message" => "Erro ao salvar."]);
      return $this->response->setJSON(["success" => true, "message" => $mensagem]);
    } catch (\Exception $e) {
      return $this->response->setJSON(["success" => false, "message" => "Erro: " . $e->getMessage()]);
    }
  }
  /**
   * Função auxiliar para extrair texto de DOCX
   */
  private function readDocx($filePath)
  {
    if (!file_exists($filePath))
      return "Arquivo não existe no servidor.";

    $zip = new \ZipArchive;

    // Tenta abrir o arquivo. Se falhar, geralmente é porque a extensão ZIP não está ativa no PHP
    if ($zip->open($filePath) === TRUE) {
      // No padrão DOCX, o texto fica em word/document.xml
      $index = $zip->locateName('word/document.xml');

      if ($index !== false) {
        $data = $zip->getFromIndex($index);
        $zip->close();

        // Limpa tags XML para sobrar apenas o texto
        $dom = new \DOMDocument;
        // Os flags abaixo evitam erros de parsing do XML e warnings nos logs
        $dom->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

        return strip_tags($dom->saveXML());
      }
      $zip->close();
      return "Erro: O arquivo DOCX não tem o formato padrão (document.xml não encontrado).";
    }

    return "Erro: Falha ao abrir o arquivo. Verifique se a extensão 'php-zip' está habilitada no seu PHP.ini.";
  }
  public function processar_arquivo_word()
  {
    $file = $this->request->getFile('arquivo'); // ou o nome do seu input file

    if ($file->isValid() && !$file->hasMoved()) {
      // Move para uma pasta temporária para poder ler
      $newName = $file->getRandomName();
      $file->move(WRITEPATH . 'uploads', $newName);

      $path = WRITEPATH . 'uploads/' . $newName;

      // USA A FUNÇÃO CRIADA ACIMA
      $textoDoWord = $this->readDocx($path);

      if ($textoDoWord === false || (is_string($textoDoWord) && strpos($textoDoWord, 'Erro:') === 0)) {
        // Remove o arquivo temporário em caso de erro
        if (file_exists($path)) {
          unlink($path);
        }
        $mensagemErro = ($textoDoWord !== false && is_string($textoDoWord)) ? $textoDoWord : 'Falha ao ler DOCX';
        return $this->response->setJSON(['success' => false, 'message' => $mensagemErro]);
      }

      // Agora $textoDoWord tem o conteúdo limpo.
      // Faça o que precisa fazer...

      // Remove o arquivo temporário
      if (file_exists($path)) {
        unlink($path);
      }

      return $this->response->setJSON(['success' => true, 'data' => $textoDoWord]);
    } else {
      return $this->response->setJSON(['success' => false, 'message' => 'Arquivo inválido']);
    }
  }

  public function deletar_unidade()
  {
    try {
      $db = db_connect();
      $id = $this->request->getPost('id');
      if (!$id)
        return $this->response->setJSON(["success" => false, "message" => "ID não informado."]);
      $tem_alunos = $db->table('siamesa_alunos')->where(['unidade_id' => $id, 'deleted' => 0])->countAllResults();
      if ($tem_alunos > 0)
        return $this->response->setJSON(["success" => false, "message" => "Existem alunos nesta unidade."]);
      $db->table('siamesa_unidades')->where('id', $id)->update(['deleted' => 1]);
      return $this->response->setJSON(["success" => true, "message" => "Unidade removida!"]);
    } catch (\Exception $e) {
      return $this->response->setJSON(["success" => false, "message" => "Erro: " . $e->getMessage()]);
    }
  }

  public function visualizar_comprovante($comprovante_id)
  {
    try {
      $db = db_connect();
      $comprovante = $db->table('siamesa_comprovantes')->where('id', $comprovante_id)->where('deleted', 0)->get()->getRowArray();
      if (!$comprovante)
        return "Comprovante não encontrado.";

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
      return view("Bombeiros\Views\comprovante_template", $view_data);
    } catch (\Exception $e) {
      return "Erro: " . $e->getMessage();
    }
  }
}