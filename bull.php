<?php
/*
 * Plugin Name: Bull
 *
 * code inspired by askimet
 */

// FROM ASKIMET : Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'BULL_PLUGIN_DIR', plugin_dir_path( __FILE__ ));

require_once(BULL_PLUGIN_DIR."class.bull.php");

//on init run bull
add_action( 'init', array( 'Bull', 'init' ) );

