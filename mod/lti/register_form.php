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
 * This file defines de main basiclti configuration form
 *
 * @package mod_lti
 * @copyright  2014 Vital Source Technologies http://vitalsource.com
 * @author     Stephen Vickers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

class mod_lti_register_types_form extends moodleform {

    public function definition() {
        global $CFG;

        $mform    =& $this->_form;

        $mform->addElement('header', 'setup', get_string('registration_options', 'lti'));

        // Tool Provider name.

        $mform->addElement('text', 'lti_registrationname', get_string('registrationname', 'lti'));
        $mform->setType('lti_registrationname', PARAM_TEXT);
        $mform->addHelpButton('lti_registrationname', 'registrationname', 'lti');
        $mform->addRule('lti_registrationname', null, 'required', null, 'client');

        // Registration URL.

        $mform->addElement('text', 'lti_registrationurl', get_string('registrationurl', 'lti'), array('size' => '64'));
        $mform->setType('lti_registrationurl', PARAM_TEXT);
        $mform->addHelpButton('lti_registrationurl', 'registrationurl', 'lti');

        // LTI Capabilities.

        $options = array_keys(lti_get_capabilities());
        natcasesort($options);
        $attributes = array( 'multiple' => 1, 'size' => min(count($options), 10) );
        $mform->addElement('select', 'lti_capabilities', get_string('capabilities', 'lti'),
            array_combine($options, $options), $attributes);
        $mform->setType('lti_capabilities', PARAM_TEXT);
        $mform->addHelpButton('lti_capabilities', 'capabilities', 'lti');

        // LTI Services.

        $services = lti_get_services();
        $options = array();
        foreach ($services as $service) {
            $options[$service->get_id()] = $service->get_name();
        }
        $attributes = array( 'multiple' => 1, 'size' => min(count($options), 10) );
        $mform->addElement('select', 'lti_services', get_string('services', 'lti'), $options, $attributes);
        $mform->setType('lti_services', PARAM_TEXT);
        $mform->addHelpButton('lti_services', 'services', 'lti');

        $mform->addElement('hidden', 'toolproxyid');
        $mform->setType('toolproxyid', PARAM_INT);

        $tab = optional_param('tab', '', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'tab', $tab);
        $mform->setType('tab', PARAM_ALPHAEXT);

        $courseid = optional_param('course', 1, PARAM_INT);
        $mform->addElement('hidden', 'course', $courseid);
        $mform->setType('course', PARAM_INT);

        // Add standard buttons, common to all modules.

        $this->add_action_buttons();

    }

    public function disable_fields() {

        $mform    =& $this->_form;

        $mform->disabledIf('lti_registrationurl', null);
        $mform->disabledIf('lti_capabilities', null);
        $mform->disabledIf('lti_services', null);

    }

    public function required_fields() {

        $mform    =& $this->_form;

        $mform->addRule('lti_registrationurl', null, 'required', null, 'client');

    }

}
