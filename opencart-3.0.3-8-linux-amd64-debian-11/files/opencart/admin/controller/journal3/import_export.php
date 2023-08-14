<?php

use Journal3\Utils\Str;

class ControllerJournal3ImportExport extends Controller {

	private static $TABLES = array(
		'variable'   => array('journal3_variable'),
		'setting'    => array('journal3_setting'),
		'skin'       => array(
			'journal3_skin',
			'journal3_skin_setting',
		),
		'style'      => array('journal3_style'),
		'module'     => array('journal3_module'),
		'layout'     => array('journal3_layout'),
		'blog'       => array(
			'journal3_blog_category',
			'journal3_blog_category_description',
			'journal3_blog_category_to_layout',
			'journal3_blog_category_to_store',
			'journal3_blog_post',
			'journal3_blog_post_description',
			'journal3_blog_post_to_category',
			'journal3_blog_post_to_layout',
			'journal3_blog_post_to_store',
			'journal3_blog_post_to_product',
			'journal3_blog_comments',
		),
		'newsletter' => array('journal3_newsletter'),
		'message'    => array('journal3_message'),
		'catalog'    => array(
			'attribute',
			'attribute_description',
			'attribute_group',
			'attribute_group_description',

			'category',
			'category_description',
			'category_filter',
			'category_path',
			'category_to_layout',
			'category_to_store',

			'filter',
			'filter_description',
			'filter_group',
			'filter_group_description',

			'information',
			'information_description',
			'information_to_layout',
			'information_to_store',

			'layout',
			'layout_route',

			'manufacturer',
			'manufacturer_to_store',

			'option',
			'option_description',
			'option_value',
			'option_value_description',

			'product',
			'product_attribute',
			'product_description',
			'product_discount',
			'product_filter',
			'product_image',
			'product_option',
			'product_option_value',
			'product_recurring',
			'product_related',
			'product_reward',
			'product_special',
			'product_to_category',
			'product_to_download',
			'product_to_layout',
			'product_to_store',

			'stock_status',

			'seo_url',

			'url_alias',
		),
	);

	public function __construct($registry) {
		parent::__construct($registry);

		$this->load->model('journal3/import_export');
		$this->load->language('error/permission');

		if (JOURNAL3_ENV === 'development') {
			static::$TABLES['catalog'] = array_merge(static::$TABLES['catalog'], [
				"order",
				"order_history",
				"order_option",
				"order_product",
				"order_recurring",
				"order_recurring_transaction",
				"order_shipment",
				"order_status",
				"order_total",
				"order_voucher",
				"journal3_product_sales",
			]);
		}
	}

	public function all() {
		try {
			$this->journal3_response->json('success', $this->model_journal3_import_export->all());
		} catch (Exception $e) {
			$this->journal3_response->json('error', $e->getMessage());
		}
	}

	public function download() {
		try {
			$filename = $this->journal3_request->get('filename');

			$file = DIR_SYSTEM . 'library/journal3/data/import_export/' . $filename;

			if (!headers_sent()) {
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . $filename . '"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));

				if (ob_get_level()) {
					ob_end_clean();
				}

				readfile($file, 'rb');

				exit;
			} else {
				throw new Exception('Error: Headers already sent out!');
			}
		} catch (Exception $e) {
			$this->journal3_response->json('error', $e->getMessage());
		}
	}

	public function copy() {
		try {
			if (!$this->user->hasPermission('modify', 'journal3/import_export')) {
				throw new Exception($this->language->get('text_permission'));
			}

			$filename = $this->journal3_request->get('id');

			$file = DIR_SYSTEM . 'library/journal3/data/import_export/' . $filename;

			$content = file_get_contents($file);

			$this->journal3_response->json('success', $this->model_journal3_import_export->import($content));

			$this->journal3_cache->delete();
		} catch (Exception $e) {
			$this->journal3_response->json('error', $e->getMessage());
		}
	}

