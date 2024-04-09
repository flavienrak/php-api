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
  case 'POST':
    $userPost = json_decode(file_get_contents('php://input'));

    $userId = $userPost->userId;
    $categorie = $userPost->categorie;
    $nom = $userPost->nom;
    $adresseIP = $userPost->adresseIP;
    $dateInstallation = $userPost->dateInstallation;
    $description = $userPost->description;
    $sysExploitation = $userPost->sysExploitation;
    $capRAM = $userPost->capRAM;
    $capStockage = $userPost->capStockage;
    $passerelle = $userPost->passerelle;
    $protocoleRoutage = $userPost->protocoleRoutage;
    $nbPorts = $userPost->nbPorts;
    $typePorts = $userPost->typePorts;
    $qualiteService = $userPost->qualiteService;
    $debit = $userPost->debit;
    $tempsConnexion = $userPost->tempsConnexion;
    $tempsReponse = $userPost->tempsReponse;
    $tauxErreur = $userPost->tauxErreur;

    $createdAt = date('Y-m-d H:i:s');
    $updatedAt = date('Y-m-d H:i:s');

    $result = mysqli_query(
      $db_conn,
      "INSERT INTO equipements (userId, categorie, nom, adresseIP, dateInstallation, description, sysExploitation, capRAM, capStockage, passerelle, protocoleRoutage, nbPorts, typePorts, qualiteService, debit, tempsConnexion, tempsReponse, tauxErreur, createdAt, updatedAt) VALUES('$userId', '$categorie', '$nom', '$adresseIP', '$dateInstallation', '$description', '$sysExploitation', '$capRAM', '$capStockage', '$passerelle', '$protocoleRoutage', '$nbPorts', '$typePorts', '$qualiteService', '$debit', '$tempsConnexion', '$tempsReponse', '$tauxErreur', '$createdAt', '$updatedAt')"
    );

    $allTypes = ["debit", "tauxErreur", "tempsReponse", "tempsConnexion"];


    if ($result) {
      $equipement_id = mysqli_insert_id($db_conn);


      $select_query = "SELECT * FROM equipements WHERE id='$equipement_id'";
      $select_result = mysqli_query($db_conn, $select_query);

      if ($select_result && mysqli_num_rows($select_result) > 0) {
        $equipement_data = mysqli_fetch_assoc($select_result);

        foreach ($allTypes as $type) {
          mysqli_query($db_conn, "INSERT INTO data (equipementId, type, value, date) VALUES ('{$equipement_data['id']}', '$type', '$equipement_data[$type]', '{$equipement_data['createdAt']}')");
        }

        $select_query = "SELECT * FROM equipements WHERE id='$equipement_id'";
        $select_result = mysqli_query($db_conn, $select_query);

        echo $json_encode($equipement_data);
        return;
      } else {
        echo json_encode(["NotFound" => "Data not found"]);
        return;
      }
    } else {
      echo json_encode(["Error" => "Data not inserted"]);
      return;
    }
  default:
    # code...
    break;
}
