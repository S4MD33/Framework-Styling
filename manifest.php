<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = array();

$manifest['name']        = __( 'Theme Styling', 'mepro' );
$manifest['description'] = __( "This extension lets you control the website visual style. Starting from predefined styles to changing specific fonts and colors across the website.", 'mepro' );
$manifest['version']     = '1.0.5';
$manifest['display']     = true;
$manifest['standalone']  = true;

$manifest['github_update'] = 'puriwp/Framework-Styling';