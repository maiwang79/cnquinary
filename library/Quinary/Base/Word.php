<?php

namespace Quinary\Base;

const WX_FR_BI= 1;			//五行作用常数之比
const WX_FR_SHEN = 1.2622;	//五行作用常数之生
const WX_FR_KE= -1.3968;	//五行作用常数之克
const WX_FR_XIE = -0.6420;	//五行作用常数之泄
const WX_FR_HAO = 0.2212;	//五行作用常数之耗


//不同环境气场下的五行寒热作用
global $FAV_GROUP;
$FAV_GROUP = array(
	'Q1'	=> array(
		'NORTH' => array(4,5),
		'SOUTH'	=> array(1,2),
		'FREE'	=> array(3)
	),
	'Q2'	=> array(
		'NORTH' => array(5),
		'SOUTH'	=> array(2),
		'FREE'	=> array(1,3,4)
	),
	'Q3'	=> array(
		'NORTH'	=> array(4,5),
		'SOUTH'	=> array(2,3),
		'FREE'	=>	array(1),
	),
	'Q4'	=> array(
		'NORTH'	=> array(4,5),
		'SOUTH'	=> array(1,2),
		'FREE'	=> array(3),
	),
	'Q5'	=> array(
		'NORTH'	=> array(1,4,5),
		'SOUTH'	=> array(2),
		'FREE'	=> array(3),
	)
);

/**
 * 行运元素
 */

class Word {
	private $_timelevel;
	private $_scope;
	private $_element;
	protected $_data = [];

	private $_edate;
	private $_begin_time;
	private $_end_time;
	protected $_pattern;

	private function __CONSTRUCT(EasyDate $edate,Pattern $pattern = null ){
		$this->_edate = $edate;
		$this->_pattern = $pattern;
	}

	/**
	 * 从数据实例中获取元素对象
	 */
	public static function locate(EasyDate $edate,$pos,$pattern = null){//echo "locate ".$edate->time." $pos<br/>";
		$l = strtolower(substr($pos,0,1));
		$type = strtolower(substr($pos,1,1));

		static $LEVELS = array('y'=>'year','m'=>'month','d'=>'day','h'=>'hour');
		$level = $LEVELS[$l];
		$gz = $edate->$level;
		$elem = null;
		if ($type == 'g')
			$elem = $gz->stem;
		elseif ($type == 'z')
			$elem = $gz->branch;

		$begin_time = $middle_time = $end_time = 0;
		switch($l){
				case 'y':
					$begin_time = strtotime($edate->format('Y-1-1'));
					$middle_time = strtotime($edate->format('Y-7-1'));
					$end_time = strtotime($edate->format('Y-12-31 23:59:59')) + 1;
					break;
				case 'm':
					$begin_time = $edate->term();
					$middle_time = $edate->middle_term();
					$end_time = $edate->next_term();
					break;
				case 'd':
					$begin_time = strtotime($edate->format('Y-m-d'))-3600;
					$middle_time = strtotime($edate->format('Y-m-d 11:00:00'));
					$end_time = $begin_time +86400 ;
					break;
				case 'h':
					$base = strtotime($edate->format('Y-m-d')) - 3600;
					$begin_time = $base + ($edate->format('H')+1) * 3600;
					$middle_time = $begin_time + 3600;
					$end_time = $begin_time + 7200;
					break;
		}
		$edate = new EasyDate($begin_time);

		if ($elem){
			$word = new self($edate,$pattern);
			$word->_timelevel = $l;

			if ($elem->is_branch){
				if ($l == 'h'){
					$offset = $edate->format('H') % 2 == 1 ? 3600:0;
					//$offset = 0;
					$word->_begin_time = $begin_time + $offset;
					$word->_end_time = $middle_time + $offset;
				}
				else {
					$word->_begin_time = $middle_time;
					$word->_end_time = $end_time;
				}
			}
			else {
				$word->_begin_time = $begin_time;
				$word->_end_time = $middle_time;
			}
			$word->_element = $elem;
			$word->_pos = $pos;
			return $word;
		}
		else
			return null;
	}

	public function __GET($prop){
		$prop = strtolower($prop);

		if ($prop == 'timelevel')
			return $this->_timelevel;

		if (in_array($prop,['edate','element','pattern','begin_time','end_time']))
			return $this->{'_'.$prop};

		if ($prop == 'time')
			return $this->_edate->time;


		if (in_array($prop,array('name','index')))
			return $this->_element->$prop;

		if ($this->_element->$prop)
			return $this->_element->$prop;

		if ($prop == 'term'){
			switch($this->timelevel){
				case 'year':
				case 'y':
					$term = $gan->edate->format('Y');
					break;
				case 'month':
				case 'm':
					$term = $gan->edate->format('Ym');
					break;
				case 'day':
				case 'd':
					$term = $gan->edate->format('Ymd');
					break;
				case 'hour':
				case 'h':
					$term = $gan->edate->format('YmdH');
					break;
				default:
					exit('invalid level '.$gan->timelevel);
			}
			if ($this->_element->is_branch)
				$term .= 'Z';
			else
				$term == 'G';
			return $term;
		}

		if(isset($this->_data[$prop]))
			return (float)number_format($this->_data[$prop],4);

	}

