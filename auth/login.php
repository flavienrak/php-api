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

    $email = $userPost->email;
    $password = $userPost->password;

    $result = mysqli_query($db_conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($result) == 0) {
      echo json_encode(["userNotFound" => true]);
      return;
    }

    $row = mysqli_fetch_assoc($result);
    $hashedPassword = $row['password'];

    if (password_verify($password, $hashedPassword)) {
      unset($row['password']);
      echo json_encode(["user" => $row]);
      return;
    } else {
      echo json_encode(["passwordIncorrect" => true]);
      return;
    }

  default:
    # code...
    echo json_encode(["methodNotAllowed" => true]);
    break;
}
