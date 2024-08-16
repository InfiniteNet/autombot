<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica se o usuário está logado e é um administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$output = '';
$error = '';

// URL do arquivo ZIP no GitHub Releases
$repo_url = 'https://github.com/InfiniteNet/autombot/archive/refs/heads/main.zip';

// Pasta temporária para o download
$temp_folder = '../files/';

// Caminho onde o código do projeto está
$project_path = 'autombot/';

try {
    // Inicializar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $repo_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Seguir redirecionamentos
    curl_setopt($ch, CURLOPT_FAILONERROR, true); // Retornar erro se o HTTP status não for 200
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');

    // Baixar o arquivo ZIP do repositório
    $zip_file = $temp_folder . 'update.zip';
    $zip_data = curl_exec($ch);

    if ($zip_data === false) {
        throw new Exception('Erro ao baixar o arquivo do GitHub: ' . curl_error($ch));
    }

    // Salvar o arquivo ZIP
    if (file_put_contents($zip_file, $zip_data) === false) {
        throw new Exception('Erro ao salvar o arquivo baixado.');
    }

    curl_close($ch);

    // Verifica se o arquivo ZIP foi baixado corretamente
    if (!file_exists($zip_file)) {
        throw new Exception('Erro ao salvar o arquivo baixado.');
    }

    // Extrair o arquivo ZIP
    $zip = new ZipArchive;
    if ($zip->open($zip_file) === TRUE) {
        $zip->extractTo($temp_folder);
        $zip->close();

        // Substituir os arquivos no projeto
        $extracted_folder = $temp_folder . 'autombot-V1.0/';
        if (!file_exists($extracted_folder)) {
            throw new Exception('Erro: Diretório extraído não encontrado.');
        }
        recurse_copy($extracted_folder, $project_path);

        $output = 'Atualização concluída com sucesso!';
    } else {
        throw new Exception('Erro ao extrair o arquivo ZIP.');
    }

    // Remover arquivos temporários
    unlink($zip_file);
    rrmdir($extracted_folder);
} catch (Exception $e) {
    $error = 'Erro ao atualizar: ' . $e->getMessage();
}

// Função para copiar recursivamente os arquivos
function recurse_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

// Função para remover uma pasta e seus arquivos
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object))
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        rmdir($dir);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Projeto</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .output, .error {
            background-color: #222;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            width: 90%;
            max-width: 800px;
            overflow: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        .error {
            background-color: #ff0000;
            color: #fff;
        }
        form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Atualizar Projeto</h1>
    <?php if ($error): ?>
        <div class="error">
            <p><?php echo $error; ?></p>
        </div>
    <?php elseif ($output): ?>
        <div class="output">
            <p><?php echo $output; ?></p>
        </div>
    <?php endif; ?>
</body>
</html>
