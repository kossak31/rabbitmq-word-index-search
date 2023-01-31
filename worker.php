<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('file_queue', false, true, false, false);

echo "[x] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) {
    //$msg->body
    echo "[x] ficheiro recebido a processar\n";

    // ligar ao mysql
    $conn = mysqli_connect('127.0.0.1', 'root', '', 'rabbitmq');
    if ($conn->connect_errno) {
        echo "Verify MySQL connection: " . $conn->connect_error;
        exit();
    }

    // receber conteudo da mensagem
    $file_data = json_decode($msg->body, true);
    $file_name = $file_data['name'];
    $tmp_name = $file_data['tmp_name'];
    //$file_content = $file_data['content'];

    $target_dir = 'uploads/';
    $target_file = $target_dir . $tmp_name;
    $file_content = file_get_contents($target_file);


    //verificar se ficheiro ja existe na base de dados
    $sql = "SELECT * FROM words WHERE filename = '$file_name'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        echo "[x] ficheiro '$file_name' ja existe na base de dados!!!.\n";
        mysqli_close($conn);

        // remover o ficheiro do servidor
        unlink($target_file);
    } else {

        //regex separar palavra a palavra
        preg_match_all('/\w+/', $file_content, $word_list);

        // inserir palavra na base de dados com numero de linha
        foreach ($word_list[0] as $word) {
            $sql = "INSERT INTO words (word, filename) VALUES ('$word', '$file_name')";
            mysqli_query($conn, $sql);
        }

        mysqli_close($conn);
        echo "[x] processo terminado\n";

        // remover o ficheiro do servidor
        unlink($target_file);
    }
};


$channel->basic_consume('file_queue', '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
