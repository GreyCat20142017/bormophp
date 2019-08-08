<?php

    require_once('init.php');

    $http_origin = $_SERVER['HTTP_ORIGIN'];

    $result = json_encode([status => !STATUS_OK, JSON_UNESCAPED_UNICODE]);

    if (!empty($_GET['word'])) {

        $word = trim(strip_tags($_GET['word']));
        $exact = !empty($_GET['exact']) ? intval(strip_tags($_GET['exact'])) : 0;

        $content = get_search_result($connection, $word, $exact);

        $result = json_encode([
            'status' => STATUS_OK,
            'word' => $word,
            'exact' => $exact,
            'content' => $content
        ], JSON_UNESCAPED_UNICODE);
    }

    if ($http_origin === LOCALHOST_URL || $http_origin === GITHUB_URL) {
        header("Access-Control-Allow-Origin: $http_origin");
    }
    header('Content-type: application/json; charset=UTF-8');

    echo $result;
