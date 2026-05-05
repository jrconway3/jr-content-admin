<?php

if (!defined('ABSPATH')) {
    exit;
}

function jr_content_admin_register_metaboxes()
{
    foreach (jr_content_admin_post_types_with_video_options() as $post_type) {
        add_meta_box(
            'jr-content-admin-video',
            __('Video', 'jr-content-admin'),
            'jr_content_admin_render_video_metabox',
            $post_type,
            'side',
            'default'
        );

        add_meta_box(
            'jr-content-admin-options',
            __('Options', 'jr-content-admin'),
            'jr_content_admin_render_options_metabox',
            $post_type,
            'side',
            'default'
        );
    }

    foreach (jr_content_admin_post_types_with_gallery() as $post_type) {
        add_meta_box(
            'jr-content-admin-gallery',
            __('Media Gallery', 'jr-content-admin'),
            'jr_content_admin_render_gallery_metabox',
            $post_type,
            'normal',
            'default'
        );
    }

    foreach (jr_content_admin_post_types_with_associated_games() as $post_type) {
        add_meta_box(
            'jr-content-admin-associated-games',
            __('Associated Games', 'jr-content-admin'),
            'jr_content_admin_render_associated_games_metabox',
            $post_type,
            'normal',
            'default'
        );
    }

    foreach (jr_content_admin_post_types_with_associated_characters() as $post_type) {
        add_meta_box(
            'jr-content-admin-associated-characters',
            __('Associated Characters', 'jr-content-admin'),
            'jr_content_admin_render_associated_characters_metabox',
            $post_type,
            'normal',
            'default'
        );
    }

    foreach (jr_content_admin_post_types_with_ratings() as $post_type) {
        add_meta_box(
            'jr-content-admin-ratings',
            __('Ratings', 'jr-content-admin'),
            'jr_content_admin_render_ratings_metabox',
            $post_type,
            'normal',
            'default'
        );
    }
}

function jr_content_admin_get_repeatable_meta_rows($post_id, $meta_key)
{
    $rows = get_post_meta($post_id, $meta_key);

    if (!is_array($rows)) {
        return array();
    }

    return array_values(array_filter($rows, static function ($row) {
        return $row !== '' && $row !== null;
    }));
}

function jr_content_admin_decode_repeatable_item($value, $fallback = array())
{
    if (is_array($value)) {
        return array_merge($fallback, $value);
    }

    if (is_object($value)) {
        return array_merge($fallback, get_object_vars($value));
    }

    if (is_numeric($value)) {
        return array_merge($fallback, array('id' => (string) $value));
    }

    if (!is_string($value) || trim($value) === '') {
        return $fallback;
    }

    $decoded = json_decode($value, true);
    if (is_array($decoded)) {
        return array_merge($fallback, $decoded);
    }

    return $fallback;
}

function jr_content_admin_render_repeater_controls()
{
    echo '<div class="jr-content-admin-repeater-controls">';
    echo '<button type="button" class="button-link jr-content-admin-move-up">' . esc_html__('Move up', 'jr-content-admin') . '</button>';
    echo '<button type="button" class="button-link jr-content-admin-move-down">' . esc_html__('Move down', 'jr-content-admin') . '</button>';
    echo '<button type="button" class="button-link-delete jr-content-admin-remove-row">' . esc_html__('Remove item', 'jr-content-admin') . '</button>';
    echo '</div>';
}

function jr_content_admin_render_ratings_row($index, array $item)
{
    $type = isset($item['type']) ? $item['type'] : '';
    $summary = isset($item['summary']) ? $item['summary'] : '';
    $rating = isset($item['rating']) ? (string) $item['rating'] : '1';

    echo '<div class="jr-content-admin-repeater-row" data-row-index="' . esc_attr((string) $index) . '">';
    echo '<div class="jr-content-admin-grid">';
    echo '<p><label><strong>' . esc_html__('Type', 'jr-content-admin') . '</strong></label><input class="widefat" type="text" name="jr_content_admin_ratings[' . esc_attr((string) $index) . '][type]" value="' . esc_attr($type) . '"></p>';
    echo '<p><label><strong>' . esc_html__('Rating', 'jr-content-admin') . '</strong></label><select class="widefat" name="jr_content_admin_ratings[' . esc_attr((string) $index) . '][rating]">';
    for ($value = 1; $value <= 10; $value++) {
        echo '<option value="' . esc_attr((string) $value) . '"' . selected($rating, (string) $value, false) . '>' . esc_html((string) $value) . '</option>';
    }
    echo '</select></p>';
    echo '</div>';
    echo '<p><label><strong>' . esc_html__('Summary', 'jr-content-admin') . '</strong></label><textarea class="widefat" rows="4" name="jr_content_admin_ratings[' . esc_attr((string) $index) . '][summary]">' . esc_textarea($summary) . '</textarea></p>';
    jr_content_admin_render_repeater_controls();
    echo '</div>';
}

