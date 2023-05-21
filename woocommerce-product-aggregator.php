<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Plugin Name: Woocommerce Product Aggregator
 * Author: Pratyush Gupta
 * Author uri: https://www.linkedin.com/in/pratyush-gupta/
 * Description: Allows to create woocommerce product aggregator
 * Version: 1.0.0
 * Text Domain: woocommerce_product_aggregator
 */ 

 // Add a menu page to the left sidebar
function wpa_menu() {
    add_menu_page(
        'WPA',
        'WPA',
        'manage_options',
        'wpa-plugin',
        'wpa_plugin_page',
        'dashicons-text',
        30
    );
}
add_action( 'admin_menu', 'wpa_menu' );

// Render the plugin settings page
function wpa_plugin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    if ( isset( $_POST['wpa_storage_submit'] ) ) {
        // Save the submitted text as an option
        $text_content = $_POST['wpa_content'];
        if ( ! empty( $text_content ) ) {
            update_option( 'wpa_text', $text_content );
        }
    }
    
    // Retrieve the stored text option
    $wpa_text = get_option( 'wpa_text' );
    ?>
    <div class="wrap">
        <h1>WPA HTML Block</h1>
        <form method="post" action="">
            <?php
            // Display the WordPress editor
            wp_editor( wp_unslash( $wpa_text ), 'wpa_content', array( 'textarea_rows' => 5 ) );
            ?>
            <p><input type="submit" name="wpa_storage_submit" class="button-primary" value="Save Text"></p>
        </form>
    </div>
    <?php
}

 /* JS and CSS files */
function add_js_css_func(){
    wp_enqueue_script( 'wpa-js', plugin_dir_url( __FILE__ ) . 'js/wpa.js', array(), '1.0', true );
    wp_enqueue_style( 'wpa-css', plugin_dir_url( __FILE__ ) . 'css/wpa.css', array(), '1.0.0', 'all' );
}

 add_action('wp_enqueue_scripts','add_js_css_func');


 /* Generate Product Title By ID */
 function get_prod_title($prod_id){
    $product = wc_get_product( $prod_id );
    return $product->get_title();
 }


 /* Generate Product Content By ID */
 function get_prod_cont($prod_id){
    // Get the product ID
$product_id = $prod_id;

// Get the product object
$product = wc_get_product($product_id);

$vari_data = "";

/* Generate Top Content */

// Check if the product exists and is of type 'product'
 if (get_post_field('post_content', $product_id)) {
     // Get the product description
     $top_content_part = '<div class="wpa-top-content">'.get_post_field('post_content', $product_id).'</div>';
 }else{
    $top_content_part = '';
 }

/* Generate Bottom Content */
if (get_option( 'wpa_text' )) {
    $bottom_content_part = '<div class="wpa-bottom-content">'.wp_unslash(get_option( 'wpa_text' )).'</div>';
}else{
   $bottom_content_part = '';
}

// Check if the product has variations
if ($product->is_type('variable')) {
    // Get the variation attributes
    $attributes = $product->get_variation_attributes();
    $variation_id = $product->get_id();
    $variations = $product->get_children();


        // Loop through the attributes
        
        foreach ($attributes as $attribute_name => $options) {
            // Get the variation options for the attribute
            $variation_options = $product->get_variation_attributes($attribute_name);
            //var_dump(reset($variation_options));

            // Output the attribute name and options
            //$vari_data = $vari_data.'<label for="' . sanitize_title($attribute_name) . '">' . wc_attribute_label($attribute_name) . '</label>';
            $vari_data = $vari_data.'<select class="vari-select" id="' . sanitize_title($attribute_name) . '" name="' . esc_attr($attribute_name) . '">';
            
            $vari_data_option = "";
            $l = 0;
            foreach (reset($variation_options) as $option) {
                $vari_data_option = $vari_data_option.'<option value="' . $variations[$l] . '">' . esc_html($option) . '</option>';
                
                $l++;
            }
            
            $vari_data = $vari_data.$vari_data_option.'</select> <a class="add-to-cart-btn" href="'.site_url().'/checkout/?add-to-cart='.$product_id.'&variation_id='.$variations[0].'">Add to Cart</a>';
        }
    }else{
        /* For Non Variable Products */
        $vari_data = '<a class="add-to-cart-btn" href="'.site_url().'/checkout/?add-to-cart='.$product_id.'">Add to Cart</a>';
    }
    return $top_content_part.$vari_data.$bottom_content_part;
 }

 /* Generate Inner Horizontal Tab */
 function gen_inner_h_tab($ids){
    $prod_ids_arr_uni = explode(",", $ids);

    $j = 0;
    $k = 0;
    $h_tab_head = '';
    $h_tab_body = '';
    foreach( $prod_ids_arr_uni as $prod_id){
        if($k==0){
            $h_tab_head_active = 'class="active"';
        }else{
            $h_tab_head_active = '';
        }
        $h_tab_head_rel = str_replace(' ', '', $prod_id);

        /* Horizontal Tab Head */
        $h_tab_head = $h_tab_head.'<li '.$h_tab_head_active.' rel="a'.$h_tab_head_rel.'">'.get_prod_title($prod_id).'</li>';


        /* Horizontal Tab Body */
        $h_tab_body = $h_tab_body.'<div id="a'.$h_tab_head_rel.'" class="h-tab_content">'.get_prod_cont($prod_id).'</div>';


        $j++;
        $k++;
    }

    /* Inner Tab data */
    $innder_data = '
    <div class="h-tab">
                        
    <!-- Horizontal Tab Head -->
    <ul class="h-tab_tab-head">
    '.$h_tab_head.'
    
    </ul>
    
    <!-- Horizontal Tab Body -->
    <div class="h-tab_container">
    
    '.$h_tab_body.'
        
        
        
    </div>
    
    
    
    </div>';
    return $innder_data;
 }

 /* Main Shortcode */
 function wpa_main_func($atts){
    // Extract shortcode attributes
    $shortcode_atts = shortcode_atts(
        array(
            'tab_cats' => 'Undefined',
            'prod_ids' => '0',
        ),
        $atts
    );

    // Access individual shortcode attributes
    $tab_cats = $shortcode_atts['tab_cats'];
    $prod_ids = $shortcode_atts['prod_ids'];

    $tab_cats_arr = explode("|", $tab_cats);
    $prod_ids_arr = explode("|", $prod_ids);

    $v_tab_head = '';
    $v_tab_body = '';
    $i = 0;
    foreach( $tab_cats_arr as $tab_cat){
        if($i==0){
            $v_tab_head_active = 'class="active"';
        }else{
            $v_tab_head_active = '';
        }
        $v_tab_head_rel = str_replace(' ', '', $tab_cat);

        /* Verticle Tab Head */
        $v_tab_head = $v_tab_head.'<li '.$v_tab_head_active.' rel="'.$v_tab_head_rel.'">'.$tab_cat.'</li>';


        /* Verticle Tab Body */
        $v_tab_body = $v_tab_body.'<div id="'.$v_tab_head_rel.'" class="v-tab_content">'.gen_inner_h_tab($prod_ids_arr[$i]).'</div>';


        $i++;
        $k = 0;
    }


    $data = '<div class="v-tab">
                <!-- Verticle Tab Head -->
                <ul class="v-tab_tab-head">
                '.$v_tab_head.'
                </ul>
                
                <!-- Verticle Tab Body -->
                <div class="v-tab_container">
                '.$v_tab_body.'
                </div>
            </div>';
    return $data;
 }
 add_shortcode('wpa_main','wpa_main_func');