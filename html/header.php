<?php
if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
    session_start();
}
// error_reporting(0);

require_once('/app/vendor/smarty/smarty/libs/Smarty.class.php');

function create_smarty_instance() {
    $s = new Smarty;
    if (!is_dir('/tmp/smarty/templates_c')) { @mkdir('/tmp/smarty/templates_c', 0777, true); }
    if (!is_dir('/tmp/smarty/cache')) { @mkdir('/tmp/smarty/cache', 0777, true); }
    $s->setCompileDir('/tmp/smarty/templates_c');
    $s->setCacheDir('/tmp/smarty/cache');
    // ensure custom plugins (paginate_*) are available on all instances
    @require_once __DIR__ . '/libs/SmartyPaginate.class.php';
    $s->addPluginsDir(__DIR__ . '/libs/plugins');
    return $s;
}

$smarty = create_smarty_instance();

// load simple pagination helper and register custom plugins dir
@require_once __DIR__ . '/libs/SmartyPaginate.class.php';
$smarty->addPluginsDir(__DIR__ . '/libs/plugins');

function my_filter($var, $type = "int")
{
    switch ($type) {
        case 'string':
            $var = isset($var) ? filter_var($var, FILTER_SANITIZE_ADD_SLASHES) : '';
            break;
        case 'url':
            $var = isset($var) ? filter_var($var, FILTER_SANITIZE_URL) : '';
            break;
        case 'email':
            $var = isset($var) ? filter_var($var, FILTER_SANITIZE_EMAIL) : '';
            break;
        case 'int':
        default:
            $var = isset($var) ? filter_var($var, FILTER_SANITIZE_NUMBER_INT) : '';
            break;
    }

    return $var;
}

