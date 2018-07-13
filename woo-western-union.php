<?php
$mailIme;
/*

Plugin Name: Woocommerce Western Union Payment Addon

Plugin URI: https://daxdax89.com

Description: Integrates Western Union with Woocommerce with name randomizing and order settings

Version: 1.0

Author: DaX

Author URI: https://daxdax89.com

License: GPL2

*/

if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly.

}

// Make sure WooCommerce is active

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	return;

}


//=========================================================================

/**

 * function woo_western_union_install

 *

 */

function woo_western_union_install(){

	$content = '<h2>Submit Western Union Payment Information</h2>

	<hr>

	[wu_form]

	';

	if(! post_exists('Western Union Form', $content)){

		wp_insert_post( array(

			'post_title'     => 'Western Union Form',

			'post_name'      => 'wu-form',

			'post_status'    => 'publish',

			'post_type'      => 'page',

			'post_content'   => $content,

			'comment_status' => 'closed',

			'ping_status'    => 'closed'

		) );

	}

}

register_activation_hook(__FILE__,'woo_western_union_install');

//=========================================================================


//=========================================================================

/**

 * function wc_western_union_add_to_gateways

 * 

 * @param array $gateways

 * @return array

 */

add_filter( 'woocommerce_payment_gateways', 'wc_western_union_add_to_gateways' );

function wc_western_union_add_to_gateways( $gateways ) {

	$gateways['western_union'] = 'WC_Western_Union';

	return $gateways;

}

//=========================================================================

//=========================================================================

/**

 * function wc_western_union_gateway_plugin_links

 * 

 * @param array $links

 * @return array

 */

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_western_union_gateway_plugin_links' );

function wc_western_union_gateway_plugin_links( $links ) {

	$plugin_links = array(

		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=western_union' ) . '">' . __( 'Configure', 'wc-gateway-wu' ) . '</a>'

	);

	return array_merge( $plugin_links, $links );

}

//=========================================================================
//=========================================================================

/**

 * function wc_western_union_submit_payment

 * 

 */

add_filter( 'wc_get_template', 'wc_western_union_template_function' ,10,5);

function wc_western_union_template_function( $located, $template_name, $args, $template_path, $default_path ) {



	if($template_name === 'order/order-details-customer.php'){

		$located = plugin_dir_path( __FILE__ ) . '/assets/order-details-customer.php';

	}

	return $located;

	

}

//=========================================================================

//=========================================================================

/**

 * function wc_western_union_submit_payment

 * 

 */

add_action('woocommerce_thankyou', 'wc_western_union_submit_payment', 10, 1);

function wc_western_union_submit_payment( $order_id ) {



	if ( ! $order_id )

		return;



	$order = wc_get_order($order_id);

	if($order->payment_method === 'western_union'){

		echo "<a href='/purchase/wu-form/' class='thank-you-submit-payment' >Submit Payment</a>";

	}

}

//=========================================================================

//=========================================================================

/**

 * function add_wc_wu_styles

 * 

 */

add_action( 'wp_enqueue_scripts', 'add_wc_wu_styles' );

function add_wc_wu_styles() {



	wp_enqueue_style( 'wc-wu-style', plugins_url().'/woo-western-union/assets/wc-wu-theme.css');

	

}

//=========================================================================

//=========================================================================

/**

 * Add [wu-form]

 * 

 */

add_shortcode( 'wu_form', 'wu_form_func' );