function jr_content_admin_render_related_row($field_name, $index, array $item, array $options, $label)
{
    $selected_id = isset($item['id']) ? (string) $item['id'] : '';
    $description = isset($item['description']) ? $item['description'] : '';

    echo '<div class="jr-content-admin-repeater-row" data-row-index="' . esc_attr((string) $index) . '">';
    echo '<p><label><strong>' . esc_html($label) . '</strong></label>';
    echo '<select class="widefat" name="' . esc_attr($field_name) . '[' . esc_attr((string) $index) . '][id]">';
    echo '<option value="">' . esc_html__('Select an item', 'jr-content-admin') . '</option>';
    foreach ($options as $option_id => $option_label) {
        echo '<option value="' . esc_attr((string) $option_id) . '"' . selected($selected_id, (string) $option_id, false) . '>' . esc_html($option_label) . '</option>';
    }
    echo '</select></p>';
    echo '<p><label><strong>' . esc_html__('Description', 'jr-content-admin') . '</strong></label><textarea class="widefat" rows="4" name="' . esc_attr($field_name) . '[' . esc_attr((string) $index) . '][description]">' . esc_textarea($description) . '</textarea></p>';
    jr_content_admin_render_repeater_controls();
    echo '</div>';
}

function jr_content_admin_render_gallery_image_row($index, array $item)
{
    $attachment_id = '';
    if (isset($item['id'])) {
        $attachment_id = (string) $item['id'];
    } elseif (isset($item['attachment_id'])) {
        $attachment_id = (string) $item['attachment_id'];
    }

    $title = isset($item['title']) ? $item['title'] : '';
    $caption = isset($item['caption']) ? $item['caption'] : '';
    $photo_credit = isset($item['photo_credit']) ? $item['photo_credit'] : '';
    $download_disabled = !empty($item['high_resolution_download_disabled']);
    $image_src = $attachment_id ? wp_get_attachment_image_url((int) $attachment_id, 'medium') : '';

    echo '<div class="jr-content-admin-repeater-row" data-row-index="' . esc_attr((string) $index) . '">';
    echo '<div class="jr-content-admin-media-picker">';
    echo '<input type="hidden" class="jr-content-admin-media-id" name="jr_content_admin_gallery_images[' . esc_attr((string) $index) . '][id]" value="' . esc_attr($attachment_id) . '">';
    echo '<div class="jr-content-admin-image-preview">';
    if ($image_src) {
        echo '<img src="' . esc_url($image_src) . '" alt="">';
    } else {
        echo '<span class="description">' . esc_html__('No image selected.', 'jr-content-admin') . '</span>';
    }
    echo '</div>';
    echo '<p><button type="button" class="button jr-content-admin-select-image">' . esc_html__('Select image', 'jr-content-admin') . '</button> ';
    echo '<button type="button" class="button-link jr-content-admin-clear-image">' . esc_html__('Clear image', 'jr-content-admin') . '</button></p>';
    echo '</div>';
    echo '<div class="jr-content-admin-grid">';
    echo '<p><label><strong>' . esc_html__('Title', 'jr-content-admin') . '</strong></label><input class="widefat" type="text" name="jr_content_admin_gallery_images[' . esc_attr((string) $index) . '][title]" value="' . esc_attr($title) . '"></p>';
    echo '<p><label><strong>' . esc_html__('Photo Credit', 'jr-content-admin') . '</strong></label><input class="widefat" type="text" name="jr_content_admin_gallery_images[' . esc_attr((string) $index) . '][photo_credit]" value="' . esc_attr($photo_credit) . '"></p>';
    echo '</div>';
    echo '<p><label><strong>' . esc_html__('Caption', 'jr-content-admin') . '</strong></label><textarea class="widefat" rows="4" name="jr_content_admin_gallery_images[' . esc_attr((string) $index) . '][caption]">' . esc_textarea($caption) . '</textarea></p>';
    echo '<p><label><input type="checkbox" name="jr_content_admin_gallery_images[' . esc_attr((string) $index) . '][high_resolution_download_disabled]" value="1"' . checked($download_disabled, true, false) . '> ' . esc_html__('Disable high-resolution download', 'jr-content-admin') . '</label></p>';
    jr_content_admin_render_repeater_controls();
    echo '</div>';
}

