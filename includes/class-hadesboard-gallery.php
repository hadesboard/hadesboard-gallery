<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Hadesboard_Gallery  {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        $this->includes();
    }

    public function enqueue_scripts() {

        wp_enqueue_style( 'hb-gallery-styles', HADESBOARD_GALLERY_URL . 'assets/css/hb-gallery-styles.css', array(), '1.0' );
        
        // Enqueue LightGallery CSS
        wp_enqueue_style('fancybox.min.css', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css');

        // Enqueue LightGallery JS
        wp_enqueue_script('jquery.fancybox.min', HADESBOARD_GALLERY_URL . 'assets/js/jquery.fancybox.min.js' , array('jquery'), '3.5.7', true);

        wp_enqueue_script( 'hb-gallery-scripts', HADESBOARD_GALLERY_URL . 'assets/js/hb-gallery-scripts.js', array( 'jquery', 'masonry'), '1.0', true );
        wp_localize_script( 'hb-gallery-scripts', 'hbg_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
        wp_enqueue_script( 'masonry', 'https://cdnjs.cloudflare.com/ajax/libs/masonry/4.2.2/masonry.pkgd.min.js', array( 'jquery' ), '1.0', true );

    }

    public function includes() {
        include_once HADESBOARD_GALLERY_PATH . 'admin/class-hadesboard-gallery-cpt.php';
        include_once HADESBOARD_GALLERY_PATH . 'public/class-hadesboard-gallery-shortcode.php';
        include_once HADESBOARD_GALLERY_PATH . 'includes/class-hadesboard-gallery-ajax.php';
    }
    
}
