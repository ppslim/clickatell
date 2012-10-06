<?php

/* Example use of the click_api - makeing use of the XML library */

# Include the XML library
require_once("clickapi/smtp_click_api.php");


# Should we use a session or include the username and password in each message
# Sessions will allow us to check our username and password are OK before we start
# In this example, yes.
define("CLICK_SESSION", TRUE);

# Define the username, password and XML API id
define("CLICK_USERNAME", "test");
define("CLICK_PASSWORD", "abc123");
define("CLICK_API_ID", "123456");

# Create a new instance of the XML API. All API's are prefixed with the API type, with _click_api on the end
$click = &new smtp_click_api(CLICK_USERNAME, CLICK_PASSWORD, CLICK_API_ID);

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

# SMTP uses Pear::Mail for Mail::Factory
# As such, we need to setup the required parameters for that lib
# This is done in two ways.
#   1: Pass the name of the mail driver and the options needed for that driver
#   2: Provide a object through which mail is sent.
#        The object must provide a send() method which accepts 3 params
#        To address, array of headers (key names for header names), message content
# If this function isn't called, we use the "mail" (PHP mail function) type factory by default
$click->smtp_options("mail");

# Send them
$err =& $click->send();

# Check for errors
if ($click->is_result_error($err)) {
	$log[] = sprintf("Error occured (%s):\n  %s", $err->errno, $err->errmsg);
} else {
	$log[] = "Messages sent OK";
}

