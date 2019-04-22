<?php

namespace Quinary\Base;

/**
 * 供干、支等继承的具有五行属性的元素基本类
 */

abstract class Elements implements IQuinariable {

	static $INSTANCES=[];
	protected $_index;

	use Enum;

	protected function __CONSTRUCT($i){
		$size = static::Size();
		$i = ($i+$size-1) % $size+1;
		$this->_index = $i;
		$keys = array_keys(static::$ITEMS);
		$this->_key = $keys[$i-1];
		
		if (empty(static::$INSTANCES[$i]))
			static::$INSTANCES[$i] = clone $this;
	}

	public function __GET($prop){
		if ($prop == 'index')
			return $this->_index;
		if ($prop == 'key')
			return $this->key();
		if ($prop == 'name')
			return $this->name();
		if ($prop == 'qtype')
			return $this->qtype();
		if ($prop == 'type')
			return $this->qtype();
		if ($prop == 'wx'){
			return $this->qtype();
		}
		if ($prop == 'wuxing')
			return $this->Wuxing();
		if ($prop == 'is_branch')
			return $this instanceof Branch?true:false;
	}

	protected static function _init(){
		if (!empty(static::$INSTANCES))
			return;
		
		static::$INSTANCES = [];
		$KEYS = static::Keys();
		$size = static::size();
		for($i=1;$i<=$size;$i++){
			$inst = new static($i);
			$inst->_index = $i;
			$inst->_key = $KEYS[$i-1];
			static::$INSTANCES[$i] = $inst;
		}
		
	}

	/**
	 * 获取枚举元素
	 * @param $entry mixed 输入数据
	 * @param $returntype int 返回类型，0|object，1|int，2|KEY
	 */
	public static function Entry($entry,$returntype = 0){
		//static::_init();

		$key = null;
		$i = 0;
		$size = static::size();
		$result_index = null;
		if(is_numeric($entry)){
		 	$i = $entry % $size;
			$i >0 || $i+= $size;
		 	if ($i===0) $i= $size;

			$result_index = $i ;
			$key = array_keys(static::$ITEMS)[$i-1];
		}
		else{
			$i = 0;
			foreach(static::$ITEMS as $k=>$v){
				$i++;
				if (strtolower($k) == $entry || $v == $entry){
					$key = $k;
					$result_index = $i ;
				}
			}
		}
		if ($returntype == 1)
			return $i;
		elseif ($returntype == 2)
			return $key;
		else {
			return  new static($i);
		}
	}

	/**
	 * 静态调用
	 */
	static function __callStatic($method,$params=[]){
		if (count($params) && in_array($params[0],static::$ITEMS)){
			$obj = new static();
			$obj->meta_make($params[0]);
			return $obj;
		}

		$param1 = array_shift($params);
		$obj = static::get($param1);
		return $obj->$method(array_shift($params));
	}

	public function Next($step = 1){
		return static::Entry(($this->index + (int)$step + self::size()-1) %self::size()+1);
	}

	public static function NextIndex($index,$step){
		$index += $step;
		$index %= self::size();
		if ($index <= 0)
			$index += self::size();
		return $index;
	}

	public function Prev($step = 1){
		return $this->Next($step * -1);
	}

	public function Wuxing(){
		return Wuxing::entry($this->qtype());
	}

	public function __toString(){
		return static::$SYMBOL.$this->index.'['.$this->name.']';
	}
	
	/**
	 * 统一定位干支的符号
	 */
	public function EIndex(){
		return static::$SYMBOL.$this->index;
	}
	
	abstract function QType();
	
	abstract function Typename();
}

?>