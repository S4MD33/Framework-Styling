<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = array();

$manifest['name']        = __( 'Theme Styling', 'fw' );
$manifest['description'] = __( "This extension lets you control the website visual style. Starting from predefined styles to changing specific fonts and colors across the website.", 'fw' );
$manifest['version']     = '1.1.6';
$manifest['display']     = true;
$manifest['standalone']  = true;

$manifest['github_update'] = 'puriwp/Framework-Styling';
