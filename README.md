# Tagliatella

A PHP script that produces an XML file containing a schedule for conference
talks, for those who like to organize conferences by editing rows in a database
by hand.

The format is sometimes called Pentabarf, but to my understanding that's
xCal (RFC 6321), which is quite different and incompatible. Frab calls this
format simply 'xml'.

Compatible with:
* [Giggity](https://wilmer.gaa.st/main.php/giggity.html)
* [FOSDEM Companion for Android](https://github.com/cbeyls/fosdem-companion-android)
* [ILS Companion\LDTO Companion\Generic Conference Companion](https://github.com/0iras0r/ils-companion-android)

Requires PHP 5.4+ with pdo_mysql extension.

## Installation

* Import database.sql into MySQL
* Add the data by editing rows by hand (refer to database.sql for some documentation)
* Edit config.php appropriately

## License

MIT (see LICENSE).
