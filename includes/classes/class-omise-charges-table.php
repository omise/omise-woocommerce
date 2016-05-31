<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'Omise_Charges_Table' ) ) {
    class Omise_Charges_Table extends WP_List_Table {
        function __construct( $omise_list_object ) {            
            parent::__construct( array(
                'singular' => 'omise_charge',
                'plural'   => 'omise_charges',
                'ajax'     => false
            ) );

            $this->items = $omise_list_object;
        }

        function get_table_classes() {
            return array( 'widefat', 'striped', $this->_args['plural'] );
        }

        function prepare_items() {
            $columns    = $this->get_columns();
            $hidden     = array();
            $sortable   = array();

            $totalitems = $this->items['total'];
            $perpage    = $this->items['limit'];
            $paged      = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
            $totalpages = ceil( $totalitems / $perpage );
            
            $this->set_pagination_args( array(
                'total_items' => $totalitems,
                'total_pages' => $totalpages,
                'per_page'    => $perpage,
            ) );

            $this->_column_headers = array( $columns, $hidden, $sortable );

            // Clear item property to call `no_items` method.
            if ( $this->items['total'] <= 0 )
                $this->items = array();
        }

        function get_columns() {
            return $columns = array(
                'chrg_amount'     => __( 'Amount' ),
                'chrg_id'         => __( 'Charge Id' ),
                'chrg_authorized' => __( 'Authorized' ),
                'chrg_paid'       => __( 'Paid' ),
                'chrg_failure'    => __( 'Failure Message' ),
                'chrg_datetime'   => __( 'Created' ),
                'chrg_action'     => '',
            );
        }

        function display_rows() {
            $records = $this->items;

            list( $columns, $hidden ) = $this->get_column_info();

            if ( 'list' === $records['object'] && $records['total'] > 0 ) {
                foreach ( $records['data'] as $record ) {
                    echo "<tr id='record_{$record['id']}'>";

                        $this->single_row_columns( $record );

                    echo "</tr>";
                }
            }
        }

        function column_chrg_amount( $record ) {
            $class = ( $record['failure_code'] ) ? 'TextDanger' : ( ( ! $record['authorized'] || ! $record['captured'] ) ? 'TextWarning' : 'TextSuccess' );
            echo "<strong class='Omise-" . $class . "'>" . OmisePluginHelperCurrency::format( $record['currency'], $record['amount'] ) . "</strong>";
        }

        function column_chrg_id( $record ) {
            echo "<a href='" . OmisePluginHelperTransaction::url( $record ) . "'>" . stripslashes( $record['id'] ) . "</a>";
        }

        function column_chrg_authorized( $record ) {
            echo $record['authorized'] ? '<strong class="Omise-TextSuccess">Yes</strong>' : 'No';
        }

        function column_chrg_paid( $record ) {
            echo $record['captured'] ? '<strong class="Omise-TextSuccess">Yes</strong>' : 'No';
        }

        function column_chrg_failure( $record ) {
            echo $record['failure_code'] ? '(' . $record['failure_code'] . ') ' . $record['failure_message'] : '-';
        }

        function column_chrg_datetime( $record ) {
            echo Omise_Util::date_format( $record['created'] );
        }

        function column_chrg_action( $record ) {
            echo "<a href='" . OmisePluginHelperTransaction::url( $record ) . "'>view detail</a>";
        }
    }
}
