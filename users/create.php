<?php

error_reporting(E_ALL);

ini_set("display_errors", 1);
header("Access-Control-Allow-Origin:* ");
header("Access-Control-Allow-Headers:* ");
header("Access-Control-Allow-Methods:* ");

$db_conn = mysqli_connect("localhost", "root", "", "equipements");

if (!$db_conn) {
  die("Error: Could not connect" . mysqli_connect_error());
}

$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
  case 'POST':
    $userPost = json_decode(file_get_contents('php://input'));

    $nom = $userPost->nom;
    $prenom = $userPost->prenom;
    $userPassword = $userPost->userPassword;
    $email = $userPost->email;

    $password = password_hash($userPassword, PASSWORD_DEFAULT);
    $result = mysqli_query($db_conn, "INSERT INTO user (name, username, email) VALUES('$name', '$username', '$email')");

    if ($result) {
      echo json_encode(["newUser" => true]);
      return;
    } else {
      echo json_encode(["Error" => "Data not inserted"]);
      return;
    }

  default:
    # code...
    break;
}
