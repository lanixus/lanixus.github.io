<?php
session_start();

if (!isset($_SESSION['context'])) {
    $_SESSION['context'] = [];
}

function send_message($prompt) {
    $url = "http://178.236.243.195:8080/api/generate";
    $data = [
        "model" => "llama3.2",
        "prompt" => $prompt
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return "Error en la solicitud";
    }

    $responses = explode("\n", $result);
    $complete_response = "";

    foreach ($responses as $res) {
        $json_data = trim($res);
        if (!empty($json_data)) {
            $decoded = json_decode($json_data, true);
            if ($decoded && isset($decoded['response'])) {
                $complete_response .= $decoded['response'];
            }
        }
    }

    return $complete_response;
}

$response = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_input'])) {
        $user_input = $_POST['user_input'];
        $_SESSION['context'][] = ["role" => "user", "content" => $user_input];
        
        $prompt = implode("\n", array_map(function($msg) {
            return $msg['role'] . ": " . $msg['content'];
        }, $_SESSION['context']));

        $response = send_message($prompt);
        $_SESSION['context'][] = ["role" => "assistant", "content" => $response];
    } elseif (isset($_POST['clear_context'])) {
        $_SESSION['context'] = [];
        $response = "Contexto borrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat con IA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f0f0;
        }
        .chat-container {
            background-color: white;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .user {
            background-color: #e6f3ff;
            text-align: right;
        }
        .assistant {
            background-color: #f0f0f0;
        }
        form {
            margin-top: 20px;
        }
        input[type="text"] {
            width: 70%;
            padding: 10px;
            margin-right: 10px;
        }
        input[type="submit"], button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        input[type="submit"]:hover, button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <h1>Chat con IA</h1>
        <?php foreach ($_SESSION['context'] as $message): ?>
            <div class="message <?php echo $message['role']; ?>">
                <strong><?php echo ucfirst($message['role']); ?>:</strong> <?php echo htmlspecialchars($message['content']); ?>
            </div>
        <?php endforeach; ?>

        <?php if (!empty($response)): ?>
            <div class="message assistant">
                <strong>Assistant:</strong> <?php echo htmlspecialchars($response); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="text" name="user_input" placeholder="Escribe tu mensaje..." required>
            <input type="submit" value="Enviar">
        </form>

        <form method="post" action="">
            <button type="submit" name="clear_context">Borrar contexto</button>
        </form>
    </div>
</body>
</html>
