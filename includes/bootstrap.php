<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jr_content_admin_bootstrap() {
	add_action( 'add_meta_boxes', 'jr_content_admin_register_metaboxes' );
	add_action( 'admin_enqueue_scripts', 'jr_content_admin_enqueue_assets' );
	add_action( 'save_post', 'jr_content_admin_save_post', 10, 2 );
}
