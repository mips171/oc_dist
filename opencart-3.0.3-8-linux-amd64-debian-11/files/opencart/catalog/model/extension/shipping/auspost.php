<?php

/**
 * @version    N/A, base on Auspost Shipping API 2023
 * @link       https://developers.auspost.com.au/docs/reference
 * @since      3.0.3.8  Update on 24 Oct 2023
 */

class ModelExtensionShippingAusPost extends Model
{
	const AUSPOST_API_BASE = "https://digitalapi.auspost.com.au/";
	const AUSPOST_API_TEST = "test/";
	const AUSPOST_API_SHIPPING_ENDPOINT = "shipping/v1/prices/shipments";
	const FALLBACK_WEIGHT = 0.5;
	const CENTIMETER_UNIT = 'cm';
	const METER_UNIT = 'm';
	const ERROR_LOG_PATH = DIR_LOGS . 'auspost_request.log';

	public function getQuote($address)
	{
		$this->load->language('extension/shipping/auspost');

		$status = $this->getShippingStatus($address);
		$api_key = $this->config->get('shipping_auspost_api');
		$api_password = $this->config->get('shipping_auspost_password');
		$account_numbers = explode(',', $this->config->get('shipping_auspost_account_number'));

		$quote_data = array();

		foreach ($account_numbers as $account_no) {
			if ($status && $address['iso_code_2'] == 'AU') {
				$shipment_data = $this->prepareShipmentData($address, $account_no);
				$response_parts = $this->executeCurlRequest($shipment_data, $api_key, $api_password, $account_no);

				if (isset($response_parts['errors'])) {
					$error = $response_parts['errors'][0]['message'];
					file_put_contents(self::ERROR_LOG_PATH, $error, FILE_APPEND);
				} else {
					$quote_data = $this->extractQuotesFromResponse($response_parts, $quote_data);
				}
			}
		}

		return $this->formatMethodData($quote_data);
	}

	private function getShippingStatus($address)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('shipping_auspost_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

		return !$this->config->get('shipping_auspost_geo_zone_id') || $query->num_rows;
	}

	private function prepareShipmentData($address) {
		$dimension_data = $this->getMaxDimensions();
		$length_unit = $this->getLengthUnitByID($this->config->get('shipping_auspost_length_class_id'));

		// Convert and format weight
		$formatted_weight = $this->getFormattedWeight();

		// Determine which dimension data to use based on the account number
		$dimension_data = $this->getDimensionDataInCentimeters($dimension_data, $length_unit);

		// Build and return shipment data
		return [
			'shipments' => [
				[
					'from' => $this->getSenderDetails(),
					'to' => $this->getRecipientDetails($address),
					'items' => [
						array_merge($dimension_data, [
							'weight' => (string) $formatted_weight,
							'packaging_type' => $this->config->get('shipping_auspost_packaging_type')
						])
					]
				]
			]
		];
	}

