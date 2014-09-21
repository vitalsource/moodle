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
 * This file contains an abstract definition of an LTI service
 *
 * @package    mod_lti
 * @copyright  2014 Vital Source Technologies http://vitalsource.com
 * @author     Stephen Vickers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_lti\ltiservice;

class response {

    private $code;
    private $reason;
    private $request_method;
    private $accept;
    private $content_type;
    private $data;
    private $body;
    private $response_codes;

    function __construct() {

        $this->code = 200;
        $this->reason = null;
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->accept = null;
        $this->content_type = null;
        $this->data = null;
        $this->body = null;
        $this->response_codes = array(
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            300 => 'Multiple Choices',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            415 => 'Unsupported Media Type',
            500 => 'Internal Server Error',
            501 => 'Not Implemented'
        );

    }

    public function get_code() {
        return $this->code;
    }

    public function set_code($code) {
        $this->code = $code;
        $this->reason = null;
    }

    public function get_reason() {
        if (is_null($this->reason)) {
            $this->reason = $this->response_codes[$this->code];
        }
        if (is_null($this->reason)) {
            $this->reason = $this->response_codes[($this->code / 100) * 100];
        }
        return $this->reason;
    }

    public function set_reason($reason) {
        $this->reason = $reason;
    }

    public function get_request_method() {
        return $this->request_method;
    }

    public function get_accept() {
        return $this->accept;
    }

    public function set_accept($accept) {
        $this->accept = $accept;
    }

    public function get_content_type() {
        return $this->content_type;
    }

    public function set_content_type($content_type) {
        $this->content_type = $content_type;
    }

    public function get_request_data() {
        return $this->data;
    }

    public function set_request_data($data) {
        $this->data = $data;
    }

    public function set_body($body) {
        $this->body = $body;
    }

    public function send() {
        header("HTTP/1.0 {$this->code} {$this->get_reason()}");
        if (($this->code >= 200) && ($this->code < 300)) {
            if (!is_null($this->content_type)) {
                header("Content-Type: {$this->content_type};charset=UTF-8");
            }
            if (!is_null($this->body)) {
                echo $this->body;
            }
        }
    }

}
