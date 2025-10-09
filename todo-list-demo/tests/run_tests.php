<?php

/**
 * Teste manual da API de Notas
 * --------------------------------------------------
 * Este script autentica e cria uma nota via HTTP.
 * Deve ser executado na raiz do projeto:
 * 
 *   php tests/manual_note_test.php
 */

$baseUrl = 'http://127.0.0.1:8000/api';

// Dados do usuário (ajuste conforme necessário)
$email = 'anthonyoriebir@gmail.com';
$password = '123456';

// 1️⃣ LOGIN
echo "Efetuando login...\n";
$login = httpPost("$baseUrl/login", [
    'email' => $email,
    'password' => $password
]);

if (empty($login['data']['token'])) {
    echo "Falha no login:\n";
    print_r($login);
    exit(1);
}

$token = $login['data']['token'];
echo "Login bem-sucedido. Token obtido: " . substr($token, 0, 20) . "...\n\n";

echo "Enviando nova nota...\n";


$note = httpPost("$baseUrl/notes", [
    'title'   => 'Teste via script manual',
    'content' => 'Esta nota foi criada manualmente via PHP.',
], [
    "Authorization: Bearer $token",
    "Content-Type: application/json",
]);

echo "\nResposta da API:\n";
print_r($note);

/**
 * ---------------------------------------------------
 * Funções auxiliares
 * ---------------------------------------------------
 */
function httpPost(string $url, array $data, array $headers = []): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers ?: ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

 

    echo "[$status] $url\n";
    if ($err) {
        echo "Erro cURL: $err\n";
    }

    // 🧹 Remove ruídos antes do JSON (warnings, quebras, etc)
    $clean = trim(preg_replace('/^[^\{\[]+/', '', $response));

    // Tenta decodificar o JSON
    $decoded = json_decode($clean, true);
    var_dump($decoded); 
    // ⚠️ Se falhou o parse, retorna o conteúdo bruto com status
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "⚠️ Resposta não é JSON válida ou contém ruído extra.\n";
        return [
            'status'       => $status,
            'data'         => null,
            'raw_response' => $response,
            'error'        => json_last_error_msg(),
        ];
    }

    // ✅ Retorna estrutura padronizada
    return [
        'status'       => $status,
        'data'         => $decoded,
        'raw_response' => $response,
    ];
}