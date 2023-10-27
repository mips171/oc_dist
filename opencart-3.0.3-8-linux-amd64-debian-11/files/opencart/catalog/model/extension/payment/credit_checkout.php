<?php
class ModelExtensionPaymentCreditCheckout extends Model {
	private $customerData = [];

    public function getMethod($address, $total) {
        $this->load->language('extension/payment/credit_checkout');

		// Check if logged in customer is on credit hold
		$onCreditHold = $this->isCustomerOnCreditHold();

		if ($onCreditHold) {
			return array();
		}

        // Check if logged in customer belongs to the "trade" group
        $isTradeGroup = $this->isCustomerInTradeGroup();

		if (!$isTradeGroup) {
			return array();
		}

        // Get customer credit balance
        $creditBalance = $this->getCustomerCreditBalance();

        // Check if total is less than or equal to zero or customer belongs to trade group and has sufficient credit
        if ($creditBalance >= $total) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'credit_checkout',
                'title'      => $this->language->get('text_title') . " - " . $creditBalance,
                'terms'      => '',
                'sort_order' => $this->config->get('payment_credit_checkout_sort_order')
            );
        }

        return $method_data;
    }

    private function isCustomerInTradeGroup() {
        $tradeGroupId = 2;

        if (!$this->customer->isLogged()) {
            return false;
        }

        $customerGroupId = $this->customer->getGroupId();
        return $customerGroupId == $tradeGroupId;
    }

	private function getCustomerData() {
		if (!empty($this->customerData)) {
			return $this->customerData;
		}

		if (!$this->customer->isLogged()) {
			return [];
		}

		$query = $this->db->query("SELECT credit, credit_hold FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		if ($query->num_rows) {
			$this->customerData = $query->row;
		}

		return $this->customerData;
	}

    private function getCustomerCreditBalance() {
        $customerData = $this->getCustomerData();
        return isset($customerData['credit']) ? (float)$customerData['credit'] : 0;
    }

    private function isCustomerOnCreditHold() {
        $customerData = $this->getCustomerData();
        return isset($customerData['credit_hold']) ? (bool)$customerData['credit_hold'] : true;
    }

}
