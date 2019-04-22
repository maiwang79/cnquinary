<?php

namespace Quinary\Destiny;

use Quinary\Base\WXInteract;

/**
 * ԭ��ʮ��
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

	private static $HOSTNAME = '����';

	private static $NAMES = [
		['�ȼ�','��'],
		['�ٲ�','��'],
		['�˹�','��'],
		['ʳ��','ʳ'],
		['����','��'],
		['ƫ��','��'],
		['����','��'],
		['��ɱ','ɱ'],
		['��ӡ','ӡ'],
		['ƫӡ','��']
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
	 * ����
	 */
	public function Name($simple = false){
		if ($this->_ishost)
			return self::$HOSTNAME;
		$rolename = self::$NAMES[$this->_role];
		
		return $rolename[$simple?1:0];
	}
}