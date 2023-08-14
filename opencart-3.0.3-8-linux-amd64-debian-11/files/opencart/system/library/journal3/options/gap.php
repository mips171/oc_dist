<?php

namespace Journal3\Options;

use Journal3\Utils\Arr;
use Journal3\Utils\Str;

class Gap extends Option {

	protected static function parseValue($value, $data = null, $type = 'value') {
		if (Str::startsWith($value, '__VAR__')) {
			$value = Arr::get(static::$variables, ($data['variableType'] ?? $type) . '.' . $value);
		}

		if (is_numeric($value)) {
			$value = $value . 'px';
		}

		return $value;
	}

}
