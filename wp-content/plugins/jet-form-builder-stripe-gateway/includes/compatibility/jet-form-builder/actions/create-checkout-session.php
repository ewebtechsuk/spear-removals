<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Actions;

class Create_Checkout_Session extends Base_Action {

	private $price        = 0;
	private $currency     = '';
	private $payment_name = '';
	private $urls         = array();

	public function __construct() {
		$this->payment_name = get_option( 'blogname' ) . ' ' . __( 'payment', 'jet-form-builder' );
	}

	public function action_endpoint() {
		return 'v1/checkout/sessions';
	}

	/**
	 * @since 1.1.1 body was changed according to
	 * @link https://stripe.com/docs/upgrades#2022-08-01
	 *
	 * @return array
	 */
	public function action_body() {
		return array_merge(
			$this->urls,
			array(
				'mode'                 => 'payment',
				'payment_method_types' => $this->get_payment_methods(),
				'line_items'           => array(
					array(
						'quantity'   => 1,
						'price_data' => array(
							'currency'     => $this->currency,
							'unit_amount'  => $this->price,
							'product_data' => array(
								'name' => $this->payment_name,
							),
						),
					),
				),
			)
		);
	}

	private function get_payment_methods(): array {
		return apply_filters( 'jet-form-builder/stripe/payment-methods', array( 'card' ) );
	}

	public function set_price( $price ): Create_Checkout_Session {
		$this->price = $price;

		return $this;
	}

	public function set_currency( string $currency ): Create_Checkout_Session {
		$this->currency = $currency;

		return $this;
	}

	public function set_payment_name( string $name ): Create_Checkout_Session {
		$this->payment_name = $name;

		return $this;
	}


	public function set_urls( string $success_url, string $cancel_url ): Create_Checkout_Session {
		$this->urls = array(
			'success_url' => $success_url,
			'cancel_url'  => $cancel_url,
		);

		return $this;
	}

}
