<?php

namespace Quinary\Destiny;

use Quinary\Base\WXInteract;

/**
 * 原局十神
 */
class DestinyRole {
	private $_word;
	private $_ishost = false;
	private $_role = null;

	private function __construct($word,$role,$ishost = null){
		$this->_word = $word;
		$this->_role = $role;
		if ($role == 0)
			$this->_ishost = $ishost ? true:false;
	}

	private static $HOSTNAME = '日主';

	private static $NAMES = [
		['比肩','比'],
		['劫财','劫'],
		['伤官','伤'],
		['食神','食'],
		['正财','财'],
		['偏财','才'],
		['正官','官'],
		['七杀','杀'],
		['正印','印'],
		['偏印','枭']
	];
	
	public static function Get(Destiny $destiny, $word){
		$host = $destiny->day->stem;
		
		$i = WXInteract::Get($host = $destiny->dg,$word)->Act();
		if ($word== $host && $word->pos == 'dg'){
			return new self($word,0,1);
		}
		$i >= 0 or $i+=5;
		$j = $i;

		$i = 2* $i + (($host->index + $word->index) %2 ? 0:1);

		return new self($word,$i);
	}


	public function __tostring(){
		return $this->Name();
	}

	/**
	 * 名称
	 */
	public function Name($simple = false){
		if ($this->_ishost)
			return self::$HOSTNAME;
		$rolename = self::$NAMES[$this->_role];
		
		return $rolename[$simple?1:0];
	}
}