<?php

# ClickAPI By PPSlim
/*
	Please be aware. This class is work in progress.
	Functionality is there, however, until I have a base for each Clickatell API
	I don't know the best way of implimenting individual functions, return values
	and if or not things are supported.
	For specific support, contact me via http://www.phpclasses.org/clickapi
*/

# Make use of the Click_API class
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."click_api.php");

# We require MAIL functions. Make use of the PEAR::Mail Pear lib
require_once("Mail.php");

class smtp_click_api extends click_api {
	
	# Define the base API address to send the SMTP component to
	var $api_location = "sms@messaging.clickatell.com";

	var $msg_tree;
	var $factory_data;
	var $factory_type = "mail";
	var $factory;

	function __construct($u, $p, $a) {
		parent::__construct($u, $p, $a);
	}

	function smtp_click_api ($u, $p, $a) {
		$this->__construct($u, $p, $a);
	}

	
	function add_msg ($to, $msg, $from = FALSE) {
		if (($from === FALSE) && ($this->from === FALSE)) {
			return CLICK_ERROR;
		}
		parent::add_msg();
		
		$tmp["api_id"] = $this->api_id;
		$tmp["user"] = $this->username;
		$tmp["password"] = $this->password;
		
		$tmp["to"] = $to;
		$tmp["text"] = $msg;
		if ($from === FALSE) {
			$tmp["from"] = $this->from;
		} else {
			$tmp["from"] = $from;
		}
		if ($this->feat_use) {
			$tmp["req_feat"] = sprintf("%s", $this->feat_bit);
		}
		$this->msg_tree[] = $tmp;
	}

	function query_message ($id, $type = CLICK_MSGID_API) {
		# Unsupported in standard SMTP. Requires waiting on a callback email. Not practicle
		return CLICK_ERROR;
	}

	function session_auth () {
		return CLICK_OK;
	}

	function smtp_options(&$in, $data = NULL) {
		$this->factory_data = $data;
		if ((is_object($in)) && (is_a($in, "Mail"))) {
			$this->factory = $in;
		} elseif (is_object($in)) {
			$this->factory = $in;
		} else {
			$this->factory_type = $in;
		}
	}

	function send () {

		if (!is_a($this->factory, "Mail")) {
			$this->factory =& Mail::factory($this->factory_type, $this->factory_data);
		}

		$header["From"] = $this->api_location;
		$header["To"] = $this->api_location;
		$header["Subject"] = "NULL";

		foreach ($this->msg_tree AS $msg) {
			$tmp = "";
			foreach ($msg AS $key=>$val) {
				$tmp .= $key.":".$val."\n";
			}
			$this->factory->send($this->api_location, $header, $tmp);
		}

		parent::send();
		return CLICK_OK;
	}

}

