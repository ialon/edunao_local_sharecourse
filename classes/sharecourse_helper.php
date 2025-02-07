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
 *
 *
 * @package    local_sharecourse
 * @copyright  2024 Edunao SAS (contact@edunao.com)
 * @author     Pierre FACQ <pierre.facq@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sharecourse;

use moodle_database;
use moodle_url;

class sharecourse_helper {
    private moodle_database $db;
    public function __construct(moodle_database $db) {
        $this->db = $db;
    }

    public function get_sharecourse_url(int $courseid): moodle_url {
        // Add enrol key for self enrol if possible.
        $enrolkey = $this->db->get_field('enrol', 'password', [
            'courseid' => $courseid,
            'status' => ENROL_INSTANCE_ENABLED,
            'enrol' => 'self',
        ]);

        // Generate different share link depending on enrol options.
        if ($enrolkey !== false) {
            $courseurl = new moodle_url('/enrol/index.php', [
                'id' => $courseid,
                'enrolkey' => urlencode($enrolkey),
            ]);
        } else {
            $courseurl = new moodle_url('/course/view.php', ['id' => $courseid]);
        }

        return $courseurl;
    }

    public function get_course_metadata(int $courseid): string {
        global $PAGE, $OUTPUT;

        $course = get_course($courseid);
        $shareurl = $this->get_sharecourse_url($course->id);
        $context = \context_course::instance($course->id);

        $PAGE->set_context($context);
        $PAGE->set_url($shareurl);

        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $context->id,
            'course',
            'overviewfiles',
            0,
            'itemid, filepath, filename',
            false
        );

        // Set a default image when there is no image
        $courseimageurl = $OUTPUT->image_url('ogdefault', 'local_sharecourse');
        $courseimagewidth = 666;
        $courseimageheight = 350;

        if (!empty($files)) {
            $courseimage = reset($files);

            // Get the image info to set the width and height.
            $courseimageinfo = $courseimage->get_imageinfo();
            $courseimagewidth = (int) $courseimageinfo['width'];
            $courseimageheight = (int) $courseimageinfo['height'];

            $courseimageurl = moodle_url::make_pluginfile_url(
                $courseimage->get_contextid(),
                $courseimage->get_component(),
                $courseimage->get_filearea(),
                null,
                $courseimage->get_filepath(),
                $courseimage->get_filename()
            );
        }

        // Add a random string to the image URL to avoid caching issues.
        $courseimageurl = $courseimageurl . '?v=' . random_int(0, 1000);

        $output = '<meta property="og:title" content="' . $course->fullname . '" />';
        $output .= '<meta property="og:description" content="' . strip_tags($course->summary) . '" />';
        $output .= '<meta property="og:image" content="' . $courseimageurl . '" />';
        $output .= '<meta property="og:image:width" content="' . $courseimagewidth . '" />';
        $output .= '<meta property="og:image:height" content="' . $courseimageheight . '" />';
        $output .= '<meta property="og:url" content="' . $shareurl . '" />';
        $output .= '<meta property="og:type" content="website" />';
        $output .= '<meta property="og:logo" content="' . $OUTPUT->get_compact_logo_url() . '" />';

        // LinkedIn requires a full HTML page to parse the meta tags.
        if (stripos($_SERVER['HTTP_USER_AGENT'], 'LinkedInBot') !== false) {
            $output = '<html><head>' . $output . '</head></html>';
        }

        return $output;
    }

    public function is_crawler_request(): bool {
        $crawlers = ['WhatsApp', 'facebookexternalhit', 'Twitterbot', 'LinkedInBot'];

        foreach ($crawlers as $crawler) {
            if (stripos($_SERVER['HTTP_USER_AGENT'], $crawler) !== false) {
                return true;
            }
        }

        return false;
    }
}
