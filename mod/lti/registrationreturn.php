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
 * Handle the return from the Tool Provider after registering a tool proxy.
 *
 * @package mod_lti
 * @copyright  2014 Vital Source Technologies http://vitalsource.com
 * @author     Stephen Vickers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

$top = optional_param('top', null, PARAM_INT);
$msg = optional_param('lti_msg', null, PARAM_RAW);
$err = optional_param('lti_errormsg', null, PARAM_RAW);
$id = optional_param('id', null, PARAM_INT);

// No guest autologin.
require_login(0, false);

if (is_null($top)) {

    $params = array();
    $params['top'] = '1';
    if (!is_null($msg)) {
        $params['lti_msg'] = $msg;
    }
    if (!is_null($err)) {
        $params['lti_errormsg'] = $err;
    }
    if (!is_null($id)) {
        $params['id'] = $id;
    }
    $redirect = new moodle_url('/mod/lti/registrationreturn.php', $params);
    $redirect = $redirect->out(false);

    $clickhere = get_string('click_to_continue', 'lti', (object)array('link' => $redirect));
    $html = <<< EOD
<html>
<head>
<script type="text/javascript">
//<![CDATA[
top.location.href = '{$redirect}';
//]]
</script>
</head>
<body>
<noscript>
{$clickhere}
</noscript>
</body>
</html>
EOD;
    echo $html;

} else if (!is_null($msg) && !is_null($err)) {

    $params = array();
    $params['top'] = '1';
    if (!is_null($err)) {
        $params['lti_errormsg'] = $err;
    }
    if (!is_null($id)) {
        $params['id'] = $id;
    }
    $redirect = new moodle_url('/mod/lti/registrationreturn.php', $params);
    $redirect = $redirect->out(false);
    redirect($redirect, $err);

} else {

    $redirect = new moodle_url('/mod/lti/toolproxies.php');
    if (!empty($id)) {
        $toolproxy = $DB->get_record('lti_tool_proxies', array('id' => $id));
        switch($toolproxy->state) {
            case LTI_TOOL_PROXY_STATE_ACCEPTED:
                $tab = 'tp_accepted';
                break;
            case LTI_TOOL_PROXY_STATE_REJECTED:
                $tab = 'tp_rejected';
                break;
            default:
                $tab = '';
        }
        $redirect->param('tab', $tab);
    }
    $redirect = $redirect->out();

    if (is_null($msg)) {
        $msg = $err;
    }
    redirect($redirect, $msg);

}
