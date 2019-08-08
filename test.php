<?php

    require_once('init.php');

    $http_origin = $_SERVER['HTTP_ORIGIN'];

    $result = json_encode([status => !STATUS_OK, JSON_UNESCAPED_UNICODE]);

    $test_result = get_test_result($connection);
    $result = json_encode([
        'status' => STATUS_OK,
        'content' => $test_result
    ], JSON_UNESCAPED_UNICODE);

    if ($http_origin === LOCALHOST_URL || $http_origin === GITHUB_URL) {
        header("Access-Control-Allow-Origin: $http_origin");
    }
    header('Content-type: application/json; charset=UTF-8');

    echo $result;