function jr_content_admin_render_gallery_video_row($index, array $item)
{
    $source = isset($item['source']) ? $item['source'] : 'youtube';
    $id = isset($item['id']) ? $item['id'] : '';
    $title = isset($item['title']) ? $item['title'] : '';
    $caption = isset($item['caption']) ? $item['caption'] : '';

    echo '<div class="jr-content-admin-repeater-row" data-row-index="' . esc_attr((string) $index) . '">';
    echo '<div class="jr-content-admin-grid">';
    echo '<p><label><strong>' . esc_html__('Source', 'jr-content-admin') . '</strong></label><select class="widefat" name="jr_content_admin_gallery_videos[' . esc_attr((string) $index) . '][source]">';
    echo '<option value="youtube"' . selected($source, 'youtube', false) . '>' . esc_html__('YouTube', 'jr-content-admin') . '</option>';
    echo '<option value="vimeo"' . selected($source, 'vimeo', false) . '>' . esc_html__('Vimeo', 'jr-content-admin') . '</option>';
    echo '</select></p>';
    echo '<p><label><strong>' . esc_html__('Video ID', 'jr-content-admin') . '</strong></label><input class="widefat" type="text" name="jr_content_admin_gallery_videos[' . esc_attr((string) $index) . '][id]" value="' . esc_attr($id) . '"></p>';
    echo '</div>';
    echo '<p><label><strong>' . esc_html__('Title', 'jr-content-admin') . '</strong></label><input class="widefat" type="text" name="jr_content_admin_gallery_videos[' . esc_attr((string) $index) . '][title]" value="' . esc_attr($title) . '"></p>';
    echo '<p><label><strong>' . esc_html__('Caption', 'jr-content-admin') . '</strong></label><textarea class="widefat" rows="4" name="jr_content_admin_gallery_videos[' . esc_attr((string) $index) . '][caption]">' . esc_textarea($caption) . '</textarea></p>';
    jr_content_admin_render_repeater_controls();
    echo '</div>';
}

