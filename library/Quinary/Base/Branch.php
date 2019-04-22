<?php

namespace Quinary\Base;

/**
 * 地支基本类
 */

class Branch extends Elements {

	static $SYMBOL = 'Z';
	static $ITEMS = array('zi'=>'子','chou'=>'丑','yan'=>'寅','mao'=>'卯','chen'=>'辰','si'=>'巳','wu'=>'午','wei'=>'未','shen'=>'申','you'=>'酉','xu'=>'戌','hai'=>'亥');

	static $QUINARY_TYPES = array('zi'=>Wuxing::shui,'chou'=>Wuxing::tu,'yan'=>Wuxing::mu,'mao'=>Wuxing::mu,'chen'=>Wuxing::tu,'si'=>Wuxing::huo,'wu'=>Wuxing::huo,'wei'=>Wuxing::tu,'shen'=>Wuxing::jin,'you'=>Wuxing::jin,'xu'=>Wuxing::tu,'hai'=>Wuxing::shui);

	//藏干转化五行综合属性
	static $BRANCH_TYPES = array(
		'1' => array(5=>1),
		'2' => array(3=>0.5, 4=>0.4, 5=>0.1),
		'3' => array(1=>0.6, 2=>0.3,3=> 0.1 ),
		'4' => array(1=>1),
		'5' => array(3=>0.5, 5=>0.4,2=>0.1),
		'6' => array(2=>0.5, 4=>0.3,3=>0.1),
		'7' => array(2=>0.5, 3=>0.5),
		'8' => array(3=>0,5, 2=>0.4,1=>0.1),
		'9' => array(4=>0,5, 2=>0.3,3=>0.2),
		'10' => array(4=>1),
		'11' => array(3=>0.5,2=>0.3,4=>0.2),
		'12' => array(5=>0.6,1=>0.4),
	);

	/**
	 * 获取一般五行属性
	 */
	public function qtype(){
		return self::$QUINARY_TYPES[$this->key];
	}

	/**
	 * 支五行转化特性
	 */
	public function alias_qtype(){
		$tary = self::$BRANCH_TYPES[$this->index];
		reset($tary);
		list($key,$val) = current($tary);
		return $key;
	}

	/**
	 * 视同五行属性，土寄四宫
	 */
	public function alias_wuxing(Wuxing $show_wuxing = null){
		if (is_object($show_wuxing)){
			$show_wx = $show_wuxing->index;
		}
		else{
			$show = Wuxing::Entry($show_wuxing);
			$show_wx = $show->index;
		}
		
		if ($this->wuxing->index != 3){
			if ($this->index == 10){
				$type = [4=>0.8,5=>0.2];
				return 5;
			}
			elseif ($this->index == 9){
				$type = [4=>0.8,2=>0.2];
				return 2;
			}
			elseif ($this->index == 6){
				$type = [2=>0.6,4=>0.4];
			}
			if (isset($type)){
				$keys = array_keys($type);
				if ($show_wx == $this->wuxing->index){
					return $keys[1];
				}
				else
					return $keys[0];
			}
			else
				return $this->wuxing->index;
		}
		else {
			$type = self::$BRANCH_TYPES[$this->index];
			$keys = array_keys($type);
	
			if ($show_wx == 3){
				return $keys[1];
			}
			elseif ($show_wx == $keys[1])
				return 3;
			else
				return 3;
		}
	}

	public function TypeName(){
		return 'zhi';
	}

	/**
	 * 获取地支五行组合
	 */
	public function complex_qtype(){
		
		//藏干转化而来的五行禀气性质
		$types= self::$BRANCH_TYPES[$this->index];

		return $types;
	}
}
?>