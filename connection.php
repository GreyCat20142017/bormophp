<?php

    require_once('connection_config.php');
    require_once('db_functions.php');

    $connection = get_connection(CONNECTION_CONFIG);

    if (!$connection) {
        die('Невозможно подключиться к базе данных: ' . mysqli_connect_error());
    }
