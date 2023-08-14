<?php

use Journal3\Options\Parser;
use Journal3\Utils\Arr;

class ControllerJournal3Skin extends Controller {

	public function index() {
		$cache = $this->journal3_cache->get('skin');

		if ($cache === false) {
			$this->load->model('journal3/settings');

			$settings = $this->model_journal3_settings->getSettings();

			$cache = array(
				'php'   => array(),
				'js'    => array(),
				'fonts' => array(),
				'css'   => '',
			);

			$files = array(
				'skin/blog/post',
				'skin/blog/posts',

				'skin/footer/general',

				'skin/global/countdown',
				'skin/global/general',
				'skin/global/notification',
				'skin/global/stepper',
				'skin/global/quickview',
				'skin/global/ripple',

				'skin/header/general',

				'skin/page/account',
				'skin/page/cart',
				'skin/page/category',
				'skin/page/checkout',
				'skin/page/compare',
				'skin/page/contact',
				'skin/page/information',
				'skin/page/maintenance',
				'skin/page/manufacturers',
				'skin/page/search',
				'skin/page/sitemap',
				'skin/page/wishlist',

				'skin/product/general',

				'skin/products/general',

				'skin/image_dimensions',

				'skin/catalog_mode',
			);

			$parser = new Parser($files, $settings);

			$cache['php'] += $parser->getPhp();
			$cache['js'] += $parser->getJs();
			$fonts = $parser->getFonts();
			$cache['fonts'] = Arr::merge($cache['fonts'], $fonts);
			$cache['css'] .= $parser->getCss();

			// placeholder
			$cache['php']['placeholder'] = $parser->getSetting('placeholder');

			// footer mobile
			if ($this->journal3->is_mobile) {
				if ($data = Arr::get($cache, 'php.footerMenuPhone')) {
					$cache['php']['footerMenu'] = $data;
				}
			}

			// account link
			$cache['js']['loginUrl'] = $this->url->link('account/login', '', true);

			// checkout link
			$cache['js']['checkoutUrl'] = $this->url->link('checkout/checkout', '', true);

			// set cache
			$this->journal3_cache->set('skin', $cache);
		}

		$this->journal3->load($cache['php']);
		$this->journal3_document->addJs($cache['js']);
		$this->journal3_document->addFonts($cache['fonts']);
		$this->journal3_document->addCss($cache['css'], 'settings');

		// image dimensions
		$image_dimensions = array_keys(json_decode(file_get_contents(DIR_SYSTEM . 'library/journal3/data/settings/skin/image_dimensions.json'), true));

		foreach ($image_dimensions as $image_dimension) {
			$dimensions = $this->journal3->get($image_dimension);

			$this->config->set(str_replace('image_dimensions_', 'theme_journal3_image_', $image_dimension) . '_width', (int)$dimensions['width']);
			$this->config->set(str_replace('image_dimensions_', 'theme_journal3_image_', $image_dimension) . '_height', (int)$dimensions['height']);
		}

		// other settings
		$this->config->set('theme_journal3_product_limit', $this->journal3->get('productLimit'));
		$this->config->set('theme_journal3_product_description_length', $this->journal3->get('productDescriptionLimit'));

		// active skin
		$this->journal3_document->addClass('skin-' . $this->journal3->get('active_skin'));

		// boxed layout
		if ($this->journal3->get('globalPageBoxedLayout') === 'boxed') {
			$this->journal3_document->addClass('boxed-layout');
		}

		// default view
		if (isset($this->request->cookie['view'])) {
			$this->journal3->set('globalProductView', $this->request->cookie['view']);
		}

		// old browser
		if ($this->journal3->get('oldBrowserStatus') && in_array('ie', $this->journal3->browser_classes)) {
			$this->journal3->set('oldBrowserChrome', $this->journal3_image->resize('catalog/journal3/misc/chrome.png'));
			$this->journal3->set('oldBrowserFirefox', $this->journal3_image->resize('catalog/journal3/misc/firefox.png'));
			$this->journal3->set('oldBrowserEdge', $this->journal3_image->resize('catalog/journal3/misc/edge.png'));
			$this->journal3->set('oldBrowserOpera', $this->journal3_image->resize('catalog/journal3/misc/opera.png'));
			$this->journal3->set('oldBrowserSafari', $this->journal3_image->resize('catalog/journal3/misc/safari.png'));
		} else {
			$this->journal3->set('oldBrowserStatus', false);
		}

		// catalog mode
		if (!$this->journal3->get('catalogLanguageStatus')) {
			$this->journal3_document->addClass('no-language');
		}

		if (!$this->journal3->get('catalogCurrencyStatus')) {
			$this->journal3_document->addClass('no-currency');
		}

		if (!$this->journal3->get('catalogSearchStatus')) {
			$this->journal3_document->addClass('no-search');
		}

		if (!$this->journal3->get('catalogMiniCartStatus')) {
			$this->journal3_document->addClass('no-mini-cart');
		}

		if (!$this->journal3->get('catalogCartStatus')) {
			$this->journal3_document->addClass('no-cart');
		}

		if (!$this->journal3->get('catalogWishlistStatus')) {
			$this->journal3_document->addClass('no-wishlist');
		}

		if (!$this->journal3->get('catalogCompareStatus')) {
			$this->journal3_document->addClass('no-compare');
		}

	}

}

class_alias('ControllerJournal3Skin', '\Opencart\Catalog\Controller\Journal3\Skin');
