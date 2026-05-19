<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jr_content_admin_save_post( $post_id, $post ) {
	if ( ! isset( $_POST['jr_content_admin_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jr_content_admin_nonce'] ) ), 'jr_content_admin_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! in_array( $post->post_type, jr_content_admin_supported_post_types(), true ) ) {
		return;
	}

	jr_content_admin_save_post_page_fields( $post_id, $post->post_type );
	jr_content_admin_save_gallery_fields( $post_id, $post->post_type );
	jr_content_admin_save_associated_fields( $post_id, $post->post_type );
	jr_content_admin_save_ratings( $post_id, $post->post_type );
}

function jr_content_admin_save_post_page_fields( $post_id, $post_type ) {
	if ( ! in_array( $post_type, jr_content_admin_post_types_with_video_options(), true ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in jr_content_admin_save_post().
	$video_src         = isset( $_POST['jrblog_page_video_src'] ) ? jr_content_core_sanitize_video_source( wp_unslash( $_POST['jrblog_page_video_src'] ) ) : '';
	$video_id          = isset( $_POST['jrblog_page_video_id'] ) ? jr_content_core_sanitize_string( wp_unslash( $_POST['jrblog_page_video_id'] ) ) : '';
	$video_list        = isset( $_POST['jrblog_page_video_list'] ) ? jr_content_core_sanitize_string( wp_unslash( $_POST['jrblog_page_video_list'] ) ) : '';
	$disable_excerpt   = ! empty( $_POST['jrblog_page_disable_excerpt'] );
	$featured_position = isset( $_POST['jrblog_page_featured_position'] ) ? jr_content_core_sanitize_feature_position( wp_unslash( $_POST['jrblog_page_featured_position'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	update_post_meta( $post_id, 'jrblog_page_video_src', $video_src );
	update_post_meta( $post_id, 'jrblog_page_video_id', $video_id );
	update_post_meta( $post_id, 'jrblog_page_video_list', $video_list );
	update_post_meta( $post_id, 'jrblog_page_disable_excerpt', (bool) $disable_excerpt );
	update_post_meta( $post_id, 'jrblog_page_featured_position', $featured_position );
}

function jr_content_admin_replace_repeatable_meta( $post_id, $meta_key, array $values ) {
	delete_post_meta( $post_id, $meta_key );

	foreach ( $values as $value ) {
		if ( '' === $value || null === $value ) {
			continue;
		}

		add_post_meta( $post_id, $meta_key, $value );
	}
}

function jr_content_admin_encode_item( array $item ) {
	$encoded = wp_json_encode( $item );

	return is_string( $encoded ) ? $encoded : '';
}

function jr_content_admin_save_gallery_fields( $post_id, $post_type ) {
	if ( ! in_array( $post_type, jr_content_admin_post_types_with_gallery(), true ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in jr_content_admin_save_post().
	$image_rows = isset( $_POST['jr_content_admin_gallery_images'] ) && is_array( $_POST['jr_content_admin_gallery_images'] )
		? wp_unslash( $_POST['jr_content_admin_gallery_images'] )
		: array();
	$video_rows = isset( $_POST['jr_content_admin_gallery_videos'] ) && is_array( $_POST['jr_content_admin_gallery_videos'] )
		? wp_unslash( $_POST['jr_content_admin_gallery_videos'] )
		: array();
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	$image_values = array();
	foreach ( $image_rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$attachment_id = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
		$title         = isset( $row['title'] ) ? jr_content_core_sanitize_string( $row['title'] ) : '';
		$caption       = isset( $row['caption'] ) ? sanitize_textarea_field( $row['caption'] ) : '';
		$photo_credit  = isset( $row['photo_credit'] ) ? jr_content_core_sanitize_string( $row['photo_credit'] ) : '';
		$disabled      = ! empty( $row['high_resolution_download_disabled'] );

		if ( $attachment_id <= 0 && '' === $title && '' === $caption && '' === $photo_credit ) {
			continue;
		}

		$item = array(
			'id'                                => (string) $attachment_id,
			'title'                             => $title,
			'caption'                           => $caption,
			'photo_credit'                      => $photo_credit,
			'high_resolution_download_disabled' => $disabled ? '1' : '',
		);

		$image_values[] = jr_content_admin_encode_item( $item );
	}

	$video_values = array();
	foreach ( $video_rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$source   = isset( $row['source'] ) ? jr_content_core_sanitize_video_source( $row['source'] ) : 'youtube';
		$video_id = isset( $row['id'] ) ? jr_content_core_sanitize_string( $row['id'] ) : '';
		$title    = isset( $row['title'] ) ? jr_content_core_sanitize_string( $row['title'] ) : '';
		$caption  = isset( $row['caption'] ) ? sanitize_textarea_field( $row['caption'] ) : '';

		if ( '' === $video_id && '' === $title && '' === $caption ) {
			continue;
		}

		$item = array(
			'source'  => $source ? $source : 'youtube',
			'id'      => $video_id,
			'title'   => $title,
			'caption' => $caption,
		);

		$video_values[] = jr_content_admin_encode_item( $item );
	}

	jr_content_admin_replace_repeatable_meta( $post_id, 'gallery_images', $image_values );
	jr_content_admin_replace_repeatable_meta( $post_id, 'gallery_videos', $video_values );
}

function jr_content_admin_save_associated_fields( $post_id, $post_type ) {
	if ( in_array( $post_type, jr_content_admin_post_types_with_associated_games(), true ) ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in jr_content_admin_save_post().
		$game_rows = isset( $_POST['jr_content_admin_associated_games'] ) && is_array( $_POST['jr_content_admin_associated_games'] )
			? wp_unslash( $_POST['jr_content_admin_associated_games'] )
			: array();
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$game_values = array();

		foreach ( $game_rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$id          = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
			$description = isset( $row['description'] ) ? sanitize_textarea_field( $row['description'] ) : '';

			if ( $id <= 0 ) {
				continue;
			}

			$game_values[] = jr_content_admin_encode_item(
				array(
					'id'          => (string) $id,
					'description' => $description,
				)
			);
		}

		jr_content_admin_replace_repeatable_meta( $post_id, 'associated_games', $game_values );
	}

	if ( in_array( $post_type, jr_content_admin_post_types_with_associated_characters(), true ) ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in jr_content_admin_save_post().
		$character_rows = isset( $_POST['jr_content_admin_associated_characters'] ) && is_array( $_POST['jr_content_admin_associated_characters'] )
			? wp_unslash( $_POST['jr_content_admin_associated_characters'] )
			: array();
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$character_values = array();

		foreach ( $character_rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$id          = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
			$description = isset( $row['description'] ) ? sanitize_textarea_field( $row['description'] ) : '';

			if ( $id <= 0 ) {
				continue;
			}

			$character_values[] = jr_content_admin_encode_item(
				array(
					'id'          => (string) $id,
					'description' => $description,
				)
			);
		}

		jr_content_admin_replace_repeatable_meta( $post_id, 'associated_characters', $character_values );
	}
}

function jr_content_admin_save_ratings( $post_id, $post_type ) {
	if ( ! in_array( $post_type, jr_content_admin_post_types_with_ratings(), true ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in jr_content_admin_save_post().
	$rows = isset( $_POST['jr_content_admin_ratings'] ) && is_array( $_POST['jr_content_admin_ratings'] )
		? wp_unslash( $_POST['jr_content_admin_ratings'] )
		: array();
	// phpcs:enable WordPress.Security.NonceVerification.Missing
	$values = array();

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$type    = isset( $row['type'] ) ? jr_content_core_sanitize_string( $row['type'] ) : '';
		$summary = isset( $row['summary'] ) ? sanitize_textarea_field( $row['summary'] ) : '';
		$rating  = isset( $row['rating'] ) ? max( 1, min( 10, absint( $row['rating'] ) ) ) : 1;

		if ( '' === $type && '' === $summary ) {
			continue;
		}

		$values[] = jr_content_admin_encode_item(
			array(
				'type'    => $type,
				'summary' => $summary,
				'rating'  => $rating,
			)
		);
	}

	jr_content_admin_replace_repeatable_meta( $post_id, 'ratings', $values );
}
