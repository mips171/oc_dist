<?php
static $config = NULL;
static $log = NULL;

// Error Handler
function error_handler_for_export($errno, $errstr, $errfile, $errline) {
	global $config;
	global $log;
	
	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$errors = "Notice";
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$errors = "Warning";
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$errors = "Fatal Error";
			break;
		default:
			$errors = "Unknown";
			break;
	}
		
	if (($errors=='Warning') || ($errors=='Unknown')) {
		return true;
	}

	if ($config->get('config_error_display')) {
		echo '<b>' . $errors . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>';
	}
	
	if ($config->get('config_error_log')) {
		$log->write('PHP ' . $errors . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
	}

	return true;
}

function fatal_error_shutdown_handler_for_export() {
	$last_error = error_get_last();
	if ($last_error && $last_error['type'] === E_ERROR) {
		// fatal error
		error_handler_for_export(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
	}
}

class ControllerExtensionReportAdvSalesProfit extends Controller { 
	private $error = array();
	
	public function index() { 			
		$this->load->language('extension/report/adv_sales_profit');

		$query1 = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE code = 'adv_profit_module'");
		if (empty($query1->num_rows)) {	
			$this->session->data['error_sop'] = $this->language->get('error_installed1');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));		
		}

		$query2 = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE code = 'adv_reports_sales_profit'");
		if (empty($query2->num_rows)) {	
			$this->session->data['error_sop'] = $this->language->get('error_installed2');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));		
		}
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/report/adv_sales_profit');

	    $this->document->addScript('view/javascript/bootstrap/js/bootstrap-multiselect.js');
	    $this->document->addStyle('view/javascript/bootstrap/css/bootstrap-multiselect.css');
		$this->document->addScript('view/javascript/bootstrap/js/bootstrap-select.min.js');
		$this->document->addStyle('view/javascript/bootstrap/css/bootstrap-select.css');
		
		if (isset($this->request->get['filter_date_start'])) {
			$this->session->data['filter_date_start'] = $filter_date_start = $this->request->get['filter_date_start'];			
		} else {
			$this->session->data['filter_date_start'] = $filter_date_start = '';
		}

		if (isset($this->request->get['filter_date_end'])) {
			$this->session->data['filter_date_end'] = $filter_date_end = $this->request->get['filter_date_end'];			
		} else {
			$this->session->data['filter_date_end'] = $filter_date_end = '';
		}

		$data['ranges'] = array();
		
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_custom'),
			'value' => 'custom',
			'style' => 'color:#666',
		);			
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_today'),
			'value' => 'today',
			'style' => 'color:#090',
		);
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_yesterday'),
			'value' => 'yesterday',
			'style' => 'color:#090',
		);
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_week'),
			'value' => 'week',
			'style' => 'color:#090',
		);
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_month'),
			'value' => 'month',
			'style' => 'color:#090',
		);					
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_quarter'),
			'value' => 'quarter',
			'style' => 'color:#090',
		);
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_year'),
			'value' => 'year',
			'style' => 'color:#090',
		);
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_current_week'),
			'value' => 'current_week',
			'style' => 'color:#06C',
		);
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_current_month'),
			'value' => 'current_month',
			'style' => 'color:#06C',
		);	
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_current_quarter'),
			'value' => 'current_quarter',
			'style' => 'color:#06C',
		);			
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_current_year'),
			'value' => 'current_year',
			'style' => 'color:#06C',
		);			
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_last_week'),
			'value' => 'last_week',
			'style' => 'color:#F90',
		);
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_last_month'),
			'value' => 'last_month',
			'style' => 'color:#F90',
		);	
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_last_quarter'),
			'value' => 'last_quarter',
			'style' => 'color:#F90',
		);			
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_last_year'),
			'value' => 'last_year',
			'style' => 'color:#F90',
		);			
		$data['ranges'][] = array(
			'text'  => $this->language->get('stat_all_time'),
			'value' => 'all_time',
			'style' => 'color:#F00',
		);
		
		if (isset($this->request->get['filter_range'])) {
			$this->session->data['filter_range'] = $filter_range = $this->request->get['filter_range'];		
		} else {
			$this->session->data['filter_range'] = $filter_range = 'current_year'; //show Current Year in Statistical Range by default
		}

		$data['report'] = array();
		
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_sales_summary'),
			'value' 	=> 'sales_summary',
			'divider' 	=> '',
		);
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_day_of_week'),
			'value' 	=> 'day_of_week',
			'divider' 	=> '',
		);		
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_hour'),
			'value' 	=> 'hour',
			'divider' 	=> '',
		);	
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_store'),
			'value' 	=> 'store',
			'divider' 	=> '',
		);
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_currency'),
			'value' 	=> 'currency',
			'divider' 	=> '',
		);
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_customer_group'),
			'value' 	=> 'customer_group',
			'divider' 	=> '',
		);
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_tax'),
			'value' 	=> 'tax',
			'divider' 	=> '',
		);		
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_country'),
			'value' 	=> 'country',
			'divider' 	=> '',
		);
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_postcode'),
			'value' 	=> 'postcode',
			'divider' 	=> '',
		);
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_region_state'),
			'value' 	=> 'region_state',
			'divider' 	=> '',
		);
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_city'),
			'value' 	=> 'city',
			'divider' 	=> '',
		);
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_payment_method'),
			'value' 	=> 'payment_method',
			'divider' 	=> '',
		);
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_shipping_method'),
			'value' 	=> 'shipping_method',
			'divider' 	=> '',
		);
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_coupons'),
			'value' 	=> 'coupon',
			'divider' 	=> '',
		);	
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_gift_vouchers'),
			'value' 	=> 'voucher',
			'divider' 	=> '',
		);		
		$data['report'][] = array(
			'text'  	=> '',
			'value' 	=> '',
			'divider'	=> 'true',
		);		
		$data['report'][] = array(
			'text'  	=> $this->language->get('text_abandoned_orders'),
			'value' 	=> 'abandoned_orders',
			'divider' 	=> '',
		);			
		
		if (isset($this->request->get['filter_report'])) {
			$this->session->data['filter_report'] = $filter_report = $this->request->get['filter_report'];				
		} else {
			$this->session->data['filter_report'] = $filter_report = 'sales_summary'; //show Sales Summary in Report By default
		}

		$data['details'] = array();

		$data['details'][] = array(
			'text'  	=> $this->language->get('text_no_details'),
			'value' 	=> 'no_details',
			'subtext' 	=> '',
		);
		$data['details'][] = array(
			'text'  	=> $this->language->get('text_basic_details'),
			'value' 	=> 'basic_details',
			'subtext' 	=> '',
		);
		$data['details'][] = array(
			'text'  	=> $this->language->get('text_all_details'),
			'value' 	=> 'all_details_products',
			'subtext'	=> $this->language->get('text_all_details_products'),	
		);
		$data['details'][] = array(
			'text'  	=> $this->language->get('text_all_details'),
			'value' 	=> 'all_details_orders',
			'subtext'	=> $this->language->get('text_all_details_orders'),	
		);
		
		if (isset($this->request->get['filter_details'])) {
			$this->session->data['filter_details'] = $filter_details = $this->request->get['filter_details'];			
		} else {
			$this->session->data['filter_details'] = $filter_details = 'no_details';
		}	
		
		$data['group'] = array();
		
		$data['group'][] = array(
			'text'  => $this->language->get('text_year'),
			'value' => 'year',
		);

		$data['group'][] = array(
			'text'  => $this->language->get('text_quarter'),
			'value' => 'quarter',
		);
		
		$data['group'][] = array(
			'text'  => $this->language->get('text_month'),
			'value' => 'month',
		);
		
		$data['group'][] = array(
			'text'  => $this->language->get('text_week'),
			'value' => 'week',
		);

		$data['group'][] = array(
			'text'  => $this->language->get('text_day'),
			'value' => 'day',
		);
		
		$data['group'][] = array(
			'text'  => $this->language->get('text_order'),
			'value' => 'order',
		);
		
		if (isset($this->request->get['filter_group'])) {
			$this->session->data['filter_group'] = $filter_group = $this->request->get['filter_group'];			
		} else {
			$this->session->data['filter_group'] = $filter_group = 'month';
		}				

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_filters')) {
			$advsop_settings_filters = $this->config->get('advsop' . $this->user->getId() . '_settings_filters');
		} else {
			$advsop_settings_filters = array();
		}

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_tr_columns')) {
			$advsop_settings_tr_columns = $this->config->get('advsop' . $this->user->getId() . '_settings_tr_columns');
		} else {
			$advsop_settings_tr_columns = array();
		}

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_cr_columns')) {
			$advsop_settings_cr_columns = $this->config->get('advsop' . $this->user->getId() . '_settings_cr_columns');
		} else {
			$advsop_settings_cr_columns = array();
		}

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_vr_columns')) {
			$advsop_settings_vr_columns = $this->config->get('advsop' . $this->user->getId() . '_settings_vr_columns');
		} else {
			$advsop_settings_vr_columns = array();
		}
		
		if ($this->config->get('advsop' . $this->user->getId() . '_settings_mv_columns')) {
			$advsop_settings_mv_columns = $this->config->get('advsop' . $this->user->getId() . '_settings_mv_columns');
		} else {
			$advsop_settings_mv_columns = array();
		}

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_all_columns')) {
			$advsop_settings_all_columns = $this->config->get('advsop' . $this->user->getId() . '_settings_all_columns');
		} else {
			$advsop_settings_all_columns = array();
		}
		
		$data['sort'] = array();

		if ($filter_report == 'sales_summary' or $filter_report == 'abandoned_orders' or $filter_report == 'tax' or $filter_report == 'coupon' or $filter_report == 'voucher') {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_date'),
			'value' => 'report_type',
		);
		} elseif ($filter_report == 'day_of_week') {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_day_of_week'),
			'value' => 'report_type',
		);
		} elseif ($filter_report == 'hour') {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_hour'),
			'value' => 'report_type',
		);
		} elseif ($filter_report == 'store') {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_store'),
			'value' => 'report_type',
		);
		} elseif ($filter_report == 'currency') {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_currency'),
			'value' => 'report_type',
		);	
		} elseif ($filter_report == 'customer_group') {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_customer_group'),
			'value' => 'report_type',
		);
		} elseif ($filter_report == 'country') {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_country'),
			'value' => 'report_type',
		);
		} elseif ($filter_report == 'postcode') {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_postcode'),
			'value' => 'report_type',
		);
		} elseif ($filter_report == 'region_state') {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_region_state'),
			'value' => 'report_type',
		);
		} elseif ($filter_report == 'city') {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_city'),
			'value' => 'report_type',
		);
		} elseif ($filter_report == 'payment_method') {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_payment_method'),
			'value' => 'report_type',
		);
		} elseif ($filter_report == 'shipping_method') {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_shipping_method'),
			'value' => 'report_type',
		);	
		}
		
		if ($filter_report == 'tax') {
		if (!$advsop_settings_tr_columns or (in_array('tr_tax_name', $advsop_settings_tr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_tax_title'),
			'value' => 'tax_name',
		);
		}
		if (!$advsop_settings_tr_columns or (in_array('tr_tax_rate', $advsop_settings_tr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_tax_rate'),
			'value' => 'tax_rate',
		);
		}
		if (!$advsop_settings_tr_columns or (in_array('tr_tax_orders', $advsop_settings_tr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_orders'),
			'value' => 'tax_orders',
		);
		}
		if (!$advsop_settings_tr_columns or (in_array('tr_tax_total', $advsop_settings_tr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_tax_total'),
			'value' => 'tax_total',
		);
		}
		} elseif ($filter_report == 'coupon') {
		if (!$advsop_settings_cr_columns or (in_array('cr_coupon_name', $advsop_settings_cr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_coupon_name'),
			'value' => 'coupon_name',
		);
		}
		if (!$advsop_settings_cr_columns or (in_array('cr_coupon_code', $advsop_settings_cr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_coupon_code'),
			'value' => 'coupon_code',
		);
		}
		if (!$advsop_settings_cr_columns or (in_array('cr_coupon_discount', $advsop_settings_cr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_coupon_discount'),
			'value' => 'coupon_discount',
		);
		}
		if (!$advsop_settings_cr_columns or (in_array('cr_coupon_type', $advsop_settings_cr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_coupon_type'),
			'value' => 'coupon_type',
		);
		}
		if (!$advsop_settings_cr_columns or (in_array('cr_coupon_status', $advsop_settings_cr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_coupon_status'),
			'value' => 'coupon_status',
		);
		}	
		if (!$advsop_settings_cr_columns or (in_array('cr_coupon_date_added', $advsop_settings_cr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_coupon_date_added'),
			'value' => 'coupon_date_added',
		);
		}			
		if (!$advsop_settings_cr_columns or (in_array('cr_coupon_last_used', $advsop_settings_cr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_coupon_last_used'),
			'value' => 'coupon_last_used',
		);
		}			
		if (!$advsop_settings_cr_columns or (in_array('cr_coupon_amount', $advsop_settings_cr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_coupon_amount'),
			'value' => 'coupon_amount',
		);
		}
		if (!$advsop_settings_cr_columns or (in_array('cr_coupon_orders', $advsop_settings_cr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_orders'),
			'value' => 'coupon_orders',
		);
		}	
		if (!$advsop_settings_cr_columns or (in_array('cr_coupon_total', $advsop_settings_cr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_total'),
			'value' => 'coupon_total',
		);
		}	
		} elseif ($filter_report == 'voucher') {
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_code', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_voucher_code'),
			'value' => 'voucher_code',
		);
		}
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_from_name', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_voucher_from_name'),
			'value' => 'voucher_from_name',
		);
		}
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_from_email', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_voucher_from_email'),
			'value' => 'voucher_from_email',
		);
		}
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_to_name', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_voucher_to_name'),
			'value' => 'voucher_to_name',
		);
		}
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_to_email', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_voucher_to_email'),
			'value' => 'voucher_to_email',
		);
		}	
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_theme', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_voucher_theme'),
			'value' => 'voucher_theme',
		);
		}			
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_status', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_voucher_status'),
			'value' => 'voucher_status',
		);
		}			
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_date_added', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_voucher_date_added'),
			'value' => 'voucher_date_added',
		);
		}	
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_amount', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_voucher_amount'),
			'value' => 'voucher_amount',
		);
		}
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_used_value', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_voucher_used_value'),
			'value' => 'voucher_used_value',
		);
		}
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_remaining_value', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_voucher_remaining_value'),
			'value' => 'voucher_remaining_value',
		);
		}
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_orders', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_orders'),
			'value' => 'voucher_orders',
		);
		}	
		if (!$advsop_settings_vr_columns or (in_array('vr_voucher_total', $advsop_settings_vr_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_total'),
			'value' => 'voucher_total',
		);
		}		
		} else {
		if (!$advsop_settings_mv_columns or (in_array('mv_orders', $advsop_settings_mv_columns))) {
		$data['sort'][] = array(
			'text'  => $this->language->get('column_orders'),
			'value' => 'orders',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_customers', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_customers'),
			'value' => 'customers',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_products', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_products'),
			'value' => 'products',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_sub_total', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_sub_total'),
			'value' => 'sub_total',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_shipping', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_shipping'),
			'value' => 'shipping',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_reward', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_reward'),
			'value' => 'reward',
		);
		}	
		if (in_array('mv_earned_points', $advsop_settings_mv_columns)) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_earned_reward_points'),
			'value' => 'earned_reward_points',
		);
		}	
		if (in_array('mv_used_points', $advsop_settings_mv_columns)) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_used_reward_points'),
			'value' => 'used_reward_points',
		);
		}			
		if (!$advsop_settings_mv_columns or (in_array('mv_coupon', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_coupon'),
			'value' => 'coupon',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_tax', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_taxes'),
			'value' => 'tax',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_credit', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_credit'),
			'value' => 'credit',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_voucher', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_voucher'),
			'value' => 'voucher',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_discount', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_discount'),
			'value' => 'discount',
		);
		}		
		if (!$advsop_settings_mv_columns or (in_array('mv_total', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_total'),
			'value' => 'total',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_aov', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_aov'),
			'value' => 'aov',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_refunds', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_refunds'),
			'value' => 'refunds',
		);
		}	
		if (!$advsop_settings_mv_columns or (in_array('mv_total_sales', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_total_sales'),
			'value' => 'total_sales',
		);
		}
		if (in_array('mv_product_costs', $advsop_settings_mv_columns)) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_product_costs'),
			'value' => 'prod_costs',
		);
		}
		if (in_array('mv_commission', $advsop_settings_mv_columns)) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_commission'),
			'value' => 'commission',
		);
		}
		if (in_array('mv_payment_costs', $advsop_settings_mv_columns)) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_payment_cost'),
			'value' => 'pay_costs',
		);
		}
		if (in_array('mv_shipping_costs', $advsop_settings_mv_columns)) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_shipping_cost'),
			'value' => 'ship_costs',
		);
		}
		if (in_array('mv_extra_costs', $advsop_settings_mv_columns)) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_extra_cost'),
			'value' => 'extra_cost',
		);
		}
		if (in_array('mv_return_costs', $advsop_settings_mv_columns)) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_return_cost'),
			'value' => 'return_cost',
		);
		}
		if (in_array('mv_shipping_balance', $advsop_settings_mv_columns)) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_shipping_balance'),
			'value' => 'ship_balance',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_total_costs', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_total_costs'),
			'value' => 'total_costs',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_total_profit', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_total_profit'),
			'value' => 'total_profit',
		);
		}
		if (!$advsop_settings_mv_columns or (in_array('mv_total_profit_prc', $advsop_settings_mv_columns))) {		
		$data['sort'][] = array(
			'text'  => $this->language->get('column_total_profit_prc'),
			'value' => 'total_profit_prc',
		);
		}
		}	
		
		if (isset($this->request->get['filter_sort'])) {
			$this->session->data['filter_sort'] = $filter_sort = $this->request->get['filter_sort'];					
		} else {
			$this->session->data['filter_sort'] = $filter_sort = 'report_type';
		}	

		$data['order'] = array();

		$data['order'][] = array(
			'text'  => $this->language->get('text_asc'),
			'value' => 'asc',
		);
		$data['order'][] = array(
			'text'  => $this->language->get('text_desc'),
			'value' => 'desc',
		);
		
		if (isset($this->request->get['filter_order'])) {
			$this->session->data['filter_order'] = $filter_order = $this->request->get['filter_order'];
		} else {
			if ($filter_report != 'sales_summary' && $filter_report != 'abandoned_orders' && $filter_report != 'tax') {
				$this->session->data['filter_order'] = $filter_order = 'asc';				
			} else {
				$this->session->data['filter_order'] = $filter_order = 'desc';
			}
		}
		
		$data['limit'] = array();

		$data['limit'][] = array(
			'text'  	=> '10',
			'value' 	=> '10',
			'subtext'	=> '',
		);
		$data['limit'][] = array(
			'text'  	=> '25',
			'value' 	=> '25',
			'subtext'	=> '',
		);
		$data['limit'][] = array(
			'text'  	=> '50',
			'value' 	=> '50',
			'subtext'	=> '',
		);
		$data['limit'][] = array(
			'text'  	=> '100',
			'value' 	=> '100',
			'subtext'	=> '',
		);
		$data['limit'][] = array(
			'text'  	=> '500',
			'value' 	=> '500',
			'subtext'	=> '',
		);
		$data['limit'][] = array(
			'text'  	=> '1000',
			'value' 	=> '1000',
			'subtext'	=> '',
		);			
		$data['limit'][] = array(
			'text'  	=> $this->language->get('text_all'),
			'value' 	=> '99999',
			'subtext'	=> '',
		);
		$data['limit'][] = array(
			'text'  	=> $this->language->get('text_all'),
			'value' 	=> '999999',
			'subtext'	=> $this->language->get('text_for_export'),
		);		
		
		if (isset($this->request->get['filter_limit'])) {
			$this->session->data['filter_limit'] = $filter_limit = $this->request->get['filter_limit'];
		} else {
			$this->session->data['filter_limit'] = $filter_limit = 25;
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		if (isset($this->request->get['filter_status_date_start'])) {
			$this->session->data['filter_status_date_start'] = $filter_status_date_start = $this->request->get['filter_status_date_start'];			
		} else {
			$this->session->data['filter_status_date_start'] = $filter_status_date_start = '';
		}

		if (isset($this->request->get['filter_status_date_end'])) {
			$this->session->data['filter_status_date_end'] = $filter_status_date_end = $this->request->get['filter_status_date_end'];		
		} else {
			$this->session->data['filter_status_date_end'] = $filter_status_date_end = '';
		}

		$data['order_statuses'] = $this->model_extension_report_adv_sales_profit->getOrderStatuses(); 	
		if (isset($this->request->get['filter_order_status_id'])) {
			$filter_order_status_id = explode(',', $this->request->get['filter_order_status_id']);
			$this->session->data['filter_order_status_id'] = $this->request->get['filter_order_status_id'];
		} else {
			$filter_order_status_id = array();
			$this->session->data['filter_order_status_id'] = '';
		}

		if (isset($this->request->get['filter_order_id_from'])) {
			if (is_numeric(trim($this->request->get['filter_order_id_from']))) {
				$this->session->data['filter_order_id_from'] = $filter_order_id_from = trim($this->request->get['filter_order_id_from']);
			} else {
				$this->session->data['filter_order_id_from'] = $filter_order_id_from = '';
			}
		} else {
			$this->session->data['filter_order_id_from'] = $filter_order_id_from = '';
		}
		
		if (isset($this->request->get['filter_order_id_to'])) {
			if (is_numeric(trim($this->request->get['filter_order_id_to']))) {
				$this->session->data['filter_order_id_to'] = $filter_order_id_to = trim($this->request->get['filter_order_id_to']);
			} else {
				$this->session->data['filter_order_id_to'] = $filter_order_id_to = '';
			}
		} else {
			$this->session->data['filter_order_id_to'] = $filter_order_id_to = '';
		}

		if (isset($this->request->get['filter_order_value_min'])) {
			if (is_numeric(trim($this->request->get['filter_order_value_min']))) {
				$this->session->data['filter_order_value_min'] = $filter_order_value_min = trim($this->request->get['filter_order_value_min']);
			} else {
				$this->session->data['filter_order_value_min'] = $filter_order_value_min = '';
			}
		} else {
			$this->session->data['filter_order_value_min'] = $filter_order_value_min = '';
		}
		
		if (isset($this->request->get['filter_order_value_max'])) {
			if (is_numeric(trim($this->request->get['filter_order_value_max']))) {
				$this->session->data['filter_order_value_max'] = $filter_order_value_max = trim($this->request->get['filter_order_value_max']);
			} else {
				$this->session->data['filter_order_value_max'] = $filter_order_value_max = '';
			}
		} else {
			$this->session->data['filter_order_value_max'] = $filter_order_value_max = '';
		}
		
		$data['stores'] = $this->model_extension_report_adv_sales_profit->getOrderStores();
		if (isset($this->request->get['filter_store_id'])) {
			$filter_store_id = explode(',', $this->request->get['filter_store_id']);
			$this->session->data['filter_store_id'] = $this->request->get['filter_store_id'];
		} else {
			$filter_store_id = array();
			$this->session->data['filter_store_id'] = '';
		}

		$data['currencies'] = $this->model_extension_report_adv_sales_profit->getOrderCurrencies();
		if (isset($this->request->get['filter_currency'])) {
			$filter_currency = explode(',', $this->request->get['filter_currency']);
			$this->session->data['filter_currency'] = $this->request->get['filter_currency'];
		} else {
			$filter_currency = array();
			$this->session->data['filter_currency'] = '';
		}

		$data['taxes'] = $this->model_extension_report_adv_sales_profit->getOrderTaxes();	
		if (isset($this->request->get['filter_taxes'])) {
			$filter_taxes = explode(',', $this->request->get['filter_taxes']);
			$this->session->data['filter_taxes'] = $this->request->get['filter_taxes'];
		} else {
			$filter_taxes = array();
			$this->session->data['filter_taxes'] = '';
		}

		$data['tax_classes'] = $this->model_extension_report_adv_sales_profit->getOrderTaxClasses();
		if (isset($this->request->get['filter_tax_classes'])) {
			$filter_tax_classes = explode(',', $this->request->get['filter_tax_classes']);
			$this->session->data['filter_tax_classes'] = $this->request->get['filter_tax_classes'];
		} else {
			$filter_tax_classes = array();
			$this->session->data['filter_tax_classes'] = '';
		}

		$data['geo_zones'] = $this->model_extension_report_adv_sales_profit->getOrderGeoZones();
		if (isset($this->request->get['filter_geo_zones'])) {
			$filter_geo_zones = explode(',', $this->request->get['filter_geo_zones']);
			$this->session->data['filter_geo_zones'] = $this->request->get['filter_geo_zones'];
		} else {
			$filter_geo_zones = array();
			$this->session->data['filter_geo_zones'] = '';
		}

		$data['customer_groups'] = $this->model_extension_report_adv_sales_profit->getOrderCustomerGroups();	
		if (isset($this->request->get['filter_customer_group_id'])) {
			$filter_customer_group_id = explode(',', $this->request->get['filter_customer_group_id']);
			$this->session->data['filter_customer_group_id'] = $this->request->get['filter_customer_group_id'];
		} else {
			$filter_customer_group_id = array();
			$this->session->data['filter_customer_group_id'] = '';
		}
		
		if (isset($this->request->get['filter_customer_name'])) {
			$this->session->data['filter_customer_name'] = $filter_customer_name = $this->request->get['filter_customer_name'];
		} else {
			$this->session->data['filter_customer_name'] = $filter_customer_name = '';
		}

		if (isset($this->request->get['filter_customer_email'])) {
			$this->session->data['filter_customer_email'] = $filter_customer_email = $this->request->get['filter_customer_email'];
		} else {
			$this->session->data['filter_customer_email'] = $filter_customer_email = '';
		}

		if (isset($this->request->get['filter_customer_telephone'])) {
			$this->session->data['filter_customer_telephone'] = $filter_customer_telephone = $this->request->get['filter_customer_telephone'];
		} else {
			$this->session->data['filter_customer_telephone'] = $filter_customer_telephone = '';
		}

		if (isset($this->request->get['filter_ip'])) {
			$this->session->data['filter_ip'] = $filter_ip = $this->request->get['filter_ip'];
		} else {
			$this->session->data['filter_ip'] = $filter_ip = '';
		}
		
		if (isset($this->request->get['filter_payment_company'])) {
			$this->session->data['filter_payment_company'] = $filter_payment_company = $this->request->get['filter_payment_company'];
		} else {
			$this->session->data['filter_payment_company'] = $filter_payment_company = '';
		}
		
		if (isset($this->request->get['filter_payment_address'])) {
			$this->session->data['filter_payment_address'] = $filter_payment_address = $this->request->get['filter_payment_address'];
		} else {
			$this->session->data['filter_payment_address'] = $filter_payment_address = '';
		}

		if (isset($this->request->get['filter_payment_city'])) {
			$this->session->data['filter_payment_city'] = $filter_payment_city = $this->request->get['filter_payment_city'];
		} else {
			$this->session->data['filter_payment_city'] = $filter_payment_city = '';
		}
		
		if (isset($this->request->get['filter_payment_zone'])) {
			$this->session->data['filter_payment_zone'] = $filter_payment_zone = $this->request->get['filter_payment_zone'];
		} else {
			$this->session->data['filter_payment_zone'] = $filter_payment_zone = '';
		}
		
		if (isset($this->request->get['filter_payment_postcode'])) {
			$this->session->data['filter_payment_postcode'] = $filter_payment_postcode = $this->request->get['filter_payment_postcode'];
		} else {
			$this->session->data['filter_payment_postcode'] = $filter_payment_postcode = '';
		}

		if (isset($this->request->get['filter_payment_country'])) {
			$this->session->data['filter_payment_country'] = $filter_payment_country = $this->request->get['filter_payment_country'];
		} else {
			$this->session->data['filter_payment_country'] = $filter_payment_country = '';
		}

		$data['payment_methods'] = $this->model_extension_report_adv_sales_profit->getOrderPaymentMethods();	
		if (isset($this->request->get['filter_payment_method'])) {
			$filter_payment_method = explode(',', $this->request->get['filter_payment_method']);
			$this->session->data['filter_payment_method'] = $this->request->get['filter_payment_method'];
		} else {
			$filter_payment_method = array();
			$this->session->data['filter_payment_method'] = '';
		}
		
		if (isset($this->request->get['filter_shipping_company'])) {
			$this->session->data['filter_shipping_company'] = $filter_shipping_company = $this->request->get['filter_shipping_company'];
		} else {
			$this->session->data['filter_shipping_company'] = $filter_shipping_company = '';
		}
		
		if (isset($this->request->get['filter_shipping_address'])) {
			$this->session->data['filter_shipping_address'] = $filter_shipping_address = $this->request->get['filter_shipping_address'];
		} else {
			$this->session->data['filter_shipping_address'] = $filter_shipping_address = '';
		}

		if (isset($this->request->get['filter_shipping_city'])) {
			$this->session->data['filter_shipping_city'] = $filter_shipping_city = $this->request->get['filter_shipping_city'];
		} else {
			$this->session->data['filter_shipping_city'] = $filter_shipping_city = '';
		}
		
		if (isset($this->request->get['filter_shipping_zone'])) {
			$this->session->data['filter_shipping_zone'] = $filter_shipping_zone = $this->request->get['filter_shipping_zone'];
		} else {
			$this->session->data['filter_shipping_zone'] = $filter_shipping_zone = '';
		}
		
		if (isset($this->request->get['filter_shipping_postcode'])) {
			$this->session->data['filter_shipping_postcode'] = $filter_shipping_postcode = $this->request->get['filter_shipping_postcode'];
		} else {
			$this->session->data['filter_shipping_postcode'] = $filter_shipping_postcode = '';
		}

		if (isset($this->request->get['filter_shipping_country'])) {
			$this->session->data['filter_shipping_country'] = $filter_shipping_country = $this->request->get['filter_shipping_country'];
		} else {
			$this->session->data['filter_shipping_country'] = $filter_shipping_country = '';
		}

		$data['shipping_methods'] = $this->model_extension_report_adv_sales_profit->getOrderShippingMethods();	
		if (isset($this->request->get['filter_shipping_method'])) {
			$filter_shipping_method = explode(',', $this->request->get['filter_shipping_method']);
			$this->session->data['filter_shipping_method'] = $this->request->get['filter_shipping_method'];
		} else {
			$filter_shipping_method = array();
			$this->session->data['filter_shipping_method'] = '';
		}

		$data['categories'] = $this->model_extension_report_adv_sales_profit->getProductsCategories(0);
		if (isset($this->request->get['filter_category'])) {
			$filter_category = explode(',', $this->request->get['filter_category']);
			$this->session->data['filter_category'] = $this->request->get['filter_category'];
		} else {
			$filter_category = array();
			$this->session->data['filter_category'] = '';
		}
		
		$data['manufacturers'] = $this->model_extension_report_adv_sales_profit->getProductsManufacturers(); 
		if (isset($this->request->get['filter_manufacturer'])) {
			$filter_manufacturer = explode(',', $this->request->get['filter_manufacturer']);
			$this->session->data['filter_manufacturer'] = $this->request->get['filter_manufacturer'];
		} else {
			$filter_manufacturer = array();
			$this->session->data['filter_manufacturer'] = '';
		}

		$data['suppliers'] = $this->model_extension_report_adv_sales_profit->getProductsSuppliers(); 
		if (isset($this->request->get['filter_supplier'])) {
			$filter_supplier = explode(',', $this->request->get['filter_supplier']);
			$this->session->data['filter_supplier'] = $this->request->get['filter_supplier'];
		} else {
			$filter_supplier = array();
			$this->session->data['filter_supplier'] = '';
		}
		
		if (isset($this->request->get['filter_sku'])) {
			$this->session->data['filter_sku'] = $filter_sku = $this->request->get['filter_sku'];
		} else {
			$this->session->data['filter_sku'] = $filter_sku = '';
		}

		if (isset($this->request->get['filter_product_name'])) {
			$this->session->data['filter_product_name'] = $filter_product_name = $this->request->get['filter_product_name'];
		} else {
			$this->session->data['filter_product_name'] = $filter_product_name = '';
		}
		
		if (isset($this->request->get['filter_model'])) {
			$this->session->data['filter_model'] = $filter_model = $this->request->get['filter_model'];
		} else {
			$this->session->data['filter_model'] = $filter_model = '';
		}

		if ($advsop_settings_filters and (in_array('option', $advsop_settings_filters))) {		
		$data['order_options'] = $this->model_extension_report_adv_sales_profit->getProductOptions();
		if (isset($this->request->get['filter_option'])) {
			$filter_option = explode(',', $this->request->get['filter_option']);
			$this->session->data['filter_option'] = $this->request->get['filter_option'];
		} else {
			$filter_option = array();
			$this->session->data['filter_option'] = '';
		}
		} else {
			$filter_option = '';
			$this->session->data['filter_option'] = '';
		}

		if ($advsop_settings_filters and (in_array('attribute', $advsop_settings_filters))) {		
		$data['attributes'] = $this->model_extension_report_adv_sales_profit->getProductAttributes();
		if (isset($this->request->get['filter_attribute'])) {
			$filter_attribute = explode(',', $this->request->get['filter_attribute']);
			$this->session->data['filter_attribute'] = $this->request->get['filter_attribute'];
		} else {
			$filter_attribute = array();
			$this->session->data['filter_attribute'] = '';
		}
		} else {
			$filter_attribute = '';
			$this->session->data['filter_attribute'] = '';
		}

		$data['locations'] = $this->model_extension_report_adv_sales_profit->getProductLocations();
		if (isset($this->request->get['filter_location'])) {
			$filter_location = explode(',', $this->request->get['filter_location']);
			$this->session->data['filter_location'] = $this->request->get['filter_location'];
		} else {
			$filter_location = array();
			$this->session->data['filter_location'] = '';
		}

		if ($advsop_settings_filters and (in_array('coupon_name', $advsop_settings_filters))) {		
		$data['coupon_names'] = $this->model_extension_report_adv_sales_profit->getOrderCouponNames();
		if (isset($this->request->get['filter_coupon_name'])) {
			$filter_coupon_name = explode(',', $this->request->get['filter_coupon_name']);
			$this->session->data['filter_coupon_name'] = $this->request->get['filter_coupon_name'];
		} else {
			$filter_coupon_name = array();
			$this->session->data['filter_coupon_name'] = '';
		}
		} else {
			$filter_coupon_name = '';
			$this->session->data['filter_coupon_name'] = '';
		}

		if (isset($this->request->get['filter_coupon_code'])) {
			$this->session->data['filter_coupon_code'] = $filter_coupon_code = $this->request->get['filter_coupon_code'];
		} else {
			$this->session->data['filter_coupon_code'] = $filter_coupon_code = '';
		}

		if (isset($this->request->get['filter_voucher_code'])) {
			$this->session->data['filter_voucher_code'] = $filter_voucher_code = $this->request->get['filter_voucher_code'];
		} else {
			$this->session->data['filter_voucher_code'] = $filter_voucher_code = '';
		}

		if ($advsop_settings_filters and (in_array('campaign_name', $advsop_settings_filters))) {		
		$data['campaign_names'] = $this->model_extension_report_adv_sales_profit->getOrderCampaignNames();
		if (isset($this->request->get['filter_campaign_name'])) {
			$filter_campaign_name = explode(',', $this->request->get['filter_campaign_name']);
			$this->session->data['filter_campaign_name'] = $this->request->get['filter_campaign_name'];
		} else {
			$filter_campaign_name = array();
			$this->session->data['filter_campaign_name'] = '';
		}
		} else {
			$filter_campaign_name = '';
			$this->session->data['filter_campaign_name'] = '';
		}

		if (isset($this->request->get['filter_campaign_code'])) {
			$this->session->data['filter_campaign_code'] = $filter_campaign_code = $this->request->get['filter_campaign_code'];
		} else {
			$this->session->data['filter_campaign_code'] = $filter_campaign_code = '';
		}
		
		if (isset($_GET['cron'])) {
			$cron_settings = $this->config->get('advsop' . $this->user->getId() . 'cron_setting') !== null ? unserialize($this->config->get('advsop' . $this->user->getId() . 'cron_setting')) : '';
			foreach ($cron_settings as $cron_setting) {
				if (isset($_GET['cron_id']) && ($_GET['cron_id'] == $cron_setting['cron_id'])) {
					$filter_report = $cron_setting['cron_filter_report'];
					$filter_details = $cron_setting['cron_filter_details'];
					$filter_group = $cron_setting['cron_filter_group'];
					$filter_sort = $cron_setting['cron_filter_sort'];
					$filter_order = $cron_setting['cron_filter_order'];
					$filter_limit = $cron_setting['cron_filter_limit'];
					
					$filter_range = $cron_setting['cron_filter_range'];
					$filter_date_start = $cron_setting['cron_date_start'];
					$filter_date_end = $cron_setting['cron_date_end'];
					$filter_status_date_start = $cron_setting['cron_status_date_start'];
					$filter_status_date_end = $cron_setting['cron_status_date_end'];
					$filter_order_status_id = $cron_setting['cron_filter_order_status_id'] != '' ? explode(',', $cron_setting['cron_filter_order_status_id']) : '';
					$filter_order_id_from = $cron_setting['cron_filter_order_id_from'];
					$filter_order_id_to = $cron_setting['cron_filter_order_id_to'];
					$filter_order_value_min = $cron_setting['cron_filter_order_value_min'];
					$filter_order_value_max = $cron_setting['cron_filter_order_value_max'];	
					$filter_store_id = $cron_setting['cron_filter_store_id'] != '' ? explode(',', $cron_setting['cron_filter_store_id']) : '';
					$filter_currency = $cron_setting['cron_filter_currency'] != '' ? explode(',', $cron_setting['cron_filter_currency']) : '';
					$filter_taxes = $cron_setting['cron_filter_taxes'] != '' ? explode(',', $cron_setting['cron_filter_taxes']) : '';	
					$filter_tax_classes = $cron_setting['cron_filter_tax_classes'] != '' ? explode(',', $cron_setting['cron_filter_tax_classes']) : '';	
					$filter_geo_zones = $cron_setting['cron_filter_geo_zones'] != '' ? explode(',', $cron_setting['cron_filter_geo_zones']) : '';	
					$filter_customer_group_id = $cron_setting['cron_filter_customer_group_id'] != '' ? explode(',', $cron_setting['cron_filter_customer_group_id']) : '';
					$filter_customer_name = $cron_setting['cron_filter_customer_name'];	
					$filter_customer_email = $cron_setting['cron_filter_customer_email'];	
					$filter_customer_telephone = $cron_setting['cron_filter_customer_telephone'];	
					$filter_ip = $cron_setting['cron_filter_ip'];	
					$filter_payment_company = $cron_setting['cron_filter_payment_company'];
					$filter_payment_address = $cron_setting['cron_filter_payment_address'];
					$filter_payment_city = $cron_setting['cron_filter_payment_city'];
					$filter_payment_zone = $cron_setting['cron_filter_payment_zone'];
					$filter_payment_postcode = $cron_setting['cron_filter_payment_postcode'];
					$filter_payment_country = $cron_setting['cron_filter_payment_country'];
					$filter_payment_method = $cron_setting['cron_filter_payment_method'] != '' ? explode(',', $cron_setting['cron_filter_payment_method']) : '';	
					$filter_shipping_company = $cron_setting['cron_filter_shipping_company'];
					$filter_shipping_address = $cron_setting['cron_filter_shipping_address'];
					$filter_shipping_city = $cron_setting['cron_filter_shipping_city'];	
					$filter_shipping_zone = $cron_setting['cron_filter_shipping_zone'];
					$filter_shipping_postcode = $cron_setting['cron_filter_shipping_postcode'];
					$filter_shipping_country = $cron_setting['cron_filter_shipping_country'];
					$filter_shipping_method = $cron_setting['cron_filter_shipping_method'] != '' ? explode(',', $cron_setting['cron_filter_shipping_method']) : '';	
					$filter_category = $cron_setting['cron_filter_category'] != '' ? explode(',', $cron_setting['cron_filter_category']) : '';	
					$filter_manufacturer = $cron_setting['cron_filter_manufacturer'] != '' ? explode(',', $cron_setting['cron_filter_manufacturer']) : '';	
					$filter_supplier = $cron_setting['cron_filter_supplier'] != '' ? explode(',', $cron_setting['cron_filter_supplier']) : '';	
					$filter_sku = $cron_setting['cron_filter_sku'];		
					$filter_product_name = $cron_setting['cron_filter_product_name'];
					$filter_model = $cron_setting['cron_filter_model'];
					$filter_option = $cron_setting['cron_filter_option'] != '' ? explode(',', $cron_setting['cron_filter_option']) : '';	
					$filter_attribute = $cron_setting['cron_filter_attribute'] != '' ? explode(',', $cron_setting['cron_filter_attribute']) : '';
					$filter_location = $cron_setting['cron_filter_location'] != '' ? explode(',', $cron_setting['cron_filter_location']) : '';	
					$filter_coupon_name = $cron_setting['cron_filter_coupon_name'] != '' ? explode(',', $cron_setting['cron_filter_coupon_name']) : '';
					$filter_coupon_code = $cron_setting['cron_filter_coupon_code'];
					$filter_voucher_code = $cron_setting['cron_filter_voucher_code'];
					$filter_campaign_name = $cron_setting['cron_filter_campaign_name'] != '' ? explode(',', $cron_setting['cron_filter_campaign_name']) : '';
					$filter_campaign_code = $cron_setting['cron_filter_campaign_code'];
				}
			}
		}
		
		$url = '';

		if (isset($this->request->get['filter_date_start'])) {
			$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		}

		if (isset($this->request->get['filter_date_end'])) {
			$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		}

		if (isset($this->request->get['filter_range'])) {
			$url .= '&filter_range=' . $this->request->get['filter_range'];
		}

		if (isset($this->request->get['filter_report'])) {
			$url .= '&filter_report=' . $this->request->get['filter_report'];
		}

		if (isset($this->request->get['filter_group'])) {
			$url .= '&filter_group=' . $this->request->get['filter_group'];
		}
		
		if (isset($this->request->get['filter_sort'])) {
			$url .= '&filter_sort=' . $this->request->get['filter_sort'];
		}

		if (isset($this->request->get['filter_order'])) {
			$url .= '&filter_order=' . $this->request->get['filter_order'];
		}
		
		if (isset($this->request->get['filter_details'])) {
			$url .= '&filter_details=' . $this->request->get['filter_details'];
		}
		
		if (isset($this->request->get['filter_limit'])) {
			$url .= '&filter_limit=' . $this->request->get['filter_limit'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['filter_status_date_start'])) {
			$url .= '&filter_status_date_start=' . $this->request->get['filter_status_date_start'];
		}
		
		if (isset($this->request->get['filter_status_date_end'])) {
			$url .= '&filter_status_date_end=' . $this->request->get['filter_status_date_end'];
		}
		
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
		
		if (isset($this->request->get['filter_order_id_from'])) {
			$url .= '&filter_order_id_from=' . $this->request->get['filter_order_id_from'];
		}
		
		if (isset($this->request->get['filter_order_id_to'])) {
			$url .= '&filter_order_id_to=' . $this->request->get['filter_order_id_to'];
		}

		if (isset($this->request->get['filter_order_value_min'])) {
			$url .= '&filter_order_value_min=' . $this->request->get['filter_order_value_min'];
		}
		
		if (isset($this->request->get['filter_order_value_max'])) {
			$url .= '&filter_order_value_max=' . $this->request->get['filter_order_value_max'];
		}
		
		if (isset($this->request->get['filter_store_id'])) {
			$url .= '&filter_store_id=' . $this->request->get['filter_store_id'];
		}
		
		if (isset($this->request->get['filter_currency'])) {
			$url .= '&filter_currency=' . $this->request->get['filter_currency'];
		}
		
		if (isset($this->request->get['filter_taxes'])) {
			$url .= '&filter_taxes=' . $this->request->get['filter_taxes'];
		}
		
		if (isset($this->request->get['filter_tax_classes'])) {
			$url .= '&filter_tax_classes=' . $this->request->get['filter_tax_classes'];
		}
		
		if (isset($this->request->get['filter_geo_zones'])) {
			$url .= '&filter_geo_zones=' . $this->request->get['filter_geo_zones'];
		}
		
		if (isset($this->request->get['filter_customer_group_id'])) {
			$url .= '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'];
		}
		
		if (isset($this->request->get['filter_customer_name'])) {
			$url .= '&filter_customer_name=' . urlencode(html_entity_decode($this->request->get['filter_customer_name'], ENT_QUOTES, 'UTF-8'));
		}		

		if (isset($this->request->get['filter_customer_email'])) {
			$url .= '&filter_customer_email=' . urlencode(html_entity_decode($this->request->get['filter_customer_email'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_customer_telephone'])) {
			$url .= '&filter_customer_telephone=' . urlencode(html_entity_decode($this->request->get['filter_customer_telephone'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_ip'])) {
			$url .= '&filter_ip=' . urlencode(html_entity_decode($this->request->get['filter_ip'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_company'])) {
			$url .= '&filter_payment_company=' . urlencode(html_entity_decode($this->request->get['filter_payment_company'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_address'])) {
			$url .= '&filter_payment_address=' . urlencode(html_entity_decode($this->request->get['filter_payment_address'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_city'])) {
			$url .= '&filter_payment_city=' . urlencode(html_entity_decode($this->request->get['filter_payment_city'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_zone'])) {
			$url .= '&filter_payment_zone=' . urlencode(html_entity_decode($this->request->get['filter_payment_zone'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_postcode'])) {
			$url .= '&filter_payment_postcode=' . urlencode(html_entity_decode($this->request->get['filter_payment_postcode'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_country'])) {
			$url .= '&filter_payment_country=' . urlencode(html_entity_decode($this->request->get['filter_payment_country'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_method'])) {
			$url .= '&filter_payment_method=' . $this->request->get['filter_payment_method'];
		}	
		
		if (isset($this->request->get['filter_shipping_company'])) {
			$url .= '&filter_shipping_company=' . urlencode(html_entity_decode($this->request->get['filter_shipping_company'], ENT_QUOTES, 'UTF-8'));
		}	

		if (isset($this->request->get['filter_shipping_address'])) {
			$url .= '&filter_shipping_address=' . urlencode(html_entity_decode($this->request->get['filter_shipping_address'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_shipping_city'])) {
			$url .= '&filter_shipping_city=' . urlencode(html_entity_decode($this->request->get['filter_shipping_city'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_shipping_zone'])) {
			$url .= '&filter_shipping_zone=' . urlencode(html_entity_decode($this->request->get['filter_shipping_zone'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_shipping_postcode'])) {
			$url .= '&filter_shipping_postcode=' . urlencode(html_entity_decode($this->request->get['filter_shipping_postcode'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_shipping_country'])) {
			$url .= '&filter_shipping_country=' . urlencode(html_entity_decode($this->request->get['filter_shipping_country'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_shipping_method'])) {
			$url .= '&filter_shipping_method=' . $this->request->get['filter_shipping_method'];
		}	
		
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}	
		
		if (isset($this->request->get['filter_manufacturer'])) {
			$url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
		}	

		if (isset($this->request->get['filter_supplier'])) {
			$url .= '&filter_supplier=' . $this->request->get['filter_supplier'];
		}
		
		if (isset($this->request->get['filter_sku'])) {
			$url .= '&filter_sku=' . urlencode(html_entity_decode($this->request->get['filter_sku'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_product_name'])) {
			$url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_option'])) {
			$url .= '&filter_option=' . $this->request->get['filter_option'];
		}	
		
		if (isset($this->request->get['filter_attribute'])) {
			$url .= '&filter_attribute=' . $this->request->get['filter_attribute'];
		}			

		if (isset($this->request->get['filter_location'])) {
			$url .= '&filter_location=' . $this->request->get['filter_location'];
		}	
		
		if (isset($this->request->get['filter_coupon_name'])) {
			$url .= '&filter_coupon_name=' . $this->request->get['filter_coupon_name'];
		}	
		
		if (isset($this->request->get['filter_coupon_code'])) {
			$url .= '&filter_coupon_code=' . urlencode(html_entity_decode($this->request->get['filter_coupon_code'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_voucher_code'])) {
			$url .= '&filter_voucher_code=' . urlencode(html_entity_decode($this->request->get['filter_voucher_code'], ENT_QUOTES, 'UTF-8'));
		}	

		if (isset($this->request->get['filter_campaign_name'])) {
			$url .= '&filter_campaign_name=' . $this->request->get['filter_campaign_name'];
		}	
		
		if (isset($this->request->get['filter_campaign_code'])) {
			$url .= '&filter_campaign_code=' . urlencode(html_entity_decode($this->request->get['filter_campaign_code'], ENT_QUOTES, 'UTF-8'));
		}	
		
   		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/report/adv_sales_profit', 'user_token=' . $this->session->data['user_token'], true)
   		);
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = 'module' AND `code` = 'adv_profit_module'");
		if (!$query->rows) {
			$data['settings'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		} else {	
			$data['settings'] = $this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true);
		}	

		if ($this->config->get('advsop' . $this->user->getId() . '_formula_1')) {
			$data['advsop_formula_1'] = $this->config->get('advsop' . $this->user->getId() . '_formula_1');
		} else {
			$data['advsop_formula_1'] = 0;
		}
		
		if ($this->config->get('advsop' . $this->user->getId() . '_formula_2')) {
			$data['advsop_formula_2'] = $this->config->get('advsop' . $this->user->getId() . '_formula_2');
		} else {
			$data['advsop_formula_2'] = 0;
		}
		
		if ($this->config->get('advsop' . $this->user->getId() . '_formula_3')) {
			$data['advsop_formula_3'] = $this->config->get('advsop' . $this->user->getId() . '_formula_3');
		} else {
			$data['advsop_formula_3'] = 0;
		}

		if ($this->config->get('advsop' . $this->user->getId() . '_formula_4')) {
			$data['advsop_formula_4'] = $this->config->get('advsop' . $this->user->getId() . '_formula_4');
		} else {
			$data['advsop_formula_4'] = 0;
		}
		
		if ($this->config->get('advsop' . $this->user->getId() . '_formula_5')) {
			$data['advsop_formula_5'] = $this->config->get('advsop' . $this->user->getId() . '_formula_5');
		} else {
			$data['advsop_formula_5'] = 0;
		}

		if ($this->config->get('advsop' . $this->user->getId() . '_formula_6')) {
			$data['advsop_formula_6'] = $this->config->get('advsop' . $this->user->getId() . '_formula_6');
		} else {
			$data['advsop_formula_6'] = 0;
		}

		if ($this->config->get('advsop' . $this->user->getId() . '_formula_7')) {
			$data['advsop_formula_7'] = $this->config->get('advsop' . $this->user->getId() . '_formula_7');
		} else {
			$data['advsop_formula_7'] = 0;
		}
		
		if ($this->config->get('advsop' . $this->user->getId() . '_return_action_refund')) {
			$data['advsop_return_action_refund'] = $this->config->get('advsop' . $this->user->getId() . '_return_action_refund');
		} else {
			$data['advsop_return_action_refund'] = '';
		}

		if ($this->config->get('advsop' . $this->user->getId() . '_date_format')) {
			$data['advsop_date_format'] = $this->config->get('advsop' . $this->user->getId() . '_date_format');
		} else {
			$data['advsop_date_format'] = 'DDMMYYYY';
		}

		if ($this->config->get('advsop' . $this->user->getId() . '_hour_format')) {
			$data['advsop_hour_format'] = $this->config->get('advsop' . $this->user->getId() . '_hour_format');
		} else {
			$data['advsop_hour_format'] = '24';
		}
		
		if ($this->config->get('advsop' . $this->user->getId() . '_week_days')) {
			$data['advsop_week_days'] = $this->config->get('advsop' . $this->user->getId() . '_week_days');
		} else {
			$data['advsop_week_days'] = 'mon_sun';
		}

		$selected_load_save_reports = $this->config->get('advsop' . $this->user->getId() . 'sr_load_save_report') !== null ? unserialize($this->config->get('advsop' . $this->user->getId() . 'sr_load_save_report')) : '';
		
		if (isset($this->request->post['advsop' . $this->user->getId() . 'sr_load_save_report'])) {
			$data['advsop_load_save_reports'] = $this->request->post['advsop_load_save_report'];
		} elseif (isset($selected_load_save_reports)) {
			$data['advsop_load_save_reports'] = $selected_load_save_reports;
		} else { 	
			$data['advsop_load_save_reports'] = array();
		}

		$selected_cron_settings = $this->config->get('advsop' . $this->user->getId() . 'cron_setting') !== null ? unserialize($this->config->get('advsop' . $this->user->getId() . 'cron_setting')) : '';
		
		if (isset($this->request->post['advsop' . $this->user->getId() . 'cron_setting'])) {
			$data['advsop_cron_settings'] = $this->request->post['advsop_cron_setting'];
		} elseif (isset($selected_cron_settings)) {
			$data['advsop_cron_settings'] = $selected_cron_settings;
		} else { 	
			$data['advsop_cron_settings'] = array();
		}
		
		$this->load->model('localisation/return_action');
		$data['return_actions'] = $this->model_localisation_return_action->getReturnActions();
		
		$data['auth'] = FALSE;
		$data['ldata'] = FALSE;
		$data['orders'] = array();
		
		$filter_data = array(
			'filter_date_start'	     		=> $filter_date_start, 
			'filter_date_end'	     		=> $filter_date_end,
			'filter_range'           		=> $filter_range,
			'filter_report'           		=> $filter_report,
			'filter_group'           		=> $filter_group,
			'filter_status_date_start'		=> $filter_status_date_start, 
			'filter_status_date_end'		=> $filter_status_date_end, 			
			'filter_order_status_id'		=> $filter_order_status_id,
			'filter_order_id_from'			=> $filter_order_id_from,
			'filter_order_id_to'			=> $filter_order_id_to,	
			'filter_order_value_min'		=> $filter_order_value_min,
			'filter_order_value_max'		=> $filter_order_value_max,				
			'filter_store_id'				=> $filter_store_id,
			'filter_currency'				=> $filter_currency,
			'filter_taxes'					=> $filter_taxes,
			'filter_tax_classes'			=> $filter_tax_classes,
			'filter_geo_zones'				=> $filter_geo_zones,			
			'filter_customer_group_id'		=> $filter_customer_group_id,
			'filter_customer_name'	 	 	=> $filter_customer_name,			
			'filter_customer_email'			=> $filter_customer_email,
			'filter_customer_telephone'		=> $filter_customer_telephone,
			'filter_ip' 	 				=> $filter_ip,			
			'filter_payment_company'		=> $filter_payment_company,
			'filter_payment_address'		=> $filter_payment_address,
			'filter_payment_city'			=> $filter_payment_city,
			'filter_payment_zone'			=> $filter_payment_zone,			
			'filter_payment_postcode'		=> $filter_payment_postcode,
			'filter_payment_country'		=> $filter_payment_country,
			'filter_payment_method'  		=> $filter_payment_method,
			'filter_shipping_company'		=> $filter_shipping_company,
			'filter_shipping_address'		=> $filter_shipping_address,
			'filter_shipping_city'			=> $filter_shipping_city,
			'filter_shipping_zone'			=> $filter_shipping_zone,			
			'filter_shipping_postcode'		=> $filter_shipping_postcode,
			'filter_shipping_country'		=> $filter_shipping_country,
			'filter_shipping_method'  		=> $filter_shipping_method,
			'filter_category'				=> $filter_category,
			'filter_manufacturer'			=> $filter_manufacturer,
			'filter_supplier'				=> $filter_supplier,
			'filter_sku' 	 				=> $filter_sku,
			'filter_product_name'			=> $filter_product_name,
			'filter_model' 	 				=> $filter_model,
			'filter_option'  				=> $filter_option,
			'filter_attribute' 	 		 	=> $filter_attribute,
			'filter_location'  				=> $filter_location,
			'filter_coupon_name'			=> $filter_coupon_name,
			'filter_coupon_code'			=> $filter_coupon_code,
			'filter_voucher_code'			=> $filter_voucher_code,
			'filter_campaign_name'			=> $filter_campaign_name,
			'filter_campaign_code'			=> $filter_campaign_code,
			'filter_sort'  					=> $filter_sort,
			'filter_order'  				=> $filter_order,
			'filter_details'  				=> $filter_details,
			'filter_limit'  				=> $filter_limit,			
			'start'                  		=> ($page - 1) * $filter_limit
		);

		$results = $this->model_extension_report_adv_sales_profit->getSalesProfit($filter_data);
		$totals = $filter_limit != '999999' ? $this->model_extension_report_adv_sales_profit->getSalesProfitTotal($filter_data) : FALSE;

		if ($filter_limit != '999999') {
		if ($filter_details == 'all_details_products' or $filter_details == 'all_details_orders') {
		$counter = 0;
		foreach ($totals as $total) {
			$counter += count(explode(',',$total['counter'] ?? '',0));
		}
		$total = $counter;
		} else {
		$total = $totals;
		}
		} else {
		$total = '999999';	
		}
		
		foreach ($results as $result) {

		if ($filter_details != 'all_details_products' && $filter_details != 'all_details_orders') {

		  if ($filter_details == 'basic_details' && $filter_report != 'tax') {
			$details = array(
				'order_ord_id'     					=> $result['order_ord_id'],
				'order_ord_id_link'     			=> $result['order_ord_id_link'],
				'order_ord_date'    				=> $result['order_ord_date'],
				'order_inv_no'     					=> $result['order_inv_no'],
				'order_name'   						=> $result['order_name'],
				'order_email'   					=> $result['order_email'],
				'order_group'   					=> $result['order_group'],
				'order_shipping_method' 			=> strip_tags($result['order_shipping_method'], '<br>'),
				'order_payment_method'  			=> strip_tags($result['order_payment_method'], '<br>'),
				'order_status'  					=> $result['order_status'],
				'order_store'      					=> $result['order_store'],	
				'order_currency' 					=> $result['order_currency'],				
				'order_products' 					=> $result['order_products'],
				'order_sub_total'  					=> $result['order_sub_total'],
				'order_shipping'  					=> $result['order_shipping'],
				'order_tax'  						=> $result['order_tax'],
				'order_coupon'  					=> $filter_report == 'coupon' ? $result['order_coupon'] : '',
				'order_voucher'  					=> $filter_report == 'voucher' ? $result['order_voucher'] : '',
				'order_value'  						=> $result['order_value'],
				'order_sales'   					=> $result['order_sales'],
				'order_costs'   					=> $result['order_costs'],				
				'order_profit'   					=> $result['order_profit'],	
				'order_profit_prc' 					=> $result['order_profit_prc'],				
				'product_ord_id'  					=> $result['product_ord_id'],
				'product_ord_id_link'  				=> $result['product_ord_id_link'],
				'product_ord_date'  				=> $result['product_ord_date'],
				'product_pid'  						=> $result['product_pid'],
				'product_pid_link'  				=> $result['product_pid_link'],	
				'product_sku'  						=> $result['product_sku'],
				'product_model'  					=> $result['product_model'],				
				'product_name'  					=> $result['product_name'],	
				'product_option'  					=> $result['product_option'],					
				'product_attributes'  				=> $result['product_attributes'],
				'product_manu'  					=> $result['product_manu'],
				'product_category'  				=> $result['product_category'],
				'product_supplier'  				=> $result['product_supplier'],			
				'product_currency'  				=> $result['product_currency'],
				'product_price'  					=> $result['product_price'],
				'product_quantity'  				=> $result['product_quantity'],
				'product_total_excl_vat'  			=> $result['product_total_excl_vat'],				
				'product_tax'  						=> $result['product_tax'],
				'product_total_incl_vat'  			=> $result['product_total_incl_vat'],				
				'product_sales'  					=> $result['product_sales'],
				'product_cost'   					=> $result['product_cost'],			
				'product_profit'   					=> $result['product_profit'],
				'product_profit_margin' 			=> $result['product_profit_margin'],
				'product_profit_markup' 			=> $result['product_profit_markup'],
				'customer_ord_id' 					=> $result['customer_ord_id'],
				'customer_ord_id_link' 				=> $result['customer_ord_id_link'],
				'customer_ord_date' 				=> $result['customer_ord_date'],
				'customer_cust_id' 					=> $result['customer_cust_id'],
				'customer_cust_id_link' 			=> $result['customer_cust_id_link'],	
				'billing_name' 						=> $result['billing_name'],
				'billing_company' 					=> $result['billing_company'],
				'billing_address_1' 				=> $result['billing_address_1'],
				'billing_address_2' 				=> $result['billing_address_2'],
				'billing_city' 						=> $result['billing_city'],
				'billing_zone' 						=> $result['billing_zone'],
				'billing_postcode' 					=> $result['billing_postcode'],	
				'billing_country' 					=> $result['billing_country'],
				'customer_telephone' 				=> $result['customer_telephone'],
				'shipping_name' 					=> $result['shipping_name'],
				'shipping_company' 					=> $result['shipping_company'],
				'shipping_address_1' 				=> $result['shipping_address_1'],
				'shipping_address_2' 				=> $result['shipping_address_2'],
				'shipping_city' 					=> $result['shipping_city'],
				'shipping_zone' 					=> $result['shipping_zone'],
				'shipping_postcode' 				=> $result['shipping_postcode'],	
				'shipping_country' 					=> $result['shipping_country']			
			);
		  } else {
			$details = array(		
			);			  
		  }
		  
		  if ($filter_report == 'tax') {

			$orders_total = 0;
			$total_tax_total = 0;
	
			foreach ($results as $totals) {
    			$orders_total += $totals['orders'];
				$total_tax_total += $totals['total_tax'];		
			}
			$data['orders_total'] = $orders_total;
			$data['total_tax_total'] = $this->currency->format($total_tax_total, $this->config->get('config_currency'));
			$data['total_tax_total_raw'] = $total_tax_total;

			$orders[] = array(
				'year'		       					=> $result['year'],
				'quarter'		       				=> 'Q' . $result['quarter'],	
				'year_quarter'		       			=> 'Q' . $result['quarter']. '<br>' . $result['year'],
				'month'		       					=> $result['month'],
				'year_month'		       			=> $result['month'] . '<br>' . $result['year'],			
				'date_start' 						=> date($data['advsop_date_format'] == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($result['date_start'])),
				'date_end'   						=> date($data['advsop_date_format'] == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($result['date_end'])),	
				'tax_title'   						=> html_entity_decode($result['tax_title']),
				'tax_rate'   						=> substr($result['tax_rate'],0,-2),
				'order_id'   						=> $result['order_id'],	
				'orders'     						=> $result['orders'],
				'total_tax'        					=> $this->currency->format($result['total_tax'], $this->config->get('config_currency')),		
				'total_tax_raw'      				=> $result['total_tax'],	
				'orders_total'      				=> $orders_total,	
				'total_tax_total'      				=> $this->currency->format($total_tax_total, $this->config->get('config_currency')),
				'total_tax_total_raw'      			=> $total_tax_total
			);

		  } else if ($filter_report == 'coupon') {

			$coupon_amount_total = 0;
			$coupon_orders_total = 0;
			$coupon_total_total = 0;
			
			foreach ($results as $totals) {
				$coupon_amount_total += $totals['coupon_amount'];	
    			$coupon_orders_total += $totals['coupon_orders'];
				$coupon_total_total += $totals['coupon_total'];
			}
			$data['coupon_amount_total'] = $this->currency->format($coupon_amount_total, $this->config->get('config_currency'));
			$data['coupon_amount_total_raw'] = $coupon_amount_total;
			$data['coupon_orders_total'] = $coupon_orders_total;
			$data['coupon_total_total'] = $this->currency->format($coupon_total_total, $this->config->get('config_currency'));
			$data['coupon_total_total_raw'] = $coupon_total_total;

			$orders[] = array(
				'date_start' 						=> date($data['advsop_date_format'] == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($result['date_start'])),
				'date_end'   						=> date($data['advsop_date_format'] == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($result['date_end'])),	
				'coupon_id'   						=> $result['coupon_id'],
				'order_id'   						=> $result['order_id'],
				'coupon_name'   					=> $result['coupon_name'],
				'coupon_code'   					=> $result['coupon_code'],
				'coupon_discount'   				=> substr($result['coupon_discount'],0,-2),
				'coupon_type'   					=> ($result['coupon_type'] == 'P' ? $this->language->get('text_percent') : $this->language->get('text_amount')),
				'coupon_status'   					=> ($result['coupon_status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'coupon_date_added'   				=> date($data['advsop_date_format'] == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($result['coupon_date_added'])),	
				'coupon_last_used'   				=> date($data['advsop_date_format'] == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($result['coupon_last_used'])),	
				'coupon_amount'        				=> $this->currency->format($result['coupon_amount'], $this->config->get('config_currency')),		
				'coupon_amount_raw'      			=> $result['coupon_amount'],
				'coupon_orders'   					=> $result['coupon_orders'],
				'coupon_total'        				=> $this->currency->format($result['coupon_total'], $this->config->get('config_currency')),		
				'coupon_total_raw'      			=> $result['coupon_total'],	
				'coupon_amount_total'     	 		=> $this->currency->format($coupon_amount_total, $this->config->get('config_currency')),
				'coupon_amount_total_raw' 	     	=> $coupon_amount_total,
				'coupon_orders_total'     	 		=> $coupon_orders_total,
				'coupon_total_total'     	 		=> $this->currency->format($coupon_total_total, $this->config->get('config_currency')),
				'coupon_total_total_raw' 	     	=> $coupon_total_total,
			) + $details;

		  } else if ($filter_report == 'voucher') {

			$voucher_amount_total = 0;
			$voucher_used_value_total = 0;
			$voucher_remaining_value_total = 0;
			$voucher_orders_total = 0;
			$voucher_total_total = 0;
			
			foreach ($results as $totals) {
    			$voucher_amount_total += $totals['voucher_amount'];
				$voucher_used_value_total += $totals['voucher_used_value'];
				$voucher_remaining_value_total += $totals['voucher_remaining_value'];
    			$voucher_orders_total += $totals['voucher_orders'];
				$voucher_total_total += $totals['voucher_total'];
			}
			$data['voucher_amount_total'] = $this->currency->format($voucher_amount_total, $this->config->get('config_currency'));
			$data['voucher_amount_total_raw'] = $voucher_amount_total;
			$data['voucher_used_value_total'] = $this->currency->format($voucher_used_value_total, $this->config->get('config_currency'));
			$data['voucher_used_value_total_raw'] = $voucher_used_value_total;
			$data['voucher_remaining_value_total'] = $this->currency->format($voucher_remaining_value_total, $this->config->get('config_currency'));
			$data['voucher_remaining_value_total_raw'] = $voucher_remaining_value_total;
			$data['voucher_orders_total'] = $voucher_orders_total;
			$data['voucher_total_total'] = $this->currency->format($voucher_total_total, $this->config->get('config_currency'));
			$data['voucher_total_total_raw'] = $voucher_total_total;

			$orders[] = array(
				'date_start' 						=> date($data['advsop_date_format'] == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($result['date_start'])),
				'date_end'   						=> date($data['advsop_date_format'] == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($result['date_end'])),	
				'voucher_id'   						=> $result['voucher_id'],
				'order_id'   						=> $result['order_id'],
				'voucher_code'   					=> $result['voucher_code'],
				'voucher_from_name'   				=> $result['voucher_from_name'],
				'voucher_from_email'   				=> $result['voucher_from_email'],
				'voucher_to_name'   				=> $result['voucher_to_name'],
				'voucher_to_email'   				=> $result['voucher_to_email'],
				'voucher_theme'   					=> $result['voucher_theme'],
				'voucher_status'   					=> ($result['voucher_status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'voucher_date_added'   				=> date($data['advsop_date_format'] == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($result['voucher_date_added'])),				
				'voucher_amount'        			=> $this->currency->format($result['voucher_amount'], $this->config->get('config_currency')),		
				'voucher_amount_raw'      			=> $result['voucher_amount'],		
				'voucher_used_value'        		=> $this->currency->format($result['voucher_used_value'], $this->config->get('config_currency')),		
				'voucher_used_value_raw'     	 	=> $result['voucher_used_value'],
				'voucher_remaining_value'     	  	=> $this->currency->format($result['voucher_remaining_value'], $this->config->get('config_currency')),		
				'voucher_remaining_value_raw' 	  	=> $result['voucher_remaining_value'],
				'voucher_orders'   					=> $result['voucher_orders'],
				'voucher_total'        				=> $this->currency->format($result['voucher_total'], $this->config->get('config_currency')),		
				'voucher_total_raw'      			=> $result['voucher_total'],
				'voucher_amount_total'      		=> $this->currency->format($voucher_amount_total, $this->config->get('config_currency')),
				'voucher_amount_total_raw'      	=> $voucher_amount_total,
				'voucher_used_value_total'      	=> $this->currency->format($voucher_used_value_total, $this->config->get('config_currency')),
				'voucher_used_value_total_raw'     	=> $voucher_used_value_total,
				'voucher_remaining_value_total'    	=> $this->currency->format($voucher_remaining_value_total, $this->config->get('config_currency')),
				'voucher_remaining_value_total_raw'	=> $voucher_remaining_value_total,	
				'voucher_orders_total'     	 		=> $voucher_orders_total,
				'voucher_total_total'     	 		=> $this->currency->format($voucher_total_total, $this->config->get('config_currency')),
				'voucher_total_total_raw' 	     	=> $voucher_total_total,
			) + $details;
			
		  } else {
			  
			$total_sales = $result['sub_total']+$result['handling']+$result['low_order_fee']+$result['reward']+$result['coupon']+$result['credit']+$result['voucher']+($data['advsop_formula_1'] ? $result['shipping'] : 0)-($data['advsop_formula_6'] ? $result['refunds'] : 0);
			$total_costs = $result['prod_costs']+$result['commission']+($data['advsop_formula_3'] ? $result['payment_cost'] : 0)+($data['advsop_formula_2'] ? $result['shipping_cost'] : 0)+($data['advsop_formula_4'] ? $result['extra_cost'] : 0)+($data['advsop_formula_5'] ? $result['return_cost'] : 0)-($data['advsop_formula_7'] ? $result['prod_costs_returns'] : 0);
			$total_profit_prc = ($total_sales) > 0 ? round(100 * (($total_sales-$total_costs) / $total_sales), 2) . '%' : '0%';

			$orders_total = 0;
			$customers_total = 0;
			$products_total = 0;
    		$sub_total_total = 0;
			$handling_total = 0;
			$low_order_fee_total = 0;
			$shipping_total = 0;
			$reward_total = 0;	
			$earned_reward_points_total = 0;
			$used_reward_points_total = 0;
			$coupon_total = 0;
			$taxes_total = 0;
			$credit_total = 0;
			$voucher_total = 0;
			$discount_total = 0;
			$total_total = 0;
			$refunds_total = 0;
    		$total_sales_total = 0;			
			$prod_costs_total = 0;
			$commission_total = 0;
			$pay_costs_total = 0;
			$ship_costs_total = 0;
			$extra_costs_total = 0;
			$return_costs_total = 0;
    		$total_costs_total = 0;
    		$total_profit_total_prc = 0; 
	
			foreach ($results as $totals) {
    			$orders_total += $totals['orders'];
				$customers_total += $totals['customers'];
				$products_total += $totals['products'];
				$sub_total_total += $totals['sub_total'];
				$handling_total += $totals['handling'];
				$low_order_fee_total += $totals['low_order_fee'];
				$shipping_total += $totals['shipping'];
				$reward_total += $totals['reward'];
				$earned_reward_points_total += $totals['earned_reward_points'];
				$used_reward_points_total += $totals['used_reward_points'];	
				$coupon_total += $totals['coupon'];
				$taxes_total += $totals['taxes'];
				$credit_total += $totals['credit'];
				$voucher_total += $totals['voucher'];
				$discount_total += $totals['discount'];
				$total_total += $totals['total'];
				$refunds_total += $totals['refunds'];
				$total_sales_total += ($totals['sub_total']+$totals['handling']+$totals['low_order_fee']+$totals['reward']+$totals['coupon']+$totals['credit']+$totals['voucher']+($data['advsop_formula_1'] ? $totals['shipping'] : 0)-($data['advsop_formula_6'] ? $totals['refunds'] : 0));
				$prod_costs_total += $totals['prod_costs']-($data['advsop_formula_7'] ? $totals['prod_costs_returns'] : 0);
				$commission_total += $totals['commission'];
				$pay_costs_total += $totals['payment_cost'];
				$ship_costs_total += $totals['shipping_cost'];
				$extra_costs_total += $totals['extra_cost'];
				$return_costs_total += $totals['return_cost'];
				$total_costs_total += ($totals['prod_costs']+$totals['commission']+($data['advsop_formula_3'] ? $totals['payment_cost'] : 0)+($data['advsop_formula_2'] ? $totals['shipping_cost'] : 0)+($data['advsop_formula_4'] ? $totals['extra_cost'] : 0)+($data['advsop_formula_5'] ? $totals['return_cost'] : 0)-($data['advsop_formula_7'] ? $totals['prod_costs_returns'] : 0));
				$total_profit_total_prc = ($total_sales_total) > 0 ? round(100 * (($total_sales_total-$total_costs_total) / $total_sales_total), 2) . '%' : '0%';			
			}
    			$data['orders_total'] = $filter_report == 'currency' ? '' : $orders_total;
				$data['customers_total'] = $filter_report == 'currency' ? '' : $customers_total;
				$data['products_total'] = $filter_report == 'currency' ? '' : $products_total;
				$data['sub_total_total'] = $filter_report == 'currency' ? '' : $this->currency->format($sub_total_total, $this->config->get('config_currency'));
				$data['handling_total'] = $filter_report == 'currency' ? '' : $this->currency->format($handling_total, $this->config->get('config_currency'));
				$data['low_order_fee_total'] = $filter_report == 'currency' ? '' : $this->currency->format($low_order_fee_total, $this->config->get('config_currency'));
				$data['shipping_total'] = $filter_report == 'currency' ? '' : $this->currency->format($shipping_total, $this->config->get('config_currency'));
				$data['reward_total'] = $filter_report == 'currency' ? '' : $this->currency->format($reward_total, $this->config->get('config_currency'));
				$data['earned_reward_points_total'] = $filter_report == 'currency' ? '' : ($earned_reward_points_total ? $earned_reward_points_total : 0);
				$data['used_reward_points_total'] = $filter_report == 'currency' ? '' : ($used_reward_points_total ? $used_reward_points_total : 0);	
				$data['coupon_total'] = $filter_report == 'currency' ? '' : $this->currency->format($coupon_total, $this->config->get('config_currency'));
				$data['taxes_total'] = $filter_report == 'currency' ? '' : $this->currency->format($taxes_total, $this->config->get('config_currency'));
				$data['credit_total'] = $filter_report == 'currency' ? '' : $this->currency->format($credit_total, $this->config->get('config_currency'));
				$data['voucher_total'] = $filter_report == 'currency' ? '' : $this->currency->format($voucher_total, $this->config->get('config_currency'));
				$data['discount_total'] = $filter_report == 'currency' ? '' : $this->currency->format($discount_total, $this->config->get('config_currency'));
				$data['total_total'] = $filter_report == 'currency' ? '' : $this->currency->format($total_total, $this->config->get('config_currency'));
				$data['aov_total'] = $filter_report == 'currency' ? '' : $this->currency->format($total_total / $orders_total, $this->config->get('config_currency'));
				$data['refunds_total'] = $filter_report == 'currency' ? '' : $this->currency->format($data['advsop_formula_6'] ? '-' . $refunds_total : $refunds_total, $this->config->get('config_currency'));
				$data['total_sales_total'] = $filter_report == 'currency' ? '' : $this->currency->format($total_sales_total, $this->config->get('config_currency'));
				$data['total_sales_total_raw'] = $filter_report == 'currency' ? '' : $total_sales_total;
				$data['prod_costs_total'] = $filter_report == 'currency' ? '' : $this->currency->format('-' . $prod_costs_total, $this->config->get('config_currency'));
				$data['commission_total'] = $filter_report == 'currency' ? '' : $this->currency->format('-' . $commission_total, $this->config->get('config_currency'));
				$data['pay_costs_total'] = $filter_report == 'currency' ? '' : $this->currency->format('-' . $pay_costs_total, $this->config->get('config_currency'));
				$data['ship_costs_total'] = $filter_report == 'currency' ? '' : $this->currency->format('-' . $ship_costs_total, $this->config->get('config_currency'));
				$data['extra_costs_total'] = $filter_report == 'currency' ? '' : $this->currency->format('-' . $extra_costs_total, $this->config->get('config_currency'));
				$data['return_costs_total'] = $filter_report == 'currency' ? '' : $this->currency->format('-' . $return_costs_total, $this->config->get('config_currency'));
				$data['ship_balance_total'] = $filter_report == 'currency' ? '' : $this->currency->format($shipping_total-$ship_costs_total, $this->config->get('config_currency'));
				$data['total_costs_total'] = $filter_report == 'currency' ? '' : $this->currency->format('-' . $total_costs_total, $this->config->get('config_currency'));
				$data['total_costs_total_raw'] = $filter_report == 'currency' ? '' : $total_costs_total;
				$data['total_profit_total'] = $filter_report == 'currency' ? '' : $this->currency->format($total_sales_total-$total_costs_total, $this->config->get('config_currency'));
				$data['total_profit_total_raw'] = $filter_report == 'currency' ? '' : $total_sales_total-$total_costs_total;
				$data['total_profit_total_prc'] = $filter_report == 'currency' ? '' : $total_profit_total_prc;

			$orders[] = array(
				'year'		       					=> $result['year'],
				'quarter'		       				=> 'Q' . $result['quarter'],	
				'year_quarter'		       			=> 'Q' . $result['quarter']. '<br>' . $result['year'],
				'month'		       					=> $result['month'],
				'year_month'		       			=> $result['month'] . '<br>' . $result['year'],			
				'date_start' 						=> date($data['advsop_date_format'] == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($result['date_start'])),
				'date_end'   						=> date($data['advsop_date_format'] == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($result['date_end'])),	
				'day_of_week'   					=> $result['day_of_week'],
				'hour'   							=> date($data['advsop_hour_format'] == '24' ? 'H:00' : 'g:00 A', strtotime($result['date_added'])),
				'store_name'   						=> html_entity_decode($result['store_name']),
				'currency_code'   					=> html_entity_decode($result['currency_code']),
				'customer_group'   					=> html_entity_decode($result['customer_group']),
				'tax'   							=> $filter_report == 'tax' ? html_entity_decode($result['tax_title']) : '',
				'payment_country'   				=> $result['payment_country'],
				'payment_postcode'   				=> $result['payment_postcode'],
				'payment_zone'   					=> $result['payment_zone']. ', ' . $result['payment_country'],
				'payment_city'   					=> $result['payment_city']. ', ' . $result['payment_country'],
				'payment_method'   					=> preg_replace('~\(.*?\)~', '', $result['payment_method']),
				'shipping_method'   				=> preg_replace('~\(.*?\)~', '', $result['shipping_method']),
				'order_id'   						=> $result['order_id'],	
				'orders'     						=> $result['orders'],
				'customers'   						=> $result['customers'],				
				'products'   						=> $result['products'],	
				'iso_code'   						=> $result['iso_code'],
				'sub_total'        					=> $filter_report == 'currency' ? $this->currency->format($result['sub_total'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['sub_total'], $this->config->get('config_currency')),
				'sub_total_raw'      				=> $filter_report == 'currency' ? ($result['currency_value']*$result['sub_total']) : $result['sub_total'],
				'handling'        					=> $filter_report == 'currency' ? $this->currency->format($result['handling'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['handling'], $this->config->get('config_currency')),
				'handling_raw'      				=> $filter_report == 'currency' ? ($result['currency_value']*$result['handling']) : $result['handling'],
				'low_order_fee'        				=> $filter_report == 'currency' ? $this->currency->format($result['low_order_fee'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['low_order_fee'], $this->config->get('config_currency')),
				'low_order_fee_raw'      			=> $filter_report == 'currency' ? ($result['currency_value']*$result['low_order_fee']) : $result['low_order_fee'],
				'shipping'        					=> $filter_report == 'currency' ? $this->currency->format($result['shipping'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['shipping'], $this->config->get('config_currency')),
				'shipping_raw'      				=> $filter_report == 'currency' ? ($result['currency_value']*$result['shipping']) : $result['shipping'],
				'reward'      						=> $filter_report == 'currency' ? $this->currency->format($result['reward'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['reward'], $this->config->get('config_currency')),
				'reward_raw'      					=> $filter_report == 'currency' ? ($result['currency_value']*$result['reward']) : $result['reward'],
				'earned_reward_points'   	   		=> $result['earned_reward_points'] ? $result['earned_reward_points'] : 0,
				'used_reward_points'   	   			=> $result['used_reward_points'] ? $result['used_reward_points'] : 0,
				'coupon'      						=> $filter_report == 'currency' ? $this->currency->format($result['coupon'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['coupon'], $this->config->get('config_currency')),
				'coupon_raw'      					=> $filter_report == 'currency' ? ($result['currency_value']*$result['coupon']) : $result['coupon'],
				'taxes'        						=> $filter_report == 'currency' ? $this->currency->format($result['taxes'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['taxes'], $this->config->get('config_currency')),
				'taxes_raw'      					=> $filter_report == 'currency' ? ($result['currency_value']*$result['taxes']) : $result['taxes'],
				'credit'      						=> $filter_report == 'currency' ? $this->currency->format($result['credit'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['credit'], $this->config->get('config_currency')),
				'credit_raw'      					=> $filter_report == 'currency' ? ($result['currency_value']*$result['credit']) : $result['credit'],
				'voucher'        					=> $filter_report == 'currency' ? $this->currency->format($result['voucher'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['voucher'], $this->config->get('config_currency')),
				'voucher_raw'      					=> $filter_report == 'currency' ? ($result['currency_value']*$result['voucher']) : $result['voucher'],
				'discount'      					=> $filter_report == 'currency' ? $this->currency->format($result['discount'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['discount'], $this->config->get('config_currency')),
				'discount_raw'      				=> $filter_report == 'currency' ? ($result['currency_value']*$result['discount']) : $result['discount'],				
				'total'      						=> $filter_report == 'currency' ? $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['total'], $this->config->get('config_currency')),
				'total_raw'      					=> $filter_report == 'currency' ? ($result['currency_value']*$result['total']) : $result['total'],
				'aov'      							=> $filter_report == 'currency' ? $this->currency->format($result['total'] / $result['orders'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['total'] / $result['orders'], $this->config->get('config_currency')),
				'aov_raw'    		  				=> $filter_report == 'currency' ? ($result['currency_value']*($result['total'] / $result['orders'])) : $result['total'] / $result['orders'],
				'refunds'      						=> $filter_report == 'currency' ? $this->currency->format($data['advsop_formula_6'] ? '-' . $result['refunds'] : $result['refunds'], $result['currency_code'], $result['currency_value']) : $this->currency->format($data['advsop_formula_6'] ? '-' . $result['refunds'] : $result['refunds'], $this->config->get('config_currency')),
				'refunds_raw'      					=> $filter_report == 'currency' ? ($result['currency_value']*$result['refunds']) : $result['refunds'],
				'total_sales'      					=> $filter_report == 'currency' ? $this->currency->format($total_sales, $result['currency_code'], $result['currency_value']) : $this->currency->format($total_sales, $this->config->get('config_currency')),
				'total_sales_raw'      				=> $filter_report == 'currency' ? ($result['currency_value']*$total_sales) : $total_sales,
				'prod_costs'      					=> $filter_report == 'currency' ? $this->currency->format('-' . ($result['prod_costs']-($data['advsop_formula_7'] ? $result['prod_costs_returns'] : 0)), $result['currency_code'], $result['currency_value']) : $this->currency->format('-' . ($result['prod_costs']-($data['advsop_formula_7'] ? $result['prod_costs_returns'] : 0)), $this->config->get('config_currency')),
				'prod_costs_raw'      				=> $filter_report == 'currency' ? ($result['currency_value']*($result['prod_costs']-($data['advsop_formula_7'] ? $result['prod_costs_returns'] : 0))) : $result['prod_costs']-($data['advsop_formula_7'] ? $result['prod_costs_returns'] : 0),
				'commission'      					=> $filter_report == 'currency' ? $this->currency->format('-' . ($result['commission']), $result['currency_code'], $result['currency_value']) : $this->currency->format('-' . ($result['commission']), $this->config->get('config_currency')),
				'commission_raw'      				=> $filter_report == 'currency' ? ($result['currency_value']*$result['commission']) : $result['commission'],
				'pay_costs'      					=> $filter_report == 'currency' ? $this->currency->format('-' . ($result['payment_cost']), $result['currency_code'], $result['currency_value']) : $this->currency->format('-' . ($result['payment_cost']), $this->config->get('config_currency')),
				'pay_costs_raw'      				=> $filter_report == 'currency' ? ($result['currency_value']*$result['payment_cost']) : $result['payment_cost'],
				'ship_costs'      					=> $filter_report == 'currency' ? $this->currency->format('-' . ($result['shipping_cost']), $result['currency_code'], $result['currency_value']) : $this->currency->format('-' . ($result['shipping_cost']), $this->config->get('config_currency')),
				'ship_costs_raw'      				=> $filter_report == 'currency' ? ($result['currency_value']*$result['shipping_cost']) : $result['shipping_cost'],
				'extra_costs'      					=> $filter_report == 'currency' ? $this->currency->format('-' . ($result['extra_cost']), $result['currency_code'], $result['currency_value']) : $this->currency->format('-' . ($result['extra_cost']), $this->config->get('config_currency')),
				'extra_costs_raw'      				=> $filter_report == 'currency' ? ($result['currency_value']*$result['extra_cost']) : $result['extra_cost'],
				'return_costs'      				=> $filter_report == 'currency' ? $this->currency->format('-' . ($result['return_cost']), $result['currency_code'], $result['currency_value']) : $this->currency->format('-' . ($result['return_cost']), $this->config->get('config_currency')),
				'return_costs_raw'      			=> $filter_report == 'currency' ? ($result['currency_value']*$result['return_cost']) : $result['return_cost'],
				'ship_balance'      				=> $filter_report == 'currency' ? $this->currency->format($result['shipping']-$result['shipping_cost'], $result['currency_code'], $result['currency_value']) : $this->currency->format($result['shipping']-$result['shipping_cost'], $this->config->get('config_currency')),
				'ship_balance_raw'      			=> $filter_report == 'currency' ? ($result['currency_value']*($result['shipping']-$result['shipping_cost'])) : $result['shipping']-$result['shipping_cost'],
				'total_costs'      					=> $filter_report == 'currency' ? $this->currency->format('-' . ($total_costs), $result['currency_code'], $result['currency_value']) : $this->currency->format('-' . ($total_costs), $this->config->get('config_currency')),
				'total_costs_raw'      				=> $filter_report == 'currency' ? ($result['currency_value']*$total_costs) : $total_costs,
				'total_profit'      				=> $filter_report == 'currency' ? $this->currency->format(($total_sales-$total_costs), $result['currency_code'], $result['currency_value']) : $this->currency->format(($total_sales-$total_costs), $this->config->get('config_currency')),
				'total_profit_raw'      			=> $filter_report == 'currency' ? ($result['currency_value']*($total_sales-$total_costs)) : $total_sales-$total_costs,
				'total_profit_prc' 					=> $total_profit_prc,
				'gsales'      						=> $filter_report == 'currency' ? round($result['currency_value']*$total_sales, 2) : round($total_sales, 2),
				'gcosts'      						=> $filter_report == 'currency' ? round($result['currency_value']*$total_costs, 2) : round($total_costs, 2),
				'gprofit'      						=> $filter_report == 'currency' ? round($result['currency_value']*($total_sales-$total_costs), 2) : round($total_sales-$total_costs, 2),
				'orders_total'      				=> $filter_report == 'currency' ? '' : $orders_total,	
				'customers_total'      				=> $filter_report == 'currency' ? '' : $customers_total,
				'products_total'      				=> $filter_report == 'currency' ? '' : $products_total,
				'sub_total_total'      				=> $filter_report == 'currency' ? '' : $this->currency->format($sub_total_total, $this->config->get('config_currency')),
				'handling_total'      				=> $filter_report == 'currency' ? '' : $this->currency->format($handling_total, $this->config->get('config_currency')),
				'low_order_fee_total'      			=> $filter_report == 'currency' ? '' : $this->currency->format($low_order_fee_total, $this->config->get('config_currency')),
				'shipping_total'      				=> $filter_report == 'currency' ? '' : $this->currency->format($shipping_total, $this->config->get('config_currency')),
				'reward_total'      				=> $filter_report == 'currency' ? '' : $this->currency->format($reward_total, $this->config->get('config_currency')),	
				'earned_reward_points_total'   	   	=> $filter_report == 'currency' ? '' : ($earned_reward_points_total ? $earned_reward_points_total : 0),
				'used_reward_points_total'   	   	=> $filter_report == 'currency' ? '' : ($used_reward_points_total ? $used_reward_points_total : 0),
				'coupon_total'      				=> $filter_report == 'currency' ? '' : $this->currency->format($coupon_total, $this->config->get('config_currency')),
				'taxes_total'      					=> $filter_report == 'currency' ? '' : $this->currency->format($taxes_total, $this->config->get('config_currency')),
				'credit_total'      				=> $filter_report == 'currency' ? '' : $this->currency->format($credit_total, $this->config->get('config_currency')),
				'voucher_total'      				=> $filter_report == 'currency' ? '' : $this->currency->format($voucher_total, $this->config->get('config_currency')),
				'discount_total'      				=> $filter_report == 'currency' ? '' : $this->currency->format($discount_total, $this->config->get('config_currency')),
				'total_total'      					=> $filter_report == 'currency' ? '' : $this->currency->format($total_total, $this->config->get('config_currency')),
				'aov_total' 						=> $filter_report == 'currency' ? '' : $this->currency->format($total_total / $orders_total, $this->config->get('config_currency')),
				'refunds_total'      				=> $filter_report == 'currency' ? '' : $this->currency->format($data['advsop_formula_6'] ? '-' . $refunds_total : $refunds_total, $this->config->get('config_currency')),
				'total_sales_total'      			=> $filter_report == 'currency' ? '' : $this->currency->format($total_sales_total, $this->config->get('config_currency')),
				'total_sales_total_raw'      		=> $filter_report == 'currency' ? '' : $total_sales_total,
				'prod_costs_total'      			=> $filter_report == 'currency' ? '' : $this->currency->format('-' . $prod_costs_total, $this->config->get('config_currency')),
				'commission_total'      			=> $filter_report == 'currency' ? '' : $this->currency->format('-' . $commission_total, $this->config->get('config_currency')),	
				'pay_costs_total'      				=> $filter_report == 'currency' ? '' : $this->currency->format('-' . $pay_costs_total, $this->config->get('config_currency')),
				'ship_costs_total'      			=> $filter_report == 'currency' ? '' : $this->currency->format('-' . $ship_costs_total, $this->config->get('config_currency')),
				'extra_costs_total'      			=> $filter_report == 'currency' ? '' : $this->currency->format('-' . $extra_costs_total, $this->config->get('config_currency')),
				'return_costs_total'      			=> $filter_report == 'currency' ? '' : $this->currency->format('-' . $return_costs_total, $this->config->get('config_currency')),
				'ship_balance_total'      			=> $filter_report == 'currency' ? '' : $this->currency->format($shipping_total-$ship_costs_total, $this->config->get('config_currency')),
				'total_costs_total'      			=> $filter_report == 'currency' ? '' : $this->currency->format('-' . $total_costs_total, $this->config->get('config_currency')),
				'total_costs_total_raw'      		=> $filter_report == 'currency' ? '' : $total_costs_total,
				'total_profit_total'      			=> $filter_report == 'currency' ? '' : $this->currency->format($total_sales_total-$total_costs_total, $this->config->get('config_currency')),
				'total_profit_total_raw'      		=> $filter_report == 'currency' ? '' : $total_sales_total-$total_costs_total,
				'total_profit_total_prc' 			=> $filter_report == 'currency' ? '' : $total_profit_total_prc,		
			) + $details;
		  }

		} else {

			// Options
			if (in_array('all_prod_option', $advsop_settings_all_columns)) {	

			$option_data = array();
			$options = $this->model_extension_report_adv_sales_profit->getOrderOptions($result['order_product_id']);

			foreach ($options as $option) {
				if ($option['type'] != 'checkbox' && $option['type'] != 'text' && $option['type'] != 'textarea' && $option['type'] != 'file' && $option['type'] != 'image' && $option['type'] != 'date' && $option['type'] != 'datetime' && $option['type'] != 'time') {
					$option_data[] = array(
						'name'  	=> $option['name'],
						'value' 	=> $option['value'],
						'type'  	=> $option['type']
					);
				}
			}

			} else {
			
			$option_data = '';
			
			}
			
			// Taxes
			$tax_data = array();
			$taxes = $this->model_extension_report_adv_sales_profit->getOrderTaxesDivided($result['order_id']);

			foreach ($taxes as $tax) {
				$tax_data[] = array(
						'title'  	=> $tax['title'],
						'value' 	=> $tax['value']
				);
			}
			
			// Custom Fields
			if (in_array('all_custom_fields', $advsop_settings_all_columns)) {		

			$this->load->model('customer/custom_field');

			$account_custom_fields = array();

			$filter_data = array(
				'sort'  => 'cf.sort_order',
				'order' => 'ASC',
			);

			$custom_fields = $this->model_customer_custom_field->getCustomFields($filter_data);
			$data['custom_fields'] = $this->model_extension_report_adv_sales_profit->getCustomFieldsNames($filter_data);			
			$order_custom_field = json_decode($result['custom_field'], true);

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'account' && isset($order_custom_field[$custom_field['custom_field_id']])) {
					if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
						$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_custom_field[$custom_field['custom_field_id']]);

						if ($custom_field_value_info) {
							$account_custom_fields[] = array(
								'name'  => $custom_field['name'],
								'value' => $custom_field_value_info['name']
							);
						}
					}

					if ($custom_field['type'] == 'checkbox' && is_array($order_custom_field[$custom_field['custom_field_id']])) {
						foreach ($order_custom_field[$custom_field['custom_field_id']] as $custom_field_value_id) {
							$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

							if ($custom_field_value_info) {
								$account_custom_fields[] = array(
									'name'  => $custom_field['name'],
									'value' => $custom_field_value_info['name']
								);
							}
						}
					}

					if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
						$account_custom_fields[] = array(
							'name'  => $custom_field['name'],
							'value' => $order_custom_field[$custom_field['custom_field_id']]
						);
					}
				}
			}

			} else {
			
			$account_custom_fields = '';
			
			}
			
			$profit_margin = $result['product_sales'] > 0 ? sprintf('%0.2f', 100 * (($result['product_sales']-$result['product_cost'])/$result['product_sales'])) . '%' : '0%';
			$profit_markup = $result['product_cost'] > 0 ? sprintf('%0.2f', 100 * (($result['product_sales']-$result['product_cost'])/$result['product_cost'])) . '%' : '0%';
			$order_profit_prc = ($result['order_sales']) > 0 ? sprintf('%0.2f', 100 * (($result['order_sales']-$result['order_costs']) / $result['order_sales'])) . '%' : '0%';
			
			$orders[] = array(
				'order_id'   					=> $result['order_id'],	
				'order_id_link'     			=> $result['order_id_link'], 
				'date_added'    				=> date($data['advsop_date_format'] == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($result['date_added'])),
				'invoice'     					=> $result['invoice_prefix'] . $result['invoice_no'],
				'name'   						=> $result['firstname'] . ' ' . $result['lastname'],
				'email'   						=> $result['email'],
				'cust_group'   					=> $result['cust_group'],
				'product_id'  					=> $result['product_id'],	
				'product_id_link'  				=> $result['product_id_link'],	
				'product_sku'  					=> $result['product_sku'],
				'product_model'  				=> $result['product_model'],				
				'product_name'  				=> $result['product_name'],	
				'product_options'  				=> $result['product_options'], 
				'product_option'   		   		=> $option_data,				
				'product_attributes'  			=> $result['product_attributes'],
				'product_category'  			=> $result['product_category'],
				'product_manu'  				=> $result['product_manu'],				
				'product_supplier'  			=> $result['product_supplier'],
				'currency_code' 				=> $result['currency_code'],
				'product_price'        			=> $this->currency->format($result['product_price'], $result['currency_code'], $result['currency_value']),
				'product_price_raw'  			=> $result['product_price_raw'],
				'product_quantity'  			=> $result['product_quantity'],
				'product_total_excl_vat'  		=> $this->currency->format($result['product_total_excl_vat'], $result['currency_code'], $result['currency_value']),
				'product_total_excl_vat_raw'  	=> $result['product_total_excl_vat_raw'],
				'product_tax'  					=> $this->currency->format($result['product_tax'], $result['currency_code'], $result['currency_value']),
				'product_tax_raw'  				=> $result['product_tax_raw'],
				'product_total_incl_vat'  		=> $this->currency->format($result['product_total_incl_vat'], $result['currency_code'], $result['currency_value']),
				'product_total_incl_vat_raw'  	=> $result['product_total_incl_vat_raw'],			
				'product_discount'  			=> $this->currency->format($result['product_discount'], $result['currency_code'], $result['currency_value']),
				'product_discount_raw'  		=> $result['product_discount_raw'],				
				'product_qty_refund'  			=> $result['product_qty_refund'],
				'product_refund'  				=> $this->currency->format($data['advsop_formula_6'] ? '-' . $result['product_refund'] : $result['product_refund'], $result['currency_code'], $result['currency_value']),
				'product_refund_raw'  			=> $result['product_refund_raw'],
				'product_reward_points'      	=> $result['product_reward_points'] ? $result['product_reward_points'] : 0,
				'product_sales'  				=> $this->currency->format($result['product_sales'], $result['currency_code'], $result['currency_value']),
				'product_sales_raw'  			=> $result['product_sales_raw'],				
				'product_cost'   				=> $this->currency->format('-' . $result['product_cost'], $result['currency_code'], $result['currency_value']),
				'product_cost_raw'   			=> $result['product_cost_raw'],
				'product_profit'   				=> $this->currency->format(($result['product_sales']-$result['product_cost']), $result['currency_code'], $result['currency_value']),
				'product_profit_raw'   			=> $result['product_sales_raw']-$result['product_cost_raw'],
				'product_profit_margin' 		=> $profit_margin,
				'product_profit_markup' 		=> $profit_markup,
				'order_sub_total'  				=> $this->currency->format($result['order_sub_total'], $result['currency_code'], $result['currency_value']),
				'order_sub_total_raw'  			=> $result['order_sub_total_raw'],
				'order_handling'  				=> $this->currency->format($result['order_handling'], $result['currency_code'], $result['currency_value']),
				'order_handling_raw'  			=> $result['order_handling_raw'],
				'order_low_order_fee'  			=> $this->currency->format($result['order_low_order_fee'], $result['currency_code'], $result['currency_value']),
				'order_low_order_fee_raw'		=> $result['order_low_order_fee_raw'],
				'order_shipping'  				=> $this->currency->format($result['order_shipping'], $result['currency_code'], $result['currency_value']),
				'order_shipping_raw'  			=> $result['order_shipping_raw'],
				'order_reward'  				=> $this->currency->format($result['order_reward'], $result['currency_code'], $result['currency_value']),
				'order_reward_raw'  			=> $result['order_reward_raw'],
				'order_earned_points'      		=> $result['order_earned_points'] ? $result['order_earned_points'] : 0,
				'order_used_points'      		=> $result['order_used_points'] ? $result['order_used_points'] : 0,				
				'order_coupon'  				=> $this->currency->format($result['order_coupon'], $result['currency_code'], $result['currency_value']),
				'order_coupon_raw'  			=> $result['order_coupon_raw'],
				'order_coupon_name'  			=> $result['order_coupon_name'],
				'order_coupon_code'  			=> $result['order_coupon_code'],
				'order_tax'  					=> $this->currency->format($result['order_tax'], $result['currency_code'], $result['currency_value']),
				'order_tax_raw'  				=> $result['order_tax_raw'],
				'order_taxes'  					=> $tax_data,
				'order_credit'  				=> $this->currency->format($result['order_credit'], $result['currency_code'], $result['currency_value']),
				'order_credit_raw'  			=> $result['order_credit_raw'],
				'order_voucher'  				=> $this->currency->format($result['order_voucher'], $result['currency_code'], $result['currency_value']),
				'order_voucher_raw'  			=> $result['order_voucher_raw'],
				'order_voucher_code'  			=> $result['order_voucher_code'],
				'order_discount'      			=> $this->currency->format($result['order_discount'], $result['currency_code'], $result['currency_value']),
				'order_discount_raw'      		=> $result['order_discount_raw'],					
				'order_value'  					=> $this->currency->format($result['order_value'], $result['currency_code'], $result['currency_value']),
				'order_value_raw'  				=> $result['order_value_raw'],
				'order_refund'      			=> $this->currency->format($data['advsop_formula_6'] ? '-' . $result['order_refund'] : $result['order_refund'], $result['currency_code'], $result['currency_value']),
				'order_refund_raw'      		=> $result['order_refund_raw'],
				'order_sales'   				=> $this->currency->format($result['order_sales'], $result['currency_code'], $result['currency_value']),
				'order_sales_raw'   			=> $result['order_sales_raw'],
				'order_product_costs'   		=> $this->currency->format('-' . ($result['order_product_costs']-($data['advsop_formula_7'] ? $result['order_product_costs_returns'] : 0)), $result['currency_code'], $result['currency_value']),
				'order_product_costs_raw'   	=> $result['order_product_costs_raw']-($data['advsop_formula_7'] ? $result['order_product_costs_returns_raw'] : 0),
				'order_commission'   			=> $this->currency->format('-' . ($result['order_commission']), $result['currency_code'], $result['currency_value']),
				'order_commission_raw'   		=> $result['order_commission_raw'],
				'order_payment_cost'   			=> $this->currency->format('-' . ($result['order_payment_cost']), $result['currency_code'], $result['currency_value']),
				'order_payment_cost_raw'   		=> $result['order_payment_cost_raw'],
				'order_shipping_cost'   		=> $this->currency->format('-' . ($result['order_shipping_cost']), $result['currency_code'], $result['currency_value']),
				'order_shipping_cost_raw'   	=> $result['order_shipping_cost_raw'],
				'order_extra_cost'   			=> $this->currency->format('-' . ($result['order_extra_cost']), $result['currency_code'], $result['currency_value']),
				'order_extra_cost_raw'   		=> $result['order_extra_cost_raw'],
				'order_return_cost'   			=> $this->currency->format('-' . ($result['order_return_cost']), $result['currency_code'], $result['currency_value']),
				'order_return_cost_raw'   		=> $result['order_return_cost_raw'],
				'order_ship_balance'      		=> $this->currency->format($result['order_shipping']-$result['order_shipping_cost'], $result['currency_code'], $result['currency_value']),
				'order_ship_balance_raw'      	=> $result['order_shipping_raw']-$result['order_shipping_cost_raw'],				
				'order_costs'   				=> $this->currency->format('-' . ($result['order_costs']), $result['currency_code'], $result['currency_value']),
				'order_costs_raw'   			=> $result['order_costs_raw'],
				'order_profit'   				=> $this->currency->format(($result['order_sales']-$result['order_costs']), $result['currency_code'], $result['currency_value']),
				'order_profit_raw'   			=> $result['order_sales_raw']-$result['order_costs_raw'],
				'order_profit_prc' 				=> $order_profit_prc,
				'shipping_method' 				=> preg_replace('~\(.*?\)~', '', $result['shipping_method']),
				'payment_method'  				=> preg_replace('~\(.*?\)~', '', $result['payment_method']),
				'order_status'  				=> $result['order_status'],
				'store_name'      				=> $result['store_name'],	
				'order_campaign_name'  			=> $result['order_campaign_name'],
				'order_campaign_code'  			=> $result['order_campaign_code'],
				'customer_id' 					=> $result['customer_id'],	
				'customer_id_link' 				=> $result['customer_id_link'],	
				'custom_field' 					=> $result['custom_field'],	
				'custom_fields' 				=> $account_custom_fields,				
				'payment_firstname' 			=> $result['payment_firstname'],
				'payment_lastname' 				=> $result['payment_lastname'],
				'payment_company' 				=> $result['payment_company'],
				'payment_address_1' 			=> $result['payment_address_1'],
				'payment_address_2' 			=> $result['payment_address_2'],
				'payment_city' 					=> $result['payment_city'],
				'payment_zone' 					=> $result['payment_zone'],
				'payment_zone_id' 				=> $result['payment_zone_id'],
				'payment_zone_code' 			=> $result['payment_zone_code'],
				'payment_postcode' 				=> $result['payment_postcode'],	
				'payment_country' 				=> $result['payment_country'],
				'payment_country_id' 			=> $result['payment_country_id'],
				'payment_country_code' 			=> $result['payment_country_code'],
				'telephone' 					=> $result['telephone'],
				'shipping_firstname' 			=> $result['shipping_firstname'],
				'shipping_lastname' 			=> $result['shipping_lastname'],
				'shipping_company' 				=> $result['shipping_company'],
				'shipping_address_1' 			=> $result['shipping_address_1'],
				'shipping_address_2' 			=> $result['shipping_address_2'],
				'shipping_city' 				=> $result['shipping_city'],
				'shipping_zone' 				=> $result['shipping_zone'],
				'shipping_zone_id' 				=> $result['shipping_zone_id'],
				'shipping_zone_code' 			=> $result['shipping_zone_code'],
				'shipping_postcode' 			=> $result['shipping_postcode'],	
				'shipping_country' 				=> $result['shipping_country'],
				'shipping_country_id' 			=> $result['shipping_country_id'],
				'shipping_country_code' 		=> $result['shipping_country_code'],
				'order_weight' 					=> $result['order_weight'] . $result['weight_class'],
				'order_comment' 				=> $result['comment']
			);
			
			}
		
		}

		$data['adv_ext_name'] = $this->language->get('adv_ext_name');
		$data['adv_ext_short_name'] = 'adv_sales_profit';
		$data['adv_ext_version'] = $this->language->get('adv_ext_version');	
		$data['adv_ext_url'] = 'https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=4087';
		
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_no_details'] = $this->language->get('text_no_details');
		$data['text_basic_details'] = $this->language->get('text_basic_details');
		$data['text_all_details'] = $this->language->get('text_all_details');			
		$data['text_no_results'] = $this->language->get('text_no_results');	
		$data['text_all'] = $this->language->get('text_all');
		$data['text_for_export'] = $this->language->get('text_for_export');
		$data['text_select_all'] = $this->language->get('text_select_all');
		$data['text_all_status'] = $this->language->get('text_all_status');		
		$data['text_all_stores'] = $this->language->get('text_all_stores');
		$data['text_all_currencies'] = $this->language->get('text_all_currencies');
		$data['text_all_taxes'] = $this->language->get('text_all_taxes');	
		$data['text_all_tax_classes'] = $this->language->get('text_all_tax_classes');			
		$data['text_all_zones'] = $this->language->get('text_all_zones');			
		$data['text_all_groups'] = $this->language->get('text_all_groups');
		$data['text_all_payment_methods'] = $this->language->get('text_all_payment_methods');	
		$data['text_all_shipping_methods'] = $this->language->get('text_all_shipping_methods');
		$data['text_all_categories'] = $this->language->get('text_all_categories');
		$data['text_all_manufacturers'] = $this->language->get('text_all_manufacturers');
		$data['text_all_suppliers'] = $this->language->get('text_all_suppliers');
		$data['text_all_options'] = $this->language->get('text_all_options');
		$data['text_all_attributes'] = $this->language->get('text_all_attributes');
		$data['text_all_locations'] = $this->language->get('text_all_locations');	
		$data['text_all_coupon_names'] = $this->language->get('text_all_coupon_names');
		$data['text_all_campaign_names'] = $this->language->get('text_all_campaign_names');
		$data['text_selected'] = $this->language->get('text_selected');		
		$data['text_detail'] = $this->language->get('text_detail');
		$data['text_order_list'] = $this->language->get('text_order_list');
		$data['text_filter_total'] = $this->language->get('text_filter_total');
		$data['text_load_save_options'] = $this->language->get('text_load_save_options');
		$data['text_load_save'] = $this->language->get('text_load_save');
		$data['text_export_options'] = $this->language->get('text_export_options');
		$data['text_report_type'] = $this->language->get('text_report_type');
		$data['help_report_type'] = $this->language->get('help_report_type');
		$data['text_export_type'] = $this->language->get('text_export_type');
		$data['text_select_export_type'] = $this->language->get('text_select_export_type');
		$data['text_export_logo_criteria'] = $this->language->get('text_export_logo_criteria');
		$data['text_export_csv_delimiter'] = $this->language->get('text_export_csv_delimiter');	
		$data['text_export_no_details'] = $this->language->get('text_export_no_details');
		$data['text_export_all_details'] = $this->language->get('text_export_all_details');
		$data['text_all_details_products'] = $this->language->get('text_all_details_products');
		$data['text_all_details_orders'] = $this->language->get('text_all_details_orders');		
		$data['text_export_basic_details'] = $this->language->get('text_export_basic_details');	
		$data['text_formula_setting1'] = $this->language->get('text_formula_setting1');
		$data['text_formula_setting2'] = $this->language->get('text_formula_setting2');
		$data['text_formula_setting3'] = $this->language->get('text_formula_setting3');	
		$data['text_formula_setting4'] = $this->language->get('text_formula_setting4');	
		$data['text_formula_setting5'] = $this->language->get('text_formula_setting5');
		$data['text_formula_setting6'] = $this->language->get('text_formula_setting6');	
		$data['text_formula_setting7'] = $this->language->get('text_formula_setting7');	
		$data['text_return_action'] = $this->language->get('text_return_action');
		$data['text_profit_calculation'] = $this->language->get('text_profit_calculation');
		$data['text_formula_explanation'] = $this->language->get('text_formula_explanation');
		$data['text_local_settings'] = $this->language->get('text_local_settings');
		$data['text_check_all'] = $this->language->get('text_check_all');
		$data['text_uncheck_all'] = $this->language->get('text_uncheck_all');
		$data['text_filtering_options'] = $this->language->get('text_filtering_options');
		$data['text_column_settings'] = $this->language->get('text_column_settings');
		$data['text_mv_columns'] = $this->language->get('text_mv_columns');		
		$data['text_bd_columns'] = $this->language->get('text_bd_columns');	
		$data['text_all_columns'] = $this->language->get('text_all_columns');		
		$data['text_export_note'] = $this->language->get('text_export_note');
		$data['text_report_settings'] = $this->language->get('text_report_settings');		
		$data['text_cron_settings'] = $this->language->get('text_cron_settings');
		$data['text_module_settings'] = $this->language->get('text_module_settings');		
		$data['text_format_date'] = $this->language->get('text_format_date');
		$data['text_format_date_eu'] = $this->language->get('text_format_date_eu');
		$data['text_format_date_us'] = $this->language->get('text_format_date_us');
		$data['text_format_hour'] = $this->language->get('text_format_hour');
		$data['text_format_hour_24'] = $this->language->get('text_format_hour_24');
		$data['text_format_hour_12'] = $this->language->get('text_format_hour_12');		
		$data['text_format_week'] = $this->language->get('text_format_week');
		$data['text_format_week_mon_sun'] = $this->language->get('text_format_week_mon_sun');
		$data['text_format_week_sun_sat'] = $this->language->get('text_format_week_sun_sat');
		$data['text_export_notice1'] = $this->language->get('text_export_notice1');
		$data['text_export_notice2'] = $this->language->get('text_export_notice2');		
		$data['text_export_limit'] = $this->language->get('text_export_limit');
		$data['text_cron'] = $this->language->get('text_cron');
		$data['text_cron_1'] = $this->language->get('text_cron_1');
		$data['text_cron_1_text'] = $this->language->get('text_cron_1_text');
		$data['text_cron_2'] = $this->language->get('text_cron_2');
		$data['text_cron_3'] = $this->language->get('text_cron_3');
		$data['text_cron_3_text'] = $this->language->get('text_cron_3_text');
		$data['text_cron_3_option1'] = $this->language->get('text_cron_3_option1');
		$data['text_cron_3_option2'] = $this->language->get('text_cron_3_option2');
		$data['text_save_path'] = $this->language->get('text_save_path');
		$data['help_save_path'] = $this->language->get('help_save_path');
		$data['text_cron_email'] = $this->language->get('text_cron_email');
		$data['help_cron_email'] = $this->language->get('help_cron_email');
		$data['text_cron_file_name'] = $this->language->get('text_cron_file_name');	
		$data['help_cron_file_name'] = $this->language->get('help_cron_file_name');
		$data['text_cron_4'] = $this->language->get('text_cron_4');
		$data['text_cron_user'] = $this->language->get('text_cron_user');
		$data['help_cron_user'] = $this->language->get('help_cron_user');
		$data['text_cron_pass'] = $this->language->get('text_cron_pass');
		$data['help_cron_pass'] = $this->language->get('help_cron_pass');
		$data['text_cron_token'] = $this->language->get('text_cron_token');
		$data['help_cron_token'] = $this->language->get('help_cron_token');
		$data['text_token_generate'] = $this->language->get('text_token_generate');
		$data['text_cron_5'] = $this->language->get('text_cron_5');
		$data['text_cpanel_setting'] = $this->language->get('text_cpanel_setting');
		$data['text_cpanel_setting_note'] = $this->language->get('text_cpanel_setting_note');
		$data['text_cron_command'] = $this->language->get('text_cron_command');
		$data['text_cron_command_empty'] = $this->language->get('text_cron_command_empty');
		$data['text_cron_6'] = $this->language->get('text_cron_6');
		$data['entry_cron_title'] = $this->language->get('entry_cron_title');
		$data['entry_cron_command'] = $this->language->get('entry_cron_command');		
		$data['text_save_cron'] = $this->language->get('text_save_cron');		
		$data['text_pagin_page'] = $this->language->get('text_pagin_page');
		$data['text_pagin_of'] = $this->language->get('text_pagin_of');
		$data['text_pagin_results'] = $this->language->get('text_pagin_results');	
		$data['text_loading'] = $this->language->get('text_loading');
		$data['text_report_date'] = $this->language->get('text_report_date');
		$data['text_report_criteria'] = $this->language->get('text_report_criteria');
		$data['text_email'] = $this->language->get('text_email');
		$data['text_telephone'] = $this->language->get('text_telephone');
		$data['text_report_title'] = $this->language->get('text_report_title');
		$data['text_cron_title'] = $this->language->get('text_cron_title');	
		
		$data['column_date'] = $this->language->get('column_date');
		$data['column_date_start'] = $this->language->get('column_date_start');
		$data['column_date_end'] = $this->language->get('column_date_end');
    	$data['column_orders'] = $this->language->get('column_orders');
    	$data['column_customers'] = $this->language->get('column_customers');		
		$data['column_products'] = $this->language->get('column_products');		
		$data['column_sub_total'] = $this->language->get('column_sub_total');
		$data['column_handling'] = $this->language->get('column_handling');	
		$data['column_loworder'] = $this->language->get('column_loworder');
		$data['column_shipping'] = $this->language->get('column_shipping');
		$data['column_reward'] = $this->language->get('column_reward');
		$data['column_earned_reward_points'] = $this->language->get('column_earned_reward_points');
		$data['column_used_reward_points'] = $this->language->get('column_used_reward_points');
		$data['column_coupon'] = $this->language->get('column_coupon');
		$data['column_coupon_name'] = $this->language->get('column_coupon_name');
		$data['column_coupon_code'] = $this->language->get('column_coupon_code');
		$data['column_campaign_name'] = $this->language->get('column_campaign_name');
		$data['column_campaign_code'] = $this->language->get('column_campaign_code');
		$data['column_coupon_discount'] = $this->language->get('column_coupon_discount');
		$data['column_coupon_type'] = $this->language->get('column_coupon_type');
		$data['column_coupon_status'] = $this->language->get('column_coupon_status');
		$data['column_coupon_date_added'] = $this->language->get('column_coupon_date_added');
		$data['column_coupon_last_used'] = $this->language->get('column_coupon_last_used');
		$data['column_coupon_amount'] = $this->language->get('column_coupon_amount');
		$data['column_taxes'] = $this->language->get('column_taxes');		
		$data['column_credit'] = $this->language->get('column_credit');	
		$data['column_voucher'] = $this->language->get('column_voucher');	
		$data['column_voucher_code'] = $this->language->get('column_voucher_code');
		$data['column_voucher_from_name'] = $this->language->get('column_voucher_from_name');
		$data['column_voucher_from_email'] = $this->language->get('column_voucher_from_email');
		$data['column_voucher_to_name'] = $this->language->get('column_voucher_to_name');
		$data['column_voucher_to_email'] = $this->language->get('column_voucher_to_email');
		$data['column_voucher_theme'] = $this->language->get('column_voucher_theme');
		$data['column_voucher_status'] = $this->language->get('column_voucher_status');
		$data['column_voucher_date_added'] = $this->language->get('column_voucher_date_added');
		$data['column_voucher_amount'] = $this->language->get('column_voucher_amount');
		$data['column_voucher_used_value'] = $this->language->get('column_voucher_used_value');
		$data['column_voucher_remaining_value'] = $this->language->get('column_voucher_remaining_value');
		$data['column_discount'] = $this->language->get('column_discount');
		$data['column_total'] = $this->language->get('column_total');
		$data['column_aov'] = $this->language->get('column_aov');
		$data['column_refunds'] = $this->language->get('column_refunds');
		$data['column_total_sales'] = $this->language->get('column_total_sales');			
		$data['column_product_costs'] = $this->language->get('column_product_costs');		
		$data['column_commission'] = $this->language->get('column_commission');	
		$data['column_payment_cost'] = $this->language->get('column_payment_cost');
		$data['column_shipping_cost'] = $this->language->get('column_shipping_cost');
		$data['column_extra_cost'] = $this->language->get('column_extra_cost');
		$data['column_return_cost'] = $this->language->get('column_return_cost');
		$data['column_shipping_balance'] = $this->language->get('column_shipping_balance');
		$data['column_total_costs'] = $this->language->get('column_total_costs');			
		$data['column_total_profit'] = $this->language->get('column_total_profit');	
		$data['help_total_profit'] = $this->language->get('help_total_profit');
		$data['column_total_profit_prc'] = $this->language->get('column_total_profit_prc');
		$data['help_total_profit_prc'] = $this->language->get('help_total_profit_prc');	
		$data['column_action'] = $this->language->get('column_action');
		$data['column_order_date_added'] = $this->language->get('column_order_date_added');
		$data['column_order_order_id'] = $this->language->get('column_order_order_id');
		$data['column_order_inv_no'] = $this->language->get('column_order_inv_no');
		$data['column_order_customer'] = $this->language->get('column_order_customer');
		$data['column_order_email'] = $this->language->get('column_order_email');		
		$data['column_order_customer_group'] = $this->language->get('column_order_customer_group');		
		$data['column_order_shipping_method'] = $this->language->get('column_order_shipping_method');
		$data['column_order_payment_method'] = $this->language->get('column_order_payment_method');		
		$data['column_order_status'] = $this->language->get('column_order_status');
		$data['column_order_store'] = $this->language->get('column_order_store');
		$data['column_order_currency'] = $this->language->get('column_order_currency');		
		$data['column_order_quantity'] = $this->language->get('column_order_quantity');	
		$data['column_order_sub_total'] = $this->language->get('column_order_sub_total');	
		$data['column_order_shipping'] = $this->language->get('column_order_shipping');
		$data['column_order_tax'] = $this->language->get('column_order_tax');			
		$data['column_order_value'] = $this->language->get('column_order_value');
		$data['column_order_refund'] = $this->language->get('column_order_refund');
		$data['column_order_sales'] = $this->language->get('column_order_sales');
		$data['column_order_prod_costs'] = $this->language->get('column_order_prod_costs');		
		$data['column_order_commission'] = $this->language->get('column_order_commission');	
		$data['column_order_payment_cost'] = $this->language->get('column_order_payment_cost');
		$data['column_order_shipping_cost'] = $this->language->get('column_order_shipping_cost');
		$data['column_order_extra_cost'] = $this->language->get('column_order_extra_cost');
		$data['column_order_return_cost'] = $this->language->get('column_order_return_cost');
		$data['column_order_costs'] = $this->language->get('column_order_costs');
		$data['column_order_profit'] = $this->language->get('column_order_profit');	
		$data['column_order_margin'] = $this->language->get('column_order_margin');	
		$data['column_prod_order_id'] = $this->language->get('column_prod_order_id');		
		$data['column_prod_date_added'] = $this->language->get('column_prod_date_added');			
		$data['column_prod_id'] = $this->language->get('column_prod_id');
		$data['column_prod_sku'] = $this->language->get('column_prod_sku');		
		$data['column_prod_model'] = $this->language->get('column_prod_model');		
		$data['column_prod_name'] = $this->language->get('column_prod_name');	
		$data['column_prod_option'] = $this->language->get('column_prod_option');	
		$data['column_prod_attributes'] = $this->language->get('column_prod_attributes');			
		$data['column_prod_manu'] = $this->language->get('column_prod_manu');
		$data['column_prod_category'] = $this->language->get('column_prod_category');	
		$data['column_prod_supplier'] = $this->language->get('column_prod_supplier');	
		$data['column_prod_currency'] = $this->language->get('column_prod_currency');
		$data['column_prod_price'] = $this->language->get('column_prod_price');
		$data['column_prod_quantity'] = $this->language->get('column_prod_quantity');
		$data['column_prod_total_excl_vat'] = $this->language->get('column_prod_total_excl_vat');
		$data['column_prod_tax'] = $this->language->get('column_prod_tax');
		$data['column_prod_total_incl_vat'] = $this->language->get('column_prod_total_incl_vat');
		$data['column_prod_discount'] = $this->language->get('column_prod_discount');
		$data['column_prod_qty_refunded'] = $this->language->get('column_prod_qty_refunded');
		$data['column_prod_refunded'] = $this->language->get('column_prod_refunded');
		$data['column_prod_reward_points'] = $this->language->get('column_prod_reward_points');
		$data['column_prod_sales'] = $this->language->get('column_prod_sales');
		$data['column_prod_cost'] = $this->language->get('column_prod_cost');	
		$data['column_prod_profit'] = $this->language->get('column_prod_profit');	
		$data['column_prod_profit_margin'] = $this->language->get('column_prod_profit_margin');
		$data['column_prod_profit_markup'] = $this->language->get('column_prod_profit_markup');	
		$data['column_customer_order_id'] = $this->language->get('column_customer_order_id');
		$data['column_customer_date_added'] = $this->language->get('column_customer_date_added');		
		$data['column_customer_cust_id'] = $this->language->get('column_customer_cust_id');
		$data['column_custom_fields'] = $this->language->get('column_custom_fields');
		$data['column_billing_name'] = $this->language->get('column_billing_name');
		$data['column_billing_first_name'] = $this->language->get('column_billing_first_name');
		$data['column_billing_last_name'] = $this->language->get('column_billing_last_name');
		$data['column_billing_company'] = $this->language->get('column_billing_company');
		$data['column_billing_address_1'] = $this->language->get('column_billing_address_1');
		$data['column_billing_address_2'] = $this->language->get('column_billing_address_2');
		$data['column_billing_city'] = $this->language->get('column_billing_city');
		$data['column_billing_zone'] = $this->language->get('column_billing_zone');
		$data['column_billing_zone_id'] = $this->language->get('column_billing_zone_id');
		$data['column_billing_zone_code'] = $this->language->get('column_billing_zone_code');
		$data['column_billing_postcode'] = $this->language->get('column_billing_postcode');		
		$data['column_billing_country'] = $this->language->get('column_billing_country');
		$data['column_billing_country_id'] = $this->language->get('column_billing_country_id');
		$data['column_billing_country_code'] = $this->language->get('column_billing_country_code');
		$data['column_customer_telephone'] = $this->language->get('column_customer_telephone');
		$data['column_shipping_name'] = $this->language->get('column_shipping_name');
		$data['column_shipping_first_name'] = $this->language->get('column_shipping_first_name');
		$data['column_shipping_last_name'] = $this->language->get('column_shipping_last_name');
		$data['column_shipping_company'] = $this->language->get('column_shipping_company');
		$data['column_shipping_address_1'] = $this->language->get('column_shipping_address_1');
		$data['column_shipping_address_2'] = $this->language->get('column_shipping_address_2');
		$data['column_shipping_city'] = $this->language->get('column_shipping_city');
		$data['column_shipping_zone'] = $this->language->get('column_shipping_zone');
		$data['column_shipping_zone_id'] = $this->language->get('column_shipping_zone_id');
		$data['column_shipping_zone_code'] = $this->language->get('column_shipping_zone_code');
		$data['column_shipping_postcode'] = $this->language->get('column_shipping_postcode');		
		$data['column_shipping_country'] = $this->language->get('column_shipping_country');
		$data['column_shipping_country_id'] = $this->language->get('column_shipping_country_id');
		$data['column_shipping_country_code'] = $this->language->get('column_shipping_country_code');
		$data['column_order_weight'] = $this->language->get('column_order_weight');
		$data['column_order_comment'] = $this->language->get('column_order_comment');
		
		$data['column_year'] = $this->language->get('column_year');
		$data['column_quarter'] = $this->language->get('column_quarter');
		$data['column_month'] = $this->language->get('column_month');
		$data['column_day_of_week'] = $this->language->get('column_day_of_week');
		$data['column_hour'] = $this->language->get('column_hour');
		$data['column_store'] = $this->language->get('column_store');
		$data['column_currency'] = $this->language->get('column_currency');
		$data['column_customer_group'] = $this->language->get('column_customer_group');
		$data['column_tax_title'] = $this->language->get('column_tax_title');
		$data['column_tax_rate'] = $this->language->get('column_tax_rate');
		$data['column_tax_total'] = $this->language->get('column_tax_total');
		$data['column_country'] = $this->language->get('column_country');
		$data['column_postcode'] = $this->language->get('column_postcode');
		$data['column_region_state'] = $this->language->get('column_region_state');
		$data['column_city'] = $this->language->get('column_city');
		$data['column_payment_method'] = $this->language->get('column_payment_method');
		$data['column_shipping_method'] = $this->language->get('column_shipping_method');
		
		$data['column_grevenue'] = $this->language->get('column_grevenue');
		$data['column_gexpenses'] = $this->language->get('column_gexpenses');
		$data['column_gprofit'] = $this->language->get('column_gprofit');
			
		$data['entry_order_created'] = $this->language->get('entry_order_created');
		$data['entry_order_abandoned'] = $this->language->get('entry_order_abandoned');
		$data['entry_status_changed'] = $this->language->get('entry_status_changed');	
		$data['entry_date_start'] = $this->language->get('entry_date_start');
		$data['entry_date_end'] = $this->language->get('entry_date_end');
		$data['entry_range'] = $this->language->get('entry_range');	
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_order_id'] = $this->language->get('entry_order_id');
		$data['entry_order_id_from'] = $this->language->get('entry_order_id_from');
		$data['entry_order_id_to'] = $this->language->get('entry_order_id_to');	
		$data['entry_order_value'] = $this->language->get('entry_order_value');
		$data['entry_order_value_min'] = $this->language->get('entry_order_value_min');
		$data['entry_order_value_max'] = $this->language->get('entry_order_value_max');			
		$data['entry_store'] = $this->language->get('entry_store');
		$data['entry_currency'] = $this->language->get('entry_currency');	
		$data['entry_tax'] = $this->language->get('entry_tax');
		$data['entry_tax_classes'] = $this->language->get('entry_tax_classes');		
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');		
		$data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$data['entry_company'] = $this->language->get('entry_company');
		$data['entry_customer_name'] = $this->language->get('entry_customer_name');		
		$data['entry_customer_email'] = $this->language->get('entry_customer_email'); 
		$data['entry_customer_telephone'] = $this->language->get('entry_customer_telephone');
		$data['entry_ip'] = $this->language->get('entry_ip');
		$data['entry_payment_company'] = $this->language->get('entry_payment_company');
		$data['entry_payment_address'] = $this->language->get('entry_payment_address');
		$data['entry_payment_city'] = $this->language->get('entry_payment_city');
		$data['entry_payment_zone'] = $this->language->get('entry_payment_zone');		
		$data['entry_payment_postcode'] = $this->language->get('entry_payment_postcode');
		$data['entry_payment_country'] = $this->language->get('entry_payment_country');		
		$data['entry_payment_method'] = $this->language->get('entry_payment_method');
		$data['entry_shipping_company'] = $this->language->get('entry_shipping_company');
		$data['entry_shipping_address'] = $this->language->get('entry_shipping_address');
		$data['entry_shipping_city'] = $this->language->get('entry_shipping_city');
		$data['entry_shipping_zone'] = $this->language->get('entry_shipping_zone');		
		$data['entry_shipping_postcode'] = $this->language->get('entry_shipping_postcode');
		$data['entry_shipping_country'] = $this->language->get('entry_shipping_country');
		$data['entry_shipping_method'] = $this->language->get('entry_shipping_method');		
		$data['entry_category'] = $this->language->get('entry_category'); 
		$data['entry_manufacturer'] = $this->language->get('entry_manufacturer');
		$data['entry_supplier'] = $this->language->get('entry_supplier');
		$data['entry_sku'] = $this->language->get('entry_sku');
		$data['entry_product'] = $this->language->get('entry_product');
		$data['entry_model'] = $this->language->get('entry_model');
		$data['entry_option'] = $this->language->get('entry_option');
		$data['entry_attributes'] = $this->language->get('entry_attributes');
		$data['entry_location'] = $this->language->get('entry_location');
		$data['entry_coupon_name'] = $this->language->get('entry_coupon_name');
		$data['entry_coupon_code'] = $this->language->get('entry_coupon_code');
		$data['entry_voucher_code'] = $this->language->get('entry_voucher_code');	
		$data['entry_campaign_name'] = $this->language->get('entry_campaign_name');
		$data['entry_campaign_code'] = $this->language->get('entry_campaign_code');	

		$data['entry_report'] = $this->language->get('entry_report');
		$data['entry_group'] = $this->language->get('entry_group');		
		$data['entry_sort_by'] = $this->language->get('entry_sort_by');
		$data['entry_show_details'] = $this->language->get('entry_show_details');	
		$data['entry_limit'] = $this->language->get('entry_limit');	
		
		$data['entry_title'] = $this->language->get('entry_title');	
		$data['entry_link'] = $this->language->get('entry_link');	

		$data['button_filter'] = $this->language->get('button_filter');
		$data['button_clear'] = $this->language->get('button_clear');
		$data['button_load_save'] = $this->language->get('button_load_save');
		$data['button_load'] = $this->language->get('button_load');
		$data['button_add_report'] = $this->language->get('button_add_report');		
		$data['button_remove'] = $this->language->get('button_remove');
		$data['button_filters'] = $this->language->get('button_filters');
		$data['button_chart'] = $this->language->get('button_chart');		
		$data['button_export'] = $this->language->get('button_export');
		$data['button_settings'] = $this->language->get('button_settings');
		$data['button_documentation'] = $this->language->get('button_documentation');
		$data['button_close'] = $this->language->get('button_close');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_expand'] = $this->language->get('button_expand');
		$data['button_collapse'] = $this->language->get('button_collapse');		
		
		$data['heading_title'] = $this->language->get('heading_title');
		$data['heading_version'] = $this->language->get('heading_version');		
		
		$data['user_token'] = $this->session->data['user_token'];

		$url = '';

		if (isset($this->request->get['filter_date_start'])) {
			$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		}

		if (isset($this->request->get['filter_date_end'])) {
			$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		}

		if (isset($this->request->get['filter_range'])) {
			$url .= '&filter_range=' . $this->request->get['filter_range'];
		}

		if (isset($this->request->get['filter_report'])) {
			$url .= '&filter_report=' . $this->request->get['filter_report'];
		}

		if (isset($this->request->get['filter_group'])) {
			$url .= '&filter_group=' . $this->request->get['filter_group'];
		}
		
		if (isset($this->request->get['filter_sort'])) {
			$url .= '&filter_sort=' . $this->request->get['filter_sort'];
		}

		if (isset($this->request->get['filter_order'])) {
			$url .= '&filter_order=' . $this->request->get['filter_order'];
		}
		
		if (isset($this->request->get['filter_details'])) {
			$url .= '&filter_details=' . $this->request->get['filter_details'];
		}
		
		if (isset($this->request->get['filter_limit'])) {
			$url .= '&filter_limit=' . $this->request->get['filter_limit'];
		}

		if (isset($this->request->get['filter_status_date_start'])) {
			$url .= '&filter_status_date_start=' . $this->request->get['filter_status_date_start'];
		}
		
		if (isset($this->request->get['filter_status_date_end'])) {
			$url .= '&filter_status_date_end=' . $this->request->get['filter_status_date_end'];
		}
		
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
		
		if (isset($this->request->get['filter_order_id_from'])) {
			$url .= '&filter_order_id_from=' . $this->request->get['filter_order_id_from'];
		}
		
		if (isset($this->request->get['filter_order_id_to'])) {
			$url .= '&filter_order_id_to=' . $this->request->get['filter_order_id_to'];
		}

		if (isset($this->request->get['filter_order_value_min'])) {
			$url .= '&filter_order_value_min=' . $this->request->get['filter_order_value_min'];
		}
		
		if (isset($this->request->get['filter_order_value_max'])) {
			$url .= '&filter_order_value_max=' . $this->request->get['filter_order_value_max'];
		}
		
		if (isset($this->request->get['filter_store_id'])) {
			$url .= '&filter_store_id=' . $this->request->get['filter_store_id'];
		}
		
		if (isset($this->request->get['filter_currency'])) {
			$url .= '&filter_currency=' . $this->request->get['filter_currency'];
		}
		
		if (isset($this->request->get['filter_taxes'])) {
			$url .= '&filter_taxes=' . $this->request->get['filter_taxes'];
		}
		
		if (isset($this->request->get['filter_tax_classes'])) {
			$url .= '&filter_tax_classes=' . $this->request->get['filter_tax_classes'];
		}
		
		if (isset($this->request->get['filter_geo_zones'])) {
			$url .= '&filter_geo_zones=' . $this->request->get['filter_geo_zones'];
		}
		
		if (isset($this->request->get['filter_customer_group_id'])) {
			$url .= '&filter_customer_group_id=' . $this->request->get['filter_customer_group_id'];
		}
		
		if (isset($this->request->get['filter_customer_name'])) {
			$url .= '&filter_customer_name=' . urlencode(html_entity_decode($this->request->get['filter_customer_name'], ENT_QUOTES, 'UTF-8'));
		}		

		if (isset($this->request->get['filter_customer_email'])) {
			$url .= '&filter_customer_email=' . urlencode(html_entity_decode($this->request->get['filter_customer_email'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_customer_telephone'])) {
			$url .= '&filter_customer_telephone=' . urlencode(html_entity_decode($this->request->get['filter_customer_telephone'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_ip'])) {
			$url .= '&filter_ip=' . urlencode(html_entity_decode($this->request->get['filter_ip'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_company'])) {
			$url .= '&filter_payment_company=' . urlencode(html_entity_decode($this->request->get['filter_payment_company'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_address'])) {
			$url .= '&filter_payment_address=' . urlencode(html_entity_decode($this->request->get['filter_payment_address'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_city'])) {
			$url .= '&filter_payment_city=' . urlencode(html_entity_decode($this->request->get['filter_payment_city'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_zone'])) {
			$url .= '&filter_payment_zone=' . urlencode(html_entity_decode($this->request->get['filter_payment_zone'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_postcode'])) {
			$url .= '&filter_payment_postcode=' . urlencode(html_entity_decode($this->request->get['filter_payment_postcode'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_country'])) {
			$url .= '&filter_payment_country=' . urlencode(html_entity_decode($this->request->get['filter_payment_country'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_payment_method'])) {
			$url .= '&filter_payment_method=' . $this->request->get['filter_payment_method'];
		}	
		
		if (isset($this->request->get['filter_shipping_company'])) {
			$url .= '&filter_shipping_company=' . urlencode(html_entity_decode($this->request->get['filter_shipping_company'], ENT_QUOTES, 'UTF-8'));
		}	

		if (isset($this->request->get['filter_shipping_address'])) {
			$url .= '&filter_shipping_address=' . urlencode(html_entity_decode($this->request->get['filter_shipping_address'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_shipping_city'])) {
			$url .= '&filter_shipping_city=' . urlencode(html_entity_decode($this->request->get['filter_shipping_city'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_shipping_zone'])) {
			$url .= '&filter_shipping_zone=' . urlencode(html_entity_decode($this->request->get['filter_shipping_zone'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_shipping_postcode'])) {
			$url .= '&filter_shipping_postcode=' . urlencode(html_entity_decode($this->request->get['filter_shipping_postcode'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_shipping_country'])) {
			$url .= '&filter_shipping_country=' . urlencode(html_entity_decode($this->request->get['filter_shipping_country'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_shipping_method'])) {
			$url .= '&filter_shipping_method=' . $this->request->get['filter_shipping_method'];
		}	
		
		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}	
		
		if (isset($this->request->get['filter_manufacturer'])) {
			$url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
		}	

		if (isset($this->request->get['filter_supplier'])) {
			$url .= '&filter_supplier=' . $this->request->get['filter_supplier'];
		}
		
		if (isset($this->request->get['filter_sku'])) {
			$url .= '&filter_sku=' . urlencode(html_entity_decode($this->request->get['filter_sku'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_product_name'])) {
			$url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_option'])) {
			$url .= '&filter_option=' . $this->request->get['filter_option'];
		}	
		
		if (isset($this->request->get['filter_attribute'])) {
			$url .= '&filter_attribute=' . $this->request->get['filter_attribute'];
		}			

		if (isset($this->request->get['filter_location'])) {
			$url .= '&filter_location=' . $this->request->get['filter_location'];
		}	
		
		if (isset($this->request->get['filter_coupon_name'])) {
			$url .= '&filter_coupon_name=' . $this->request->get['filter_coupon_name'];
		}	
		
		if (isset($this->request->get['filter_coupon_code'])) {
			$url .= '&filter_coupon_code=' . urlencode(html_entity_decode($this->request->get['filter_coupon_code'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (isset($this->request->get['filter_voucher_code'])) {
			$url .= '&filter_voucher_code=' . urlencode(html_entity_decode($this->request->get['filter_voucher_code'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_campaign_name'])) {
			$url .= '&filter_campaign_name=' . $this->request->get['filter_campaign_name'];
		}	
		
		if (isset($this->request->get['filter_campaign_code'])) {
			$url .= '&filter_campaign_code=' . urlencode(html_entity_decode($this->request->get['filter_campaign_code'], ENT_QUOTES, 'UTF-8'));
		}	
		
		if (!isset($_GET['cron'])) {
			$total > 0 ? $data['orders'] = $orders : '';
			file_put_contents('sop_json_array.txt', json_encode($data['orders']));
		}
		
		$data['user'] = $this->user->getId();

		$data['report_types'][] = array(
			'name'			=> $this->language->get('text_export_no_details'),
			'type'			=> 'export_no_details',
			'icon'			=> 'fa fa-list-alt'
		);
		$data['report_types'][] = array(
			'name'			=> $this->language->get('text_export_basic_details'),
			'type'			=> 'export_basic_details',
			'icon'			=> 'fa fa-list-alt'
		);
		$data['report_types'][] = array(
			'name'			=> $this->language->get('text_export_all_details')." ".$this->language->get('text_all_details_products'),
			'type' 			=> 'export_all_details_products',
			'icon'			=> 'fa fa-list-alt'
		);
		$data['report_types'][] = array(
			'name'			=> $this->language->get('text_export_all_details')." ".$this->language->get('text_all_details_orders'),
			'type' 			=> 'export_all_details_orders',
			'icon'			=> 'fa fa-list-alt'
		);

		if (isset($this->session->data['report_type'])) {
			$data['report_type'] = $this->session->data['report_type'];
		} else {
			$data['report_type'] = 'export_no_details';
		}

		$data['export_types'][] = array(
			'name'			=> $this->language->get('text_export_xlsx'),
			'type'			=> 'export_xlsx',
			'icon'			=> 'icon-green fa fa-file-excel-o'
		);
		$data['export_types'][] = array(
			'name'			=> $this->language->get('text_export_xls'),
			'type'			=> 'export_xls',
			'icon'			=> 'icon-green fa fa-file-excel-o'
		);
		$data['export_types'][] = array(
			'name'			=> $this->language->get('text_export_csv'),
			'type'			=> 'export_csv',
			'icon'			=> 'icon-dark-green fa fa-file-text-o'
		);		
		$data['export_types'][] = array(
			'name'			=> $this->language->get('text_export_pdf'),
			'type'			=> 'export_pdf',
			'icon'			=> 'icon-red fa fa-file-pdf-o'
		);
		$data['export_types'][] = array(
			'name'			=> $this->language->get('text_export_html'),
			'type'			=> 'export_html',
			'icon'			=> 'icon-black fa fa-file-text-o'
		);
		
		if (isset($this->session->data['export_type'])) {
			$data['export_type'] = $this->session->data['export_type'];
		} else {
			$data['export_type'] = '';
		}		
		
		if (isset($this->session->data['export_logo_criteria'])) {
			$data['export_logo_criteria'] = $this->session->data['export_logo_criteria'];
		} else {
			$data['export_logo_criteria'] = 0;
		}			

		$data['export_csv_delimiters'][] = array(
			'name'			=> $this->language->get('text_csv_delimiter_comma'),
			'type'			=> 'comma',
		);
		$data['export_csv_delimiters'][] = array(
			'name'			=> $this->language->get('text_csv_delimiter_semi'),
			'type'			=> 'semi',
		);
		$data['export_csv_delimiters'][] = array(
			'name'			=> $this->language->get('text_csv_delimiter_tab'),
			'type'			=> 'tab',
		);
		
		if (isset($this->session->data['export_csv_delimiter'])) {
			$data['export_csv_delimiter'] = $this->session->data['export_csv_delimiter'];
		} else {
			$data['export_csv_delimiter'] = 'comma';
		}
		
		if (isset($_GET['cron'])) {
			$cron_settings = $this->config->get('advsop' . $this->user->getId() . 'cron_setting') !== null ? unserialize($this->config->get('advsop' . $this->user->getId() . 'cron_setting')) : '';
			foreach ($cron_settings as $cron_setting) {
				if (isset($_GET['cron_id']) && ($_GET['cron_id'] == $cron_setting['cron_id'])) {
					$data['report_type'] = $cron_setting['cron_report_type'];
					$data['export_type'] = $cron_setting['cron_export_type'];
					$data['export_logo_criteria'] = $cron_setting['cron_export_logo_criteria'];
					$data['export_csv_delimiter'] = $cron_setting['cron_export_csv_delimiter'];
				}
			}
		}
		
		$data['cron_report_type'] = 'export_no_details';
		$data['cron_export_type'] = '';
		$data['cron_export_logo_criteria'] = 0;
		$data['cron_export_csv_delimiter'] = 'comma';
		$data['cron_export_file'] = 'send_to_email';
		$data['cron_file_save_path'] = 'report';
		$data['cron_file_name'] = '';
		$data['cron_email'] = '';
		$data['cron_user_id'] = $this->user->getId();
		$data['root_dir'] = dirname(DIR_APPLICATION) . '/';
		$data['dir_sep'] = '/';
		
		$data['filters'] = array(
			'store'						=> substr($this->language->get('entry_store'),0,-1),			
			'currency'					=> substr($this->language->get('entry_currency'),0,-1),			
			'tax'						=> substr($this->language->get('entry_tax'),0,-1),			
			'tax_class'					=> substr($this->language->get('entry_tax_classes'),0,-1),
			'geo_zone'					=> substr($this->language->get('entry_geo_zone'),0,-1),
			'customer_group'			=> substr($this->language->get('entry_customer_group'),0,-1),
			'customer_name'				=> substr($this->language->get('entry_customer_name'),0,-1),
			'customer_email'			=> substr($this->language->get('entry_customer_email'),0,-1),
			'customer_telephone'		=> substr($this->language->get('entry_customer_telephone'),0,-1),
			'ip'						=> substr($this->language->get('entry_ip'),0,-1),
			'payment_company'			=> substr($this->language->get('entry_payment_company'),0,-1),			
			'payment_address'			=> substr($this->language->get('entry_payment_address'),0,-1),	
			'payment_city'				=> substr($this->language->get('entry_payment_city'),0,-1),	
			'payment_zone'				=> substr($this->language->get('entry_payment_zone'),0,-1),	
			'payment_postcode'			=> substr($this->language->get('entry_payment_postcode'),0,-1),	
			'payment_country'			=> substr($this->language->get('entry_payment_country'),0,-1),
			'payment_method'			=> substr($this->language->get('entry_payment_method'),0,-1),
			'shipping_company'			=> substr($this->language->get('entry_shipping_company'),0,-1),
			'shipping_address'			=> substr($this->language->get('entry_shipping_address'),0,-1),
			'shipping_city'				=> substr($this->language->get('entry_shipping_city'),0,-1),
			'shipping_zone'				=> substr($this->language->get('entry_shipping_zone'),0,-1),
			'shipping_postcode'			=> substr($this->language->get('entry_shipping_postcode'),0,-1),
			'shipping_country'			=> substr($this->language->get('entry_shipping_country'),0,-1),
			'shipping_method'			=> substr($this->language->get('entry_shipping_method'),0,-1),
			'category'					=> substr($this->language->get('entry_category'),0,-1),
			'manufacturer'				=> substr($this->language->get('entry_manufacturer'),0,-1),
			'supplier'					=> substr($this->language->get('entry_supplier'),0,-1),
			'product'					=> substr($this->language->get('entry_product'),0,-1),
			'sku'						=> substr($this->language->get('entry_sku'),0,-1),			
			'model'						=> substr($this->language->get('entry_model'),0,-1),
			'option'					=> substr($this->language->get('entry_option'),0,-1),
			'attribute'					=> substr($this->language->get('entry_attributes'),0,-1),
			'location'					=> substr($this->language->get('entry_location'),0,-1),
			'coupon_name'				=> substr($this->language->get('entry_coupon_name'),0,-1),
			'coupon_code'				=> substr($this->language->get('entry_coupon_code'),0,-1),
			'voucher_code'				=> substr($this->language->get('entry_voucher_code'),0,-1),
			'campaign_name'				=> substr($this->language->get('entry_campaign_name'),0,-1),
			'campaign_code'				=> substr($this->language->get('entry_campaign_code'),0,-1)
		);

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_filters')) {
			$data['advsop_settings_filters'] = $this->config->get('advsop' . $this->user->getId() . '_settings_filters');
		} else {
			$data['advsop_settings_filters'] = array('store','currency','tax','tax_class','geo_zone','customer_group','customer_name','customer_email','customer_telephone','payment_company','payment_address','payment_city','payment_zone','payment_postcode','payment_country','payment_method','shipping_company','shipping_address','shipping_city','shipping_zone','shipping_postcode','shipping_country','shipping_method','category','manufacturer','supplier','product','sku','model');
		}

		$data['tr_columns'] = array(
			'tr_tax_name'				=> $this->language->get('column_tax_title'),
			'tr_tax_rate'				=> $this->language->get('column_tax_rate'),
			'tr_tax_orders'				=> $this->language->get('column_orders'),
			'tr_tax_total'				=> $this->language->get('column_tax_total')
		);

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_tr_columns')) {
			$data['advsop_settings_tr_columns'] = $this->config->get('advsop' . $this->user->getId() . '_settings_tr_columns');
		} else {
			$data['advsop_settings_tr_columns'] = array_keys($data['tr_columns']);
		}

		$data['cr_columns'] = array(
			'cr_coupon_name'			=> $this->language->get('column_coupon_name'),
			'cr_coupon_code'			=> $this->language->get('column_coupon_code'),
			'cr_coupon_discount'		=> $this->language->get('column_coupon_discount'),
			'cr_coupon_type'			=> $this->language->get('column_coupon_type'),
			'cr_coupon_status'			=> $this->language->get('column_coupon_status'),
			'cr_coupon_date_added'		=> $this->language->get('column_coupon_date_added'),
			'cr_coupon_last_used'		=> $this->language->get('column_coupon_last_used'),
			'cr_coupon_amount'			=> $this->language->get('column_coupon_amount'),
			'cr_coupon_orders'			=> $this->language->get('column_orders'),
			'cr_coupon_total'			=> $this->language->get('column_total')
		);

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_cr_columns')) {
			$data['advsop_settings_cr_columns'] = $this->config->get('advsop' . $this->user->getId() . '_settings_cr_columns');
		} else {
			$data['advsop_settings_cr_columns'] = array_keys($data['cr_columns']);
		}

		$data['vr_columns'] = array(
			'vr_voucher_code'				=> $this->language->get('column_voucher_code'),
			'vr_voucher_from_name'			=> $this->language->get('column_voucher_from_name'),
			'vr_voucher_from_email'			=> $this->language->get('column_voucher_from_email'),
			'vr_voucher_to_name'			=> $this->language->get('column_voucher_to_name'),
			'vr_voucher_to_email'			=> $this->language->get('column_voucher_to_email'),
			'vr_voucher_theme'				=> $this->language->get('column_voucher_theme'),
			'vr_voucher_status'				=> $this->language->get('column_voucher_status'),
			'vr_voucher_date_added'			=> $this->language->get('column_voucher_date_added'),
			'vr_voucher_amount'				=> $this->language->get('column_voucher_amount'),
			'vr_voucher_used_value'			=> $this->language->get('column_voucher_used_value'),
			'vr_voucher_remaining_value'	=> $this->language->get('column_voucher_remaining_value'),
			'vr_voucher_orders'				=> $this->language->get('column_orders'),
			'vr_voucher_total'				=> $this->language->get('column_total')
		);

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_vr_columns')) {
			$data['advsop_settings_vr_columns'] = $this->config->get('advsop' . $this->user->getId() . '_settings_vr_columns');
		} else {
			$data['advsop_settings_vr_columns'] = array_keys($data['vr_columns']);
		}
		
		$data['mv_columns'] = array(
			'mv_orders'					=> $this->language->get('column_orders'),			
			'mv_customers'				=> $this->language->get('column_customers'),			
			'mv_products'				=> $this->language->get('column_products'),			
			'mv_sub_total'				=> $this->language->get('column_sub_total'),
			'mv_handling'				=> $this->language->get('column_handling'),
			'mv_loworder'				=> $this->language->get('column_loworder'),
			'mv_shipping'				=> $this->language->get('column_shipping'),
			'mv_reward'					=> $this->language->get('column_reward'),
			'mv_earned_points'			=> $this->language->get('column_earned_reward_points'),
			'mv_used_points'			=> $this->language->get('column_used_reward_points'),	
			'mv_coupon'					=> $this->language->get('column_coupon'),
			'mv_tax'					=> $this->language->get('column_taxes'),
			'mv_credit'					=> $this->language->get('column_credit'),			
			'mv_voucher'				=> $this->language->get('column_voucher'),
			'mv_discount'				=> $this->language->get('column_discount'),
			'mv_total'					=> $this->language->get('column_total'),
			'mv_aov'					=> $this->language->get('column_aov'),
			'mv_refunds'				=> $this->language->get('column_refunds'),	
			'mv_total_sales'			=> $this->language->get('column_total_sales'),	
			'mv_product_costs'			=> $this->language->get('column_product_costs'),	
			'mv_commission'				=> $this->language->get('column_commission'),
			'mv_payment_costs'			=> $this->language->get('column_payment_cost'),
			'mv_shipping_costs'			=> $this->language->get('column_shipping_cost'),
			'mv_extra_costs'			=> $this->language->get('column_extra_cost'),
			'mv_return_costs'			=> $this->language->get('column_return_cost'),
			'mv_shipping_balance'		=> $this->language->get('column_shipping_balance'),
			'mv_total_costs'			=> $this->language->get('column_total_costs'),
			'mv_total_profit'			=> $this->language->get('column_total_profit'),
			'mv_total_profit_prc'		=> $this->language->get('column_total_profit_prc')
		);

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_mv_columns')) {
			$data['advsop_settings_mv_columns'] = $this->config->get('advsop' . $this->user->getId() . '_settings_mv_columns');
		} else {
			$data['advsop_settings_mv_columns'] = array('mv_orders','mv_customers','mv_products','mv_sub_total','mv_handling','mv_loworder','mv_shipping','mv_reward','mv_coupon','mv_tax','mv_credit','mv_voucher','mv_discount','mv_total','mv_aov','mv_refunds','mv_total_sales','mv_total_costs','mv_total_profit','mv_total_profit_prc');
		}
		
		$data['ol_columns'] = array(
			'ol_order_order_id'			=> $this->language->get('column_order_order_id'),			
			'ol_order_date_added'		=> $this->language->get('column_order_date_added'),			
			'ol_order_inv_no'			=> $this->language->get('column_order_inv_no'),			
			'ol_order_customer'			=> $this->language->get('column_order_customer'),
			'ol_order_email'			=> $this->language->get('column_order_email'),
			'ol_order_customer_group'	=> $this->language->get('column_order_customer_group'),
			'ol_order_shipping_method'	=> $this->language->get('column_order_shipping_method'),
			'ol_order_payment_method'	=> $this->language->get('column_order_payment_method'),
			'ol_order_status'			=> $this->language->get('column_order_status'),
			'ol_order_store'			=> $this->language->get('column_order_store'),
			'ol_order_currency'			=> $this->language->get('column_order_currency'),			
			'ol_order_quantity'			=> $this->language->get('column_order_quantity'),	
			'ol_order_sub_total'		=> $this->language->get('column_order_sub_total'),	
			'ol_order_shipping'			=> $this->language->get('column_order_shipping'),	
			'ol_order_tax'				=> $this->language->get('column_order_tax'),	
			'ol_order_value'			=> $this->language->get('column_order_value'),
			'ol_order_sales'			=> $this->language->get('column_order_sales'),
			'ol_order_costs'			=> $this->language->get('column_order_costs'),
			'ol_order_profit'			=> $this->language->get('column_order_profit'),
			'ol_order_profit_prc'		=> $this->language->get('column_order_margin')
		);

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_ol_columns')) {
			$data['advsop_settings_ol_columns'] = $this->config->get('advsop' . $this->user->getId() . '_settings_ol_columns');
		} else {
			$data['advsop_settings_ol_columns'] = array_keys($data['ol_columns']);
		}
		
		$data['pl_columns'] = array(
			'pl_prod_order_id'			=> $this->language->get('column_prod_order_id'),			
			'pl_prod_date_added'		=> $this->language->get('column_prod_date_added'),										
			'pl_prod_id'				=> $this->language->get('column_prod_id'),
			'pl_prod_sku'				=> $this->language->get('column_prod_sku'),
			'pl_prod_model'				=> $this->language->get('column_prod_model'),
			'pl_prod_name'				=> $this->language->get('column_prod_name'),
			'pl_prod_option'			=> $this->language->get('column_prod_option'),
			'pl_prod_attributes'		=> $this->language->get('column_prod_attributes'),
			'pl_prod_category'			=> $this->language->get('column_prod_category'),				
			'pl_prod_manu'				=> $this->language->get('column_prod_manu'),
			'pl_prod_supplier'			=> $this->language->get('column_prod_supplier'),			
			'pl_prod_currency'			=> $this->language->get('column_prod_currency'),	
			'pl_prod_price'				=> $this->language->get('column_prod_price'),	
			'pl_prod_quantity'			=> $this->language->get('column_prod_quantity'),	
			'pl_prod_total_excl_vat'	=> $this->language->get('column_prod_total_excl_vat'),	
			'pl_prod_tax'				=> $this->language->get('column_prod_tax'),	
			'pl_prod_total_incl_vat'	=> $this->language->get('column_prod_total_incl_vat'),
			'pl_prod_sales'				=> $this->language->get('column_prod_sales'),
			'pl_prod_cost'				=> $this->language->get('column_prod_cost'),
			'pl_prod_profit'			=> $this->language->get('column_prod_profit'),
			'pl_prod_profit_margin'		=> $this->language->get('column_prod_profit_margin'),
			'pl_prod_profit_markup'		=> $this->language->get('column_prod_profit_markup')
		);

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_pl_columns')) {
			$data['advsop_settings_pl_columns'] = $this->config->get('advsop' . $this->user->getId() . '_settings_pl_columns');
		} else {
			$data['advsop_settings_pl_columns'] = array('pl_prod_order_id','pl_prod_date_added','pl_prod_id','pl_prod_sku','pl_prod_model','pl_prod_name','pl_prod_option','pl_prod_currency','pl_prod_price','pl_prod_quantity','pl_prod_total_excl_vat','pl_prod_tax','pl_prod_total_incl_vat','pl_prod_sales','pl_prod_cost','pl_prod_profit','pl_prod_profit_margin','pl_prod_profit_markup');
		}
		
		$data['cl_columns'] = array(
			'cl_customer_order_id'		=> $this->language->get('column_customer_order_id'),			
			'cl_customer_date_added'	=> $this->language->get('column_customer_date_added'),										
			'cl_customer_cust_id'		=> $this->language->get('column_customer_cust_id'),
			'cl_billing_name'			=> strip_tags($this->language->get('column_billing_name')),
			'cl_billing_company'		=> strip_tags($this->language->get('column_billing_company')),
			'cl_billing_address_1'		=> strip_tags($this->language->get('column_billing_address_1')),
			'cl_billing_address_2'		=> strip_tags($this->language->get('column_billing_address_2')),
			'cl_billing_city'			=> strip_tags($this->language->get('column_billing_city')),
			'cl_billing_zone'			=> strip_tags($this->language->get('column_billing_zone')),
			'cl_billing_postcode'		=> strip_tags($this->language->get('column_billing_postcode')),			
			'cl_billing_country'		=> strip_tags($this->language->get('column_billing_country')),
			'cl_customer_telephone'		=> $this->language->get('column_customer_telephone'),
			'cl_shipping_name'			=> strip_tags($this->language->get('column_shipping_name')),	
			'cl_shipping_company'		=> strip_tags($this->language->get('column_shipping_company')),	
			'cl_shipping_address_1'		=> strip_tags($this->language->get('column_shipping_address_1')),	
			'cl_shipping_address_2'		=> strip_tags($this->language->get('column_shipping_address_2')),
			'cl_shipping_city'			=> strip_tags($this->language->get('column_shipping_city')),
			'cl_shipping_zone'			=> strip_tags($this->language->get('column_shipping_zone')),
			'cl_shipping_postcode'		=> strip_tags($this->language->get('column_shipping_postcode')),
			'cl_shipping_country'		=> strip_tags($this->language->get('column_shipping_country'))
		);

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_cl_columns')) {
			$data['advsop_settings_cl_columns'] = $this->config->get('advsop' . $this->user->getId() . '_settings_cl_columns');
		} else {
			$data['advsop_settings_cl_columns'] = array('cl_customer_order_id','cl_customer_date_added','cl_customer_cust_id','cl_billing_name','cl_billing_company','cl_billing_address_1','cl_billing_city','cl_billing_zone','cl_billing_postcode','cl_billing_country','cl_customer_telephone','cl_shipping_name','cl_shipping_company','cl_shipping_address_1','cl_shipping_city','cl_shipping_zone','cl_shipping_postcode','cl_shipping_country');
		}
		
		$data['all_columns'] = array(
			'all_order_inv_no'			=> $this->language->get('column_order_inv_no'),			
			'all_order_customer_name'	=> $this->language->get('column_order_customer'),			
			'all_order_email'			=> $this->language->get('column_order_email'),			
			'all_order_customer_group'	=> $this->language->get('column_order_customer_group'),
			'all_prod_id'				=> $this->language->get('column_prod_id'),
			'all_prod_sku'				=> $this->language->get('column_prod_sku'),
			'all_prod_model'			=> $this->language->get('column_prod_model'),
			'all_prod_name'				=> $this->language->get('column_prod_name'),
			'all_prod_option'			=> $this->language->get('column_prod_option'),
			'all_prod_attributes'		=> $this->language->get('column_prod_attributes'),
			'all_prod_category'			=> $this->language->get('column_prod_category'),			
			'all_prod_manu'				=> $this->language->get('column_prod_manu'),
			'all_prod_supplier'			=> $this->language->get('column_prod_supplier'),
			'all_prod_currency'			=> $this->language->get('column_prod_currency'),
			'all_prod_price'			=> $this->language->get('column_prod_price'),
			'all_prod_quantity'			=> $this->language->get('column_prod_quantity'),
			'all_prod_total_excl_vat'	=> $this->language->get('column_prod_total_excl_vat'),
			'all_prod_tax'				=> $this->language->get('column_prod_tax'),
			'all_prod_total_incl_vat'	=> $this->language->get('column_prod_total_incl_vat'),
			'all_prod_discount'			=> $this->language->get('column_prod_discount'),
			'all_prod_qty_refund'		=> $this->language->get('column_prod_qty_refunded'),
			'all_prod_refund'			=> $this->language->get('column_prod_refunded'),
			'all_prod_reward_points'	=> $this->language->get('column_prod_reward_points'),
			'all_prod_sales'			=> $this->language->get('column_prod_sales'),
			'all_prod_cost'				=> $this->language->get('column_prod_cost'),
			'all_prod_profit'			=> $this->language->get('column_prod_profit'),
			'all_prod_profit_margin'	=> $this->language->get('column_prod_profit_margin'),
			'all_prod_profit_markup'	=> $this->language->get('column_prod_profit_markup'),
			'all_sub_total'				=> $this->language->get('column_sub_total'),
			'all_handling'				=> $this->language->get('column_handling'),
			'all_loworder'				=> $this->language->get('column_loworder'),
			'all_shipping'				=> $this->language->get('column_shipping'),
			'all_reward'				=> $this->language->get('column_reward'),
			'all_reward_points'			=> $this->language->get('column_reward_points'),			
			'all_coupon'				=> $this->language->get('column_coupon'),
			'all_coupon_name'			=> $this->language->get('column_coupon_name'),
			'all_coupon_code'			=> $this->language->get('column_coupon_code'),
			'all_order_tax'				=> $this->language->get('column_order_tax'),
			'all_credit'				=> $this->language->get('column_credit'),
			'all_voucher'				=> $this->language->get('column_voucher'),
			'all_voucher_code'			=> $this->language->get('column_voucher_code'),
			'all_discount'				=> $this->language->get('column_discount'),
			'all_order_value'			=> $this->language->get('column_order_value'),
			'all_refund'				=> $this->language->get('column_order_refund'),
			'all_order_sales'			=> $this->language->get('column_order_sales'),
			'all_order_prod_costs'		=> $this->language->get('column_order_prod_costs'),
			'all_order_commission'		=> $this->language->get('column_order_commission'),
			'all_order_payment_cost'	=> $this->language->get('column_order_payment_cost'),
			'all_order_shipping_cost'	=> $this->language->get('column_order_shipping_cost'),
			'all_order_extra_cost'		=> $this->language->get('column_order_extra_cost'),
			'all_order_return_cost'		=> $this->language->get('column_order_return_cost'),
			'all_shipping_balance'		=> $this->language->get('column_shipping_balance'),
			'all_order_costs'			=> $this->language->get('column_order_costs'),
			'all_order_profit'			=> $this->language->get('column_order_profit'),
			'all_order_profit_prc'		=> $this->language->get('column_order_margin'),
			'all_order_shipping_method'	=> $this->language->get('column_order_shipping_method'),
			'all_order_payment_method'	=> $this->language->get('column_order_payment_method'),
			'all_order_status'			=> $this->language->get('column_order_status'),
			'all_order_store'			=> $this->language->get('column_order_store'),
			'all_campaign_name'			=> $this->language->get('column_campaign_name'),
			'all_campaign_code'			=> $this->language->get('column_campaign_code'),			
			'all_customer_cust_id'		=> $this->language->get('column_customer_cust_id'),
			'all_custom_fields'			=> $this->language->get('column_custom_fields'),
			'all_billing_first_name'	=> strip_tags($this->language->get('column_billing_first_name')),
			'all_billing_last_name'		=> strip_tags($this->language->get('column_billing_last_name')),
			'all_billing_company'		=> strip_tags($this->language->get('column_billing_company')),
			'all_billing_address_1'		=> strip_tags($this->language->get('column_billing_address_1')),
			'all_billing_address_2'		=> strip_tags($this->language->get('column_billing_address_2')),
			'all_billing_city'			=> strip_tags($this->language->get('column_billing_city')),
			'all_billing_zone'			=> strip_tags($this->language->get('column_billing_zone')),
			'all_billing_zone_id'		=> strip_tags($this->language->get('column_billing_zone_id')),
			'all_billing_zone_code'		=> strip_tags($this->language->get('column_billing_zone_code')),
			'all_billing_postcode'		=> strip_tags($this->language->get('column_billing_postcode')),			
			'all_billing_country'		=> strip_tags($this->language->get('column_billing_country')),
			'all_billing_country_id'	=> strip_tags($this->language->get('column_billing_country_id')),
			'all_billing_country_code'	=> strip_tags($this->language->get('column_billing_country_code')),
			'all_customer_telephone'	=> $this->language->get('column_customer_telephone'),
			'all_shipping_first_name'	=> strip_tags($this->language->get('column_shipping_first_name')),
			'all_shipping_last_name'	=> strip_tags($this->language->get('column_shipping_last_name')),
			'all_shipping_company'		=> strip_tags($this->language->get('column_shipping_company')),	
			'all_shipping_address_1'	=> strip_tags($this->language->get('column_shipping_address_1')),	
			'all_shipping_address_2'	=> strip_tags($this->language->get('column_shipping_address_2')),
			'all_shipping_city'			=> strip_tags($this->language->get('column_shipping_city')),
			'all_shipping_zone'			=> strip_tags($this->language->get('column_shipping_zone')),
			'all_shipping_zone_id'		=> strip_tags($this->language->get('column_shipping_zone_id')),
			'all_shipping_zone_code'	=> strip_tags($this->language->get('column_shipping_zone_code')),
			'all_shipping_postcode'		=> strip_tags($this->language->get('column_shipping_postcode')),
			'all_shipping_country'		=> strip_tags($this->language->get('column_shipping_country')),
			'all_shipping_country_id'	=> strip_tags($this->language->get('column_shipping_country_id')),
			'all_shipping_country_code'	=> strip_tags($this->language->get('column_shipping_country_code')),
			'all_order_weight'			=> $this->language->get('column_order_weight'),
			'all_order_comment'			=> $this->language->get('column_order_comment')
		);

		if ($this->config->get('advsop' . $this->user->getId() . '_settings_all_columns')) {
			$data['advsop_settings_all_columns'] = $this->config->get('advsop' . $this->user->getId() . '_settings_all_columns');
		} else {
			$data['advsop_settings_all_columns'] = array_keys($data['all_columns']);
		}

		$user = 'advsop' . $this->user->getId();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0' AND `code` = '" . $user . "'");
		$data['initialise'] = '';
		 if (!$query->rows) {
			$data['text_initialise_use'] = $this->language->get('text_initialise_use');			 
			$data['initialise'] = $query;
			$settings_data = array(
				'advsop' . $this->user->getId() . '_settings_filters' 		=> array('store','currency','tax','tax_class','geo_zone','customer_group','customer_name','customer_email','customer_telephone','payment_company','payment_address','payment_city','payment_zone','payment_postcode','payment_country','payment_method','shipping_company','shipping_address','shipping_city','shipping_zone','shipping_postcode','shipping_country','shipping_method','category','manufacturer','supplier','product','sku','model'),
				'advsop' . $this->user->getId() . '_settings_tr_columns' 	=> array_keys($data['tr_columns']),
				'advsop' . $this->user->getId() . '_settings_cr_columns' 	=> array_keys($data['cr_columns']),
				'advsop' . $this->user->getId() . '_settings_vr_columns' 	=> array_keys($data['vr_columns']),
				'advsop' . $this->user->getId() . '_settings_mv_columns' 	=> array('mv_orders','mv_customers','mv_products','mv_sub_total','mv_handling','mv_loworder','mv_shipping','mv_reward','mv_coupon','mv_tax','mv_credit','mv_voucher','mv_discount','mv_total','mv_aov','mv_refunds','mv_total_sales','mv_total_costs','mv_total_profit','mv_total_profit_prc'),
				'advsop' . $this->user->getId() . '_settings_ol_columns' 	=> array_keys($data['ol_columns']),
				'advsop' . $this->user->getId() . '_settings_pl_columns' 	=> array('pl_prod_order_id','pl_prod_date_added','pl_prod_id','pl_prod_sku','pl_prod_model','pl_prod_name','pl_prod_option','pl_prod_currency','pl_prod_price','pl_prod_quantity','pl_prod_total_excl_vat','pl_prod_tax','pl_prod_total_incl_vat','pl_prod_sales','pl_prod_cost','pl_prod_profit','pl_prod_profit_margin','pl_prod_profit_markup'),
				'advsop' . $this->user->getId() . '_settings_cl_columns' 	=> array('cl_customer_order_id','cl_customer_date_added','cl_customer_cust_id','cl_billing_name','cl_billing_company','cl_billing_address_1','cl_billing_city','cl_billing_zone','cl_billing_postcode','cl_billing_country','cl_customer_telephone','cl_shipping_name','cl_shipping_company','cl_shipping_address_1','cl_shipping_city','cl_shipping_zone','cl_shipping_postcode','cl_shipping_country'),
				'advsop' . $this->user->getId() . '_settings_all_columns' 	=> array_keys($data['all_columns'])
			);
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting($user, $settings_data);
		}
		
		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $filter_limit;
		$pagination->url = $this->url->link('extension/report/adv_sales_profit', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($total) ? (($page - 1) * $filter_limit) + 1 : 0, ((($page - 1) * $filter_limit) > ($total - $filter_limit)) ? $total : ((($page - 1) * $filter_limit) + $filter_limit), $total, ceil($total / $filter_limit));

		$data['report_link'] = $this->url->link('extension/report/adv_sales_profit', 'user_token=' . $this->session->data['user_token'], true);
		$data['save_report_link'] = 'index.php?route=extension/report/adv_sales_profit'. $url;
		
		$data['filter_date_start'] = $filter_date_start;
		$data['filter_date_end'] = $filter_date_end;
		$data['filter_range'] = $filter_range;
		$data['filter_report'] = $filter_report;
		$data['filter_group'] = $filter_group;		
		$data['filter_sort'] = $filter_sort;
		$data['filter_order'] = $filter_order;
		$data['filter_details'] = $filter_details;
		$data['filter_limit'] = $filter_limit;		
		$data['filter_status_date_start'] = $filter_status_date_start;
		$data['filter_status_date_end'] = $filter_status_date_end;
		$data['filter_order_status_id'] = $filter_order_status_id;		
		$data['filter_order_id_from'] = $filter_order_id_from;
		$data['filter_order_id_to'] = $filter_order_id_to;
		$data['filter_order_value_min'] = $filter_order_value_min;
		$data['filter_order_value_max'] = $filter_order_value_max;			
		$data['filter_store_id'] = $filter_store_id;
		$data['filter_currency'] = $filter_currency;
		$data['filter_taxes'] = $filter_taxes;
		$data['filter_tax_classes'] = $filter_tax_classes;		
		$data['filter_geo_zones'] = $filter_geo_zones;
		$data['filter_customer_group_id'] = $filter_customer_group_id;
		$data['filter_customer_name'] = $filter_customer_name; 
		$data['filter_customer_email'] = $filter_customer_email; 		
		$data['filter_customer_telephone'] = $filter_customer_telephone;
		$data['filter_ip'] = $filter_ip;
		$data['filter_payment_company'] = $filter_payment_company; 
		$data['filter_payment_address'] = $filter_payment_address; 
		$data['filter_payment_city'] = $filter_payment_city; 
		$data['filter_payment_postcode'] = $filter_payment_postcode; 
		$data['filter_payment_zone'] = $filter_payment_zone; 
		$data['filter_payment_country'] = $filter_payment_country; 
		$data['filter_payment_method'] = $filter_payment_method; 		
		$data['filter_shipping_company'] = $filter_shipping_company; 
		$data['filter_shipping_address'] = $filter_shipping_address; 
		$data['filter_shipping_city'] = $filter_shipping_city; 
		$data['filter_shipping_postcode'] = $filter_shipping_postcode; 
		$data['filter_shipping_zone'] = $filter_shipping_zone; 
		$data['filter_shipping_country'] = $filter_shipping_country; 
		$data['filter_shipping_method'] = $filter_shipping_method; 
		$data['filter_manufacturer'] = $filter_manufacturer; 
		$data['filter_category'] = $filter_category; 
		$data['filter_supplier'] = $filter_supplier;		
		$data['filter_sku'] = $filter_sku; 
		$data['filter_product_name'] = $filter_product_name; 
		$data['filter_model'] = $filter_model; 
		$data['filter_option'] = $filter_option;
		$data['filter_attribute'] = $filter_attribute;
		$data['filter_location'] = $filter_location;
		$data['filter_coupon_name'] = $filter_coupon_name; 
		$data['filter_coupon_code'] = $filter_coupon_code; 
		$data['filter_voucher_code'] = $filter_voucher_code;
		$data['filter_campaign_name'] = $filter_campaign_name; 
		$data['filter_campaign_code'] = $filter_campaign_code; 
		
		$data['url'] = $this->url->link('extension/report/adv_sales_profit', 'user_token=' . $this->session->data['user_token'] . $url . '&page='. $page, true);
				
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if (!isset($_GET['cron'])) {
			$this->response->setOutput($this->load->view('extension/report/adv_sales_profit', $data));
		} else {	
			$export_data = array(
				'results'		=> $total > 0 ? $data['orders'] = $orders : ''
			);

			$user_id = $_GET['user_id'];

			if ($this->config->get('advsop' . $user_id . '_settings_tr_columns')) {
				$advsop_settings_tr_columns = $this->config->get('advsop' . $user_id . '_settings_tr_columns');
			} else {
				$advsop_settings_tr_columns = array();
			}

			if ($this->config->get('advsop' . $user_id . '_settings_cr_columns')) {
				$advsop_settings_cr_columns = $this->config->get('advsop' . $user_id . '_settings_cr_columns');
			} else {
				$advsop_settings_cr_columns = array();
			}

			if ($this->config->get('advsop' . $user_id . '_settings_vr_columns')) {
				$advsop_settings_vr_columns = $this->config->get('advsop' . $user_id . '_settings_vr_columns');
			} else {
				$advsop_settings_vr_columns = array();
			}
			
			if ($this->config->get('advsop' . $user_id . '_settings_mv_columns')) {
				$advsop_settings_mv_columns = $this->config->get('advsop' . $user_id . '_settings_mv_columns');
			} else {
				$advsop_settings_mv_columns = array();
			}	

			if ($this->config->get('advsop' . $user_id . '_settings_ol_columns')) {
				$advsop_settings_ol_columns = $this->config->get('advsop' . $user_id . '_settings_ol_columns');
			} else {
				$advsop_settings_ol_columns = array();
			}
		
			if ($this->config->get('advsop' . $user_id . '_settings_pl_columns')) {
				$advsop_settings_pl_columns = $this->config->get('advsop' . $user_id . '_settings_pl_columns');
			} else {
				$advsop_settings_pl_columns = array();
			}
		
			if ($this->config->get('advsop' . $user_id . '_settings_cl_columns')) {
				$advsop_settings_cl_columns = $this->config->get('advsop' . $user_id . '_settings_cl_columns');
			} else {
				$advsop_settings_cl_columns = array();
			}
		
			if ($this->config->get('advsop' . $user_id . '_settings_all_columns')) {
				$advsop_settings_all_columns = $this->config->get('advsop' . $user_id . '_settings_all_columns');
			} else {
				$advsop_settings_all_columns = array();
			}
			
			$cron_settings = $this->config->get('advsop' . $user_id . 'cron_setting') !== null ? unserialize($this->config->get('advsop' . $user_id . 'cron_setting')) : '';
			foreach ($cron_settings as $cron_setting) {
				if (isset($_GET['cron_id']) && ($_GET['cron_id'] == $cron_setting['cron_id'])) {
					$report_type = $cron_setting['cron_report_type'];
					$export_type = $cron_setting['cron_export_type'];
					$export_logo_criteria = $cron_setting['cron_export_logo_criteria'];
					$export_csv_delimiter = $cron_setting['cron_export_csv_delimiter'];
			
					$filter_report = $cron_setting['cron_filter_report'];
					$filter_details = $cron_setting['cron_filter_details'];
					$filter_group = $cron_setting['cron_filter_group'];
					$filter_sort = $cron_setting['cron_filter_sort'];
					$filter_order = $cron_setting['cron_filter_order'];
					$filter_limit = $cron_setting['cron_filter_limit'];
					
					$filter_range = $cron_setting['cron_filter_range'];
					$filter_date_start = $cron_setting['cron_date_start'];
					$filter_date_end = $cron_setting['cron_date_end'];
					$filter_order_status_id = $cron_setting['cron_filter_order_status_id'];
					$filter_status_date_start = $cron_setting['cron_status_date_start'];
					$filter_status_date_end = $cron_setting['cron_status_date_end'];
					$filter_order_id_from = $cron_setting['cron_filter_order_id_from'];
					$filter_order_id_to = $cron_setting['cron_filter_order_id_to'];
					$filter_order_value_min = $cron_setting['cron_filter_order_value_min'];
					$filter_order_value_max = $cron_setting['cron_filter_order_value_max'];
		
					$filter_store_id = $cron_setting['cron_filter_store_id'];
					$filter_currency = $cron_setting['cron_filter_currency'];
					$filter_taxes = $cron_setting['cron_filter_taxes'];
					$filter_tax_classes = $cron_setting['cron_filter_tax_classes'];
					$filter_geo_zones = $cron_setting['cron_filter_geo_zones'];
					$filter_customer_group_id = $cron_setting['cron_filter_customer_group_id'];
					$filter_customer_name = $cron_setting['cron_filter_customer_name'];
					$filter_customer_email = $cron_setting['cron_filter_customer_email'];
					$filter_customer_telephone = $cron_setting['cron_filter_customer_telephone'];
					$filter_ip = $cron_setting['cron_filter_ip'];
					$filter_payment_company = $cron_setting['cron_filter_payment_company'];
					$filter_payment_address = $cron_setting['cron_filter_payment_address'];
					$filter_payment_city = $cron_setting['cron_filter_payment_city'];
					$filter_payment_zone = $cron_setting['cron_filter_payment_zone'];
					$filter_payment_postcode = $cron_setting['cron_filter_payment_postcode'];
					$filter_payment_country = $cron_setting['cron_filter_payment_country'];
					$filter_payment_method = $cron_setting['cron_filter_payment_method'];
					$filter_shipping_company = $cron_setting['cron_filter_shipping_company'];
					$filter_shipping_address = $cron_setting['cron_filter_shipping_address'];
					$filter_shipping_city = $cron_setting['cron_filter_shipping_city'];
					$filter_shipping_zone = $cron_setting['cron_filter_shipping_zone'];
					$filter_shipping_postcode = $cron_setting['cron_filter_shipping_postcode'];
					$filter_shipping_country = $cron_setting['cron_filter_shipping_country'];
					$filter_shipping_method = $cron_setting['cron_filter_shipping_method'];
					$filter_category = $cron_setting['cron_filter_category'];
					$filter_manufacturer = $cron_setting['cron_filter_manufacturer'];
					$filter_supplier = $cron_setting['cron_filter_supplier'];
					$filter_sku = $cron_setting['cron_filter_sku'];
					$filter_product_name = $cron_setting['cron_filter_product_name'];
					$filter_model = $cron_setting['cron_filter_model'];
					$filter_option = $cron_setting['cron_filter_option'];
					$filter_attribute = $cron_setting['cron_filter_attribute'];
					$filter_location = $cron_setting['cron_filter_location'];
					$filter_coupon_name = $cron_setting['cron_filter_coupon_name'];
					$filter_coupon_code = $cron_setting['cron_filter_coupon_code'];
					$filter_voucher_code = $cron_setting['cron_filter_voucher_code'];
					$filter_campaign_name = $cron_setting['cron_filter_campaign_name'];
					$filter_campaign_code = $cron_setting['cron_filter_campaign_code'];
					
					$file_save_path = $cron_setting['cron_file_save_path'];
					$file_name = $cron_setting['cron_file_name'];
					$export_file = $cron_setting['cron_export_file'];
					$email = $cron_setting['cron_email'];
				}
			}
			
			if ($export_type == 'export_xlsx') {
				$logo = str_replace('\\', '/', DIR_IMAGE . $this->config->get('config_logo'));
			} else {
				$this->load->model('tool/image');
				$logo = $this->model_tool_image->resize($this->config->get('config_logo'), 268, 50);
			}
		
			if ($report_type == 'export_no_details' && $export_type == 'export_xls') {
				$cwd = getcwd();			
				chdir(DIR_SYSTEM . 'library/pear');
				require_once('Spreadsheet/Excel/Writer.php');
				chdir($cwd);			
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xls.inc.php');
				exit();
			} elseif ($report_type == 'export_no_details' && $export_type == 'export_xlsx') {
				if (PHP_VERSION_ID < 80000) {			
				require_once(DIR_SYSTEM . 'library/PHPExcel/Classes/PHPExcel.php');
				require_once(DIR_SYSTEM . 'library/PHPExcel/Classes/PHPExcel/IOFactory.php');			
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xlsx.inc.php');
				} else {
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xlsx_php8.inc.php');
				}
				exit();			
			} elseif ($report_type == 'export_no_details' && $export_type == 'export_csv') {
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_csv.inc.php');
				exit();
			} elseif ($report_type == 'export_no_details' && $export_type == 'export_pdf') {
				require_once(DIR_SYSTEM . 'library/dompdf/autoload.inc.php');
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_pdf.inc.php');
				exit();
			} elseif ($report_type == 'export_no_details' && $export_type == 'export_html') {
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_html.inc.php');
				exit();	
			} elseif ($report_type == 'export_basic_details' && $export_type == 'export_xls') {
				$cwd = getcwd();			
				chdir(DIR_SYSTEM . 'library/pear');
				require_once('Spreadsheet/Excel/Writer.php');
				chdir($cwd);			
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xls_basic_details.inc.php');
				exit();
			} elseif ($report_type == 'export_basic_details' && $export_type == 'export_xlsx') {
				if (PHP_VERSION_ID < 80000) {			
				require_once(DIR_SYSTEM . 'library/PHPExcel/Classes/PHPExcel.php');
				require_once(DIR_SYSTEM . 'library/PHPExcel/Classes/PHPExcel/IOFactory.php');			
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xlsx_basic_details.inc.php');
				} else {
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xlsx_php8_basic_details.inc.php');
				}
				exit();	
			} elseif ($report_type == 'export_basic_details' && $export_type == 'export_csv') {
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_csv_basic_details.inc.php');
				exit();				
			} elseif ($report_type == 'export_basic_details' && $export_type == 'export_pdf') {
				require_once(DIR_SYSTEM . 'library/dompdf/autoload.inc.php');
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_pdf_basic_details.inc.php');	
				exit();
			} elseif ($report_type == 'export_basic_details' && $export_type == 'export_html') {
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_html_basic_details.inc.php');
				exit();	
			} elseif (($report_type == 'export_all_details_products' or $report_type == 'export_all_details_orders') && $export_type == 'export_xls') {
				$cwd = getcwd();			
				chdir(DIR_SYSTEM . 'library/pear');
				require_once('Spreadsheet/Excel/Writer.php');
				chdir($cwd);			
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xls_all_details.inc.php');
				exit();
			} elseif (($report_type == 'export_all_details_products' or $report_type == 'export_all_details_orders') && $export_type == 'export_xlsx') {
				if (PHP_VERSION_ID < 80000) {			
				require_once(DIR_SYSTEM . 'library/PHPExcel/Classes/PHPExcel.php');
				require_once(DIR_SYSTEM . 'library/PHPExcel/Classes/PHPExcel/IOFactory.php');			
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xlsx_all_details.inc.php');
				} else {
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xlsx_php8_all_details.inc.php');
				}
				exit();	
			} elseif (($report_type == 'export_all_details_products' or $report_type == 'export_all_details_orders') && $export_type == 'export_csv') {
				include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_csv_all_details.inc.php');
				exit();			
			} else {
				exit();
			}		
		}
	}
	
	public function customer_autocomplete() {
		$json = array();

		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->request->get['filter_customer_name']) or isset($this->request->get['filter_customer_email']) or isset($this->request->get['filter_customer_telephone']) or isset($this->request->get['filter_ip']) or isset($this->request->get['filter_payment_company']) or isset($this->request->get['filter_payment_address']) or isset($this->request->get['filter_payment_city']) or isset($this->request->get['filter_payment_zone']) or isset($this->request->get['filter_payment_postcode']) or isset($this->request->get['filter_payment_country']) or isset($this->request->get['filter_shipping_company']) or isset($this->request->get['filter_shipping_address']) or isset($this->request->get['filter_shipping_city']) or isset($this->request->get['filter_shipping_zone']) or isset($this->request->get['filter_shipping_postcode']) or isset($this->request->get['filter_shipping_country'])) {
			
		$this->load->model('extension/report/adv_sales_profit');
		
		if (isset($this->request->get['filter_customer_name'])) {
			$filter_customer_name = $this->request->get['filter_customer_name'];
		} else {
			$filter_customer_name = '';
		}

		if (isset($this->request->get['filter_customer_email'])) {
			$filter_customer_email = $this->request->get['filter_customer_email'];
		} else {
			$filter_customer_email = '';
		}	

		if (isset($this->request->get['filter_customer_telephone'])) {
			$filter_customer_telephone = $this->request->get['filter_customer_telephone'];
		} else {
			$filter_customer_telephone = '';
		}

		if (isset($this->request->get['filter_ip'])) {
			$filter_ip = $this->request->get['filter_ip'];
		} else {
			$filter_ip = '';
		}
		
		if (isset($this->request->get['filter_payment_company'])) {
			$filter_payment_company = $this->request->get['filter_payment_company'];
		} else {
			$filter_payment_company = '';
		}
		
		if (isset($this->request->get['filter_payment_address'])) {
			$filter_payment_address = $this->request->get['filter_payment_address'];
		} else {
			$filter_payment_address = '';
		}

		if (isset($this->request->get['filter_payment_city'])) {
			$filter_payment_city = $this->request->get['filter_payment_city'];
		} else {
			$filter_payment_city = '';
		}
		
		if (isset($this->request->get['filter_payment_zone'])) {
			$filter_payment_zone = $this->request->get['filter_payment_zone'];
		} else {
			$filter_payment_zone = '';
		}
		
		if (isset($this->request->get['filter_payment_postcode'])) {
			$filter_payment_postcode = $this->request->get['filter_payment_postcode'];
		} else {
			$filter_payment_postcode = '';
		}

		if (isset($this->request->get['filter_payment_country'])) {
			$filter_payment_country = $this->request->get['filter_payment_country'];
		} else {
			$filter_payment_country = '';
		}
		
		if (isset($this->request->get['filter_shipping_company'])) {
			$filter_shipping_company = $this->request->get['filter_shipping_company'];
		} else {
			$filter_shipping_company = '';
		}
		
		if (isset($this->request->get['filter_shipping_address'])) {
			$filter_shipping_address = $this->request->get['filter_shipping_address'];
		} else {
			$filter_shipping_address = '';
		}

		if (isset($this->request->get['filter_shipping_city'])) {
			$filter_shipping_city = $this->request->get['filter_shipping_city'];
		} else {
			$filter_shipping_city = '';
		}
		
		if (isset($this->request->get['filter_shipping_zone'])) {
			$filter_shipping_zone = $this->request->get['filter_shipping_zone'];
		} else {
			$filter_shipping_zone = '';
		}
		
		if (isset($this->request->get['filter_shipping_postcode'])) {
			$filter_shipping_postcode = $this->request->get['filter_shipping_postcode'];
		} else {
			$filter_shipping_postcode = '';
		}

		if (isset($this->request->get['filter_shipping_country'])) {
			$filter_shipping_country = $this->request->get['filter_shipping_country'];
		} else {
			$filter_shipping_country = '';
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = 10;
		}
		
		$filter_data = array(		
			'filter_customer_name' 	 		=> $filter_customer_name,
			'filter_customer_email' 	 	=> $filter_customer_email,			
			'filter_customer_telephone' 	=> $filter_customer_telephone,
			'filter_ip' 					=> $filter_ip,			
			'filter_payment_company' 		=> $filter_payment_company,
			'filter_payment_address' 		=> $filter_payment_address,
			'filter_payment_city' 			=> $filter_payment_city,
			'filter_payment_zone' 			=> $filter_payment_zone,			
			'filter_payment_postcode' 		=> $filter_payment_postcode,
			'filter_payment_country' 		=> $filter_payment_country,			
			'filter_shipping_company' 		=> $filter_shipping_company,
			'filter_shipping_address' 		=> $filter_shipping_address,
			'filter_shipping_city' 			=> $filter_shipping_city,
			'filter_shipping_zone' 			=> $filter_shipping_zone,			
			'filter_shipping_postcode' 		=> $filter_shipping_postcode,
			'filter_shipping_country' 		=> $filter_shipping_country,
			'start'        					=> 0,
			'limit'        					=> $limit
		);
						
		$results = $this->model_extension_report_adv_sales_profit->getCustomerAutocomplete($filter_data);
			
			foreach ($results as $result) {
				$json[] = array(
					'customer_id'     		=> $result['customer_id'],				
					'cust_name'     		=> html_entity_decode($result['cust_name'], ENT_QUOTES, 'UTF-8'),
					'cust_email'     		=> $result['cust_email'],
					'cust_telephone'     	=> $result['cust_telephone'],
					'cust_ip'     			=> $result['cust_ip'],
					'payment_company'     	=> html_entity_decode($result['payment_company'], ENT_QUOTES, 'UTF-8'),	
					'payment_address'     	=> html_entity_decode($result['payment_address'], ENT_QUOTES, 'UTF-8'),	
					'payment_city'     		=> html_entity_decode($result['payment_city'], ENT_QUOTES, 'UTF-8'),	
					'payment_zone'     		=> html_entity_decode($result['payment_zone'], ENT_QUOTES, 'UTF-8'),						
					'payment_postcode'     	=> $result['payment_postcode'],
					'payment_country'     	=> html_entity_decode($result['payment_country'], ENT_QUOTES, 'UTF-8'),					
					'shipping_company'     	=> html_entity_decode($result['shipping_company'], ENT_QUOTES, 'UTF-8'),	
					'shipping_address'     	=> html_entity_decode($result['shipping_address'], ENT_QUOTES, 'UTF-8'),
					'shipping_city'     	=> html_entity_decode($result['shipping_city'], ENT_QUOTES, 'UTF-8'),
					'shipping_zone'     	=> html_entity_decode($result['shipping_zone'], ENT_QUOTES, 'UTF-8'),					
					'shipping_postcode'     => $result['shipping_postcode'],
					'shipping_country'     	=> html_entity_decode($result['shipping_country'], ENT_QUOTES, 'UTF-8')			
				);
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function product_autocomplete() {
		$json = array();

		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->request->get['filter_sku']) or isset($this->request->get['filter_product_name']) or isset($this->request->get['filter_model'])) {
		
		$this->load->model('extension/report/adv_sales_profit');
					
		if (isset($this->request->get['filter_sku'])) {
			$filter_sku = $this->request->get['filter_sku'];
		} else {
			$filter_sku = '';
		}

		if (isset($this->request->get['filter_product_name'])) {
			$filter_product_name = $this->request->get['filter_product_name'];
		} else {
			$filter_product_name = '';
		}
		
		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = '';
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = 10;
		}
		
		$filter_data = array(				
			'filter_sku' 	 				=> $filter_sku,
			'filter_product_name' 	 		=> $filter_product_name,
			'filter_model' 	 				=> $filter_model,
			'start'        					=> 0,
			'limit'        					=> $limit	
		);
						
		$results = $this->model_extension_report_adv_sales_profit->getProductAutocomplete($filter_data);
			
			foreach ($results as $result) {
				$json[] = array(
					'product_id'     		=> $result['product_id'],
					'prod_sku'     			=> html_entity_decode($result['prod_sku'], ENT_QUOTES, 'UTF-8'),					
					'prod_name'     		=> html_entity_decode($result['prod_name'], ENT_QUOTES, 'UTF-8'),
					'prod_model'     		=> html_entity_decode($result['prod_model'], ENT_QUOTES, 'UTF-8')				
				);
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function coupon_autocomplete() {
		$json = array();

		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->request->get['filter_coupon_code'])) {
			
		$this->load->model('extension/report/adv_sales_profit');

		if (isset($this->request->get['filter_coupon_code'])) {
			$filter_coupon_code = $this->request->get['filter_coupon_code'];
		} else {
			$filter_coupon_code = '';
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = 10;
		}
		
		$filter_data = array(		
			'filter_coupon_code' 	 		=> $filter_coupon_code,
			'start'        					=> 0,
			'limit'        					=> $limit			
		);
						
		$results = $this->model_extension_report_adv_sales_profit->getCouponAutocomplete($filter_data);
			
			foreach ($results as $result) {
				$json[] = array(
					'coupon_id'     		=> $result['coupon_id'],
					'coupon_code'     		=> html_entity_decode($result['coupon_code'], ENT_QUOTES, 'UTF-8')
				);
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function voucher_autocomplete() {
		$json = array();

		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->request->get['filter_voucher_code'])) {
			
		$this->load->model('extension/report/adv_sales_profit');

		if (isset($this->request->get['filter_voucher_code'])) {
			$filter_voucher_code = $this->request->get['filter_voucher_code'];
		} else {
			$filter_voucher_code = '';
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = 10;
		}
		
		$filter_data = array(		
			'filter_voucher_code' 	 		=> $filter_voucher_code,
			'start'        					=> 0,
			'limit'        					=> $limit
		);
						
		$results = $this->model_extension_report_adv_sales_profit->getVoucherAutocomplete($filter_data);
			
			foreach ($results as $result) {
				$json[] = array(
					'voucher_id'     		=> $result['voucher_id'],
					'voucher_code'     		=> html_entity_decode($result['voucher_code'], ENT_QUOTES, 'UTF-8')
				);
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function campaign_autocomplete() {
		$json = array();

		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->request->get['filter_campaign_code'])) {
			
		$this->load->model('extension/report/adv_sales_profit');

		if (isset($this->request->get['filter_campaign_code'])) {
			$filter_campaign_code = $this->request->get['filter_campaign_code'];
		} else {
			$filter_campaign_code = '';
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = 10;
		}
		
		$filter_data = array(		
			'filter_campaign_code' 	 		=> $filter_campaign_code,
			'start'        					=> 0,
			'limit'        					=> $limit			
		);
						
		$results = $this->model_extension_report_adv_sales_profit->getCampaignAutocomplete($filter_data);
			
			foreach ($results as $result) {
				$json[] = array(
					'marketing_id'     		=> $result['marketing_id'],
					'campaign_code'     	=> html_entity_decode($result['campaign_code'], ENT_QUOTES, 'UTF-8')
				);
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function settings($filter_data = array()) {
		$json = array();
		
		$this->load->model('setting/setting');
		$this->load->language('extension/report/adv_sales_profit');
		
		if (!$json) {
			if (!$this->user->hasPermission('modify', 'extension/report/adv_sales_profit')) {
				$json['error'] = $this->language->get('error_permission');
			} else {			
				$user = 'advsop' . $this->user->getId();
				$this->model_setting_setting->editSetting($user, $this->request->post);
				$json['success'] = $this->language->get('text_success_settings');
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}	
	
	public function load_save_report($filter_data = array()) {
		$json = array();
		
		$this->load->model('setting/setting');
		$this->load->language('extension/report/adv_sales_profit');
		
		if (!$json) {
			if (!$this->user->hasPermission('modify', 'extension/report/adv_sales_profit')) {
				$json['error'] = $this->language->get('error_permission');
			} else {			
				if (isset($this->request->post['advsop_load_save_report'])) {
					$this->request->post['advsop' . $this->user->getId() . 'sr_load_save_report'] = serialize($this->request->post['advsop_load_save_report']);
				}			
				$this->model_setting_setting->editSetting('advsop' . $this->user->getId() . 'sr', $this->request->post);
				$json['success'] = $this->language->get('text_success_save_report');
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function cron($filter_data = array()) {
		$json = array();
		
		$this->load->model('setting/setting');
		$this->load->language('extension/report/adv_sales_profit');
		
		$crons = unserialize($this->config->get('advsop' . $this->user->getId() . 'cron_setting'));
		
		if (!$json) {
			if (!$this->user->hasPermission('modify', 'extension/report/adv_sales_profit')) {
				$json['error'] = $this->language->get('error_permission');
			} else {	
				if (!$crons && $this->request->post['cron_export_type'] == '') {
					$json['error'] = $this->language->get('error_export_type');
				} else if (!$crons && ((utf8_strlen($this->request->post['cron_email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['cron_email']))) {
					$json['error'] = $this->language->get('error_export_email');					
				} else if (!$crons && $this->request->post['cron_file_name'] == '') {
					$json['error'] = $this->language->get('error_file_name');
				} else if (!$crons && $this->request->post['cron_user'] == '') {
					$json['error'] = $this->language->get('error_admin_username');
				} else if (!$crons && $this->request->post['cron_pass'] == '') {
					$json['error'] = $this->language->get('error_admin_password');
				} else if (!$crons && $this->request->post['cron_token'] == '') {
					$json['error'] = $this->language->get('error_generate_token');
				} else {
					if (isset($this->request->post['advsop_cron_setting'])) {
						$this->request->post['advsop' . $this->user->getId() . 'cron_setting'] = serialize($this->request->post['advsop_cron_setting']);
					}			
					$this->model_setting_setting->editSetting('advsop' . $this->user->getId() . 'cron', $this->request->post);
					$json['success'] = $this->language->get('text_success_save_cron');
				}			
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function export($filter_data = array()) {
		$this->load->language('extension/report/adv_sales_profit');
		$orders_data = json_decode(file_get_contents('sop_json_array.txt'), true);
		
		$export_data = array(
			'results'		=> $orders_data
		);

		$user_id = $this->user->getId();

		if ($this->config->get('advsop' . $user_id . '_settings_tr_columns')) {
			$advsop_settings_tr_columns = $this->config->get('advsop' . $user_id . '_settings_tr_columns');
		} else {
			$advsop_settings_tr_columns = array();
		}

		if ($this->config->get('advsop' . $user_id . '_settings_cr_columns')) {
			$advsop_settings_cr_columns = $this->config->get('advsop' . $user_id . '_settings_cr_columns');
		} else {
			$advsop_settings_cr_columns = array();
		}

		if ($this->config->get('advsop' . $user_id . '_settings_vr_columns')) {
			$advsop_settings_vr_columns = $this->config->get('advsop' . $user_id . '_settings_vr_columns');
		} else {
			$advsop_settings_vr_columns = array();
		}
		
		if ($this->config->get('advsop' . $user_id . '_settings_mv_columns')) {
			$advsop_settings_mv_columns = $this->config->get('advsop' . $user_id . '_settings_mv_columns');
		} else {
			$advsop_settings_mv_columns = array();
		}	

		if ($this->config->get('advsop' . $user_id . '_settings_ol_columns')) {
			$advsop_settings_ol_columns = $this->config->get('advsop' . $user_id . '_settings_ol_columns');
		} else {
			$advsop_settings_ol_columns = array();
		}
		
		if ($this->config->get('advsop' . $user_id . '_settings_pl_columns')) {
			$advsop_settings_pl_columns = $this->config->get('advsop' . $user_id . '_settings_pl_columns');
		} else {
			$advsop_settings_pl_columns = array();
		}
		
		if ($this->config->get('advsop' . $user_id . '_settings_cl_columns')) {
			$advsop_settings_cl_columns = $this->config->get('advsop' . $user_id . '_settings_cl_columns');
		} else {
			$advsop_settings_cl_columns = array();
		}
		
		if ($this->config->get('advsop' . $user_id . '_settings_all_columns')) {
			$advsop_settings_all_columns = $this->config->get('advsop' . $user_id . '_settings_all_columns');
		} else {
			$advsop_settings_all_columns = array();
		}
		
		$this->session->data['report_type'] = $report_type = $this->request->get['report_type'];		
		$this->session->data['export_type'] = $export_type = $this->request->get['export_type'];
		$this->session->data['export_logo_criteria'] = $export_logo_criteria = $this->request->get['export_logo_criteria'];
		$this->session->data['export_csv_delimiter'] = $export_csv_delimiter = $this->request->get['export_csv_delimiter'];
			
		$filter_report = $this->session->data['filter_report'];
		$filter_details = $this->session->data['filter_details'];
		$filter_group = $this->session->data['filter_group'];
		$filter_sort = $this->session->data['filter_sort'];
		$filter_order = $this->session->data['filter_order'];
		$filter_limit = $this->session->data['filter_limit'];
		
		$filter_range = $this->session->data['filter_range'];
		$filter_date_start = $this->session->data['filter_date_start'];
		$filter_date_end = $this->session->data['filter_date_end'];
		$filter_order_status_id = $this->session->data['filter_order_status_id'];
		$filter_status_date_start = $this->session->data['filter_status_date_start'];
		$filter_status_date_end = $this->session->data['filter_status_date_end'];
		$filter_order_id_from = $this->session->data['filter_order_id_from'];
		$filter_order_id_to = $this->session->data['filter_order_id_to'];
		$filter_order_value_min = $this->session->data['filter_order_value_min'];
		$filter_order_value_max = $this->session->data['filter_order_value_max'];			
		
		$filter_store_id = $this->session->data['filter_store_id'];
		$filter_currency = $this->session->data['filter_currency'];
		$filter_taxes = $this->session->data['filter_taxes'];
		$filter_tax_classes = $this->session->data['filter_tax_classes'];
		$filter_geo_zones = $this->session->data['filter_geo_zones'];
		$filter_customer_group_id = $this->session->data['filter_customer_group_id'];
		$filter_customer_name = $this->session->data['filter_customer_name'];
		$filter_customer_email = $this->session->data['filter_customer_email'];
		$filter_customer_telephone = $this->session->data['filter_customer_telephone'];
		$filter_ip = $this->session->data['filter_ip'];
		$filter_payment_company = $this->session->data['filter_payment_company'];
		$filter_payment_address = $this->session->data['filter_payment_address'];
		$filter_payment_city = $this->session->data['filter_payment_city'];
		$filter_payment_zone = $this->session->data['filter_payment_zone'];
		$filter_payment_postcode = $this->session->data['filter_payment_postcode'];
		$filter_payment_country = $this->session->data['filter_payment_country'];
		$filter_payment_method = $this->session->data['filter_payment_method'];
		$filter_shipping_company = $this->session->data['filter_shipping_company'];
		$filter_shipping_address = $this->session->data['filter_shipping_address'];
		$filter_shipping_city = $this->session->data['filter_shipping_city'];
		$filter_shipping_zone = $this->session->data['filter_shipping_zone'];
		$filter_shipping_postcode = $this->session->data['filter_shipping_postcode'];
		$filter_shipping_country = $this->session->data['filter_shipping_country'];
		$filter_shipping_method = $this->session->data['filter_shipping_method'];
		$filter_category = $this->session->data['filter_category'];
		$filter_manufacturer = $this->session->data['filter_manufacturer'];
		$filter_supplier = $this->session->data['filter_supplier'];
		$filter_sku = $this->session->data['filter_sku'];
		$filter_product_name = $this->session->data['filter_product_name'];
		$filter_model = $this->session->data['filter_model'];
		$filter_option = $this->session->data['filter_option'];
		$filter_attribute = $this->session->data['filter_attribute'];
		$filter_location = $this->session->data['filter_location'];
		$filter_coupon_name = $this->session->data['filter_coupon_name'];
		$filter_coupon_code = $this->session->data['filter_coupon_code'];
		$filter_voucher_code = $this->session->data['filter_voucher_code'];
		$filter_campaign_name = $this->session->data['filter_campaign_name'];
		$filter_campaign_code = $this->session->data['filter_campaign_code'];
		
		if ($export_type == 'export_xlsx') {
			$logo = str_replace('\\', '/', DIR_IMAGE . $this->config->get('config_logo'));
		} else {
			$this->load->model('tool/image');
			$logo = $this->model_tool_image->resize($this->config->get('config_logo'), 268, 50);
		}
		
		unset($this->session->data['error_export_type']);
		
		if ($report_type == 'export_no_details' && $export_type == 'export_xls') {
			$cwd = getcwd();			
			chdir(DIR_SYSTEM . 'library/pear');
			require_once('Spreadsheet/Excel/Writer.php');
			chdir($cwd);			
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xls.inc.php');
			exit();
		} elseif ($report_type == 'export_no_details' && $export_type == 'export_xlsx') {
			if (PHP_VERSION_ID < 80000) {			
			require_once(DIR_SYSTEM . 'library/PHPExcel/Classes/PHPExcel.php');
			require_once(DIR_SYSTEM . 'library/PHPExcel/Classes/PHPExcel/IOFactory.php');			
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xlsx.inc.php');
			} else {
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xlsx_php8.inc.php');
			}
			exit();			
		} elseif ($report_type == 'export_no_details' && $export_type == 'export_csv') {
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_csv.inc.php');
			exit();
		} elseif ($report_type == 'export_no_details' && $export_type == 'export_pdf') {
			require_once(DIR_SYSTEM . 'library/dompdf/autoload.inc.php');
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_pdf.inc.php');
			exit();
		} elseif ($report_type == 'export_no_details' && $export_type == 'export_html') {
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_html.inc.php');
			exit();	
		} elseif ($report_type == 'export_basic_details' && $export_type == 'export_xls') {
			$cwd = getcwd();			
			chdir(DIR_SYSTEM . 'library/pear');
			require_once('Spreadsheet/Excel/Writer.php');
			chdir($cwd);			
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xls_basic_details.inc.php');
			exit();
		} elseif ($report_type == 'export_basic_details' && $export_type == 'export_xlsx') {
			if (PHP_VERSION_ID < 80000) {			
			require_once(DIR_SYSTEM . 'library/PHPExcel/Classes/PHPExcel.php');
			require_once(DIR_SYSTEM . 'library/PHPExcel/Classes/PHPExcel/IOFactory.php');			
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xlsx_basic_details.inc.php');
			} else {
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xlsx_php8_basic_details.inc.php');
			}
			exit();	
		} elseif ($report_type == 'export_basic_details' && $export_type == 'export_csv') {
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_csv_basic_details.inc.php');
			exit();				
		} elseif ($report_type == 'export_basic_details' && $export_type == 'export_pdf') {
			require_once(DIR_SYSTEM . 'library/dompdf/autoload.inc.php');
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_pdf_basic_details.inc.php');	
			exit();
		} elseif ($report_type == 'export_basic_details' && $export_type == 'export_html') {
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_html_basic_details.inc.php');
			exit();	
		} elseif (($report_type == 'export_all_details_products' or $report_type == 'export_all_details_orders') && $export_type == 'export_xls') {
			$cwd = getcwd();			
			chdir(DIR_SYSTEM . 'library/pear');
			require_once('Spreadsheet/Excel/Writer.php');
			chdir($cwd);			
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xls_all_details.inc.php');
			exit();
		} elseif (($report_type == 'export_all_details_products' or $report_type == 'export_all_details_orders') && $export_type == 'export_xlsx') {
			if (PHP_VERSION_ID < 80000) {			
			require_once(DIR_SYSTEM . 'library/PHPExcel/Classes/PHPExcel.php');
			require_once(DIR_SYSTEM . 'library/PHPExcel/Classes/PHPExcel/IOFactory.php');			
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xlsx_all_details.inc.php');
			} else {
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_xlsx_php8_all_details.inc.php');
			}
			exit();	
		} elseif (($report_type == 'export_all_details_products' or $report_type == 'export_all_details_orders') && $export_type == 'export_csv') {
			include(DIR_APPLICATION . 'controller/extension/report/adv_reports/sop_export_csv_all_details.inc.php');
			exit();			
		} else {
			exit();
		}	
	}
	
	public function export_validate () {
		$json = array();
		
		$this->load->language('extension/report/adv_sales_profit');
		$orders_data = json_decode(file_get_contents('sop_json_array.txt'), true);
		
		if (!empty($orders_data)) {
			if ($this->request->post['export_type'] == '') {
				$json['error'] = $this->language->get('error_export_type');
			}
		} else {
			$json['error'] = $this->language->get('error_no_data');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	protected function clearSpreadsheetCache() {
		$files = glob(DIR_CACHE . 'Spreadsheet_Excel_Writer' . '*');
		
		if ($files) {
			foreach ($files as $file) {
				if (file_exists($file)) {
					@unlink($file);
					clearstatcache();
				}
			}
		}
	}
	
	public function chart($filter_data = array()) {
		$json = array();
		
		$this->load->language('extension/report/adv_sales_profit');

		$filter_report = $this->session->data['filter_report'];
		$filter_group = $this->session->data['filter_group'];

		$orders_data = json_decode(file_get_contents('sop_json_array.txt'), true);

		$chart_data = array(
			'results'		=> $orders_data
		);
		
		if ($filter_report == 'sales_summary' or $filter_report == 'day_of_week' or $filter_report == 'hour' or $filter_report == 'store' or $filter_report == 'currency' or $filter_report == 'customer_group') {
		
		$json['order'] = array();
		$json['customer'] = array();
		$json['product'] = array();
		$json['sales'] = array();
		$json['costs'] = array();
		$json['profit'] = array();
		$json['xaxis'] = array();

		$json['order']['label'] = $this->language->get('column_orders');
		$json['customer']['label'] = $this->language->get('column_customers');
		$json['product']['label'] = $this->language->get('column_products');
		$json['sales']['label'] = $this->language->get('column_grevenue');
		$json['costs']['label'] = $this->language->get('column_gexpenses');
		$json['profit']['label'] = $this->language->get('column_gprofit');
		$json['order']['data'] = array();
		$json['customer']['data'] = array();
		$json['product']['data'] = array();
		$json['sales']['data'] = array();
		$json['costs']['data'] = array();
		$json['profit']['data'] = array();
		
		}
		
		if ($filter_report == 'sales_summary') {
			
		if ($filter_group == 'month') {
		
		foreach ($chart_data['results'] as $key => $value) {
			$json['order']['data'][] = array($key, $value['orders']);
			$json['customer']['data'][] = array($key, $value['customers']);
			$json['product']['data'][] = array($key, $value['products']);
			$json['sales']['data'][] = array($key, $value['total_sales_raw']);
			$json['costs']['data'][] = array($key, $value['total_costs_raw']);
			$json['profit']['data'][] = array($key, $value['total_profit_raw']);
			$json['xaxis'][] = array($key, $value['year_month']);
		}
				
		} elseif ($filter_group == 'quarter') {

		foreach ($chart_data['results'] as $key => $value) {
			$json['order']['data'][] = array($key, $value['orders']);
			$json['customer']['data'][] = array($key, $value['customers']);
			$json['product']['data'][] = array($key, $value['products']);
			$json['sales']['data'][] = array($key, $value['total_sales_raw']);
			$json['costs']['data'][] = array($key, $value['total_costs_raw']);
			$json['profit']['data'][] = array($key, $value['total_profit_raw']);
			$json['xaxis'][] = array($key, $value['year_quarter']);
		}
		
		} elseif ($filter_group == 'year') {

		foreach ($chart_data['results'] as $key => $value) {
			$json['order']['data'][] = array($key, $value['orders']);
			$json['customer']['data'][] = array($key, $value['customers']);
			$json['product']['data'][] = array($key, $value['products']);
			$json['sales']['data'][] = array($key, $value['total_sales_raw']);
			$json['costs']['data'][] = array($key, $value['total_costs_raw']);
			$json['profit']['data'][] = array($key, $value['total_profit_raw']);
			$json['xaxis'][] = array($key, $value['year']);
		}
		
		}

		} elseif ($filter_report == 'day_of_week') {

		foreach ($chart_data['results'] as $key => $value) {
			$json['order']['data'][] = array($key, $value['orders']);
			$json['customer']['data'][] = array($key, $value['customers']);
			$json['product']['data'][] = array($key, $value['products']);
			$json['sales']['data'][] = array($key, $value['total_sales_raw']);
			$json['costs']['data'][] = array($key, $value['total_costs_raw']);
			$json['profit']['data'][] = array($key, $value['total_profit_raw']);
			$json['xaxis'][] = array($key, $value['day_of_week']);
		}
		
		} elseif ($filter_report == 'hour') {

		foreach ($chart_data['results'] as $key => $value) {
			$json['order']['data'][] = array($key, $value['orders']);
			$json['customer']['data'][] = array($key, $value['customers']);
			$json['product']['data'][] = array($key, $value['products']);
			$json['sales']['data'][] = array($key, $value['total_sales_raw']);
			$json['costs']['data'][] = array($key, $value['total_costs_raw']);
			$json['profit']['data'][] = array($key, $value['total_profit_raw']);
			$json['xaxis'][] = array($key, $value['hour']);
		}
		
		} elseif ($filter_report == 'store') {

		foreach ($chart_data['results'] as $key => $value) {
			$json['order']['data'][] = array($key, $value['orders']);
			$json['customer']['data'][] = array($key, $value['customers']);
			$json['product']['data'][] = array($key, $value['products']);
			$json['sales']['data'][] = array($key, $value['total_sales_raw']);
			$json['costs']['data'][] = array($key, $value['total_costs_raw']);
			$json['profit']['data'][] = array($key, $value['total_profit_raw']);
			$json['xaxis'][] = array($key, $value['store_name']);
		}
		
		} elseif ($filter_report == 'currency') {

		foreach ($chart_data['results'] as $key => $value) {
			$json['order']['data'][] = array($key, $value['orders']);
			$json['customer']['data'][] = array($key, $value['customers']);
			$json['product']['data'][] = array($key, $value['products']);
			$json['sales']['data'][] = array($key, $value['total_sales_raw']);
			$json['costs']['data'][] = array($key, $value['total_costs_raw']);
			$json['profit']['data'][] = array($key, $value['total_profit_raw']);
			$json['xaxis'][] = array($key, $value['currency_code']);
		}
		
		} elseif ($filter_report == 'customer_group') {

		foreach ($chart_data['results'] as $key => $value) {
			$json['order']['data'][] = array($key, $value['orders']);
			$json['customer']['data'][] = array($key, $value['customers']);
			$json['product']['data'][] = array($key, $value['products']);
			$json['sales']['data'][] = array($key, $value['total_sales_raw']);
			$json['costs']['data'][] = array($key, $value['total_costs_raw']);
			$json['profit']['data'][] = array($key, $value['total_profit_raw']);
			$json['xaxis'][] = array($key, $value['customer_group']);
		}

		} elseif ($filter_report == 'country') {

		foreach ($chart_data['results'] as $result) {
			$json[strtolower($result['iso_code'])] = array(
				'orders'  => $result['orders'],
				'sales'   => $this->currency->format($result['total_sales_raw'], $this->config->get('config_currency')),
				'costs'   => $this->currency->format($result['total_costs_raw'], $this->config->get('config_currency')),
				'profit'  => $this->currency->format($result['total_profit_raw'], $this->config->get('config_currency'))
			);
		}	
		
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}