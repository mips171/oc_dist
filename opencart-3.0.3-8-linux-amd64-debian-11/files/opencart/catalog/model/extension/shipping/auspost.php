<?php
/**
 * @version    N/A, base on AUSPOST API update on 18 April 2016
 * @link       https://developers.auspost.com.au/docs/reference
 * @since      2.3.0.2   Update on 21 March 2017
 */

class ModelExtensionShippingAusPost extends Model {
	public function getQuote($address) {
		$this->load->language('extension/shipping/auspost');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_auspost_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_auspost_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$error = '';

		$api_key = $this->config->get('shipping_auspost_api');
		$api_password = $this->config->get('shipping_auspost_password');
		$account_numbers = explode(',', $this->config->get('shipping_auspost_account_number'));

		$quote_data = array(); // Initialize quote_data outside of the loop

		foreach ($account_numbers as $account_no) {
			$api_account_no = trim($account_no);

			if ($status) {
				$weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->config->get('shipping_auspost_weight_class_id'));

				$length = 0;
				$width = 0;
				$height = 0;

				if ($address['iso_code_2'] == 'AU') {
					$max_length = 0;

					foreach ($this->cart->getProducts() as $product) {
						$fromUnit = $this->getLengthUnitByID($product['length_class_id']);
						$toUnit = 'cm'; // Since the AusPost API always wants centimeters

						$converted_length = $this->convertLength($product['length'], $fromUnit, $toUnit);
						$converted_width = $this->convertLength($product['width'], $fromUnit, $toUnit);
						$converted_height = $this->convertLength($product['height'], $fromUnit, $toUnit);

						if ($converted_length > $max_length) {
							$max_length = $converted_length;
						}

						if ($converted_height > $height) {
							$height = $converted_height;
						}

						if ($converted_width > $width) {
							$width = $converted_width;
						}
					}

					$length = $max_length; // Assign the maximum length after iterating through all products

					$AUSPOST_API_BASE="https://digitalapi.auspost.com.au/";
					$AUSPOST_API_TEST="test/";
					$AUSPOST_API_SHIPPING_ENDPOINT="shipping/v1/prices/shipments";

					$formatted_weight = (float)number_format($weight, 1, '.', '');
					if ($formatted_weight == 0.0) {
						// fallback weight
						$formatted_weight = 0.5;
					}

					$shipment_data = array(
						'shipments' => array(
							array(
								'from' => array(
									'suburb' => $this->config->get('shipping_auspost_suburb'),
									'state'  => $this->config->get('shipping_auspost_state'),
									'postcode' => $this->config->get('shipping_auspost_postcode')
								),
								'to' => array(
									'suburb' => $address['city'],
									'state'  => $this->getAbbreviatedState($address['zone']),
									'postcode' => $address['postcode']
								),
								'items' => array(
									array(
										'length' => $length,
										'height' => $height,
										'width' => $width,
										'weight' => (string)$formatted_weight, // using the formatted weight
										'packaging_type' => $this->config->get('shipping_auspost_packaging_type')
									)
								)
							)
						)
					);

					$curl = curl_init();

					$credentials = $api_key . ':' . $api_password;
					$base64Credentials = base64_encode($credentials);

					curl_setopt($curl, CURLOPT_HTTPHEADER, array(
						'Content-Type: application/json',
						'account-number: ' . $api_account_no,
						'Authorization: Basic ' . $base64Credentials
						// Add other headers if required
					));

					$full_url = $AUSPOST_API_BASE . $AUSPOST_API_SHIPPING_ENDPOINT;

					if ($this->config->get('shipping_auspost_testmode')) {
						$full_url = $AUSPOST_API_BASE . $AUSPOST_API_TEST . $AUSPOST_API_SHIPPING_ENDPOINT;
					}

					// Capture and log the request details
					$logMessage = "Endpoint URL: " . $full_url . "\n";
					$logMessage .= "Payload: " . json_encode($shipment_data) . "\n";
					file_put_contents(DIR_LOGS . 'auspost_request.log', $logMessage, FILE_APPEND);


					curl_setopt($curl, CURLOPT_URL, $full_url);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
					curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($shipment_data));

					$response = curl_exec($curl);

					curl_close($curl);

					if ($response) {
						$response_info = array();

						if (json_last_error() === JSON_ERROR_NONE) {
							// It's a valid JSON
							$response_parts = json_decode($response, true);
						} else {
							// Handle invalid JSON error
							$error = "Invalid JSON received" . $response;
							file_put_contents(DIR_LOGS . 'auspost_request.log', $response, FILE_APPEND);
						}

						if (isset($response_parts['errors'])) {
							$error = $response_parts['errors'][0]['message'];
							file_put_contents(DIR_LOGS . 'auspost_request.log', $error, FILE_APPEND);
						} else {
							$shipments = $response_parts['shipments'];

							foreach ($shipments as $shipment) {
								$service_name = $shipment['items'][0]['product_id'] . '_' . $api_account_no; // Append account number to make it unique
								$shipping_cost = $shipment['shipment_summary']['total_cost'];

								$quote_data[$service_name] = array(
									'code'         => 'auspost.' .  $service_name,
									'title'        => $service_name,
									'cost'         => $shipping_cost,
									'tax_class_id' => $this->config->get('shipping_auspost_tax_class_id'),
									'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($shipping_cost, 'AUD', $this->session->data['currency']), $this->config->get('shipping_auspost_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'], 1.0000000)
								);
							}
						}
					}
				}
			}
		}

		$method_data = array();

		if ($quote_data) {
			$method_data = array(
				'code'       => 'auspost',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_auspost_sort_order'),
				'error'      => $error
			);
		}

		return $method_data;
	}

	private function getAbbreviatedState($state) {
		$query = $this->db->query("SELECT `code` FROM `oc_zone` WHERE `name` = '" . $this->db->escape($state) . "' AND `status` = 1 LIMIT 1");

		if ($query->num_rows) {
			return $query->row['code'];
		}

		return $state;
	}

	private function convertLength($value, $fromUnit, $toUnit) {
		// Convert to base unit (meters)
		if ($fromUnit == 'cm') {
			$value /= 100.0;
		} elseif ($fromUnit != 'm') {
			throw new Exception("Unsupported length unit: " . $fromUnit);
		}

		// Convert to target unit
		if ($toUnit == 'cm') {
			$value *= 100.0;
		} elseif ($toUnit != 'm') {
			throw new Exception("Unsupported length unit: " . $toUnit);
		}

		// Return the value formatted to one decimal place
		return number_format($value, 1, '.', '');
	}

	private function getLengthUnitByID($lengthClassID) {
		$query = $this->db->query("SELECT `unit` FROM `" . DB_PREFIX . "length_class_description` WHERE `length_class_id` = '" . (int)$lengthClassID . "' LIMIT 1");

		if ($query->num_rows) {
			return $query->row['unit'];
		}

		throw new Exception("Length class ID not found: " . $lengthClassID);
	}
}