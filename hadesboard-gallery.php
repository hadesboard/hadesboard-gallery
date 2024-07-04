<?php
/*
Plugin Name: Hadesboard Gallery
Plugin URI: https://hadesboard.com
Description: A custom gallery plugin with masonry layout.
Version: 1.0
Author: Mohamad Gandomi
Author URI: https://hadesboard.com
Text Domain: hadesboard-gallery
Domain Path: /languages
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('HADESBOARD_GALLERY_PATH', plugin_dir_path(__FILE__));
define('HADESBOARD_GALLERY_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once HADESBOARD_GALLERY_PATH . 'includes/class-hadesboard-gallery.php';

// Initialize the plugin
function hadesboard_gallery_init() {
    $plugin = new Hadesboard_Gallery();
    load_plugin_textdomain( 'hadesboard-gallery', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action('plugins_loaded', 'hadesboard_gallery_init');