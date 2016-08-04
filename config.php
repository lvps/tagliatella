<?php
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_NAME', 'tagliatella'); // database name
define('DB_HOST', 'mysql.local');
define('DB_PORT', '3306');
define('CONFERENCE_ID', 1);

// Time zone for the "datetime" fields
define('DB_TIMEZONE', 'UTC');

// Time zone in the XML file
define('OUTPUT_TIMEZONE', 'UTC');

// Where to save the output each time the script is run.
// Set to NULL to disable.
define('OUTPUT_FILE', 'schedule.xml');

// Send the XML file as the HTTP response.
// Set to false to send a "204 No Content" code.
define('OUTPUT_RESPONSE', true);