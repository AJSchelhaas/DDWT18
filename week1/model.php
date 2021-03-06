<?php
/**
 * Model
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Enable error reporting */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Check if the route exist
 * @param string $route_uri URI to be matched
 * @param string $request_type request method
 * @return bool
 *
 */
function new_route($route_uri, $request_type){
    $route_uri_expl = array_filter(explode('/', $route_uri));
    $current_path_expl = array_filter(explode('/',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    if ($route_uri_expl == $current_path_expl && $_SERVER['REQUEST_METHOD'] == strtoupper($request_type)) {
        return True;
    }
}

/**
 * Creates a new navigation array item using url and active status
 * @param string $url The url of the navigation item
 * @param bool $active Set the navigation item to active or inactive
 * @return array
 */
function na($url, $active){
    return [$url, $active];
}

/**
 * Creates filename to the template
 * @param string $template filename of the template without extension
 * @return string
 */
function use_template($template){
    $template_doc = sprintf("views/%s.php", $template);
    return $template_doc;
}

/**
 * Creates breadcrumb HTML code using given array
 * @param array $breadcrumbs Array with as Key the page name and as Value the corresponding url
 * @return string html code that represents the breadcrumbs
 */
function get_breadcrumbs($breadcrumbs) {
    $breadcrumbs_exp = '<nav aria-label="breadcrumb">';
    $breadcrumbs_exp .= '<ol class="breadcrumb">';
    foreach ($breadcrumbs as $name => $info) {
        if ($info[1]){
            $breadcrumbs_exp .= '<li class="breadcrumb-item active" aria-current="page">'.$name.'</li>';
        }else{
            $breadcrumbs_exp .= '<li class="breadcrumb-item"><a href="'.$info[0].'">'.$name.'</a></li>';
        }
    }
    $breadcrumbs_exp .= '</ol>';
    $breadcrumbs_exp .= '</nav>';
    return $breadcrumbs_exp;
}

/**
 * Creates navigation HTML code using given array
 * @param array $navigation Array with as Key the page name and as Value the corresponding url
 * @return string html code that represents the navigation
 */
function get_navigation($navigation){
    $navigation_exp = '<nav class="navbar navbar-expand-lg navbar-light bg-light">';
    $navigation_exp .= '<a class="navbar-brand">Serious Series</a>';
    $navigation_exp .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
    $navigation_exp .= '<span class="navbar-toggler-icon"></span>';
    $navigation_exp .= '</button>';
    $navigation_exp .= '<div class="collapse navbar-collapse" id="navbarSupportedContent">';
    $navigation_exp .= '<ul class="navbar-nav mr-auto">';
    foreach ($navigation as $name => $info) {
        if ($info[1]){
            $navigation_exp .= '<li class="nav-item active">';
            $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';
        }else{
            $navigation_exp .= '<li class="nav-item">';
            $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';
        }

        $navigation_exp .= '</li>';
    }
    $navigation_exp .= '</ul>';
    $navigation_exp .= '</div>';
    $navigation_exp .= '</nav>';
    return $navigation_exp;
}

/**
 * Pritty Print Array
 * @param $input
 */
function p_print($input){
    echo '<pre>';
    print_r($input);
    echo '</pre>';
}

/**
 * Creats HTML alert code with information about the success or failure
 * @param bool $type True if success, False if failure
 * @param string $message Error/Success message
 * @return string
 */
function get_error($feedback){
    $error_exp = '
        <div class="alert alert-'.$feedback['type'].'" role="alert">
            '.$feedback['message'].'
        </div>';
    return $error_exp;
}

/**
 * Connects to database
 * @param string host
 * @param string database
 * @param string username
 * @param string password
 * @return pdo
 */
function connect_db($host, $db, $user, $pass){
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        echo sprintf("Failed to connect. %s",$e->getMessage());
    }
    return $pdo;
}

/**
 * Returns the amount of series in the database
 * @param PDO
 * @return array series_count
 */
function count_series($pdo) {
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series_count = $stmt->rowCount();
    return $series_count;
}

/**
 * Returns array with series information without special characters
 * @param pdo
 * @return array series_exp
 */
function get_series($pdo){
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series = $stmt->fetchAll();
    $series_exp = Array();

    /* Create array with htmlspecialchars */
    foreach ($series as $key => $value) {
        foreach ($value as $user_key => $user_input) {
            $series_exp[$key][$user_key] = htmlspecialchars($user_input);
        }
    }

    return $series_exp;
}

/**
 * Returns string with series information in HTML format
 * @param array series_exp
 * @return string series_table
 */
function get_series_table($series_exp) {
    /* Adding the title */
    $series_table = '
    <table class="table table-hover">
        <thead>
        <tr>
            <th scope="col">Series</th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>';

    /* creates table for each series */
    foreach ($series_exp as $key => $value){
        $series_table .= '
        <tr>
            <th scope="row">'.$value['name'].'</th>
            <td><a href="/ddwt18/week1/serie/?series_id='.$value['id'].'" role="button" class="btn btn-primary">More info</a></td>
        </tr>';
    }
    $series_table .= '
        </tbody>
    </table>';

    return $series_table;
}

/**
 * Returns an associative array with for the series id provided
 * @param pdo
 * @param array series_id
 * @return string series_array_key
 */
function get_series_info($pdo, $series_id) {
    /* Retrieves series information from database */
    $series_id_string = strval($series_id);
    $stmt = $pdo->prepare('SELECT * FROM series WHERE id = '.$series_id_string);
    $stmt->execute();
    $series = $stmt->fetchAll();
    $series_array_key = $series[0];

    /* Create array with htmlspecialchars */
    foreach ($series_array_key as $key => $value) {
        $series_array_key[$key] = htmlspecialchars($value);
    }

    /* Returns associative array */
    return $series_array_key;
}

/**
 * Adds a series to the database
 * @param pdo
 * @param array series_info
 * @return array feedback
 */
function add_series($pdo, $serie_info) {
    /* Check data type */
    if (!is_numeric($serie_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }

    /* Check if all fields are set */
    if (
        empty($serie_info['Name']) or
        empty($serie_info['Creator']) or
        empty($serie_info['Seasons']) or
        empty($serie_info['Abstract'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    /* Check if serie already exists */
    $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
    $stmt->execute([$serie_info['Name']]);
    $serie = $stmt->rowCount();
    if ($serie) {
        return [
            'type' => 'danger',
            'message' => 'This series was already added.'
        ];
    }

    /* get next primary id */
    $stmt = $pdo->prepare('SELECT MAX(id) FROM series');
    $stmt->execute();
    $max_array_id = $stmt->fetchAll();
    $primary_key = intval($max_array_id[0]['MAX(id)']) + 1;

    /* Add Serie */
    $stmt = $pdo->prepare('INSERT INTO series (name, creator, seasons, abstract) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $serie_info['Name'],
        $serie_info['Creator'],
        $serie_info['Seasons'],
        $serie_info['Abstract']
    ]);
    $inserted = $stmt->rowCount();
    if ($inserted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' added to Serious Series.", $serie_info['Name'])
        ];
    }
    else {
        return [
            'type' => 'danger',
            'message' => 'There was an error. The series was not added. Try it again.'
        ];
    }
}

/**
 * Updates a series in the database if the name already exists
 * @param pdo
 * @param array post information
 * @return array feedback
 */
function update_series($pdo, $post) {
    $serie_info = $post;
    /* Check data type */
    if (!is_numeric($serie_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. You should enter a number in the field Seasons.'
        ];
    }

    /* Check if all fields are set */
    if (
        empty($serie_info['Name']) or
        empty($serie_info['Creator']) or
        empty($serie_info['Seasons']) or
        empty($serie_info['Abstract'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    }

    /* Create variables for update */
    $series_name = $serie_info['Name'];
    $series_creator = $serie_info['Creator'];
    $series_seasons = $serie_info['Seasons'];
    $series_abstract = $serie_info['Abstract'];
    $series_id = $serie_info['SeriesId'];

    $series_current_name = get_series_info($pdo, $series_id)['name'];

    /* Check whether name is already in the database */
    $stmt = $pdo->prepare('SELECT id, name FROM series');
    $stmt->execute();
    $series = $stmt->fetchAll();

    $name_exists = false;
    foreach ($series as $key => $value) {
        if ($value['name'] != $series_current_name and $value['name'] == $series_name) {
            $name_exists = true;
        }
    }

    if ($name_exists) {
        return [
            'type' => 'danger',
            'message' => sprintf("Series '%s' can not be updated because that name already exists.", $serie_info['Name'])
        ];
    }

    /* Update serie */
    $stmt = $pdo->prepare('UPDATE series SET name = :name, creator = :creator, seasons = :seasons, abstract = :abstract
    WHERE id = :id');
    $stmt-> bindParam(':name', $series_name);
    $stmt-> bindParam(':creator', $series_creator);
    $stmt-> bindParam(':seasons', $series_seasons);
    $stmt-> bindParam(':abstract', $series_abstract);
    $stmt-> bindParam(':id', $series_id);
    $stmt->execute();

    return [
        'type' => 'success',
        'message' => sprintf("Series '%s' has been updated.", $serie_info['Name'])
    ];
}

/**
 * Removes a series from the database
 * @param pdo
 * @param string series_id
 * @return array feedback
 */
function remove_serie($pdo, $serie_id) {
    /* Get series info */
    $serie_info = get_series_info($pdo, $serie_id);
    /* Delete Serie */
    $stmt = $pdo->prepare("DELETE FROM series WHERE id = ?");
    $stmt->execute([$serie_id]);
    $deleted = $stmt->rowCount();
    if ($deleted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' was removed!", $serie_info['name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'An error occurred. The series was not removed.'
        ];
    }
}