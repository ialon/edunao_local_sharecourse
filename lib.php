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


use local_sharecourse\sharecourse_helper;

defined('MOODLE_INTERNAL') || die();

function local_sharecourse_extend_navigation_course(navigation_node $navigation, stdClass $course, $context) {
    global $PAGE, $CFG, $DB;

    if (has_capability('local/sharecourse:sharecourse', $context)) {
        $url = new moodle_url('/local/sharecourse/share.php', ['id' => $course->id]);
        $node = navigation_node::create(
            get_string('share', 'local_sharecourse'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'sharecourse',
            new pix_icon('t/share', '')
        );
        $node->showinflatnavigation = true;

        // Add the node to the end of the navigation.
        $navigation->add_node($node);
        // Construct specific requirejs config.
        $requireconfig = [
            'paths' => [
                'qrcode' => $CFG->wwwroot . '/local/sharecourse/js/qrcode-wrapper',
            ],
        ];
        // Set config for requirejs.
        $PAGE->requires->js_amd_inline('require.config(' . json_encode($requireconfig) . ')');

        // Get LTI information if possible.
        $ltiurl = (new \moodle_url('/enrol/lti/launch.php'))->out(false);
        $lticode = null;
        if (enrol_is_enabled('lti') && has_capability('local/sharecourse:sharelti', $context)) {
            $contextcourse = CONTEXT_COURSE;
            $sql = <<<SQL
            SELECT elt.id, elt.uuid
            FROM {enrol_lti_tools} elt
            JOIN {enrol} e ON elt.enrolid = e.id AND e.courseid = :courseid
            JOIN {context} c ON c.id = elt.contextid AND c.contextlevel = $contextcourse
            WHERE elt.ltiversion = 'LTI-1p3'
            SQL;
            $tools = $DB->get_records_sql($sql, ['courseid' => $course->id]);
            if (! empty($tools)) {
                $tool = reset($tools);
                $lticode = "id={$tool->uuid}";
            }
        }
        $haslti = $lticode !== null;

        // Get sharecourse link.
        $sharecoursehelper = new sharecourse_helper($DB);
        $courseurl = $sharecoursehelper->get_sharecourse_url($course->id);

        // Let plugins add buttons to share course modal.
        $extendedhtml = '';
        $pluginsfunction = get_plugins_with_function('extend_sharecourse', 'lib.php');
        foreach ($pluginsfunction as $plugintype => $plugins) {
            foreach ($plugins as $pluginfunction) {
                $extendedhtml .= $pluginfunction($course, $context);
            }
        }

        // Call init js script.
        $PAGE->requires->js_call_amd('local_sharecourse/main', 'init', [
            $course->fullname,
            $courseurl->out(false),
            $extendedhtml,
            $ltiurl,
            $lticode,
            $haslti
        ]);
    }
}


/**
 * Callback to add head elements (for releases up to Moodle 4.3).
 *
 * @return string
 */
function local_sharecourse_before_standard_html_head() {
    return before_head();
}

function before_head() {
    global $DB, $PAGE, $COURSE;

    // Exit early if not in course context.
    if ($PAGE->context->contextlevel !== CONTEXT_COURSE) {
        return '';
    }

    $sharecoursehelper = new sharecourse_helper($DB);

    return $sharecoursehelper->get_course_metadata($COURSE->id);
}
