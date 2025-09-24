<?php

use Brain\Monkey;
use voku\helper\HtmlDomParser;

/**
 * @runTestsInSeparateProcesses
 */
class Omise_MyAccount_Test extends Omise_Test_Case {

	private $omise_setting;

	protected function setUp(): void {
		parent::setUp();

		Monkey\Functions\stubs(
			[
				'wp_kses' => null,
				'plugins_url' => null,
				'admin_url' => null,
			]
		);

		$wc = Mockery::mock( 'WC' );
		$wc->shouldReceive( 'plugin_url' )->andReturn( '' );
		Monkey\Functions\expect( 'WC' )->andReturn( $wc );

		$omisePaymentMock = Mockery::mock( 'overload:Omise_Payment' );
		$omisePaymentMock->shouldReceive( 'init_settings' );
		$omisePaymentMock->shouldReceive( 'get_option' );

		$omise_card_form = Mockery::mock( 'alias:Omise_Page_Card_From_Customization' );
		$omise_card_form->shouldReceive( 'get_instance' )->andReturn( $omise_card_form );
		$omise_card_form->shouldReceive( 'get_design_setting' )->andReturn( [] );

		$this->omise_setting = $this->mockOmiseSetting( 'pkey_test_123', 'skey_test_123' );

		require_once PLUGIN_PATH . '/includes/gateway/traits/charge-request-builder-trait.php';
		require_once PLUGIN_PATH . '/includes/gateway/traits/sync-order-trait.php';
		require_once PLUGIN_PATH . '/includes/gateway/abstract-omise-payment-base-card.php';
		require_once PLUGIN_PATH . '/includes/gateway/class-omise-payment-creditcard.php';

		load_plugin();
	}

	public function test_init_panel_renders_add_new_card_form() {
		$this->omise_setting->shouldReceive( 'is_test' )->andReturn( true );
		$current_user = $this->mock_wp_user();
		$this->mock_customer_with_cards( $current_user->test_omise_customer_id, [] );

		Monkey\Functions\stubs(
			[
				'is_user_logged_in' => true,
				'wp_get_current_user' => $current_user,
				'wp_enqueue_script',
				'wp_localize_script',
				'plugin_dir_path' => __DIR__ . '/../../../',
				'get_locale' => 'en_US',
				'wp_nonce_field' => function () {
					$markup = '<input type="hidden" name="omise_add_card_nonce" value="nonce_123" />';
					echo $markup;
					return $markup;
				},
			]
		);

		ob_start();
		Omise_MyAccount::get_instance()->init_panel();
		$output = ob_get_clean();
		$page = HtmlDomParser::str_get_html( $output );

		$this->assertStringContainsString( 'Add new card', $page );
		$this->assertNotFalse( $page->findOneOrFalse( 'button#omise_add_new_card' ) );

		$add_card_form = $page->findOneOrFalse( '#omise_card_panel form#omise_cc_form' );
		$this->assertNotFalse( $add_card_form );
		$this->assertNotFalse( $add_card_form->findOneOrFalse( 'input[name="omise_add_card_nonce"]' ) );
		$this->assertNotFalse( $add_card_form->findOneOrFalse( '#omise-card' ) );
	}

	public function test_init_panel_renders_saved_card_list() {
		$this->omise_setting->shouldReceive( 'is_test' )->andReturn( true );
		$current_user = $this->mock_wp_user();
		$card = [
			'object' => 'card',
			'id' => 'card_test_123',
			'name' => 'Somchai Prasert',
			'brand' => 'Visa',
			'last_digits' => '4242',
			'expiration_month' => 2,
			'expiration_year' => 2024,
			'created_at' => '2019-12-31T12:59:59Z',
		];
		$this->mock_customer_with_cards( $current_user->test_omise_customer_id, [ $card ] );

		Monkey\Functions\stubs(
			[
				'is_user_logged_in' => true,
				'wp_get_current_user' => $current_user,
				'wp_enqueue_script',
				'wp_localize_script',
				'plugin_dir_path' => __DIR__ . '/../../../',
				'get_option' => 'F j, Y', // WordPress date format
				'get_locale' => 'en_US',
				'wp_nonce_field',
			]
		);

		// Expectations
		Monkey\Functions\expect( 'wp_create_nonce' )
			->once()
			->with( 'omise_delete_card_card_test_123' )
			->andReturn( 'nonce_test_123' );
		Monkey\Functions\expect( 'date_i18n' )
			->once()
			->with( 'F j, Y', strtotime( '2019-12-31T12:59:59Z' ) )
			->andReturn( 'December 12, 2019' );

		ob_start();
		Omise_MyAccount::get_instance()->init_panel();
		$output = ob_get_clean();
		$page = HtmlDomParser::str_get_html( $output );

		$card_table = $page->findOneOrFalse( '#omise_card_panel > table' );
		$this->assertNotFalse( $card_table );

		$card_row = $card_table->findOneOrFalse( 'tbody tr:nth-child(1)' );
		$this->assertNotFalse( $card_row );
		$this->assertStringContainsString( 'Somchai Prasert', $card_row );
		$this->assertStringContainsString( 'XXXX XXXX XXXX 4242', $card_row );
		$this->assertStringContainsString( 'December 12, 2019', $card_row );
		$this->assertNotFalse( $card_row->findOneOrFalse( 'button.delete_card[data-card-id=card_test_123][data-delete-card-nonce=nonce_test_123]' ) );
	}

