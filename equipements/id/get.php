<?php

error_reporting(E_ALL);

ini_set("display_errors", 1);
header("Access-Control-Allow-Origin:* ");
header("Access-Control-Allow-Headers:* ");
header("Access-Control-Allow-Methods:* ");

$db_conn = mysqli_connect("localhost", "root", "", "projet-php");

if (!$db_conn) {
  die("Error: Could not connect" . mysqli_connect_error());
}

$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
  case 'GET':
    $path = explode('/', $_SERVER['REQUEST_URI']);
    $equipementId = $_GET['id'];

    if (!is_numeric($equipementId) || $equipementId <= 0) {
      http_response_code(400);
      echo json_encode(['invalidID' => true]);
      exit;
    }

    $equipmentQuery = mysqli_query($db_conn, "SELECT * FROM equipements WHERE id=$equipementId");
    $equipement = mysqli_fetch_assoc($equipmentQuery);

    $dataQuery = mysqli_query($db_conn, "SELECT * FROM data WHERE equipementId=$equipementId");

    $data = [];

    if ($equipement) {
      if (!$rowData = mysqli_fetch_assoc($dataQuery)) {
        $allTypes = ["debit", "tauxErreur", "tempsReponse", "tempsConnexion"];
        foreach ($allTypes as $type) {
          mysqli_query($db_conn, "INSERT INTO data (equipementId, type, value, date) VALUES ('{$equipement['id']}', '$type', '$equipement[$type]', '{$equipement['createdAt']}')");
        }
      }

      while ($row = mysqli_fetch_assoc($dataQuery)) {
        $data[$row['type']][$row['date']] = $row['value'];
      }

      $response = [
        'equipement' => $equipement,
        'data' => $data
      ];
      echo json_encode($response);
      return;
    } else {
      http_response_code(404);
      echo json_encode(['equipementNotFound' => true]);
      return;
    }


  default:
    # code...
    break;
}
