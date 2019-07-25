<?php

    require_once('init.php');

    $http_origin = $_SERVER['HTTP_ORIGIN'];

    $result = json_encode([status => !STATUS_OK, JSON_UNESCAPED_UNICODE]);

    if (!empty($_GET['lesson'])) {

        $lesson = intval(strip_tags($_GET['lesson']));
        $offset = ($lesson - 1) * PHRASES_PAGINATION_STEP;
        $step = empty($_GET['offline']) ? PHRASES_PAGINATION_STEP : PHRASES_PAGINATION_STEP * LESSONS_FOR_OFFLINE;
        $content = get_phrases_content($connection, $step, $offset);

        $result = json_encode([
            'status' => STATUS_OK,
            'course' => 'PHRASES',
            'lesson' => $lesson,
            'content' => $content
        ], JSON_UNESCAPED_UNICODE);
    }

    if (empty($_GET['lesson'])) {

        $info = get_phrases_info($connection);

        $result = json_encode([
            'status' => STATUS_OK,
            'info' => $info
        ], JSON_UNESCAPED_UNICODE);
    }


    if ($http_origin === LOCALHOST_URL || $http_origin === GITHUB_URL) {
        header("Access-Control-Allow-Origin: $http_origin");
    }
    header('Content-type: application/json; charset=UTF-8');

    echo $result;
