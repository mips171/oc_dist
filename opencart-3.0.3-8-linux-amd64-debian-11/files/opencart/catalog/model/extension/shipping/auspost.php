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
		$api_account_no = $this->config->get('shipping_auspost_account_number');

		echo $api_key;
		echo $api_password;
		echo $api_account_no;

		$quote_data = array();

		if ($status) {
			$weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->config->get('shipping_auspost_weight_class_id'));

			$length = 0;
			$width = 0;
			$height = 0;

			if ($address['iso_code_2'] == 'AU') {
				$max_length = 0;

				foreach ($this->cart->getProducts() as $product) {
					$converted_length = $this->convertLength($product['length'], $product['length_class_id'], $this->config->get('shipping_auspost_length_class_id'));
					$converted_width = $this->convertLength($product['width'], $product['length_class_id'], $this->config->get('shipping_auspost_length_class_id'));
					$converted_height = $this->convertLength($product['height'], $product['length_class_id'], $this->config->get('shipping_auspost_length_class_id'));

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

				$AUSPOST_API_BASE="https://digitalapi.auspost.com.au/";
				$AUSPOST_API_TEST="test/";
				$AUSPOST_API_SHIPPING_ENDPOINT="shipping/v1/prices/shipments";

				$formatted_weight = (float)number_format($weight, 1, '.', '');
				if ($formatted_weight == 0.0) {
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
									'length' => number_format($length, 1, '.', ''),
									'height' => number_format($height, 1, '.', ''),
									'width' => number_format($width, 1, '.', ''),
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
				echo $logMessage;
				file_put_contents(DIR_LOGS . 'auspost_request.log', $logMessage, FILE_APPEND);


				curl_setopt($curl, CURLOPT_URL, $full_url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($shipment_data));

				$response = curl_exec($curl);

				curl_close($curl);

				if ($response) {
					$response_info = array();

					$response_parts = json_decode($response, true);

					if (isset($response_parts['errors'])) {
						$error = $response_parts['errors'][0]['message'];
						// log the error
						echo $error;
					} else {
						$shipments = $response_parts['shipments'];

						foreach ($shipments as $shipment) {
							$service_name = $shipment['items'][0]['product_id'];
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

	private function convertLength($value, $fromUnitId, $toUnitId) {
		// If converting from and to the same unit, return the original value
		if ($fromUnitId == $toUnitId) {
			return $value;
		}

		// Convert from the source unit to meters
		switch ($fromUnitId) {
			case 1: // centimeters to meters
				$valueInMeters = $value / 100;
				break;
			case 4: // meters to meters (no conversion needed)
				$valueInMeters = $value;
				break;
			default:
				throw new Exception("Unsupported source unit ID: " . $fromUnitId);
		}

		// Convert from meters to the desired unit
		switch ($toUnitId) {
			case 1: // meters to centimeters
				return $valueInMeters * 100;
			case 4: // meters to meters (no conversion needed)
				return $valueInMeters;
			default:
				throw new Exception("Unsupported destination unit ID: " . $toUnitId);
		}
	}

}