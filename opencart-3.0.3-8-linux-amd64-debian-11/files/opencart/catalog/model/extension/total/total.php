<?php
class ModelExtensionTotalTotal extends Model {
	public function getTotal($total) {
		$this->load->language('extension/total/total');

        $new_total = 0;
        $currency_info = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency WHERE `code` = '" . $this->db->escape($this->session->data['currency']) . "'")->row;

        foreach ($total['totals'] as $line_item) {
            if ($line_item['code'] == 'intermediate_order_total') continue;
            $converted_value = $line_item['value'] * $currency_info['value'];
            $new_total += round($converted_value, 2);
        }

        $converted_total = $new_total / $currency_info['value'];
        $total['total'] = round($converted_total, 2);

		$total['totals'][] = array(
			'code'       => 'total',
			'title'      => $this->language->get('text_total'),
			'value'      => max(0, $total['total']),
			'sort_order' => $this->config->get('total_total_sort_order')
		);
	}
}