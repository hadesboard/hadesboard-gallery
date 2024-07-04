<?php

class HADESBOARD_GALLERY_SHORTCODE {

    public function __construct() {
        // Register shortcode
        add_shortcode('hadesboard_gallery', array($this, 'render_gallery'));
    }

    public function render_gallery($atts) {
        ob_start();
        ?>
        <div class="hadesboard-gallery">
            <?php
            $query = new WP_Query(array('post_type' => 'hadesboard_gallery', 'posts_per_page' => -1));
            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $image_cover_id = get_post_meta(get_the_ID(), 'image_cover', true);
                    $video_cover_id = get_post_meta(get_the_ID(), 'video_cover', true);
                    $video_cover_url = $video_cover_id ? wp_get_attachment_url($video_cover_id) : '';
                    ?>
                    <div class="hadesboard-gallery-item open-modal" data-gallery-id="<?php echo get_the_ID(); ?>">
                        <?php if ($image_cover_id) : ?>
                            <?php echo wp_get_attachment_image($image_cover_id, 'medium'); ?>
                        <?php elseif ($video_cover_url) : ?>
                            <video class="hadesboard-video" muted repeat>
                                <source src="<?php echo esc_url($video_cover_url); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php else : ?>
                            <!-- <img src="<?php //echo HADESBOARD_GALLERY_URL . 'assets/images/default-thumbnail.jpg'; ?>" alt="<?php //the_title(); ?>"> -->
                        <?php endif; ?>
                        <div class="hadesboard-overlay">
                            <div class="hadesboard-overlay-text">Read More</div>
                        </div>
                    </div>
                    <?php
                endwhile;
            endif;
            wp_reset_postdata();
            ?>
            <div class="grid-sizer"></div>
        </div>

        <!-- Modal Structure -->
        <div class="hb-modal" id="hadesboardModal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="modal-body">
                    <!-- Content will be loaded dynamically via AJAX -->
                </div>
            </div>
        </div>
        
        <?php
        return ob_get_clean();
    }
}

new HADESBOARD_GALLERY_SHORTCODE();
