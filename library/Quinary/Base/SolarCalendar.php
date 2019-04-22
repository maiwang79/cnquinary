<?php

namespace Quinary\Base;

/**
 * 干支历（纯阳历）算法
 */

class SolarCalendar {

	public static $TIMEZONE = +8;	//系统所用时区

	//节气数据
	public static $SOLAR_TERM_DATA = array(0,21208,42467,63836,85337,107014,
						128867,150921,173149,195551,218072,240693,
						263343,285989,308563,331033,353350,375494,
						397447,419210,440795,462224,483532,504758);

	public static $SOLAR_TERM_NAMES = array(
		'小寒','大寒',
		'立春','雨水','惊蜇','春分','清明','谷雨',
		'立夏','小满','芒种','夏至','小暑','大暑',
		'立秋','处暑','白露','秋分','寒露','霜降',
		'立冬','小雪','大雪','冬至',
	);

	/**
	 * 精确获取节气
	 * @param $when	mixed 时间，可以是时间截、日期字符串，也可以是数组[$year,$month]
	 * @param $month int 公历月份
	 * @param $ismiddle	boolean 是节还是气，0节气|1中气
	 * @return int 节气时间戳
	 */
	public static function solarTerm($when,$ismiddle = 0){
		//夏至精确公式
		//$time = 21.991346736+0.24219879 * $year - $year/400 + $year/100;

		if (is_array($when)){
			list($year,$month) = $when;
		}
		else{
			is_numeric($when) or $when = strtotime($when);
			$when or $when = time();
			//if (!$when=intval($when) && !$when = strtotime($when))
			//	$when = time();
			//echo "date2=".date('Y-m-d',$when).",";
			list($year,$month) = explode('-',date('Y-m',$when));
		}

		$n = $month * 2 - ($ismiddle?1:2);
		return (int)31556925.9747*($year-1900) + self::$SOLAR_TERM_DATA[$n]*60 + (-2208549300) - self::$TIMEZONE * 3600;	//-2208549300000 为PHP的1900-0-6-2-5的时间戳 mktime(2,5,0,0,6,1900)-
	}

	/**
	 * 较精确获取某时间所在月份的节气或中气
	 * @param $when mixed 时间,int或EasyDate
	 * @param $ismiddle boolean 是节还是气，0节气|1中气
	 * @return int 节气时间戳
	 */
	public static function SolarTerm_locate($when = 0, $ismiddle = 0){
		if ($when instanceof EasyDate)
			$when = $when->time;
		else
			$when or $when = time();
		$year = date('Y',$when);
		$n = date('m',$when) * 2 - ($ismiddle?1:2);
		return (int)31556925.9747*($year-1900) + self::$SOLAR_TERM_DATA[$n]*60 +  (-2208549300) - self::$TIMEZONE * 3600;	//-2208549300000 为PHP的1900-0-6-2-5的时间戳 mktime(2,5,0,0,6,1900)-
	}

	/**
	 * 获取节气或中气的名称
	 */
	public static function SolarTerm_Name($month,$ismiddle = 0){
		$idx = intval($month) * 2 - ($ismiddle?1:2);
		return self::$SOLAR_TERM_NAMES[$idx];
	}

	/**
	 * 计算公历给定时点所在时期（年、月、日）起止时间戳
	 * @param @when int 给定时间
	 * @param @level varchar 时期范围（年、月、日）
	 * @return array 起止时间戳
	 
	public static function period($when , $level){
		list($y,$m,$d) = explode('-',date('Y-m-d',$when));
		switch($level){
			case Period::lvYear:
				$begin = mktime(0,0,0,1,1,$y);
				$end = mktime(12,59,59,12,31,$y);
				break;
			case Period::lvMonth:
				$begin = mktime(0,0,0,$m,1,$y);
				$end = mktime(0,0,0,$m+1,1,$y)-1;
				break;
			case Period::lvDay:
				$begin = mktime(0,0,0,$m,$d,$y);
				$end = mktime(0,0,0,$m,$d+1,$y)-1;
				break;
			case Period::lvHour;
		}
		return array($begin,$end);
	}*/

