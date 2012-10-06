<?php

/* Example use of the click_api - makeing use of the XML library */

# Include the XML library
require_once("clickapi/xml_click_api.php");


# Should we use a session or include the username and password in each message
# Sessions will allow us to check our username and password are OK before we start
# In this example, yes.
define("CLICK_SESSION", TRUE);

# Define the username, password and XML API id
define("CLICK_USERNAME", "test");
define("CLICK_PASSWORD", "abc123");
define("CLICK_API_ID", "123456");

# Create a new instance of the XML API. All API's are prefixed with the API type, with _click_api on the end
$click = &new xml_click_api(CLICK_USERNAME, CLICK_PASSWORD, CLICK_API_ID);

if (CLICK_SESSION) {
	if ($click->session_auth() === CLICK_ERROR) {
		die("OOOPS - Session authentication failed. password problems");
	}
	# If we get here, all messages added from this point use the session
} else {
	# If you don't create a session or messages added before the session creation, all use the username and password
}

# Lets added a sample message using the add_msg() function
# Function accepts a to, message and from argument
# From is optional, but returns an error if a default from address has not been set using the set_from() function

$click->add_msg("4477700123456", "A sample message from the establishment", "4477700123456");

# Returns an error, no from address or default given
@$click->add_msg("4477701123456", "A sample message from the establishment");

# Lets set one and try again
@$click->set_from("4477700123456");
@$click->add_msg("4477745123456", "A sample message from the establishment");

# Keep a log
$log[] = "Sending ".$click->counter_msg." message(s)";

# Send them
$err =& $click->send();

# Check for errors
if ($click->is_result_error($err)) {
	$log[] = sprintf("Error occured (%s):\n  %s", $err->errno, $err->errmsg);
} else {
	$log[] = "Messages sent OK";
}

