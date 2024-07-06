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
                <span class="close">&times;</span>
                <div class="modal-content">
                    <div class="modal-body">
                        <!-- Content will be loaded dynamically via AJAX -->
                    </div>
                    <div class="like-section">
                        <button id="likeButton" data-post-id=""><i class="fa fa-thumbs-up"></i></button>
                        <span id="likeCount">Likes: <b>0</b></span>
                    </div>
                </div>
                <div class="modal-navigation">
                    <button id="prevButton" disabled="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11.28 15.7l-1.34 1.37L5 12l4.94-5.07 1.34 1.38-2.68 2.72H19v1.94H8.6z"></path></svg></button>
                    <button id="nextButton" disabled="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M15.4 12.97l-2.68 2.72 1.34 1.38L19 12l-4.94-5.07-1.34 1.38 2.68 2.72H5v1.94z"></path></svg></button>
                </div>
            </div>
            
            <?php
            return ob_get_clean();
        }
    }

new HADESBOARD_GALLERY_SHORTCODE();
