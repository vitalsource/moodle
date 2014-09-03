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
 * This file contains all necessary code to launch a Tool Proxy registration
 *
 * @package mod_lti
 * @copyright  2014 Vital Source Technologies http://vitalsource.com
 * @author     Stephen Vickers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$id = required_param('id', PARAM_INT);
$tab = optional_param('tab', '', PARAM_ALPHAEXT);

require_login(0, false);

$redirect = new moodle_url('/mod/lti/toolproxies.php', array('tab' => $tab));
$redirect = $redirect->out();

require_sesskey();

$url = new moodle_url('/mod/lti/register.php', array('id'=>$id));
$PAGE->set_url($url);

admin_externalpage_setup('managemodules'); // Hacky solution for printing the admin page


$PAGE->set_heading(get_string('toolproxyregistration', 'lti'));
$PAGE->set_title("{$SITE->shortname}: " . get_string('toolproxyregistration', 'lti'));
$PAGE->navbar->add(get_string('lti_administration', 'lti'), $redirect);

// Print the page header
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('toolproxyregistration', 'lti'));

echo $OUTPUT->box_start('generalbox');

// Request the registration request content with an object tag
echo '<object id="contentframe" height="600px" width="100%" type="text/html" data="registration.php?id='.$id.'"></object>';

//Output script to make the object tag be as large as possible
$resize = '
        <script type="text/javascript">
        //<![CDATA[
            YUI().use("node", "event", function(Y) {
                //Take scrollbars off the outer document to prevent double scroll bar effect
                var doc = Y.one("body");
                doc.setStyle("overflow", "hidden");

                var frame = Y.one("#contentframe");
                var padding = 15; //The bottom of the iframe wasn\'t visible on some themes. Probably because of border widths, etc.
                var lastHeight;
                var resize = function(e) {
                    var viewportHeight = doc.get("winHeight");
                    if(lastHeight !== Math.min(doc.get("docHeight"), viewportHeight)){
                        frame.setStyle("height", viewportHeight - frame.getY() - padding + "px");
                        lastHeight = Math.min(doc.get("docHeight"), doc.get("winHeight"));
                    }
                };

                resize();

                Y.on("windowresize", resize);
            });
        //]]
        </script>
';

echo $resize;

// Finish the page
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
