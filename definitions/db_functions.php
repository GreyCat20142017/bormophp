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