<?php

/**
 * Plugin Name: Mirakel WooCommerce Carts
 */

require_once('inc/session-list.php');

class Mirakel_Woocommerce_Carts
{
    function __construct()
    {
        add_action('admin_menu', array('Mirakel_Woocommerce_Carts', 'add_admin_menu_item'));
    }

    public static function add_admin_menu_item()
    {
        add_menu_page($page_title = 'Mirakel WooCommerce Carts', $menu_title = 'WooCommerce Carts', $capability = 'manage_options', $menu_slug = 'mirakel_woocommerce_carts', $function = array('Mirakel_Woocommerce_Carts', 'render_admin_page'), $icon_url = null, $position = null);
    }

    public static function convert_wc_session_into_array($db_result)
    {

        // Turn into array
        $session_array = (array) $db_result;

        // Unserialize data
        foreach ($session_array as $key => $value) {
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

        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_sessions", OBJECT);
        foreach ($results as $key => $value) {

            $session_array = Mirakel_Woocommerce_Carts::convert_wc_session_into_array($value);

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

}
new Mirakel_Woocommerce_Carts;