function jr_content_admin_render_repeater_wrapper($title, $description, $rows_html, $add_label, $template_id)
{
    echo '<div class="jr-content-admin-section">';
    echo '<h3>' . esc_html($title) . '</h3>';
    if ($description !== '') {
        echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '<div class="jr-content-admin-repeater" data-template-id="' . esc_attr($template_id) . '" data-next-index="' . esc_attr((string) count($rows_html)) . '">';
    echo '<div class="jr-content-admin-repeater-rows">';
    foreach ($rows_html as $row_html) {
        echo $row_html;
    }
    echo '</div>';
    echo '<p><button type="button" class="button jr-content-admin-add-row">' . esc_html($add_label) . '</button></p>';
    echo '</div>';
    echo '</div>';
}

function jr_content_admin_render_nonce()
{
    wp_nonce_field('jr_content_admin_save', 'jr_content_admin_nonce');
}

function jr_content_admin_render_video_metabox($post)
{
    jr_content_admin_render_nonce();

    $video_src = get_post_meta($post->ID, 'jrblog_page_video_src', true);
    $video_id = get_post_meta($post->ID, 'jrblog_page_video_id', true);
    $video_list = get_post_meta($post->ID, 'jrblog_page_video_list', true);

    echo '<p>' . esc_html__('This metabox replaces the old Bebop video fields for posts and pages.', 'jr-content-admin') . '</p>';
    echo '<p><label for="jrblog_page_video_src"><strong>' . esc_html__('Video Source', 'jr-content-admin') . '</strong></label></p>';
    echo '<select class="widefat" id="jrblog_page_video_src" name="jrblog_page_video_src">';
    echo '<option value=""' . selected($video_src, '', false) . '>' . esc_html__('None', 'jr-content-admin') . '</option>';
    echo '<option value="youtube"' . selected($video_src, 'youtube', false) . '>' . esc_html__('YouTube', 'jr-content-admin') . '</option>';
    echo '<option value="vimeo"' . selected($video_src, 'vimeo', false) . '>' . esc_html__('Vimeo', 'jr-content-admin') . '</option>';
    echo '</select>';
    echo '<p><label for="jrblog_page_video_id"><strong>' . esc_html__('Video ID', 'jr-content-admin') . '</strong></label></p>';
    echo '<input class="widefat" type="text" id="jrblog_page_video_id" name="jrblog_page_video_id" value="' . esc_attr($video_id) . '">';
    echo '<p><label for="jrblog_page_video_list"><strong>' . esc_html__('Playlist ID', 'jr-content-admin') . '</strong></label></p>';
    echo '<input class="widefat" type="text" id="jrblog_page_video_list" name="jrblog_page_video_list" value="' . esc_attr($video_list) . '">';
}

function jr_content_admin_render_options_metabox($post)
{
    jr_content_admin_render_nonce();

    $disable_excerpt = get_post_meta($post->ID, 'jrblog_page_disable_excerpt', true);
    $featured_position = get_post_meta($post->ID, 'jrblog_page_featured_position', true);

    echo '<p>' . esc_html__('This metabox controls the existing featured media and excerpt behavior.', 'jr-content-admin') . '</p>';
    echo '<p><label><input type="checkbox" name="jrblog_page_disable_excerpt" value="1" ' . checked(!empty($disable_excerpt), true, false) . '> ' . esc_html__('Disable text excerpt', 'jr-content-admin') . '</label></p>';
    echo '<p><label for="jrblog_page_featured_position"><strong>' . esc_html__('Featured Image Position', 'jr-content-admin') . '</strong></label></p>';
    echo '<select class="widefat" id="jrblog_page_featured_position" name="jrblog_page_featured_position">';
    echo '<option value="full"' . selected($featured_position, 'full', false) . '>' . esc_html__('Full Width', 'jr-content-admin') . '</option>';
    echo '<option value="left"' . selected($featured_position, 'left', false) . '>' . esc_html__('Float Left', 'jr-content-admin') . '</option>';
    echo '<option value="right"' . selected($featured_position, 'right', false) . '>' . esc_html__('Float Right', 'jr-content-admin') . '</option>';
    echo '<option value="gantry"' . selected($featured_position, 'gantry', false) . '>' . esc_html__('Use Gantry Default', 'jr-content-admin') . '</option>';
    echo '</select>';
}

function jr_content_admin_render_gallery_metabox($post)
{
    jr_content_admin_render_nonce();

    $image_rows = array();
    foreach (jr_content_admin_get_repeatable_meta_rows($post->ID, 'gallery_images') as $index => $row) {
        ob_start();
        jr_content_admin_render_gallery_image_row($index, jr_content_admin_decode_repeatable_item($row));
        $image_rows[] = ob_get_clean();
    }

    $video_rows = array();
    foreach (jr_content_admin_get_repeatable_meta_rows($post->ID, 'gallery_videos') as $index => $row) {
        ob_start();
        jr_content_admin_render_gallery_video_row($index, jr_content_admin_decode_repeatable_item($row, array('source' => 'youtube')));
        $video_rows[] = ob_get_clean();
    }

    jr_content_admin_render_repeater_wrapper(
        __('Images', 'jr-content-admin'),
        __('Manage image gallery entries while preserving the current repeated-meta storage format.', 'jr-content-admin'),
        $image_rows,
        __('Add Image', 'jr-content-admin'),
        'tmpl-jr-content-admin-gallery-image'
    );

    jr_content_admin_render_repeater_wrapper(
        __('Videos', 'jr-content-admin'),
        __('Manage video gallery entries while preserving the current repeated-meta storage format.', 'jr-content-admin'),
        $video_rows,
        __('Add Video', 'jr-content-admin'),
        'tmpl-jr-content-admin-gallery-video'
    );

    echo '<script type="text/html" id="tmpl-jr-content-admin-gallery-image">';
    jr_content_admin_render_gallery_image_row('__INDEX__', array());
    echo '</script>';

    echo '<script type="text/html" id="tmpl-jr-content-admin-gallery-video">';
    jr_content_admin_render_gallery_video_row('__INDEX__', array('source' => 'youtube'));
    echo '</script>';
}

function jr_content_admin_render_associated_games_metabox($post)
{
    jr_content_admin_render_nonce();

    $options = array();
    foreach (get_posts(array(
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'post_type' => 'game',
        'post_status' => array('draft', 'publish'),
    )) as $item) {
        $options[$item->ID] = $item->post_title;
    }

    $rows = array();
    foreach (jr_content_admin_get_repeatable_meta_rows($post->ID, 'associated_games') as $index => $row) {
        ob_start();
        jr_content_admin_render_related_row('jr_content_admin_associated_games', $index, jr_content_admin_decode_repeatable_item($row), $options, __('Game', 'jr-content-admin'));
        $rows[] = ob_get_clean();
    }

    jr_content_admin_render_repeater_wrapper(
        __('Associated Games', 'jr-content-admin'),
        __('Attach game references and optional descriptions using the current legacy-compatible storage format.', 'jr-content-admin'),
        $rows,
        __('Add Game', 'jr-content-admin'),
        'tmpl-jr-content-admin-associated-game'
    );

    echo '<script type="text/html" id="tmpl-jr-content-admin-associated-game">';
    jr_content_admin_render_related_row('jr_content_admin_associated_games', '__INDEX__', array(), $options, __('Game', 'jr-content-admin'));
    echo '</script>';
}

function jr_content_admin_render_associated_characters_metabox($post)
{
    jr_content_admin_render_nonce();

    $options = array();
    foreach (get_posts(array(
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'post_type' => 'character',
        'post_status' => array('draft', 'publish'),
    )) as $item) {
        $options[$item->ID] = $item->post_title;
    }

    $rows = array();
    foreach (jr_content_admin_get_repeatable_meta_rows($post->ID, 'associated_characters') as $index => $row) {
        ob_start();
        jr_content_admin_render_related_row('jr_content_admin_associated_characters', $index, jr_content_admin_decode_repeatable_item($row), $options, __('Character', 'jr-content-admin'));
        $rows[] = ob_get_clean();
    }

    jr_content_admin_render_repeater_wrapper(
        __('Associated Characters', 'jr-content-admin'),
        __('Attach character references and optional descriptions using the current legacy-compatible storage format.', 'jr-content-admin'),
        $rows,
        __('Add Character', 'jr-content-admin'),
        'tmpl-jr-content-admin-associated-character'
    );

    echo '<script type="text/html" id="tmpl-jr-content-admin-associated-character">';
    jr_content_admin_render_related_row('jr_content_admin_associated_characters', '__INDEX__', array(), $options, __('Character', 'jr-content-admin'));
    echo '</script>';
}

function jr_content_admin_render_ratings_metabox($post)
{
    jr_content_admin_render_nonce();

    $rows = array();
    foreach (jr_content_admin_get_repeatable_meta_rows($post->ID, 'ratings') as $index => $row) {
        ob_start();
        jr_content_admin_render_ratings_row($index, jr_content_admin_decode_repeatable_item($row, array('rating' => '1')));
        $rows[] = ob_get_clean();
    }

    jr_content_admin_render_repeater_wrapper(
        __('Ratings', 'jr-content-admin'),
        __('Manage repeated rating entries while preserving the current repeated-meta storage format.', 'jr-content-admin'),
        $rows,
        __('Add Rating', 'jr-content-admin'),
        'tmpl-jr-content-admin-rating'
    );

    echo '<script type="text/html" id="tmpl-jr-content-admin-rating">';
    jr_content_admin_render_ratings_row('__INDEX__', array('rating' => '1'));
    echo '</script>';
}
