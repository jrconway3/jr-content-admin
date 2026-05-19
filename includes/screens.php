<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jr_content_admin_supported_post_types() {
	return array( 'post', 'page', 'portfolio', 'game', 'character', 'review' );
}

function jr_content_admin_post_types_with_video_options() {
	return array( 'post', 'page' );
}

function jr_content_admin_post_types_with_gallery() {
	return array( 'post', 'page', 'character', 'game', 'review' );
}

function jr_content_admin_post_types_with_associated_games() {
	return array( 'post', 'page', 'review' );
}

function jr_content_admin_post_types_with_associated_characters() {
	return array( 'post', 'game', 'review' );
}

function jr_content_admin_post_types_with_ratings() {
	return array( 'review' );
}

function jr_content_admin_is_supported_screen( $screen = null ) {
	if ( ! $screen ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	}

	if ( ! $screen || empty( $screen->post_type ) ) {
		return false;
	}

	return in_array( $screen->post_type, jr_content_admin_supported_post_types(), true );
}
