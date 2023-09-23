<?php
	ini_set("memory_limit","256M");
	
	$results = $export_data['results'];
	if ($results) {
		
		if ($filter_report == 'products_purchased_with_options' or $filter_report == 'products_abandoned_orders') {
		$this->load->model('extension/report/adv_products_profit');
		$option_names = $this->model_extension_report_adv_products_profit->getOrderOptionsNames();
		}
	
		// we use our own error handler
		global $config;
		global $log;
		$config = $this->config;
		$log = $this->log;
		set_error_handler('error_handler_for_export',E_ALL);
		register_shutdown_function('fatal_error_shutdown_handler_for_export');
		
		if (!isset($_GET['cron'])) {
			// Creating a workbook
			$workbook = new Spreadsheet_Excel_Writer();
			$workbook->setTempDir(DIR_CACHE);
			$workbook->setVersion(8); // Use Excel97/2000 BIFF8 Format
		
			// sending HTTP headers
			$workbook->send('products_profit_report_'.date($this->config->get('advppp' . $user_id . '_hour_format') == '24' ? "Y-m-d_H-i-s" : "Y-m-d_h-i-s-A").'.xls');
			$worksheet =& $workbook->addWorksheet('ADV Products Profit Report');
			$worksheet->setInputEncoding('UTF-8');
			$worksheet->setZoom(90);	
		} else if (isset($_GET['cron'])) {
			$file_path_parts = explode('/', $file_save_path);
			$file_path = dirname(DIR_APPLICATION);
			foreach ($file_path_parts as $file_path_part) {
				$file_path .= '/' . $file_path_part;
				if (!file_exists($file_path)) {
					mkdir($file_path, 0755);
					if (file_exists(DIR_DOWNLOAD . 'index.html')) {
						copy(DIR_DOWNLOAD  . 'index.html', $file_path . DIRECTORY_SEPARATOR . 'index.html');
					}
				}
			}
		
			if ($this->request->server['HTTPS']) {
				$server = HTTPS_CATALOG;
			} else {
				$server = HTTP_CATALOG;
			}
		
			$filename = $file_name."_".date($this->config->get('advppp' . $user_id . '_hour_format') == '24' ? "Y-m-d_H-i-s" : "Y-m-d_h-i-s-A").".xls";
			$file_to_download = $server . $file_save_path . '/' . $file_name."_".date($this->config->get('advppp' . $user_id . '_hour_format') == '24' ? "Y-m-d_H-i-s" : "Y-m-d_h-i-s-A").".xls";
			$file = $file_path . '/' . $file_name."_".date($this->config->get('advppp' . $user_id . '_hour_format') == '24' ? "Y-m-d_H-i-s" : "Y-m-d_h-i-s-A").".xls";		
		
			// Creating a workbook
			$workbook = new Spreadsheet_Excel_Writer($file);
			$workbook->setTempDir(DIR_CACHE);
			$workbook->setVersion(8); // Use Excel97/2000 BIFF8 Format
		
			// sending HTTP headers
			$worksheet =& $workbook->addWorksheet('ADV Products Profit Report');
			$worksheet->setInputEncoding('UTF-8');
			$worksheet->setZoom(90);
		}

		// Formating a workbook
		if ($export_logo_criteria) {
		$workbook->setCustomColor(43, 219, 229, 241);
		$criteriaDateFormat =& $workbook->addFormat(array('Align' => 'left', 'FgColor' => '43'));	
		$criteriaTitleFormat =& $workbook->addFormat(array('Align' => 'left', 'FgColor' => '43', 'bold' => 1));
		$criteriaTitleFormat->setSize(24);
		$criteriaTitleFormat->setVAlign('vcenter');
		$criteriaFilterFormat1 =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '43', 'bold' => 1));
		$criteriaFilterFormat1->setVAlign('top');
		$criteriaFilterFormat2 =& $workbook->addFormat(array('Align' => 'left', 'FgColor' => '43'));
		$criteriaFilterFormat2->setVAlign('top');
		$criteriaFilterFormat2->setTextWrap();
		$criteriaAddressFormat =& $workbook->addFormat(array('Align' => 'left', 'FgColor' => '43'));
		$criteriaAddressFormat->setSize(14);
		$criteriaAddressFormat->setVAlign('vcenter');
		$criteriaAddressFormat->setTextWrap();		
		}
		
		$textFormat =& $workbook->addFormat(array('Align' => 'left', 'NumFormat' => "@"));
		
		$numberFormat =& $workbook->addFormat(array('Align' => 'right'));
		if ($filter_report == 'products_abandoned_orders') {
		$abnumberFormat =& $workbook->addFormat(array('Align' => 'right'));
		$abnumberFormat->setStrikeOut();
		}
		
		$priceFormat =& $workbook->addFormat(array('Align' => 'right'));
		$priceFormat->setNumFormat('0.00');
		if ($filter_report == 'products_abandoned_orders') {
		$priceFormat->setStrikeOut();	
		}

		$saleFormat =& $workbook->addFormat(array('Align' => 'right'));
		$saleFormat->setColor('green');	
		$saleFormat->setNumFormat('0.00');
		if ($filter_report == 'products_abandoned_orders') {
		$saleFormat->setStrikeOut();
		}
		
		$workbook->setCustomColor(41, 241, 249, 233);
		$ppriceColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '41', 'bordercolor' => 'silver'));
		$ppriceColumnFormat->setBorder(1);	
		$ppriceColumnFormat->setNumFormat('0.00');

		$workbook->setCustomColor(28, 247, 233, 227);
		$pcostColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '28', 'bordercolor' => 'silver'));
		$pcostColumnFormat->setBorder(1);		
		$pcostColumnFormat->setNumFormat('0.00');

		$workbook->setCustomColor(39, 223, 231, 238);
		$pprofitColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '39', 'bold' => 1, 'bordercolor' => 'silver'));
		$pprofitColumnFormat->setBorder(1);		
		$pprofitColumnFormat->setNumFormat('0.00');
		$ppercentColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '39', 'bold' => 1, 'bordercolor' => 'silver'));
		$ppercentColumnFormat->setBorder(1);		
		$ppercentColumnFormat->setNumFormat('0.00%');

		$workbook->setCustomColor(40, 249, 153, 153);
		$pnoprofitColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '40', 'bold' => 1, 'bordercolor' => 'silver'));
		$pnoprofitColumnFormat->setBorder(1);		
		$pnoprofitColumnFormat->setNumFormat('0.00');
		$pnopercentColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '40', 'bold' => 1, 'bordercolor' => 'silver'));
		$pnopercentColumnFormat->setBorder(1);		
		$pnopercentColumnFormat->setNumFormat('0.00%');

		$workbook->setCustomColor(27, 255, 255, 204);
		$soldColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '27', 'bordercolor' => 'silver'));
		$soldColumnFormat->setBorder(1);
		if ($filter_report == 'products_abandoned_orders') {
		$soldColumnFormat->setStrikeOut();
		}		
		$percentFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '27', 'bordercolor' => 'silver'));
		$percentFormat->setBorder(1);	
		$percentFormat->setNumFormat('0.00%');
		if ($filter_report == 'products_abandoned_orders') {
		$percentFormat->setStrikeOut();
		}
		
		$workbook->setCustomColor(42, 220, 255, 185);
		$salesColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '42', 'bordercolor' => 'silver'));
		$salesColumnFormat->setBorder(1);	
		$salesColumnFormat->setNumFormat('0.00');
		if ($filter_report == 'products_abandoned_orders') {
		$salesColumnFormat->setStrikeOut();
		}
		
		$workbook->setCustomColor(29, 255, 215, 215);
		$costsColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '29', 'bordercolor' => 'silver'));
		$costsColumnFormat->setBorder(1);		
		$costsColumnFormat->setNumFormat('0.00');
		if ($filter_report == 'products_abandoned_orders') {
		$costsColumnFormat->setStrikeOut();
		}
		
		$workbook->setCustomColor(44, 196, 217, 238);
		$profitColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '44', 'bold' => 1, 'bordercolor' => 'silver'));
		$profitColumnFormat->setBorder(1);		
		$profitColumnFormat->setNumFormat('0.00');
		$abprofitColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '44', 'bold' => 1, 'bordercolor' => 'silver'));
		if ($filter_report == 'products_abandoned_orders') {
		$profitColumnFormat->setStrikeOut();
		}	
		$percentColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '44', 'bold' => 1, 'bordercolor' => 'silver'));
		$percentColumnFormat->setBorder(1);		
		$percentColumnFormat->setNumFormat('0.00%');
		if ($filter_report == 'products_abandoned_orders') {
		$percentColumnFormat->setStrikeOut();
		}

		$workbook->setCustomColor(45, 249, 153, 153);
		$noprofitColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '45', 'bold' => 1, 'bordercolor' => 'silver'));
		$noprofitColumnFormat->setBorder(1);		
		$noprofitColumnFormat->setNumFormat('0.00');
		if ($filter_report == 'products_abandoned_orders') {
		$noprofitColumnFormat->setStrikeOut();
		}
		$nopercentColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '45', 'bold' => 1, 'bordercolor' => 'silver'));
		$nopercentColumnFormat->setBorder(1);		
		$nopercentColumnFormat->setNumFormat('0.00%');
		if ($filter_report == 'products_abandoned_orders') {
		$nopercentColumnFormat->setStrikeOut();
		}
		
		$boxFormatText =& $workbook->addFormat(array('bold' => 1));
		$boxFormatNumber =& $workbook->addFormat(array('Align' => 'right', 'bold' => 1));

		// Set the column widths
		$j = 0;
		if ($filter_report == 'all_products_with_without_orders' or $filter_report == 'products_without_orders') {		
		$worksheet->setColumn($j,$j++,13);	
		} elseif ($filter_report == 'products_shopping_carts' or $filter_report == 'products_wishlists') {
		$worksheet->setColumn($j,$j++,13);
		$worksheet->setColumn($j,$j++,13);
		} else {
		if ($filter_group == 'year') {			
		$worksheet->setMerge(0, 1, 0, 1);
		$worksheet->setColumn($j,$j++,10);
		} elseif ($filter_group == 'quarter') {
		$worksheet->setColumn($j,$j++,10);
		$worksheet->setColumn($j,$j++,10);	
		} elseif ($filter_group == 'month') {
		$worksheet->setColumn($j,$j++,10);
		$worksheet->setColumn($j,$j++,13);
		} elseif ($filter_group == 'day') {
		$worksheet->setMerge(0, 1, 0, 1);
		$worksheet->setColumn($j,$j++,13);
		} elseif ($filter_group == 'order') {
		$worksheet->setColumn($j,$j++,10);
		$worksheet->setColumn($j,$j++,13);
		} else {
		$worksheet->setColumn($j,$j++,13);
		$worksheet->setColumn($j,$j++,13);
		}
		}		
		if ($filter_report == 'products_shopping_carts' or $filter_report == 'products_wishlists') {
		in_array('scw_id', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,8) : '';
		in_array('scw_name', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,25) : '';
		if ($filter_report == 'products_shopping_carts') {
		in_array('scw_name', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		}	
		in_array('scw_sku', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : ''; 
		in_array('scw_upc', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : ''; 
		in_array('scw_ean', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : ''; 
		in_array('scw_jan', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : ''; 
		in_array('scw_isbn', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : ''; 
		in_array('scw_mpn', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : ''; 
		in_array('scw_model', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('scw_category', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('scw_manufacturer', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('scw_supplier', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('scw_attribute', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('scw_status', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('scw_location', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('scw_tax_class', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('scw_viewed', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('scw_price', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('scw_cost', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('scw_profit', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('scw_profit_margin', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('scw_profit_markup', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('scw_stock_quantity', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		if ($filter_report == 'products_shopping_carts') {
		in_array('scw_cart_quantity', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		} elseif ($filter_report == 'products_wishlists') {
		in_array('scw_wishlist_quantity', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,13) : '';		
		}
		in_array('scw_customer_id', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('scw_date_added', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('scw_customer_name', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('scw_customer_email', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('scw_customer_telephone', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('scw_customer_group', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,16) : '';
		in_array('scw_billing_company', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('scw_billing_address_1', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('scw_billing_address_2', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('scw_billing_city', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('scw_billing_zone', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('scw_billing_postcode', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('scw_billing_country', $advppp_settings_scw_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		} elseif ($filter_report == 'products_without_orders') {
		in_array('pnp_id', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,8) : '';
		in_array('pnp_name', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,25) : '';
		in_array('pnp_sku', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,15) : ''; 
		in_array('pnp_upc', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,15) : ''; 
		in_array('pnp_ean', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,15) : ''; 
		in_array('pnp_jan', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,15) : ''; 
		in_array('pnp_isbn', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,15) : ''; 
		in_array('pnp_mpn', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,15) : ''; 
		in_array('pnp_model', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('pnp_category', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('pnp_manufacturer', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('pnp_supplier', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('pnp_attribute', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('pnp_status', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('pnp_location', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('pnp_tax_class', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('pnp_viewed', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('pnp_price', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pnp_cost', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pnp_profit', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pnp_profit_margin', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('pnp_profit_markup', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('pnp_stock_quantity', $advppp_settings_pnp_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		} elseif ($filter_report == 'manufacturers' or $filter_report == 'categories' or $filter_report == 'suppliers') {
		if ($filter_report == 'manufacturers') {
		$worksheet->setColumn($j,$j++,20);	
		} elseif ($filter_report == 'categories') {
		$worksheet->setColumn($j,$j++,20);	
		} elseif ($filter_report == 'suppliers') {
		$worksheet->setColumn($j,$j++,20);
		}
		in_array('cm_orders', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('cm_customers', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('cm_sold_quantity', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('cm_sold_percent', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('cm_total_excl_vat', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cm_total_tax', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cm_total_incl_vat', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cm_app', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('cm_discount', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('cm_refunds', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cm_reward_points', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cm_total_sales', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('cm_total_costs', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('cm_total_profit', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('cm_total_profit_margin', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('cm_total_profit_markup', $advppp_settings_cm_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		} elseif ($filter_report == 'products_options') {
		$worksheet->setColumn($j,$j++,25);
		in_array('mv_orders', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('mv_customers', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		$worksheet->setColumn($j,$j++,10);
		$worksheet->setColumn($j,$j++,10);
		} else {
		in_array('mv_id', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,8) : '';
		in_array('mv_name', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,25) : '';
		if ($filter_report == 'products_purchased_with_options' or $filter_report == 'products_abandoned_orders') {
		if ($option_names) {
		foreach ($option_names as $option_name) {
		in_array('mv_name', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		}
		}
		}
		in_array('mv_sku', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_upc', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_ean', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_jan', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_isbn', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_mpn', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_model', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_category', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('mv_manufacturer', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('mv_supplier', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('mv_attribute', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('mv_status', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('mv_location', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('mv_tax_class', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('mv_viewed', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('mv_price', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_cost', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_profit', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_profit_margin', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_profit_markup', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_stock_quantity', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('mv_orders', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('mv_customers', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('mv_sold_quantity', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('mv_sold_percent', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('mv_total_excl_vat', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_total_tax', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_total_incl_vat', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_app', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('mv_discount', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('mv_refunds', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_reward_points', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_total_sales', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('mv_total_costs', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('mv_total_profit', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('mv_total_profit_margin', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('mv_total_profit_markup', $advppp_settings_mv_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		}

		if ($export_logo_criteria) {
		$worksheet->setMerge(0, 0, 0, 1);
		$worksheet->writeString(0, 0, '', $criteriaDateFormat);			
		$worksheet->setMerge(0, 2, 0, $j-1);
		$worksheet->writeString(0, 2, $this->language->get('text_report_date').": ".date($this->config->get('advppp' . $user_id . '_hour_format') == '24' ? "Y-m-d H:i:s" : "Y-m-d h:i:s A"), $criteriaDateFormat);
		$worksheet->setRow(1, 50);	
		$worksheet->setMerge(1, 0, 1, 1);
		$worksheet->writeString(1, 0, '', $criteriaTitleFormat);			
		$worksheet->setMerge(1, 2, 1, $j-1);
		$worksheet->writeString(1, 2, $this->language->get('adv_ext_name'), $criteriaTitleFormat);
		$worksheet->setRow(2, 30);
		$worksheet->setMerge(2, 0, 2, 1);
		$worksheet->writeString(2, 0, '', $criteriaAddressFormat);			
		$worksheet->setMerge(2, 2, 2, $j-1);
		$worksheet->writeString(2, 2, $this->config->get('config_name').", ".$this->config->get('config_address').", ".$this->language->get('text_email')."".$this->config->get('config_email').", ".$this->language->get('text_telephone')."".$this->config->get('config_telephone'), $criteriaAddressFormat);		
		$worksheet->setRow(3, 40);		
		$worksheet->setMerge(3, 0, 3, 1);
		$worksheet->writeString(3, 0, $this->language->get('text_report_criteria'), $criteriaFilterFormat1);		
		$worksheet->setMerge(3, 2, 3, $j-1);
			$filter_criteria = "";
			if ($filter_report) {	
				if ($filter_report == 'all_products_with_without_orders') {
					$filter_report_name = $this->language->get('text_all_products')." ".$this->language->get('text_with_without_orders');
				} elseif ($filter_report == 'products_purchased_without_options') {
					$filter_report_name = $this->language->get('text_products_purchased')." ".$this->language->get('text_without_options');
				} elseif ($filter_report == 'products_purchased_with_options') {
					$filter_report_name = $this->language->get('text_products_purchased')." ".$this->language->get('text_with_options');
				} elseif ($filter_report == 'new_products_purchased') {
					$filter_report_name = $this->language->get('text_new_products_purchased');
				} elseif ($filter_report == 'old_products_purchased') {
					$filter_report_name = $this->language->get('text_old_products_purchased');
				} elseif ($filter_report == 'products_without_orders') {
					$filter_report_name = $this->language->get('text_products')." ".$this->language->get('text_without_orders');
				} elseif ($filter_report == 'products_options') {
					$filter_report_name = $this->language->get('text_product_options')." ".$this->language->get('text_products_options');
				} elseif ($filter_report == 'categories') {
					$filter_report_name = $this->language->get('text_categories');						
				} elseif ($filter_report == 'manufacturers') {
					$filter_report_name = $this->language->get('text_manufacturers');
				} elseif ($filter_report == 'suppliers') {
					$filter_report_name = $this->language->get('text_suppliers');						
				} elseif ($filter_report == 'products_abandoned_orders') {
					$filter_report_name = $this->language->get('text_products_abandoned_orders');					
				} elseif ($filter_report == 'products_shopping_carts') {
					$filter_report_name = $this->language->get('text_products_shopping_carts');					
				} elseif ($filter_report == 'products_wishlists') {
					$filter_report_name = $this->language->get('text_products_wishlists');					
				}	
				$filter_criteria .= $this->language->get('entry_report')." ". strip_tags($filter_report_name) ."; ";
			}
			if ($filter_details) {
				if ($filter_details == 'no_details') {
					$filter_details_name = $this->language->get('text_no_details');
				} elseif ($filter_details == 'basic_details') {
					$filter_details_name = $this->language->get('text_basic_details');
				} elseif ($filter_details == 'all_details_products') {
					$filter_details_name = $this->language->get('text_all_details')." ".$this->language->get('text_all_details_products');				
				} elseif ($filter_details == 'all_details_orders') {
					$filter_details_name = $this->language->get('text_all_details')." ".$this->language->get('text_all_details_orders');		
				}					
				$filter_criteria .= $this->language->get('entry_show_details')." ".$filter_details_name."; ";
			}
			if ($filter_group) {	
				if ($filter_group == 'no_group') {
					$filter_group_name = $this->language->get('text_no_group');
				} elseif ($filter_group == 'year') {
					$filter_group_name = $this->language->get('text_year');
				} elseif ($filter_group == 'quarter') {
					$filter_group_name = $this->language->get('text_quarter');	
				} elseif ($filter_group == 'month') {
					$filter_group_name = $this->language->get('text_month');
				} elseif ($filter_group == 'week') {
					$filter_group_name = $this->language->get('text_week');
				} elseif ($filter_group == 'day') {
					$filter_group_name = $this->language->get('text_day');
				} elseif ($filter_group == 'order') {
					$filter_group_name = $this->language->get('text_order');					
				}				
				$filter_criteria .= $this->language->get('entry_group')." ".$filter_group_name."; ";
			}
			if ($filter_sort) {	
				if ($filter_sort == 'date') {
					$filter_sort_name = $this->language->get('column_date');
				} elseif ($filter_sort == 'id') {
					$filter_sort_name = $this->language->get('column_id');
				} elseif ($filter_sort == 'sku') {
					$filter_sort_name = $this->language->get('column_sku');	
				} elseif ($filter_sort == 'upc') {
					$filter_sort_name = $this->language->get('column_upc');	
				} elseif ($filter_sort == 'ean') {
					$filter_sort_name = $this->language->get('column_ean');	
				} elseif ($filter_sort == 'jan') {
					$filter_sort_name = $this->language->get('column_jan');	
				} elseif ($filter_sort == 'isbn') {
					$filter_sort_name = $this->language->get('column_isbn');	
				} elseif ($filter_sort == 'mpn') {
					$filter_sort_name = $this->language->get('column_mpn');	
				} elseif ($filter_sort == 'name') {
					$filter_sort_name = $this->language->get('column_prod_name');
				} elseif ($filter_sort == 'model') {
					$filter_sort_name = $this->language->get('column_model');
				} elseif ($filter_sort == 'category') {
					$filter_sort_name = $this->language->get('column_category');
				} elseif ($filter_sort == 'manufacturer') {
					$filter_sort_name = $this->language->get('column_manufacturer');	
				} elseif ($filter_sort == 'supplier') {
					$filter_sort_name = $this->language->get('column_supplier');					
				} elseif ($filter_sort == 'attribute') {
					$filter_sort_name = $this->language->get('column_attribute');
				} elseif ($filter_sort == 'status') {
					$filter_sort_name = $this->language->get('column_status');	
				} elseif ($filter_sort == 'location') {
					$filter_sort_name = $this->language->get('column_location');
				} elseif ($filter_sort == 'tax_class') {
					$filter_sort_name = $this->language->get('column_tax_class');
				} elseif ($filter_sort == 'price') {
					$filter_sort_name = $this->language->get('column_price');
				} elseif ($filter_sort == 'cost') {
					$filter_sort_name = $this->language->get('column_cost');	
				} elseif ($filter_sort == 'profit') {
					$filter_sort_name = $this->language->get('column_profit');
				} elseif ($filter_sort == 'profit_margin') {
					$filter_sort_name = $this->language->get('column_profit_margin') . ' [%]';	
				} elseif ($filter_sort == 'profit_markup') {
					$filter_sort_name = $this->language->get('column_profit_markup') . ' [%]';						
				} elseif ($filter_sort == 'viewed') {
					$filter_sort_name = $this->language->get('column_viewed');
				} elseif ($filter_sort == 'orders') {
					$filter_sort_name = $this->language->get('column_orders');
				} elseif ($filter_sort == 'customers') {
					$filter_sort_name = $this->language->get('column_customers');
				} elseif ($filter_sort == 'stock_quantity') {
					$filter_sort_name = $this->language->get('column_stock_quantity');
				} elseif ($filter_sort == 'cart_quantity') {
					$filter_sort_name = $this->language->get('column_cart_quantity');
				} elseif ($filter_sort == 'wishlist_quantity') {
					$filter_sort_name = $this->language->get('column_wishlist_quantity');						
				} elseif ($filter_sort == 'sold_quantity') {
					$filter_sort_name = $this->language->get('column_sold_quantity');
				} elseif ($filter_sort == 'total_excl_vat') {
					$filter_sort_name = $this->language->get('column_prod_total_excl_vat');	
				} elseif ($filter_sort == 'total_tax') {
					$filter_sort_name = $this->language->get('column_total_tax');
				} elseif ($filter_sort == 'total_incl_vat') {
					$filter_sort_name = $this->language->get('column_prod_total_incl_vat');	
				} elseif ($filter_sort == 'app') {
					$filter_sort_name = $this->language->get('column_app');
				} elseif ($filter_sort == 'discount') {
					$filter_sort_name = $this->language->get('column_product_discount');
				} elseif ($filter_sort == 'refunds') {
					$filter_sort_name = $this->language->get('column_product_refunds');
				} elseif ($filter_sort == 'reward_points') {
					$filter_sort_name = $this->language->get('column_product_reward_points');	
				} elseif ($filter_sort == 'total_sales') {
					$filter_sort_name = $this->language->get('column_total_sales');
				} elseif ($filter_sort == 'total_costs') {
					$filter_sort_name = $this->language->get('column_total_costs');	
				} elseif ($filter_sort == 'total_profit') {
					$filter_sort_name = $this->language->get('column_total_profit');
				} elseif ($filter_sort == 'total_profit_margin') {
					$filter_sort_name = $this->language->get('column_total_profit_margin') . ' [%]';
				} elseif ($filter_sort == 'total_profit_markup') {
					$filter_sort_name = $this->language->get('column_total_profit_markup') . ' [%]';				
				}			
				if ($filter_order == 'asc') {
					$filter_order_name = $this->language->get('text_asc');
				} elseif ($filter_order == 'desc') {
					$filter_order_name = $this->language->get('text_desc');
				}				
				$filter_criteria .= $this->language->get('entry_sort_by')." ".$filter_sort_name." ".$filter_order_name."; ";
			}
			if ($filter_limit) {	
				$filter_criteria .= $this->language->get('entry_limit')." ".$filter_limit."; ";
			}	
			$filter_criteria .= "\n";
			if ($filter_range) {	
				if ($filter_range == 'custom') {
					$filter_range_name = $this->language->get('stat_custom');
				} elseif ($filter_range == 'today') {
					$filter_range_name = $this->language->get('stat_today');
				} elseif ($filter_range == 'yesterday') {
					$filter_range_name = $this->language->get('stat_yesterday');	
				} elseif ($filter_range == 'week') {
					$filter_range_name = $this->language->get('stat_week');
				} elseif ($filter_range == 'month') {
					$filter_range_name = $this->language->get('stat_month');
				} elseif ($filter_range == 'quarter') {
					$filter_range_name = $this->language->get('stat_quarter');
				} elseif ($filter_range == 'year') {
					$filter_range_name = $this->language->get('stat_year');	
				} elseif ($filter_range == 'current_week') {
					$filter_range_name = $this->language->get('stat_current_week');
				} elseif ($filter_range == 'current_month') {
					$filter_range_name = $this->language->get('stat_current_month');	
				} elseif ($filter_range == 'current_quarter') {
					$filter_range_name = $this->language->get('stat_current_quarter');
				} elseif ($filter_range == 'current_year') {
					$filter_range_name = $this->language->get('stat_current_year');
				} elseif ($filter_range == 'last_week') {
					$filter_range_name = $this->language->get('stat_last_week');
				} elseif ($filter_range == 'last_month') {
					$filter_range_name = $this->language->get('stat_last_month');	
				} elseif ($filter_range == 'last_quarter') {
					$filter_range_name = $this->language->get('stat_last_quarter');
				} elseif ($filter_range == 'last_year') {
					$filter_range_name = $this->language->get('stat_last_year');
				} elseif ($filter_range == 'all_time') {
					$filter_range_name = $this->language->get('stat_all_time');					
				}				
				$filter_criteria .= $this->language->get('entry_range')." ".$filter_range_name;
				if ($filter_date_start) {	
					$filter_criteria .= " [".$this->language->get('entry_date_start')." ".$filter_date_start;
				}
				if ($filter_date_end) {	
					$filter_criteria .= ", ".$this->language->get('entry_date_end')." ".$filter_date_end."]";
				}
				$filter_criteria .= "; ";
			}
			if ($filter_order_status_id) {	
				$filter_criteria .= $this->language->get('entry_status')." ".$filter_order_status_id;
				if ($filter_status_date_start) {	
					$filter_criteria .= " [".$this->language->get('entry_date_start')." ".$filter_status_date_start;
				}
				if ($filter_status_date_end) {	
					$filter_criteria .= ", ".$this->language->get('entry_date_end')." ".$filter_status_date_end."]";
				}
				$filter_criteria .= "; ";				
			}
			if ($filter_order_id_from or $filter_order_id_to) {
				$filter_criteria .= $this->language->get('entry_order_id').": ".$filter_order_id_from."-".$filter_order_id_to."; ";
			}
			if ($filter_prod_price_min or $filter_prod_price_max) {
				$filter_criteria .= $this->language->get('entry_price_value').": ".$filter_prod_price_min."-".$filter_prod_price_max."; ";
			}
			$filter_criteria .= "\n";
			if ($filter_store_id) {	
				$filter_criteria .= $this->language->get('entry_store')." ".$filter_store_id."; ";
			}
			if ($filter_currency) {	
				$filter_criteria .= $this->language->get('entry_currency')." ".$filter_currency."; ";
			}
			if ($filter_taxes) {	
				$filter_criteria .= $this->language->get('entry_tax')." ".$filter_taxes."; ";
			}
			if ($filter_tax_classes) {	
				$filter_criteria .= $this->language->get('entry_tax_classes')." ".$filter_tax_classes."; ";
			}
			if ($filter_geo_zones) {	
				$filter_criteria .= $this->language->get('entry_geo_zone')." ".$filter_geo_zones."; ";
			}
			if ($filter_customer_group_id) {	
				$filter_criteria .= $this->language->get('entry_customer_group')." ".$filter_customer_group_id."; ";
			}		
			if ($filter_customer_name) {	
				$filter_criteria .= $this->language->get('entry_customer_name')." ".$filter_customer_name."; ";
			}
			if ($filter_customer_email) {	
				$filter_criteria .= $this->language->get('entry_customer_email')." ".$filter_customer_email."; ";
			}
			if ($filter_customer_telephone) {	
				$filter_criteria .= $this->language->get('entry_customer_telephone')." ".$filter_customer_telephone."; ";
			}
			if ($filter_ip) {	
				$filter_criteria .= $this->language->get('entry_ip')." ".$filter_ip."; ";
			}
			if ($filter_payment_company) {	
				$filter_criteria .= $this->language->get('entry_payment_company')." ".$filter_payment_company."; ";
			}
			if ($filter_payment_address) {	
				$filter_criteria .= $this->language->get('entry_payment_address')." ".$filter_payment_address."; ";
			}
			if ($filter_payment_city) {	
				$filter_criteria .= $this->language->get('entry_payment_city')." ".$filter_payment_city."; ";
			}
			if ($filter_payment_zone) {	
				$filter_criteria .= $this->language->get('entry_payment_zone')." ".$filter_payment_zone."; ";
			}
			if ($filter_payment_postcode) {	
				$filter_criteria .= $this->language->get('entry_payment_postcode')." ".$filter_payment_postcode."; ";
			}
			if ($filter_payment_country) {	
				$filter_criteria .= $this->language->get('entry_payment_country')." ".$filter_payment_country."; ";
			}
			if ($filter_payment_method) {	
				$filter_criteria .= $this->language->get('entry_payment_method')." ".$filter_payment_method."; ";
			}
			if ($filter_shipping_company) {	
				$filter_criteria .= $this->language->get('entry_shipping_company')." ".$filter_shipping_company."; ";
			}
			if ($filter_shipping_address) {	
				$filter_criteria .= $this->language->get('entry_shipping_address')." ".$filter_shipping_address."; ";
			}
			if ($filter_shipping_city) {	
				$filter_criteria .= $this->language->get('entry_shipping_city')." ".$filter_shipping_city."; ";
			}
			if ($filter_shipping_zone) {	
				$filter_criteria .= $this->language->get('entry_shipping_zone')." ".$filter_shipping_zone."; ";
			}
			if ($filter_shipping_postcode) {	
				$filter_criteria .= $this->language->get('entry_shipping_postcode')." ".$filter_shipping_postcode."; ";
			}
			if ($filter_shipping_country) {	
				$filter_criteria .= $this->language->get('entry_shipping_country')." ".$filter_shipping_country."; ";
			}
			if ($filter_shipping_method) {	
				$filter_criteria .= $this->language->get('entry_shipping_method')." ".$filter_shipping_method."; ";
			}
			if ($filter_category) {	
				$filter_criteria .= $this->language->get('entry_category')." ".$filter_category."; ";
			}
			if ($filter_manufacturer) {	
				$filter_criteria .= $this->language->get('entry_manufacturer')." ".$filter_manufacturer."; ";
			}
			if ($filter_supplier) {	
				$filter_criteria .= $this->language->get('entry_supplier')." ".$filter_supplier."; ";
			}			
			if ($filter_sku) {	
				$filter_criteria .= $this->language->get('entry_sku')." ".$filter_sku."; ";
			}
			if ($filter_upc) {	
				$filter_criteria .= $this->language->get('entry_upc')." ".$filter_upc."; ";
			}
			if ($filter_ean) {	
				$filter_criteria .= $this->language->get('entry_ean')." ".$filter_ean."; ";
			}
			if ($filter_jan) {	
				$filter_criteria .= $this->language->get('entry_jan')." ".$filter_jan."; ";
			}
			if ($filter_isbn) {	
				$filter_criteria .= $this->language->get('entry_isbn')." ".$filter_isbn."; ";
			}
			if ($filter_mpn) {	
				$filter_criteria .= $this->language->get('entry_mpn')." ".$filter_mpn."; ";
			}
			if ($filter_product_name) {	
				$filter_criteria .= $this->language->get('entry_product')." ".$filter_product_name."; ";
			}
			if ($filter_model) {	
				$filter_criteria .= $this->language->get('entry_model')." ".$filter_model."; ";
			}
			if ($filter_option) {	
				$filter_criteria .= $this->language->get('entry_option')." ".$filter_option."; ";
			}	
			if ($filter_attribute) {	
				$filter_criteria .= $this->language->get('entry_attributes')." ".$filter_attribute."; ";
			}		
			if ($filter_product_status) {	
				$filter_criteria .= $this->language->get('entry_product_status')." ".$filter_product_status."; ";
			}				
			if ($filter_location) {	
				$filter_criteria .= $this->language->get('entry_location')." ".$filter_location."; ";
			}	
			if ($filter_coupon_name) {	
				$filter_criteria .= $this->language->get('entry_coupon_name')." ".$filter_coupon_name."; ";
			}		
			if ($filter_coupon_code) {	
				$filter_criteria .= $this->language->get('entry_coupon_code')." ".$filter_coupon_code."; ";
			}	
			if ($filter_voucher_code) {	
				$filter_criteria .= $this->language->get('entry_voucher_code')." ".$filter_voucher_code."; ";
			}	
			if ($filter_campaign_name) {	
				$filter_criteria .= $this->language->get('entry_campaign_name')." ".$filter_campaign_name."; ";
			}		
			if ($filter_campaign_code) {	
				$filter_criteria .= $this->language->get('entry_campaign_code')." ".$filter_campaign_code."; ";
			}
		$worksheet->writeString(3, 2, $filter_criteria, $criteriaFilterFormat2);			
		}
		
		// The order headings row
		$export_logo_criteria ? $i = 4 : $i = 0;
		$j = 0;	
		if ($filter_report == 'all_products_with_without_orders' or $filter_report == 'products_without_orders') {	
		$worksheet->writeString($i, $j++, $this->language->get('column_date_added'), $boxFormatText);
		} elseif ($filter_report == 'products_shopping_carts' or $filter_report == 'products_wishlists') {
		$worksheet->writeString($i, $j++, $this->language->get('column_date_start'), $boxFormatText);
		$worksheet->writeString($i, $j++, $this->language->get('column_date_end'), $boxFormatText);		
		} else {
		if ($filter_group == 'year') {	
		$worksheet->writeString($i, $j++, $this->language->get('column_year'), $boxFormatText);
		} elseif ($filter_group == 'quarter') {
		$worksheet->writeString($i, $j++, $this->language->get('column_year'), $boxFormatText);
		$worksheet->writeString($i, $j++, $this->language->get('column_quarter'), $boxFormatText);
		} elseif ($filter_group == 'month') {
		$worksheet->writeString($i, $j++, $this->language->get('column_year'), $boxFormatText);
		$worksheet->writeString($i, $j++, $this->language->get('column_month'), $boxFormatText);
		} elseif ($filter_group == 'day') {
		$worksheet->writeString($i, $j++, $this->language->get('column_date'), $boxFormatText);
		} elseif ($filter_group == 'order') {
		$worksheet->writeString($i, $j++, $this->language->get('column_order_order_id'), $boxFormatText);
		$worksheet->writeString($i, $j++, $this->language->get('column_order_date_added'), $boxFormatText);
		} else {
		$worksheet->writeString($i, $j++, $this->language->get('column_date_start'), $boxFormatText);
		$worksheet->writeString($i, $j++, $this->language->get('column_date_end'), $boxFormatText);
		}
		}
		
		if ($filter_report == 'products_shopping_carts' or $filter_report == 'products_wishlists') {
			
		in_array('scw_id', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_id'), $boxFormatText) : '';
		in_array('scw_name', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_pname'), $boxFormatText) : '';
		if ($filter_report == 'products_shopping_carts') {
		in_array('scw_name', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_poption'), $boxFormatText) : '';
		}
		in_array('scw_sku', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_sku'), $boxFormatText) : '';
		in_array('scw_upc', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_upc'), $boxFormatText) : '';
		in_array('scw_ean', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_ean'), $boxFormatText) : '';
		in_array('scw_jan', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_jan'), $boxFormatText) : '';
		in_array('scw_isbn', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_isbn'), $boxFormatText) : '';
		in_array('scw_mpn', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_mpn'), $boxFormatText) : '';
		in_array('scw_model', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_model'), $boxFormatText) : '';
		in_array('scw_category', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_category'), $boxFormatText) : '';
		in_array('scw_manufacturer', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_manufacturer'), $boxFormatText) : '';
		in_array('scw_supplier', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_supplier'), $boxFormatText) : '';
		in_array('scw_attribute', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_attribute'), $boxFormatText) : '';
		in_array('scw_status', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_status'), $boxFormatText) : '';
		in_array('scw_location', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_location'), $boxFormatText) : '';
		in_array('scw_tax_class', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_tax_class'), $boxFormatText) : '';
		in_array('scw_viewed', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_viewed'), $boxFormatNumber) : '';
		in_array('scw_price', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_price'), $boxFormatNumber) : '';
		in_array('scw_cost', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_cost'), $boxFormatNumber) : '';
		in_array('scw_profit', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_profit'), $boxFormatNumber) : '';
		in_array('scw_profit_margin', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_profit_margin'), $boxFormatNumber) : '';
		in_array('scw_profit_markup', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_profit_markup'), $boxFormatNumber) : '';
		in_array('scw_stock_quantity', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_stock_quantity'), $boxFormatNumber) : '';
		if ($filter_report == 'products_shopping_carts') {
		in_array('scw_cart_quantity', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_cart_quantity'), $boxFormatNumber) : '';
		} elseif ($filter_report == 'products_wishlists') {
		in_array('scw_wishlist_quantity', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_wishlist_quantity'), $boxFormatNumber) : '';
		}
		in_array('scw_customer_id', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_customer_cust_id'), $boxFormatText) : '';
		in_array('scw_date_added', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_customer_date_added'), $boxFormatText) : '';
		in_array('scw_customer_name', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_customer'), $boxFormatText) : '';
		in_array('scw_customer_email', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_email'), $boxFormatText) : '';
		in_array('scw_customer_telephone', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_customer_telephone'), $boxFormatText) : '';
		in_array('scw_customer_group', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_customer_group'), $boxFormatText) : '';
		in_array('scw_billing_company', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_company'), $boxFormatText) : '';
		in_array('scw_billing_address_1', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_address_1'), $boxFormatText) : '';
		in_array('scw_billing_address_2', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_address_2'), $boxFormatText) : '';
		in_array('scw_billing_city', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_city'), $boxFormatText) : '';
		in_array('scw_billing_zone', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_region_state'), $boxFormatText) : '';
		in_array('scw_billing_postcode', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_postcode'), $boxFormatText) : '';
		in_array('scw_billing_country', $advppp_settings_scw_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_country'), $boxFormatText) : '';
		
		} elseif ($filter_report == 'products_without_orders') {
			
		in_array('pnp_id', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_id'), $boxFormatText) : '';
		in_array('pnp_name', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_name'), $boxFormatText) : '';
		in_array('pnp_sku', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_sku'), $boxFormatText) : '';
		in_array('pnp_upc', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_upc'), $boxFormatText) : '';
		in_array('pnp_ean', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_ean'), $boxFormatText) : '';
		in_array('pnp_jan', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_jan'), $boxFormatText) : '';
		in_array('pnp_isbn', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_isbn'), $boxFormatText) : '';
		in_array('pnp_mpn', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_mpn'), $boxFormatText) : '';
		in_array('pnp_model', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_model'), $boxFormatText) : '';
		in_array('pnp_category', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_category'), $boxFormatText) : '';
		in_array('pnp_manufacturer', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_manufacturer'), $boxFormatText) : '';
		in_array('pnp_supplier', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_supplier'), $boxFormatText) : '';
		in_array('pnp_attribute', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_attribute'), $boxFormatText) : '';
		in_array('pnp_status', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_status'), $boxFormatText) : '';
		in_array('pnp_location', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_location'), $boxFormatText) : '';
		in_array('pnp_tax_class', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_tax_class'), $boxFormatText) : '';
		in_array('pnp_viewed', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_viewed'), $boxFormatNumber) : '';
		in_array('pnp_price', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_price'), $boxFormatNumber) : '';
		in_array('pnp_cost', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_cost'), $boxFormatNumber) : '';
		in_array('pnp_profit', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_profit'), $boxFormatNumber) : '';
		in_array('pnp_profit_margin', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_profit_margin'), $boxFormatNumber) : '';
		in_array('pnp_profit_markup', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_profit_markup'), $boxFormatNumber) : '';
		in_array('pnp_stock_quantity', $advppp_settings_pnp_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_stock_quantity'), $boxFormatNumber) : '';
		
		} elseif ($filter_report == 'manufacturers' or $filter_report == 'categories' or $filter_report == 'suppliers') {
			
		if ($filter_report == 'manufacturers') {
		$worksheet->writeString($i, $j++, $this->language->get('column_manufacturer'), $boxFormatText);	
		} elseif ($filter_report == 'categories') {
		$worksheet->writeString($i, $j++, $this->language->get('column_category'), $boxFormatText);	
		} elseif ($filter_report == 'suppliers') {
		$worksheet->writeString($i, $j++, $this->language->get('column_supplier'), $boxFormatText);
		}
		in_array('cm_orders', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_orders'), $boxFormatNumber) : '';
		in_array('cm_customers', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_customers'), $boxFormatNumber) : '';
		in_array('cm_sold_quantity', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_sold_quantity'), $boxFormatNumber) : '';
		in_array('cm_sold_percent', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_sold_percent'), $boxFormatNumber) : '';
		in_array('cm_total_excl_vat', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_total_excl_vat'), $boxFormatNumber) : '';
		in_array('cm_total_tax', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_tax'), $boxFormatNumber) : '';
		in_array('cm_total_incl_vat', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_total_incl_vat'), $boxFormatNumber) : '';
		in_array('cm_app', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_app'), $boxFormatNumber) : '';	
		in_array('cm_discount', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_product_discount'), $boxFormatNumber) : '';
		in_array('cm_refunds', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_product_refunds'), $boxFormatNumber) : '';
		in_array('cm_reward_points', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_product_reward_points'), $boxFormatNumber) : '';
		in_array('cm_total_sales', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_sales'), $boxFormatNumber) : '';
		in_array('cm_total_costs', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_costs'), $boxFormatNumber) : '';
		in_array('cm_total_profit', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_profit'), $boxFormatNumber) : '';
		in_array('cm_total_profit_margin', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_profit_margin'), $boxFormatNumber) : '';
		in_array('cm_total_profit_markup', $advppp_settings_cm_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_profit_markup'), $boxFormatNumber) : '';
		
		} elseif ($filter_report == 'products_options') {
			
		$worksheet->writeString($i, $j++, $this->language->get('column_prod_option'), $boxFormatText);
		in_array('mv_orders', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_orders'), $boxFormatNumber) : '';
		in_array('mv_customers', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_customers'), $boxFormatNumber) : '';
		$worksheet->writeString($i, $j++, $this->language->get('column_sold_quantity'), $boxFormatNumber);
		$worksheet->writeString($i, $j++, $this->language->get('column_sold_percent'), $boxFormatNumber);
		
		} else {
			
		in_array('mv_id', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_id'), $boxFormatText) : '';
		in_array('mv_name', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_pname'), $boxFormatText) : '';
		if ($filter_report == 'products_purchased_with_options' or $filter_report == 'products_abandoned_orders') {
		$n = 0;
		if ($option_names) {
		foreach ($option_names as $option_name) {
		in_array('mv_name', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $option_name['name'], $boxFormatText) : '';
		$n++;
		}		 
		}				
		}
		in_array('mv_sku', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_sku'), $boxFormatText) : '';
		in_array('mv_upc', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_upc'), $boxFormatText) : '';
		in_array('mv_ean', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_ean'), $boxFormatText) : '';
		in_array('mv_jan', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_jan'), $boxFormatText) : '';
		in_array('mv_isbn', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_isbn'), $boxFormatText) : '';
		in_array('mv_mpn', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_mpn'), $boxFormatText) : '';
		in_array('mv_model', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_model'), $boxFormatText) : '';
		in_array('mv_category', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_category'), $boxFormatText) : '';
		in_array('mv_manufacturer', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_manufacturer'), $boxFormatText) : '';
		in_array('mv_supplier', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_supplier'), $boxFormatText) : '';
		in_array('mv_attribute', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_attribute'), $boxFormatText) : '';
		in_array('mv_status', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_status'), $boxFormatText) : '';
		in_array('mv_location', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_location'), $boxFormatText) : '';
		in_array('mv_tax_class', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_tax_class'), $boxFormatText) : '';
		in_array('mv_viewed', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_viewed'), $boxFormatNumber) : '';
		in_array('mv_price', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_price'), $boxFormatNumber) : '';
		in_array('mv_cost', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_cost'), $boxFormatNumber) : '';
		in_array('mv_profit', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_profit'), $boxFormatNumber) : '';
		in_array('mv_profit_margin', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_profit_margin'), $boxFormatNumber) : '';
		in_array('mv_profit_markup', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_profit_markup'), $boxFormatNumber) : '';
		in_array('mv_stock_quantity', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_stock_quantity'), $boxFormatNumber) : '';
		in_array('mv_orders', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_orders'), $boxFormatNumber) : '';
		in_array('mv_customers', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_customers'), $boxFormatNumber) : '';
		in_array('mv_sold_quantity', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_sold_quantity'), $boxFormatNumber) : '';
		in_array('mv_sold_percent', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_sold_percent'), $boxFormatNumber) : '';
		in_array('mv_total_excl_vat', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_total_excl_vat'), $boxFormatNumber) : '';
		in_array('mv_total_tax', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_tax'), $boxFormatNumber) : '';
		in_array('mv_total_incl_vat', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_total_incl_vat'), $boxFormatNumber) : '';
		in_array('mv_app', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_app'), $boxFormatNumber) : '';	
		in_array('mv_discount', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_product_discount'), $boxFormatNumber) : '';
		in_array('mv_refunds', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_product_refunds'), $boxFormatNumber) : '';
		in_array('mv_reward_points', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_product_reward_points'), $boxFormatNumber) : '';
		in_array('mv_total_sales', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_sales'), $boxFormatNumber) : '';
		in_array('mv_total_costs', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_costs'), $boxFormatNumber) : '';
		in_array('mv_total_profit', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_profit'), $boxFormatNumber) : '';
		in_array('mv_total_profit_margin', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_profit_margin'), $boxFormatNumber) : '';
		in_array('mv_total_profit_markup', $advppp_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_profit_markup'), $boxFormatNumber) : '';
		}
		
		// The actual orders data
		$i += 1;
		$j = 0;
		
			foreach ($results as $result) {
			$excelRow = $i+1;
				if ($filter_report == 'all_products_with_without_orders' or $filter_report == 'products_without_orders') {
				$worksheet->write($i, $j++, $result['date_added'], $textFormat);
				} elseif ($filter_report == 'products_shopping_carts' or $filter_report == 'products_wishlists') {
				$worksheet->write($i, $j++, $result['date_start'], $textFormat);
				$worksheet->write($i, $j++, $result['date_end'], $textFormat);
				} else {
				if ($filter_group == 'year') {	
				$worksheet->write($i, $j++, $result['year'], $textFormat);
				} elseif ($filter_group == 'quarter') {
				$worksheet->write($i, $j++, $result['year'], $textFormat);
				$worksheet->write($i, $j++, 'Q' . $result['quarter'], $textFormat);		
				} elseif ($filter_group == 'month') {
				$worksheet->write($i, $j++, $result['year'], $textFormat);
				$worksheet->write($i, $j++, $result['month'], $textFormat);
				} elseif ($filter_group == 'day') {
				$worksheet->write($i, $j++, $result['date_start'], $textFormat);
				} elseif ($filter_group == 'order') {
				$worksheet->write($i, $j++, $result['order_id'], $textFormat);
				$worksheet->write($i, $j++, $result['date_start'], $textFormat);
				} else {
				$worksheet->write($i, $j++, $result['date_start'], $textFormat);
				$worksheet->write($i, $j++, $result['date_end'], $textFormat);
				}
				}
				
				if ($filter_report == 'products_shopping_carts' or $filter_report == 'products_wishlists') {
					
				in_array('scw_id', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['product_id'], $textFormat) : '';
				in_array('scw_name', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, html_entity_decode($result['name'])) : '';
				if ($filter_report == 'products_shopping_carts') {	
				if ($result['option']) {	
				$options = '';
				foreach ($result['option'] as $option) {
				$options .= $option['name'].': '.$option['value'].'; ';
				}
				in_array('scw_name', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, rtrim($options, "; ")) : '';
				} else {
				in_array('scw_name', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, '') : '';
				}				
				}	
				in_array('scw_sku', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['sku'], $textFormat) : '';
				in_array('scw_upc', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['upc'], $textFormat) : '';
				in_array('scw_ean', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['ean'], $textFormat) : '';
				in_array('scw_jan', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['jan'], $textFormat) : '';
				in_array('scw_isbn', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['isbn'], $textFormat) : '';
				in_array('scw_mpn', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['mpn'], $textFormat) : '';
				in_array('scw_model', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['model']) : '';
				in_array('scw_category', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, html_entity_decode($result['categories'])) : '';
				in_array('scw_manufacturer', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, html_entity_decode($result['manufacturers'])) : '';
				in_array('scw_supplier', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, html_entity_decode($result['suppliers'])) : '';
				in_array('scw_attribute', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, html_entity_decode(str_replace('<br>','; ',$result['attribute']))) : '';
				in_array('scw_status', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['status']) : '';
				in_array('scw_location', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['location']) : '';
				in_array('scw_tax_class', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['tax_class']) : '';
				in_array('scw_viewed', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['viewed']) : '';
				in_array('scw_price', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['price_raw'], $ppriceColumnFormat) : '';
				in_array('scw_cost', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, -$result['cost_raw'], $pcostColumnFormat) : '';
				if ($result['profit_raw'] >= 0) {
				in_array('scw_profit', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['profit_raw'], $pprofitColumnFormat) : '';
				} else {
				in_array('scw_profit', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['profit_raw'], $pnoprofitColumnFormat) : '';
				}
				if ($result['profit_raw'] >= 0) {
				in_array('scw_profit_margin', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['profit_margin'])/100, $ppercentColumnFormat) : '';
				} else {
				in_array('scw_profit_margin', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['profit_margin'])/100, $pnopercentColumnFormat) : '';
				}
				if ($result['profit_raw'] >= 0) {
				in_array('scw_profit_markup', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['profit_markup'])/100, $ppercentColumnFormat) : '';
				} else {
				in_array('scw_profit_markup', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['profit_markup'])/100, $pnopercentColumnFormat) : '';
				}				
				if ($filter_report == 'products_shopping_carts') {	
				if ($result['option']) {	
				$oquantity = '';
				foreach ($result['option'] as $option) {
				$oquantity .= $option['quantity'].'; ';
				}
				in_array('scw_stock_quantity', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['stock_quantity'] . " [" . rtrim($oquantity, "; ") . "]", $numberFormat) : '';
				} else {
				in_array('scw_stock_quantity', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['stock_quantity']) : '';
				}
				} else {
				in_array('scw_stock_quantity', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['stock_quantity']) : '';
				}
				if ($filter_report == 'products_shopping_carts') {
				in_array('scw_cart_quantity', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['cart_quantity'], $soldColumnFormat) : '';
				}
				if ($filter_report == 'products_wishlists') {
				in_array('scw_wishlist_quantity', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $result['wishlist_quantity'], $soldColumnFormat) : '';
				}
				if ($result['customer']) {	
				foreach ($result['customer'] as $customer) {
				$c = $j;
				in_array('scw_customer_id', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['customer_id'], $textFormat) : '';
				in_array('scw_date_added', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['date_added']) : '';
				in_array('scw_customer_name', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['customer_name']) : '';
				in_array('scw_customer_email', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['email']) : '';
				in_array('scw_customer_telephone', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['telephone']) : '';
				in_array('scw_customer_group', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['customer_group']) : '';
				in_array('scw_billing_company', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['company']) : '';
				in_array('scw_billing_address_1', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['address_1']) : '';
				in_array('scw_billing_address_2', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['address_2']) : '';
				in_array('scw_billing_city', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['city']) : '';
				in_array('scw_billing_zone', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['zone']) : '';
				in_array('scw_billing_postcode', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['postcode'], $textFormat) : '';
				in_array('scw_billing_country', $advppp_settings_scw_columns) ? $worksheet->write($i, $j++, $customer['country']) : '';
				$j = $c;
				$i += 1;
				}
				}
				
				} elseif ($filter_report == 'products_without_orders') {
					
				in_array('pnp_id', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['product_id'], $textFormat) : '';
				in_array('pnp_name', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, html_entity_decode($result['name'])) : '';			
				in_array('pnp_sku', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['sku'], $textFormat) : '';
				in_array('pnp_upc', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['upc'], $textFormat) : '';
				in_array('pnp_ean', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['ean'], $textFormat) : '';
				in_array('pnp_jan', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['jan'], $textFormat) : '';
				in_array('pnp_isbn', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['isbn'], $textFormat) : '';
				in_array('pnp_mpn', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['mpn'], $textFormat) : '';
				in_array('pnp_model', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['model']) : '';
				in_array('pnp_category', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, html_entity_decode($result['categories'])) : '';
				in_array('pnp_manufacturer', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, html_entity_decode($result['manufacturers'])) : '';
				in_array('pnp_supplier', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, html_entity_decode($result['suppliers'])) : '';
				in_array('pnp_attribute', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, html_entity_decode(str_replace('<br>','; ',$result['attribute']))) : '';
				in_array('pnp_status', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['status']) : '';
				in_array('pnp_location', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['location']) : '';
				in_array('pnp_tax_class', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['tax_class']) : '';
				in_array('pnp_viewed', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['viewed']) : '';
				in_array('pnp_price', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['price_raw'], $ppriceColumnFormat) : '';
				in_array('pnp_cost', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, -$result['cost_raw'], $pcostColumnFormat) : '';
				if ($result['profit_raw'] >= 0) {
				in_array('pnp_profit', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['profit_raw'], $pprofitColumnFormat) : '';
				} else {
				in_array('pnp_profit', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['profit_raw'], $pnoprofitColumnFormat) : '';
				}
				if ($result['profit_raw'] >= 0) {
				in_array('pnp_profit_margin', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['profit_margin'])/100, $ppercentColumnFormat) : '';
				} else {
				in_array('pnp_profit_margin', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['profit_margin'])/100, $pnopercentColumnFormat) : '';
				}
				if ($result['profit_raw'] >= 0) {
				in_array('pnp_profit_markup', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['profit_markup'])/100, $ppercentColumnFormat) : '';
				} else {
				in_array('pnp_profit_markup', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['profit_markup'])/100, $pnopercentColumnFormat) : '';
				}				
				in_array('pnp_stock_quantity', $advppp_settings_pnp_columns) ? $worksheet->write($i, $j++, $result['stock_quantity']) : '';
				
				} elseif ($filter_report == 'manufacturers' or $filter_report == 'categories' or $filter_report == 'suppliers') {
					
				if ($filter_report == 'manufacturers') {
				$worksheet->write($i, $j++, html_entity_decode($result['manufacturers']));
				} elseif ($filter_report == 'categories') {
				$worksheet->write($i, $j++, html_entity_decode($result['categories']));
				} elseif ($filter_report == 'suppliers') {
				$worksheet->write($i, $j++, html_entity_decode($result['suppliers']));
				}
				in_array('cm_orders', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['orders']) : '';
				in_array('cm_customers', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['customers']) : '';
				in_array('cm_sold_quantity', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['sold_quantity'], $soldColumnFormat) : '';
				in_array('cm_sold_percent', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['sold_quantity'] != 0 ? round(100 * ($result['sold_quantity'] / $result['sold_quantity_total']), 2)/100 : 0 / 100, $percentFormat) : '';
				in_array('cm_total_excl_vat', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['total_excl_vat_raw'] != NULL ? $result['total_excl_vat_raw'] : '0.00', $saleFormat) : '';
				in_array('cm_total_tax', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['total_tax_raw'] != NULL ? $result['total_tax_raw'] : '0.00', $priceFormat) : '';
				in_array('cm_total_incl_vat', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['total_incl_vat_raw'] != NULL ? $result['total_incl_vat_raw'] : '0.00', $priceFormat) : '';
				in_array('cm_app', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['app_raw'], $priceFormat) : '';
				in_array('cm_discount', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['discount_raw'] != NULL ? $result['discount_raw'] : '0.00', $priceFormat) : '';
				if ($this->config->get('advppp' . $user_id . '_formula_6')) {	
				in_array('cm_refunds', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['refunds_raw'] != NULL ? -$result['refunds_raw'] : '0.00', $saleFormat) : '';
				} else {
				in_array('cm_refunds', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['refunds_raw'] != NULL ? $result['refunds_raw'] : '0.00', $priceFormat) : '';
				}
				in_array('cm_reward_points', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['reward_points']) : '';
				in_array('cm_total_sales', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['total_sales_raw'] != NULL ? $result['total_sales_raw'] : '0.00', $salesColumnFormat) : '';
				in_array('cm_total_costs', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['total_costs_raw'] != NULL ? -$result['total_costs_raw'] : '0.00', $costsColumnFormat) : '';
				if ($result['total_profit_raw'] >= 0) {
				in_array('cm_total_profit', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['total_profit_raw'] != NULL ? $result['total_profit_raw'] : '0.00', $profitColumnFormat) : '';
				} else {
				in_array('cm_total_profit', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, $result['total_profit_raw'] != NULL ? $result['total_profit_raw'] : '0.00', $noprofitColumnFormat) : '';
				}
				if ($result['sold_quantity'] > 0) {
				if ($result['total_profit_raw'] >= 0) {
				in_array('cm_total_profit_margin', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['total_profit_margin'])/100, $percentColumnFormat) : '';
				} else {
				in_array('cm_total_profit_margin', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['total_profit_margin'])/100, $nopercentColumnFormat) : '';
				}					
				} else {
				in_array('cm_total_profit_margin', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, '0', $percentColumnFormat) : '';
				}
				if ($result['sold_quantity'] > 0) {
				if ($result['total_profit_raw'] >= 0) {
				in_array('cm_total_profit_markup', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['total_profit_markup'])/100, $percentColumnFormat) : '';
				} else {
				in_array('cm_total_profit_markup', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['total_profit_markup'])/100, $nopercentColumnFormat) : '';
				}					
				} else {
				in_array('cm_total_profit_markup', $advppp_settings_cm_columns) ? $worksheet->write($i, $j++, '0', $percentColumnFormat) : '';
				}
				
				} elseif ($filter_report == 'products_options') {
					
				$worksheet->write($i, $j++, $result['option_name']);
				in_array('mv_orders', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['orders']) : '';
				in_array('mv_customers', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['customers']) : '';
				$worksheet->write($i, $j++, $result['sold_quantity'], $soldColumnFormat);
				$worksheet->write($i, $j++, $result['sold_quantity'] != 0 ? round(100 * ($result['sold_quantity'] / $result['sold_quantity_total']), 2)/100 : 0 / 100, $percentFormat);
	
				} else {
					
				in_array('mv_id', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['product_id'], $textFormat) : '';
				in_array('mv_name', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, html_entity_decode($result['name'])) : '';
				if ($filter_report == 'products_purchased_with_options' or $filter_report == 'products_abandoned_orders') {	
				$t = $j;	
				if ($result['option']) {
				foreach ($result['option'] as $index => $option) {
				if ($option['name'] == $option_names[$index]['name']) {				
				in_array('mv_name', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $option['value']) : '';
				//in_array('mv_name', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $option['name'].': '.$option['value']) : '';				
				} else {
				foreach ($result['option'] as $option) {
				foreach ($option_names as $option_name) {			
				if ($option['name'] == $option_name['name']) {		
				in_array('mv_name', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $option['value']) : '';
				//in_array('mv_name', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $option['name'].': '.$option['value']) : '';	
				} else {
				in_array('mv_name', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';	
				}			
				}
				}			
				}
				}	
				} else {
				foreach ($option_names as $option_name) {
				in_array('mv_name', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';	
				}
				}
				$j=$t+$n;	
				}
				in_array('mv_sku', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['sku'], $textFormat) : '';
				in_array('mv_upc', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['upc'], $textFormat) : '';
				in_array('mv_ean', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['ean'], $textFormat) : '';
				in_array('mv_jan', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['jan'], $textFormat) : '';
				in_array('mv_isbn', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['isbn'], $textFormat) : '';
				in_array('mv_mpn', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['mpn'], $textFormat) : '';
				in_array('mv_model', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['model']) : '';
				in_array('mv_category', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, html_entity_decode($result['categories'])) : '';
				in_array('mv_manufacturer', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, html_entity_decode($result['manufacturers'])) : '';
				in_array('mv_supplier', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, html_entity_decode($result['suppliers'])) : '';
				in_array('mv_attribute', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, html_entity_decode(str_replace('<br>','; ',$result['attribute']))) : '';
				in_array('mv_status', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['status']) : '';
				in_array('mv_location', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['location']) : '';
				in_array('mv_tax_class', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['tax_class']) : '';
				in_array('mv_viewed', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['viewed']) : '';
				if ($filter_report == 'products_purchased_with_options' or $filter_report == 'products_abandoned_orders') {
				if ($result['option']) {	
				$oprice = '';
				foreach ($result['option'] as $option) {
				$oprice .= sprintf('%0.2f', round($option['price'], 2)).'; ';
				}
				in_array('mv_price', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, sprintf('%0.2f', round($result['price_raw'], 2)) . " [" . rtrim($oprice, "; ") . "]", $ppriceColumnFormat) : '';
				} else {
				in_array('mv_price', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['price_raw'], $ppriceColumnFormat) : '';
				}
				} else {
				in_array('mv_price', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['price_raw'], $ppriceColumnFormat) : '';
				}
				if ($filter_report == 'products_purchased_with_options' or $filter_report == 'products_abandoned_orders') {
				if ($result['option']) {	
				$ocost = '';
				foreach ($result['option'] as $option) {
				$ocost .= sprintf('%0.2f', round($option['cost'], 2)).'; ';
				}
				in_array('mv_cost', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, sprintf('%0.2f', round(-$result['cost_raw'], 2)) . " [" . rtrim($ocost, "; ") . "]", $pcostColumnFormat) : '';
				} else {
				in_array('mv_cost', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['cost_raw'], $pcostColumnFormat) : '';
				}
				} else {
				in_array('mv_cost', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['cost_raw'], $pcostColumnFormat) : '';
				}
				if ($result['profit_raw'] >= 0) {
				in_array('mv_profit', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['profit_raw'], $pprofitColumnFormat) : '';
				} else {
				in_array('mv_profit', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['profit_raw'], $pnoprofitColumnFormat) : '';
				}
				if ($result['profit_raw'] >= 0) {
				in_array('mv_profit_margin', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['profit_margin'])/100, $ppercentColumnFormat) : '';
				} else {
				in_array('mv_profit_margin', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['profit_margin'])/100, $pnopercentColumnFormat) : '';
				}					
				if ($result['profit_raw'] >= 0) {
				in_array('mv_profit_markup', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['profit_markup'])/100, $ppercentColumnFormat) : '';
				} else {
				in_array('mv_profit_markup', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['profit_markup'])/100, $pnopercentColumnFormat) : '';
				}	
				if ($filter_report == 'products_purchased_with_options' or $filter_report == 'products_abandoned_orders') {
				if ($result['option']) {	
				$oquantity = '';
				foreach ($result['option'] as $option) {
				$oquantity .= $option['quantity'].'; ';
				}
				in_array('mv_stock_quantity', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['stock_quantity'] . " [" . rtrim($oquantity, "; ") . "]", $numberFormat) : '';
				} else {
				in_array('mv_stock_quantity', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['stock_quantity']) : '';
				}
				} else {
				in_array('mv_stock_quantity', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['stock_quantity']) : '';
				}
				in_array('mv_orders', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['orders']) : '';
				in_array('mv_customers', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['customers']) : '';	
				in_array('mv_sold_quantity', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['sold_quantity'], $soldColumnFormat) : '';
				in_array('mv_sold_percent', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['sold_quantity'] != 0 ? round(100 * ($result['sold_quantity'] / $result['sold_quantity_total']), 2)/100 : 0 / 100, $percentFormat) : '';
				in_array('mv_total_excl_vat', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['total_excl_vat_raw'] != NULL ? $result['total_excl_vat_raw'] : '0.00', $saleFormat) : '';
				in_array('mv_total_tax', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['total_tax_raw'] != NULL ? $result['total_tax_raw'] : '0.00', $priceFormat) : '';
				in_array('mv_total_incl_vat', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['total_incl_vat_raw'] != NULL ? $result['total_incl_vat_raw'] : '0.00', $priceFormat) : '';
				in_array('mv_app', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['app_raw'], $priceFormat) : '';
				in_array('mv_discount', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['discount_raw'] != NULL ? $result['discount_raw'] : '0.00', $priceFormat) : '';
				if ($this->config->get('advppp' . $user_id . '_formula_6')) {	
				in_array('mv_refunds', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['refunds_raw'] != NULL ? -$result['refunds_raw'] : '0.00', $saleFormat) : '';
				} else {
				in_array('mv_refunds', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['refunds_raw'] != NULL ? $result['refunds_raw'] : '0.00', $priceFormat) : '';
				}
				in_array('mv_reward_points', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['reward_points'], $filter_report != 'products_abandoned_orders' ? $numberFormat : $abnumberFormat) : '';
				in_array('mv_total_sales', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['total_sales_raw'] != NULL ? $result['total_sales_raw'] : '0.00', $salesColumnFormat) : '';
				in_array('mv_total_costs', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['total_costs_raw'] != NULL ? -$result['total_costs_raw'] : '0.00', $costsColumnFormat) : '';
				if ($result['total_profit_raw'] >= 0) {
				in_array('mv_total_profit', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['total_profit_raw'] != NULL ? $result['total_profit_raw'] : '0.00', $profitColumnFormat) : '';
				} else {
				in_array('mv_total_profit', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, $result['total_profit_raw'] != NULL ? $result['total_profit_raw'] : '0.00', $noprofitColumnFormat) : '';
				}
				if ($result['sold_quantity'] > 0) {
				if ($result['total_profit_raw'] >= 0) {
				in_array('mv_total_profit_margin', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['total_profit_margin'])/100, $percentColumnFormat) : '';
				} else {
				in_array('mv_total_profit_margin', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['total_profit_margin'])/100, $nopercentColumnFormat) : '';
				}					
				} else {
				in_array('mv_total_profit_margin', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, '0', $percentColumnFormat) : '';
				}
				if ($result['sold_quantity'] > 0) {
				if ($result['total_profit_raw'] >= 0) {
				in_array('mv_total_profit_markup', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['total_profit_markup'])/100, $percentColumnFormat) : '';
				} else {
				in_array('mv_total_profit_markup', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['total_profit_markup'])/100, $nopercentColumnFormat) : '';
				}					
				} else {
				in_array('mv_total_profit_markup', $advppp_settings_mv_columns) ? $worksheet->write($i, $j++, '0', $percentColumnFormat) : '';
				}				
				}

				$i += 1;
				$j = 0;
			}

		$freeze = ($export_logo_criteria ? array(5, 0, 5, 0) : array(1, 0, 1, 0));
		$worksheet->freezePanes($freeze);
		
		// Let's send the file		
		$workbook->close();
		
		// Clear the spreadsheet caches
		$this->clearSpreadsheetCache();

		if (isset($_GET['cron'])) {				
			$message  = '<html dir="ltr" lang="en">' . "\n";
			$message .= '  <head>' . "\n";
			$message .= '    <title>' . $this->language->get('text_email_subject') . '</title>' . "\n";
			$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
			$message .= '  </head>' . "\n";
			$message .= '  <body>' . "\n";
			if ($export_file == 'save_on_server') {			
				$message .= '<p>' . $this->language->get('text_email_message_save_file') . ' <a href="' . $file_to_download . '">' . $filename . '</a>.</p><br />' . "\n";
			} else if ($export_file == 'send_to_email') {
				$message .= '<p>' . $this->language->get('text_email_message_send_file') . '</p><br />' . "\n";
			}
			$message .= '<p><b>' . $this->config->get('config_name') . '</b><br />' . "\n";
			$message .= $this->config->get('config_address') . '</p>' . "\n";			
			$message .= '</body>' . "\n";
			$message .= '</html>' . "\n";

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
			$mail->setTo($email);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode($this->language->get('text_email_subject'), ENT_QUOTES, 'UTF-8'));
			if ($export_file == 'save_on_server') {			
				$mail->setHtml($message);
			} else if ($export_file == 'send_to_email') {
				$mail->setHtml($message);
				$mail->addAttachment($file);
			}			
			$mail->send();
		}		
		exit;
	}		
?>