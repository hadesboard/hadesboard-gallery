<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class HADESBOARD_GALLERY_CPT {

    public function __construct() {
        add_action('init', array($this, 'register_gallery_post_type'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function register_gallery_post_type() {
        $labels = array(
            'name' => __('Galleries', 'hadesboard-gallery'),
            'singular_name' => __('Gallery', 'hadesboard-gallery'),
            'add_new' => __('Add New', 'hadesboard-gallery'),
            'add_new_item' => __('Add New Gallery', 'hadesboard-gallery'),
            'edit_item' => __('Edit Gallery', 'hadesboard-gallery'),
            'new_item' => __('New Gallery', 'hadesboard-gallery'),
            'view_item' => __('View Gallery', 'hadesboard-gallery'),
            'search_items' => __('Search Galleries', 'hadesboard-gallery'),
            'not_found' => __('No galleries found', 'hadesboard-gallery'),
            'not_found_in_trash' => __('No galleries found in Trash', 'hadesboard-gallery'),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'thumbnail'),
            'show_in_rest' => true,
            'taxonomies' => array('category', 'post_tag'),
            'menu_icon' => 'dashicons-format-gallery',
        );

        register_post_type('hadesboard_gallery', $args);

        // Add meta boxes
        add_action('add_meta_boxes_hadesboard_gallery', array($this, 'add_meta_boxes'));

        // Save meta box data
        add_action('save_post_hadesboard_gallery', array($this, 'save_meta_boxes'), 10, 2);
    }

    public function enqueue_scripts() {
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('hadesboard-gallery-admin', HADESBOARD_GALLERY_URL . 'assets/js/hadesboard-gallery-admin.js', array('jquery'), null, true);
        wp_enqueue_style('hadesboard-gallery-admin', HADESBOARD_GALLERY_URL . 'assets/css/hadesboard-gallery-admin.css');
    }

    public function add_meta_boxes($post) {
        add_meta_box('hadesboard-gallery-meta', __('Gallery', 'hadesboard-gallery'), array($this, 'render_gallery_meta_box'), 'hadesboard_gallery', 'normal', 'high');
        add_meta_box('hadesboard-gallery-image-cover-meta', __('Image Cover', 'hadesboard-gallery'), array($this, 'render_image_cover_meta_box'), 'hadesboard_gallery', 'normal', 'default');
        add_meta_box('hadesboard-gallery-video-cover-meta', __('Video Cover', 'hadesboard-gallery'), array($this, 'render_video_cover_meta_box'), 'hadesboard_gallery', 'normal', 'default');
        add_meta_box('hadesboard-gallery-like-meta', __('Like Count', 'hadesboard-gallery'), array($this, 'render_like_meta_box'), 'hadesboard_gallery', 'normal', 'default');
    }

    public function render_gallery_meta_box($post) {
        // Output HTML for gallery meta box
        $gallery = get_post_meta($post->ID, 'gallery', true);
        ?>
        <div id="gallery_container">
            <ul class="gallery">
                <?php
                if ($gallery) {
                    $gallery_ids = explode(',', $gallery);
                    foreach ($gallery_ids as $gallery_id) {
                        $attachment = get_post($gallery_id);
                        $mime_type = explode('/', $attachment->post_mime_type)[0];
                        $url = wp_get_attachment_url($gallery_id);
                        if ($mime_type === 'image') {
                            echo '<li class="image" data-id="' . esc_attr($gallery_id) . '"><img src="' . esc_url($url) . '" /><span class="remove">x</span></li>';
                        } else if ($mime_type === 'video') {
                            echo '<li class="video" data-id="' . esc_attr($gallery_id) . '"><video src="' . esc_url($url) . '" controls></video><span class="remove">x</span></li>';
                        }
                    }
                }
                ?>
            </ul>
            <input type="hidden" id="gallery" name="gallery" value="<?php echo esc_attr($gallery); ?>">
            <button type="button" class="button" id="add_gallery"><?php _e('Add Media', 'hadesboard-gallery'); ?></button>
        </div>
        <?php
    }

    public function render_image_cover_meta_box($post) {
        // Output HTML for image cover meta box
        $image_cover = get_post_meta($post->ID, 'image_cover', true);
        $image_url = $image_cover ? wp_get_attachment_url($image_cover) : '';
        ?>
        <div id="image_cover_container">
            <input type="hidden" id="image_cover" name="image_cover" value="<?php echo esc_attr($image_cover); ?>">
            <img id="image_cover_preview" src="<?php echo esc_url($image_url); ?>" style="<?php echo $image_url ? '' : 'display: none;'; ?>">
            <button type="button" class="button" id="add_image_cover"><?php _e('Set Image Cover', 'hadesboard-gallery'); ?></button>
            <button type="button" class="button" id="remove_image_cover" style="<?php echo $image_cover ? '' : 'display: none;'; ?>"><?php _e('Remove Image Cover', 'hadesboard-gallery'); ?></button>
        </div>
        <?php
    }

    public function render_video_cover_meta_box($post) {
        // Output HTML for video cover meta box
        $video_cover = get_post_meta($post->ID, 'video_cover', true);
        $video_url = $video_cover ? wp_get_attachment_url($video_cover) : '';
        ?>
        <div id="video_cover_container">
            <input type="hidden" id="video_cover" name="video_cover" value="<?php echo esc_attr($video_cover); ?>">
            <video id="video_cover_preview" src="<?php echo esc_url($video_url); ?>" style="<?php echo $video_url ? '' : 'display: none;'; ?>" controls></video>
            <button type="button" class="button" id="add_video_cover"><?php _e('Set Video Cover', 'hadesboard-gallery'); ?></button>
            <button type="button" class="button" id="remove_video_cover" style="<?php echo $video_cover ? '' : 'display: none;'; ?>"><?php _e('Remove Video Cover', 'hadesboard-gallery'); ?></button>
        </div>
        <?php
    }

    public function render_like_meta_box($post) {
        $like_count = get_post_meta($post->ID, 'like_count', true);
        ?>
        <div id="like_count_container">
            <p><?php echo __('Like Count:', 'hadesboard-gallery'); ?> <strong><?php echo intval($like_count); ?></strong></p>
            <input type="number" id="like_count" name="like_count" value="<?php echo esc_attr($like_count); ?>">
        </div>
        <?php
    }

    public function save_meta_boxes($post_id, $post) {
        if ($post->post_type !== 'hadesboard_gallery') {
            return;
        }

        // Save gallery
        if (isset($_POST['gallery'])) {
            update_post_meta($post_id, 'gallery', sanitize_text_field($_POST['gallery']));
        }

        // Save image cover
        if (isset($_POST['image_cover'])) {
            update_post_meta($post_id, 'image_cover', sanitize_text_field($_POST['image_cover']));
        }

        // Save video cover
        if (isset($_POST['video_cover'])) {
            update_post_meta($post_id, 'video_cover', sanitize_text_field($_POST['video_cover']));
        }

        // Save like count
        if (isset($_POST['like_count'])) {
            update_post_meta($post_id, 'like_count', sanitize_text_field($_POST['like_count']));
        }
    }
}

new HADESBOARD_GALLERY_CPT();
?>
