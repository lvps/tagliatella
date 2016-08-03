<?php
// in case something goes wrong...
http_response_code(500);

$UTC = new DateTimeZone('UTC');
require 'config.php';
define('DB_DSN', 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME);

$dbh = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    //PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$xml = new DOMDocument('1.0', 'UTF-8');
$schedule = $xml->createElement('schedule');
$schedule = $xml->appendChild($schedule);
$conference = $xml->createElement('conference');
$conference = $schedule->appendChild($conference);

$result = $dbh->query('SELECT title, subtitle, venue, city, `start`, `end`, days, day_change, timeslot_duration FROM conferences WHERE id='.CONFERENCE_ID);

if($result->rowCount() === 0) {
    throw new LogicException('Conference '.CONFERENCE_ID.' does not exist!');
} else {
    $result = $result->fetch();
    foreach($result as $tag => $content) {
        $xmltag = $xml->createElement($tag);
        if($content !== NULL) {
            $xmltag->textContent = $content;
        }
        $conference->appendChild($xmltag);
    }
}

$events = $dbh->query('SELECT `events`.id, `events`.title, `events`.subtitle, `events`.slug, `events`.abstract, `events`.description, `events`.language,
rooms.name AS room,
`events`.room AS room_id,
event_types.name AS type,
tracks.name AS track,
`events`.start, `events`.`end`
FROM `events`
LEFT JOIN rooms ON events.room=rooms.id
LEFT JOIN tracks ON events.track=tracks.id
LEFT JOIN event_types ON events.type=event_types.id
WHERE `events`.conference_id='.CONFERENCE_ID.'
ORDER BY events.start, rooms.id');

$events_by_date_then_room = [];
foreach($events as $event) {
    $event['start'] = new DateTime($event['start'], $UTC);
    $event['end'] = new DateTime($event['end'], $UTC);
    $event['duration'] = $event['start']->diff($event['end']);

    $day = $event['start']->format('Y-m-d');
    if(!isset($events_by_date_then_room[$day])) {
        $events_by_date_then_room[$day] = [];
    }

    if(!isset($events_by_date_then_room[$day][$event['room']])) {
        $events_by_date_then_room[$day][$event['room']] = [];
    }

    $events_by_date_then_room[$day][$event['room']][] = $event;
}

$day_index = 1;
foreach($events_by_date_then_room as $day_date => $rooms) {
    $dayxml = $xml->createElement('day');
    $dayxml->setAttribute('index', $day_index);
    $dayxml->setAttribute('date', $day_date);
    $dayxml = $schedule->appendChild($dayxml);

    foreach($rooms as $room_name => $events) {
        $roomxml = $xml->createElement('room');
        $roomxml->setAttribute('name', $room_name);
        $roomxml = $dayxml->appendChild($roomxml);

        foreach($events as $event) {
            $eventxml = $xml->createElement('event');
            $eventxml->setAttribute('id', $event['id']);
            $eventxml = $roomxml->appendChild($eventxml);
        }
    }

    $day_index++;
}

// TODO: everything else

//header('Content-type: text/xml');
echo $xml->saveXML();