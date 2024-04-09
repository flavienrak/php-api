<?php

include "algorithme.php";

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
    // type: "debit, tauxErreur, tempsReponse, tempsConnexion"
    $allEquipements = mysqli_query($db_conn, 'SELECT * FROM equipements');
    $allTypes = ["debit", "tauxErreur", "tempsReponse", "tempsConnexion"];
    $date = date('Y-m-d H:i:s');

    $equipmentData = []; // Array to store data for each equipment

    // Loop through each equipment
    while ($equipement = mysqli_fetch_assoc($allEquipements)) {
      $equipementId = $equipement['id']; // Get equipment ID
      $data = [];

      // Loop through each type
      foreach ($allTypes as $type) {
        // Count existing data points for this type
        $dataCountQuery = mysqli_query($db_conn, "SELECT COUNT(*) AS count FROM data WHERE equipementId='$equipementId' AND type='$type'");
        $dataCountRow = mysqli_fetch_assoc($dataCountQuery);
        $dataCount = (int) $dataCountRow['count'];

        // Check if data exceeds limit (11 in this case)
        if ($dataCount > 10) {
          // Delete oldest data point(s) to maintain limit
          $deleteCount = $dataCount - 10;
          $deleteQuery = mysqli_query($db_conn, "DELETE FROM data WHERE equipementId='$equipementId' AND type='$type' ORDER BY date ASC LIMIT $deleteCount");
        }

        // Get the latest value for this equipment and type (assuming value is the last column)
        $latestValueQuery = mysqli_query($db_conn, "SELECT value FROM data WHERE equipementId='$equipementId' AND type='$type' ORDER BY date DESC LIMIT 1");
        $latestValueRow = mysqli_fetch_assoc($latestValueQuery);
        $previousValue = isset($latestValueRow['value']) ? $latestValueRow['value'] : 100;  // Use 100 as default if no previous value

        // Generate random value based on previous value
        $value = 0;

        if ($type === 'debit') {
          $value = generateRandom($previousValue, 80, 75, 0.6);
          if ($value < 5) {
            $countQuery = mysqli_query($db_conn, "SELECT COUNT(*) AS count FROM notifications WHERE equipementId = '{$equipement['id']}' AND type = '$type'");
            $countRow = mysqli_fetch_assoc($countQuery);
            $totalCount = (int) $countRow['count'];

            if ($totalCount > 10) {
              // Supprimer la notification la plus ancienne
              mysqli_query($db_conn, "DELETE FROM notifications WHERE equipementId = '{$equipementId}' AND type = '$type' ORDER BY date ASC LIMIT 1");
            }

            mysqli_query($db_conn, "INSERT INTO notifications (equipementId, type, value, date) VALUES ('{$equipement['id']}', '$type', '$value', '$date')");
          }
        } else if ($type === 'tauxErreur') {
          $value = generateRandom($previousValue, 5, 5, 0.4);
          if ($value > 25) {
            $countQuery = mysqli_query($db_conn, "SELECT COUNT(*) AS count FROM notifications WHERE equipementId = '{$equipement['id']}' AND type = '$type'");
            $countRow = mysqli_fetch_assoc($countQuery);
            $totalCount = (int) $countRow['count'];

            if ($totalCount > 10) {
              // Supprimer la notification la plus ancienne
              mysqli_query($db_conn, "DELETE FROM notifications WHERE equipementId = '{$equipementId}' AND type = '$type' ORDER BY date ASC LIMIT 1");
            }

            mysqli_query($db_conn, "INSERT INTO notifications (equipementId, type, value, date) VALUES ('{$equipement['id']}', '$type', '$value', '$date')");
          }
        } else {
          $value = generateRandom($previousValue, 250, 150, 0.5);
          if ($value > 750) {
            $countQuery = mysqli_query($db_conn, "SELECT COUNT(*) AS count FROM notifications WHERE equipementId = '{$equipement['id']}' AND type = '$type'");
            $countRow = mysqli_fetch_assoc($countQuery);
            $totalCount = (int) $countRow['count'];

            if ($totalCount > 10) {
              // Supprimer la notification la plus ancienne
              mysqli_query($db_conn, "DELETE FROM notifications WHERE equipementId = '{$equipementId}' AND type = '$type' ORDER BY date ASC LIMIT 1");
            }

            mysqli_query($db_conn, "INSERT INTO notifications (equipementId, type, value, date) VALUES ('{$equipement['id']}', '$type', '$value', '$date')");
          }
        }

        $data[$type][$date] = $value;

        $result = mysqli_query(
          $db_conn,
          "INSERT INTO data (equipementId, value, type, date) VALUES('$equipementId', '$value', '$type', '$date')"
        );
      }

      // Add equipment data with generated values to the main array
      $equipmentData[$equipementId] = $data;
    }

    // Encode the equipment data array as JSON and echo the response
    // echo json_encode($equipmentData);

    $allEquipements = mysqli_query($db_conn, 'SELECT * FROM equipements');

    $equipements = [];
    while ($row = mysqli_fetch_assoc($allEquipements)) {
      $equipements[] = $row;
    }

    // Tableau pour stocker les données regroupées
    $data = [];

    // Parcourir chaque equipement
    foreach ($equipements as $equipement) {
      $equipementId = $equipement['id'];
      $data[$equipementId] = [];

      // Parcourir chaque type de données
      foreach ($allTypes as $type) {
        $dataQuery = mysqli_query($db_conn, "SELECT date, value FROM data WHERE equipementId='$equipementId' AND type='$type' ORDER BY date ASC");

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

    $countEquipementsQuery = mysqli_query($db_conn, "SELECT COUNT(*) FROM equipements");
    $countEquipementsRow = mysqli_fetch_row($countEquipementsQuery)[0];

    $limit = $countEquipementsRow * 8;
    $limitStr = "LIMIT " . $limit;

    mysqli_query($db_conn, "DELETE FROM notifications WHERE equipementId = '{$equipementId}' AND type = '$type' ORDER BY date ASC $limitStr");

    $allNotifications = mysqli_query($db_conn, 'SELECT * FROM notifications');
    $notifications = [];

    while ($row = mysqli_fetch_assoc($allNotifications)) {
      $notifications[] = $row;
    }

    // Encodage du tableau des données regroupées en JSON et affichage de la réponse
    echo json_encode(["data" => $data, "notifications" => $notifications]);
    break;

  default:
    # code...
    break;
}
