<?php
$config = array();
/* Specify application configuration parameters here. */
$config['DEBUG'] = false;
$config['isadmin'] = false;

// Change the value of the following line (example: deco3801) to the username
// required to log in to the database.
$config['dbuser'] = 'deco3801';

// Change the value of the following line (example: hh2z2WG2q) to the
// corrsponding password for logging into the database.
$config['dbpass'] = 'hh2z2WG2q';

// Change the value of the following line (example: deco3801) to the name of
// the database where the PCR data is stored.
$config['dbname'] = 'deco3801';

// Change the value of the following line (example: oF0jxF1IGjzxYUl9w8B) to
// any random combination of 19 ASCII chars. This is your "Shared Secret".
$config['blti_psk'] = 'oF0jxF1IGjzxYUl9w8B';

$GLOBALS['config'] = $config;
?>