	public function remove() {
		try {
			if (!$this->user->hasPermission('modify', 'journal3/import_export')) {
				throw new Exception($this->language->get('text_permission'));
			}

			$filename = $this->journal3_request->get('id');

			$file = DIR_SYSTEM . 'library/journal3/data/import_export/' . $filename;

			unlink($file);

			$this->journal3_response->json('success');
		} catch (Exception $e) {
			$this->journal3_response->json('error', $e->getMessage());
		}
	}

	public function export() {
		try {
			$tables = array();

			foreach (static::$TABLES as $key => $value) {
				if ($this->journal3_request->get($key, '') === 'true') {
					$tables = array_merge($tables, $value);
				}
			}

			$filename = $this->journal3_request->get('filename', '');

			if ($filename) {
				if (!$this->user->hasPermission('modify', 'journal3/import_export')) {
					throw new Exception($this->language->get('text_permission'));
				}

				$file = DIR_SYSTEM . 'library/journal3/data/import_export/' . $filename . '.sql';

				if (is_file($file) && !$this->journal3_request->get('overwrite', '')) {
					$this->journal3_response->json('success', array(
						'overwrite' => 'Filename already exists! Overwrite?',
					));
				} else {
					$sql = $this->model_journal3_import_export->export($tables);

					// safe copy old file
					if (is_file($file)) {
						rename($file, $file . '.old.' . date('Y-m-d_H-i-s', time()));
					}

					file_put_contents($file, $sql);

					$this->journal3_response->json('success');
				}
			} else {
				$filename = date('Y-m-d_H-i-s', time());

				$sql = $this->model_journal3_import_export->export($tables);

				if ($this->journal3_opencart->is_oc2) {
					$file = DIR_CACHE . $filename . '.sql';
				} else {
					$file = DIR_STORAGE . 'cache/' . $filename . '.sql';
				}

				file_put_contents($file, $sql);

				if (!headers_sent()) {
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="' . $filename . '.sql"');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));

					if (ob_get_level()) {
						ob_end_clean();
					}

					readfile($file, 'rb');

					unlink($file);

					exit;
				} else {
					throw new Exception('Error: Headers already sent out!');
				}
			}
		} catch (Exception $e) {
			$this->journal3_response->json('error', $e->getMessage());
		}
	}

	public function import() {
		try {
			if (!$this->user->hasPermission('modify', 'journal3/import_export')) {
				throw new Exception($this->language->get('text_permission'));
			}

			$file = $this->journal3_request->file('file');

			if ($file['error'] != UPLOAD_ERR_OK) {
				throw new Exception($this->language->get('error_upload_' . $file['error']));
			}

			$save = $this->journal3_request->get('save', 'true');

			if ($save === 'true') {
				$f = DIR_SYSTEM . 'library/journal3/data/import_export/' . $file['name'];

				move_uploaded_file($file['tmp_name'], $f);

				$this->journal3_response->json('success');
			} else {
				if ($file['type'] === 'application/zip') {
					$zip = new \ZipArchive();

					if ($zip->open($file['tmp_name'])) {
						for ($i = 0; $i < $zip->numFiles; $i++) {
							$file = $zip->getNameIndex($i);

							if (fnmatch('*.sql', $file)) {
								$content = $zip->getFromIndex($i);

								$this->journal3_response->json('success', $this->model_journal3_import_export->import($content));

								return;
							}
						}
					}

					throw new Exception('No .sql files were found in archive.');
				} else {
					$content = file_get_contents($file['tmp_name']);

					$this->journal3_response->json('success', $this->model_journal3_import_export->import($content));
				}
			}

			$this->journal3_cache->delete();
		} catch (Exception $e) {
			$this->journal3_response->json('error', $e->getMessage());
		}
	}

	public function check() {
		try {
			$params = array();

			foreach (static::$TABLES as $key => $value) {
				if ($this->journal3_request->get($key, '') === 'true') {
					$params[$key] = 'true';
				}
			}

			$this->journal3_response->json('success', $this->journal3_url->admin_link('journal3/import_export' . JOURNAL3_ROUTE_SEPARATOR . 'export', '&' . http_build_query($params)));
		} catch (Exception $e) {
			$this->journal3_response->json('error', $e->getMessage());
		}
	}

