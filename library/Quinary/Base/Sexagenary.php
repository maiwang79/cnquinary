<?php

namespace Quinary\Base;

/**
 * 干支类
 */

static $META_NAYIN = array(
	'海中金','炉中火','大林木','路旁土','剑锋金',
	'山头火','溪下水','城头土','白腊金','杨柳木',
	'泉中水','屋上土','霹雳火','松柏木','长流水',
	'砂石金','山下火','平地木','壁上土','金箔金',
	'覆灯火','天河水','大驿土','钗钏金','桑柘木',
	'太溪水','沙中土','天上火','石榴木','大海水'
);

class Sexagenary extends Elements {

	static $SYMBOL = 'GZ';
	static $ITEMS = ['jiazi'=>"甲子",'yichou'=>"乙丑",'binyan'=>"丙寅",'dinmao'=>"丁卯",'wuchen'=>"戊辰",'jisi'=>"己巳",
	'genwu'=>"庚午",'xinwei'=>"辛未",'renshen'=>"壬申",'guiyou'=>"癸酉",'jiaxu'=>"甲戌",'yihai'=>"乙亥",
	'binzi'=>"丙子",'dinchou'=>"丁丑",'wuyan'=>"戊寅",'jimao'=>"己卯",'genchen'=>"庚辰",'xinsi'=>"辛巳",'renwu'=>"壬午",'guiwei'=>"癸未",'jiashen'=>"甲申",'yiyou'=>"乙酉",'binxu'=>"丙戌",'dinhai'=>"丁亥",
	'wuzi'=>"戊子",'jichou'=>"己丑",'genyan'=>"庚寅",'xinmao'=>"辛卯",'renchen'=>"壬辰",'guisi'=>"癸巳",'jiawu'=>"甲午",'yiwei'=>"乙未",'binshen'=>"丙申",'dinyou'=>"丁酉",'wuxu'=>"戊戌",'jihai'=>"己亥",
	'genzi'=>"庚子",'xinchou'=>"辛丑",'renyan'=>"壬寅",'guimao'=>"癸卯",'jiachen'=>"甲辰",'yisi'=>"乙巳",'binwu'=>"丙午",'dinwei'=>"丁未",'wushen'=>"戊申",'jiyou'=>"己酉",'genxu'=>"庚戌",'xinhai'=>"辛亥",
	'renzi'=>"壬子",'guichou'=>"癸丑",'jiayan'=>"甲寅",'yimao'=>"乙卯",'binchen'=>"丙辰",'dinsi'=>"丁巳",
	'wuwu'=>"戊午",'jiwei'=>"己未",'genshen'=>"庚申",'xinyou'=>"辛酉",'renxu'=>"壬戌",'guihai'=>"癸亥"];

	protected $_stem;
	protected $_branch;
	protected $_index;

	public function __CONSTRUCT($i){
		parent::__CONSTRUCT($i);
	}

	public function __GET($prop){
		if ($prop == 'stem')
			return $this->stem();
		if ($prop == 'branch')
			return $this->branch();
		if ($prop == 'fullname')
			return $this->name.'['.$this->index.']';

		return parent::__GET($prop);
	}

	public function QType(){
		return null;
	}

	public function typename(){
		return 'ganzhi';
	}

	public function stem(){
		if (!$this->_stem)
			$this->_stem = Stem::Entry($this->index);
		return $this->_stem;
	}
	
	public function branch(){
		if (!$this->_branch)
			$this->_branch = Branch::Entry($this->index);
		return $this->_branch;
	}

	/**
	 * 遁甲，根据年干支和月支推算月干等
	 * @param $branch mixed 月支或时支，支持数字、对象等各种类型
	 * @param $month_mode bool 是否遁月，年遁月寅月起始，日遁时子时起始
	 * @return object
	 */
	public function dun($branch,$month_mode=0){
		$sub_bi = Branch::entry($branch,1);
		if (!$sub_bi)
			throw new exception('invalid_branch_entry');
		
		$si =  Stem::entry($this->index,1);

		$s = ($si-1)*12+$sub_bi+60;
		if (($sub_bi == 2) || ($sub_bi == 1))
			$s += $month_mode?12:0;

		return self::entry($s);
	}
	
	public function __ToString(){
		return self::$SYMBOL.$this->_index.'['.$this->name.']';
	}
}

?>