	public function test_init_panel_renders_empty_card_list_when_user_has_no_saved_cards() {
		$this->omise_setting->shouldReceive( 'is_test' )->andReturn( true );
		$current_user = $this->mock_wp_user();
		$this->mock_customer_with_cards( $current_user->test_omise_customer_id, [] );

		Monkey\Functions\stubs(
			[
				'is_user_logged_in' => true,
				'wp_get_current_user' => $current_user,
				'wp_enqueue_script',
				'wp_localize_script',
				'plugin_dir_path' => __DIR__ . '/../../../',
				'get_locale' => 'en_US',
				'wp_nonce_field' => function () {
					$markup = '<input type="hidden" name="omise_add_card_nonce" value="nonce_123" />';
					echo $markup;
					return $markup;
				},
			]
		);

		ob_start();
		Omise_MyAccount::get_instance()->init_panel();
		$output = ob_get_clean();
		$page = HtmlDomParser::str_get_html( $output );

		$card_table_body = $page->findOneOrFalse( '#omise_card_panel > table > tbody' );
		$this->assertNotFalse( $card_table_body );
		$this->assertEmpty( $card_table_body->innerHtml() );
	}

	public function test_init_panel_renders_nothing_for_guest_users() {
		Monkey\Functions\stubs(
			[
				'is_user_logged_in' => false,
			]
		);

		ob_start();
		Omise_MyAccount::get_instance()->init_panel();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	public function test_register_omise_my_account_scripts() {
		$current_user = $this->mock_wp_user();
		$this->omise_setting->shouldReceive( 'is_test' )->andReturn( true );

		Monkey\Functions\stubs(
			[
				'is_user_logged_in' => true,
				'wp_get_current_user' => $current_user,
			]
		);

		// Expectations
		Monkey\Functions\expect( 'wp_enqueue_script' )
			->once()
			->with( 'omise-js', Omise::OMISE_JS_LINK, [ 'jquery' ], WC_VERSION, true );
		Monkey\Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'embedded-js',
				'/assets/javascripts/omise-embedded-card.js',
				[],
				OMISE_WOOCOMMERCE_PLUGIN_VERSION,
				true
			);
		Monkey\Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'omise-myaccount-card-handler',
				'/assets/javascripts/omise-myaccount-card-handler.js',
				[ 'omise-js' ],
				WC_VERSION,
				true
			);
		Monkey\Functions\expect( 'wp_localize_script' )
			->once()
			->with(
				'omise-myaccount-card-handler',
				'omise_params',
				Mockery::subset(
					[
						'key' => 'pkey_test_123',
						'account_email' => 'johndoe@example.com',
					]
				)
			);

		Omise_MyAccount::get_instance()->register_omise_my_account_scripts();
	}

	function mock_wp_user( $attrs = [] ) {
		$current_user = Mockery::mock( 'WP_User' );
		$current_user->ID = $attrs['ID'] ?? 1;
		$current_user->test_omise_customer_id = $attrs['test_omise_customer_id'] ?? 'cust_test_123';
		$current_user->live_omise_customer_id = $attrs['live_omise_customer_id'] ?? 'cust_123';
		$current_user->user_email = $attrs['email'] ?? 'johndoe@example.com';

		return $current_user;
	}

	function mock_customer_with_cards( $customer_id, $cards = [] ) {
		$customer = Mockery::mock( 'stdClass' );
		$customer->shouldReceive( 'cards' )->andReturn( [ 'data' => $cards ] );

		$omise_customer = Mockery::mock( 'alias:OmiseCustomer' );
		$omise_customer->shouldReceive( 'retrieve' )
			->with( $customer_id )
			->andReturn( $customer );

		return $customer;
	}
}
