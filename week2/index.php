<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt18_week2', 'ddwt18','ddwt18');

/*


*/

/* Get Number of Series */
$nbr_series = count_series($db);

/* Get Number of Users */
$nbr_users = count_users($db);

/* Set 'cards' as default for right column*/
$right_column = use_template('cards');

/* Set default template navigation */
$template = Array(
    1 => Array(
        'name' => 'Home',
        'url' => '/ddwt18/week2/'
    ),
    2 => Array(
        'name' => 'Overview',
        'url' => '/ddwt18/week2/overview/'
    ),
    3 => Array(
        'name' => 'My Account',
        'url' => '/ddwt18/week2/myaccount/'
    ),
    4 => Array(
        'name' => 'Register',
        'url' => '/ddwt18/week2/register/'
    ));

/* Landing page */
if (new_route('/ddwt18/week2/', 'get')) {
    /* Page info */
    $page_title = 'Home';
    $breadcrumbs = get_breadcrumbs([
        'ddwt18' => na('/ddwt18/', False),
        'Week 2' => na('/ddwt18/week2/', False),
        'Home' => na('/ddwt18/week2/', True)
    ]);
    $navigation = get_navigation($template, $page_title);

    /* Page content */

    $page_subtitle = 'The online platform to list your favorite series';
    $page_content = 'On Series Overview you can list your favorite series. You can see the favorite series of all Series Overview users. By sharing your favorite series, you can get inspired by others and explore new series.';

    /* Choose Template */
    include use_template('main');
}

/* Overview page */
elseif (new_route('/ddwt18/week2/overview/', 'get')) {
    /* Page info */
    $page_title = 'Overview';
    $breadcrumbs = get_breadcrumbs([
        'ddwt18' => na('/ddwt18/', False),
        'Week 2' => na('/ddwt18/week2/', False),
        'Overview' => na('/ddwt18/week2/overview', True)
    ]);
    $navigation = get_navigation($template, $page_title);

    /* Page content */
    $page_subtitle = 'The overview of all series';
    $page_content = 'Here you find all series listed on Series Overview.';
    $left_content = get_serie_table($db, get_series($db));

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Choose Template */
    include use_template('main');
}

/* Single Serie */
elseif (new_route('/ddwt18/week2/serie/', 'get')) {
    /* Get series from db */
    $serie_id = $_GET['serie_id'];
    $serie_info = get_serieinfo($db, $serie_id);

    /* Page info */
    $page_title = $serie_info['name'];
    $breadcrumbs = get_breadcrumbs([
        'ddwt18' => na('/ddwt18/', False),
        'Week 2' => na('/ddwt18/week2/', False),
        'Overview' => na('/ddwt18/week2/overview/', False),
        $serie_info['name'] => na('/ddwt18/week2/serie/?serie_id='.$serie_id, True)
    ]);
    $navigation = get_navigation($template, $page_title);

    /* Page content */
    $page_subtitle = sprintf("Information about %s", $serie_info['name']);
    $page_content = $serie_info['abstract'];
    $nbr_seasons = $serie_info['seasons'];
    $creators = $serie_info['creator'];

    $added_by = get_user_name($db, $serie_info['user']);

    /* Choose Template */
    include use_template('serie');
}

/* Add serie GET */
elseif (new_route('/ddwt18/week2/add/', 'get')) {
    /* Page info */
    $page_title = 'Add Series';
    $breadcrumbs = get_breadcrumbs([
        'ddwt18' => na('/ddwt18/', False),
        'Week 2' => na('/ddwt18/week2/', False),
        'Add Series' => na('/ddwt18/week2/new/', True)
    ]);
    $navigation = get_navigation($template, $page_title);

    /* Page content */
    $page_subtitle = 'Add your favorite series';
    $page_content = 'Fill in the details of you favorite series.';
    $submit_btn = "Add Series";
    $form_action = '/ddwt18/week2/add/';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Choose Template */
    include use_template('new');
}

/* Add serie POST */
elseif (new_route('/ddwt18/week2/add/', 'post')) {
    /* Add serie to database */
    $feedback = add_serie($db, $_POST);
    /* Redirect to serie GET route */
    redirect(sprintf('/ddwt18/week2/add/?error_msg=%s',
        json_encode($feedback)));
}

