<?php
/*
Plugin Name: Sell Photo
Version: 1.0.3
Plugin URI: http://noorsplugin.com/sell-photo/
Author: naa986
Author URI: http://noorsplugin.com/
Description: Sell photos beautifully on WordPress
*/

if(!defined('ABSPATH')) exit;
if(!class_exists('SELL_PHOTO'))
{
    class SELL_PHOTO
    {
        var $plugin_version = '1.0.3';
        var $plugin_url;
        var $plugin_path;
        function __construct()
        {
            define('SELL_PHOTO_VERSION', $this->plugin_version);
            define('SELL_PHOTO_SITE_URL',site_url());
            define('SELL_PHOTO_URL', $this->plugin_url());
            define('SELL_PHOTO_PATH', $this->plugin_path());
            $this->plugin_includes();
            $this->loader_operations();
            add_action( 'wp_enqueue_scripts', array( &$this, 'plugin_scripts' ), 0 );
        }
        function plugin_includes()
        {
            if(is_admin())
            {
                add_filter('plugin_action_links', array(&$this,'add_plugin_action_links'), 10, 2 );
            }
            add_action('admin_menu', array( &$this, 'add_options_menu' ));
            add_filter('post_gallery', 'sell_photo_gallery', 10, 3);
        }
        function loader_operations()
        {
            register_activation_hook( __FILE__, array(&$this, 'activate_handler') );
            add_action('plugins_loaded',array(&$this, 'plugins_loaded_handler'));
        }
        function plugins_loaded_handler()  //Runs when plugins_loaded action gets fired
        {
            $this->check_upgrade();
        }
        
        function activate_handler()
        {
            add_option('sell_photo_plugin_version', $this->plugin_version);
            add_option('sell_photo_paypal_email', get_bloginfo('admin_email'));
            add_option('sell_photo_currency_code', 'USD');
            add_option('sell_photo_price_amount', '5.00');
            add_option('sell_photo_button_anchor', 'Buy Now');
            add_option('sell_photo_return_url', get_bloginfo('wpurl'));
        }

        function check_upgrade()
        {
            if(is_admin())
            {
                $plugin_version = get_option('sell_photo_plugin_version');
                if(!isset($plugin_version) || $plugin_version != $this->plugin_version)
                {
                    $this->activate_handler();
                    update_option('sell_photo_plugin_version', $this->plugin_version);
                }
            }
        }
        function plugin_scripts()
        {
            if (!is_admin()) 
            {
                
            }
        }
        function plugin_url()
        {
            if($this->plugin_url) return $this->plugin_url;
            return $this->plugin_url = plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
        }
        function plugin_path()
        { 	
            if ( $this->plugin_path ) return $this->plugin_path;		
            return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
        }
        function add_plugin_action_links($links, $file)
        {
            if ( $file == plugin_basename( dirname( __FILE__ ) . '/main.php' ) )
            {
                $links[] = '<a href="options-general.php?page=sell-photo-settings">Settings</a>';
            }
            return $links;
        }
        function add_options_menu()
        {
            if(is_admin())
            {
                add_options_page('Sell Photo Settings', 'Sell Photo', 'manage_options', 'sell-photo-settings', array(&$this, 'options_page'));
            }
        }
        function options_page()
        {
            $wpvl_plugin_tabs = array(
                'sell-photo-settings' => 'General'
            );
            echo '<div class="wrap">'.screen_icon().'<h2>Sell Photo v'.SELL_PHOTO_VERSION.'</h2>';;    
            echo '<div id="poststuff"><div id="post-body">';  

            if(isset($_GET['page'])){
                $current = $_GET['page'];
                if(isset($_GET['action'])){
                    $current .= "&action=".$_GET['action'];
                }
            }
            $content = '';
            $content .= '<h2 class="nav-tab-wrapper">';
            foreach($wpvl_plugin_tabs as $location => $tabname)
            {
                if($current == $location){
                    $class = ' nav-tab-active';
                } else{
                    $class = '';    
                }
                $content .= '<a class="nav-tab'.$class.'" href="?page='.$location.'">'.$tabname.'</a>';
            }
            $content .= '</h2>';
            echo $content;

            $this->general_settings();

            echo '</div></div>';
            echo '</div>';
        }
        function general_settings()
        {
            if (isset($_POST['sell_photo_update_settings']))
            {
                $nonce = $_REQUEST['_wpnonce'];
                if ( !wp_verify_nonce($nonce, 'sell_photo_general_settings')){
                        wp_die('Error! Nonce Security Check Failed! please save the settings again.');
                }
                update_option('sell_photo_enable_testmode', ($_POST["enable_testmode"]=='1')?'1':'');
                update_option('sell_photo_paypal_email', trim($_POST["paypal_email"]));
                update_option('sell_photo_currency_code', trim($_POST["currency_code"]));
                update_option('sell_photo_price_amount', trim($_POST["price_amount"]));
                update_option('sell_photo_button_anchor', trim($_POST["button_anchor"]));
                update_option('sell_photo_return_url', trim($_POST["return_url"]));
                echo '<div id="message" class="updated fade"><p><strong>';
                echo 'Settings Saved!';
                echo '</strong></p></div>';
            }
            ?>

            <div style="background: none repeat scroll 0 0 #FFF6D5;border: 1px solid #D1B655;color: #3F2502;margin: 10px 0;padding: 5px 5px 5px 10px;text-shadow: 1px 1px #FFFFFF;">	
            <p><?php _e("For documentation please visit", "SELLPHOTO"); ?><br />
            <a href="http://noorsplugin.com/sell-photo/" target="_blank"><?php _e("Sell Photo plugin page", "SELLPHOTO"); ?></a></p>
            </div>

            <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <?php wp_nonce_field('sell_photo_general_settings'); ?>

            <table class="form-table">

            <tbody>

            <tr valign="top">
            <th scope="row">Enable Test Mode</th>
            <td> <fieldset><legend class="screen-reader-text"><span>Enable Test Mode</span></legend><label for="enable_testmode">
            <input name="enable_testmode" type="checkbox" id="enable_testmode" <?php if(get_option('sell_photo_enable_testmode')== '1') echo ' checked="checked"';?> value="1">
            Check this option if you want to enable PayPal sandbox for testing</label>
            </fieldset></td>
            </tr>
            
            <tr valign="top">
            <th scope="row"><label for="paypal_email">PayPal Email</label></th>
            <td><input name="paypal_email" type="text" id="paypal_email" value="<?php echo get_option('sell_photo_paypal_email'); ?>" class="regular-text">
            <p class="description">Your PayPal email address</p></td>
            </tr>

            <tr valign="top">
            <th scope="row"><label for="currency_code">Currency Code</label></th>
            <td><input name="currency_code" type="text" id="currency_code" value="<?php echo get_option('sell_photo_currency_code'); ?>" class="regular-text">
            <p class="description">The currency of the payment (example: USD, CAD, GBP, EUR)</p></td>
            </tr>
            
            <tr valign="top">
            <th scope="row"><label for="price_amount">Price Amount</label></th>
            <td><input name="price_amount" type="text" id="price_amount" value="<?php echo get_option('sell_photo_price_amount'); ?>" class="regular-text">
            <p class="description">The default price of each gallery photo (example: 2.00)</p></td>
            </tr>
            
            <tr valign="top">
            <th scope="row"><label for="button_anchor">Button Text/Image</label></th>
            <td><input name="button_anchor" type="text" id="button_anchor" value="<?php echo get_option('sell_photo_button_anchor'); ?>" class="regular-text">
            <p class="description">The text for the Buy button. To use an image you can enter a URL instead</p></td>
            </tr>
            
            <tr valign="top">
            <th scope="row"><label for="return_url">Return URL</label></th>
            <td><input name="return_url" type="text" id="return_url" value="<?php echo get_option('sell_photo_return_url'); ?>" class="regular-text">
            <p class="description">The URL where a user will be redirected to after the payment</p></td>
            </tr>

            </tbody>

            </table>

            <p class="submit"><input type="submit" name="sell_photo_update_settings" id="sell_photo_update_settings" class="button button-primary" value="Save Changes"></p></form>

            <?php
        }
    }
    $GLOBALS['sell_photo'] = new SELL_PHOTO();
}

