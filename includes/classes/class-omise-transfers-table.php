<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'Omise_Transfers_Table' ) ) {
	class Omise_Transfers_Table extends WP_List_Table {

		function __construct( $omise_list_object ) {
			parent::__construct( array(
				'singular' => '',
				'plural'   => '',
				'ajax'     => false
			) );

			$this->items = $omise_list_object;
		}

		function get_table_classes() {
			return array(
				'widefat',
				'striped',
				$this->_args['plural']
			);
		}

		function prepare_items() {
			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = array();

			$totalitems = $this->items['total'];
			$perpage    = $this->items['limit'];
			$paged      = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
			$totalpages = ceil( $totalitems / $perpage );

			$this->set_pagination_args( array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page'    => $perpage
			) );

			$this->_column_headers = array(
				$columns,
				$hidden,
				$sortable
			);

			// Clear item property to call `no_items` method.
			if ( $this->items['total'] <= 0 ) {
				$this->items = array();
			}
		}

		function get_columns() {
			return $columns = array(
				'trsf_amount'   => __( 'Amount', 'omise' ),
				'trsf_id'       => __( 'Transfer Id', 'omise' ),
				'trsf_sent'     => _x( 'Sent', 'Transfer table column header', 'omise' ),
				'trsf_paid'     => __( 'Paid', 'omise' ),
				'trsf_failure'  => __( 'Failure Message', 'omise' ),
				'trsf_datetime' => __( 'Created', 'omise' )
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

		function column_trsf_amount( $record ) {
			$class = 'TextSuccess';

			if ( $record['failure_code'] ) {
				$class = 'TextDanger';
			} else {
				if ( ! $record['sent'] || ! $record['paid'] ) {
					$class = 'TextWarning';
				}
			}

			echo "<strong class='Omise-$class'>" . OmisePluginHelperCurrency::format( $record['currency'], $record['amount'] ) . "</strong>";
		}

		function column_trsf_id( $record ) {
			echo "<a href='" . OmisePluginHelperTransaction::url( $record ) . "'>" . stripslashes( $record['id'] ) . "</a>";
		}

		function column_trsf_sent( $record ) {
			$sent     = _x( 'Yes', 'Transfer was sent', 'omise' );
			$not_sent = _x( 'No', 'Transfer was not sent', 'omise' );

			echo $record['sent'] ? '<strong class="Omise-TextSuccess">' . $sent . '</strong>' : $not_sent;
		}

		function column_trsf_paid( $record ) {
			$paid   = _x( 'Yes', 'Transfer was paid', 'omise' );
			$unpaid = _x( 'No', 'Transfer was not paid', 'omise' );

			echo $record['paid'] ? '<strong class="Omise-TextSuccess">' . $paid . '</strong>' : $unpaid;
		}

		function column_trsf_failure( $record ) {
			echo $record['failure_code'] ? '(' . $record['failure_code'] . ') ' . $record['failure_message'] : '-';
		}

		function column_trsf_datetime( $record ) {
			echo Omise_Util::date_format( $record['created'] );
		}
	}
}
