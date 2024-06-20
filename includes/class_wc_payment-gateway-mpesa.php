<?php
	/**
	 * Mpesa payment Gateway.
	 *
	 * Provides an online Payment Gateway.
	 *
	 * @class       WC_Gateway_Mpesa
	 * @extends     WC_Payment_Gateway
	 * @version     1.0.0
	 * @package     WooCommerce\Classes\Payment
	 */
	class WC_Gateway_Mpesa extends WC_Payment_Gateway {
			/**
			 * Gateway instructions that will be added to the thank you page and emails.
			 *
			 * @var string
			 */
			public $instructions;

			/**
			 * Enable for shipping methods.
			 *
			 * @var array
			 */
			public $enable_for_methods;

			/**
			 * Enable for virtual products.
			 *
			 * @var bool
			 */
			public $enable_for_virtual;

			/**
			 * Constructor for the gateway.
			 */
			public function __construct() {
				// Setup general properties.
				$this->setup_properties();

				// Load the settings.
				$this->init_form_fields();
				$this->init_settings();

				// Get settings.
				$this->title              = $this->get_option( 'title' );
				$this->description        = $this->get_option( 'description' );
				$this->consumerKey        = $this->get_option( 'consumerKey' );
				$this->consumerSecret        = $this->get_option( 'consumerSecret' );
				$this->BusinessShortCode        = $this->get_option( 'BusinessShortCode' );
				$this->Passkey        = $this->get_option( 'Passkey' );
				$this->PartyB        = $this->get_option( 'PartyB' );
				$this->AccountReference        = $this->get_option( 'AccountReference' );
				$this->TransactionDesc        = $this->get_option( 'TransactionDesc' );
				$this->access_token_url        = $this->get_option( 'access_token_url' );
				$this->initiate_url        = $this->get_option( 'initiate_url' );

				$this->instructions       = $this->get_option( 'instructions' );
				$this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
				$this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes';

				// Actions.
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
				add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );

				// Customer Emails.
				add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
			}

			/**
			 * Setup general properties for the gateway.
			 */
			protected function setup_properties() {
				$this->id                 = 'mpesa';
				$this->icon = apply_filters( 'woocommerce_mpesa_icon', plugins_url('../assets/icon.png', __FILE__ ) );
				$this->method_title       = __( 'Mpesa Mobile Payments', 'mpesa-payments-woo' );
				$this->consumerKey        = __( 'Add Consumer Key', 'mpesa-payments-woo' );
				$this->consumerSecret        = __( 'Add Consumer Secret', 'mpesa-payments-woo' );
				$this->BusinessShortCode        = __( 'Add Business Paybill/Till number', 'mpesa-payments-woo' );
				$this->Passkey        = __( 'Add Passkey', 'mpesa-payments-woo' );
				$this->PartyB        = __( 'Add number linked to Paybill/Till', 'mpesa-payments-woo' );
				$this->AccountReference        = __( 'Add the business/organization name', 'mpesa-payments-woo' );
				$this->TransactionDesc        = __( 'Add the business transaction description', 'mpesa-payments-woo' );
				$this->access_token_url        = __( 'Add access token url', 'mpesa-payments-woo' );
				$this->initiate_url        = __( 'Add initiate url', 'mpesa-payments-woo' );
				$this->method_description = __( 'Use your mobile payments to clear the bill.', 'mpesa-payments-woo' );
				$this->has_fields         = false;
			}

			/**
			 * Initialise Gateway Settings Form Fields.
			 */
			public function init_form_fields() {
				$this->form_fields = array(
					'enabled'            => array(
						'title'       => __( 'Enable/Disable', 'mpesa-payments-woo' ),
						'label'       => __( 'Enable mpesa mobile payments', 'mpesa-payments-woo' ),
						'type'        => 'checkbox',
						'description' => '',
						'default'     => 'no',
					),
					'title'              => array(
						'title'       => __( 'Title', 'mpesa-payments-woo' ),
						'type'        => 'text',
						'description' => __( 'Mpesa payment method description that the customer will see on your checkout.', 'mpesa-payments-woo' ),
						'default'     => __( 'Mpesa mobile payments', 'mpesa-payments-woo' ),
						'desc_tip'    => true,
					),
					'consumerKey'              => array(
						'title'       => __( 'Consumer Key', 'mpesa-payments-woo' ),
						'type'        => 'text',
						'description' => __( 'Add your Consumer Key.', 'mpesa-payments-woo' ),
						'desc_tip'    => true,
					),
					'consumerSecret'              => array(
						'title'       => __( 'Consumer Secret', 'mpesa-payments-woo' ),
						'type'        => 'text',
						'description' => __( 'Add your Consumer Secret.', 'mpesa-payments-woo' ),
						'desc_tip'    => true,
					),
					'BusinessShortCode'              => array(
						'title'       => __( 'BusinessShortCode', 'mpesa-payments-woo' ),
						'type'        => 'text',
						'description' => __( 'Add your Paybill/Till number.', 'mpesa-payments-woo' ),
						'desc_tip'    => true,
					),
					'Passkey'              => array(
						'title'       => __( 'Passkey', 'mpesa-payments-woo' ),
						'type'        => 'text',
						'description' => __( 'Add your Passkey.', 'mpesa-payments-woo' ),
						'desc_tip'    => true,
					),
					'PartyB'              => array(
						'title'       => __( 'PartyB', 'mpesa-payments-woo' ),
						'type'        => 'text',
						'description' => __( 'Add the number linked to your Paybill/Till.', 'mpesa-payments-woo' ),
						'desc_tip'    => true,
					),
					'AccountReference'              => array(
						'title'       => __( 'AccountReference', 'mpesa-payments-woo' ),
						'type'        => 'text',
						'description' => __( 'Add your Business/Organization name.', 'mpesa-payments-woo' ),
						'desc_tip'    => true,
					),
					'TransactionDesc'              => array(
						'title'       => __( 'TransactionDesc', 'mpesa-payments-woo' ),
						'type'        => 'text',
						'description' => __( 'Add your Business Transaction Description.', 'mpesa-payments-woo' ),
						'desc_tip'    => true,
					),
					'access_token_url'              => array(
						'title'       => __( 'access_token_url', 'mpesa-payments-woo' ),
						'type'        => 'text',
						'description' => __( 'Add your access_token_url.', 'mpesa-payments-woo' ),
						'desc_tip'    => true,
					),
					'initiate_url'              => array(
						'title'       => __( 'ainitiate_url', 'mpesa-payments-woo' ),
						'type'        => 'text',
						'description' => __( 'Add your initiate_url.', 'mpesa-payments-woo' ),
						'desc_tip'    => true,
					),
					'description'        => array(
						'title'       => __( 'Description', 'mpesa-payments-woo' ),
						'type'        => 'textarea',
						'description' => __( 'Mpesa payment method description that the customer will see on your website.', 'mpesa-payments-woo' ),
						'default'     => __( 'Pay with your mobile cash balance.', 'mpesa-payments-woo' ),
						'desc_tip'    => true,
					),
					'instructions'       => array(
						'title'       => __( 'Instructions', 'mpesa-payments-woo' ),
						'type'        => 'textarea',
						'description' => __( 'Instructions that will be added to the thank you page.', 'mpesa-payments-woo' ),
						'default'     => __( 'Pay with your mobile cash balance.', 'mpesa-payments-woo' ),
						'desc_tip'    => true,
					),
					'enable_for_methods' => array(
						'title'             => __( 'Enable for shipping methods', 'mpesa-payments-woo' ),
						'type'              => 'multiselect',
						'class'             => 'wc-enhanced-select',
						'css'               => 'width: 400px;',
						'default'           => '',
						'description'       => __( 'If mpesa is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'mpesa-payments-woo' ),
						'options'           => $this->load_shipping_method_options(),
						'desc_tip'          => true,
						'custom_attributes' => array(
							'data-placeholder' => __( 'Select shipping methods', 'mpesa-payments-woo' ),
						),
					),
					'enable_for_virtual' => array(
						'title'   => __( 'Accept for virtual orders', 'mpesa-payments-woo' ),
						'label'   => __( 'Accept mpesa if the order is virtual', 'mpesa-payments-woo' ),
						'type'    => 'checkbox',
						'default' => 'yes',
					),
				);
			}

			/**
			 * Check If The Gateway Is Available For Use.
			 *
			 * @return bool
			 */
			public function is_available() {
				$order          = null;
				$needs_shipping = false;

				// Test if shipping is needed first.
				if ( WC()->cart && WC()->cart->needs_shipping() ) {
					$needs_shipping = true;
				} elseif ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
					$order_id = absint( get_query_var( 'order-pay' ) );
					$order    = wc_get_order( $order_id );

					// Test if order needs shipping.
					if ( $order && 0 < count( $order->get_items() ) ) {
						foreach ( $order->get_items() as $item ) {
							$_product = $item->get_product();
							if ( $_product && $_product->needs_shipping() ) {
								$needs_shipping = true;
								break;
							}
						}
					}
				}

				$needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );

				// Virtual order, with virtual disabled.
				if ( ! $this->enable_for_virtual && ! $needs_shipping ) {
					return false;
				}

				// Only apply if all packages are being shipped via chosen method, or order is virtual.
				if ( ! empty( $this->enable_for_methods ) && $needs_shipping ) {
					$order_shipping_items            = is_object( $order ) ? $order->get_shipping_methods() : false;
					$chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

					if ( $order_shipping_items ) {
						$canonical_rate_ids = $this->get_canonical_order_shipping_item_rate_ids( $order_shipping_items );
					} else {
						$canonical_rate_ids = $this->get_canonical_package_rate_ids( $chosen_shipping_methods_session );
					}

					if ( ! count( $this->get_matching_rates( $canonical_rate_ids ) ) ) {
						return false;
					}
				}

				return parent::is_available();
			}

			/**
			 * Checks to see whether or not the admin settings are being accessed by the current request.
			 *
			 * @return bool
			 */
			private function is_accessing_settings() {
				if ( is_admin() ) {
					// phpcs:disable WordPress.Security.NonceVerification
					if ( ! isset( $_REQUEST['page'] ) || 'wc-settings' !== $_REQUEST['page'] ) {
						return false;
					}
					if ( ! isset( $_REQUEST['tab'] ) || 'checkout' !== $_REQUEST['tab'] ) {
						return false;
					}
					if ( ! isset( $_REQUEST['section'] ) || 'mpesa' !== $_REQUEST['section'] ) {
						return false;
					}
					// phpcs:enable WordPress.Security.NonceVerification

					return true;
				}

				// if ( Constants::is_true( 'REST_REQUEST' ) ) {
				// 	global $wp;
				// 	if ( isset( $wp->query_vars['rest_route'] ) && false !== strpos( $wp->query_vars['rest_route'], '/payment_gateways' ) ) {
				// 		return true;
				// 	}
				// }

				return false;
			}

			/**
			 * Loads all of the shipping method options for the enable_for_methods field.
			 *
			 * @return array
			 */
			private function load_shipping_method_options() {
				// Since this is expensive, we only want to do it if we're actually on the settings page.
				if ( ! $this->is_accessing_settings() ) {
					return array();
				}

				$data_store = WC_Data_Store::load( 'shipping-zone' );
				$raw_zones  = $data_store->get_zones();
				$zones      = array();

				foreach ( $raw_zones as $raw_zone ) {
					$zones[] = new WC_Shipping_Zone( $raw_zone );
				}

				$zones[] = new WC_Shipping_Zone( 0 );

				$options = array();
				foreach ( WC()->shipping()->load_shipping_methods() as $method ) {

					$options[ $method->get_method_title() ] = array();

					// Translators: %1$s shipping method name.
					$options[ $method->get_method_title() ][ $method->id ] = sprintf( __( 'Any &quot;%1$s&quot; method', 'mpesa-payments-woo' ), $method->get_method_title() );

					foreach ( $zones as $zone ) {

						$shipping_method_instances = $zone->get_shipping_methods();

						foreach ( $shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance ) {

							if ( $shipping_method_instance->id !== $method->id ) {
								continue;
							}

							$option_id = $shipping_method_instance->get_rate_id();

							// Translators: %1$s shipping method title, %2$s shipping method id.
							$option_instance_title = sprintf( __( '%1$s (#%2$s)', 'mpesa-payments-woo' ), $shipping_method_instance->get_title(), $shipping_method_instance_id );

							// Translators: %1$s zone name, %2$s shipping method instance name.
							$option_title = sprintf( __( '%1$s &ndash; %2$s', 'mpesa-payments-woo' ), $zone->get_id() ? $zone->get_zone_name() : __( 'Other locations', 'mpesa-payments-woo' ), $option_instance_title );

							$options[ $method->get_method_title() ][ $option_id ] = $option_title;
						}
					}
				}

				return $options;
			}

			/**
			 * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
			 *
			 * @since  3.4.0
			 *
			 * @param  array $order_shipping_items  Array of WC_Order_Item_Shipping objects.
			 * @return array $canonical_rate_ids    Rate IDs in a canonical format.
			 */
			private function get_canonical_order_shipping_item_rate_ids( $order_shipping_items ) {

				$canonical_rate_ids = array();

				foreach ( $order_shipping_items as $order_shipping_item ) {
					$canonical_rate_ids[] = $order_shipping_item->get_method_id() . ':' . $order_shipping_item->get_instance_id();
				}

				return $canonical_rate_ids;
			}

			/**
			 * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
			 *
			 * @since  3.4.0
			 *
			 * @param  array $chosen_package_rate_ids Rate IDs as generated by shipping methods. Can be anything if a shipping method doesn't honor WC conventions.
			 * @return array $canonical_rate_ids  Rate IDs in a canonical format.
			 */
			private function get_canonical_package_rate_ids( $chosen_package_rate_ids ) {

				$shipping_packages  = WC()->shipping()->get_packages();
				$canonical_rate_ids = array();

				if ( ! empty( $chosen_package_rate_ids ) && is_array( $chosen_package_rate_ids ) ) {
					foreach ( $chosen_package_rate_ids as $package_key => $chosen_package_rate_id ) {
						if ( ! empty( $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ] ) ) {
							$chosen_rate          = $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ];
							$canonical_rate_ids[] = $chosen_rate->get_method_id() . ':' . $chosen_rate->get_instance_id();
						}
					}
				}

				return $canonical_rate_ids;
			}

			/**
			 * Indicates whether a rate exists in an array of canonically-formatted rate IDs that activates this gateway.
			 *
			 * @since  3.4.0
			 *
			 * @param array $rate_ids Rate ids to check.
			 * @return array
			 */
			private function get_matching_rates( $rate_ids ) {
				// First, match entries in 'method_id:instance_id' format. Then, match entries in 'method_id' format by stripping off the instance ID from the candidates.
				return array_unique( array_merge( array_intersect( $this->enable_for_methods, $rate_ids ), array_intersect( $this->enable_for_methods, array_unique( array_map( 'wc_get_string_before_colon', $rate_ids ) ) ) ) );
			}

			/**
			 * Process the payment and return the result.
			 *
			 * @param int $order_id Order ID.
			 * @return array
			 */

			 public function process_payment($order_id) {
				$order = wc_get_order($order_id);
			
				if ($order->get_total() > 0) {
					$payment_result = $this->mpesa_payment_processing($order);
			
					if ($payment_result['result'] === 'success') {
						// Return thank you redirect.
						return array(
							'result' => 'success',
							'redirect' => $payment_result['redirect'],
						);
					} else {
						wc_add_notice(__('Payment error: ', 'mpesa-payments-woo') . $payment_result['message'], 'error');
						return array(
							'result' => 'failure',
							'redirect' => '',
						);
					}
				} else {
					$order->payment_complete();
			
					// Remove cart.
					WC()->cart->empty_cart();
			
					// Return thank you redirect.
					return array(
						'result' => 'success',
						'redirect' => $this->get_return_url($order),
					);
				}
			}
			
			private function mpesa_payment_processing($order) {
				// Sanitize the phone number input
				$phone = esc_attr($_POST['payment_number']);
			
				// Set timezone to Nairobi
				date_default_timezone_set('Africa/Nairobi');
			
				// Initialize necessary variables from class properties
				$consumerKey = $this->consumerKey;
				$consumerSecret = $this->consumerSecret;
				$BusinessShortCode = $this->BusinessShortCode; // Your Paybill/Till Number
				$Passkey = $this->Passkey;
			
				$PartyA = $phone; // Customer phone number
				$PartyB = $this->PartyB; // Business short code
				$AccountReference = $this->AccountReference;
				$TransactionDesc = $this->TransactionDesc;
				$Amount = intval($order->get_total());
			
				// Get the timestamp in the required format
				$Timestamp = date('YmdHis');
			
				// Generate the password for M-PESA API
				$Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);
			
				// Headers for accessing the token
				$headers = ['Content-Type:application/json; charset=utf8'];
			
				// M-PESA endpoint URLs
				$access_token_url = $this->access_token_url;
				$initiate_url = $this->initiate_url;
			
				// Callback URL for M-PESA responses
				$CallBackURL = 'https://everosacollections.com/darajaapp/callback.php';
			
				// Get the access token from M-PESA
				$curl = curl_init($access_token_url);
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($curl, CURLOPT_HEADER, FALSE);
				curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30); // Connection timeout of 30 seconds
				curl_setopt($curl, CURLOPT_TIMEOUT, 60); // Total timeout of 60 seconds
				$result = curl_exec($curl);
				$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
				if ($status !== 200) {
					// Handle error
					return array(
						'result' => 'failure',
						'message' => "Failed to get access token: HTTP status $status",
					);
				}
			
				$result = json_decode($result);
				if (isset($result->errorCode)) {
					// Handle error
					return array(
						'result' => 'failure',
						'message' => "Failed to get access token: " . $result->errorMessage,
					);
				}
			
				$access_token = $result->access_token;
				curl_close($curl);
			
				// Headers for STK push request
				$stkheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];
			
				// Initiate the transaction
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $initiate_url);
				curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader);
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30); // Connection timeout of 30 seconds
				curl_setopt($curl, CURLOPT_TIMEOUT, 60); // Total timeout of 60 seconds
			
				$curl_post_data = array(
					'BusinessShortCode' => $BusinessShortCode,
					'Password' => $Password,
					'Timestamp' => $Timestamp,
					'TransactionType' => 'CustomerPayBillOnline',
					'Amount' => $Amount,
					'PartyA' => $PartyA,
					'PartyB' => $BusinessShortCode,
					'PhoneNumber' => $PartyA,
					'CallBackURL' => $CallBackURL,
					'AccountReference' => $AccountReference,
					'TransactionDesc' => $TransactionDesc,
				);
			
				$data_string = json_encode($curl_post_data);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
				$curl_response = curl_exec($curl);
			
				if ($curl_response === false) {
					$error_message = curl_error($curl);
					curl_close($curl);
					return array(
						'result' => 'failure',
						'message' => "Curl error: $error_message",
					);
				}
			
				$response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				curl_close($curl);
			
				if ($response_code !== 200) {
					// Handle non-200 response code
					return array(
						'result' => 'failure',
						'message' => "Failed to initiate transaction: HTTP status $response_code",
					);
				}
			
				$data_to = json_decode($curl_response);
			
				if (isset($data_to->errorCode)) {
					// Handle M-PESA API error
					return array(
						'result' => 'failure',
						'message' => "M-PESA API error: " . $data_to->errorMessage,
					);
				}
			
				// Check for successful response from M-PESA
				if (isset($data_to->ResponseCode) && $data_to->ResponseCode == '0') {
					$order->update_status('processing', __('Payment received, processing order.', 'mpesa-payments-woo'));
			
					// Remove cart
					WC()->cart->empty_cart();
			
					// Return success response
					return array(
						'result' => 'success',
						'redirect' => $this->get_return_url($order),
					);
				} else {
					// Payment pending
					$order->update_status(apply_filters('woocommerce_mpesa_process_payment_order_status', $order->has_downloadable_item() ? 'wc-invoiced' : 'processing', $order), __('Payments pending.', 'mpesa-payments-woo'));
				}
			
				return array(
					'result' => 'failure',
					'message' => 'Payment initiation failed',
				);
			}			
			
			

			/**
			 * Output for the order received page.
			 */
			public function thankyou_page() {
				if ( $this->instructions ) {
					echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
				}
			}

			/**
			 * Change payment complete order status to completed for mpesa orders.
			 *
			 * @since  3.1.0
			 * @param  string         $status Current order status.
			 * @param  int            $order_id Order ID.
			 * @param  WC_Order|false $order Order object.
			 * @return string
			 */
			public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
				if ( $order && 'mpesa' === $order->get_payment_method() ) {
					$status = 'completed';
				}
				return $status;
			}

			/**
			 * Add content to the WC emails.
			 *
			 * @param WC_Order $order Order object.
			 * @param bool     $sent_to_admin  Sent to admin.
			 * @param bool     $plain_text Email format: plain text or HTML.
			 */
			public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
				if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
					echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
				}
			}
		}
	