<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function testfunction1() {

  $asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php');

  wp_enqueue_script(
    'chatgpt-integration-script',
    plugins_url( 'build/index.js', __FILE__ ),
    array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
    filemtime(plugin_dir_path(__FILE__) . 'src/index.js'),
    true 
);

}
add_action( 'admin_enqueue_scripts', 'testfunction1' );

