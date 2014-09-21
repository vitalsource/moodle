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
 * This file contains a class definition for the Tool Proxy resource
 *
 * @package    mod_lti
 * @copyright  2014 Vital Source Technologies http://vitalsource.com
 * @author     Stephen Vickers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace ltiservice_toolproxy\resource;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lti/OAuth.php');
require_once($CFG->dirroot . '/mod/lti/TrivialStore.php');

// TODO: Switch to core oauthlib once implemented - MDL-30149
use moodle\mod\lti as lti;

/**
 * A resource implementing the Tool Proxy.
 *
 * @copyright  2014 Vital Source Technologies http://vitalsource.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class toolproxy extends \mod_lti\ltiservice\resource_base {

    function __construct($service) {

        parent::__construct($service);
        $this->id = 'ToolProxy.collection';
        $this->template = '/toolproxy';
        $this->formats[] = 'application/vnd.ims.lti.v2.toolproxy+json';
        $this->methods[] = 'POST';

    }

    public function execute($response) {

        $ok = $this->get_service()->check_tool_proxy(null, $response->get_request_data());
        $tool_proxy = $this->get_service()->get_tool_proxy();
        if ($ok) {
            $tool_proxy_json = json_decode($response->get_request_data());
            $ok = !is_null($tool_proxy_json);
            $ok = $ok && isset($tool_proxy_json->tool_profile->product_instance->product_info->product_family->vendor->code);
            $ok = $ok && isset($tool_proxy_json->security_contract->shared_secret);
            $ok = $ok && isset($tool_proxy_json->tool_profile->resource_handler);
        }
        if ($ok) {
            $base_url = '';
            if (isset($tool_proxy_json->tool_profile->base_url_choice[0]->default_base_url)) {
                $base_url = $tool_proxy_json->tool_profile->base_url_choice[0]->default_base_url;
            }
            $secure_base_url = '';
            if (isset($tool_proxy_json->tool_profile->base_url_choice[0]->secure_base_url)) {
                $secure_base_url = $tool_proxy_json->tool_profile->base_url_choice[0]->secure_base_url;
            }
            $resources = $tool_proxy_json->tool_profile->resource_handler;
            foreach ($resources as $resource) {
                $icon = new \stdClass();
                if (isset($resource->icon_info[0]->default_location->path)) {
                    $icon->path = $resource->icon_info[0]->default_location->path;
                }
                $tool = new \stdClass();
                $tool->name = $resource->resource_name->default_value;
                $messages = $resource->message;
                foreach ($messages as $message) {
                    if ($message->message_type == 'basic-lti-launch-request') {
                        $tool->path = $message->path;
                        $tool->enabled_capability = $message->enabled_capability;
                        $tool->parameter = $message->parameter;
                    }
                }
                $config = new \stdClass();
                $config->lti_toolurl = "{$base_url}{$tool->path}";
                $config->lti_typename = $tool->name;
                $config->lti_coursevisible = 1;
                $config->lti_forcessl = 0;

                $type = new \stdClass();
                $type->state = LTI_TOOL_STATE_PENDING;
                $type->toolproxyid = $tool_proxy->id;
                $type->enabledcapability = implode("\n", $tool->enabled_capability);
                $type->parameter = \ltiservice_toolproxy\resource\toolproxy::lti_extract_parameters($tool->parameter);
                if (!empty($icon->path)) {
                    $type->icon = "{$base_url}{$icon->path}";
                    if (!empty($secure_base_url)) {
                        $type->secureicon = "{$secure_base_url}{$icon->path}";
                    }
                }
                $ok = (lti_add_type($type, $config) !== FALSE);
            }
            if (isset($tool_proxy_json->custom)) {
                lti_set_tool_settings($tool_proxy_json->custom, $tool_proxy->id);
            }
        }
        if ($ok) {
            $tool_proxy->state = LTI_TOOL_PROXY_STATE_ACCEPTED;
            $tool_proxy->toolproxy = $response->get_request_data();
            $tool_proxy->secret = $tool_proxy_json->security_contract->shared_secret;
            $tool_proxy->vendorcode = $tool_proxy_json->tool_profile->product_instance->product_info->product_family->vendor->code;

            $url = $this->get_endpoint();
            $body = <<< EOD
{
  "@context" : "http://purl.imsglobal.org/ctx/lti/v2/ToolProxyId",
  "@type" : "ToolProxy",
  "@id" : "{$url}",
  "tool_proxy_guid" : "{$tool_proxy->guid}"
}
EOD;
            $response->set_code(201);
            $response->set_content_type('application/vnd.ims.lti.v2.toolproxy.id+json');
            $response->set_body($body);
        } else {
            $tool_proxy->state = LTI_TOOL_PROXY_STATE_REJECTED;
            $response->set_code(400);
        }
        lti_update_tool_proxy($tool_proxy);
    }

/**
 * Extracts the message parameters from the tool proxy entry
 *
 * @param array $parameters     Parameter section of a message
 *
 * @return String  containing parameters
 */
    private static function lti_extract_parameters($parameters) {

        $params = array();
        foreach ($parameters as $parameter) {
            if (isset($parameter->variable)) {
                $value = '$' . $parameter->variable;
            } else {
                $value = $parameter->fixed;
                if (strlen($value) > 0) {
                    $first = substr($value, 0, 1);
                    if (($first == '$') || ($first == '\\')) {
                        $value = '\\' . $value;
                    }
                }
            }
            $params[] = "{$parameter->name}={$value}";
        }

        return implode("\n", $params);

    }

}
