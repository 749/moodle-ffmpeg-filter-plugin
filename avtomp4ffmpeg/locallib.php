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

defined('MOODLE_INTERNAL') || die();

define('FILTER_AVTOMP4FFMPEG_JOBSPERPASS', 5);
define('FILTER_AVTOMP4FFMPEG_JOBSTATUS_INITIAL', 0);
define('FILTER_AVTOMP4FFMPEG_JOBSTATUS_RUNNING', 1);
define('FILTER_AVTOMP4FFMPEG_JOBSTATUS_DONE', 2);
define('FILTER_AVTOMP4FFMPEG_JOBSTATUS_FAILED', 3);
define('FILTER_AVTOMP4FFMPEG_INPUTFILE_PLACEHOLDER', '{%INPUTFILE%}');
define('FILTER_AVTOMP4FFMPEG_OUTPUTFILE_PLACEHOLDER', '{%OUTPUTFILE%}');

/**
 * @param int|null  $jobid
 * @param bool|null $displaytrace
 *
 * @throws dml_exception
 * @throws file_exception
 */
function filter_avtomp4ffmpeg_processjobs(?int $jobid = null, ?bool $displaytrace = true) {
    $ffmpegwebserviceurl = get_config('filter_avtomp4ffmpeg', 'ffmpegwebserviceurl');
    if (empty($ffmpegwebserviceurl)) {
        // don't bother if ffmpeg is not usable
        if ($displaytrace) {
            mtrace('ffmpeg webservice url not available, aborting');
        }
        return;
    }
    global $DB;
    if ($jobid > 0) {
        $jobs = $DB->get_records('filter_avtomp4ffmpeg_jobs', [
            'id'     => $jobid,
            'status' => FILTER_AVTOMP4FFMPEG_JOBSTATUS_INITIAL,
            'status' => FILTER_AVTOMP4FFMPEG_JOBSTATUS_RUNNING
        ]);
    } else {
        // take one job at a time
        $jobs = $DB->get_records(
            'filter_avtomp4ffmpeg_jobs',
            [
                'status' => FILTER_AVTOMP4FFMPEG_JOBSTATUS_INITIAL,
                'status' => FILTER_AVTOMP4FFMPEG_JOBSTATUS_RUNNING
            ],
            'id ASC',
            '*',
            '0',
            FILTER_AVTOMP4FFMPEG_JOBSPERPASS
        );
        if ($displaytrace) {
            mtrace('found ' . count($jobs) . ' jobs');
        }
    }
    while ($job = array_shift($jobs)) {
        if (!$job) {
            if ($displaytrace) {
                mtrace('no jobs found');
            }
            return;
        }
        $fs = get_file_storage();
        $inputfile = $fs->get_file_by_id($job->fileid);

        if (!$inputfile) {
            // $job->status = FILTER_AVTOMP4FFMPEG_JOBSTATUS_FAILED;
            // $DB->update_record('filter_avtomp4ffmpeg_jobs', $job);
            update_job_and_record($job, FILTER_AVTOMP4FFMPEG_JOBSTATUS_FAILED);

            if ($displaytrace) {
                mtrace('file ' . $job->fileid . ' not found');
            }

            return;
        }
        // TODO: Get Source-Extension
        $source_format = pathinfo($inputfile->get_filename(), PATHINFO_EXTENSION);
        $target_format = pathinfo($inputfile->get_filename(), PATHINFO_EXTENSION);
        // retrieve file conversion status from the webservice
        if ($job->status = FILTER_AVTOMP4FFMPEG_JOBSTATUS_RUNNING) {
            // TODO: Implement call to webservice
            // TODO: In case the result is 'converted' update the job-record and write output.
        }
        // to make sure we don't try to run the same job twice
        // $job->status = FILTER_AVTOMP4FFMPEG_JOBSTATUS_RUNNING;
        // $DB->update_record('filter_avtomp4ffmpeg_jobs', $job);
        update_job_and_record($job, FILTER_AVTOMP4FFMPEG_JOBSTATUS_RUNNING);
        // create temp directory for data storage during conversion
        $tempdir = make_temp_directory('filter_avtomp4ffmpeg');
        $tmpinputfilepath = $inputfile->copy_content_to_temp('filter_avtomp4ffmpeg');
        $tmpoutputfilename = str_replace('.ogg', '.m4a', $inputfile->get_filename());
        $tmpoutputfilename = str_replace('.webm', '.mp4', $tmpoutputfilename);
        $tmpoutputfilename = str_replace('.ogv', '.mp4', $tmpoutputfilename);
        $tmpoutputfilepath = $tempdir . DIRECTORY_SEPARATOR . $tmpoutputfilename;

        $type = (strpos($tmpoutputfilename, '.m4a') !== false) ? 'audio' : 'video';

        $inputfileplaceholder_preg = preg_quote(FILTER_AVTOMP4FFMPEG_INPUTFILE_PLACEHOLDER, '/');
        $outputfileplaceholder_preg = preg_quote(FILTER_AVTOMP4FFMPEG_OUTPUTFILE_PLACEHOLDER, '/');
        $ffmpegoptions =
            preg_replace(
                '/^(.*)' . $inputfileplaceholder_preg . '(.*)' . $outputfileplaceholder_preg . '(.*)$/',
                '$1 ' . escapeshellarg($tmpinputfilepath) . ' $2 ' . escapeshellarg($tmpoutputfilepath) . ' $3',
                get_config('filter_avtomp4ffmpeg', $type . 'ffmpegsettings')
            );

        $command = escapeshellcmd(trim($ffmpegwebserviceurl) . ' ' . $ffmpegoptions);
        if ($displaytrace) {
            mtrace($command);
        }

        // $request_body = array(
        //     "conversionFile" => "@". $ffmpegwebserviceurl,
        //     "originalFormat" => $source_format,
        //     "targetFormat" => $target_format
        // );
        $output = null;
        $return = null;

        //TODO: webservice connection 
        exec($command, $output, $return);
        if ($output) {
            mtrace($output);
        }
        if ($displaytrace) {
            mtrace('...returned ' . $return);
        }

        unlink($tmpinputfilepath); // not needed anymore

        if (!file_exists($tmpoutputfilepath) || !is_readable($tmpoutputfilepath)) {
            // $job->status = FILTER_AVTOMP4FFMPEG_JOBSTATUS_FAILED;
            // $DB->update_record('filter_avtomp4ffmpeg_jobs', $job);
            update_job_and_record($job, FILTER_AVTOMP4FFMPEG_JOBSTATUS_FAILED);
            if ($displaytrace) {
                mtrace('output file not found');
            }

            return;
        }

        $fs = get_file_storage();
        $inputfile_properties = $DB->get_record('files', ['id' => $inputfile->get_id()]);
        $outputfile_properties = [
            'contextid'    => $inputfile_properties->contextid,
            'component'    => $inputfile_properties->component,
            'filearea'     => $inputfile_properties->filearea,
            'itemid'       => $inputfile_properties->itemid,
            'filepath'     => $inputfile_properties->filepath,
            'filename'     => $tmpoutputfilename,
            'userid'       => $inputfile_properties->userid,
            'author'       => $inputfile_properties->author,
            'license'      => $inputfile_properties->license,
            'timecreated'  => time(),
            'timemodified' => time()
        ];
        try {
            $outputfile = $fs->create_file_from_pathname($outputfile_properties, $tmpoutputfilepath);
        } catch (Exception $exception) {
            // $job->status = FILTER_AVTOMP4FFMPEG_JOBSTATUS_FAILED;
            // $DB->update_record('filter_avtomp4ffmpeg_jobs', $job);
            update_job_and_record($job, FILTER_AVTOMP4FFMPEG_JOBSTATUS_FAILED);
            if ($displaytrace) {
                mtrace('file could not be saved: ' . $exception->getMessage());
            }

            return;
        }
        unlink($tmpoutputfilepath); // not needed anymore

        // $job->status = FILTER_AVTOMP4FFMPEG_JOBSTATUS_DONE;
        // $DB->update_record('filter_avtomp4ffmpeg_jobs', $job);
        update_job_and_record($job, FILTER_AVTOMP4FFMPEG_JOBSTATUS_DONE);
        if ($displaytrace) {
            mtrace('created file id ' . $outputfile->get_id());
        }
    }
}

/**
 * @param $job
 * @param int $new_status
 *
 */
function update_job_and_record($job, $new_status) {
    global $DB;
    $job->status = $new_status;
    $DB->update_record('filter_avtomp4ffmpeg_jobs', $job);
}