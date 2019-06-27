<?php
/**
 * mailer.php
 *
 * This file handles secure mail transport using the Mailgun
 * library with Google reCAPTCHA integration.
 *
 * @author Rochelle Lewis <rlewis37@cnm.edu>
 **/

// require all composer dependencies
require_once("vendor/autoload.php");

// require mail-config.php
require_once("mail-config.php");

use Mailgun\Mailgun;
use ReCaptcha\ReCaptcha;



// verify user's reCAPTCHA input
$recaptcha = new ReCaptcha($secret);
$resp = $recaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);

try {
	//if there's a reCAPTCHA error, throw an exception
	if (!$resp->isSuccess()) {
		throw(new Exception("reCAPTCHA error!"));
	}

	/**
	 * Sanitize the inputs from the form: name, email, subject, and message.
	 * This assumes jQuery (NOT Angular!) will be AJAX submitting the form,
	 * so we're using the $_POST superglobal.
	 **/

	$name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$address = filter_input(INPUT_POST, "address", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$city = filter_input(INPUT_POST, "city", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$zip_code = filter_input(INPUT_POST, "zip_code", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
	$phone_number = filter_input(INPUT_POST, "phone_number", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$subject = filter_input(INPUT_POST, "subject", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$message = filter_input(INPUT_POST, "message", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

	// First, instantiate the SDK with your API credentials
	$mg = Mailgun::create($mailgunApiKey); // For US servers


// Now, compose and send your message.
// $mg->messages()->send($domain, $params);
	$mg->messages()->send($mailgunDomain, [
		'from'    => $email,
		'to'      => $MAIL_RECIPIENTS["email"],
		'subject' => "$name: $subject",
		'address' => "$address",
		'city' => "$city",
		'zip_code' => "$zip_code",
		'phone_number' => "$phone_number",
		'text'    => $message
	]);


	// report a successful send!
	echo "<div class=\"alert alert-success\" role=\"alert\">Email successfully sent.</div>";

} catch(Exception $exception) {
	echo "<div class=\"alert alert-danger\" role=\"alert\"><strong>Oh snap!</strong> Unable to send email: " . $exception->getMessage() . "</div>";
}