<?php
// in case something goes wrong...
http_response_code(500);

require 'config.php';
define('DB_DSN', 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME);

$db_timezone = new DateTimeZone(DB_TIMEZONE);
$output_timezone = new DateTimeZone(OUTPUT_TIMEZONE);

$dbh = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
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
        addChild($xml, $conference, $tag, $content);
    }
}

$events = $dbh->query('SELECT `events`.id, `events`.title, `events`.subtitle, `events`.slug, `events`.abstract, `events`.description, `events`.language,
rooms.name AS room,
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
    $event['start'] = new DateTime($event['start'], $db_timezone);
    $event['end'] = new DateTime($event['end'], $db_timezone);
    $event['start']->setTimezone($output_timezone);
    $event['start']->setTimezone($output_timezone);
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
$select_people = $dbh->prepare('SELECT people.name, people.id FROM people LEFT JOIN events_people ON people.id=events_people.person_id WHERE events_people.event_id = ?');

$keys = ['room', 'title', 'subtitle', 'track', 'type', 'language', 'abstract', 'description'];
$day_index = 1;
foreach($events_by_date_then_room as $day_date => $rooms) {
    $dayxml = addChild($xml, $schedule, 'day', NULL);
    $dayxml->setAttribute('index', $day_index);
    $dayxml->setAttribute('date', $day_date);

    foreach($rooms as $room_name => $events) {
        $roomxml = addChild($xml, $dayxml, 'room', NULL);
        $roomxml->setAttribute('name', $room_name);

        foreach($events as $event) {
            $eventxml = addChild($xml, $roomxml, 'event', NULL);
            $eventxml->setAttribute('id', $event['id']);

            // this stops PHPStorm from complaining, but most of these elements are just strings...
            /** @var $event DateTime[] */
            addChild($xml, $eventxml, 'start', $event['start']->format('H:i'));
            addChild($xml, $eventxml, 'duration', $event['duration']->format('%H:%I'));
            foreach($keys as $k) {
                addChild($xml, $eventxml, $k, $event[$k]);
            }

            // TODO: do we need this?
            // addChild($xml, $eventxml, 'slug', '');

            if($select_people->execute([$event['id']])) {
                $personsxml = $xml->createElement('persons');
                $personsxml = $eventxml->appendChild($personsxml);

                while($row = $select_people->fetch()) {
                    $personxml = addChild($xml, $personsxml, 'person', $row['name']);
                    $personxml->setAttribute('id', $row['id']);
                }
            } else {
                addChild($xml, $eventxml, 'persons', NULL);
            }
        }
    }

    $day_index++;
}

http_response_code(200);
header('Content-type: text/xml');
echo $xml->saveXML();
exit();

// ----------------------------------------------------------------------------

/**
 * Add child to an element and return it
 *
 * @param $xml DOMDocument
 * @param $parent DOMNode
 * @param $tagname string
 * @param $content string
 * @return DOMElement
 */
function addChild($xml, $parent, $tagname, $content) {
    $child = $xml->createElement($tagname);
    if($content !== NULL && $content !== '') {
        $child->textContent = $content;
    }
    return $parent->appendChild($child);
}