	private function getFormattedWeight() {
		$weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->config->get('shipping_auspost_weight_class_id'));
		return (float) number_format($weight, 1, '.', '') ?: self::FALLBACK_WEIGHT;
	}

	private function getDimensionDataInCentimeters($dimension_data, $length_unit) {
		return [
			'length' => number_format($this->convertLength($dimension_data['length'], $length_unit, self::CENTIMETER_UNIT), 1, '.', ''),
			'width' => number_format($this->convertLength($dimension_data['width'], $length_unit, self::CENTIMETER_UNIT), 1, '.', ''),
			'height' => number_format($this->convertLength($dimension_data['height'], $length_unit, self::CENTIMETER_UNIT), 1, '.', '')
		];
	}

	private function getSenderDetails() {
		return [
			'suburb' => $this->config->get('shipping_auspost_suburb'),
			'state' => $this->config->get('shipping_auspost_state'),
			'postcode' => $this->config->get('shipping_auspost_postcode')
		];
	}

	private function getRecipientDetails($address) {
		return [
			'suburb' => $address['city'],
			'state' => $this->getAbbreviatedState($address['zone']),
			'postcode' => $address['postcode']
		];
	}

	private function getMaxDimensions()
	{
		$max_length = 0;
		$max_width = 0;
		$max_height = 0;

		foreach ($this->cart->getProducts() as $product) {
			$dimensions = $this->convertProductDimensions($product);
			$max_length = max($max_length, $dimensions['length']);
			$max_width = max($max_width, $dimensions['width']);
			$max_height = max($max_height, $dimensions['height']);
		}

		return array(
			'length' => $max_length,
			'width' => $max_width,
			'height' => $max_height
		);
	}

	private function convertProductDimensions($product)
	{
		$fromUnit = $this->getLengthUnitByID($product['length_class_id']);
		$toUnit = self::CENTIMETER_UNIT;

		return array(
			'length' => $this->convertLength($product['length'], $fromUnit, $toUnit),
			'width' => $this->convertLength($product['width'], $fromUnit, $toUnit),
			'height' => $this->convertLength($product['height'], $fromUnit, $toUnit)
		);
	}

	private function executeCurlRequest($shipment_data, $api_key, $api_password, $account_no)
	{
		$curl = curl_init();
		$credentials = $api_key . ':' . $api_password;
		$base64Credentials = base64_encode($credentials);

		curl_setopt(
			$curl,
			CURLOPT_HTTPHEADER,
			array(
				'Content-Type: application/json',
				'account-number: ' . $account_no,
				'Authorization: Basic ' . $base64Credentials
			)
		);

		$full_url = self::AUSPOST_API_BASE . ($this->config->get('shipping_auspost_testmode') ? self::AUSPOST_API_TEST : '') . self::AUSPOST_API_SHIPPING_ENDPOINT;

		// Log request details
		file_put_contents(self::ERROR_LOG_PATH, "Endpoint URL: " . $full_url . "\nPayload: " . json_encode($shipment_data) . "\n", FILE_APPEND);

		curl_setopt($curl, CURLOPT_URL, $full_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($shipment_data));

		$response = curl_exec($curl);

		curl_close($curl);

		return json_last_error() === JSON_ERROR_NONE ? json_decode($response, true) : [];
	}

	private function extractQuotesFromResponse($response_parts, $quote_data)
	{
		$shipments = $response_parts['shipments'];

		foreach ($shipments as $shipment) {
			$product_id = $shipment['items'][0]['product_id'];

			if (in_array($product_id, self::$adminOnlyProductIds)) {
				continue; // skip it
			}

			$friendly_name = $this->getTypeByProductId($product_id);
			$service_name = $friendly_name ?: $product_id;
			$shipping_cost = $shipment['shipment_summary']['total_cost'];

			$quote_data[$service_name] = array(
				'code' => 'auspost.' . $service_name,
				'title' => $service_name,
				'cost' => $shipping_cost,
				'tax_class_id' => $this->config->get('shipping_auspost_tax_class_id'),
				'text' => $this->currency->format($this->tax->calculate($this->currency->convert($shipping_cost, 'AUD', $this->session->data['currency']), $this->config->get('shipping_auspost_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'], 1.0000000)
			);
		}

		return $quote_data;
	}

	private function formatMethodData($quote_data)
	{
		if (!empty($quote_data)) {
			$method_data = array(
				'code' => 'auspost',
				'title' => $this->language->get('text_title'),
				'quote' => $quote_data,
				'sort_order' => $this->config->get('shipping_auspost_sort_order'),
				'error' => false
			);
		} else {
			$method_data = array();
		}

		return $method_data;
	}

	private function getAbbreviatedState($state)
	{
		$query = $this->db->query("SELECT `code` FROM `oc_zone` WHERE `name` = '" . $this->db->escape($state) . "' AND `status` = 1 LIMIT 1");

		if ($query->num_rows) {
			return $query->row['code'];
		}

		return $state;
	}

	private function convertLength($value, $fromUnit, $toUnit)
	{
		// Convert to base unit (meters)
		if ($fromUnit == self::CENTIMETER_UNIT) {
			$value /= 100.0;
		} elseif ($fromUnit != self::METER_UNIT) {
			throw new Exception("Unsupported length unit: " . $fromUnit);
		}

		// Convert to target unit
		if ($toUnit == self::CENTIMETER_UNIT) {
			$value *= 100.0;
		} elseif ($toUnit != self::METER_UNIT) {
			throw new Exception("Unsupported length unit: " . $toUnit);
		}

		// Return the value formatted to one decimal place
		return number_format($value, 1, '.', '');
	}

	private function getLengthUnitByID($lengthClassID)
	{
		$query = $this->db->query("SELECT `unit` FROM `" . DB_PREFIX . "length_class_description` WHERE `length_class_id` = '" . (int) $lengthClassID . "' LIMIT 1");

		if ($query->num_rows) {
			return $query->row['unit'];
		}

		throw new Exception("Length class ID not found: " . $lengthClassID);
	}

	// Get the friendly name by product ID
	private function getTypeByProductId($productId)
	{
		return isset(self::$productIdToTypeMap[$productId]) ? self::$productIdToTypeMap[$productId] : null;
	}

	private static $productIdToTypeMap = [
		"PRM" => "StarTrack Premium",
		"EXP" => "StarTrack Road Express",
		"7E55" => "Auspost Parcel Post + Signature",
		"3K55" => "Auspost Express Post + Signature",
		"RET" => "StarTrack Express Tail-lift",
		"RE2" => "StarTrack Express Tail-lift 2 Person",
		"FPP" => "StarTrack 1, 3 & 5kg Fixed Price Premium",
		"FPA" => "StarTrack 1, 3 & 5kg Fixed Price Airlock",
		"ARL" => "StarTrack Airlock",
		"XID1" => "Auspost Express E-parcel ID&V 1",
		"XID2" => "Auspost Express E-parcel ID&V 2",
		"RPI8" => "Auspost INTL Economy + Signature / Registered Post",
		"PTI8" => "Auspost INTL Standard/Pack & Track",
		"ID1" => "Auspost E-parcel ID&V 1",
		"ID2" => "Auspost E-parcel ID&V 2",
		"AIR8" => "Auspost INTL ECONOMY/AIRMAIL PARCELS",
		"EL1" => "Auspost Parcel Post XL 1",
		"3W35" => "Auspost Metro + Signature",
		"3W33" => "Auspost Metro",
		"3W05" => "Auspost Metro Cubing + Signature",
		"3W03" => "Auspost Metro Cubing"
	];

	// Later we will work out how to restrict the admin-only options from the front-end
	private static $adminOnlyProductIds = [
		"RET",
		"RE2",
		"FPP",
		"FPA",
		"ARL",
		"XID1",
		"XID2",
		"RPI8",
		"PTI8",
		"ID1",
		"ID2",
		"AIR8",
		"EL1",
		"3W35",
		"3W33",
		"3W05",
		"3W03"
	];
}