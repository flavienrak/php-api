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
    $userId = $_GET['id'];

    if (!is_numeric($userId) || $userId <= 0) {
      http_response_code(400);
      echo json_encode(['invalidID' => true]);
      exit;
    }

    $id = (int) $userId;

    $nom = $userPost->nom;
    $adresseIP = $userPost->adresseIP;
    $description = $userPost->description;
    $sysExploitation = $userPost->sysExploitation;
    $capRAM = $userPost->capRAM;
    $capStockage = $userPost->capStockage;
    $debit = $userPost->debit;
    $tempsConnexion = $userPost->tempsConnexion;
    $tempsReponse = $userPost->tempsReponse;
    $tauxErreur = $userPost->tauxErreur;
    $updatedAt = date('Y-m-d H:i:s');


    $result = mysqli_query(
      $db_conn,
      "UPDATE equipements SET 
        nom='$nom', 
        adresseIP='$adresseIP', 
        description='$description', 
        sysExploitation='$sysExploitation', 
        capRAM='$capRAM', 
        capStockage='$capStockage', 
        debit='$debit', 
        tempsConnexion='$tempsConnexion', 
        tempsReponse='$tempsReponse', 
        tauxErreur='$tauxErreur', 
        updatedAt='$updatedAt' 
        WHERE id=$id"
    );

    if ($result) {
      $updatedEquipement = mysqli_query($db_conn, "SELECT * FROM equipements WHERE id=$id");
      $equipement = mysqli_fetch_assoc($updatedEquipement);

      echo json_encode($equipement);
    } else {
      http_response_code(500);
      echo json_encode(['updateError' => true]);
    }


  default:
    # code...
    break;
}