function wu_form_func( ) {

	ob_start();

	if($_POST['customer-mtcn']){

		$data = filter_var_array($_POST, FILTER_SANITIZE_STRING);

		$data = array_map(function($value){

			$value = str_replace('"', "", $value);

			$value = str_replace("'", "", $value);

			return $value;

		}, $data);

		if($data != null && count($data) === 7){

			$order = wc_get_order($data['order-id']);

			if($order){

				$order->update_meta_data('WU-email', $data['customer-email']);

				$order->update_meta_data('WU-first-name', $data['customer-name']);

				$order->update_meta_data('WU-last-name', $data['customer-last-name']);

				$order->update_meta_data('WU-payment-country', $data['customer-payment-country']);

				$order->update_meta_data('WU-mtcn', $data['customer-mtcn']);

				$order->save();

				wc_print_notice( __( 'Your information has been sent', 'wu_payment' ), 'success' );

			}else{

				wc_print_notice( __( 'Order number not found', 'wu_payment' ), 'error' );			

			}

		}else{

			wc_print_notice( __( 'There was an error, please try again', 'wu_payment' ), 'error' );			

		}

	}

	echo '<div class="form-wu">

	<form method="post" class="login-form-wu">';

	if($data){

		echo '<label for="order-id">Order Number:</label> <input type="text" name="order-id" value="'.$data['order-id'].'"/>';

	}else if($_GET['orderid']){

		echo '<label for="order-id">Order Number:</label> <input type="text" name="order-id" value="'.$_GET['orderid'].'" readonly/>';

	}else{

		echo '<label for="order-id">Order Number:</label> <input type="text" name="order-id" required/>';

	}

	echo '

	<label for="payment-method">Payment Method:</label> <input type="text" name="payment-method" value="Western Union" readonly/>

	<label for="customer-email">Your registered Email:</label> <input type="email" name="customer-email" required/>

	<label for="customer-name">Receiver First name:</label> <input type="text" name="customer-name" required/>

	<label for="customer-last-name">Receiver Last name:</label> <input type="text" name="customer-last-name" required/>

	<label for="customer-payment-country">Payment Country:</label> <input type="text" name="customer-payment-country" required/>

	<label for="customer-mtcn">MTCN#:</label> <input type="text" name="customer-mtcn" required/>

	<input type="submit" value="Submit">

	</form>

	</div>';

	return ob_get_clean();

}

//=========================================================================

//=========================================================================

/**

 * Western Union Payment Gateway

 *

 */

add_action( 'plugins_loaded', 'wc_western_union_gateway_init', 11 );

