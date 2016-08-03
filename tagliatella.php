<?php
// in case something goes wrong...
http_response_code(500);

require 'config.php';
define('DB_DSN', 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME);

$dbh = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false
]);

$xml = new DOMDocument('1.0', 'UTF-8');
$schedule = $xml->createElement('schedule');
$schedule = $xml->appendChild($schedule);
$conference = $xml->createElement('conference');
$conference = $schedule->appendChild($conference);

$result = $dbh->query('SELECT title, subtitle, venue, city, `start`, `end`, days, day_change, timeslot_duration FROM conferences WHERE id='.CONFERENCE_ID)->fetch(PDO::FETCH_ASSOC);

if($result === false) {
    throw new LogicException('Conference '.CONFERENCE_ID.' does not exist!');
} else {
    foreach($result as $tag => $content) {
        $xmltag = $xml->createElement($tag);
        if($content !== NULL) {
            $xmltag->textContent = $content;
        }
        $conference->appendChild($xmltag);
    }
}

// TODO: everything else

//header('Content-type: text/xml');
echo $xml->saveXML();