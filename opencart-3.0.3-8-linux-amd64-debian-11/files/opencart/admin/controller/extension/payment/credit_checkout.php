<?php

class ControllerExtensionPaymentCreditCheckout extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/credit_checkout');
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_credit_checkout', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['error_warning'] = $this->getErrorWarning();
		$data['breadcrumbs'] = $this->getBreadcrumbs();
		$data['action'] = $this->url->link('extension/payment/credit_checkout', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
		$data = array_merge($data, $this->getPaymentSettings());

		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/credit_checkout', $data));
	}

	private function getErrorWarning() {
		return isset($this->error['warning']) ? $this->error['warning'] : '';
	}

	private function getBreadcrumbs() {
		return array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			),
			array(
				'text' => $this->language->get('text_extension'),
				'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/payment/credit_checkout', 'user_token=' . $this->session->data['user_token'], true)
			)
		);
	}

	private function getPaymentSettings() {
		return array(
			'payment_credit_checkout_order_status_id' => $this->getPostOrDefault('payment_credit_checkout_order_status_id', $this->config->get('payment_credit_checkout_order_status_id')),
			'payment_credit_checkout_status' => $this->getPostOrDefault('payment_credit_checkout_status', $this->config->get('payment_credit_checkout_status')),
			'payment_credit_checkout_sort_order' => $this->getPostOrDefault('payment_credit_checkout_sort_order', $this->config->get('payment_credit_checkout_sort_order')),
			'payment_credit_max_total' => $this->getPostOrDefault('payment_credit_max_total', $this->config->get('payment_credit_max_total')),
		);
	}

	private function getPostOrDefault($key, $default) {
		return isset($this->request->post[$key]) ? $this->request->post[$key] : $default;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/credit_checkout')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
}
