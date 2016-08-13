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
				'trsf_amount'   => Omise_Util::translate( 'Amount' ),
				'trsf_id'       => Omise_Util::translate( 'Transfer Id' ),
				'trsf_sent'     => Omise_Util::translate( 'Sent', 'Transfer table column header' ),
				'trsf_paid'     => Omise_Util::translate( 'Paid' ),
				'trsf_failure'  => Omise_Util::translate( 'Failure Message' ),
				'trsf_datetime' => Omise_Util::translate( 'Created' )
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
			$sent     = Omise_Util::translate( 'Yes', 'Transfer was sent' );
			$not_sent = Omise_Util::translate( 'No', 'Transfer was not sent' );

			echo $record['sent'] ? '<strong class="Omise-TextSuccess">' . $sent . '</strong>' : $not_sent;
		}

		function column_trsf_paid( $record ) {
			$paid   = Omise_Util::translate( 'Yes', 'Transfer was paid' );
			$unpaid = Omise_Util::translate( 'No', 'Transfer was not paid' );

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
