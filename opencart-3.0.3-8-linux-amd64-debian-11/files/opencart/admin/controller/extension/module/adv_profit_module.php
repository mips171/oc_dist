<?php
static $config = NULL;
static $log = NULL;

// Error Handler
function error_handler_for_stock_export($errno, $errstr, $errfile, $errline) {
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

function fatal_error_shutdown_handler_for_stock_export() {
	$last_error = error_get_last();
	if ($last_error && $last_error['type'] === E_ERROR) {
		// fatal error
		error_handler_for_stock_export(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
	}
}

class ControllerExtensionModuleAdvProfitModule extends Controller {

	// Template Switcher by JNeuhoff (https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=31589)
	// Copyright (c) 2015-2017 by MHCCORP.COM
	// GNU General Public License Version 3 (https://www.gnu.org/licenses/gpl.html)
	private $error = array();
	protected $template_engines = array();

	public function __construct($registry) {
		// Call parent constructor
		parent::__construct($registry);

		if (!$this->config->get('module_adv_profit_module_status')) {
			return;
		}
		
		// Find all available template engines
		$template_engines = array();
		$files = glob(DIR_SYSTEM . 'library/template/*.php');
		if ($files) {
			foreach ($files as $file) {
				if (is_file($file)) {
					$template_engine = basename($file,'.php');
					$template_engines[] = $template_engine;
				}
			}
		}
		$this->template_engines = $template_engines;
	}
	
	public function index() {
		$this->load->language('extension/module/adv_profit_module');
		
		$this->document->setTitle($this->language->get('heading_title_main'));

		$this->document->addScript('view/javascript/bootstrap/js/bootstrap-filestyle.min.js');
	  	$this->document->addScript('view/javascript/bootstrap/js/bootstrap-multiselect.js');
	    $this->document->addStyle('view/javascript/bootstrap/css/bootstrap-multiselect.css');

		$this->load->model('extension/module/adv_profit_module');
		
		$data['categories'] = $this->model_extension_module_adv_profit_module->getProductsCategories(0);

		if (isset($this->request->get['filter_category'])) {
			$data['filter_category'] = explode('_', $this->request->get['filter_category']);
		} else {
			$data['filter_category'] = array();
		}

		$this->load->model('catalog/manufacturer');
		$data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();
		
		if (isset($this->request->get['filter_manufacturer'])) {
			$data['filter_manufacturer'] = explode('_', $this->request->get['filter_manufacturer']);
		} else {
			$data['filter_manufacturer'] = array();
		}	

		$data['suppliers'] = $this->model_extension_module_adv_profit_module->getProductsSuppliers();
		
		if (isset($this->request->get['filter_supplier'])) {
			$data['filter_supplier'] = explode('_', $this->request->get['filter_supplier']);
		} else {
			$data['filter_supplier'] = array();
		}	
		
		if (isset($this->request->get['filter_status'])) {
			$data['filter_status'] = explode('_', $this->request->get['filter_status']);
		} else {
			$data['filter_status'] = NULL;
		}	

		if (isset($this->request->get['filter_rounding'])) {
			$data['filter_rounding'] = $this->request->get['filter_rounding'];
		} else {
			$data['filter_rounding'] = 'RD';
		}
		
		if (isset($this->request->get['export'])) {
			$export = $this->request->get['export'] ;
		} else {
			$export = '';
		}
		
		$this->load->model('setting/setting');
		
		if (!isset($_POST['acode']) && !isset($_POST['deact']) && ($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if ((isset($this->request->files['upload'])) && (is_uploaded_file($this->request->files['upload']['tmp_name']))) {
				$file = $this->request->files['upload']['tmp_name'];
				if ($this->model_extension_module_adv_profit_module->upload($file)) {
					$this->session->data['success'] = $this->language->get('text_upload_success');
					$this->response->redirect($this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true));
				} else {
					$this->session->data['warning'] = $this->language->get('error_upload');
					$this->response->redirect($this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true));
 				}
			}	
			
			if (isset($this->request->post['adv_payment_cost_type'])) {
				$this->request->post['adv_payment_cost_type'] = serialize($this->request->post['adv_payment_cost_type']);
			}
			
			$this->model_setting_setting->editSetting('adv', $this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['heading_title_main'] = $this->language->get('heading_title_main');
		$data['text_edit'] = $this->language->get('text_edit');

		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_none'] = $this->language->get('text_none');
		
		$data['tab_product_cost'] = $this->language->get('tab_product_cost');
		$data['tab_payment_cost'] = $this->language->get('tab_payment_cost');		
		$data['tab_shipping_cost'] = $this->language->get('tab_shipping_cost');
		$data['tab_extra_cost'] = $this->language->get('tab_extra_cost');
		$data['tab_general'] = $this->language->get('tab_general');	
		$data['tab_settings'] = $this->language->get('tab_settings');	
		$data['tab_about'] = $this->language->get('tab_about');

		$data['text_import_export_note'] = $this->language->get('text_import_export_note');
		$data['text_price_rounding'] = $this->language->get('text_price_rounding');		
		$data['text_all'] = $this->language->get('text_all');
		$data['text_all_categories'] = $this->language->get('text_all_categories');
		$data['text_all_manufacturers'] = $this->language->get('text_all_manufacturers');
		$data['text_all_suppliers'] = $this->language->get('text_all_suppliers');
		$data['text_all_statuses'] = $this->language->get('text_all_statuses');
		$data['text_selected'] = $this->language->get('text_selected');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');		
		$data['text_export'] = $this->language->get('text_export');		
		$data['text_import'] = $this->language->get('text_import');		
		$data['text_set_order_product_cost_confirm'] = $this->language->get('text_set_order_product_cost_confirm');
		$data['text_set_order_payment_cost_confirm'] = $this->language->get('text_set_order_payment_cost_confirm');
		$data['text_set_order_shipping_cost_confirm'] = $this->language->get('text_set_order_shipping_cost_confirm');
		$data['text_set_order_extra_cost_confirm'] = $this->language->get('text_set_order_extra_cost_confirm');
		$data['text_set_set_order_product_cost'] = $this->language->get('text_set_set_order_product_cost');
		$data['text_set_set_order_payment_cost'] = $this->language->get('text_set_set_order_payment_cost');
		$data['text_set_set_order_shipping_cost'] = $this->language->get('text_set_set_order_shipping_cost');
		$data['text_set_set_order_extra_cost'] = $this->language->get('text_set_set_order_extra_cost');
		$data['text_plist_settings'] = $this->language->get('text_plist_settings');
		$data['text_adv_plist_category_status'] = $this->language->get('text_adv_plist_category_status');
		$data['text_adv_plist_manufacturer_status'] = $this->language->get('text_adv_plist_manufacturer_status');
		$data['text_adv_plist_price_tax_status'] = $this->language->get('text_adv_plist_price_tax_status');
		$data['text_adv_plist_cost_status'] = $this->language->get('text_adv_plist_cost_status');		
		$data['text_adv_plist_profit_status'] = $this->language->get('text_adv_plist_profit_status');
		$data['text_adv_plist_profit_margin_status'] = $this->language->get('text_adv_plist_profit_margin_status');
		$data['text_adv_plist_profit_markup_status'] = $this->language->get('text_adv_plist_profit_markup_status');
		$data['text_adv_plist_supplier_status'] = $this->language->get('text_adv_plist_supplier_status');
		$data['text_adv_plist_sold_status'] = $this->language->get('text_adv_plist_sold_status');
		$data['text_sold_order_status'] = $this->language->get('text_sold_order_status');
		$data['text_local_settings'] = $this->language->get('text_local_settings');
		$data['text_format_date'] = $this->language->get('text_format_date');
		$data['text_format_date_eu'] = $this->language->get('text_format_date_eu');
		$data['text_format_date_us'] = $this->language->get('text_format_date_us');
		$data['text_format_hour'] = $this->language->get('text_format_hour');
		$data['text_format_hour_24'] = $this->language->get('text_format_hour_24');
		$data['text_format_hour_12'] = $this->language->get('text_format_hour_12');			
		$data['text_pedit_settings'] = $this->language->get('text_pedit_settings');
		$data['text_price_incl_tax'] = $this->language->get('text_price_incl_tax');
		$data['text_tax_store_based'] = $this->language->get('text_tax_store_based');
		$data['text_tax_country'] = $this->language->get('text_tax_country');
		$data['text_tax_zone'] = $this->language->get('text_tax_zone');
		$data['text_help_request'] = $this->language->get('text_help_request');
		$data['text_asking_help'] = $this->language->get('text_asking_help');		
		$data['text_terms'] = $this->language->get('text_terms');	
		
		$data['error_permission'] = $this->language->get('error_permission');			
		
		$data['entry_import_export'] = $this->language->get('entry_import_export');
		$data['entry_category'] = $this->language->get('entry_category');
		$data['entry_manufacturer'] = $this->language->get('entry_manufacturer');	
		$data['entry_supplier'] = $this->language->get('entry_supplier');
		$data['entry_prod_status'] = $this->language->get('entry_prod_status');
		$data['entry_set_order_product_cost'] = $this->language->get('entry_set_order_product_cost');	
		$data['entry_set_order_payment_cost'] = $this->language->get('entry_set_order_payment_cost');	
		$data['entry_set_order_shipping_cost'] = $this->language->get('entry_set_order_shipping_cost');	
		$data['entry_set_order_extra_cost'] = $this->language->get('entry_set_order_extra_cost');	
		
		$data['entry_adv_payment_cost_status'] = $this->language->get('entry_adv_payment_cost_status');		
		$data['entry_adv_payment_cost_total'] = $this->language->get('entry_adv_payment_cost_total');
		$data['entry_adv_payment_cost_payment_type'] = $this->language->get('entry_adv_payment_cost_payment_type');
		$data['entry_adv_payment_cost_percentage'] = $this->language->get('entry_adv_payment_cost_percentage');
		$data['entry_adv_payment_cost_fixed_fee'] = $this->language->get('entry_adv_payment_cost_fixed_fee');
		$data['entry_adv_payment_cost_geo_zone'] = $this->language->get('entry_adv_payment_cost_geo_zone');
		
		$data['entry_adv_shipping_cost_status'] = $this->language->get('entry_adv_shipping_cost_status');
		$data['entry_adv_shipping_cost_total'] = $this->language->get('entry_adv_shipping_cost_total');
		$data['entry_adv_shipping_cost_rate'] = $this->language->get('entry_adv_shipping_cost_rate');
		$data['entry_status'] = $this->language->get('entry_status');
		
		$data['entry_adv_extra_cost_status'] = $this->language->get('entry_adv_extra_cost_status');
		$data['entry_adv_extra_cost'] = $this->language->get('entry_adv_extra_cost');		

		$data['button_documentation'] = $this->language->get('button_documentation');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_export'] = $this->language->get('button_export');
		$data['button_import'] = $this->language->get('button_import');		
		$data['button_set_order_product_cost'] = $this->language->get('button_set_order_product_cost');
		$data['button_set_order_payment_cost'] = $this->language->get('button_set_order_payment_cost');
		$data['button_set_order_shipping_cost'] = $this->language->get('button_set_order_shipping_cost');
		$data['button_set_order_extra_cost'] = $this->language->get('button_set_order_extra_cost');
		$data['button_add_payment'] = $this->language->get('button_add_payment');
		$data['button_remove_payment'] = $this->language->get('button_remove_payment');
		
		$data['column_prod_id'] = $this->language->get('column_prod_id');
		$data['column_option_id'] = $this->language->get('column_option_id');		
		$data['column_name'] = $this->language->get('column_name');
		$data['column_option'] = $this->language->get('column_option');
		$data['column_sku'] = $this->language->get('column_sku');
		$data['column_model'] = $this->language->get('column_model');
		$data['column_supplier_id'] = $this->language->get('column_supplier_id');
		$data['column_subtract'] = $this->language->get('column_subtract');
		$data['column_stock_quantity'] = $this->language->get('column_stock_quantity');		
		$data['column_restock_quantity'] = $this->language->get('column_restock_quantity');
		$data['column_new_quantity'] = $this->language->get('column_new_quantity');
		$data['column_costing_method'] = $this->language->get('column_costing_method');		
		$data['column_cost'] = $this->language->get('column_cost');
		$data['column_restock_cost'] = $this->language->get('column_restock_cost');
		$data['column_new_cost'] = $this->language->get('column_new_cost');
		$data['column_price'] = $this->language->get('column_price');	
		$data['column_cost_multiplier'] = $this->language->get('column_cost_multiplier');	
		$data['column_price_multiplier'] = $this->language->get('column_price_multiplier');
		$data['column_set_price'] = $this->language->get('column_set_price');		
		$data['column_new_price'] = $this->language->get('column_new_price');
		$data['column_profit'] = $this->language->get('column_profit');
		$data['column_comment'] = $this->language->get('column_comment');
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$data['url_set_order_product_cost'] = $this->url->link('extension/module/adv_profit_module/SetOrderProductCost', 'user_token=' . $this->session->data['user_token']);
		$data['url_set_order_payment_cost'] = $this->url->link('extension/module/adv_profit_module/SetOrderPaymentCost', 'user_token=' . $this->session->data['user_token']);
		$data['url_set_order_shipping_cost'] = $this->url->link('extension/module/adv_profit_module/SetOrderShippingCost', 'user_token=' . $this->session->data['user_token']);
		$data['url_set_order_extra_cost'] = $this->url->link('extension/module/adv_profit_module/SetOrderExtraCost', 'user_token=' . $this->session->data['user_token']);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);			
		} else {
			$data['success'] = '';
		}

		if (isset($this->session->data['warning'])) {
			$data['warning'] = $this->session->data['warning'];
			unset($this->session->data['warning']);			
		} else {
			$data['warning'] = '';
		}
		
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

  		$data['breadcrumbs'] = array();
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_extension'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title_main'),
			'href'      => $this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true)
   		);

		$data['action'] = $this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
	  if ($export != 'xls'){
		$data['payments'] = array();
		
		$this->load->model('setting/extension');

		if (isset($this->request->post['adv_payment_cost_status'])) {
			$data['adv_payment_cost_status'] = $this->request->post['adv_payment_cost_status'];
		} else {
			$data['adv_payment_cost_status'] = $this->config->get('adv_payment_cost_status');
		}
		
		$selected_payment_types = $this->config->get('adv_payment_cost_type') !== null ? unserialize($this->config->get('adv_payment_cost_type')) : '';
		
		if (isset($this->request->post['adv_payment_cost_type'])) {
			$data['adv_payment_cost_types'] = $this->request->post['adv_payment_cost_type'];
		} elseif (isset($selected_payment_types)) {
			$data['adv_payment_cost_types'] = $selected_payment_types;
		} else { 	
			$data['adv_payment_cost_types'] = array();
		}
		
		$this->load->model('localisation/geo_zone');
		$data['pc_geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$payment_types = $this->model_setting_extension->getInstalled('payment');

		foreach ($payment_types as $key => $code) {
			$this->load->language('extension/payment/' . $code);
				$data['payment_types'][] = array(
				'name'       => $this->language->get('heading_title'),
				'paymentkey' => $code
				);
		}

		if (isset($this->request->post['adv_shipping_cost_weight_status'])) {
			$data['adv_shipping_cost_weight_status'] = $this->request->post['adv_shipping_cost_weight_status'];
		} else {
			$data['adv_shipping_cost_weight_status'] = $this->config->get('adv_shipping_cost_weight_status');
		}
		
		$sc_geo_zones = $this->model_localisation_geo_zone->getGeoZones();
		$data['sc_geo_zones'] = $sc_geo_zones;
		
		foreach ($sc_geo_zones as $sc_geo_zone) {
			if (isset($this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total']) && $this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total'] != '') {
				$data['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total'] = $this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total'];
			} elseif (isset($this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total']) && $this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total'] == '') {
				$data['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total'] = '';				
			} else {
				$data['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total'] = $this->config->get('adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total');
			}	

			if (isset($this->error['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total'])) {
				$data['error_shipping_cost_' . $sc_geo_zone['geo_zone_id'] . '_total'] = $this->error['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total'];
			} else {
				$data['error_shipping_cost_' . $sc_geo_zone['geo_zone_id'] . '_total'] = '';
			}	
			
			if (isset($this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate'])) {
				$data['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate'] = $this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate'];
			} else {
				$data['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate'] = $this->config->get('adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate');
			}		

			if (isset($this->error['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate'])) {
				$data['error_shipping_cost_' . $sc_geo_zone['geo_zone_id'] . '_rate'] = $this->error['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate'];
			} else {
				$data['error_shipping_cost_' . $sc_geo_zone['geo_zone_id'] . '_rate'] = '';
			}	
		
			if (isset($this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_status'])) {
				$data['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_status'] = $this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_status'];
			} else {
				$data['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_status'] = $this->config->get('adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_status');
			}
			
		}

		if (isset($this->request->post['adv_extra_cost_status'])) {
			$data['adv_extra_cost_status'] = $this->request->post['adv_extra_cost_status'];
		} else {
			$data['adv_extra_cost_status'] = $this->config->get('adv_extra_cost_status');
		}

		if (isset($this->request->post['adv_extra_cost'])) {
			$data['adv_extra_cost'] = $this->request->post['adv_extra_cost'];
		} else {
			$data['adv_extra_cost'] = $this->config->get('adv_extra_cost');
		}	

		if (isset($this->error['adv_extra_cost'])) {
			$data['error_extra_cost'] = $this->error['adv_extra_cost'];
		} else {
			$data['error_extra_cost'] = '';
		}	

		if (isset($this->request->post['adv_plist_category_status'])) {
			$data['adv_plist_category_status'] = $this->request->post['adv_plist_category_status'];
		} else {
			$data['adv_plist_category_status'] = $this->config->get('adv_plist_category_status');
		}

		if (isset($this->request->post['adv_plist_manufacturer_status'])) {
			$data['adv_plist_manufacturer_status'] = $this->request->post['adv_plist_manufacturer_status'];
		} else {
			$data['adv_plist_manufacturer_status'] = $this->config->get('adv_plist_manufacturer_status');
		}
		if (isset($this->request->post['adv_plist_price_tax_status'])) {
			$data['adv_plist_price_tax_status'] = $this->request->post['adv_plist_price_tax_status'];
		} else {
			$data['adv_plist_price_tax_status'] = $this->config->get('adv_plist_price_tax_status');
		}
		
		if (isset($this->request->post['adv_plist_cost_status'])) {
			$data['adv_plist_cost_status'] = $this->request->post['adv_plist_cost_status'];
		} else {
			$data['adv_plist_cost_status'] = $this->config->get('adv_plist_cost_status');
		}

		if (isset($this->request->post['adv_plist_profit_status'])) {
			$data['adv_plist_profit_status'] = $this->request->post['adv_plist_profit_status'];
		} else {
			$data['adv_plist_profit_status'] = $this->config->get('adv_plist_profit_status');
		}
		
		if (isset($this->request->post['adv_plist_profit_margin_status'])) {
			$data['adv_plist_profit_margin_status'] = $this->request->post['adv_plist_profit_margin_status'];
		} else {
			$data['adv_plist_profit_margin_status'] = $this->config->get('adv_plist_profit_margin_status');
		}
		
		if (isset($this->request->post['adv_plist_profit_markup_status'])) {
			$data['adv_plist_profit_markup_status'] = $this->request->post['adv_plist_profit_markup_status'];
		} else {
			$data['adv_plist_profit_markup_status'] = $this->config->get('adv_plist_profit_markup_status');
		}
		
		if (isset($this->request->post['adv_plist_supplier_status'])) {
			$data['adv_plist_supplier_status'] = $this->request->post['adv_plist_supplier_status'];
		} else {
			$data['adv_plist_supplier_status'] = $this->config->get('adv_plist_supplier_status');
		}

		if (isset($this->request->post['adv_plist_sold_status'])) {
			$data['adv_plist_sold_status'] = $this->request->post['adv_plist_sold_status'];
		} else {
			$data['adv_plist_sold_status'] = $this->config->get('adv_plist_sold_status');
		}
		
		$data['order_statuses'] = $this->model_extension_module_adv_profit_module->getOrderStatuses(); 	
		
		if (isset($this->request->post['adv_sold_order_status'])) {
			$data['adv_sold_order_status'] = $this->request->post['adv_sold_order_status'];
		} else {
			$data['adv_sold_order_status'] = $this->config->get('adv_sold_order_status');
		}
		
		if (isset($this->request->post['adv_date_format'])) {
			$data['adv_date_format'] = $this->request->post['adv_date_format'];
		} else {
			$data['adv_date_format'] = $this->config->get('adv_date_format');
		}

		if (isset($this->request->post['adv_hour_format'])) {
			$data['adv_hour_format'] = $this->request->post['adv_hour_format'];
		} else {
			$data['adv_hour_format'] = $this->config->get('adv_hour_format');
		}
		if (isset($this->request->post['adv_price_tax'])) {
			$data['adv_price_tax'] = $this->request->post['adv_price_tax'];
		} else {
			$data['adv_price_tax'] = $this->config->get('adv_price_tax');
		}
		
		if (isset($this->request->post['adv_price_tax_store_based'])) {
			$data['adv_price_tax_store_based'] = $this->request->post['adv_price_tax_store_based'];
		} else {
			$data['adv_price_tax_store_based'] = $this->config->get('adv_price_tax_store_based');
		}
		
		if (isset($this->request->post['adv_price_tax_country_id'])) {
			$data['adv_price_tax_country_id'] = $this->request->post['adv_price_tax_country_id'];
		} else {
			$data['adv_price_tax_country_id'] = $this->config->get('adv_price_tax_country_id');
		}

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();
		
		if (isset($this->request->post['adv_price_tax_zone_id'])) {
			$data['adv_price_tax_zone_id'] = $this->request->post['adv_price_tax_zone_id'];
		} else {
			$data['adv_price_tax_zone_id'] = $this->config->get('adv_price_tax_zone_id');
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/adv_profit_module', $data));
		
	  } elseif ($export == 'xls') {	  
		$this->model_extension_module_adv_profit_module->createXLS($data['filter_category'], $data['filter_manufacturer'], $data['filter_supplier'], $data['filter_status'], $data['filter_rounding']);
	  }
	}
	
	protected function validate() {
		$data['error_numeric_value'] = $this->language->get('error_numeric_value');
		$data['error_shipping_cost_total'] = $this->language->get('error_shipping_cost_total');
		$data['error_shipping_cost_rate'] = $this->language->get('error_shipping_cost_rate');
		$data['error_extra_cost'] = $this->language->get('error_extra_cost');
		
		if (!$this->user->hasPermission('modify', 'extension/module/adv_profit_module')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		$this->load->model('localisation/geo_zone');
		$sc_geo_zones = $this->model_localisation_geo_zone->getGeoZones();
		
		foreach ($sc_geo_zones as $sc_geo_zone) {
    		if (isset($this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total']) && (!preg_match('/^[0-9.]*$/', $this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total']))) {
      			$this->error['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total'] = $this->language->get('error_shipping_cost_total');
    		}

    		if (isset($this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total']) && ($this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_status'] == '1') && (!preg_match('/^[0-9.]/', $this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total']))) {
      			$this->error['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_total'] = $this->language->get('error_shipping_cost_total');
    		}
		
    		if (isset($this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate']) && (!preg_match('/^[0-9,.:]*$/', $this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate']))) {
      			$this->error['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate'] = $this->language->get('error_shipping_cost_rate');
    		}
			
    		if (isset($this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate']) && ($this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_status'] == '1') && (!preg_match('/^[0-9,.:]/', $this->request->post['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate']))) {
      			$this->error['adv_shipping_cost_weight_' . $sc_geo_zone['geo_zone_id'] . '_rate'] = $this->language->get('error_shipping_cost_rate');
    		}					
		}

    	if (isset($this->request->post['adv_extra_cost']) && (!preg_match('/^[0-9,.:]*$/', $this->request->post['adv_extra_cost']))) {
      		$this->error['adv_extra_cost'] = $this->language->get('error_extra_cost');
    	}
			
    	if (isset($this->request->post['adv_extra_cost']) && ($this->request->post['adv_extra_cost_status'] == '1') && (!preg_match('/^[0-9,.:]/', $this->request->post['adv_extra_cost']))) {
      		$this->error['adv_extra_cost'] = $this->language->get('error_extra_cost');
    	}
			
		return !$this->error;	
	}
	
	public function install() {
		// Add indexes
		$query = $this->db->query("SHOW KEYS FROM `" . DB_PREFIX . "order_product` WHERE Key_name = 'product_id';");
			if (!$query->rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_product` ADD INDEX (product_id);");
			}
		$query = $this->db->query("SHOW KEYS FROM `" . DB_PREFIX . "order_option` WHERE Key_name = 'order_id';");
			if (!$query->rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_option` ADD INDEX (order_id);");
			}
		$query = $this->db->query("SHOW KEYS FROM `" . DB_PREFIX . "order_history` WHERE Key_name = 'order_id';");
			if (!$query->rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_history` ADD INDEX (order_id);");
			}
		$query = $this->db->query("SHOW KEYS FROM `" . DB_PREFIX . "order` WHERE Key_name = 'customer_id';");
			if (!$query->rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD INDEX (customer_id);");
			}
		$query = $this->db->query("SHOW KEYS FROM `" . DB_PREFIX . "return` WHERE Key_name = 'order_id';");
			if (!$query->rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "return` ADD INDEX (order_id);");
			}
		$query = $this->db->query("SHOW KEYS FROM `" . DB_PREFIX . "return` WHERE Key_name = 'product_id';");
			if (!$query->rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "return` ADD INDEX (product_id);");
			}
		$query = $this->db->query("SHOW KEYS FROM `" . DB_PREFIX . "customer_reward` WHERE Key_name = 'order_id';");
			if (!$query->rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer_reward` ADD INDEX (order_id);");
			}
		$query = $this->db->query("SHOW KEYS FROM `" . DB_PREFIX . "customer_activity` WHERE Key_name != 'PRIMARY';");
			if (!$query->rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer_activity` ADD INDEX (customer_id);");
			}
			
		// Add tables
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "product_cost'");
        	if (!$query->num_rows) {
            	$this->db->query("
                	CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_cost` (
					  `product_cost_id` int(11) NOT NULL AUTO_INCREMENT,
					  `product_id` int(11) NOT NULL,
					  `supplier_id` int(11) NOT NULL DEFAULT '0',
					  `cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `cost_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `cost_percentage` decimal(15,2) NOT NULL DEFAULT '0.00',
					  `cost_additional` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `costing_method` int(1) NOT NULL,
					  PRIMARY KEY (`product_cost_id`), 
					  INDEX `product_id` (`product_id`) 
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            	");
			} 

		$pc = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_cost ");
		if (!$pc->rows) {
			$query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product`");
			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_cost SET product_id = '" . (int)$result['product_id'] . "', supplier_id = '0', cost = '0.0000', cost_amount = '0.0000', cost_percentage = '0.00', cost_additional = '0.0000', costing_method = '0'");
			}
        } else {	
			$query_product = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product` p WHERE p.product_id NOT IN (SELECT p.product_id FROM " . DB_PREFIX . "product_cost pc, " . DB_PREFIX . "product p WHERE pc.product_id = p.product_id)");
			foreach ($query_product->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_cost SET product_id = '" . (int)$result['product_id'] . "', supplier_id = '0', cost = '0.0000', cost_amount = '0.0000', cost_percentage = '0.00', cost_additional = '0.0000', costing_method = '0'");
			}
		}
		
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "product_option_cost'");
        	if (!$query->num_rows) {
            	$this->db->query("
                	CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_option_cost` (
					  `product_option_cost_id` int(11) NOT NULL AUTO_INCREMENT,
					  `product_option_value_id` int(11) NOT NULL,
					  `product_id` int(11) NOT NULL,
					  `cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `cost_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `cost_prefix` varchar(1) NOT NULL,
					  `costing_method` int(1) NOT NULL,
					  `sku` varchar(64) NOT NULL,
					  PRIMARY KEY (`product_option_cost_id`), 
					  INDEX `product_option_value_id` (`product_option_value_id`) 
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            	");
			} 

		$poc = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_cost ");
		if (!$pc->rows) {
			$query = $this->db->query("SELECT product_option_value_id, product_id FROM `" . DB_PREFIX . "product_option_value`");
			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_cost SET product_option_value_id = '" . (int)$result['product_option_value_id'] . "', product_id = '" . (int)$result['product_id'] . "', cost = '0.0000', cost_amount = '0.0000', cost_prefix = '', costing_method = '0', sku = ''");
			}
        } else {
			$query_option = $this->db->query("SELECT pov.product_option_value_id, pov.product_id FROM `" . DB_PREFIX . "product_option_value` pov WHERE pov.product_option_value_id NOT IN (SELECT pov.product_option_value_id FROM " . DB_PREFIX . "product_option_cost poc, " . DB_PREFIX . "product_option_value pov WHERE poc.product_option_value_id = pov.product_option_value_id)");
			foreach ($query_option->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_cost SET product_option_value_id = '" . (int)$result['product_option_value_id'] . "', product_id = '" . (int)$result['product_id'] . "', cost = '0.0000', cost_amount = '0.0000', cost_prefix = '', costing_method = '0', sku = ''");
			}
		}
		
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "order_product_cost'");
        	if (!$query->num_rows) {
            	$this->db->query("
                	CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "order_product_cost` (
					  `order_product_cost_id` int(11) NOT NULL AUTO_INCREMENT,
					  `order_product_id` int(11) NOT NULL,
					  `order_id` int(11) NOT NULL,
					  `product_id` int(11) NOT NULL,
					  `supplier_id` int(11) NOT NULL DEFAULT '0',
					  `cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `base_price` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  PRIMARY KEY (`order_product_cost_id`), 
					  INDEX `order_product_id` (`order_product_id`) 
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            	");
			} 

		$opc = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product_cost ");
		if (!$opc->rows) {
			$query = $this->db->query("SELECT op.order_product_id, op.order_id, op.product_id, op.price FROM `" . DB_PREFIX . "order_product` op, `" . DB_PREFIX . "order` o WHERE o.order_id = op.order_id AND o.order_status_id > 0");
			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_product_cost SET order_product_id = '" . (int)$result['order_product_id'] . "', order_id = '" . (int)$result['order_id'] . "', product_id = '" . (int)$result['product_id'] . "', supplier_id = '0', cost = '0.0000', base_price = '" . (float)$result['price'] . "'");
			}
		} else {	
			$query_order_product = $this->db->query("SELECT op.order_product_id, op.order_id, op.product_id, op.price FROM `" . DB_PREFIX . "order_product` op, `" . DB_PREFIX . "order` o WHERE o.order_id = op.order_id AND o.order_status_id > 0 AND op.order_product_id NOT IN (SELECT op.order_product_id FROM " . DB_PREFIX . "order_product_cost opc, " . DB_PREFIX . "order_product op WHERE opc.order_product_id = op.order_product_id)");
			foreach ($query_order_product->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_product_cost SET order_product_id = '" . (int)$result['order_product_id'] . "', order_id = '" . (int)$result['order_id'] . "', product_id = '" . (int)$result['product_id'] . "', supplier_id = '0', cost = '0.0000', base_price = '" . (float)$result['price'] . "'");
			}
		}
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_product_cost` ADD INDEX (order_id);");
		
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "order_cost'");
        	if (!$query->num_rows) {
            	$this->db->query("
                	CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "order_cost` (
					  `order_cost_id` int(11) NOT NULL AUTO_INCREMENT,
					  `order_id` int(11) NOT NULL,
					  `payment_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `shipping_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `extra_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  PRIMARY KEY (`order_cost_id`), 
					  INDEX `order_id` (`order_id`) 
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            	");
			} 

		$oc = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_cost ");
		if (!$oc->rows) {
			$query = $this->db->query("SELECT order_id FROM `" . DB_PREFIX . "order` WHERE order_status_id > 0");
			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_cost SET order_id = '" . (int)$result['order_id'] . "', payment_cost = '0.0000', shipping_cost = '0.0000', extra_cost = '0.0000'");
			}
        } else {	
			$query_order = $this->db->query("SELECT o.order_id FROM `" . DB_PREFIX . "order` o WHERE order_status_id > 0 AND o.order_id NOT IN (SELECT oc.order_id FROM " . DB_PREFIX . "order_cost oc)");
			foreach ($query_order->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_cost SET order_id = '" . (int)$result['order_id'] . "', payment_cost = '0.0000', shipping_cost = '0.0000', extra_cost = '0.0000'");
			}
		}
		
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "return_cost'");
        	if (!$query->num_rows) {
            	$this->db->query("
                	CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "return_cost` (
					  `return_cost_id` int(11) NOT NULL AUTO_INCREMENT,
					  `return_id` int(11) NOT NULL,
					  `order_id` int(11) NOT NULL,
					  `cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  PRIMARY KEY (`return_cost_id`), 
					  INDEX `return_id` (`return_id`) 
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
            	");
			} 

		$rc = $this->db->query("SELECT * FROM " . DB_PREFIX . "return_cost ");
		if (!$rc->rows) {
			$query = $this->db->query("SELECT return_id, order_id FROM `" . DB_PREFIX . "return`");
			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "return_cost SET return_id = '" . (int)$result['return_id'] . "', order_id = '" . (int)$result['order_id'] . "', cost = '0.0000'");
			}
        } else {	
			$query_return = $this->db->query("SELECT r.return_id, r.order_id FROM `" . DB_PREFIX . "return` r WHERE r.return_id NOT IN (SELECT r.return_id FROM " . DB_PREFIX . "return_cost rc, `" . DB_PREFIX . "return` r WHERE rc.return_id = r.return_id)");
			foreach ($query_return->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "return_cost SET return_id = '" . (int)$result['return_id'] . "', order_id = '" . (int)$result['order_id'] . "', cost = '0.0000'");
			}
		}
		
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "product_stock_history'");
        	if (!$query->num_rows) {
            	$this->db->query("
                	CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_stock_history` (
					  `product_stock_history_id` int(11) NOT NULL AUTO_INCREMENT,
					  `product_id` int(11) NOT NULL,
					  `restock_quantity` int(4) NOT NULL DEFAULT '0',
					  `stock_quantity` int(4) NOT NULL DEFAULT '0',
					  `supplier_id` int(11) NOT NULL DEFAULT '0',
					  `costing_method` int(1) NOT NULL,
					  `restock_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `comment` text NOT NULL DEFAULT '',
					  `date_added` datetime,
					  PRIMARY KEY (`product_stock_history_id`), 
					  INDEX `product_id` (`product_id`) 
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
            	");
			} 

		$psh = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_stock_history ");
		if (!$psh->rows) {
			$query = $this->db->query("SELECT product_id, quantity, price FROM " . DB_PREFIX . "product ");
			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_stock_history SET product_id = '" . (int)$result['product_id'] . "', restock_quantity = '0', stock_quantity = '" . (int)$result['quantity'] . "', costing_method = '0', restock_cost = '0.0000', cost = '0.0000', price = '" . (float)$result['price'] . "', comment = 'Initial Stock Quantity', date_added = NOW()");
			}
		}
		
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "product_option_stock_history'");
        	if (!$query->num_rows) {
            	$this->db->query("
                	CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_option_stock_history` (
					  `product_option_stock_history_id` int(11) NOT NULL AUTO_INCREMENT,
					  `product_option_id` int(11) NOT NULL,
					  `product_id` int(11) NOT NULL,
					  `option_id` int(11) NOT NULL,
					  `option_value_id` int(11) NOT NULL,
					  `restock_quantity` int(4) NOT NULL DEFAULT '0',
					  `stock_quantity` int(4) NOT NULL DEFAULT '0',
					  `supplier_id` int(11) NOT NULL DEFAULT '0',
					  `costing_method` int(1) NOT NULL,
					  `restock_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
					  `comment` text NOT NULL DEFAULT '',
					  `date_added` datetime,
					  PRIMARY KEY (`product_option_stock_history_id`), 
					  INDEX `product_id` (`product_id`) 
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
            	");
			} 

		$posh = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_stock_history ");
		if (!$posh->rows) {
			$query = $this->db->query("SELECT product_option_id, product_id, option_id, option_value_id, quantity, price FROM " . DB_PREFIX . "product_option_value ");
			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_stock_history SET product_option_id = '" . (int)$result['product_option_id'] . "', product_id = '" . (int)$result['product_id'] . "', option_id = '" . (int)$result['option_id'] . "', option_value_id = '" . (int)$result['option_value_id'] . "', restock_quantity = '0', stock_quantity = '" . (int)$result['quantity'] . "', costing_method = '0', restock_cost = '0.0000', cost = '0.0000', price = '" . (float)$result['price'] . "', comment = 'Initial Stock Quantity', date_added = NOW()");
			}
		}
		
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "supplier'");
        	if (!$query->num_rows) {
            	$this->db->query("
				  CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "supplier` (
  					`supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  					`name` varchar(64) NOT NULL,
  					`website` varchar(255) NOT NULL,
  					`email` varchar(96) NOT NULL,
  					`telephone` varchar(32) NOT NULL,
  					`fax` varchar(32) NOT NULL,
  					`company` varchar(40) NOT NULL,					
  					`address_1` varchar(128) NOT NULL,
  					`address_2` varchar(128) NOT NULL,
  					`city` varchar(128) NOT NULL,
  					`postcode` varchar(10) NOT NULL,
  					`country_id` int(11) NOT NULL DEFAULT '0',
  					`zone_id` int(11) NOT NULL DEFAULT '0',
  					`status` tinyint(1) NOT NULL,
  					PRIMARY KEY (`supplier_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
				  ");
			} 

        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "supplier_to_store'");
        	if (!$query->num_rows) {
            	$this->db->query("
				  CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "supplier_to_store` (
  					`supplier_id` int(11) NOT NULL,
  					`store_id` int(11) NOT NULL,
  					PRIMARY KEY (`supplier_id`,`store_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;
				  ");
			}

		$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `code` = 'module_adv_profit_module', `key` = 'module_adv_profit_module_status', `value` = '1'");
		
		$this->load->model('user/user_group');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/adv_supplier');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/adv_supplier');	
		
		// add event handlers
		$this->load->model('setting/event');
		$code = 'module_adv_profit_module';
		$app = 'admin/';
		$trigger = 'view/*/before';
		$route = 'extension/module/adv_profit_module/override';
		$this->model_setting_event->addEvent( $code, $app.$trigger, $route, 1, 0 );
		$trigger = 'view/design/layout_form/before';
		$route = 'extension/module/adv_profit_module/eventViewDesignLayoutFormBefore';
		$this->model_setting_event->addEvent( $code, $app.$trigger, $route, 1, 0 );	
	}

	public function uninstall() {
		// remove event handlers
		$this->load->model('setting/event');
		$code = 'module_adv_profit_module';
		$this->model_setting_event->deleteEventByCode( $code );
	}


	// event handler for admin/view/*/before 
	public function override(&$route, &$data, &$template) {
		if (!$this->config->get('module_adv_profit_module_status')) {
			return null;
		}

		// Find the correct template_engine and template_directory for a later rendering
		foreach ($this->template_engines as $template_engine) {
			$ext = ($template_engine == 'template') ? '.tpl' : '.'.$template_engine;
			if (is_file(DIR_TEMPLATE.$route.$ext)) {
				$this->config->set( 'template_engine', $template_engine );
				$this->config->set( 'template_directory', '' );
				return null;
			} 
		}

		trigger_error("Cannot find template file for route '$route'");
		exit;
	}


	// event handler for admin/view/*/before
	public function render(&$route, &$data, &$template) {
		if (!$this->config->get('module_adv_profit_module_status')) {
			return null;
		}

		// If there is a template buffer from previous view/*/before events then we render
		if ($template) {
			$template_engine = $this->config->get('template_engine');

			if ($template_engine == 'twig') {
				// include and register Twig auto-loader
				include_once(DIR_SYSTEM . 'library/template/Twig/Autoloader.php');
				
				Twig_Autoloader::register();
						
				// specify where to look for templates
				$loader = new \Twig_Loader_Filesystem(DIR_TEMPLATE);		
				
				$config = array('autoescape' => false);
				
				if ($this->config->get('template_cache')) {
					$config['cache'] = DIR_CACHE;
				}
				
				// initialize Twig environment
				$twig = new \Twig_Environment($loader, $config);
					
				return $twig->createTemplate($template)->render($data);
			}

			// render using other template engines, such as PHP
			$template = new Template($this->registry->get('config')->get('template_engine'));
			foreach ($data as $key => $value) {
				$template->set($key, $value);
			}
			return $template->render($this->registry->get('config')->get('template_directory') . $route);		
		}
	
	}
	
	public function SetOrderProductCost() {
		if (!$this->user->hasPermission('modify', 'extension/module/adv_profit_module')) {
			$this->load->language('extension/module/adv_profit_module');			
			$this->session->data['warning'] = $this->language->get('error_permission');
			$this->response->redirect($this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true));			
		} else {
			$query_order = $this->db->query("SELECT o.order_id FROM `" . DB_PREFIX . "order` o WHERE order_status_id > 0 AND o.order_id NOT IN (SELECT oc.order_id FROM " . DB_PREFIX . "order_cost oc)");
			foreach ($query_order->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_cost SET order_id = '" . (int)$result['order_id'] . "', payment_cost = '0.0000', shipping_cost = '0.0000', extra_cost = '0.0000'");
			}
			$query_order_product = $this->db->query("SELECT op.order_product_id, op.order_id, op.product_id, op.price FROM `" . DB_PREFIX . "order_product` op, `" . DB_PREFIX . "order` o WHERE o.order_id = op.order_id AND o.order_status_id > 0 AND op.order_product_id NOT IN (SELECT op.order_product_id FROM " . DB_PREFIX . "order_product_cost opc, " . DB_PREFIX . "order_product op WHERE opc.order_product_id = op.order_product_id)");
			foreach ($query_order_product->rows as $result) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_product_cost SET order_product_id = '" . (int)$result['order_product_id'] . "', order_id = '" . (int)$result['order_id'] . "', product_id = '" . (int)$result['product_id'] . "', supplier_id = '0', cost = '0.0000', base_price = '" . (float)$result['price'] . "'");
			}
			$query_product_cost = $this->db->query("SELECT pc.product_id, pc.cost FROM `" . DB_PREFIX . "product` p, `" . DB_PREFIX . "product_cost` pc WHERE p.product_id = pc.product_id");
			foreach ($query_product_cost->rows as $result_product_cost) {			
				$this->db->query("UPDATE `" . DB_PREFIX . "order_product_cost` opc SET opc.cost = '" . (float)$result_product_cost['cost'] . "' + IFNULL((SELECT SUM(IF(poc.cost_prefix = '+',poc.cost,-poc.cost)) FROM `" . DB_PREFIX . "order_option` oo, `" . DB_PREFIX . "product_option_cost` poc WHERE opc.product_id = '" . (int)$result_product_cost['product_id'] . "' AND opc.order_product_id = oo.order_product_id AND oo.product_option_value_id = poc.product_option_value_id),0) WHERE opc.product_id = '" . (int)$result_product_cost['product_id'] . "' AND opc.cost = '0.0000' OR opc.cost IS NULL");
			}
			$this->load->language('extension/module/adv_profit_module');
			$this->session->data['success'] = $this->language->get('text_set_order_product_cost_success');	
			$this->response->redirect($this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true));
		}	
	}

	public function SetOrderPaymentCost() {
		if (!$this->user->hasPermission('modify', 'extension/module/adv_profit_module')) {
			$this->load->language('extension/module/adv_profit_module');			
			$this->session->data['warning'] = $this->language->get('error_permission');
			$this->response->redirect($this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true));			
		} else {
			$query_payment_cost = $this->db->query("SELECT order_id, payment_code, total, payment_country_id, payment_zone_id FROM `" . DB_PREFIX . "order` WHERE order_status_id > 0");
			foreach ($query_payment_cost->rows as $result_payment_cost) {
				
			  if ($this->config->get('adv_payment_cost_status') && $this->config->get('adv_payment_cost_type') && $result_payment_cost['payment_code']) {
				$getPaymentTypes = unserialize($this->config->get('adv_payment_cost_type'));
				if ($getPaymentTypes) {
				  foreach ($getPaymentTypes as $payment_type) {
					if ($result_payment_cost['payment_code'] == $payment_type['pc_paymentkey']) {	
						
						if ($result_payment_cost['total'] > $payment_type['pc_order_total']) {
								$country_id	= $result_payment_cost['payment_country_id'];
								$zone_id 	= $result_payment_cost['payment_zone_id'];

							$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$payment_type['pc_geozone'] . "' AND country_id = '" . (int)$country_id . "' AND (zone_id = '" . (int)$zone_id . "' OR zone_id = '0')");
						
							if (!$payment_type['pc_geozone']) {
								$pc_status = true;
							} elseif ($query->num_rows) {
								$pc_status = true;
							} else {
								$pc_status = false;
							}		
			
							if (($result_payment_cost['total'] > $payment_type['pc_order_total']) && ($pc_status) && ($result_payment_cost['total'] > 0)) {
								$payment_cost = ($payment_type['pc_percentage']*$result_payment_cost['total'])/100 + $payment_type['pc_fixed'];
								$this->db->query("UPDATE `" . DB_PREFIX . "order_cost` SET payment_cost = '" . $payment_cost . "' WHERE order_id = '" . (int)$result_payment_cost['order_id'] . "'");								
							}
							
						}
						
					}
				  }
				}
			  }
			
			}
			$this->load->language('extension/module/adv_profit_module');
			$this->session->data['success'] = $this->language->get('text_set_order_payment_cost_success');	
			$this->response->redirect($this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true));
		}	
	}

	public function SetOrderShippingCost() {
		if (!$this->user->hasPermission('modify', 'extension/module/adv_profit_module')) {
			$this->load->language('extension/module/adv_profit_module');			
			$this->session->data['warning'] = $this->language->get('error_permission');
			$this->response->redirect($this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true));			
		} else {
			$query_shipping_cost = $this->db->query("SELECT order_id, shipping_country_id, shipping_zone_id, total FROM `" . DB_PREFIX . "order` WHERE order_status_id > 0");
			foreach ($query_shipping_cost->rows as $result_shipping_cost) {
				
				$country_id	= $result_shipping_cost['shipping_country_id'];
				$zone_id 	= $result_shipping_cost['shipping_zone_id'];				
				$query_geo_zone = $this->db->query("SELECT geo_zone_id FROM " . DB_PREFIX . "zone_to_geo_zone WHERE country_id = '" . (int)$country_id . "' AND (zone_id = '" . (int)$zone_id . "' OR zone_id = '0')");

				if ($query_geo_zone->rows) {	
					foreach ($query_geo_zone->rows as $result_geo_zone) {
						if (($this->config->get('adv_shipping_cost_weight_status') == '1') && ($this->config->get('adv_shipping_cost_weight_' . $result_geo_zone['geo_zone_id'] . '_status') == '1') && ($this->config->get('adv_shipping_cost_weight_' . $result_geo_zone['geo_zone_id'] . '_rate') != '')) {
				
							if (($result_shipping_cost['total'] >= $this->config->get('adv_shipping_cost_weight_' . $result_geo_zone['geo_zone_id'] . '_total'))) {
								$weight = 0;
								
								$products_query = $this->db->query("SELECT p.product_id, p.shipping, p.weight, p.weight_class_id, op.order_product_id, op.product_id, op.order_id, op.quantity FROM `" . DB_PREFIX . "product` p, `" . DB_PREFIX . "order_product` op WHERE op.order_id = '" . (int)$result_shipping_cost['order_id'] . "' AND op.product_id = p.product_id AND p.shipping = '1'");

								if ($products_query->num_rows) {
									foreach ($products_query->rows as $result_product) {
										$option_weight = 0;
										
										$options_query = $this->db->query("SELECT oo.product_option_id, oo.product_option_value_id, oo.order_product_id, oo.order_id FROM `" . DB_PREFIX . "order_option` oo WHERE oo.order_id = '" . (int)$result_product['order_id'] . "' AND oo.order_product_id = '" . (int)$result_product['order_product_id'] . "'");
										
										foreach ($options_query->rows as $result_option) {
											$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, o.type FROM `" . DB_PREFIX . "product_option` po, `" . DB_PREFIX . "option` o WHERE po.product_option_id = '" . (int)$result_option['product_option_id'] . "' AND po.product_id = '" . (int)$result_product['product_id'] . "' AND po.option_id = o.option_id");
								
											if ($option_query->num_rows) {
												$option_value_query = $this->db->query("SELECT pov.option_value_id, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov WHERE pov.product_option_value_id = '" . (int)$result_option['product_option_value_id'] . "' AND pov.product_option_id = '" . (int)$result_option['product_option_id'] . "'");								

													if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio' || $option_query->row['type'] == 'image') {
														if ($option_value_query->num_rows) {
															if ($option_value_query->row['weight_prefix'] == '+') {
																$option_weight += $option_value_query->row['weight'];
															} elseif ($option_value_query->row['weight_prefix'] == '-') {
																$option_weight -= $option_value_query->row['weight'];
															}							
														}
													
													} elseif ($option_query->row['type'] == 'checkbox') {
														if ($option_value_query->num_rows) {
															if ($option_value_query->row['weight_prefix'] == '+') {
																$option_weight += $option_value_query->row['weight'];
															} elseif ($option_value_query->row['weight_prefix'] == '-') {
																$option_weight -= $option_value_query->row['weight'];
															}							
														}
													
													} elseif ($option_query->row['type'] == 'text' || $option_query->row['type'] == 'textarea' || $option_query->row['type'] == 'file' || $option_query->row['type'] == 'date' || $option_query->row['type'] == 'datetime' || $option_query->row['type'] == 'time') {
													
														$option_weight += 0;					
													}
											}
										}
										
										$weight += $this->weight->convert(($result_product['weight'] + $option_weight) * $result_product['quantity'], (int)$result_product['weight_class_id'], $this->config->get('config_weight_class_id'));										
									}
								}
								
								$rates = explode(',', $this->config->get('adv_shipping_cost_weight_' . $result_geo_zone['geo_zone_id'] . '_rate'));
				
								foreach ($rates as $rate) {
								$adv_shipping_cost_data = explode(':', $rate);
				
									if ($adv_shipping_cost_data[0] >= $weight) {
										if (isset($adv_shipping_cost_data[1])) {
											$shipping_cost = $adv_shipping_cost_data[1];
											$this->db->query("UPDATE `" . DB_PREFIX . "order_cost` SET shipping_cost = '" . $shipping_cost . "' WHERE order_id = '" . (int)$result_shipping_cost['order_id'] . "'");
										}
										break;
									}
								}
							
							}
						
						}
					}
				}
			}
			$this->load->language('extension/module/adv_profit_module');
			$this->session->data['success'] = $this->language->get('text_set_order_shipping_cost_success');	
			$this->response->redirect($this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true));
		}	
	}

	public function SetOrderExtraCost() {
		if (!$this->user->hasPermission('modify', 'extension/module/adv_profit_module')) {
			$this->load->language('extension/module/adv_profit_module');			
			$this->session->data['warning'] = $this->language->get('error_permission');
			$this->response->redirect($this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true));			
		} else {
			$query_extra_cost = $this->db->query("SELECT order_id, total FROM `" . DB_PREFIX . "order` WHERE order_status_id > 0");
			foreach ($query_extra_cost->rows as $result_extra_cost) {
				if (($this->config->get('adv_extra_cost_status') == '1') && ($this->config->get('adv_extra_cost') != '')) {
					$rates = explode(',', $this->config->get('adv_extra_cost'));
				
					foreach ($rates as $rate) {
						$adv_extra_cost_data = explode(':', $rate);
				
						if ($adv_extra_cost_data[0] >= $result_extra_cost['total']) {
							if (isset($adv_extra_cost_data[1])) {
								$extra_cost = $adv_extra_cost_data[1];
								$this->db->query("UPDATE `" . DB_PREFIX . "order_cost` SET extra_cost = '" . $extra_cost . "' WHERE order_id = '" . (int)$result_extra_cost['order_id'] . "'");
							}
							break;
						}
						
					}
						
				}				
			}
			$this->load->language('extension/module/adv_profit_module');
			$this->session->data['success'] = $this->language->get('text_set_order_extra_cost_success');	
			$this->response->redirect($this->url->link('extension/module/adv_profit_module', 'user_token=' . $this->session->data['user_token'], true));
		}	
	}	
}