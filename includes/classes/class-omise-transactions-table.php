<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'Omise_Transactions_Table' ) ) {
	class Omise_Transactions_Table extends WP_List_Table {
		function __construct( $omise_list_object ) {            
			parent::__construct( array(
				'singular' => 'omise_transaction',
				'plural'   => 'omise_transactions',
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
				'trxn_amount'   => __( 'Amount' ),
				'trxn_id'       => __( 'Transaction Id' ),
				'trxn_datetime' => __( 'Transaction Time' )
			);
		}

		function extra_tablenav( $which ) { }

		function display_rows() {
			$records = $this->items;

			list( $columns, $hidden ) = $this->get_column_info();

			if ( 'list' === $records['object'] && $records['total'] > 0 ) {
				foreach ( $records['data'] as $transaction ) {
					echo "<tr id='record_{$transaction['id']}'>";

						$this->single_row_columns( $transaction );

					echo "</tr>";
				}
			}
		}

		function column_trxn_amount( $transaction ) {
			echo "<strong class='Omise-" . strtoupper( $transaction['type'] ) . "'>" . OmisePluginHelperTransaction::type( $transaction['source'] ) . " " . ( $transaction['type'] === 'debit' ? '-' : '' ) . OmisePluginHelperCurrency::format( $transaction['currency'], $transaction['amount'] ) . "</strong>";
		}

		function column_trxn_id( $transaction ) {
			echo "<a href='" . OmisePluginHelperTransaction::url( $transaction ) . "'>" . stripslashes( $transaction['source'] ) . "</a>";
		}

		function column_trxn_datetime( $transaction ) {
			echo Omise_Util::date_format( $transaction['created'] );
		}
	}
}
