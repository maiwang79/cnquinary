<?php

namespace Quinary\Base;

/**
 * 枚举基本类
 */

trait Enum {
	//protected static $size;
	protected static $SYMBOL;	
	protected static $ITEMS;
	//protected static $spec_prefix;

	protected $_key;
	
	/*
	public static function __callStatic($key,$params=array()){
		if ($i = rray($key,static::$KEYS)){
			$obj = new static();
			$obj->_key = $key;
			return $obj;
		}
		return null;
	}*/

	public function __GET($prop){


		//if ($prop == 'spec'){
		//	return static::$spec_prefix.'_'.$this->index;
		//}
	}

	static function Items(){
		return static::$ITEMS;
	}

	static function Keys(){
		return array_keys(static::$ITEMS);
	}

	static function Names(){
		return array_values(static::$ITEMS);
	}

	static function Size(){
		return count(static::$ITEMS);
	}

	static function Find($key){
		if (isset(static::$ITEMS[$key])){
			$obj = static::entry($key);
			return $obj;
		}
		if ($key1 = array_search($key,$ITEMS)){
			$obj = static::entry($key);
			return $obj;
		}
		return null;
	}

	public function key(){
		return $this->_key;
	}
	
	public function name(){
		return static::$ITEMS[$this->_key];
	}

	/*
	static function spec($i){
		if (!is_int($i))
			throw new exception('invalid enum index');
		return static::$spec_prefix.'_'.static::$key[$i-1];
	}
	*/

	public function equal($entry1){
		if ($this->_key === $entry1->_key)
			return true;
		return false;
	}

	public function __ToString(){
		return $this->name;
	}
}

?>