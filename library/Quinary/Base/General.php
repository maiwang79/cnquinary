<?php

namespace Quinary\Base;

class General {
	
	/**
	 * 向量加并标准化
	 */
	public static function Power_Add($power1,$power2){
		$result =[];
		for($i=1;$i<=5;$i++){
			$result[$i] = $power1[$i]+$power2[$i];
		}
		return self::Normalize($result);
	}

	/**
	 * 向量按一定比例重分配
	 */
	public static function Power_Assign($power1,$rates,$usages){
		$power1 = $power2 = self::Standard($power1);

		$t = [];
		$s1 = 5;
		foreach($rates as $k => $r){
			if (in_array($k,$usages)){
				$power2[$k] *= $r;
				$s1 -= $powers[$k];
			}
			else{
				$t[$k] = $r;
			}
		}

		$s2 = array_sum($t);
		foreach($t as $k => $r){
			$power2[$k] *= $power1[$k] * $r * $s1 / $s2;
		}
		return self::Standard($power2);
	}

	/**
	 * 附加五行逻辑的增长方式
	 */
	public static function Power_Rise($power,$key,$rate){
		$power1 = $power;
		$power1[($key+4)%5+1]*= $rate* (1+ESY_CRATIO_BI);
		$power1[($key+5)%5+1]*= $rate* (1+ESY_CRATIO_SHEN);
		$power1[($key+1)%5+1]*= $rate* (1+ESY_CRATIO_KE);
		$power1[($key+2)%5+1]*= $rate* (1+ESY_CRATIO_HAO);
		$power1[($key+3)%5+1]*= $rate* (1+ESY_CRATIO_XIE);

		return self::Standard($power1);
	}

	/**
	 * 标准化
	 */
	public static function Normalize($power){
		$sum = array_sum($power);
		if ($sum == 255 * 3)
			return $power;
		$r = (255 * 3) / $sum ;
		$result = [];
		foreach($power as $k => $v)
			$result[$k] = intval($v * $r*100000)/100000;

		return $result;
	}

	/**
	 * 标准化
	 */
	public static function Standard($change){
		$sum = array_sum($change);
		if ($sum == 5)
			return $change;
		$r = 5 / $sum ;
		$result = [];
		foreach($change as $k => $v)
			$result[$k] = intval($v * $r*100000)/100000;

		return $result;
	}

	/**
	 * 格式化输出
	 */
	public static function Format($power){
		$power = self::Normalize($power);

		return '['.$power[3].'#'.$power[1].'_'.$power[2].'_'.$power[4].'_'.$power[5].']';
	}

	/**
	 * 变动量格式化输出
	 */
	public static function Format1($change){
		ksort($change);
		$change = self::Standard($change);
		return '['.implode(',',$change).']';
	}
}