function wc_western_union_gateway_init() {

	class WC_Western_Union extends WC_Payment_Gateway {

		/**

		 * Constructor for the gateway.

		 */

		public function __construct() {



			$this->id                 = 'western_union';

			$this->icon               = apply_filters('woocommerce_wu_icon', plugins_url().'/woo-western-union/assets/wu.png');

			$this->has_fields         = false;

			$this->method_title       = __( 'Western Union', 'wc-gateway-wu' );

			$this->method_description = __( 'Enables Western Union payments', 'wc-gateway-wu' );



			// Load the settings.

			$this->init_form_fields();

			$this->init_settings();



			// Define user set variables

			$this->title        = $this->get_option( 'title' );

			$this->description  = $this->get_option( 'description' );

			$this->instructions = $this->get_option( 'instructions', $this->description );

			$this->link_name = $this->get_option( 'link_name' );

			$this->link_url = $this->get_option( 'link_url' );

			$this->names_under = $this->get_option( 'names_under' );

			$this->names_over = $this->get_option( 'names_over' );


			// Actions

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );



			// Customer Emails

			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

		}


		/**

		 * Initialize Gateway Settings Form Fields

		 */

		public function init_form_fields() {



			$this->form_fields = apply_filters( 'wc_western_union_form_fields', array(



				'enabled' => array(

					'title'   => __( 'Enable/Disable', 'wc-gateway-wu' ),

					'type'    => 'checkbox',

					'label'   => __( 'Enable Western Union Payment', 'wc-gateway-wu' ),

					'default' => 'yes'

				),

				

				'title' => array(

					'title'       => __( 'Title', 'wc-gateway-wu' ),

					'type'        => 'text',

					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-wu' ),

					'default'     => __( 'Western Union', 'wc-gateway-wu' ),

					'desc_tip'    => true,

				),

				

				'description' => array(

					'title'       => __( 'Description', 'wc-gateway-wu' ),

					'type'        => 'textarea',

					'description' => __( 'Payment method description that the customer will see on your checkout.', 'wc-gateway-wu' ),

					'default'     => __( 'Pay with Western Union.', 'wc-gateway-wu' ),

					'desc_tip'    => true,

				),
				
				'name_selection' => array(

					'title'       => __( 'Name selection', 'wc-gateway-wu' ),

					'type'        => 'select',

					'description' => __( 'Choose how names will display.', 'wc-gateway-wu' ),
					'options' => array(
						'randomize' => __( 'Randomize', 'Randomize' ),
						'order'   => __( 'Names in order', 'Names in order' ),
					),
					'default'     => __( 'randomize', 'wc-gateway-wu' ),

					'desc_tip'    => true,

				),
				
				
				'names_under' => array(

					'title'       => __( 'Names Under 500$', 'wc-gateway-wu' ),

					'type'        => 'textarea',

					'description' => __( 'Enter names for random select.', 'wc-gateway-wu' ),

					'default'     => __( 'Jhon Doe', 'wc-gateway-wu' ),

					'desc_tip'    => true,

				),

				'names_over' => array(

					'title'       => __( 'Names Over 500$', 'wc-gateway-wu' ),

					'type'        => 'textarea',

					'description' => __( 'Enter names for random select.', 'wc-gateway-wu' ),

					'default'     => __( 'Jhon Doe', 'wc-gateway-wu' ),

					'desc_tip'    => true,

				),


				

				'instructions' => array(

					'title'       => __( 'Thank you/Mail info', 'wc-gateway-wu' ),

					'type'        => 'textarea',

					'description' => __( 'Information that will be added to the thank you page and emails. OPTIONAL - Use {form_link} to put the link of the WU form to the thank you page', 'wc-gateway-wu' ),

					'default'     => 'Thank you for buying with us. The next step is following the {form_link} so we can complete your order.',

					'desc_tip'    => true,

				),

				'link_name' => array(

					'title'       => __( 'Form link - Name', 'wc-gateway-wu' ),

					'type'        => 'text',

					'description' => __( 'This is the name of the link which will appear at the thank you page', 'wc-gateway-wu' ),

					'default'     => 'Western Union Form',

					'desc_tip'    => true,

				),

				'link_url' => array(

					'title'       => __( 'Form link - URL', 'wc-gateway-wu' ),

					'type'        => 'text',

					'description' => __( 'This is the URL of the link which will appear at the thank you page, leave empty if want to use default URL', 'wc-gateway-wu' ),

					'default'     => '',

					'desc_tip'    => true,

				)

			) );

		}





		/**

		 * Output for the order received page.

		 */

		public function thankyou_page() {
			$t = explode("-",$this->names_under);
			$single_name = array_rand($t,1);
			$mailIme = $single_name;
			if ( $this->instructions ) {

				if($this->link_url !== ''){


					$instructions = str_replace("{form_link}","<a href='".$this->link_url."'>".$this->link_name."</a>", $this->instructions.$t[$mailIme]."OPREM DOBRO");			

				}else{
					$t = explode("-",$this->names_under);
					// $single_name = array_rand($t,1);
					$instructions = str_replace("{form_link}","<a href='/test/wu-form/'>".$this->link_name.$this->names_under."</a>", $this->instructions.$t[$mailIme].$this->name_selection);			

				}

				echo wpautop( wptexturize( $instructions ) );

			}

		}





		/**

		 * Add content to the WC emails.

		 *

		 * @access public

		 * @param WC_Order $order

		 * @param bool $sent_to_admin

		 * @param bool $plain_text

		 */

		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
			$t = explode("-",$this->names_under);
			// $single_name = array_rand($t,1);
			if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {

				if($this->link_url !== ''){

					$instructions = str_replace("{form_link}","<a href='".$this->link_url."'>".$this->link_name."</a>", $this->instructions.$t[$mailIme]);			

				}else{

					$instructions = str_replace("{form_link}","<a href='/test/wu-form/'>".$this->link_name."</a>", $this->instructions.$t[$mailIme]);			

				}

				echo wpautop( wptexturize( $instructions ) ) . PHP_EOL;

			}

		}





		/**

		 * Process the payment and return the result

		 *

		 * @param int $order_id

		 * @return array

		 */

		public function process_payment( $order_id ) {



			$order = wc_get_order( $order_id );

			

			// Mark as on-hold (we're awaiting the payment)

			$order->update_status( 'on-hold', __( 'Waiting for Western Union payment', 'wc-gateway-wu' ) );

			

			// Reduce stock levels

			$order->reduce_order_stock();

			

			// Remove cart

			WC()->cart->empty_cart();

			

			// Return thankyou redirect

			return array(

				'result' 	=> 'success',

				'redirect'	=> $this->get_return_url( $order )

			);

		}
		
		function get_last_order_id(){
			global $wpdb;
			$statuses = array_keys(wc_get_order_statuses());
			$statuses = implode( "','", $statuses );

            // Getting last Order ID (max value)
			$results = $wpdb->get_col( "
				SELECT MAX(ID) FROM {$wpdb->prefix}posts
				WHERE post_type LIKE 'shop_order'
				AND post_status IN ('$statuses')
				" );
			return reset($results);
		}



  } // end \WC_Western_Union class

}

//=========================================================================