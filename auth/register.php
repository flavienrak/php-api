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
    $prenom = $userPost->prenom;
    $email = $userPost->email;
    $password = $userPost->password;

    if (strlen($nom) < 3) {
      echo json_encode(["nomError" => true]);
      return;
    } else if (strlen($prenom) < 3) {
      echo json_encode(["prenomError" => true]);
      return;
    } else   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      echo json_encode(["emailError" => true]);
      return;
    }

    $result = mysqli_query($db_conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($result) > 0) {
      echo json_encode(["emailError" => true]);
      return;
    }

    if (strlen($password) < 6) {
      echo json_encode(["passwordError" => true]);
      return;
    }

    $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (nom, prenom, email, password) VALUES ('$nom', '$prenom', '$email', '$passwordHashed')";
    $result = mysqli_query($db_conn, $query);

    if ($result) {
      $newUserId = mysqli_insert_id($db_conn);
      $newUserResult = mysqli_query($db_conn, "SELECT * FROM users WHERE id=$newUserId");
      $newUserRow = mysqli_fetch_assoc($newUserResult);
      unset($newUserRow['password']);
      echo json_encode(["user" => $newUserRow]);

      return;
    } else {
      echo json_encode(["registerError" => true]);
      return;
    }

  default:
    # code...
    echo json_encode(["methodNotAllowed" => true]);
    break;
}
