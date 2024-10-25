<?php

$conn = new mysqli('localhost', 'lisc6834_sean', 'seanbswisnushakira', 'lisc6834_eventdb');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>