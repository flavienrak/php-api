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
      $id = $path[4];
      $deletedUser = mysqli_query($db_conn, "DELETE FROM user WHERE id='$id'");

      if ($deletedUser) {
        echo json_encode(["userDeleted" => true]);
        return;
      } else {
        echo json_encode(["Error" => "Cannot delete user: '$id'"]);
        return;
      }
    } else {
      # code...
      $destination = $_SERVER['DOCUMENT_ROOT'] . "/php-api" . "/";
      $allProducts = mysqli_query($db_conn, "SELECT * FROM product");

      if (mysqli_num_rows($allProducts) > 0) {
        while ($row = mysqli_fetch_array($allProducts)) {

          # code...
          $json_array["products"][] = array("id" => $row["id"], "name" => $row["name"], "file" => $row["file"],);
        }
        echo json_encode($json_array['products']);
        return;
      } else {
        echo json_encode(['productNotFound' => true]);
        return;
      }
    }

  case 'POST':

    if (isset($_FILES['file'])) {
      $name = $_POST['name'];
      $description = $_POST['description'];
      $file = time() . $_FILES['file']['name'];
      $file_temp = $_FILES['file']['tmp_name'];
      $destination = $_SERVER['DOCUMENT_ROOT'] . '/php-api/files/images' . "/" . $file;

      $result = mysqli_query($db_conn, "INSERT INTO product (name, description, file) VALUES('$name','$description', '$file')");

      if ($result) {
        move_uploaded_file($file_temp, $destination);
        echo json_encode(["productAdded" => true]);
        return;
      } else {
        echo json_encode(["error" => "Cannot add product"]);
        return;
      }
    } else {
      echo json_encode(["error" => "File required"]);
      return;
    }

  default:
    # code...
    break;
}
