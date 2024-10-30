<?php
/*
Plugin Name: ImmersiveSlider
Plugin URI: https://tishonator.com/plugins/immersive-slider
Description: ImmersiveSlider plugins allows you to insert and configure a Responsive jQuery Slider into your WordPress site as a shortcode.
Author: tishonator
Version: 1.0.2
Author URI: http://tishonator.com/
Contributors: tishonator
Text Domain: immersive-slider
*/

if ( !class_exists('ImmersiveSliderPlugin') ) :

    /**
     * Register the plugin.
     *
     * Display the administration panel, insert JavaScript etc.
     */
    class ImmersiveSliderPlugin {
        
    	/**
    	 * Instance object
    	 *
    	 * @var object
    	 * @see get_instance()
    	 */
    	protected static $instance = NULL;


        /**
         * Constructor
         */
        public function __construct() {}

        /**
         * Setup
         */
        public function setup() {

            if ( class_exists('ImmersiveSliderProPlugin') )
                return;

            register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );


            if ( is_admin() ) { // admin actions

                add_action('admin_menu', array(&$this, 'add_admin_page'));

                add_action('admin_enqueue_scripts', array(&$this, 'admin_scripts'));
            }

            add_action( 'init', array(&$this, 'register_shortcode') );
        }

        public function register_shortcode() {

            add_shortcode( 'immersive-slider', array(&$this, 'display_shortcode') );
        }

        public function display_shortcode($atts) {

            $result = '';

            $options = get_option( 'immersive_slider_options' );
            
            if ( ! $options )
                return $result;

            $result .= '';

            // JS
            wp_register_script('is_immersive_slider_js', plugins_url('js/jquery.immersive-slider.js', __FILE__), array('jquery'));

            wp_enqueue_script('is_immersive_slider_js',
                    plugins_url('js/jquery.immersive-slider.js', __FILE__), array('jquery') );

             // CSS
            wp_register_style('is_immersiveslider_css',
                plugins_url('css/immersive-slider.css', __FILE__), true);

            wp_enqueue_style( 'is_immersiveslider_css', plugins_url('css/immersive-slider.css', __FILE__), array( ) );

            $result .= '<div class="slider-main"><div class="page_container"><div id="immersive_slider">'; 
            
            for ( $slideNumber = 1; $slideNumber <= 3; ++$slideNumber ) {

                $slideTitle = array_key_exists('slide_' . $slideNumber . '_title', $options)
                                ? $options[ 'slide_' . $slideNumber . '_title' ] : '';

                $slideText = array_key_exists('slide_' . $slideNumber . '_text', $options)
                                ? $options[ 'slide_' . $slideNumber . '_text' ] : '';

                $slideImage = array_key_exists('slide_' . $slideNumber . '_image', $options)
                                ? $options[ 'slide_' . $slideNumber . '_image' ] : '';

                if ( $slideTitle || $slideText || $slideImage ) :

                    $result .= '<div class="slide"';

                    if ( $slideImage != '' ) :
                        $result .= ' data-blurred="' . esc_attr( $slideImage ) . '"';
                    endif;

                    $result .= '>';

                    $result .= '<div class="content">';

                    if ( $slideTitle != '' ) :
                        $result .= '<h2>' . $slideTitle . '</h2>';
                    endif;

                    if ( $slideText != '' ) :
                        $result .= '<div class="slide-content">';
                        $result .= $slideText;
                        $result .= '</div>';
                    endif;

                    $result .= '</div>'; // close div.content tag

                    if ( $slideImage != '' ) :
                        $result .= '<div class="image">';
                        $result .= '<img src="' . esc_attr( $slideImage ) . '" />';
                        $result .= '</div>';
                    endif;

                    $result .= '</div>'; // close div.slide tag

                endif;
            }

            $result .= '<a href="#" class="is-prev">&laquo;</a><a href="#" class="is-next">&raquo;</a>';
            $result .= '</div></div></div>';

            return $result;
        }

        public function admin_scripts($hook) {

            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');

            wp_register_script('is_upload_media', plugins_url('js/is-upload-media.js', __FILE__), array('jquery'));
            wp_enqueue_script('is_upload_media');

            wp_enqueue_style('thickbox');
        }

    	/**
    	 * Used to access the instance
         *
         * @return object - class instance
    	 */
    	public static function get_instance() {

    		if ( NULL === self::$instance ) {
                self::$instance = new self();
            }

    		return self::$instance;
    	}

        /**
         * Unregister plugin settings on deactivating the plugin
         */
        public function deactivate() {

            unregister_setting('immersive_slider', 'immersive_slider_options');
        }

        /** 
         * Print the Section text
         */
        public function print_section_info() {}

        public function admin_init_settings() {

            register_setting('immersive_slider', 'immersive_slider_options');

            // add separate sections for each of Sliders
            add_settings_section( 'immersive_slider_section',
                __( 'Slider Settings', 'immersive-slider' ),
                array(&$this, 'print_section_info'),
                'immersive_slider' );

            for ( $slideNumber = 1; $slideNumber <= 3; ++$slideNumber ) {

                // Slide Title
                add_settings_field(
                    'slide_' . $slideNumber . '_title',
                    sprintf( __( 'Slide %s Title', 'immersive-slider' ), $slideNumber ),
                    array(&$this, 'input_callback'),
                    'immersive_slider',
                    'immersive_slider_section',
                    [ 'label_for' => 'slide_' . $slideNumber . '_title',
                      'page' =>  'immersive_slider_options' ]
                );

                // Slide Text
                add_settings_field(
                    'slide_' . $slideNumber . '_text',
                    sprintf( __( 'Slide %s Text', 'immersive-slider' ), $slideNumber ),
                    array(&$this, 'textarea_callback'),
                    'immersive_slider',
                    'immersive_slider_section',
                    [ 'label_for' => 'slide_' . $slideNumber . '_text',
                      'page' =>  'immersive_slider_options' ]
                );

                // Slide Image
                add_settings_field(
                    'slide_' . $slideNumber . '_image',
                    sprintf( __( 'Slide %s Image', 'immersive-slider' ), $slideNumber ),
                    array(&$this, 'image_callback'),
                    'immersive_slider',
                    'immersive_slider_section',
                    [ 'label_for' => 'slide_' . $slideNumber . '_image',
                      'page' =>  'immersive_slider_options' ]
                );
            }
        }

        public function input_callback($args) {

            // get the value of the setting we've registered with register_setting()
            $options = get_option( $args['page'] );
 
            // output the field
            $fieldValue = ($options && $args['label_for'] && array_key_exists(esc_attr( $args['label_for'] ), $options))
                                ? $options[ esc_attr( $args['label_for'] ) ] : 
                                    (array_key_exists('default_val', $args) ? $args['default_val'] : '');
            ?>

            <input type="text" id="<?php echo $args['page'] . '[' . $args['label_for'] . ']'; ?>"
                name="<?php echo $args['page'] . '[' . $args['label_for'] . ']'; ?>" class="regular-text"
                value="<?php echo $fieldValue; ?>" />
<?php
        }

        public function image_callback($args) {

            // get the value of the setting we've registered with register_setting()
            $options = get_option( $args['page'] );
 
            // output the field

            $fieldValue = $options && $args['label_for'] && array_key_exists(esc_attr( $args['label_for'] ), $options)
                                ? $options[ esc_attr( $args['label_for'] ) ] : '';
            ?>

            <input type="text" id="<?php echo $args['page'] . '[' . $args['label_for'] . ']'; ?>"
                name="<?php echo $args['page'] . '[' . $args['label_for'] . ']'; ?>" class="regular-text"
                value="<?php echo $fieldValue; ?>" />
            <input class="upload_image_button button button-primary" type="button" value="Change Image" />

            <p><img class="slider-img-preview" <?php if ( $fieldValue ) : ?> src="<?php echo esc_attr($fieldValue); ?>" <?php endif; ?> style="max-width:300px;height:auto;" /><p>

<?php         
        }

        public function textarea_callback($args) {

            // get the value of the setting we've registered with register_setting()
            $options = get_option( $args['page'] );
 
            // output the field

            $fieldValue = $options && $args['label_for'] && array_key_exists(esc_attr( $args['label_for'] ), $options)
                                ? $options[ esc_attr( $args['label_for'] ) ] : '';
            ?>

            <textarea id="<?php echo $args['page'] . '[' . $args['label_for'] . ']'; ?>"
                name = "<?php echo $args['page'] . '[' . $args['label_for'] . ']'; ?>"
                rows="10" cols="39"><?php echo $fieldValue; ?></textarea>
<?php
        }

        public function add_admin_page() {

            add_menu_page( __('Immersive Slider Settings', 'immersive-slider'),
                __('Immersive Slider', 'immersive-slider'), 'manage_options',
                'immersive-slider.php', array(&$this, 'show_settings'),
                'dashicons-format-gallery', 6 );

            //call register settings function
            add_action( 'admin_init', array(&$this, 'admin_init_settings') );
        }

        /**
         * Display the settings page.
         */
        public function show_settings() { ?>

            <div class="wrap">
                <div id="icon-options-general" class="icon32"></div>

                <div class="notice notice-info"> 
                    <p><strong><?php _e('Upgrade to ImmersiveSliderPro Plugin', 'immersive-slider'); ?>:</strong></p>
                    <ol>
                        <li><?php _e('Configure Up to 10 Different Sliders', 'immersive-slider'); ?></li>
                        <li><?php _e('Insert Up to 10 Slides per Slider', 'immersive-slider'); ?></li>
                        <li><?php _e('Color Options: Slider Background, Title and Text, Link and Link Hover.', 'immersive-slider'); ?></li>
                        <li><?php _e('Sliding Settings: Sliding Delay, Pagination', 'immersive-slider'); ?></li>
                        <li><?php _e('Sliding Effects: slide, bounce, fade, slideUp, or bounceUp.', 'immersive-slider'); ?></li>
                    </ol>
                    <a href="https://tishonator.com/plugins/immersive-slider" class="button-primary">
                        <?php _e('Upgrade to ImmersiveSliderPRO Plugin', 'immersive-slider'); ?>
                    </a>
                    <p></p>
                </div>


                <h2><?php _e('Immersive Slider Settings', 'immersive-slider'); ?></h2>

                <form action="options.php" method="post">
                <?php settings_fields('immersive_slider'); ?>
                <?php do_settings_sections('immersive_slider'); ?>

                <h3>
                  Usage
                </h3>
                <p>
                    <?php _e('Use the shortcode', 'immersive-slider'); ?> <code>[immersive-slider]</code> <?php echo _e( 'to display Slider to any page or post.', 'immersive-slider' ); ?>
                </p>

                <?php submit_button(); ?>
              </form>
            </div>
    <?php
        }
    }

endif; // ImmersiveSliderPlugin

add_action('plugins_loaded', array( ImmersiveSliderPlugin::get_instance(), 'setup' ), 10);
