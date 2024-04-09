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
    $allEquipements = mysqli_query($db_conn, 'SELECT * FROM equipements');
    $equipements = [];

    while ($row = mysqli_fetch_assoc($allEquipements)) {
      $equipements[] = $row;
    }

    $allTypes = ["debit", "tauxErreur", "tempsReponse", "tempsConnexion"];

    // Tableau pour stocker les données regroupées
    $data = [];

    // Parcourir chaque equipement
    foreach ($equipements as $equipement) {
      $equipementId = $equipement['id'];
      $data[$equipementId] = [];

      // Parcourir chaque type de données
      foreach ($allTypes as $type) {
        $dataQuery = mysqli_query($db_conn, "SELECT * FROM data WHERE equipementId='$equipementId' AND type='$type' ORDER BY date ASC");

        // Si aucune donnée n'existe, insérer une valeur par défaut
        if (mysqli_num_rows($dataQuery) <= 0) {
          mysqli_query($db_conn, "INSERT INTO data (equipementId, type, value, date) VALUES ('$equipementId', '$type', '$equipement[$type]', '{$equipement['createdAt']}')");
          $dataQuery = mysqli_query($db_conn, "SELECT * FROM data WHERE equipementId='$equipementId' AND type='$type' ORDER BY date ASC");
        }

        // Tableau pour stocker les valeurs pour ce type
        $data[$equipementId][$type] = [];

        // Parcourir chaque valeur
        while ($dataRow = mysqli_fetch_assoc($dataQuery)) {
          $date = $dataRow['date'];
          $value = $dataRow['value'];

          // Ajout de la valeur au tableau
          $data[$equipementId][$type][$date] = $value;
        }
      }
    }

    $allNotifications = mysqli_query($db_conn, 'SELECT * FROM notifications');
    $notifications = [];

    while ($row = mysqli_fetch_assoc($allNotifications)) {
      $notifications[] = $row;
    }

    // Encodage du tableau des données regroupées en JSON et affichage de la réponse
    echo json_encode(["equipements" => $equipements, "data" => $data, "notifications" => $notifications]);
    return;

  default:
    # code...
    break;
}
