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
  case 'GET':
    $path = explode('/', $_SERVER['REQUEST_URI']);

    if (isset($path[4]) && is_numeric($path[4])) {
      $json_array = array();
      $id = $path[4];
      $getUserRow  = mysqli_query($db_conn, "SELECT * FROM user WHERE id='$id'");

      while ($user = mysqli_fetch_array($getUserRow)) {
        $json_array['user'] = array('id' => $user['id'], 'name' => $user['name'], 'username' => $user['username'], 'email' => $user['email'],);
      }
      echo json_encode($json_array['user']);

      return;
    };
    break;

  case 'PUT':
    $values = json_decode(file_get_contents('php://input'));

    $id = $values->id;
    $name = $values->name;
    $username = $values->username;
    $email = $values->email;

    $updatedUser = mysqli_query($db_conn, "UPDATE user SET name='$name', username='$username', email='$email'");

    if ($updatedUser) {
      echo json_encode(["userUpdated" => true]);
      return;
    } else {
      echo json_encode(["Error" => "Data not inserted"]);
      return;
    }

  case 'DELETE':
    $path = explode('/', $_SERVER['REQUEST_URI']);

    if (isset($path[4]) && is_numeric($path[4])) {
      $id = $path[4];
      $deletedUser = mysqli_query($db_conn, "DELETE FROM user WHERE id='$id'");

      if ($deletedUser) {
        echo json_encode(["userDeleted" => true]);
        return;
      } else {
        echo json_encode(["Error" => "Cannot delete user: '$id'"]);
        return;
      }
    }

  default:
    # code...
    break;
}
