<?php

    require_once('init.php');

    $http_origin = $_SERVER['HTTP_ORIGIN'];

    $result = json_encode([status => !STATUS_OK, JSON_UNESCAPED_UNICODE]);

    if (!empty($_GET['course']) && !empty($_GET['lesson'])) {

        $course = trim(strip_tags($_GET['course']));
        $lesson = intval(strip_tags($_GET['lesson']));
        $offset = ($lesson - 1) * PAGINATION_STEP;
        $content = get_lesson_content($connection, $course, PAGINATION_STEP, $offset);

        $result = json_encode([
            'status' => STATUS_OK,
            'course' => $course,
            'lesson' => $lesson,
            'content' => $content
        ], JSON_UNESCAPED_UNICODE);
    }

    if (empty($_GET['course']) && empty($_GET['lesson'])) {

        $info = get_lessons_info($connection, $course);

        $result = json_encode([
            'status' => STATUS_OK,
            'info' => $info
        ], JSON_UNESCAPED_UNICODE);
    }


    if ($http_origin === 'http://localhost:3000' || $http_origin === 'https://greycat20142017.github.io/bormo') {
        header("Access-Control-Allow-Origin: $http_origin");
    }
    header('Content-type: application/json; charset=UTF-8');

    echo $result;