function sell_photo_gallery($output, $attr, $instance) 
{
    	$post = get_post();
        
	$html5 = current_theme_supports( 'html5', 'gallery' );
	$atts = shortcode_atts( array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post ? $post->ID : 0,
		'itemtag'    => $html5 ? 'figure'     : 'dl',
		'icontag'    => $html5 ? 'div'        : 'dt',
		'captiontag' => $html5 ? 'figcaption' : 'dd',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => '',
		'link'       => '',
                'sell_photo' => '', // use this parameter to enable photo selling functionality for this gallery
                'price'      => ''  //override the settings price to use a different one for this gallery
	), $attr, 'gallery' );

	$id = intval( $atts['id'] );

	if ( ! empty( $atts['include'] ) ) {
		$_attachments = get_posts( array( 'include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( ! empty( $atts['exclude'] ) ) {
		$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
	} else {
		$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
	}

	if ( empty( $attachments ) ) {
		return '';
	}

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment ) {
			$output .= wp_get_attachment_link( $att_id, $atts['size'], true ) . "\n";
		}
		return $output;
	}

	$itemtag = tag_escape( $atts['itemtag'] );
	$captiontag = tag_escape( $atts['captiontag'] );
	$icontag = tag_escape( $atts['icontag'] );
	$valid_tags = wp_kses_allowed_html( 'post' );
	if ( ! isset( $valid_tags[ $itemtag ] ) ) {
		$itemtag = 'dl';
	}
	if ( ! isset( $valid_tags[ $captiontag ] ) ) {
		$captiontag = 'dd';
	}
	if ( ! isset( $valid_tags[ $icontag ] ) ) {
		$icontag = 'dt';
	}

	$columns = intval( $atts['columns'] );
	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
	$float = is_rtl() ? 'right' : 'left';

	$selector = "gallery-{$instance}";

	$gallery_style = '';

	/**
	 * Filter whether to print default gallery styles.
	 *
	 * @since 3.1.0
	 *
	 * @param bool $print Whether to print default gallery styles.
	 *                    Defaults to false if the theme supports HTML5 galleries.
	 *                    Otherwise, defaults to true.
	 */
	if ( apply_filters( 'use_default_gallery_style', ! $html5 ) ) {
		$gallery_style = "
		<style type='text/css'>
			#{$selector} {
				margin: auto;
			}
			#{$selector} .gallery-item {
				float: {$float};
				margin-top: 10px;
				text-align: center;
				width: {$itemwidth}%;
			}
			#{$selector} img {
				border: 2px solid #cfcfcf;
			}
			#{$selector} .gallery-caption {
				margin-left: 0;
			}
			/* see gallery_shortcode() in wp-includes/media.php */
		</style>\n\t\t";
	}

	$size_class = sanitize_html_class( $atts['size'] );
	$gallery_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";

	/**
	 * Filter the default gallery shortcode CSS styles.
	 *
	 * @since 2.5.0
	 *
	 * @param string $gallery_style Default CSS styles and opening HTML div container
	 *                              for the gallery shortcode output.
	 */
	$output = apply_filters( 'gallery_style', $gallery_style . $gallery_div );

	$i = 0;
	foreach ( $attachments as $id => $attachment ) {

		$attr = ( trim( $attachment->post_excerpt ) ) ? array( 'aria-describedby' => "$selector-$id" ) : '';
		if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
			$image_output = wp_get_attachment_link( $id, $atts['size'], false, false, false, $attr );
		} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] ) {
			$image_output = wp_get_attachment_image( $id, $atts['size'], false, $attr );
		} else {
			$image_output = wp_get_attachment_link( $id, $atts['size'], true, false, false, $attr );
		}
		$image_meta  = wp_get_attachment_metadata( $id );

		$orientation = '';
		if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
			$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
		}
		$output .= "<{$itemtag} class='gallery-item'>";
		$output .= "
			<{$icontag} class='gallery-icon {$orientation}'>
				$image_output
			</{$icontag}>";
		if ( $captiontag && trim($attachment->post_excerpt) ) {
			$output .= "
				<{$captiontag} class='wp-caption-text gallery-caption' id='$selector-$id'>
				" . wptexturize($attachment->post_excerpt) . "
				</{$captiontag}>";
		}
                /*display button for each photo */
                if(!empty($atts['sell_photo'])){
                    $title = $attachment->post_title;  
                    $price = $atts['price'];
                    $button = sell_photo_get_button_code_for_paypal($title, $price);
                    $output .= $button;
                }
                /* end of button code */
		$output .= "</{$itemtag}>";
		if ( ! $html5 && $columns > 0 && ++$i % $columns == 0 ) {
			$output .= '<br style="clear: both" />';
		}
	}

	if ( ! $html5 && $columns > 0 && $i % $columns !== 0 ) {
		$output .= "
			<br style='clear: both' />";
	}

	$output .= "
		</div>\n";

	return $output;
}

