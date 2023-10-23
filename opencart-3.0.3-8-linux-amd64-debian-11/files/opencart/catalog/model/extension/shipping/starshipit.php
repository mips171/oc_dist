<?php
/**
 * @version    N/A
 * @link       https://https://api-docs.starshipit.com/
 * @since      3.0.3.8
 */

class ModelExtensionShippingStarshipit extends Model {
    public function getQuote($address) {
        $this->load->language('extension/shipping/starshipit');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_starshipit_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if (!$this->config->get('shipping_starshipit_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $error = '';

        $api_key = $this->config->get('shipping_starshipit_api_key');
        $subscription_key = $this->config->get('shipping_starshipit_subscription_key');

        $quote_data = array();

        if ($status) {
            $weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->config->get('shipping_starshipit_weight_class_id'));

            $curl = curl_init();

            $data = array(
                'destination' => array(
                    'street' => $address['address_1'],
                    'suburb' => $address['city'],
                    'city' => $address['city'],
                    'state' => $address['zone'],
                    'post_code' => $address['postcode'],
                    'country_code' => $address['iso_code_2']
                ),
                'packages' => array(
                    array('weight' => $weight)
                )
            );

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.starshipit.com/api/rates',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'StarShipIT-Api-Key: ' . $api_key,
                    'Ocp-Apim-Subscription-Key: ' . $subscription_key
                )
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            if ($response) {
                $response_parts = json_decode($response, true);

                if (!$response_parts['success']) {
                    $error = 'Failed to get rates from Starshipit';
                } else {
                    $response_rates = $response_parts['rates'];

                    foreach ($response_rates as $rate) {
                        $quote_data[$rate['service_name']] = array(
                            'code'         => 'starshipit.' .  $rate['service_code'],
                            'title'        => $rate['service_name'],
                            'cost'         => $this->currency->convert($rate['total_price'], 'AUD', $this->config->get('config_currency')),
                            'tax_class_id' => $this->config->get('shipping_starshipit_tax_class_id'),
                            'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($rate['total_price'], 'AUD', $this->session->data['currency']), $this->config->get('shipping_starshipit_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'], 1.0000000)
                        );
                    }
                }
            }
        }

        $method_data = array();

        if ($quote_data) {
            $method_data = array(
                'code'       => 'starshipit',
                'title'      => $this->language->get('text_title'),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('shipping_starshipit_sort_order'),
                'error'      => $error
            );
        }

        return $method_data;
    }
}
