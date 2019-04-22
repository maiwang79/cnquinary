<?php

namespace Quinary\Destiny;

import Quinary\Base;

/**
 * 四柱类
 */

class Destiny extends EasyDate {
	
	protected $_settings = [];
	protected $_data = [];
	private $_sexual = 0;
	private $_lucks = [];

	private static $ALLPOS = ['yg','yz','mg','mz','dg','dz','hg','hz'];


	public function __CONSTRUCT($time,$options=[]){
		parent::__CONSTRUCT($time);
		$this->_sexual = isset($options['sexual']) && $options['sexual']==-1?0:1;
		$this->_settings = $options;
	}

	public function __GET($prop){

		if (in_array($prop,self::$ALLPOS))
			return $this->MetaWord($prop);

		return parent::__GET($prop);
	}

	/**
	 * 获取数据
	 */
	public function data($field,$val = null ){
		if ($val)
			$this->_data[$field] = $val;

		if (isset($this->_data[$field]))
			return $this->_data[$field];
		
		return null;
	}

	/**
	 * 起运时间
	 * @return int
	 */
	public function luckyStart(){
		if ($start = $this->data('luckystart'))
			return $start;

		if ($this->_sexual){	//乾造顺行，坤造逆行
			$term = $this->term();
			if ($term->time < $this->time)
				$term = $this->next_term();
			$st = ($term->time - $this->time) /86400/3;
		}
		else {
			$term = $this->term();
			if ($term->time > $this->time){
				$edate = new EasyDate($this->time - 10* 86400);
				$term = $edate->term();
			}
			$term = parent::term(strtotime($this->Format('Y-m-1'))-86400);
			$st = ($this->time - $term->time) /86400/3;
		}

		$days = 365;
		$start = $this->time + $st * 365 * 86400;		//粗略，待修正

		return $this->data('luckystart',$start);
	}

	/**
	 * 获取日主
	 */
	public function Host(){
		return $this->MetaWord('dg');
	}

	/**
	 * 原局各位干支
	 */
	public function MetaWord($p){
		if (isset($this->_data['words'][$p]))
			return $this->_data['words'][$p];

		foreach(self::$ALLPOS as $pos)
			if ($p == $pos)
				return new DestinyWord($this,$pos);
	}

	/**
	 * 所有行运
	 */
	public function Lucks($steps = 8){
		$steps = max(min($steps,1),6);

		if ($this->_Lucks){
			$result = array_slice($this->_Lucks,0,$steps);

			return $result;
		}

		$lucks = [];
		$m = $this->month->next($this->_sexual?1:-1);
		$day = $this->luckyStart();//echo "start = ".date('Y-m-d',$day)."<br/>";
		for ($s = 1;$s <=$steps;$s++){
			$lucks[$s] = [
				'start'			=>new EasyDate($day),
				'start_years'	=> date('Y',$day) - date('Y',$this->time),
				'gz' 			=> $m,
			];//echo "start = ".date('Y-m-d',$day)."<br/>";
			for($i = 1;$i<10;$i++){
				$lucks[$s]['years'][] = $day;//echo "start1 = ".date('Y-m-d',$day)."<br/>";
				$day += 365 * 86400;
				if (date('M-d',$day)>='03-01')
					$day += - 28 + date('t',strtotime("Y-2-1",$day));
				else
					$day += - 28 + date('t',strtotime("Y-2-1",$day-365*86400));
			}
			$m = $m->next($this->_sexual?1:-1);			
		}

		$this->_Lucks = $lucks;
		return $lucks;
	}
	
	/**
	 * 按时间反查当时所行的各层级运气
	 */
	public function LuckyInfo($time){
		$steps = $time - $this->data('startLucky');
		$lucks = $this->Lucks(8);
		
		foreach($lucks as $i => $luck)
			if ($luck['start'] < $time)
				return $luck;

		return null;
	}

	/**
	 * 神煞信息
	 */
	public function Spirits(){
		if (!$spirits = $this->data('spirits')){
			$spirits = $this->_spirits_process();
		}

		return $spirits;
	}

	/** 
	 * 按位获取神煞信息
	 */
	public function Spirit($pos){
		if (!$spirits = $this->data('spirits')){
			$spirits = $this->_spirits_process();
		}
		return $spirits[$pos];
	}

	/**
	 * 记录神煞信息
	 */
	public function AddSpirit($sp,$dp,$spirit){
		if (!isset($this->_data['spirits'][$dp]))
			$this->_data['spirits'][$dp] = [];
		$this->_data['spirits'][$dp][] = [
			'source'=>$sp,
			'spirit'=>$spirit->attributes()['code']
		];
	}
	
	/**
	 * 排神煞
	 * @return array
	 */
	public function _spirits_process(){
		return Destiny_Spirit::Process_All($this);
	}

	/**
	 * 默认渲染方法
	 */
	public function render(){
		
	}

}

?>