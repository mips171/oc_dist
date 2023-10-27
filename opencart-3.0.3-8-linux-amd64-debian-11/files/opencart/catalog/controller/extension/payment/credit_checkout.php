<?php
class ControllerExtensionPaymentCreditCheckout extends Controller {
	public function index() {
		$data['continue'] = $this->url->link('checkout/success');

		return $this->load->view('extension/payment/credit_checkout', $data);
	}

	public function confirm() {
		if (isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code'] == 'credit_checkout') {

			$this->load->model('extension/payment/credit_checkout');
			$this->load->language('extension/payment/credit_checkout');

			$order_id = $this->request->get['order_id'];
			$customer_id = $this->model_extension_payment_credit_checkout->getCustomerByOrderId($order_id);
			$customer_credit = $this->model_extension_payment_credit_checkout->getCustomerCredit($customer_id);

			$this->load->model('checkout/order');

			$orderAmount = $this->model_checkout_order->getOrder($order_id)['total'];
			$remaining_credit = $customer_credit - $orderAmount;

			$this->model_extension_payment_credit_checkout->updateCustomerCredit($order_id, $remaining_credit);

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_credit_checkout_order_status_id'));
		}
	}
}
