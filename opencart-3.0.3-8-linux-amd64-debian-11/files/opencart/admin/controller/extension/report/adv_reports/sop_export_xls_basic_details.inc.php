<?php
	ini_set("memory_limit","256M");
	
	$results = $export_data['results'];
	if ($results) {
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
			$workbook->send('sales_profit_report_basic_details_'.date($this->config->get('advsop' . $user_id . '_hour_format') == '24' ? "Y-m-d_H-i-s" : "Y-m-d_h-i-s-A").'.xls');
			$worksheet =& $workbook->addWorksheet('ADV Sales Profit Report');
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
		
			$filename = $file_name."_".date($this->config->get('advsop' . $user_id . '_hour_format') == '24' ? "Y-m-d_H-i-s" : "Y-m-d_h-i-s-A").".xls";
			$file_to_download = $server . $file_save_path . '/' . $file_name."_".date($this->config->get('advsop' . $user_id . '_hour_format') == '24' ? "Y-m-d_H-i-s" : "Y-m-d_h-i-s-A").".xls";
			$file = $file_path . '/' . $file_name."_".date($this->config->get('advsop' . $user_id . '_hour_format') == '24' ? "Y-m-d_H-i-s" : "Y-m-d_h-i-s-A").".xls";		
		
			// Creating a workbook
			$workbook = new Spreadsheet_Excel_Writer($file);
			$workbook->setTempDir(DIR_CACHE);
			$workbook->setVersion(8); // Use Excel97/2000 BIFF8 Format
		
			// sending HTTP headers
			$worksheet =& $workbook->addWorksheet('ADV Sales Profit Report');
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
		if ($filter_report == 'abandoned_orders') {
		$abtextFormat =& $workbook->addFormat(array('Align' => 'left', 'NumFormat' => "@"));
		$abtextFormat->setStrikeOut();
		}	
		
		$numberFormat =& $workbook->addFormat(array('Align' => 'right'));
		if ($filter_report == 'abandoned_orders') {
		$abnumberFormat =& $workbook->addFormat(array('Align' => 'right'));
		$abnumberFormat->setStrikeOut();
		}
		
		$priceFormat =& $workbook->addFormat(array('Align' => 'right'));
		$priceFormat->setNumFormat('0.00');
		if ($filter_report == 'abandoned_orders') {
		$priceFormat->setStrikeOut();	
		}

		$saleFormat =& $workbook->addFormat(array('Align' => 'right'));
		$saleFormat->setColor('green');	
		$saleFormat->setNumFormat('0.00');
		if ($filter_report == 'abandoned_orders') {
		$saleFormat->setStrikeOut();
		}

		$costFormat =& $workbook->addFormat(array('Align' => 'right'));
		$costFormat->setColor('red');		
		$costFormat->setNumFormat('0.00');
		if ($filter_report == 'abandoned_orders') {
		$costFormat->setStrikeOut();
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
		if ($filter_report == 'abandoned_orders') {
		$soldColumnFormat->setStrikeOut();
		}		
		$percentFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '27', 'bordercolor' => 'silver'));
		$percentFormat->setBorder(1);	
		$percentFormat->setNumFormat('0.00%');
		if ($filter_report == 'abandoned_orders') {
		$percentFormat->setStrikeOut();
		}
		
		$workbook->setCustomColor(42, 220, 255, 185);
		$salesColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '42', 'bordercolor' => 'silver'));
		$salesColumnFormat->setBorder(1);	
		$salesColumnFormat->setNumFormat('0.00');
		if ($filter_report == 'abandoned_orders') {
		$salesColumnFormat->setStrikeOut();
		}
		
		$workbook->setCustomColor(29, 255, 215, 215);
		$costsColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '29', 'bordercolor' => 'silver'));
		$costsColumnFormat->setBorder(1);		
		$costsColumnFormat->setNumFormat('0.00');
		if ($filter_report == 'abandoned_orders') {
		$costsColumnFormat->setStrikeOut();
		}
		
		$workbook->setCustomColor(44, 196, 217, 238);
		$profitColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '44', 'bold' => 1, 'bordercolor' => 'silver'));
		$profitColumnFormat->setBorder(1);		
		$profitColumnFormat->setNumFormat('0.00');
		$abprofitColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '44', 'bold' => 1, 'bordercolor' => 'silver'));
		if ($filter_report == 'abandoned_orders') {
		$profitColumnFormat->setStrikeOut();
		}	
		$percentColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '44', 'bold' => 1, 'bordercolor' => 'silver'));
		$percentColumnFormat->setBorder(1);		
		$percentColumnFormat->setNumFormat('0.00%');
		if ($filter_report == 'abandoned_orders') {
		$percentColumnFormat->setStrikeOut();
		}

		$workbook->setCustomColor(45, 249, 153, 153);
		$noprofitColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '45', 'bold' => 1, 'bordercolor' => 'silver'));
		$noprofitColumnFormat->setBorder(1);		
		$noprofitColumnFormat->setNumFormat('0.00');
		if ($filter_report == 'abandoned_orders') {
		$noprofitColumnFormat->setStrikeOut();
		}
		$nopercentColumnFormat =& $workbook->addFormat(array('Align' => 'right', 'FgColor' => '45', 'bold' => 1, 'bordercolor' => 'silver'));
		$nopercentColumnFormat->setBorder(1);		
		$nopercentColumnFormat->setNumFormat('0.00%');
		if ($filter_report == 'abandoned_orders') {
		$nopercentColumnFormat->setStrikeOut();
		}
		
		$boxFormatText =& $workbook->addFormat(array('bold' => 1));
		$boxFormatNumber =& $workbook->addFormat(array('Align' => 'right', 'bold' => 1));

		// Set the column widths
		$j = 0;
		if ($filter_report == 'sales_summary' or $filter_report == 'abandoned_orders') {
		if ($filter_group == 'year') {	
		$worksheet->setColumn($j,$j++,10);
		} elseif ($filter_group == 'quarter') {
		$worksheet->setColumn($j,$j++,10);
		$worksheet->setColumn($j,$j++,10);	
		} elseif ($filter_group == 'month') {
		$worksheet->setColumn($j,$j++,10);
		$worksheet->setColumn($j,$j++,13);
		} elseif ($filter_group == 'day') {
		$worksheet->setColumn($j,$j++,13);
		} elseif ($filter_group == 'order') {
		$worksheet->setColumn($j,$j++,10);
		$worksheet->setColumn($j,$j++,13);
		} else {
		$worksheet->setColumn($j,$j++,13);
		$worksheet->setColumn($j,$j++,13);
		}	
		} elseif ($filter_report == 'day_of_week') {
		$worksheet->setColumn($j,$j++,15);
		} elseif ($filter_report == 'hour') {
		$worksheet->setColumn($j,$j++,10);
		} elseif ($filter_report == 'store') {
		$worksheet->setColumn($j,$j++,15);
		} elseif ($filter_report == 'currency') {
		$worksheet->setColumn($j,$j++,13);
		} elseif ($filter_report == 'customer_group') {
		$worksheet->setColumn($j,$j++,15);
		} elseif ($filter_report == 'country') {
		$worksheet->setColumn($j,$j++,15);
		} elseif ($filter_report == 'postcode') {
		$worksheet->setColumn($j,$j++,18);
		} elseif ($filter_report == 'region_state') {
		$worksheet->setColumn($j,$j++,25);
		} elseif ($filter_report == 'city') {
		$worksheet->setColumn($j,$j++,25);
		} elseif ($filter_report == 'payment_method') {
		$worksheet->setColumn($j,$j++,20);
		} elseif ($filter_report == 'shipping_method') {
		$worksheet->setColumn($j,$j++,20);
		} elseif ($filter_report == 'coupon') {
		$worksheet->setColumn($j,$j++,13);
		$worksheet->setColumn($j,$j++,13);
		} elseif ($filter_report == 'voucher') {
		$worksheet->setColumn($j,$j++,13);
		$worksheet->setColumn($j,$j++,13);	
		}
		if ($filter_report == 'coupon') {
		in_array('cr_coupon_name', $advsop_settings_cr_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('cr_coupon_code', $advsop_settings_cr_columns) ? $worksheet->setColumn($j,$j++,16) : '';
		in_array('cr_coupon_discount', $advsop_settings_cr_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cr_coupon_type', $advsop_settings_cr_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cr_coupon_status', $advsop_settings_cr_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cr_coupon_date_added', $advsop_settings_cr_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cr_coupon_last_used', $advsop_settings_cr_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cr_coupon_amount', $advsop_settings_cr_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('cr_coupon_orders', $advsop_settings_cr_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('cr_coupon_total', $advsop_settings_cr_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		} elseif ($filter_report == 'voucher') {
		in_array('vr_voucher_code', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,16) : '';
		in_array('vr_voucher_from_name', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('vr_voucher_from_email', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('vr_voucher_to_name', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('vr_voucher_to_email', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('vr_voucher_theme', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('vr_voucher_status', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('vr_voucher_date_added', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('vr_voucher_amount', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,15) : '';	
		in_array('vr_voucher_used_value', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,15) : '';	
		in_array('vr_voucher_remaining_value', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,15) : '';	
		in_array('vr_voucher_orders', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('vr_voucher_total', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		} else {
		in_array('mv_orders', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('mv_customers', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('mv_products', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('mv_sub_total', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_handling', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_loworder', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_shipping', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_reward', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_earned_points', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('mv_used_points', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('mv_coupon', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_tax', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_credit', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_voucher', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_discount', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('mv_total', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_aov', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_refunds', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,16) : '';
		in_array('mv_total_sales', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_product_costs', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_commission', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('mv_payment_costs', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,14) : '';
		in_array('mv_shipping_costs', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,14) : '';
		in_array('mv_extra_costs', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_return_costs', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_shipping_balance', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,16) : '';
		in_array('mv_total_costs', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('mv_total_profit', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('mv_total_profit_prc', $advsop_settings_mv_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		}	

		in_array('ol_order_order_id', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('ol_order_date_added', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('ol_order_inv_no', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('ol_order_customer', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('ol_order_email', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('ol_order_customer_group', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('ol_order_shipping_method', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('ol_order_payment_method', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('ol_order_status', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('ol_order_store', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('ol_order_currency', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('ol_order_quantity', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('ol_order_sub_total', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('ol_order_shipping', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('ol_order_tax', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		if ($filter_report == 'coupon') {
		in_array('cr_coupon_amount', $advsop_settings_cr_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		} elseif ($filter_report == 'voucher') {
		in_array('vr_voucher_used_value', $advsop_settings_vr_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		}
		in_array('ol_order_value', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('ol_order_sales', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,14) : '';
		in_array('ol_order_costs', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('ol_order_profit', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('ol_order_profit_prc', $advsop_settings_ol_columns) ? $worksheet->setColumn($j,$j++,13) : '';		

		in_array('cl_customer_cust_id', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,11) : '';			 
		in_array('cl_billing_name', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('cl_billing_company', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('cl_billing_address_1', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('cl_billing_address_2', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('cl_billing_city', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('cl_billing_zone', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cl_billing_postcode', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,17) : '';
		in_array('cl_billing_country', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('cl_customer_telephone', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cl_shipping_name', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('cl_shipping_company', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('cl_shipping_address_1', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('cl_shipping_address_2', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('cl_shipping_city', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,20) : '';
		in_array('cl_shipping_zone', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('cl_shipping_postcode', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,17) : '';
		in_array('cl_shipping_country', $advsop_settings_cl_columns) ? $worksheet->setColumn($j,$j++,20) : '';

		in_array('pl_prod_order_id', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('pl_prod_date_added', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pl_prod_id', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('pl_prod_sku', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pl_prod_model', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pl_prod_name', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,25) : '';
		in_array('pl_prod_option', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,18) : '';
		in_array('pl_prod_attributes', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,20) : '';			
		in_array('pl_prod_category', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,18) : '';	
		in_array('pl_prod_manu', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,18) : '';			
		in_array('pl_prod_supplier', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,18) : ''; 		 	
		in_array('pl_prod_currency', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,10) : '';
		in_array('pl_prod_price', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pl_prod_quantity', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('pl_prod_total_excl_vat', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pl_prod_tax', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pl_prod_total_incl_vat', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pl_prod_sales', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,15) : '';
		in_array('pl_prod_cost', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pl_prod_profit', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pl_prod_profit_margin', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,13) : '';
		in_array('pl_prod_profit_markup', $advsop_settings_pl_columns) ? $worksheet->setColumn($j,$j++,13) : ''; 
		
		if ($export_logo_criteria) {
		$worksheet->setMerge(0, 0, 0, 1);
		$worksheet->writeString(0, 0, '', $criteriaDateFormat);			
		$worksheet->setMerge(0, 2, 0, $j-1);
		$worksheet->writeString(0, 2, $this->language->get('text_report_date').": ".date($this->config->get('advsop' . $user_id . '_hour_format') == '24' ? "Y-m-d H:i:s" : "Y-m-d h:i:s A"), $criteriaDateFormat);
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
				if ($filter_report == 'sales_summary') {
					$filter_report_name = $this->language->get('text_sales_summary');
				} elseif ($filter_report == 'day_of_week') {
					$filter_report_name = $this->language->get('text_day_of_week');
				} elseif ($filter_report == 'hour') {
					$filter_report_name = $this->language->get('text_hour');
				} elseif ($filter_report == 'store') {
					$filter_report_name = $this->language->get('text_store');
				} elseif ($filter_report == 'currency') {
					$filter_report_name = $this->language->get('text_currency');
				} elseif ($filter_report == 'customer_group') {
					$filter_report_name = $this->language->get('text_customer_group');
				} elseif ($filter_report == 'tax') {
					$filter_report_name = $this->language->get('text_tax');
				} elseif ($filter_report == 'country') {
					$filter_report_name = $this->language->get('text_country');
				} elseif ($filter_report == 'postcode') {
					$filter_report_name = $this->language->get('text_postcode');
				} elseif ($filter_report == 'region_state') {
					$filter_report_name = $this->language->get('text_region_state');
				} elseif ($filter_report == 'city') {
					$filter_report_name = $this->language->get('text_city');
				} elseif ($filter_report == 'payment_method') {
					$filter_report_name = $this->language->get('text_payment_method');
				} elseif ($filter_report == 'shipping_method') {
					$filter_report_name = $this->language->get('text_shipping_method');					
				} elseif ($filter_report == 'coupon') {
					$filter_report_name = $this->language->get('text_coupons');					
				} elseif ($filter_report == 'voucher') {
					$filter_report_name = $this->language->get('text_gift_vouchers');					
				} elseif ($filter_report == 'abandoned_orders') {
					$filter_report_name = $this->language->get('text_abandoned_orders');			
				}	
				$filter_criteria .= $this->language->get('entry_report')." ".$filter_report_name."; ";
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
				if ($filter_group == 'year') {
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
				if ($filter_sort == 'report_type') {			
					if ($filter_report == 'sales_summary' or $filter_report == 'abandoned_orders' or $filter_report == 'tax' or $filter_report == 'coupon' or $filter_report == 'voucher') {
						$filter_sort_name = $this->language->get('column_date');
					} elseif ($filter_report == 'day_of_week') {
						$filter_sort_name = $this->language->get('column_day_of_week');
					} elseif ($filter_report == 'hour') {
						$filter_sort_name = $this->language->get('column_hour');
					} elseif ($filter_report == 'store') {
						$filter_sort_name = $this->language->get('column_store');
					} elseif ($filter_report == 'currency') {
						$filter_sort_name = $this->language->get('column_currency');
					} elseif ($filter_report == 'customer_group') {
						$filter_sort_name = $this->language->get('column_customer_group');
					} elseif ($filter_report == 'country') {
						$filter_sort_name = $this->language->get('column_country');
					} elseif ($filter_report == 'postcode') {
						$filter_sort_name = $this->language->get('column_postcode');
					} elseif ($filter_report == 'region_state') {
						$filter_sort_name = $this->language->get('column_region_state');
					} elseif ($filter_report == 'city') {
						$filter_sort_name = $this->language->get('column_city');
					} elseif ($filter_report == 'payment_method') {
						$filter_sort_name = $this->language->get('column_payment_method');
					} elseif ($filter_report == 'shipping_method') {
						$filter_sort_name = $this->language->get('column_shipping_method');
					}
					
				}
				if ($filter_report == 'tax') {
				if ($filter_sort == 'tax_name') {
					$filter_sort_name = $this->language->get('column_tax_title');
				} elseif ($filter_sort == 'tax_rate') {
					$filter_sort_name = $this->language->get('column_tax_rate');
				} elseif ($filter_sort == 'tax_orders') {
					$filter_sort_name = $this->language->get('column_orders');
				} elseif ($filter_sort == 'tax_total') {
					$filter_sort_name = $this->language->get('column_tax_total');
				}					
				} elseif ($filter_report == 'coupon') {
				if ($filter_sort == 'coupon_name') {
					$filter_sort_name = $this->language->get('column_coupon_name');
				} elseif ($filter_sort == 'coupon_code') {
					$filter_sort_name = $this->language->get('column_coupon_code');
				} elseif ($filter_sort == 'coupon_discount') {
					$filter_sort_name = $this->language->get('column_coupon_discount');
				} elseif ($filter_sort == 'coupon_type') {
					$filter_sort_name = $this->language->get('column_coupon_type');
				} elseif ($filter_sort == 'coupon_status') {
					$filter_sort_name = $this->language->get('column_coupon_status');
				} elseif ($filter_sort == 'coupon_date_added') {
					$filter_sort_name = $this->language->get('column_coupon_date_added');
				} elseif ($filter_sort == 'coupon_last_used') {
					$filter_sort_name = $this->language->get('column_coupon_last_used');
				} elseif ($filter_sort == 'coupon_amount') {
					$filter_sort_name = $this->language->get('column_coupon_amount');	
				} elseif ($filter_sort == 'coupon_orders') {
					$filter_sort_name = $this->language->get('column_orders');
				} elseif ($filter_sort == 'coupon_total') {
					$filter_sort_name = $this->language->get('column_total');					
				}	
				} elseif ($filter_report == 'voucher') {
				if ($filter_sort == 'voucher_code') {
					$filter_sort_name = $this->language->get('column_voucher_code');
				} elseif ($filter_sort == 'voucher_from_name') {
					$filter_sort_name = $this->language->get('column_voucher_from_name');
				} elseif ($filter_sort == 'voucher_from_email') {
					$filter_sort_name = $this->language->get('column_voucher_from_email');
				} elseif ($filter_sort == 'voucher_to_name') {
					$filter_sort_name = $this->language->get('column_voucher_to_name');
				} elseif ($filter_sort == 'voucher_to_email') {
					$filter_sort_name = $this->language->get('column_voucher_to_email');
				} elseif ($filter_sort == 'voucher_theme') {
					$filter_sort_name = $this->language->get('column_voucher_theme');
				} elseif ($filter_sort == 'voucher_status') {
					$filter_sort_name = $this->language->get('column_voucher_status');
				} elseif ($filter_sort == 'voucher_date_added') {
					$filter_sort_name = $this->language->get('column_voucher_date_added');
				} elseif ($filter_sort == 'voucher_amount') {
					$filter_sort_name = $this->language->get('column_voucher_amount');	
				} elseif ($filter_sort == 'voucher_used_value') {
					$filter_sort_name = $this->language->get('column_voucher_used_value');	
				} elseif ($filter_sort == 'voucher_remaining_value') {
					$filter_sort_name = $this->language->get('column_voucher_remaining_value');						
				} elseif ($filter_sort == 'voucher_orders') {
					$filter_sort_name = $this->language->get('column_orders');
				} elseif ($filter_sort == 'voucher_total') {
					$filter_sort_name = $this->language->get('column_total');					
				}
				} else {
				if ($filter_sort == 'orders') {
					$filter_sort_name = $this->language->get('column_orders');
				} elseif ($filter_sort == 'customers') {
					$filter_sort_name = $this->language->get('column_customers');
				} elseif ($filter_sort == 'products') {
					$filter_sort_name = $this->language->get('column_products');	
				} elseif ($filter_sort == 'sub_total') {
					$filter_sort_name = $this->language->get('column_sub_total');
				} elseif ($filter_sort == 'shipping') {
					$filter_sort_name = $this->language->get('column_shipping');
				} elseif ($filter_sort == 'reward') {
					$filter_sort_name = $this->language->get('column_reward');
				} elseif ($filter_sort == 'earned_reward_points') {
					$filter_sort_name = $this->language->get('column_earned_reward_points');	
				} elseif ($filter_sort == 'used_reward_points') {
					$filter_sort_name = $this->language->get('column_used_reward_points');
				} elseif ($filter_sort == 'coupon') {
					$filter_sort_name = $this->language->get('column_coupon');	
				} elseif ($filter_sort == 'tax') {
					$filter_sort_name = $this->language->get('column_taxes');
				} elseif ($filter_sort == 'credit') {
					$filter_sort_name = $this->language->get('column_credit');
				} elseif ($filter_sort == 'voucher') {
					$filter_sort_name = $this->language->get('column_voucher');
				} elseif ($filter_sort == 'discount') {
					$filter_sort_name = $this->language->get('column_discount');	
				} elseif ($filter_sort == 'total') {
					$filter_sort_name = $this->language->get('column_total');
				} elseif ($filter_sort == 'aov') {
					$filter_sort_name = $this->language->get('column_aov');	
				} elseif ($filter_sort == 'refunds') {
					$filter_sort_name = $this->language->get('column_refunds');
				} elseif ($filter_sort == 'total_sales') {
					$filter_sort_name = $this->language->get('column_total_sales');
				} elseif ($filter_sort == 'prod_costs') {
					$filter_sort_name = $this->language->get('column_product_costs');
				} elseif ($filter_sort == 'commission') {
					$filter_sort_name = $this->language->get('column_commission');	
				} elseif ($filter_sort == 'pay_costs') {
					$filter_sort_name = $this->language->get('column_payment_cost');
				} elseif ($filter_sort == 'ship_costs') {
					$filter_sort_name = $this->language->get('column_shipping_cost');	
				} elseif ($filter_sort == 'extra_cost') {
					$filter_sort_name = $this->language->get('column_extra_cost');
				} elseif ($filter_sort == 'return_cost') {
					$filter_sort_name = $this->language->get('column_return_cost');
				} elseif ($filter_sort == 'ship_balance') {
					$filter_sort_name = $this->language->get('column_shipping_balance');
				} elseif ($filter_sort == 'total_costs') {
					$filter_sort_name = $this->language->get('column_total_costs');	
				} elseif ($filter_sort == 'total_profit') {
					$filter_sort_name = $this->language->get('column_total_profit');
				} elseif ($filter_sort == 'total_profit_prc') {
					$filter_sort_name = $this->language->get('column_total_profit') . ' [%]';					
				}
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
			if ($filter_order_value_min or $filter_order_value_max) {
				$filter_criteria .= $this->language->get('entry_order_value').": ".$filter_order_value_min."-".$filter_order_value_max."; ";
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
		if ($filter_report == 'sales_summary' or $filter_report == 'abandoned_orders') {
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
		} elseif ($filter_report == 'day_of_week') {
		$worksheet->writeString($i, $j++, $this->language->get('column_day_of_week'), $boxFormatText);
		} elseif ($filter_report == 'hour') {
		$worksheet->writeString($i, $j++, $this->language->get('column_hour'), $boxFormatText);
		} elseif ($filter_report == 'store') {
		$worksheet->writeString($i, $j++, $this->language->get('column_store'), $boxFormatText);
		} elseif ($filter_report == 'currency') {
		$worksheet->writeString($i, $j++, $this->language->get('column_currency'), $boxFormatText);
		} elseif ($filter_report == 'customer_group') {
		$worksheet->writeString($i, $j++, $this->language->get('column_customer_group'), $boxFormatText);
		} elseif ($filter_report == 'country') {
		$worksheet->writeString($i, $j++, $this->language->get('column_country'), $boxFormatText);
		} elseif ($filter_report == 'postcode') {
		$worksheet->writeString($i, $j++, $this->language->get('column_postcode'), $boxFormatText);
		} elseif ($filter_report == 'region_state') {
		$worksheet->writeString($i, $j++, $this->language->get('column_region_state'), $boxFormatText);
		} elseif ($filter_report == 'city') {
		$worksheet->writeString($i, $j++, $this->language->get('column_city'), $boxFormatText);
		} elseif ($filter_report == 'payment_method') {
		$worksheet->writeString($i, $j++, $this->language->get('column_payment_method'), $boxFormatText);
		} elseif ($filter_report == 'shipping_method') {
		$worksheet->writeString($i, $j++, $this->language->get('column_shipping_method'), $boxFormatText);
		} elseif ($filter_report == 'coupon') {
		$worksheet->writeString($i, $j++, $this->language->get('column_date_start'), $boxFormatText);
		$worksheet->writeString($i, $j++, $this->language->get('column_date_end'), $boxFormatText);
		} elseif ($filter_report == 'voucher') {
		$worksheet->writeString($i, $j++, $this->language->get('column_date_start'), $boxFormatText);
		$worksheet->writeString($i, $j++, $this->language->get('column_date_end'), $boxFormatText);	
		}
		
		if ($filter_report == 'coupon') {
			
		in_array('cr_coupon_name', $advsop_settings_cr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_coupon_name'), $boxFormatText) : '';
		in_array('cr_coupon_code', $advsop_settings_cr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_coupon_code'), $boxFormatText) : '';
		in_array('cr_coupon_discount', $advsop_settings_cr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_coupon_discount'), $boxFormatNumber) : '';
		in_array('cr_coupon_type', $advsop_settings_cr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_coupon_type'), $boxFormatText) : '';
		in_array('cr_coupon_status', $advsop_settings_cr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_coupon_status'), $boxFormatText) : '';
		in_array('cr_coupon_date_added', $advsop_settings_cr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_coupon_date_added'), $boxFormatText) : '';
		in_array('cr_coupon_last_used', $advsop_settings_cr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_coupon_last_used'), $boxFormatText) : '';
		in_array('cr_coupon_amount', $advsop_settings_cr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_coupon_amount'), $boxFormatNumber) : '';
		in_array('cr_coupon_orders', $advsop_settings_cr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_orders'), $boxFormatNumber) : '';
		in_array('cr_coupon_total', $advsop_settings_cr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total'), $boxFormatNumber) : '';
		
		} elseif ($filter_report == 'voucher') {
			
		in_array('vr_voucher_code', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher_code'), $boxFormatText) : '';
		in_array('vr_voucher_from_name', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher_from_name'), $boxFormatText) : '';
		in_array('vr_voucher_from_email', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher_from_email'), $boxFormatText) : '';
		in_array('vr_voucher_to_name', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher_to_name'), $boxFormatText) : '';
		in_array('vr_voucher_to_email', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher_to_email'), $boxFormatText) : '';
		in_array('vr_voucher_theme', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher_theme'), $boxFormatText) : '';
		in_array('vr_voucher_status', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher_status'), $boxFormatText) : '';
		in_array('vr_voucher_date_added', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher_date_added'), $boxFormatText) : '';
		in_array('vr_voucher_amount', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher_amount'), $boxFormatNumber) : '';
		in_array('vr_voucher_used_value', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher_used_value'), $boxFormatNumber) : '';
		in_array('vr_voucher_remaining_value', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher_remaining_value'), $boxFormatNumber) : '';	
		in_array('vr_voucher_orders', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_orders'), $boxFormatNumber) : '';
		in_array('vr_voucher_total', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total'), $boxFormatNumber) : '';	
		
		} else {
			
		in_array('mv_orders', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_orders'), $boxFormatNumber) : '';
		in_array('mv_customers', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_customers'), $boxFormatNumber) : '';
		in_array('mv_products', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_products'), $boxFormatNumber) : '';
		in_array('mv_sub_total', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_sub_total'), $boxFormatNumber) : '';
		in_array('mv_handling', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_handling'), $boxFormatNumber) : '';
		in_array('mv_loworder', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_loworder'), $boxFormatNumber) : '';
		in_array('mv_shipping', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_shipping'), $boxFormatNumber) : '';
		in_array('mv_reward', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_reward'), $boxFormatNumber) : '';
		in_array('mv_earned_points', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_earned_reward_points'), $boxFormatNumber) : '';
		in_array('mv_used_points', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_used_reward_points'), $boxFormatNumber) : '';
		in_array('mv_coupon', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_coupon'), $boxFormatNumber) : '';
		in_array('mv_tax', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_taxes'), $boxFormatNumber) : '';
		in_array('mv_credit', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_credit'), $boxFormatNumber) : '';
		in_array('mv_voucher', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher'), $boxFormatNumber) : '';
		in_array('mv_discount', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_discount'), $boxFormatNumber) : '';
		in_array('mv_total', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total'), $boxFormatNumber) : '';
		in_array('mv_aov', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_aov'), $boxFormatNumber) : '';
		in_array('mv_refunds', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_refunds'), $boxFormatNumber) : '';
		in_array('mv_total_sales', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_sales'), $boxFormatNumber) : '';
		in_array('mv_product_costs', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_cost'), $boxFormatNumber) : '';
		in_array('mv_commission', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_commission'), $boxFormatNumber) : '';
		in_array('mv_payment_costs', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_payment_cost'), $boxFormatNumber) : '';
		in_array('mv_shipping_costs', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_shipping_cost'), $boxFormatNumber) : '';
		in_array('mv_extra_costs', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_extra_cost'), $boxFormatNumber) : '';
		in_array('mv_return_costs', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_return_cost'), $boxFormatNumber) : '';
		in_array('mv_shipping_balance', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_shipping_balance'), $boxFormatNumber) : '';
		in_array('mv_total_costs', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_costs'), $boxFormatNumber) : '';
		in_array('mv_total_profit', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_profit'), $boxFormatNumber) : '';
		in_array('mv_total_profit_prc', $advsop_settings_mv_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_total_profit_prc'), $boxFormatNumber) : '';
		}
					
		in_array('ol_order_order_id', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_order_id'), $boxFormatText) : '';
		in_array('ol_order_date_added', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_date_added'), $boxFormatText) : '';
		in_array('ol_order_inv_no', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_inv_no'), $boxFormatText) : '';
		in_array('ol_order_customer', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_customer'), $boxFormatText) : '';
		in_array('ol_order_email', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_email'), $boxFormatText) : '';
		in_array('ol_order_customer_group', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_customer_group'), $boxFormatText) : '';
		in_array('ol_order_shipping_method', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_shipping_method'), $boxFormatText) : '';
		in_array('ol_order_payment_method', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_payment_method'), $boxFormatText) : '';
		in_array('ol_order_status', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_status'), $boxFormatText) : '';
		in_array('ol_order_store', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_store'), $boxFormatText) : '';	
		in_array('ol_order_currency', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_currency'), $boxFormatText) : '';	
		in_array('ol_order_quantity', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_quantity'), $boxFormatNumber) : '';
		in_array('ol_order_sub_total', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_sub_total'), $boxFormatNumber) : '';
		in_array('ol_order_shipping', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_shipping'), $boxFormatNumber) : '';
		in_array('ol_order_tax', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_tax'), $boxFormatNumber) : '';
		if ($filter_report == 'coupon') {
		in_array('cr_coupon_amount', $advsop_settings_cr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_coupon'), $boxFormatNumber) : '';
		} elseif ($filter_report == 'voucher') {
		in_array('vr_voucher_used_value', $advsop_settings_vr_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_voucher'), $boxFormatNumber) : '';
		}		
		in_array('ol_order_value', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_value'), $boxFormatNumber) : '';
		in_array('ol_order_sales', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_sales'), $boxFormatNumber) : '';
		in_array('ol_order_costs', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_costs'), $boxFormatNumber) : '';
		in_array('ol_order_profit', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_profit'), $boxFormatNumber) : '';
		in_array('ol_order_profit_prc', $advsop_settings_ol_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_order_margin'), $boxFormatNumber) : '';		
		
		in_array('cl_customer_cust_id', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_customer_cust_id'), $boxFormatText) : '';
		in_array('cl_billing_name', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_billing_name')), $boxFormatText) : '';
		in_array('cl_billing_company', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_billing_company')), $boxFormatText) : '';
		in_array('cl_billing_address_1', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_billing_address_1')), $boxFormatText) : '';
		in_array('cl_billing_address_2', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_billing_address_2')), $boxFormatText) : '';
		in_array('cl_billing_city', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_billing_city')), $boxFormatText) : '';
		in_array('cl_billing_zone', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_billing_zone')), $boxFormatText) : '';
		in_array('cl_billing_postcode', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_billing_postcode')), $boxFormatText) : '';
		in_array('cl_billing_country', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_billing_country')), $boxFormatText) : '';
		in_array('cl_customer_telephone', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_customer_telephone'), $boxFormatText) : '';
		in_array('cl_shipping_name', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_shipping_name')), $boxFormatText) : '';
		in_array('cl_shipping_company', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_shipping_company')), $boxFormatText) : '';
		in_array('cl_shipping_address_1', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_shipping_address_1')), $boxFormatText) : '';
		in_array('cl_shipping_address_2', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_shipping_address_2')), $boxFormatText) : '';
		in_array('cl_shipping_city', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_shipping_city')), $boxFormatText) : '';
		in_array('cl_shipping_zone', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_shipping_zone')), $boxFormatText) : '';
		in_array('cl_shipping_postcode', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_shipping_postcode')), $boxFormatText) : '';
		in_array('cl_shipping_country', $advsop_settings_cl_columns) ? $worksheet->writeString($i, $j++, strip_tags($this->language->get('column_shipping_country')), $boxFormatText) : '';

		in_array('pl_prod_order_id', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_order_id'), $boxFormatText) : '';
		in_array('pl_prod_date_added', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_date_added'), $boxFormatText) : '';
		in_array('pl_prod_id', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_id'), $boxFormatText) : '';
		in_array('pl_prod_sku', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_sku'), $boxFormatText) : '';
		in_array('pl_prod_model', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_model'), $boxFormatText) : '';	
		in_array('pl_prod_name', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_name'), $boxFormatText) : '';
		in_array('pl_prod_option', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_option'), $boxFormatText) : '';
		in_array('pl_prod_attributes', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_attributes'), $boxFormatText) : '';		
		in_array('pl_prod_category', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_category'), $boxFormatText) : '';
		in_array('pl_prod_manu', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_manu'), $boxFormatText) : '';			 
		in_array('pl_prod_supplier', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_supplier'), $boxFormatText) : '';	
		in_array('pl_prod_currency', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_currency'), $boxFormatText) : '';
		in_array('pl_prod_price', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_price'), $boxFormatNumber) : '';		
		in_array('pl_prod_quantity', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_quantity'), $boxFormatNumber) : '';
		in_array('pl_prod_total_excl_vat', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_total_excl_vat'), $boxFormatNumber) : '';
		in_array('pl_prod_tax', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_tax'), $boxFormatNumber) : '';
		in_array('pl_prod_total_incl_vat', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_total_incl_vat'), $boxFormatNumber) : '';
		in_array('pl_prod_sales', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_sales'), $boxFormatNumber) : '';
		in_array('pl_prod_cost', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_cost'), $boxFormatNumber) : '';
		in_array('pl_prod_profit', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_profit'), $boxFormatNumber) : '';
		in_array('pl_prod_profit_margin', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_profit_margin'), $boxFormatNumber) : '';
		in_array('pl_prod_profit_markup', $advsop_settings_pl_columns) ? $worksheet->writeString($i, $j++, $this->language->get('column_prod_profit_markup'), $boxFormatNumber) : '';
		
		// The actual orders data
		$i += 1;
		$j = 0;
		
			foreach ($results as $result) {
			$excelRow = $i+1;
				if ($filter_report == 'sales_summary' or $filter_report == 'abandoned_orders') {
				if ($filter_group == 'year') {	
				$worksheet->write($i, $j++, $result['year'], $textFormat);
				} elseif ($filter_group == 'quarter') {
				$worksheet->write($i, $j++, $result['year'], $textFormat);
				$worksheet->write($i, $j++, $result['quarter'], $textFormat);		
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
				} elseif ($filter_report == 'day_of_week') {
				$worksheet->write($i, $j++, $result['day_of_week'], $textFormat);
				} elseif ($filter_report == 'hour') {
				$worksheet->write($i, $j++, $result['hour'], $textFormat);
				} elseif ($filter_report == 'store') {
				$worksheet->write($i, $j++, $result['store_name'], $textFormat);
				} elseif ($filter_report == 'currency') {
				$worksheet->write($i, $j++, $result['currency_code'], $textFormat);
				} elseif ($filter_report == 'customer_group') {
				$worksheet->write($i, $j++, $result['customer_group'], $textFormat);
				} elseif ($filter_report == 'country') {
				$worksheet->write($i, $j++, $result['payment_country'], $textFormat);
				} elseif ($filter_report == 'postcode') {
				$worksheet->write($i, $j++, $result['payment_postcode'], $textFormat);
				} elseif ($filter_report == 'region_state') {
				$worksheet->write($i, $j++, $result['payment_zone'], $textFormat);
				} elseif ($filter_report == 'city') {
				$worksheet->write($i, $j++, $result['payment_city'], $textFormat);
				} elseif ($filter_report == 'payment_method') {
				$worksheet->write($i, $j++, $result['payment_method'], $textFormat);
				} elseif ($filter_report == 'shipping_method') {
				$worksheet->write($i, $j++, $result['shipping_method'], $textFormat);
				} elseif ($filter_report == 'coupon') {
				$worksheet->write($i, $j++, $result['date_start'], $textFormat);
				$worksheet->write($i, $j++, $result['date_end'], $textFormat);
				} elseif ($filter_report == 'voucher') {
				$worksheet->write($i, $j++, $result['date_start'], $textFormat);
				$worksheet->write($i, $j++, $result['date_end'], $textFormat);
				}
		
				if ($filter_report == 'coupon') {
				
				in_array('cr_coupon_name', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, $result['coupon_name'], $textFormat) : '';
				in_array('cr_coupon_code', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, $result['coupon_code'], $textFormat) : '';
				in_array('cr_coupon_discount', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, $result['coupon_discount'], $priceFormat) : '';
				in_array('cr_coupon_type', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, $result['coupon_type'], $textFormat) : '';
				in_array('cr_coupon_status', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, $result['coupon_status'], $textFormat) : '';
				in_array('cr_coupon_date_added', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, $result['coupon_date_added'], $textFormat) : '';
				in_array('cr_coupon_last_used', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, $result['coupon_last_used'], $textFormat) : '';
				in_array('cr_coupon_amount', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, $result['coupon_amount_raw'] != NULL ? $result['coupon_amount_raw'] : '0.00', $priceFormat) : '';
				in_array('cr_coupon_orders', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, $result['coupon_orders']) : '';
				in_array('cr_coupon_total', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, $result['coupon_total_raw'] != NULL ? $result['coupon_total_raw'] : '0.00', $priceFormat) : '';
				
				} elseif ($filter_report == 'voucher') {
				
				in_array('vr_voucher_code', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_code'], $textFormat) : '';
				in_array('vr_voucher_from_name', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_from_name'], $textFormat) : '';
				in_array('vr_voucher_from_email', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_from_email'], $textFormat) : '';
				in_array('vr_voucher_to_name', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_to_name'], $textFormat) : '';
				in_array('vr_voucher_to_email', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_to_email'], $textFormat) : '';
				in_array('vr_voucher_theme', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_theme'], $textFormat) : '';
				in_array('vr_voucher_status', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_status'], $textFormat) : '';
				in_array('vr_voucher_date_added', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_date_added'], $textFormat) : '';
				in_array('vr_voucher_amount', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_amount_raw'] != NULL ? $result['voucher_amount_raw'] : '0.00', $priceFormat) : '';
				in_array('vr_voucher_used_value', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_used_value_raw'] != NULL ? $result['voucher_used_value_raw'] : '0.00', $priceFormat) : '';
				in_array('vr_voucher_remaining_value', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_remaining_value_raw'] != NULL ? $result['voucher_remaining_value_raw'] : '0.00', $priceFormat) : '';
				in_array('vr_voucher_orders', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_orders']) : '';
				in_array('vr_voucher_total', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, $result['voucher_total_raw'] != NULL ? $result['voucher_total_raw'] : '0.00', $priceFormat) : '';
				
				} else {
					
				in_array('mv_orders', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['orders'], $filter_report != 'abandoned_orders' ? $numberFormat : $abnumberFormat) : '';
				in_array('mv_customers', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['customers'], $filter_report != 'abandoned_orders' ? $numberFormat : $abnumberFormat) : '';
				in_array('mv_products', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['products'], $filter_report != 'abandoned_orders' ? $numberFormat : $abnumberFormat) : '';
				in_array('mv_sub_total', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['sub_total_raw'], $saleFormat) : '';
				in_array('mv_handling', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['handling_raw'] != NULL ? $result['handling_raw'] : '0.00', $saleFormat) : '';
				in_array('mv_loworder', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['low_order_fee_raw'] != NULL ? $result['low_order_fee_raw'] : '0.00', $saleFormat) : '';
				if ($this->config->get('advsop' . $user_id . '_formula_1')) {	
				in_array('mv_shipping', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['shipping_raw'] != NULL ? $result['shipping_raw'] : '0.00', $saleFormat) : '';
				} else {
				in_array('mv_shipping', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['shipping_raw'] != NULL ? $result['shipping_raw'] : '0.00', $priceFormat) : '';
				}
				in_array('mv_reward', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['reward_raw'] != NULL ? $result['reward_raw'] : '0.00', $saleFormat) : '';
				in_array('mv_earned_points', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['earned_reward_points'], $filter_report != 'abandoned_orders' ? $numberFormat : $abnumberFormat) : '';
				in_array('mv_used_points', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['used_reward_points'], $filter_report != 'abandoned_orders' ? $numberFormat : $abnumberFormat) : '';
				in_array('mv_coupon', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['coupon_raw'] != NULL ? $result['coupon_raw'] : '0.00', $saleFormat) : '';
				in_array('mv_tax', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['taxes_raw'] != NULL ? $result['taxes_raw'] : '0.00', $priceFormat) : '';
				in_array('mv_credit', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['credit_raw'] != NULL ? $result['credit_raw'] : '0.00', $saleFormat) : '';
				in_array('mv_voucher', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['voucher_raw'] != NULL ? $result['voucher_raw'] : '0.00', $saleFormat) : '';
				in_array('mv_discount', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['discount_raw'], $priceFormat) : '';
				in_array('mv_total', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['total_raw'], $priceFormat) : '';
				in_array('mv_aov', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['aov_raw'], $priceFormat) : '';
				if ($this->config->get('advsop' . $user_id . '_formula_6')) {	
				in_array('mv_refunds', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['refunds_raw'] != NULL ? -$result['refunds_raw'] : '0.00', $saleFormat) : '';
				} else {
				in_array('mv_refunds', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['refunds_raw'] != NULL ? $result['refunds_raw'] : '0.00', $priceFormat) : '';
				}				
				in_array('mv_total_sales', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['total_sales_raw'], $salesColumnFormat) : '';
				in_array('mv_product_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['prod_costs_raw'], $costFormat) : '';
				in_array('mv_commission', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['commission_raw'], $costFormat) : '';
				if ($this->config->get('advsop' . $user_id . '_formula_3')) {	
				in_array('mv_payment_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['pay_costs_raw'], $costFormat) : '';
				} else {
				in_array('mv_payment_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['pay_costs_raw'], $priceFormat) : '';
				}
				if ($this->config->get('advsop' . $user_id . '_formula_2')) {	
				in_array('mv_shipping_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['ship_costs_raw'], $costFormat) : '';
				} else {
				in_array('mv_shipping_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['ship_costs_raw'], $priceFormat) : '';
				}	
				if ($this->config->get('advsop' . $user_id . '_formula_4')) {	
				in_array('mv_extra_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['extra_costs_raw'], $costFormat) : '';
				} else {
				in_array('mv_extra_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['extra_costs_raw'], $priceFormat) : '';
				}
				if ($this->config->get('advsop' . $user_id . '_formula_5')) {	
				in_array('mv_return_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['return_costs_raw'] != NULL ? -$result['return_costs_raw'] : '0.00', $costFormat) : '';
				} else {
				in_array('mv_return_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['return_costs_raw'] != NULL ? -$result['return_costs_raw'] : '0.00', $priceFormat) : '';
				}				
				in_array('mv_shipping_balance', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['ship_balance_raw'], $priceFormat) : '';
				in_array('mv_total_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, -$result['total_costs_raw'], $costsColumnFormat) : '';
				if ($result['total_profit_raw'] >= 0) {
				in_array('mv_total_profit', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['total_profit_raw'], $profitColumnFormat) : '';
				} else {
				in_array('mv_total_profit', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, $result['total_profit_raw'], $noprofitColumnFormat) : '';
				}
				if ($result['total_sales_raw'] > 0) {
				if ($result['total_profit_raw'] >= 0) {
				in_array('mv_total_profit_prc', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['total_profit_prc'])/100, $percentColumnFormat) : '';
				} else {
				in_array('mv_total_profit_prc', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, str_replace('%','',$result['total_profit_prc'])/100, $nopercentColumnFormat) : '';
				}					
				} else {
				in_array('mv_total_profit_prc', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '0', $percentColumnFormat) : '';
				}
				}	
								
				$d = $i;
				if (in_array('ol_order_order_id', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_ord_id']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('ol_order_date_added', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_ord_date']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('ol_order_inv_no', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_inv_no']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('ol_order_customer', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_name']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('ol_order_email', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_email']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('ol_order_customer_group', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_group']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('ol_order_shipping_method', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_shipping_method']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('ol_order_payment_method', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_payment_method']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('ol_order_status', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_status']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('ol_order_store', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_store']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, html_entity_decode($value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('ol_order_currency', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_currency']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}		
				if (in_array('ol_order_quantity', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_products']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $filter_report != 'abandoned_orders' ? '' : $abnumberFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('ol_order_sub_total', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_sub_total']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $saleFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('ol_order_shipping', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_shipping']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $priceFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('ol_order_tax', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_tax']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $priceFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if ($filter_report == 'coupon') {
				if (in_array('cr_coupon_amount', $advsop_settings_cr_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_coupon']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $saleFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				} elseif ($filter_report == 'voucher') {
				if (in_array('vr_voucher_used_value', $advsop_settings_vr_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_voucher']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $saleFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				}					
				if (in_array('ol_order_value', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_value']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $priceFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('ol_order_sales', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_sales']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $salesColumnFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('ol_order_costs', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_costs']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, -str_replace('-','',$value), $costsColumnFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}				
				if (in_array('ol_order_profit', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_profit']);	
				foreach ($details as $key => $value) {
				$c = $j;	
				if ($value >= 0) {
				$worksheet->write($i, $j++, $value, $profitColumnFormat);
				} else {
				$worksheet->write($i, $j++, $value, $noprofitColumnFormat);
				}				
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('ol_order_profit_prc', $advsop_settings_ol_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['order_profit_prc']);	
				foreach ($details as $key => $value) {
				$c = $j;	
				if ($value >= 0) {
				$worksheet->write($i, $j++, str_replace('%','',$value)/100, $percentColumnFormat);
				} else {
				$worksheet->write($i, $j++, str_replace('%','',$value)/100, $nopercentColumnFormat);
				}				
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				
				if (in_array('cl_customer_cust_id', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['customer_cust_id']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('cl_billing_name', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['billing_name']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('cl_billing_company', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['billing_company']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('cl_billing_address_1', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['billing_address_1']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('cl_billing_address_2', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['billing_address_2']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('cl_billing_city', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['billing_city']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('cl_billing_zone', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['billing_zone']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('cl_billing_postcode', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['billing_postcode']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('cl_billing_country', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['billing_country']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}		
				if (in_array('cl_customer_telephone', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['customer_telephone']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('cl_shipping_name', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['shipping_name']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('cl_shipping_company', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['shipping_company']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('cl_shipping_address_1', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['shipping_address_1']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('cl_shipping_address_2', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['shipping_address_2']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('cl_shipping_city', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['shipping_city']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('cl_shipping_zone', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['shipping_zone']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('cl_shipping_postcode', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['shipping_postcode']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('cl_shipping_country', $advsop_settings_cl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['shipping_country']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}					

				$j = 0;	
				if ($filter_report == 'sales_summary' or $filter_report == 'abandoned_orders') {
				if ($filter_group == 'year') {	
				$worksheet->write($i, $j++, '');
				} elseif ($filter_group == 'quarter') {
				$worksheet->write($i, $j++, '');
				$worksheet->write($i, $j++, '');
				} elseif ($filter_group == 'month') {
				$worksheet->write($i, $j++, '');
				$worksheet->write($i, $j++, '');
				} elseif ($filter_group == 'day') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_group == 'order') {
				$worksheet->write($i, $j++, '');
				$worksheet->write($i, $j++, '');
				} else {
				$worksheet->write($i, $j++, '');
				$worksheet->write($i, $j++, '');
				}
				} elseif ($filter_report == 'day_of_week') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'hour') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'store') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'currency') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'customer_group') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'country') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'postcode') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'region_state') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'city') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'payment_method') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'shipping_method') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'coupon') {
				$worksheet->write($i, $j++, '');
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'voucher') {
				$worksheet->write($i, $j++, '');
				$worksheet->write($i, $j++, '');
				}
		
				if ($filter_report == 'coupon') {
			
				in_array('cr_coupon_name', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_code', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_discount', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_type', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_status', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_date_added', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_last_used', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_amount', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_orders', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_total', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
		
				} elseif ($filter_report == 'voucher') {
			
				in_array('vr_voucher_code', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_from_name', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_from_email', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_to_name', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_to_email', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_theme', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_status', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_date_added', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_amount', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_used_value', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_remaining_value', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_orders', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_total', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
		
				} else {
			
				in_array('mv_orders', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_customers', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_products', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_sub_total', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_handling', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_loworder', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_shipping', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_reward', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_earned_points', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_used_points', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_coupon', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_tax', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_credit', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_voucher', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_discount', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_total', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_aov', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_refunds', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_total_sales', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_product_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_commission', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_payment_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_shipping_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_extra_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_return_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_shipping_balance', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_total_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_total_profit', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_total_profit_prc', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				}
					
				in_array('ol_order_order_id', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_date_added', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_inv_no', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_customer', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_email', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_customer_group', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_shipping_method', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_payment_method', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_status', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_store', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_currency', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_quantity', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_sub_total', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_shipping', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_tax', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				if ($filter_report == 'coupon') {
				in_array('cr_coupon_amount', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				} elseif ($filter_report == 'voucher') {
				in_array('vr_voucher_used_value', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				}
				in_array('ol_order_value', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_sales', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_costs', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_profit', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_profit_prc', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';	
		
				in_array('cl_customer_cust_id', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_name', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_company', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_address_1', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_address_2', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_city', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_zone', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_postcode', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_country', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_customer_telephone', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_name', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_company', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_address_1', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_address_2', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_city', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_zone', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_postcode', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_country', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';

				if (in_array('pl_prod_order_id', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_ord_id']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('pl_prod_date_added', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_ord_date']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('pl_prod_id', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_pid']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('pl_prod_sku', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_sku']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('pl_prod_model', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_model']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('pl_prod_name', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_name']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, html_entity_decode($value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('pl_prod_option', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_option']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, str_replace('&nbsp;','',$value), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('pl_prod_attributes', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_attributes']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, html_entity_decode(str_replace('&nbsp;','',$value)), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('pl_prod_category', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_category']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, html_entity_decode(str_replace('&nbsp;','',$value)), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('pl_prod_manu', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_manu']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, html_entity_decode(str_replace('&nbsp;','',$value)), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('pl_prod_supplier', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_supplier']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, html_entity_decode(str_replace('&nbsp;','',$value)), $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('pl_prod_currency', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_currency']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $textFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}		
				if (in_array('pl_prod_price', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_price']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $priceFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('pl_prod_quantity', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_quantity']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('pl_prod_total_excl_vat', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_total_excl_vat']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $saleFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('pl_prod_tax', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_tax']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $priceFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('pl_prod_total_incl_vat', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_total_incl_vat']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $priceFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}
				if (in_array('pl_prod_sales', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_sales']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, $value, $salesColumnFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('pl_prod_cost', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_cost']);	
				foreach ($details as $key => $value) {
				$c = $j;					
				$worksheet->write($i, $j++, -str_replace('-','',$value), $costsColumnFormat);
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('pl_prod_profit', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_profit']);	
				foreach ($details as $key => $value) {
				$c = $j;
				if ($value >= 0) {
				$worksheet->write($i, $j++, $value, $profitColumnFormat);
				} else {
				$worksheet->write($i, $j++, $value, $noprofitColumnFormat);
				}				
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	
				if (in_array('pl_prod_profit_margin', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_profit_margin']);	
				foreach ($details as $key => $value) {
				$c = $j;
				if ($value >= 0) {
				$worksheet->write($i, $j++, str_replace('%','',$value)/100, $percentColumnFormat);
				} else {
				$worksheet->write($i, $j++, str_replace('%','',$value)/100, $nopercentColumnFormat);
				}				
				$j = $c;
				$i += 1;		
				}
				$j++;
				}				
				if (in_array('pl_prod_profit_markup', $advsop_settings_pl_columns)) {
				$i = $d;		
				$details = explode('<br>', $result['product_profit_markup']);	
				foreach ($details as $key => $value) {
				$c = $j;
				if ($value >= 0) {
				$worksheet->write($i, $j++, str_replace('%','',$value)/100, $percentColumnFormat);
				} else {
				$worksheet->write($i, $j++, str_replace('%','',$value)/100, $nopercentColumnFormat);
				}				
				$j = $c;
				$i += 1;		
				}
				$j++;
				}	

				$j = 0;	
				if ($filter_report == 'sales_summary' or $filter_report == 'abandoned_orders') {
				if ($filter_group == 'year') {	
				$worksheet->write($i, $j++, '');
				} elseif ($filter_group == 'quarter') {
				$worksheet->write($i, $j++, '');
				$worksheet->write($i, $j++, '');
				} elseif ($filter_group == 'month') {
				$worksheet->write($i, $j++, '');
				$worksheet->write($i, $j++, '');
				} elseif ($filter_group == 'day') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_group == 'order') {
				$worksheet->write($i, $j++, '');
				$worksheet->write($i, $j++, '');
				} else {
				$worksheet->write($i, $j++, '');
				$worksheet->write($i, $j++, '');
				}
				} elseif ($filter_report == 'day_of_week') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'hour') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'store') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'currency') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'customer_group') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'country') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'postcode') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'region_state') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'city') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'payment_method') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'shipping_method') {
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'coupon') {
				$worksheet->write($i, $j++, '');
				$worksheet->write($i, $j++, '');
				} elseif ($filter_report == 'voucher') {
				$worksheet->write($i, $j++, '');
				$worksheet->write($i, $j++, '');
				}
		
				if ($filter_report == 'coupon') {
			
				in_array('cr_coupon_name', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_code', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_discount', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_type', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_status', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_date_added', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_last_used', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_amount', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_orders', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cr_coupon_total', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
		
				} elseif ($filter_report == 'voucher') {
			
				in_array('vr_voucher_code', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_from_name', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_from_email', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_to_name', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_to_email', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_theme', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_status', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_date_added', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_amount', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_used_value', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_remaining_value', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_orders', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('vr_voucher_total', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
		
				} else {
			
				in_array('mv_orders', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_customers', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_products', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_sub_total', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_handling', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_loworder', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_shipping', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_reward', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_earned_points', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_used_points', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_coupon', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_tax', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_credit', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_voucher', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_discount', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_total', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_aov', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_refunds', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_total_sales', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_product_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_commission', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_payment_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_shipping_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_extra_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_return_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_shipping_balance', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_total_costs', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_total_profit', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('mv_total_profit_prc', $advsop_settings_mv_columns) ? $worksheet->write($i, $j++, '') : '';
				}
					
				in_array('ol_order_order_id', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_date_added', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_inv_no', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_customer', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_email', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_customer_group', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_shipping_method', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_payment_method', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_status', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_store', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_currency', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_quantity', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_sub_total', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_shipping', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_tax', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				if ($filter_report == 'coupon') {
				in_array('cr_coupon_amount', $advsop_settings_cr_columns) ? $worksheet->write($i, $j++, '') : '';
				} elseif ($filter_report == 'voucher') {
				in_array('vr_voucher_used_value', $advsop_settings_vr_columns) ? $worksheet->write($i, $j++, '') : '';
				}				
				in_array('ol_order_value', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_sales', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_costs', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_profit', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('ol_order_profit_prc', $advsop_settings_ol_columns) ? $worksheet->write($i, $j++, '') : '';	
		
				in_array('cl_customer_cust_id', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_name', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_company', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_address_1', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_address_2', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_city', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_zone', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_postcode', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_billing_country', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_customer_telephone', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_name', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_company', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_address_1', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_address_2', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_city', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_zone', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_postcode', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				in_array('cl_shipping_country', $advsop_settings_cl_columns) ? $worksheet->write($i, $j++, '') : '';
				
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