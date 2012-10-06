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

# We require XML & HTML functions. Make use of the XML_Tree & HTTP_Request Pear libs
require_once("XML/Tree.php");
require_once("HTTP/Request.php");

class xml_click_api extends click_api {
	
	# Define the base API address to send the XML component to
	var $api_location = "http://api.clickatell.com/xml/xml";

	var $auth_tree;
	var $xml_tree;
	var $xml_pipe;
	var $query_tree;

	function __construct($u, $p, $a) {
		parent::__construct($u, $p, $a);
		$this->xml_tree =& new XML_Tree;
		$this->xml_pipe =& $this->prep_xml_tree($this->xml_tree);
	}

	function xml_click_api ($u, $p, $a) {
		$this->__construct($u, $p, $a);
	}

	function &prep_xml_tree (&$tree) {
		$new =& $tree->addRoot("clickAPI");
		return $new;
	}

	function add_msg ($to, $msg, $from = FALSE) {
		if (($from === FALSE) && ($this->from === FALSE)) {
			return CLICK_ERROR;
		}
		parent::add_msg();
		$tmp =& $this->xml_pipe->addChild("sendMsg");
		if ($this->use_session) {
			$tmp->addChild("session_id", $this->session);
		} else {
			$tmp->addChild("api_id", $this->api_id);
			$tmp->addChild("user", $this->username);
			$tmp->addChild("password", $this->password);
		}
		$tmp->addChild("to", $to);
		$tmp->addChild("text", $msg);
		if ($from === FALSE) {
			$tmp->addChild("from", $this->from);
		} else {
			$tmp->addChild("from", $from);
		}
		if ($this->feat_use) {
			$tmp->addChild("req_feat", sprintf("%s", $this->feat_bit));
		}
	}

	function query_message ($id, $type = CLICK_MSGID_API) {
		$this->query_tree = &new XML_Tree;
		$pipe =& $this->prep_xml_tree($this->query_tree);
		$qu =& $pipe->addChild("queryMsg");
		if ($this->use_session) {
			$qu->addChild("session_id", $this->session);
		} else {
			$qu->addChild("api_id", $this->api_id);
			$qu->addChild("user", $this->username);
			$qu->addChild("password", $this->password);
		}
		$qu->addChild($type, $id);
		$xml = $this->query_tree->get();
		unset($tmp, $pipe, $qu);

		$http =& new HTTP_Request($this->api_location);
		$http->setMethod(HTTP_REQUEST_METHOD_POST);
		$http->addPostData("data", $xml);
		if (PEAR::isError($http->sendRequest())) {
			return CLICK_ERROR;
		}
		$tree =& new XML_Tree;
		$p1 =& $tree->getTreeFromString($http->getResponseBody());
		$this->query_return =& $tree;
		$p2 =& $p1->getElement(array(1,3));
		if (strtolower($p2->name) == "fault") {
			$err = &new click_api_error($p2->content);
			return $err;
		}
		return $p2->content;
	}

	function session_auth () {
		if ($this->use_session) {
			return CLICK_OK;
		}
		$tree =& new XML_Tree;
		$p1 =& $this->prep_xml_tree($tree);
		$p2 =& $p1->addChild("auth");
		$p2->addChild("api_id", $this->api_id);
		$p2->addChild("user", $this->username);
		$p2->addChild("password", $this->password);
		$xml = $tree->get();
		unset($p2,$p1,$tree);

		$http =& new HTTP_Request($this->api_location);
		$http->setMethod(HTTP_REQUEST_METHOD_POST);
		$http->addPostData("data", $xml);
		if (PEAR::isError($http->sendRequest())) {
			return CLICK_ERROR;
		}
		$tree =& new XML_Tree;
		$p1 =& $tree->getTreeFromString($http->getResponseBody());
		$this->auth_tree =& $tree;
		$p2 =& $p1->getElement(array(1,1));
		if (strtolower($p2->name) == "fault") {
			$err = &new click_api_error($p2->content);
			return $err;
		}
		$this->session = $p2->content;
		unset($tree,$p1,$p2,$http);
		parent::session_auth();
		return CLICK_OK;
	}

	function send () {
		$xml = $this->xml_tree->get();
		$http =& new HTTP_Request($this->api_location);
		$http->setMethod(HTTP_REQUEST_METHOD_POST);
		$http->addPostData("data", $xml);
		if (PEAR::isError($http->sendRequest())) {
			return CLICK_ERROR;
		}
		parent::send();
		unset($this->xml_tree);
		$this->xml_tree =& new XML_Tree;
		$this->xml_pipe =& $this->prep_xml_tree($this->xml_tree);
		return CLICK_OK;
	}

}