	public function __CALL($method,$param){
		if (method_exists($this->_element,$method))
			return call_user_func_array(array($this->_element,$method),$param);
	}

	public function __ToString(){
		$print = strtoupper($this->_timelevel).$this->_element;
		//$print = $this->_element."\n";
		//foreach($this->_data as $k=>$v)
		//	$print.= "$k=> $v \n";
		return $print;
	}

	/**
	 * 获取上一级Word
	 */
	public function parent(){
		$edate = $this->_edate;
		$parent = null;
		$ppos = null;
		$t = $this->_edate->time;
		switch($this->_timelevel){
			case 'y':
				break;
			case 'm':
				$m = date('m',$t);
				$d = date('d',$t);
				$termd = date('d',$edate->term());
				if ($m <=7 || $m == 8 && $d < $termd)
					$ppos = 'yg';
				else
					$ppos = 'yz';
				break;
			case 'd':
				$middle_term = $parent;
				if ($t > $edate->term()->time && $t < $edate->middle_term()->time)
					$ppos = 'mg';
				else
					$ppos = 'mz';
				break;
			case 'h':
				if (date('H',$t) >= 11 && date('H',$t) < 23)
					$ppos = 'dz';
				else
					$ppos = 'dg';
				//echo "parent ppos $ppos ".date('Y-m-d H:i:s',$t)." ";
				break;
		}

		if ($ppos){
			$parent = self::locate($edate,$ppos,$this->pattern);
		}
		return $parent;
	}

	/**
	 * 获取邻位
	 */
	public function partner(){
		if (!$this->element->is_branch){
			$pos = $this->timelevel.'z';
			$begin_time = $this->begin_time;
		}
		else{
			$pos = $this->timelevel.'g';
			$begin_time = $this->parent()->begin_time;
		}

		return self::locate(new EasyDate($begin_time),$pos,$this->pattern);
	}


	/**
	 * 下属子项
	 */
	public function subfirst(){
		switch($this->_timelevel){
			case 'y':
				$subpos = 'mg';
				break;
			case 'd':
				$subpos = 'hg';
				break;
			default:
				throw new \Exception('unsupport');
		}
		return self::locate($this->_edate,$subpos,$this->pattern);
	}

	/**
	 * 前一字，历史期间必须有数据
	 */
	public function prev(array $data = []){
		$new = clone $this;
		$new->_element = $new->_element->next(-1);
		$new->data = $data;
		return $new;
		
		if ($this->element->is_branch)
			return $this->partner();
		else{
			switch($this->_timelevel){
				case 'y':
					$begin_time = $this->begin_time;
					$begin_time = strtotime(date('Y-m-d',$begin_time));
					break;
				case 'm':
					$begin_time = strtotime(date('Y-m-1',$this->begin_time));
					break;
				case 'd':
					$begin_time -= 86400;
					break;
				case 'h':
					$begin_time -= 3600;
			}
			$pos = $this->_timelevel.'z';
			return self::Locate(new EasyDate($begin_time),$pos);
		}		
	}

	/**
	 * 后一字，基于推测需要可以不生成数据
	 */
	public function next(array $data = []){
		/*$new = clone $this;
		$new->_element = $new->_element->next(1);
		$new->data = $data;
		return $new;*/		
		
		if (!$this->element->is_branch)
			return $this->partner();
		else{
			$begin_time = $this->end_time;
			$pos = $this->_timelevel.'g';
			return self::Locate(new EasyDate($begin_time),$pos);
		}
	}

	/**
	 * 年级别前置状态，计算元素与前一元素的生克关系
	 * 更精准的话还可提供实际场景中的历史禀气因素
	 */
	public function pre_state(){
		$w = $this;
		$char = $w->EIndex();
		$gz = $this->Sexagenary();

		$parent = null;
		if (strtoupper(substr($char)) == 'G'){
			$prev_w = $w->Partner()->prev();
		}
		else{
			$prev_w = $w->Partner();
		}

		$act1 = $result['func'] = ($w->qtype +5 - $prev_w->qtype) % 5;

		$isg = 0;
		if ($gz->index % 12 <=5)
			$isg = 1;

		$parent_gz = $gz->parent();
		$parent_element = $isg ? $parent_gz->stem : $parent_gz->branch;
		$act2 = ($w->qtype +5 - $parnet_element->qtype) % 5;

		switch ($act2){
			case 0:
				$r = WX_FR_BI;
				break;
			case 1:
				$r = WX_FR_SHEN;
				break;
			case 2:
				$r = WX_FR_KE;
			case 3:
				$r = WX_FR_XIE;
			case 4:
				$r = WX_FR_HAO;
		}

		return $r;
	}


	/**
	 * 根据绝对五行及前置状态按喜忌划分五行种类
	 */
	public function fav_group(){
		$gz = $this->Sexagenary();
		return $FAV_GROUP['Q'.$this->qtype];
	}
	
	/**
	 * 设置或获取数据
	 */
	public function data($field,$value = null){
		if ($value !== null){
			$this->_data[$field] = $value;
			return $this;
		}

		if (isset($this->_data[$field]))
			return $this->_data[$field];
	}
	
	/**
	 * 数组形式数据合并
	 */
	public function dataMerge(array $_data){
		$this->_data = array_merge($this->_data,$_data);
	}
}
?>