<?php

namespace Quinary\Base;

class EasyDate {

	const base = 441763200;		//mktime(0,0,0,1,1,1984)

	protected $_time = 0 ;			//当前时间戳
	protected $_term = 0;			//本月节气时间
	protected $_middle_term = 0;	//本月中气时间

	protected $_sexagenarys = array('year'=>null,'month'=>null,'day'=>null,'hour'=>'');

	public function __construct($time = 0){
		if (!is_numeric($time))
			$time = strtotime($time);
		$this->_time = $time ?$time : time();
		$this->_term = SolarCalendar::solarterm($this->time);	//获取节
		$this->_middle_term = SolarCalendar::solarterm($this->time,1);	//获取气

		$this->_calculate();
	}


	public function __get($property){
		$property = strtolower($property);
		if (isset($this->$property))
			return $this->$property;
		elseif (in_array($property, TermLevel::Names())){
			return $this->_sexagenarys[$property];
		}
		elseif (in_array(strtolower($property) ,array('time','term','middle_term'))){
			$property1 = '_'.strtolower($property);
			return $this->$property1;
		}
		elseif (in_array($property,TermLevel::Keys())){
			return $this->_sexagenarys[$property];
		}
		else{
			return null;
		}
	}

	/**
	 * 排四柱
	 */
	protected function _calculate(){
		$sy = date('Y',$this->_time) - 1983;
		$mz = date('m',$this->_time) + 1;

		$sd = floor(($this->_time - 441734400)/86400+31)%60;	//441734400是1984-01-01 00:00:00
		if(date('H',$this->_time) >=23){
			$sd++;
			$hz=1;
		}
		else{
			$hz = ceil(date('H',$this->_time)/2)+1;	
		}

		if ($this->_time < (int)SolarCalendar::solarterm_locate($this->_time)){
			$mz --;
		}
		
			if ($mz <= 2){
				//$mz += 12;
				$sy --;
			}
		/*if ($mz == 13 || $mz == 14){
			$mz -= 12;
			$sy --;
		}*/
		
		$this->_sexagenarys['year'] = Sexagenary::Entry($sy);
		$this->_sexagenarys['month'] = $this->year->dun($mz,1);	//年遁月寅月起始
		$this->_sexagenarys['day'] = Sexagenary::Entry($sd);
		$this->_sexagenarys['hour'] = $this->day->dun($hz); //日遁时子时起始
	}

	/**
	 * 获取实例
	 * @param $date mixed 日期
	 */
	public static function get($date=null){
		return new self($date);
	}

	/**
	 * 流年/月/日/时何字当运
	 * @param $level termlevel 
	 * @return mixed 干或支
	 */
	public function word(termlevel $level){
		switch ($level->index){
			case TermLevel::year:
				$type = ($this->_sexagenarys['month']->Branch >=3 && $this->_sexagenarys['m']->Branch < 9) ? 1:2;
				break;
			case TermLevel::month:
				$type = $this->time >= SolarCalendar::solarterm_locate($this,1)? 2:1;
				break;
			case TermLevel::day:
				$type = $this->_sexagenarys['hour']->Branch >=7 ? 2:1;
				break;
			case TermLevel::hour:
				$type = date('h',$this->time) % 2 ==1 ?1:2;
				break;
		}

		$level_key = $level->key;

		return $type == 1 ? $this->$level_key->stem :  $this->$level_key->branch;
	}
	

	/**
	 * 本月节气的easydate对象
	 */
	public function term(){
		return self::get($this->term);
	}

	/**
	 * 本月中气的easydate对象
	 */
	public function middle_term(){
		return self::get($this->middle_term);
	}

	/**
	 * 下一节气easydate对象
	 */
	public function next_term(){
		$nextterm_time = SolarCalendar::solarterm_locate($this->_time + 86400 * 27);
		return self::get($nextterm_time);
		
	}

	/**
	 * 当前起始时间戳
	 * @param $level termlevel 年/月/日
	 * @return array
	 */
	public function period(termlevel $level){
		return SolarCalendar::period($this->time,$level);
	}

	/**
	 * 格式化显示
	 * @param $format string 格式字符串，待定义
	 * @return string
	 */
	public function format($format=null){
		return date($format,$this->time);
	}

	/**
	 * 默认格式显示
	 */
	public function __tostring(){
		$string = array();
		foreach($this->_sexagenarys as $f){
			$string[] = $f->name;
		}
		return date('Y-m-d H:i',$this->_time).' '.implode(' ',$string);
	}
}
?>