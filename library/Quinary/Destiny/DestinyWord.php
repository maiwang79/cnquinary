<?php

namespace Quinary\Destiny;

/**
 * ԭ�ְ���
 */
class DestinyWord {
	
	private $_destiny;
	private $_pos;
	private $_element;
	private $_role;

	private function __CONSTRUCT(Destiny $destiny,$pos,$elem){
		$this->_destiny = $destiny;
		$this->_pos = $pos;
		$this->_element = $elem;
	}

	/**
	 * ������ʵ���л�ȡԪ�ض���
	 */
	public static function locate(Destiny $destiny,$pos){
		$l = strtolower(substr($pos,0,1));
		$type = strtolower(substr($pos,1,1));

		static $LEVELS = array('y'=>'year','m'=>'month','d'=>'day','h'=>'hour');
		$level = $LEVELS[$l];
		$gz = $destiny->$level;
		$elem = null;
		if ($type == 'g')
			$elem = $gz->stem;
		elseif ($type == 'z')
			$elem = $gz->branch;

		if ($elem){
			return new self($destiny,$pos,$elem);
		}
		else
			return null;
	} 

	/**
	 * ��ȡ����
	 */
	public function Destiny(){
		return $this->_destiny;
	}

	/**
	 * ��λ��Ӧ��ɷ
	 */
	public function Spirits(){
		return $this->_destiny->Spirit($this->_pos);
	}
	
	/**
	 * ʮ��
	 */
	public function Role(){
		if (!$this->_role){
			$this->_role = DestinyRole::Get($this->_destiny,$this);
		}
		return $this->_role;
	}
}