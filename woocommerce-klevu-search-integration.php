<?php /** @noinspection AutoloadingIssuesInspection */
/**
 * Plugin Name: WooCommerce Klevu Search Integration
 * Plugin URI: https://mrwadson.ru/
 * Description: Allows Klevu search tracking events in WooCommerce.
 * Author: MrWadson
 * Author URI: https://mrwadson.ru/
 * Version: 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Klevu_Search_Integration' ) ) {
	class WC_Klevu_Search_Integration {

		/** @var WC_Klevu_Track_Search_Integration Instance of this class */
		private static $instance;

		/**
		 * Initialize the plugin.
		 */
		public function __construct() {
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
				return;
			}

			if ( class_exists( 'WC_Integration' ) ) {
				include_once __DIR__ . '/includes/class-wc-klevu-track-search-integration.php';
				add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			}

			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_links' ) );
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return WC_Klevu_Search_Integration A single instance of this class.
		 */
		public static function get_instance(): self {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Add a new integration to WooCommerce.
		 *
		 * @param array $integrations WooCommerce's integrations.
		 *
		 * @return array               Google Analytics integration added.
		 */
		public function add_integration( array $integrations ): array {
			$integrations[] = 'WC_Klevu_Track_Search_Integration';

			return $integrations;
		}

		/**
		 * Add links on the plugins page.
		 *
		 * @param array $links Default links
		 *
		 * @return array        Default and added links
		 */
		public function plugin_links( array $links ): array {
			$settings_url = $this->get_settings_url();

			$plugin_links = array(
				'settings' => '<a href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'wc-klevu-search-integration' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Gets the settings page URL.
		 *
		 * @return string Settings URL
		 */
		public function get_settings_url(): string {
			return add_query_arg(array(
					'page'    => 'wc-settings',
					'tab'     => 'integration',
					'section' => 'klevu_search_integration'
				),
				admin_url( 'admin.php' )
			);
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'woocommerce' ), '0.1.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'woocommerce' ), '0.1.0' );
		}
	}

	add_action( 'plugins_loaded', array( 'WC_Klevu_Search_Integration', 'get_instance' ), 0 );
}
