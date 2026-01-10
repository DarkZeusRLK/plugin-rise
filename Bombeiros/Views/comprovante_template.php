<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Pagamento - Projeto SIAMESA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
            color: #333;
            padding: 20px;
        }

        .comprovante-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 200px;
            height: auto;
            margin-bottom: 10px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #1a4d7a;
            margin: 10px 0;
        }

        .logo-subtitle {
            font-size: 12px;
            color: #666;
        }

        .titulo {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-label {
            font-weight: bold;
            width: 200px;
            min-width: 200px;
        }

        .info-value {
            flex: 1;
            border-bottom: 1px dotted #999;
            min-height: 20px;
            padding-bottom: 3px;
        }

        .checkbox-group {
            display: flex;
            gap: 20px;
            margin: 15px 0;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-item label {
            cursor: pointer;
        }

        .checkbox-item input[type="checkbox"]:checked+label {
            color: #c00;
            font-weight: bold;
        }

        .valor-section {
            background: #f9f9f9;
            padding: 15px;
            border: 2px solid #ddd;
            margin: 20px 0;
            text-align: center;
        }

        .valor-label {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .valor-value {
            font-size: 24px;
            font-weight: bold;
            color: #1a4d7a;
        }

        .forma-pagamento {
            margin: 20px 0;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
        }

        .footer-text {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .slogan {
            text-align: center;
            font-weight: bold;
            color: #1a4d7a;
            margin: 20px 0;
        }

        .contato {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 15px;
        }

        .contato a {
            color: #1a4d7a;
            text-decoration: none;
        }

        @media print {
            body {
                padding: 0;
            }

            .comprovante-container {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="comprovante-container">
        <div class="header">
            <div class="logo-text" style="background: #1a4d7a; color: white; padding: 15px; border-radius: 5px; display: inline-block;">
                SIAMESA
            </div>
            <div class="logo-subtitle">ACADEMIA DE TREINAMENTO MIRIM</div>
        </div>

        <div class="titulo">COMPROVANTE DE PAGAMENTO – PROJETO SIAMESA</div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">CNPJ:</span>
                <span class="info-value">63.357.041/0001-50</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tel/WhatsApp:</span>
                <span class="info-value">(11) 96399-8061</span>
            </div>
            <div class="info-row">
                <span class="info-label">E-mail:</span>
                <span class="info-value">tiago.frank@siamesa.com.br</span>
            </div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Nº do comprovante:</span>
                <span class="info-value"><?= $numero_comprovante ?? 'XXXX'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Data emissão:</span>
                <span class="info-value"><?= $data_emissao ?? 'XX/XX/XXXX'; ?></span>
            </div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Responsável:</span>
                <span class="info-value"><?= $responsavel_nome ?? 'XXXXXXXXXXXXXXXXXXXX'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">CPF:</span>
                <span class="info-value"><?= $responsavel_cpf ?? 'XX.XXX.XXX.XX-XX'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Aluno(a):</span>
                <span class="info-value"><?= $aluno_nome ?? 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'; ?></span>
            </div>
            <?php if (!empty($aluno_nome_adicional)): ?>
                <div class="info-row">
                    <span class="info-label">Aluno(a):</span>
                    <span class="info-value"><?= $aluno_nome_adicional; ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Mensalidade do Projeto SIAMESA, referente à:</span>
                <span class="info-value"></span>
            </div>
            <div class="checkbox-group">
                <?php
                $mensalidade_num = isset($mensalidade_numero) ? (int) $mensalidade_numero : 1;
                for ($i = 1; $i <= 6; $i++):
                    $checked = ($i == $mensalidade_num) ? 'checked' : '';
                    $style = ($i == $mensalidade_num) ? 'color: #c00; font-weight: bold;' : '';
                    ?>
                    <div class="checkbox-item">
                        <input type="checkbox" <?= $checked; ?> disabled>
                        <label style="<?= $style; ?>"><?= $i; ?>º Mensalidade</label>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="valor-section">
            <div class="valor-label">Valor (R$):</div>
            <div class="valor-value"><?= 'R$ ' . number_format($valor ?? 0, 2, ',', '.'); ?></div>
        </div>

        <div class="forma-pagamento">
            <div class="info-row">
                <span class="info-label">Forma de pagamento:</span>
                <span class="info-value"></span>
            </div>
            <div class="checkbox-group">
                <?php
                $formas = ['BOLETO', 'CRÉDITO', 'DÉBITO', 'PIX'];
                $forma_selecionada = $forma_pagamento ?? '';
                foreach ($formas as $forma):
                    $checked = (strtoupper($forma) == strtoupper($forma_selecionada)) ? 'checked' : '';
                    $style = (strtoupper($forma) == strtoupper($forma_selecionada)) ? 'color: #c00; font-weight: bold;' : '';
                    ?>
                    <div class="checkbox-item">
                        <input type="checkbox" <?= $checked; ?> disabled>
                        <label style="<?= $style; ?>"><?= $forma; ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Conferido por:</span>
                <span class="info-value"><?= $conferido_por ?? 'XXXXXXXXXXXXXXXXXXXXXXXX'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Data conferência:</span>
                <span class="info-value"><?= $data_conferencia ?? 'XXXXXXXXXXXXX'; ?></span>
            </div>
        </div>

        <div class="footer-text">
            Este comprovante confirma o recebimento do valor referente à mensalidade indicada. Guarde-o para eventuais comprovações junto à instituição.
        </div>
        <div class="slogan">
            PROJETO SIAMESA - Formação, Disciplina e Cidadania.
        </div>

        <div class="contato">
            <strong>www.siamesa.com.br</strong><br>
            <strong>@siamesaacademiamirim</strong>
        </div>
    </div>
</body>

</html>