<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $search_word = $_GET['search'];
    $results = array();


    $conn = mysqli_connect('127.0.0.1', 'root', '', 'rabbitmq');
    if (!$conn) {
        die("verificar ligacao: " . mysqli_connect_error());
    }

    // procurar palavra
    $sql = "SELECT * FROM words WHERE word LIKE '$search_word'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = array("word" => $search_word, "filename" => $row['filename'], "id" => $row['id']);
        }
    }

    // html
    if (!empty($results)) {
        echo '  <!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Indexação RabbitMQ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>

<body>
  <div class="container">
';
        echo "<table class='table'>";
        echo "<tr><th>palavra</th><th>ficheiro</th><th>ID</th></tr>";
        foreach ($results as $result) {
            echo "<tr>";
            echo "<td>" . $result['word'] . "</td>";
            echo "<td>" . $result['filename'] . "</td>";
            echo "<td>" . $result['id'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "sem resultados.";
    }

    mysqli_close($conn);
}
