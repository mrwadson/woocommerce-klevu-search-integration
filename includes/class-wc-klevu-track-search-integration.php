<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Klevu Search Integration.
 *
 * @class   WC_Klevu_Search_Integration
 * @extends WC_Integration
 *
 * @property $klevu_search_url
 * @property $klevu_js_api_key
 * @property $klevu_search_min_chars
 * @property $klevu_search_selector
 */
class WC_Klevu_Track_Search_Integration extends WC_Integration {
	public function __construct() {
		$this->id                 = 'klevu_track_search_integration';
		$this->method_title       = __( 'Klevu Search', 'wc-klevu-search-integration' );
		$this->method_description = __( 'Klevu Smart Search tracking Analytics events <a href="https://www.klevu.com/">www.klevu.com</a>.', 'wc-klevu-search-integration' );

		$this->init_form_fields();
		$this->init_settings();
		$this->init_options();

		if ($this->enabled === 'yes') {
			$this->init_klevu_integration();
			$this->track_klevu_analytics();
		}

		// Admin Options
		add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'show_options_info' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets' ) );
	}

	/**
	 * Tells WooCommerce, which settings to display under the "integration" tab.
	 *
	 * @return void
	 */
	public function init_form_fields(): void {
		$this->form_fields = array(
			'enabled' => array(
				'title'       => __( 'Enable', 'wc-klevu-search-integration' ),
				'description' => __( 'Enable integration', 'wc-klevu-search-integration' ),
				'desc_tip'    => 'Enable Klevu Search tracking',
				'type'        => 'checkbox',
				'default'     => 'no',
			),
			'klevu_search_url' => array(
				'title'       => __( 'Klevu APIv2 Search URL', 'wc-klevu-search-integration' ),
				'description' => __( 'Klevu APIv2 Search URL', 'wc-klevu-search-integration' ),
				'desc_tip'    => 'The URL path to send customers to access the full search results.',
				'type'        => 'text',
				'placeholder' => 'https://{{APIv2_Cloud_Search_URL}}/cs/v2/search',
				'class'        => 'klevu-settings',
			),
			'klevu_js_api_key' => array(
				'title'       => __( 'Klevu JS API Key', 'wc-klevu-search-integration' ),
				'description' => __( 'Klevu JS API Key', 'wc-klevu-search-integration' ),
				'desc_tip'    => 'Klevu Search Url',
				'type'        => 'text',
				'placeholder' => 'klevu-14728819608184175',
				'class'        => 'klevu-settings',
			),
			'klevu_search_min_chars' => array(
				'title'       => __( 'Klevu search min chars', 'wc-klevu-search-integration' ),
				'description' => __( 'Klevu search min chars', 'wc-klevu-search-integration' ),
				'desc_tip'    => 'Minimum number of characters to display the search list',
				'type'        => 'number',
				'placeholder' => '0',
				'class'        => 'klevu-settings',
			),
			'klevu_search_selector' => array(
				'title'       => __( 'Klevu search input selector', 'wc-klevu-search-integration' ),
				'description' => __( 'Klevu search input selector', 'wc-klevu-search-integration' ),
				'desc_tip'    => 'The class or ID or handle used to locate the input search box(es)',
				'type'        => 'text',
				'placeholder' => '.search-field', // default WooCommerce search field selector
				'class'        => 'klevu-settings',
			)
		);
	}

	/**
	 * Loads all of our options for this plugin (stored as properties as well).
	 *
	 * @return void
	 */
	public function init_options(): void {
		$options = array(
			'klevu_search_url',
			'klevu_search_min_chars',
			'klevu_search_selector',
			'klevu_js_api_key'
		);
		foreach ( $options as $option ) {
			$this->$option = $this->get_option( $option );
		}
	}

