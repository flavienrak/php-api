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

    $nom = $userPost->nom;
    $adresseIP = $userPost->adresseIP;
    $marque = $userPost->marque;
    $dateInstallation = $userPost->dateInstallation;
    $description = $userPost->description;
    $sysExploitation = $userPost->sysExploitation;
    $ram = $userPost->ram;
    $stockage = $userPost->stockage;
    $debit = $userPost->debit;
    $tempsConnexion = $userPost->tempsConnexion;
    $tempsReponse = $userPost->tempsReponse;
    $tauxErreur = $userPost->tauxErreur;
    $createdAt = $userPost->createdAt;
    $updatedAt = $userPost->updatedAt;

    $result = mysqli_query(
      $db_conn,
      "INSERT INTO equipements (nom, adresseIP, marque, dateInstallation, description, sysExploitation, ram, stockage, debit, tempsConnexion, tempsReponse, tauxErreur, createdAt, updatedAt) VALUES('$nom', '$adresseIP', '$marque', '$dateInstallation', '$description', '$sysExploitation', '$ram', '$stockage', '$debit', '$tempsConnexion', '$tempsReponse', '$tauxErreur', '$createdAt', '$updatedAt')"
    );

    if ($result) {
      // Récupérer l'ID de l'équipement nouvellement créé
      $equipement_id = mysqli_insert_id($db_conn);

      // Sélectionner l'équipement nouvellement créé à partir de la base de données
      $select_query = "SELECT * FROM equipements WHERE id = $equipement_id";
      $select_result = mysqli_query($db_conn, $select_query);

      if ($select_result && mysqli_num_rows($select_result) > 0) {
        // Récupérer les données de l'équipement sous forme de tableau associatif
        $equipement_data = mysqli_fetch_assoc($select_result);

        // Convertir les données de l'équipement en format JSON
        $equipement_json = json_encode($equipement_data);

        // Retourner les données JSON
        echo $equipement_json;
      } else {
        echo "Erreur lors de la récupération de l'équipement.";
      }
    } else {
      echo json_encode(["Error" => "Data not inserted"]);
      return;
    }

  default:
    # code...
    break;
}
