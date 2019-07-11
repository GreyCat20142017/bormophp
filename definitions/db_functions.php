<?php
    /**
     * Функция принимает ассоциативный массив с параметрами подключения к БД (host, user, password, database)
     * Возвращает соединение или false
     * @param $config
     * @return mysqli
     */
    function get_connection($config) {
        $connection = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database']);
        if ($connection) {
            mysqli_set_charset($connection, "utf8");
        }
        return $connection;
    }

    /**
     * Функция принимает соединение, текст запроса и пользовательское сообщение для вывода в случае ошибки.
     * Возвращает либо данные, полученные из БД в виде массива, либо ассоциативный массив с описанием ошибки
     * @param        $connection
     * @param        $query
     * @param string $user_error_message
     * @param bool $single
     * @return array|null
     */
    function get_data_from_db(&$connection, $query, $user_error_message, $single = false) {
        $data = [[ERROR_KEY => $user_error_message]];
        if ($connection) {
            $result = mysqli_query($connection, $query);
            if ($result) {
                $data = $single ? mysqli_fetch_assoc($result) : mysqli_fetch_all($result, MYSQLI_ASSOC);
            } else {
                $data = [[ERROR_KEY => mysqli_error($connection)]];
            }
        }
        return $data;
    }

    /**
     * Функция устанавливает, имел ли место факт ошибки при получении данных, анализируя переданный по ссылке массив,
     * полученный функцией get_data_from_db
     * @param $data
     * @return bool
     */
    function was_error(&$data) {
        return isset($data[0]) && array_key_exists(ERROR_KEY, $data[0]);
    }

    /**
     * Функция для совместного использования с функцией was_error. Возвращает описание ошибки.
     * @param array $data
     * @return string
     */
    function get_error_description(&$data) {
        return isset($data[0]) ? get_assoc_element($data[0], ERROR_KEY) : 'Неизвестная ошибка...';
    }

    /**
     * Функция возвращает массив с данными урока, либо пустой массив
     * @param $connection
     * @param $course
     * @param $limit
     * @param $offset
     * @return array
     */
    function get_lesson_content($connection, $course, $limit, $offset) {
        $course = mysqli_real_escape_string($connection, $course);
        $sql = 'SELECT TRIM(english) AS english, TRIM(russian) AS russian FROM words WHERE trim(course) = "' . $course . '"' .
            ' LIMIT ' . $limit . ' OFFSET ' . $offset . ';';
        $data = get_data_from_db($connection, $sql, 'Невозможно получить данные', false);
        return (!$data || was_error($data)) ? [] : $data;
    }

    /**
     * Функция возвращает данные о количестве уроков в курсах
     * @param $connection
     * @param $course
     * @return array|null
     */
    function get_lessons_info($connection, $course) {
        $course = mysqli_real_escape_string($connection, $course);
        $sql = 'SELECT course AS name, CEIL(SUM(1)/20) AS lastlesson FROM words GROUP BY course;';
        $data = get_data_from_db($connection, $sql, 'Невозможно получить данные', false);
        return (!$data || was_error($data)) ? [] : $data;
    }

    /**
     * Возвращает массив с вариантами перевода по части строки или точному совпадению
     * @param $connection
     * @param $search_text
     * @return array|null
     */
    function get_search_result($connection, $search_text, $exact = 0) {
        $search_text = trim(mysqli_real_escape_string($connection, $search_text));
        $word = empty($exact) ? '%' . $search_text . '%' : $search_text;
        $first = mb_substr($search_text, 0, 1, 'UTF8');
        $need_russian = (preg_match("/^[A-Za-z]/", $first)) ? 1 : 0;

        $sql = 'SELECT DISTINCT
                   CASE WHEN ' . $need_russian . ' = 1 THEN  TRIM(russian) ELSE  trim(english) END AS translate,
                   CASE WHEN ' . $need_russian . ' = 1 THEN  TRIM(english) ELSE  trim(russian) END AS word,
                   CASE WHEN TRIM(english) = "' . $search_text . '" OR TRIM(russian) = "' . $search_text . '" THEN 0 ELSE 1 END AS exact_order
                FROM words WHERE english LIKE "' . $word . '" OR russian LIKE "' . $word . '"  
                ORDER BY exact_order, word, translate;';
        $data = get_data_from_db($connection, $sql, 'Невозможно получить данные', false);
        return (!$data || was_error($data)) ? [] : $data;
    }