	/**
	 * 计算干支历给定时点所在时期（年、月、日）起止时间戳
	 * @param $time int 给定时间
	 * @param $level varchar 时期范围（年、月、日）
	 * @return array 起止时间戳
	 */
	public static function period($time , $level){
		list($y,$m,$d,$h) = explode('-',date('Y-m-d-h',$time));
		switch($level){
			case Period::lvYear:
				$solarterm_halfyear = self::solarterm(array($y,8));
				$solarterm = self::solarterm($y,2);
				if ($time > $solarterm_halfyear){
					$begin = $solarterm_halfyear;
					$end = self::solarterm($y+1,2);
					$isfirst = 0;
				}
				elseif ($time > $solarterm){
					$begin = $solarterm;
					$end = $solarterm_halfyear;
					$isfirst = 1;
				}
				else{
					$end = $solarterm;
					$begin = self::solarterm(array($y-1,2));
					$isfirst = 0;
				}
				break;
			case Period::lvMonth:
				$solarterm = self::solarterm(array($y,$m));
				$solarterm_middle = self::solarterm(array($y,$m),1);
				if ($time > $solarterm_middle){	//8-12月
					$begin = $solarterm_middle;
					$end = self::solarterm(array($y,$m+1));
					$isfirst = 0;
				}
				elseif ($time > $solarterm){	//2-7月
					$begin = $solarterm;
					$end = $solarterm_middle;
					$isfirst = 1;
				}
				else{	//1月
					$end = $solarterm;
					$beginm = self::solarterm(array($y,$m-1),1);
					$isfirst = 0;
				}
				break;
			case Period::lvDay:
				//暂不处理本日交节/气的情况
				if ($h >= 23){	//夜子时
					$begin = mktime(23,0,0,$m,$d,$y);
					$isfirst = 1;
				}
				elseif($h >= 11) {	//午后
					$begin = mktime(11,0,0,$m,$d,$y);
					$isfirst = 0;
				}
				else{	//早子时及上午
					$begin = mktime(23,0,0,$m,$d-1,$y);
					$isfirst = 1;
				}
				$end = $begin + 3600 * 12;
				break;
			case Period::lvHour;
				$isfirst = $h %2;
				$begin = $time-=$time%3600;
				$end = $begin + 3600;
				break;
		}
		return array($begin,$end,$isfirst);
	}

	/**
	 * 排四柱
	 * @param $time int 时间
	 * @return array Sexagenary数组
	 */
	public static function Pillars($time=null){
		if(!($time=intval($time)) && !$time=strtotime($time))
			$time = time();

		$sy = date('Y',$time) - 1983;
		$mz = date('m',$time) + 1;

		if ($time < self::solarterm($time)){
			$mz --;
		}
		if ($mz <= 2){
			$mz += 12;
			$sy --;
		}
		$sd = floor(($time - 441734400)/86400+31)%60;	//441734400是1984-01-01 00:00:00
		if(date('H',$time) >=23){
			$sd++;
			$hz=1;
		}
		else{
			$hz = ceil(date('H',$time)/2)+1;	
		}

		if ($mz == 13 || $mz == 14){
			$mz -= 12;
			$sy ++;
		}

		$result = array();
		$result[Period::lvYear] = Sexagenary::entry($sy);
		$result[Period::lvMonth] = $result[Period::lvYear]->dun($mz);
		$result[Period::lvDay] = Sexagenary::entry($sd);
		$result[Period::lvHour] = $result[Period::lvDay]->dun($hz);
		if (in_array($mz % 12 ,array(1,2)))
			$result[Period::lvYear] = $result[Period::lvYear]->prev();
		return $result;
	}

	/**
	 * 仅计算日柱干支
	 */
	public static function day_gz($time = null){
		$sd = floor(($time - 441734400)/86400+31)%60;	//441734400是1984-01-01 00:00:00
		if(date('H',$time) >=23){
			$sd++;
		}
		return Sexagenary::entry($sd);
	}

	
	/**
	 * 计算干支历下一时期（年、月、日）起止时间戳
	 * @param int 给定时间
	 * @param varchar 时期范围（年、月、日）
	 * @return array 起止时间戳
	 */
	public static function easylastperiod($when, $level){
		$edate = new easydate($when);
		list($y,$m,$d) = explode('-',date('Y-m-d',$when));
		$begin = $end = 0;
		switch(strtolower($level)){
			case 'y':
			case TermLevel::year:
				$tag = $when >= 3 && $when >= self::solarterm($y,2);
				$begin = self::solarterm($y + $tag ? 1:0 ,2);
				$end = self::solarterm($y + $tag ? 2:1,2) - 1 ;
				break;
			case 'm':
			case TermLevel::month:
				$tag = $when >= self::solarterm($y,$m);
				$m += $tag ?1:0;
				if ($m == 13){
					$y ++;
					$m = 1;
				}
				$begin = self::solarterm($y ,$m);
				
				$m += 1;
				if ($m == 13){
					$y ++;
					$m = 1;
				}
				$end = self::solarterm($y, $m) - 1 ;
				break;
			case 'd':
			case TermLevel::day:
				$begin = mktime(0,0,0,$m,$d+1,$y);
				$end = mktime(0,0,0,$m,$d+2,$y);
				break;
			default:
				throw new exception('error date level');
		}
		return array(new Easydate($begin),new Easydate($end));
	}

}

?>