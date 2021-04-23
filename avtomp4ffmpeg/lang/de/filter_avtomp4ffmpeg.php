<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version information
 *
 * @package    filter_avtomp4ffmpeg
 * @copyright  2021 Sven Patrick Meier <sven.patrick.meier@team-parallax.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['filtername'] = 'Ffmpeg-basierter audio/video zu MP4 Konvertierungsfilter';
$string['ffmpegwebserviceurl'] = 'Ffmpeg-Webservice URL';
$string['ffmpegwebserviceurldefault'] = 'https://beispiel-konvertierungsservice.com';
$string['ffmpegwebserviceurl_desc'] = 'URL für den ffmpeg-Konverterservice';
$string['convertaudio'] = 'Audio konvertieren';
$string['convertaudio_desc'] = 'Konvertiert .OGG Dateien zu .MP4 (M4A)';
$string['convertvideo'] = 'Video konvertieren';
$string['convertvideo_desc'] = 'Konvertiert .WEBM Dateien zu .MP4';
$string['processjobs_task'] = 'Prozess des Re-Encodings von MP4 Dateien';
$string['convertonlyexts'] = 'Nur Dateien mit dieser Endung konvertieren';
$string['convertonlyexts_desc'] = 'Komma separierte Liste an Dateiendungen die zu MP4 konvertiert werden sollen';
$string['privacy:metadata'] = 'Der Ffmpeg-basierte audio/video zu MP4 Filter speichert keine persönlichen Daten.';
// TODO: Review if needed here
$string['audioffmpegsettings'] = 'Einstellungen - Audio-Konvertierungen';
// TODO: Review if needed here
$string['audioffmpegsettings_desc'] = 'This should contain at least "-i {%INPUTFILE%} {%OUTPUTFILE%}"; place your options around these as needed';
// TODO: Review if needed here
$string['videoffmpegsettings'] = 'Einstellungen - Video-Konvertierung';
// TODO: Review if needed here
$string['videoffmpegsettings_desc'] = 'This should contain at least "-i {%INPUTFILE%} {%OUTPUTFILE%}"; place your options around these as needed';
