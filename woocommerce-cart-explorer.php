<?php

/**
 * Plugin Name: Mirakel WooCommerce Carts
 */

use function PHPSTORM_META\type;

require_once('inc/session-list.php');

class Mirakel_Woocommerce_Carts
{
    function __construct()
    {
        add_action('admin_menu', array('Mirakel_Woocommerce_Carts', 'add_admin_menu_item'));
        add_action('wp_footer', array('Mirakel_Woocommerce_Carts', 'footer_debug'));
    }

    public static function add_admin_menu_item()
    {
        add_menu_page($page_title = 'Mirakel WooCommerce Carts', $menu_title = 'WooCommerce Carts', $capability = 'manage_options', $menu_slug = 'mirakel_woocommerce_carts', $function = array('Mirakel_Woocommerce_Carts', 'render_admin_page'), $icon_url = null, $position = null);
    }

    public static function query_database($session_id)
    {
        global $wpdb;
        if( gettype($session_id) === 'integer' ) {
            $results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}woocommerce_sessions WHERE `session_id` = %s", $session_id), ARRAY_A );
        } else {
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_sessions", ARRAY_A );
        }
        // $results = self::unserialize_session_data($results);
        return $results;
    }

    public static function unserialize_session_data($db_result)
    {
        // Unserialize data
        foreach ($db_result as $key => $value) {
            $session_array[$key] = maybe_unserialize($value);
        }
        foreach ($session_array['session_value'] as $key => $value) {
            $session_array['session_value'][$key] = maybe_unserialize($value);
        }

        return $session_array;
    }

    public static function prepare_list_data()
    {
        $data = array();

        $results = self::query_database('all');

        foreach ($results as $key => $value) {

            $session_array = Mirakel_Woocommerce_Carts::unserialize_session_data($value);

            // Session ID
            $session_id = $session_array['session_id'];

            // Cart items
            $cart_items = array();
            foreach ($session_array['session_value']['cart'] as $key => $value) {
                $cart_items[] = $value['product_id'];
            }

            $readable_cart_items = array();
            foreach ($cart_items as $key => $value) {
                $product = wc_get_product($value);
                $readable_cart_items[] = $product->get_name();
            }

            // Removed cart items
            $removed_cart_items = array();
            foreach ($session_array['session_value']['removed_cart_contents'] as $key => $value) {
                $removed_cart_items[] = $value['product_id'];
            }

            // Subtotal
            $subtotal = $session_array['session_value']['cart_totals']['subtotal'];

            // [applied_coupons]
            $applied_coupons = $session_array['session_value']['applied_coupons'];

            // Session expiry
            $session_expiry = $session_array['session_expiry'];
            $readable_session_expiry = date_i18n( get_option('date_format') . ' ' . get_option('time_format'), $session_expiry);

            $data[] = array(
                'session_id'        => '<a href="' . add_query_arg('session_details', $session_id, $_SERVER['REQUEST_URI']) . '">' . $session_id . '</a>',
                'cart_items'        => join(', ', $readable_cart_items),
                'subtotal'          => wc_price($subtotal),
                'session_expiry'    => $readable_session_expiry
            );
        }

        return $data;
    }

    public static function render_admin_page()
    {

    }

    public static function get_session_data( $session_id )
    {
        
    }

    public static function render_session_details( $session_id ) {
        require_once('inc/session-details.php');
    }

    public static function footer_debug() {
        if( isset( WC()->session ) ) {

            // IP address
            WC()->session->set('test_data', 'testing 123');

            // Referer

            echo "<pre>";
            var_dump( WC()->session );
            echo "</pre>";
        } else {
            echo "no wc session";
        }
    }

}
new Mirakel_Woocommerce_Carts;
