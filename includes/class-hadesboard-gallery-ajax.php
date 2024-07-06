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
        add_action('wp_ajax_toggle_like_gallery_item', [$this, 'toggle_like_gallery_item']);
        add_action('wp_ajax_nopriv_toggle_like_gallery_item', [$this, 'toggle_like_gallery_item']);
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
                $liked_count = get_post_meta($post_id, 'like_count', true);

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
                            $attachment_title = esc_attr(get_the_title($attachment_id));
                            $attachment_full = wp_get_attachment_image_src($attachment_id, 'full');

                            if (strpos($attachment_type, 'image') !== false) {
                                // Link to open image in LightGallery
                                $html .= '<a href="' . esc_url($attachment_full[0]) .'" data-fancybox="gallery"  data-caption="' . $attachment_title . '">';
                                $html .= '<img src="' . esc_url($attachment_url) . '" alt="' . $attachment_title . '">';
                                $html .= '</a>';
                            } elseif (strpos($attachment_type, 'video') !== false) {
                                // Video handling for LightGallery
                                $html .= '<a href="' . esc_url($attachment_url) . '" data-fancybox="gallery" data-caption="' . $attachment_title . '">';
                                $html .= '<video controls><source src="' . esc_url($attachment_url) . '" type="' . esc_attr($attachment_type) . '"></video>';
                                $html .= '</a>';
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
                    'like_count' => $liked_count ? $liked_count : 0,
                    'post_id' => $post_id ? $post_id : 0
                ]);
            }
            wp_reset_postdata();
        } else {
            wp_send_json_error('Post not found');
        }

        wp_die();
    }

    function toggle_like_gallery_item() {
        if (!isset($_POST['post_id'])) {
            wp_send_json_error('Invalid post ID.');
            return;
        }

        $post_id = intval($_POST['post_id']);
        $like_count = get_post_meta($post_id, 'like_count', true);
        if (!$like_count) {
            $like_count = 0;
        }

        $liked = false;
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $liked_posts = get_user_meta($user_id, '_liked_posts', true);
            if (!$liked_posts) {
                $liked_posts = [];
            }

            if (in_array($post_id, $liked_posts)) {
                // Unlike the post
                $liked_posts = array_diff($liked_posts, [$post_id]);
                $like_count--;
                $liked = false;
            } else {
                // Like the post
                $liked_posts[] = $post_id;
                $like_count++;
                $liked = true;
            }

            update_user_meta($user_id, '_liked_posts', $liked_posts);
        } else {
            // Handle non-logged-in users
            if (isset($_COOKIE['liked_posts'])) {
                $liked_posts = json_decode(stripslashes($_COOKIE['liked_posts']), true);
            } else {
                $liked_posts = [];
            }

            if (in_array($post_id, $liked_posts)) {
                // Unlike the post
                $liked_posts = array_diff($liked_posts, [$post_id]);
                $like_count--;
                $liked = false;
            } else {
                // Like the post
                $liked_posts[] = $post_id;
                $like_count++;
                $liked = true;
            }

            setcookie('liked_posts', json_encode($liked_posts), time() + (10 * 365 * 24 * 60 * 60), '/');
        }

        update_post_meta($post_id, 'like_count', $like_count);

        wp_send_json_success([
            'like_count' => $like_count,
            'liked' => $liked,
        ]);
    }

}

new HADESBOARD_GALLERY_AJAX();
