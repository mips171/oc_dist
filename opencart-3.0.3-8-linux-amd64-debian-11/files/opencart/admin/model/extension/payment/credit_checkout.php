<?php

class ModelExtensionPaymentCreditCheckout extends Model {

    /**
     * Retrieve the credit amount for a customer
     *
     * @param int $customerId
     * @return float
     */
    public function getCustomerCredit($customerId) {
        $query = $this->db->query("SELECT `credit` FROM `" . DB_PREFIX . "customer` WHERE `customer_id` = '" . (int)$customerId . "'");

        if ($query->num_rows) {
            return (float)$query->row['credit'];
        } else {
            return 0.0;
        }
    }

    /**
     * Update the credit amount for a customer
     *
     * @param int $customerId
     * @param float $amount The new customer credit amount
     * @return void
     */
    public function updateCustomerCredit($customerId, $amount) {
        $currentCredit = $this->getCustomerCredit($customerId);

        $this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `credit` = '" . (float)$amount . "' WHERE `customer_id` = '" . (int)$customerId . "'");

        // Log the credit change
        $this->logCreditChange($customerId, $amount, $currentCredit, $amount);
    }

    public function getCustomerByOrderId($order_id) {
		$query = $this->db->query("SELECT `customer_id` FROM `" . DB_PREFIX . "order` WHERE `order_id` = '" . (int)$order_id . "'");

		if ($query->num_rows) {
			return $query->row['customer_id'];
		} else {
			return 0;
		}
	}

    /**
     * Log changes in customer credit for auditing purposes
     *
     * @param int $customerId
     * @param float $amountChanged
     * @param float $previousCredit
     * @param float $newCredit
     * @return void
     */
    private function logCreditChange($customerId, $amountChanged, $previousCredit, $newCredit) {
        $log = new Log('credit_changes.log');
        $log->write("Customer ID: {$customerId} - Amount Changed: {$amountChanged} - Previous Credit: {$previousCredit} - New Credit: {$newCredit}");
    }

}
