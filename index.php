<?php 
error_reporting(E_ALL &~E_NOTICE &~E_USER_WARNING);

// inclusione del file di configurazione 
require_once './config.php';

// inclusione delle classe Router 
require_once PATH_ROOT.'/core/router.class.php';

// Oggetto Ruter 
$Router = new Router();

// inclusione del file di routing 
require_once PATH_ROOT.'/routing.php';

?>