	public function download_item() {
		try {
			if (!$this->user->hasPermission('modify', 'journal3/import_export')) {
				throw new Exception($this->language->get('text_permission'));
			}

			$name = $this->journal3_request->get('name');
			$type = $this->journal3_request->get('type', '');
			$id = $this->journal3_request->get('id');

			if (Str::startsWith($name, 'module')) {
				$sql = $this->model_journal3_import_export->exportTable('journal3_module', ' WHERE `module_id` = ' . (int)$id);
				$sql = str_replace("VALUES ('" . $id . "'", "VALUES (null", $sql);
			} else if (in_array($name, array('style', 'variable'))) {
				$sql = $this->model_journal3_import_export->exportTable('journal3_' . $name, ' WHERE `' . $name . '_name` = \'' . $this->db->escape($id) . '\' AND `' . $name . '_type` = \'' . $this->db->escape($type) . '\'');
			} else if ($name == 'layout') {
				$sql1 = $this->model_journal3_import_export->exportTable('layout', ' WHERE `layout_id` = \'' . $this->db->escape($id) . '\'');
				$sql1 = str_replace("VALUES ('" . $id . "'", "VALUES (null", $sql1);

				$sql = $sql1;

				$sql .= "UPDATE `{$this->journal3_db->prefix('layout')}` SET `name` = CONCAT(`name`, ' - COPY') WHERE `layout_id` = LAST_INSERT_ID();" . "\n\n";
				$sql .= "DELETE FROM `{$this->journal3_db->prefix('journal3_layout')}` WHERE `layout_id` = LAST_INSERT_ID();" . "\n\n";

				$sql2 = $this->model_journal3_import_export->exportTable('journal3_layout', ' WHERE `layout_id` = \'' . $this->db->escape($id) . '\'');
				$sql2 = str_replace("VALUES ('" . $id . "'", "VALUES (LAST_INSERT_ID()", $sql2);

				$sql .= $sql2;
			} else {
				throw new Exception('Error: Not implemented yet!');
			}

			$filename = date('Y-m-d_H-i-s', time());

			if ($this->journal3_opencart->is_oc2) {
				$file = DIR_CACHE . $filename . '.sql';
			} else {
				$file = DIR_STORAGE . 'cache/' . $filename . '.sql';
			}

			file_put_contents($file, $sql);

			if (!headers_sent()) {
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . $filename . '.sql"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));

				if (ob_get_level()) {
					ob_end_clean();
				}

				readfile($file, 'rb');

				unlink($file);

				exit;
			} else {
				throw new Exception('Error: Headers already sent out!');
			}
		} catch (Exception $e) {
			$this->journal3_response->json('error', $e->getMessage());
		}
	}

	public function download_type() {
		try {
			if (!$this->user->hasPermission('modify', 'journal3/import_export')) {
				throw new Exception($this->language->get('text_permission'));
			}

			$table = $this->journal3_request->get('table');
			$type = $this->journal3_request->get('type');

			if (!in_array($table, array_keys(static::$TABLES))) {
				throw new Exception('Error: Invalid table name!');
			}

			$sql = $this->model_journal3_import_export->exportTable('journal3_' . $this->db->escape($table), ' WHERE `' . $this->db->escape($table) . '_type` = \'' . $this->db->escape($type) . '\'');

			$filename = date('Y-m-d_H-i-s', time());

			if ($this->journal3_opencart->is_oc2) {
				$file = DIR_CACHE . $filename . '.sql';
			} else {
				$file = DIR_STORAGE . 'cache/' . $filename . '.sql';
			}

			file_put_contents($file, $sql);

			if (!headers_sent()) {
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' . $filename . '.sql"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));

				if (ob_get_level()) {
					ob_end_clean();
				}

				readfile($file, 'rb');

				unlink($file);

				exit;
			} else {
				throw new Exception('Error: Headers already sent out!');
			}
		} catch (Exception $e) {
			$this->journal3_response->json('error', $e->getMessage());
		}
	}

}

class_alias('ControllerJournal3ImportExport', '\Opencart\Admin\Controller\Journal3\ImportExport');
