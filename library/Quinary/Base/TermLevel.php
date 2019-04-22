<?php

namespace Quinary\Base;

abstract class Setting {
	use Enum;
}

class TermLevel extends Setting {

	static $ITEMS = ['year'=>'年','month'=>'月','day'=>'日','hour'=>'时'];

	public static function GetName($index){
		return $NAMES[$index];
	}
}



?>