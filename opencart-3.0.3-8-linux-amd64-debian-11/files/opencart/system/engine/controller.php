<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Controller class
*/
abstract class Controller {
	protected $registry;

	public function __construct($registry) {
		$this->registry = $registry;
	}

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}

    public function getFilterValue($request, $key, $default = '') {
        if (isset($request->get[$key])) {
            return $request->get[$key];
        } else {
            return $default;
        }
    }

    public function buildURL($request, $params)
    {
        $url = '';

        foreach ($params as $param) {
            $paramName = $param[0];
            $encode = $param[1] ?? false; // Default to false if not specified

            if (isset($request[$paramName])) {
                $value = $request[$paramName];

                // Apply URL encoding if required
                if ($encode) {
                    $value = urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
                }

                $url .= '&' . $paramName . '=' . $value;
            }
        }

        return $url;
    }
}