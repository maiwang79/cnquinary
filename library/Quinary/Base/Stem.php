<?php

namespace Quinary\Base;

/**
 * 天干基本类
 */

//$stem_types = array('jia'=>WuXing::mu,'yi'=>WuXing::mu,'bin'=>WuXing::huo,'din'=>WuXing::huo,'wu'=>WuXing::tu,'ji'=>WuXing::tu,'gen'=>WuXing::jin,'xin'=>WuXing::jin,'ren'=>WuXing::shui,'gui'=>WuXing::shui);

class Stem extends Elements {

	static $SYMBOL = 'G';
	static $ITEMS = array('jia'=>'甲','yi'=>'乙','bin'=>'丙','din'=>'丁','wu'=>'戊','ji'=>'己','gen'=>'庚','xin'=>'辛','ren'=>'壬','gui'=>'癸');

	public function QType(){
		return ceil($this->index /2);
	}
	
	public function TypeName(){
		return 'gan';
	}
}

?>