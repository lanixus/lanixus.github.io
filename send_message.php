<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $prompt = $data['prompt'];

    $url = "http://178.236.243.195:8080/api/generate";
    $headers = [
        "Content-Type: application/json"
    ];
    $payload = json_encode([
        "model" => "llama3.2",
        "prompt" => $prompt
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    
    $response = curl_exec($ch);
    curl_close($ch);

    // Imprimir la respuesta cruda para diagnosticar
    // echo "Respuesta cruda: " . $response; // Descomentar para diagnóstico

    // Separar respuestas múltiples
    $responses = explode("\n", trim($response));
    $complete_response = "";

    foreach ($responses as $res) {
        $res = trim($res);
        if (!empty($res)) {
            $json_data = json_decode($res, true);
            $complete_response .= isset($json_data['response']) ? $json_data['response'] : '';
        }
    }

    // Enviar respuesta al cliente
    header('Content-Type: application/json');
    echo json_encode(['response' => $complete_response]);
}
?>