function sell_photo_get_button_code_for_paypal($item_name, $price)
{
    $url = "https://www.paypal.com/cgi-bin/webscr";
    $testmode = get_option('sell_photo_enable_testmode');
    if(isset($testmode) && !empty($testmode)){
        $url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
    }
    $paypal_email = get_option('sell_photo_paypal_email');
    $amount = get_option('sell_photo_price_amount');
    if(is_numeric($price)){
        $amount = $price;
    }
    $amount = number_format($amount, 2, '.', '');
    $currency = get_option('sell_photo_currency_code');
    $return_url = get_option('sell_photo_return_url'); 
    $button = get_option('sell_photo_button_anchor');
    $image_button = strstr($button, 'http');
    if($image_button==FALSE){
        $button = '<input type="submit" class="sell_photo_button" value="'.$button.'">';	
    }
    else{
        $button = '<input type="image" src="'.$button.'" border="0" name="submit" alt="'.$item_name.'">';
    }
    $button_code = <<<EOT
    <form method="post" action="$url">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="business" value="$paypal_email">
    <input type="hidden" name="item_name" value="$item_name">
    <input type="hidden" name="amount" value="$amount">
    <input type="hidden" name="currency_code" value="$currency">
    <input type="hidden" name="return" value="$return_url">
    $button
    </form>
EOT;
    return $button_code;
}