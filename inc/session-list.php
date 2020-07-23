<?php

if (is_admin()) {
    new Paulund_Wp_List_Table();
}

/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class Paulund_Wp_List_Table
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_example_list_table_page'));
    }

    public function add_menu_example_list_table_page()
    {
        add_submenu_page(
            $parent_slug = 'woocommerce',
            $page_title = __('Carts', 'asdf'),
            $menu_title = __('Carts', 'asdf'),
            $capability = 'manage_options',
            $menu_slug = 'carts',
            $function = array($this, 'list_table_page')
        );
    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    {
        if (!isset($_GET['session_details'])) {
            
            $exampleListTable = new Example_List_Table();
            $exampleListTable->prepare_items();
?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <h2><?php _e('Carts', 'asdf') ?></h2>
                <?php $exampleListTable->display(); ?>
            </div>
<?php
        } else { ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <h2><?php _e('Carts', 'asdf') ?></h2>
                <?php Mirakel_Woocommerce_Carts::render_session_details( $_GET['session_details'] ); ?>
            </div>
        <?php }
    }
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Example_List_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort($data, array(&$this, 'sort_data'));

        $perPage = 20;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ));

        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        // Columns: session id, cart items, subtotal, session expiry
        $columns = array(
            'session_id'        => __('Session ID', 'asdf'),
            'cart_items'        => __('Cart Items', 'asdf'),
            'subtotal'          => __('Subtotal', 'asdf'),
            'session_expiry'    => __('Session Expiry', 'asdf')
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array(
            // 'session_id' => array('session_id', false)
        );
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        $data = Mirakel_Woocommerce_Carts::prepare_list_data();
        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'session_id':
            case 'cart_items':
            case 'subtotal':
            case 'session_expiry':
                return $item[$column_name];

            default:
                return print_r($item, true);
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'session_id';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }


        $result = strcmp($a[$orderby], $b[$orderby]);

        if ($order === 'asc') {
            return $result;
        }

        return -$result;
    }
}