/* Edit serie GET */
elseif (new_route('/ddwt18/week2/edit/', 'get')) {
    /* Get serie info from db */
    $serie_id = $_GET['serie_id'];
    $serie_info = get_serieinfo($db, $serie_id);

    /* Page info */
    $page_title = 'Edit Series';
    $breadcrumbs = get_breadcrumbs([
        'ddwt18' => na('/ddwt18/', False),
        'Week 2' => na('/ddwt18/week2/', False),
        sprintf("Edit Series %s", $serie_info['name']) => na('/ddwt18/week2/new/', True)
    ]);
    $navigation = get_navigation($template, $page_title);

    /* Page content */
    $page_subtitle = sprintf("Edit %s", $serie_info['name']);
    $page_content = 'Edit the series below.';
    $submit_btn = "Edit Series";
    $form_action = '/ddwt18/week2/edit/';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Choose Template */
    include use_template('new');
}

/* Edit serie POST */
elseif (new_route('/ddwt18/week2/edit/', 'post')) {
    /* Add serie to database */
    $feedback = add_serie($db, $_POST);
    /* Redirect to serie GET route */
    redirect(sprintf('/ddwt18/week2/edit/?error_msg=%s&serie_id=%s',
        json_encode($feedback),$_POST['serie_id']));
}

/* Remove serie */
elseif (new_route('/ddwt18/week2/remove/', 'post')) {
    /* Add serie to database */
    $feedback = add_serie($db, $_POST);
    /* Redirect to serie GET route */
    redirect(sprintf('/ddwt18/week2/overview/?error_msg=%s',
        json_encode($feedback)));
}

/* My account GET */
elseif (new_route('/ddwt18/week2/myaccount/', 'get')) {
    /* Page info */
    $page_title = 'My account';
    $breadcrumbs = get_breadcrumbs([
        'ddwt18' => na('/ddwt18/', False),
        'Week 2' => na('/ddwt18/week2/', False),
        'My account' => na('/ddwt18/week2/account/', True)
    ]);
    $navigation = get_navigation($template, $page_title);

    /* Page content */
    $page_subtitle = 'Your account';
    $page_content = 'See the information on your account.';
    $submit_btn = "Add Series";
    $form_action = '/ddwt18/week2/add/';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Choose Template */
    include use_template('account');
}

/* Register GET */
elseif (new_route('/ddwt18/week2/register/', 'get')) {
    /* Page info */
    $page_title = 'Register';
    $breadcrumbs = get_breadcrumbs([
        'ddwt18' => na('/ddwt18/', False),
        'Week 2' => na('/ddwt18/week2/', False),
        'Register' => na('/ddwt18/week2/register/', True)
    ]);
    $navigation = get_navigation($template, $page_title);

    /* Page content */
    $page_subtitle = 'Register an account';
    $page_content = 'Create an account to start listing your own favourite series.';
    $submit_btn = "Register";
    $form_action = '/ddwt18/week2/register/';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Choose Template */
    include use_template('register');
}

/* Register POST */
elseif (new_route('/ddwt18/week2/register/', 'post')) {

    /* Redirect to serie GET route */
    redirect(sprintf('/ddwt18/week2/register/'));
}

/* Login GET */
elseif (new_route('/ddwt18/week2/login/', 'get')) {
    /* Page info */
    $page_title = 'Login';
    $breadcrumbs = get_breadcrumbs([
        'ddwt18' => na('/ddwt18/', False),
        'Week 2' => na('/ddwt18/week2/', False),
        'Login' => na('/ddwt18/week2/login/', True)
    ]);
    $navigation = get_navigation($template, $page_title);

    /* Page content */
    $page_subtitle = 'login to your account';
    $page_content = 'Login to be able to add your own series.';
    $submit_btn = "Login";
    $form_action = '/ddwt18/week2/login/';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Choose Template */
    include use_template('login');
}

/* Login POST */
elseif (new_route('/ddwt18/week2/login/', 'post')) {

    /* Redirect to serie GET route
    redirect(sprintf('/ddwt18/week2/login/?error_msg=%s',
        json_encode($feedback)));
    */
}

/* Logout */
elseif (new_route('/ddwt18/week2/login/', 'get')) {

}

else {
    http_response_code(404);
}