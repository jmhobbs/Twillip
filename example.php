<?php
	define( 'IS_DEV', true );

	require_once( 'twilio.php' );

	if( IS_DEV ) {
		require_once( 'twillip.php' );
		Twillip::Start();
	}

	$r = new Response();
	if( isset( $_REQUEST['Caller'] ) ) {
		$r->addSay( 'This app uses Twillip for obviously awesome reasons!' );
		$r->addPlay( 'funky-beats.mp3', array( 'loop' => 3 ) );
		$r->addRedirect( '/doesntexist.php' );
	}
	else {
		$r->addSay( 'Oh no! I didn\'t get sent a phone number! Who in blue blazes are you?' );
		$r->addSay( 'This line will generate a PHP warning now: ' . $_REQUEST['Caller'] );
	}
	$r->respond();

	if( IS_DEV ) { Twillip::End(); }
