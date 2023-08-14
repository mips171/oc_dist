<?php

class ControllerJournal3EventLanguage extends Controller {

	private static $languages;

	public function model_localisation_language_after(&$route, &$args, &$output) {
		if (is_array($output)) {
			foreach ($output as $language) {
				static::$languages[$language['code']] = $language;
			}
		}
	}

	public function view_common_language_before(&$route, &$args) {
		if ($args['languages']) {
			$images = [];

			foreach ($args['languages'] as &$language) {
				$language_extension = static::$languages[$language['code']]['extension'] ?? null;

				if ($language_extension) {
					$image = "extension/{$language_extension}/catalog/language/{$language['code']}/{$language['code']}.png";
				} else {
					$image = "catalog/language/{$language['code']}/{$language['code']}.png";
				}

				$images[$language['code']] = $image;

				if (is_file($image)) {
					$language['journal3_language_image'] = $this->journal3_image->base64($image);
				} else {
					$language['journal3_language_image'] = '';
				}
			}

			$filesize = getimagesize($images[$args['code']]);

			$args['journal3_language_image_width'] = $filesize[0];
			$args['journal3_language_image_height'] = $filesize[1];

			$args['journal3_language_image_placeholder'] = $this->journal3_image->transparent($filesize[0] * 2, $filesize[1] * 2);

			$args['journal3_language_image'] = $this->journal3_image->base64($images[$args['code']]);
		}
	}

}

class_alias('ControllerJournal3EventLanguage', '\Opencart\Catalog\Controller\Journal3\Event\Language');

