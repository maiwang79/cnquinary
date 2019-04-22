<?php

namespace Quinary\Base;

define('ESY_WXACT_SHEN',1);

define('ESY_CRATIO_SHEN', 0.02);
define('ESY_CRATIO_KE', -0.035);
define('ESY_CRATIO_BI', 0.03);
define('ESY_CRATIO_HAO', -0.005);
define('ESY_CRATIO_XIE', -0.015);

define('ESY_DUNRATIO_SHEN', 0.02);
define('ESY_DUNRATIO_KE', -0.035);
define('ESY_DUNRATIO_BI', 0.03);
define('ESY_DUNRATIO_HAO', -0.005);
define('ESY_DUNRATIO_XIE', -0.015);

define('ESY_DUN_COES',0.6);	//年遁月、日遁时的生克作用系数
define('ESY_ACT1_COES',0.4);	//同柱干支作用系数1（支对干作用）
define('ESY_ACT2_COES',0.4);	//同柱干支作用系数1（干对支作用）

/**
 * 五行生克基本类
 */
class WXInteract {
	const SHEN = -1;
	const KE = -2;
	const BI = 0;
	const HAO = 1;
	const XIE = 2;

	private $_self = null;
	private $_foreign = null;
	private $_act = 0;
	private $_actRatio = 0;

	private function __CONSTRUCT($elem1,$elem2){

		if (is_int($elem1)){
			$elem1 = Wuxing::Entry($elem1);
		}
		if (is_int($elem2)){
			$elem2 = Wuxing::Entry($elem2);
		}

		$this->_self = $elem1;
		$this->_foreign = $elem2;if(!is_object($elem1)){print_r($elem1);print_r($elem2);throw new \Exception('wxact invalid elem1');exit('invalid elem1');}if(!is_object($elem2)){print_r($elem2);exit('wxact invalid_elem2');}
		$this->_act = $elem2->wuxing->index - $elem1->wuxing->index;
		if ($this->_act >= 3)
			$this->_act -= 5;
		elseif($this->_act <=-3)
			$this->_act += 5;

		switch($this->_act){
			case WXInteract::SHEN:
				$ratio1 = ESY_CRATIO_SHEN;
				$ratio2 = ESY_CRATIO_HAO;
				break;
			case WXInteract::KE:
				$ratio1 = ESY_CRATIO_KE;
				$ratio2 = ESY_CRATIO_XIE;
				break;
			case WXInteract::BI:
				$ratio1 = $ratio2 = ESY_CRATIO_BI;
				break;
			case WXInteract::XIE:
				$ratio1 = ESY_CRATIO_XIE;
				$ratio2 = ESY_CRATIO_KE;
				break;
			case WXInteract::HAO:
				$ratio1 = ESY_CRATIO_HAO;
				$ratio2 = ESY_CRATIO_SHEN;
				break;
		}
		$this->_actRatio = $ratio1;
	}

	/**
	 * 获取
	 */
	public static function Get($elem1,$elem2){
		return new self($elem1,$elem2);
	}

	public static function Get_Wuxing($wuxing,$act){
		if (is_int($wuxing))
			$wuxing = Wuxing::Entry($wuxing);

		switch($act){
			case 'SHEN':
				$act = WXInteract::SHEN;
				break;
			case 'KE':
				$act = WXInteract::KE;
				break;
			case 'BI':
				$act = WXInteract::BI;
				break;
			case 'HAO':
				$act = WXInteract::HAO;
				break;
			case 'XIE':
				$act = WXInteract::XIE;
				break;
		}
		if (!$act)
			return $wuxing;
		
		$act_wuxing = $wuxing->next($act);

		return $act_wuxing;
	}

	/**
	 * 年遁月、日遁时获取生克对象及系数
	 */
	public static function Dun($elem1,$parent_elem){
		$obj = new self($leem1,$parent_elem);

		$dun_ratio1 = null;
		switch($obj->_act){
			case WXInteract::SHEN:
				$dun_ratio1 = ESY_DUNRATIO_SHEN;
				break;
			case WXInteract::KE:
				$dun_ratio1 = ESY_DUNRATIO_KE;
				break;
			case WXInteract::BI:
				$dun_ratio1 = ESY_DUNRATIO_BI;
				break;
			case WXInteract::XIE:
				$dun_ratio1 = ESY_DUNRATIO_XIE;
				break;
			case WXInteract::HAO:
				$dun_ratio1 = ESY_DUNRATIO_HAO;
		}
		$obj->_actRatio = $dun_ratio1;
		return $obj; 
	}

	public function Act(){
		return $this->_act;
	}
	
	public function ActRatio(){
		return $this->_actRatio;
	}
}
