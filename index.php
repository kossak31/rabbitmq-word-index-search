<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// ligar ao RabbitMQ
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// definir queue
$channel->queue_declare('file_queue', false, true, false, false);

// verficar se o form foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {



  // guardar o ficheiro no servidor
  $target_dir = 'uploads/';
  $target_file = $target_dir . basename($_FILES['file']['tmp_name'] . ".txt");
  move_uploaded_file($_FILES['file']['tmp_name'], $target_file);


  // array com o nome do ficheiro e o conteudo
  $file_data = [
    'name' => basename($_FILES['file']['name']),
    'tmp_name' => basename($_FILES['file']['tmp_name'] . ".txt")
  ];

  // Encode para JSON
  $json_data = json_encode($file_data);

  // criar a mensagem
  $msg = new AMQPMessage($json_data);

  // publicar a mensagem
  $channel->basic_publish($msg, '', 'file_queue');

  //mensagem de sucesso de envio de ficheiro
  echo "<div class='alert alert-info' role='alert'>ficheiro enviado a processar!</div>";
}

// fechar a ligação
$channel->close();
$connection->close();

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Indexação RabbitMQ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>

<body>
  <div class="container">


    <div class="mt-4 p-5 bg-secondary text-white rounded">
      <h1>Indexação</h1>
      <p>Serviço empresarial para indexação de palavras em ficheiros de texto</p>
    </div>


    <div class="offset-md-3 mt-5">
    <div class="">
      <form action="search.php" method="get">
        
          <div class="row align-items-center" style="padding-bottom: 20px;">
            <div class="col-auto">
              <label for="">palavra a pesquisar:</label>
            </div>
            <div class="col-auto">
              <input class="form-control" type="text" name="search">
            </div>

            <div class="col-auto">
              <button class="btn btn-primary" type="submit">Search</button>
            </div>
          </div>
      </form>

      <form method="post" enctype="multipart/form-data">
        <div class="row  align-items-center">
          <div class="col-auto">
            <label for="">adicionar ficheiro:</label>
          </div>
          <div class="col-auto">
            <input class="form-control" type="file" name="file">
          </div>
          <div class="col-auto">
            <button class="btn btn-primary" type="submit">Index</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  </div><!-- end container -->

</body>

</html>