	/**
	 * Init Klevu integration.
	 *
	 * @return void
	 */
	private function init_klevu_integration(): void {
		wp_enqueue_script( 'klevu', 'https://js.klevu.com/core/v2/klevu.js' );
		wp_enqueue_script( 'klevu-search', 'https://js.klevu.com/theme/default/v2/quick-search.js' );
		wp_enqueue_script( 'klevu-search-results-page', 'https://js.klevu.com/theme/default/v2/search-results-page.js' );
		wp_add_inline_script( 'klevu-search-results-page', $this->get_klevu_code() );
	}

	/**
	 * Klevu quick search js code.
	 *
	 * @return string
	 */
	private function get_klevu_code(): string {
		return 'var klevu_lang = "en";
			klevu.interactive(function () {
				var options = {
					url: {
						landing: "/?ktype=klev&post_type=product",
						protocol: "https:",
						search: "' . $this->klevu_search_url . '"
					},
					search: {
						minChars: ' . $this->klevu_search_min_chars. ',
						searchBoxSelector: "' . $this->klevu_search_selector . '",
						apiKey: "' . $this->klevu_js_api_key . '"
					},
					analytics: {
						apiKey: "' . $this->klevu_js_api_key . '"
					}
				};
				klevu(options);
			});';
	}

	/**
	 * Track Woocommerce Klevu searches.
	 *
	 * @return void
	 */
	private function track_klevu_analytics(): void {
		add_action( 'woocommerce_after_shop_loop', array( $this, 'track_product_searches' ) );
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'track_product_clicks_from_search_results' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'track_multiple_order_data' ), 9 );
	}

	/**
	 * Reporting product searches.
	 *
	 * @return void
	 */
	public function track_product_searches(): void {
		if ( is_search() ) {
			$term  = get_search_query();
			$total = (int) wc_get_loop_prop( 'total' );

			$query = array(
				'klevu_apiKey'       => $this->klevu_js_api_key,
				'klevu_term'         => $term,
				'klevu_totalResults' => $total,
				'klevu_typeOfQuery'  => 'WILDCARD_AND'
			);

			$uri  = sprintf( 'https://stats.ksearchnet.com/analytics/n-search/search?%s', http_build_query( $query ) );
			$code = $this->prepareFetchJs( $uri );

			wc_enqueue_js( $code );
		}
	}

	/**
	 * Reporting product clicks from search results.
	 *
	 * @return void
	 */
	public function track_product_clicks_from_search_results(): void {
		global $product;

		if ( $product instanceof WC_Product && is_search() ) {
			$term  = get_search_query();

			$query = array(
				'klevu_apiKey'           => $this->klevu_js_api_key,
				'klevu_keywords'         => $term,
				'klevu_type'             => 'clicked',
				'klevu_productId'        => self::get_product_id ( $product ),
				'klevu_productGroupId'   => self::get_product_group_id( $product ),
				'klevu_productVariantId' => self::get_product_variant_id( $product ),
				'klevu_productName'      => $product->get_name(),
				'klevu_productUrl'       => $product->get_permalink(),
			);

			$uri  = sprintf( 'https://stats.ksearchnet.com/analytics/productTracking?%s', http_build_query( $query ) );
			$code = $this->prepareFetchJs( $uri );

			wc_enqueue_js( "
				$('.products .post-" . esc_js( $product->get_id() ) . " a' ).on('click', function() {
					$code
				});
			" );
		}
	}

	/**
	 * Reporting multiple order data.
	 *
	 * @return void
	 */
	public function track_multiple_order_data(): void {
		global $wp;

		if ( is_order_received_page() ) {
			$order_id = $wp->query_vars['order-received'] ?? 0;
			$order    = wc_get_order( $order_id );

			if ( $order && ! $order->get_meta( '_klevu_tracked' ) ) {
				$order_key = empty( $_GET['key'] ) ? '' : wc_clean( wp_unslash( $_GET['key'] ) );
				if ( ! $order->key_is_valid( $order_key ) ) {
					return;
				}

				$items = array();

				// Order items
				if ( $order_items = $order->get_items() ) {

					/** @var WC_Order_Item_Product $item */
					foreach ( $order_items as $item ) {

						/** @var WC_Product $product */
						$product = $item->get_product();

						$items[] = array(
							'order_id' => $order->get_order_number(),
							'order_line_id' => $order_id,
							'item_name' => $item->get_name(),
							'item_id' => self::get_product_id ( $product ),
							'item_group_id' => self::get_product_group_id( $product ),
							'item_variant_id' => self::get_product_variant_id( $product ),
							'unit_price' => $order->get_item_total( $item ),
							'currency' => $order->get_currency(),
							'units' => $item->get_quantity()
						);
					}
				}

				if ( $items ) {
					$event = array(
						'event'         => 'order_purchase',
						'event_apikey'  => $this->klevu_js_api_key,
						'event_version' => '1.0.0',
						'event_data'    => array(
							'items' => $items
						)
					);

					$uri  = 'https://stats.ksearchnet.com/analytics/collect';
					$code = $this->prepareFetchJs( $uri, 'POST', array( $event ) );

					wc_enqueue_js( "
						$code
					" );

					// Mark the order as tracked.
					$order->update_meta_data( '_klevu_tracked', 1 );
					$order->save();
				}
			}
		}
	}

	/**
	 * Get item identifier from product data.
	 * For compound products consisting of a child/variant and a parent, this is usually parentId-childId.
	 *
	 * @param WC_Product $product WC_Product Object
	 *
	 * @return string
	 */
	public static function get_product_id( WC_Product $product ): string {
		return self::get_product_variant_id( $product );
	}

	/**
	 * Get item identifier from product data.
	 * For simple products is Product ID.
	 *
	 * @param WC_Product $product WC_Product Object
	 *
	 * @return string
	 */
	public static function get_product_group_id( WC_Product $product ): string {
		if ( $product->is_type( 'variation' ) ) {
			return $product->get_parent_id();
		}

		return $product->get_id();
	}

	/**
	 * Get item identifier from product data.
	 *
	 * @param WC_Product $product WC_Product Object
	 *
	 * @return string
	 */
	public static function get_product_variant_id( WC_Product $product ): string {
		return $product->get_id();
	}

	/**
	 * Prepare JS code for GET requests to Klevu server.
	 *
	 * @param string $uri
	 * @param string $method - can be GET or POST
	 * @param array $data
	 *
	 * @return string
	 */
	private function prepareFetchJs( string $uri , string $method = 'GET', array $data = array()): string {
		$json = wp_json_encode($data);
		return "
				var requestOptions = {
					method: '$method',
					redirect: 'follow'
				};
				
				if (requestOptions['method'] === 'POST') {
					requestOptions['headers'] = {
						'Content-Type': 'application/json;charset=utf-8'
					}
					requestOptions['body'] = JSON.stringify($json)
				}

				fetch('$uri', requestOptions)
					.then(response => response.text())
					.then(result => console.log('$uri', requestOptions, result))
					.catch(error => console.log('error', error));
			";
	}

	/**
	 * Enqueue the admin JavaScript
	 */
	public function load_admin_assets(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'woocommerce_page_wc-settings' !== $screen->id ) {
			return;
		}

		if ( empty( $_GET['tab'] ) ) {
			return;
		}

		if ( 'integration' !== $_GET['tab'] ) {
			return;
		}

		wp_enqueue_script( 'ss-klevu-search-admin-enhanced-settings', $this->get_asset_url( 'admin-klevu-settings.js' ), [ 'jquery' ], '0.1.0' );
	}

	/**
	 *
	 * @param string $js_file
	 *
	 * @return string
	 */
	public function get_asset_url( string $js_file ): string {
		return untrailingslashit( plugin_dir_url( plugin_basename( __FILE__ ) ) ) . '/assets/js/' . $js_file;
	}

	/**
	 * Shows some additional help text after saving the settings.
	 *
	 * @return void
	 */
	public function show_options_info(): void {
		$this->method_description .= "<div class='notice notice-info'><p>" . __( 'Please allow Klevu Search up to 48 hours to start displaying results.', 'wc-klevu-search-integration' ) . '</p></div>';
	}
}
