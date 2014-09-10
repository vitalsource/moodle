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
 * This file contains a controller for receiving LTI service requests
 *
 * @package    mod_lti
 * @copyright  2014 Vital Source Technologies http://vitalsource.com
 * @author     Stephen Vickers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/lti/locallib.php');
require_once($CFG->dirroot . '/mod/lti/service/service_response.php');


$response = new mod_lti_service_response();

$is_get = $response->get_request_method() == 'GET';

if ($is_get) {
    $response->set_accept($_SERVER['HTTP_ACCEPT']);
} else {
    $response->set_content_type($_SERVER['CONTENT_TYPE']);
}

$services = get_plugin_list('ltiservice');

$ok = false;
$path = $_SERVER['PATH_INFO'];

foreach ($services as $name => $location) {
    require_once("{$location}/service.php");
    $classname = "ltiservice_{$name}";
    $service = new $classname();
    $resources = $service->get_resources();
    foreach ($resources as $resource) {
        if (($is_get && !is_null($response->get_accept()) && (strpos($response->get_accept(), '*/*') === FALSE) &&
             !in_array($response->get_accept(), $resource->get_formats())) ||
            (!$is_get && !in_array($response->get_content_type(), $resource->get_formats()))) {
            continue;
        }
        $template = $resource->get_template();
        $template = preg_replace('/\{[a-zA-Z_]+\}/', '[^/]+', $template);
        $template = preg_replace('/\{\?[0-9a-zA-Z_\-,]+\}$/', '', $template);
        $template = str_replace('/', '\/', $template);
        if (preg_match("/{$template}/", $path) === 1) {
            $ok = true;
            break 2;
        }
    }
}
if (!$ok) {
    $response->set_code(400);
} else {
    $body = file_get_contents('php://input');
    $response->set_request_data($body);
    if (in_array($response->get_request_method(), $resource->get_methods())) {
        $resource->execute($response);
    } else {
        $response->set_code(405);
    }
}
$response->send();
