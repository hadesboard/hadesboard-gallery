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

        // Query to get the specific post
        $query = new WP_Query(array(
            'post_type' => 'hadesboard_gallery',
            'p' => $post_id,
        ));

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $post_id = get_the_ID();
                $title = get_the_title();
                $content = apply_filters('the_content', get_the_content());
                $gallery = get_post_meta($post_id, 'gallery', true);

                // Prepare HTML to return as AJAX response
                $html = '<h2>' . esc_html($title) . '</h2>';
                $html .= '<div class="hb-post-content">' . $content . '</div>';

                if ($gallery) {
                    $gallery_ids = explode(',', $gallery); // Assuming IDs are stored as comma-separated values

                    $html .= '<div class="hb-gallery">';
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

                // Get next and previous post IDs
                $prev_post_id = get_adjacent_post(false, '', false);
                $next_post_id = get_adjacent_post(false, '', true);

                // Send JSON response
                wp_send_json_success([
                    'html' => $html,
                    'prev_post_id' => $prev_post_id ? $prev_post_id->ID : null,
                    'next_post_id' => $next_post_id ? $next_post_id->ID : null,
                ]);
            }
            wp_reset_postdata();
        } else {
            wp_send_json_error('Post not found');
        }

        wp_die();
    }

}

new HADESBOARD_GALLERY_AJAX();
