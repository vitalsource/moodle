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
 * This file contains a class definition for the Tool Settings service
 *
 * @package    mod_lti
 * @copyright  2014 Vital Source Technologies http://vitalsource.com
 * @author     Stephen Vickers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lti/service/service_base.php');
require_once('resource/systemsettings.php');
require_once('resource/contextsettings.php');
require_once('resource/linksettings.php');

/**
 * A service implementing Tool Settings.
 *
 * @copyright  2014 Vital Source Technologies http://vitalsource.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ltiservice_toolsettings extends ltiservice_base {

    function __construct() {

        parent::__construct();
        $this->id = 'toolsettings';
        $this->name = 'Tool Settings';

    }

    public function get_resources() {

        if (is_null($this->resources)) {
            $this->resources = array();
            $this->resources[] = new ltiresource_systemsettings($this);
            $this->resources[] = new ltiresource_contextsettings($this);
            $this->resources[] = new ltiresource_linksettings($this);
        }

        return $this->resources;

    }

    public static function distinct_settings(&$systemsettings, &$contextSettings, $linksettings) {

        if (!is_null($systemsettings)) {
            foreach ($systemsettings as $key => $value) {
                if ((!is_null($contextSettings) && array_key_exists($key, $contextSettings)) ||
                    (!is_null($linksettings) && array_key_exists($key, $linksettings))) {
                    unset($systemsettings[$key]);
                }
            }
        }
        if (!is_null($contextSettings)) {
            foreach ($contextSettings as $key => $value) {
                if (!is_null($linksettings) && array_key_exists($key, $linksettings)) {
                    unset($contextSettings[$key]);
                }
            }
        }
    }

    public static function settings_to_json($settings, $simpleformat, $type, $resource) {

        $json = '';
        if (!is_null($settings)) {
            $indent = '';
            if (!$simpleformat) {
                $json .= "    {\n      \"@type\":\"{$type}\",\n";
                $json .= "      \"@id\":\"{$resource->get_endpoint()}\",\n";
                $json .= '      "custom":';
                $json .= "{\n";
                $indent = '      ';
            }
            $isfirst = true;
            foreach ($settings as $key => $value) {
                if (!$isfirst) {
                    $json .= ",\n";
                } else {
                    $isfirst = false;
                }
                $json .= "{$indent}  \"{$key}\":\"{$value}\"";
            }
            if (!$simpleformat) {
                if (!$isfirst) {
                    $json .= "\n";
                }
                $json .= "{$indent}}\n    }";
            }
        }

        return $json;

    }

}
