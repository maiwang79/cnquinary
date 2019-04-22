<?php

namespace Quinary\Base;

/**
 * 五行基本类
 */

class Wuxing extends Elements {
	const mu = 1;
	const huo = 2;
	const tu = 3;
	const jin = 4;
	const shui = 5;

	static $ITEMS = array('mu'=>'木','huo'=>'火','tu'=>'土','jin'=>'金','shui'=>'水');

	public function qtype(){
		return $this->index;
	}

	public function typename(){
		return 'wuxing';
	}

	/**
	 * 所克五行
	 */
	public function restrain_type() {
		return self::entry($this->index + 2);
	}
	
	/**
	 * 所生五行
	 */
	public function increase_type() {
		return self::entry($this->index + 1);
	}
	
	/**
	 * 按生克作用得出五行
	 */
	public function acted_type(interact $act){
		$i = $this->index;
		switch ($act->name()){
			case 'shen':
				$i+= $act;
		}
		return self::entry($i);
	}

	/**
	 * 获取生克关系（整数）
	 */
	public static function Diff($type1,$type2,$returntype = 0){
		$t = ($type1 - $type2 +5)%5;
		
		return $t;
	}

	/**
	 * 格式化输出
	 */
	public static function RenderData(array $data){
		if (count($data) != 5)
			return;
		
		$str = '[';
		foreach($data as $wx => $value){
			$str.=' '.number_format($value,4);
		}
		$str .= ' ]';
		return $str;
	}
}
?>