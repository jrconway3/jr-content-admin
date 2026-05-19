<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jr_content_admin_enqueue_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	if ( ! jr_content_admin_is_supported_screen() ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_style(
		'jr-content-admin',
		JR_CONTENT_ADMIN_URL . 'assets/css/admin.css',
		array(),
		JR_CONTENT_ADMIN_VERSION
	);

	wp_enqueue_script(
		'jr-content-admin',
		JR_CONTENT_ADMIN_URL . 'assets/js/admin.js',
		array( 'jquery' ),
		JR_CONTENT_ADMIN_VERSION,
		true
	);

	wp_localize_script(
		'jr-content-admin',
		'jrContentAdmin',
		array(
			'selectImage'  => __( 'Select image', 'jr-content-admin' ),
			'useImage'     => __( 'Use image', 'jr-content-admin' ),
			'emptyPreview' => __( 'No image selected.', 'jr-content-admin' ),
			'removeItem'   => __( 'Remove item', 'jr-content-admin' ),
			'moveUp'       => __( 'Move up', 'jr-content-admin' ),
			'moveDown'     => __( 'Move down', 'jr-content-admin' ),
		)
	);
}
