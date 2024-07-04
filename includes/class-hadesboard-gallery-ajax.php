<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class HADESBOARD_GALLERY_AJAX {
    public function __construct() {
        // Hook to handle AJAX requests for logged-in and non-logged-in users
        add_action('wp_ajax_get_gallery_item', [$this, 'get_gallery_item_callback']);
        add_action('wp_ajax_nopriv_get_gallery_item', [$this, 'get_gallery_item_callback']);
    }

    public function get_gallery_item_callback() {
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        // Check if post_id is valid
        if ($post_id <= 0) {
            wp_send_json_error('Invalid post ID');
        }

        // Fetch necessary data for the modal content
        $title = get_the_title($post_id);
        $content = get_post_field('post_content', $post_id);
        $gallery = get_post_meta($post_id, 'gallery', true);

        

        // Prepare HTML to return as AJAX response
        $html = '<h2>' . esc_html($title) . '</h2>';

        if ($gallery) {
            $gallery_ids = explode(',', $gallery); // Assuming IDs are stored as comma-separated values

            $html .= '<div class="gallery">';
            foreach ($gallery_ids as $attachment_id) {
                $attachment_id = intval($attachment_id);
                $attachment = get_post($attachment_id);

                if ($attachment) {
                    $attachment_type = $attachment->post_mime_type;
                    $attachment_url = wp_get_attachment_url($attachment_id);

                    if (strpos($attachment_type, 'image') !== false) {
                        $html .= '<img src="' . esc_url($attachment_url) . '" alt="' . esc_attr(get_the_title($attachment_id)) . '">';
                    } elseif (strpos($attachment_type, 'video') !== false) {
                        $html .= '<video controls><source src="' . esc_url($attachment_url) . '" type="' . esc_attr($attachment_type) . '"></video>';
                    }
                }
            }
            $html .= '</div>';
        }

        // Send JSON response
        wp_send_json_success($html);

        wp_die();
    }
}

new HADESBOARD_GALLERY_AJAX();
