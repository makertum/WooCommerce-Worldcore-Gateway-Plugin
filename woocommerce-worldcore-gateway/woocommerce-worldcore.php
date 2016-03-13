<?php
class MKT_WORLDCORE extends WC_Payment_Gateway{
	// GATEWAY SETUP
	function __construct() {

		$this->id = "mkt_worldcore";
		$this->method_title = __( "Worldcore", 'mkt-worldcore' );
		$this->method_description = __( "Worldcore Payment Gateway Plug-in for WooCommerce", 'mkt-worldcore' );
		$this->title = __( "Worldcore", 'mkt-worldcore' );
		$this->icon = "https://worldcore.eu/images/pink_logo.png";

		// Since this a pure redirect gateway, we have no fields
		$this->has_fields = false;

		// Initializing backend settings form
		$this->init_form_fields();

		// Initializing backend settings
		$this->init_settings();

		// Making settings available as variables for later use
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}

		// SSL check
		add_action( 'admin_notices', array( $this,	'do_ssl_check' ) );

		// Save settings
		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		// Setting up WC_API endpoint for processing SCI status callback
		add_action('woocommerce_api_'.strtolower(get_class($this)), array(&$this, 'process_sci_callback'));
		// apparently this only works via http://example.com/?wc-api=mkt_worldcore and not via http://example.com/wc-api/mkt_worldcore
		$this->sci_response_url=str_replace( 'https:', 'http:', add_query_arg( 'wc-api', 'MKT_WORLDCORE', home_url( '/' ) ) );

	}

	// BUILD ADMINISTRATION PANEL
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'		=> __( 'Enable / Disable', 'mkt-worldcore' ),
				'label'		=> __( 'Enable this payment gateway', 'mkt-worldcore' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'title' => array(
				'title'		=> __( 'Title', 'mkt-worldcore' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Payment title the customer will see during the checkout process.', 'mkt-worldcore' ),
				'default'	=> __( 'Worldcore', 'mkt-worldcore' ),
			),
			'description' => array(
				'title'		=> __( 'Description', 'mkt-worldcore' ),
				'type'		=> 'textarea',
				'desc_tip'	=> __( 'Payment description the customer will see during the checkout process.', 'mkt-worldcore' ),
				'default'	=> __( 'Pay easily and fast via worldcore.eu', 'mkt-worldcore' ),
				'css'		=> 'max-width:350px;'
			),
			'api_key' => array(
				'title'		=> __( 'Worldcore API Key', 'mkt-worldcore' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'This is the API Key according API page of Worldcore Member\'s area.', 'mkt-worldcore' ),
			),
			'api_password' => array(
				'title'		=> __( 'Worldcore API Password', 'mkt-worldcore' ),
				'type'		=> 'password',
				'desc_tip'	=> __( 'This is the API Password according API page of Worldcore Member\'s area.', 'mkt-worldcore' ),
			),
			'api_account' => array(
				'title'		=> __( 'Worldcore Reception Account Number', 'mkt-worldcore' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'This is the Worldcore Account that receives the payments.', 'mkt-worldcore' ),
			)/*,
			'sandbox_mode' => array(
				'title'		=> __( 'Sandbox Mode', 'mkt-worldcore' ),
				'label'		=> __( 'Enable Sandbox Mode', 'mkt-worldcore' ),
				'type'		=> 'checkbox',
				'description' => __( 'Turn on sandbox mode for testing.', 'mkt-worldcore' ),
				'default'	=> 'no',
				)*/
			);
		}

	// PROCESSING THE SCI CALLBACK
	function process_sci_callback() {
		// todo: implement logging
		//define(LOG_PATH, realpath(dirname(__FILE__)).'/log');
		//file_put_contents(LOG_PATH.'/log.txt', 'something we want to log'."\n", FILE_APPEND);
		$headers=apache_request_headers();
		$json_body=file_get_contents('php://input');
		$hash_check=strtoupper(hash('sha256', $json_body.$this->api_password));

 		// Hash checking
		if($headers['Wsignature']==$hash_check){
			global $woocommerce;

			// decoding response and retrieving order id
			$response=json_decode($json_body,true);
			$order_id=str_replace("order #","",$response['invoiceId']);

			// Retrieving order information so that we know who had to pay how much for which order
			$customer_order = new WC_Order( $order_id );

			// Checking validity of response (order information must be found plus the correct amount must have been transferred to the correct worldcore account)
			if(
				$customer_order &&
				$response['amount']==$customer_order->order_total &&
				$response['account']==$this->api_account
			){
				// Payment is valid
				// Add success note to order
				$customer_order->add_order_note( __( 'Worldcore payment completed, tracking id: '.$response['track'], 'mkt-worldcore' ) );

				// Mark order as Paid
				$customer_order->payment_complete();

			}else{
				// Payment is invalid
				// we could cancel the order here, but for now I will just add a note to the order that encourages manual checking
				$customer_order->add_order_note( __( 'Worldcore payment failed. Recommending manual check.', 'mkt-worldcore' ) );
			}
		}else{
			// Some code to log invalid payments maybe?
			// We don't log anything at the moment
		}
	}

		// PROCESSING PAYMENT VIA WORLDCORE FORWARDING
		public function process_payment( $order_id ) {
			global $woocommerce;

			// Retrieving order information so that we know who has to pay how much
			$customer_order = new WC_Order( $order_id );

			// Setting API url for environment
			$api_url = 'https://api.worldcore.eu/v1/merchant';
			/*
			$api_url = ( $this->sandbox == "yes" )
			? 'https://api.worldcore.eu/sandbox/merchant'
			: 'https://api.worldcore.eu/v1/merchant';
			*/

			// Assembling POST string
			$post_str=json_encode(array(
				'account' => $this->api_account,
				'amount' => $customer_order->order_total,
				'invoiceId' => 'order #'.$customer_order->get_order_number()
			));

			// Prepping authentification
			$hash_in=strtoupper(hash('sha256', $post_str.$this->api_password));
			$auth_header='Authorization: wauth key='.$this->api_key.', hash='.$hash_in;

			// Requesting URL for redirect via curl
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $api_url);
			curl_setopt($curl, CURLOPT_HEADER, true);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', $auth_header));
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post_str);
			curl_setopt($curl, CURLOPT_TIMEOUT, 30);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$curl_response = curl_exec($curl);

			// Processing curl response
			if($curl_response==false){
				// There was no response
				$error_msg = curl_error($curl);
				// Processing the error
				wc_add_notice( 'Curl error: '.$error_msg, 'error' );
				$customer_order->add_order_note( __( 'Curl error: '.$error_msg, 'mkt-worldcore' ) );
				throw new Exception( __( $error_msg, 'mkt-worldcore' ) );
			}else{
				// We have a response, processing the response
				list($response_headers, $json_response)=explode("\r\n\r\n", $curl_response, 2);
				preg_match("/^WSignature: ([A-Z0-9]{64})\r$/m", $response_headers, $hash_outputed);
				$hash_check=strtoupper(hash('sha256', $json_response.$this->api_password));

				// Hash checking
				if($hash_outputed[1]!=$hash_check){
					// The hash did not match
					// Processing the error
					wc_add_notice( 'Authentification error: Hash mismatch', 'error' );
					$customer_order->add_order_note( __( 'Authentification error: Hash mismatch', 'mkt-worldcore' ) );
					throw new Exception( __( "Hash does not match!", 'mkt-worldcore' ) );
				}else{
					// The hash matched, decoding response
					$decoded_response=json_decode($json_response, true);
					if(isset($decoded_response['error'])){
						// The response indicates an error
						// processing the error
						wc_add_notice( 'Gateway error: '.json_encode($decoded_response['error']), 'error' );
						$customer_order->add_order_note( __( 'Gateway error: '.json_encode($decoded_response['error']), 'mkt-worldcore' ) );
						throw new Exception( __( $decoded_response['error'], 'mkt-worldcore' ) );
					}else{
						// The response is fine, emptying cart and redirecting
						$woocommerce->cart->empty_cart();
						// processing the successful redirect
						return array(
							'result'   => 'success',
							'redirect' => $decoded_response['data']['url']
						);
					}
				}
			}
			curl_close($curl);
		}
	}

?>
