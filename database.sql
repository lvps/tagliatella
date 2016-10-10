SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `conferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT, # An integer.
  `title` varchar(100) NOT NULL, # Conference name, e.g. 'Someconf 2017'
  `subtitle` varchar(100) DEFAULT NULL, # Conference subtitle
  `venue` varchar(100) DEFAULT NULL, # Where the conference will take place e.g. 'University of Some Place' or 'University of Some Place, 123 Fake Street'
  `city` varchar(100) DEFAULT NULL, # City, e.g. 'Springfield'
  `start` char(10) NOT NULL, # First day of events in YYYY-MM-DD format (exactly 10 characters), e.g. '2017-01-25'
  `end` char(10) NOT NULL, # Last day of events, e.g. '2017-01-26'
  `persons_url` varchar(512) DEFAULT NULL, # Not part of frab\Pentabarf standard, used by ILS Companion aka LD Companion aka Generic Conference Companion (https://github.com/0iras0r/ils-companion-android). Disable STANDARD_TAGS_ONLY if you need this!
  `events_url` varchar(512) DEFAULT NULL, # Not part of frab\Pentabarf standard, same as above. Disable STANDARD_TAGS_ONLY if you need this!
  `days` int(11) NOT NULL, # How many days with events, e.g. '2'
  `day_change` char(8) NOT NULL, # Time of the first event, maybe? FOSDEM had this set to 09:00:00 in their 2016 schedule. HH:MM:SS or ISO 8601 format, e.g. 2017-10-29T18:00:00+02:00
  `timeslot_duration` char(8) NOT NULL, # Gratest common divisor of the duration of each event. I doubt any client in existence uses this for anything. HH:MM:SS or ISO 8601 format.
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT, # An integer.
  `conference_id` int(11) NOT NULL, # 'id' from the 'conferences' table
  `title` varchar(100) NOT NULL, # Event title, e.g. 'An introduction to frobnicating things'
  `subtitle` varchar(100) DEFAULT NULL, # Event subtitle
  `slug` varchar(100) DEFAULT NULL, # Event title to be used in URLs, e.g. 'an-introduction-to-frobnicating-things'
  `abstract` text, # Short description of the event. No HTML allowed, apparently.
  `description` text, # Longer description of the event. HTML is allowed, apparently.
  `language` char(2) DEFAULT NULL, # two-letters language code, e.g. 'en'
  `room` int(11) NOT NULL, # 'id' from the 'rooms' table
  `track` int(11) NOT NULL, # 'id' from the 'tracks' table
  `type` int(11) NOT NULL, # 'id' from the 'event_types' table
  `start` datetime NOT NULL, # Date and time when the event begins. Set the time zone via the DB_TIMEZONE constant.
  `end` datetime NOT NULL, # Date and time when the event ends. Set the time zone via the DB_TIMEZONE constant. These fields will be used to calculate the 'duration'.
  PRIMARY KEY (`id`),
  KEY `room` (`room`),
  KEY `track` (`track`),
  KEY `type` (`type`),
  KEY `conference_id` (`conference_id`),
  CONSTRAINT `events_ibfk_5` FOREIGN KEY (`conference_id`) REFERENCES `conferences` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `events_ibfk_6` FOREIGN KEY (`type`) REFERENCES `event_types` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `events_ibfk_7` FOREIGN KEY (`track`) REFERENCES `tracks` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `events_ibfk_8` FOREIGN KEY (`room`) REFERENCES `rooms` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

# Maps people to events
CREATE TABLE `events_people` (
  `event_id` int(11) NOT NULL, # 'id' from 'events'
  `person_id` int(11) NOT NULL, # 'id' from 'people'
  UNIQUE KEY `person_id_event_id` (`person_id`,`event_id`),
  KEY `person_id` (`person_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `events_people_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `events_people_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `event_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT, # An integer.
  `name` varchar(100) NOT NULL, # event type, e.g. 'talk', 'keynote', 'mosh pit', ...
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `people` (
  `id` int(11) NOT NULL AUTO_INCREMENT, # An integer.
  `name` varchar(100) NOT NULL, # Person name, e.g. 'John Doe'
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT, # An integer.
  `name` varchar(100) NOT NULL, # E.g. 'Room 101'
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `tracks` (
  `id` int(11) NOT NULL AUTO_INCREMENT, # An integer.
  `name` varchar(100) NOT NULL, # Topic of the event.
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
