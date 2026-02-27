<?php

function get_conn(){
    $host = "db";
    $port = 3306;
    $username = "winky";
    $password = "testpassword";
    $database = "school";

    $conn = mysqli_connect($host, $username, $password, $database, $port);

    if (!$conn) {
        die('Khong the ket noi database: ' . mysqli_connect_error());
    }

    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}