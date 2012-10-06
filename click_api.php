<?php

# ClickAPI By PPSlim
/*
	Please be aware. This class is work in progress.
	Functionality is there, however, until I have a base for each Clickatell API
	I don't know the best way of implimenting individual functions, return values
	and if or not things are supported.
	For specific support, contact me via http://www.phpclasses.org/clickapi
*/

define("CLICK_ERROR", 0);
define("CLICK_OK", 1);

define("CLICK_MSGID_API", "apiMsgId");
define("CLICK_MSGID_CLI", "cliMsgId");

define("CLICK_FEAT_TEXT",	0X0001); #  On by default
define("CLICK_FEAT_8BIT",	0X0002); #  On by defeult
define("CLICK_FEAT_UDH",	0X0004); #  On by default
define("CLICK_FEAT_UCS2",	0X0008); #  On by default
define("CLICK_FEAT_ALPHA",	0X0010); #   Text sender_id
define("CLICK_FEAT_NUMBER",	0X0020); # Number sender_id
define("CLICK_FEAT_FLASH",	0X0200);
define("CLICK_FEAT_DELIVACK", 0X2000);
define("CLICK_FEAT_CONCAT",	0X4000); # On by default
define("CLICK_FEAT_CLASS_DEFAULTS", (CLICK_FEAT_TEXT | CLICK_FEAT_8BIT | CLICK_FEAT_UDH | CLICK_FEAT_UCS2 | CLICK_FEAT_CONCAT));


class click_api {

	var $username;
	var $password;
	var $api_id;
	var $session;
	var $from;

	var $use_session = FALSE;

	var $feat_def = CLICK_FEAT_CLASS_DEFAULTS;
	var $feat_use = FALSE;
	var $feat_bit;

	var $counter_msg = 0;

	function __construct ($u, $p, $a) {
		$this->username = $u;
		$this->password = $p;
		$this->api_id = $a;
		$this->from = FALSE;
		$this->feat_bit = $this->feat_def;
	}

	function click_api($u, $p, $a) {
		call_user_func(array(&$this, "__construct"), $u, $p, $a);
	}

	function set_from($from) {
		$this->from = $from;
	}

	function add_msg () {
		$this->counter_msg++;
	}

	function query_message() {
	}

	function session_auth () {
		$this->use_session = TRUE;
	}

	function send() {
	}

	function add_feat ($in) {
		$this->feat_bit = ($this->feat_bit | $in);
		$this->feat_use = TRUE;
	}

	function set_feat ($in, $del = FALSE) {
		if ($del === TRUE) {
			$this->feat_bit = FALSE;
			$this->feat_use = FALSE;
		} else {
			if ($in === FALSE) {
				$this->feat_use = FALSE;
				$this->feat_bit = $this->feat_def;
			} else {
				$this->feat_bit = $in;
				$this->feat_use = TRUE;
			}
		}
	}

	function is_result_error ($in) {
		return is_a($in, "click_api_eror");
	}

	function api_options() {
	}

}

class click_api_error {
	var $type;
	var $errno;
	var $errmsg;
	var $proceed;

	function __construct ($in) {
		$this->type = CLICK_ERROR;
		$this->proceed = CLICK_ERROR;

		$tmp = explode(",",$in,2);

		$this->errno = trim($tmp[0]);
		switch ($this->errno) {
			case "001":
			case "002":
			case "003":
			case "004":
			case "005":
			case "101":
			case "102":
			case "103":
			case "104":
			case "105":
			case "106":
			case "107":
			case "108":
			case "109":
			case "110":
			case "111":
			case "112":
			case "113":
			case "114":
			case "115":
			case "116":
			case "201":
			case "202":
			case "301":
			case "302":
			case "605":
			case "606":
			case "607":
				$this->errmsg = trim($tmp[1]);
				break;
			default:
				$this->errmsg = sprintf("Unknown clickatell error returned \"%s\"", trim($tmp[1]));
		}
				

	}

	function click_api_error($in) {
		call_user_func(array(&$this, "__construct"), $in);
	}
}


class click_api_status {
	var $type;
	var $statno;
	var $statmsg;
	var $statdesc;
	var $proceed;

	function __construct ($in) {
		$this->type = CLICK_OK;
		$this->proceed = CLICK_OK;

		$tmp = explode(",",$in,2);

		$this->statno = trim($tmp[0]);
		switch ($this->statno) {
			case "001":
				$this->statmsg = "Message unknown";
				$this->statdesc = "The delivering network did not recognise the message type or content";
				break;
			case "002":
				$this->statmsg = "Message queued";
				$this->statdesc = "The message could not be delivered and have been queued for attempted delivery";
				break;
			case "003":
				$this->statmsg = "Delivered";
				$this->statdesc = "Delivered to the network or gateway (delivered to the recipient)";
				break;
			case "004":
				$this->statmsg = "Received by recipient";
				$this->statdesc = "Confirmation of receipt on the handset of the recipient";
				break;
			case "005":
				$this->statmsg = "Error with message";
				$this->statdesc = "There was and error with the message, probably caused by the content of the messsage itself";
				break;
			case "006":
				$this->statmsg = "User cancelled message delivery";
				$this->statdesc = "Client cancelled the message by setting the validity period, or the message was terminated by an internal machanism";
				break;
			case "007":
				$this->statmsg = "Error delivering message";
				$this->statdesc = "An error occired delivering the message to the handset";
				break;
			case "008":
				$this->statmsg = "OK";
				$this->statdesc = "Message received by gateway";
				break;
			case "009":
				$this->statmsg = "Routing error";
				$this->statdesc = "The routing gateway or network had had an error routing the message";
				break;
			case "010":
				$this->statmsg = "Message expires";
				$this->statdesc = "Message has expires at the network due to the handset being off, or out of reach";
				break;
			case "011":
				$this->statmsg = "Message queued for later delivery";
				$this->statdesc = "Message has been queued ay the Clickatell gateway for delivery at a later time (delayed delivery)";
				break;
			case "012":
				$this->statmsg = "Out of credit";
				$this->statdesc = "The message cannot be delivered due to lack of funds in your account. Please re-purchase credits";
				break;
			default:
				$this->statmsg = sprintf("Unkown or other status suplied \"%s\"", trim($tmp[1]));
		}
	}

	function click_api_status($in) {
		call_user_func(array(&$this, "__construct"), $in);
	}
}

