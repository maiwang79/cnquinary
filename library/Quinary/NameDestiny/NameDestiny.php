<?php

namespace Quinary\NameDestiny;

/**
 * 姓名预测学算法
 */

class NameDestiny {

	public static $encoding = 'utf-8';
	public static $DOUBLE_SURNAMES = array('欧阳','太史','端木','上官','司马','东方','独孤','南宫','万俟','闻人','夏侯','诸葛','尉迟','公羊','赫连','澹台','皇甫','宗政','濮阳','公冶','太叔','申屠','公孙','慕容','仲孙','钟离','长孙','宇文','司徒','鲜于','司空','闾丘','子车','亓官','司寇','巫马','公西','颛孙','壤驷','公良','漆雕','乐正','宰父','谷梁','拓跋','夹谷','轩辕','令狐','段干','百里','呼延','东郭','南门','羊舌','微生','公户','公玉','公仪','梁丘','公仲','公上','公门','公山','公坚','左丘','公伯','西门','公祖','第五','公乘','贯丘','公皙','南荣','东里','东宫','仲长','子书','子桑','即墨','达奚','褚师');	//复姓，仅取中国现存常见复姓

	private static $RESOURCES = [];

	private static $_resource_word = "resource/word.xml";
	private static $_resource_result = "resource/result.xml";
	private static $_resource_eightyone = "resource/eightyone.xml";
	private static $_resource_pattern = "resource/pattern.xml";

	public static $LUCKY_LEVELS = array(
		'-2'	=> '大凶',
		'-1'	=> '凶',
		'-0.5'	=> '半凶',
		'0'		=> '平',
		'0.5'	=> '半吉',
		'1'		=> '吉',
		'2'		=> '大吉'
	);

	private $_data = array();

	/**
	 * 构造器
	 */
	public function __CONSTRUCT($name,$gender=1,$options = array()){

		$this->_parse_name($name,$options);
		$this->_data['gender'] = $gender ? 1:0;
	}

	public static function init(){
		self::$_resource_word = __DIR__."/../resource/word.xml";
		self::$_resource_result = __DIR__."/../resource/result.xml";
		self::$_resource_eightyone = __DIR__."/../resource/eightyone.xml";
		self::$_resource_pattern = __DIR__."/../resource/pattern.xml";
	}

	/**
	 * 获取实例
	 */
	public static function Instance($name){
		return new self($name);
	}

	/**
	 * 获取数据资源
	 */
	public static function Resource($resource){
		$var = '_resource_'.$resource;
		$resourcefile = realpath(self::$$var);
		if (!$resourcefile)
			return null;

		if (!isset(self::$RESOURCES[$resource])){
			try {
				if (!file_exists($resourcefile))
					throw new Kohana_Exception('resource_no_found '.$resourcefile);
				self::$RESOURCES[$resource] = simplexml_load_file($resourcefile);
			}
			catch(Exception $e){
				die($e->getMessage());
			}
		}

		return self::$RESOURCES[$resource];
	}

	/**
	 * 吉凶程度名称
	 */
	public static function LuckyName($lucky){
		return isset(self::$LUCKY_LEVELS[$lucky])? self::$LUCKY_LEVELS[$lucky]:null;
	}

	public function __GET($prop){
		$prop == strtolower($prop);

		if ($prop == 'sky')
			return $this->_data['names'][0]['num']+$this->_data['names'][1]['num'];			//天格

		if ($prop == 'owner')
			return $this->_data['names'][1]['num']+$this->_data['names'][2]['num'];			//人格

		if ($prop == 'earth')
			return $this->_data['names'][2]['num']+$this->_data['names'][3]['num'];			//地格

		if ($prop == 'outter'){			//外格，单名加名字数
			$t = $this->_data['names'][0]['num']+$this->_data['names'][3]['num'];
			if ($t == 2)
				$t += $this->_data['names'][2]['num'];
			return $t;			
		}
		
		if ($prop == 'total'){		//总格
			$t = 0;
			foreach($this->_data['names'] as $w)
				if ($w['text'])
					$t += $w['num'];

			return $t;
		}
	}

	/**
	 * 解析姓名为天地人格
	 */
	private function _parse_name($name,$options = null){
		$len = mb_strlen($name, self::$encoding);

		if ($len == 3){
			if (in_array(mb_substr($name,0,2,self::$encoding),self::$DOUBLE_SURNAMES)){	//复姓

				$this->_data['names'][0] = $this->Word(mb_substr($name,0,1,self::$encoding));
				$this->_data['names'][1] = $this->Word(mb_substr($name,1,1,self::$encoding));
				$this->_data['names'][2] = $this->Word(mb_substr($name,2,1,self::$encoding));
				$this->_data['names'][3] = array('text'=>null,'num'=>1);
			}
			else {
				$this->_data['names'][0] = array('text'=>null,'num'=>1);
				$this->_data['names'][1] = $this->Word(mb_substr($name,0,1,self::$encoding));
				$this->_data['names'][2] = $this->Word(mb_substr($name,1,1,self::$encoding));
				$this->_data['names'][3] = $this->Word(mb_substr($name,2,1,self::$encoding));
			}
		}
		elseif ($len == 2){
				$this->_data['names'][0] = array('text'=>null,'num'=>1);
				$this->_data['names'][1] = $this->Word(mb_substr($name,0,1,self::$encoding));
				$this->_data['names'][2] = $this->Word(mb_substr($name,1,1,self::$encoding));
				$this->_data['names'][3] = array('text'=>null,'num'=>1);
		}
		
		//出嫁妇人冠夫姓，但双姓不取，算法本身未作定义
		if (isset($options['husband_surename']) && !$this->_data[0]['text'])
			$this->_data['names'][0] = $this->Word($options['husband_surename']);
	}

	/**
	 * 按笔划数获取相应单字
	 */
	public static function Words($num){
		$resource_word = self::Resource('word');
		
		return $resource_word->xpath("/root/word[@num='$num']");
	}

	/**
	 * 获取单字
	 */
	public static function Word($word){
		if (!is_object(self::$_resource_word))
			self::$_resource_word = simplexml_load_file(self::$_resource_word);
		
		foreach (self::$_resource_word->xpath("word[@text='$word']") as $wordnode){
			$return = array();
			foreach($wordnode->attributes() as $k => $v)
				$return[$k] = (string)$v;
			return $return;
		}
	}

	/**
	 * 获取笔划数
	 */
	public static function WordNum($word){
		if (!is_object(self::$_resource_word))
			self::$_resource_word = simplexml_load_file(self::$_resource_word);

		foreach (self::$_resource_word->xpath("word[@text='$word']") as $wordnode){
			return $num = $wordnode->attributes()['num'];
		}
	}

	/**
	 * 获取五行属性
	 */
	public static function WuXing($num){
		return branch::Entry($num % 10)->wuxing;
	}

	/**
	 * 数理运气
	 */
	public static function number_fortunes($num,$type = null){
		if (!is_object(self::$_resource_eightyone))
			self::$_resource_eightyone = simplexml_load_file(self::$_resource_eightyone);

		$numnode  = null;
		foreach (self::$_resource_eightyone->xpath("number[@id='$num']") as $numnode){
			$result = array('means'=>array());

			foreach($numnode->attributes() as $k=>$v)
				$result[$k]= $v;

			foreach($numnode->children() as $mean){
				$result['means'][(string)$mean['type']] = (string)$mean;
			}

			return $result;
		}
		return null;
	}

	/**
	 * 用于渲染的格式化数据
	 */
	public static function formatnum($num){
		if ($num >= 10)
			return $num;
		
		return '&nbsp;&nbsp;'.$num;
	}

	/**
	 * 按格局计算运气
	 */
	public static function Pattern_Lucky($pattern,$subject){

		$data = $props = [];
		$i = 0;
		foreach($pattern->word as $node){
			$data[$i++]['num']= (int)$node->attributes()['num'];
		}
		$data = $data+ array('2'=>0,'3'=>0); 

		$props['sky'] = $data[0]['num']+$data[1]['num'];			//天格
		$props['owner'] = $data[1]['num']+$data[2]['num'];			//人格
		$props['earth'] = $data[2]['num']+$data[3]['num'];			//地格
		$props['outter'] = $data[0]['num']+$data[3]['num'];			//外格

		$props['total'] = $data[0]['num']+$data[1]['num']+$data[0]['num']+$data[3]['num'];	//总格

		$ren = $props['owner'] % 10;
		$wai = $props['outter'] % 10;
		$tian = $props['sky'] % 10;
		$di = $props['earth'] % 10;
		$gender = 1;	//性别，默认不处理男女

		$node = null;
		$resource_result = self::resource('result');
		switch ($subject){
			case 'success':	//成功运
				@list($node) = $resource_result->xpath("regular[@type='success']/rule[contains(@ren,'$ren')][contains(@tian,'$tian')]");

				break;
			case 'basic':	//基础运
				@list($node) = $resource_result->xpath("regular[@type='basic']/rule[contains(@ren,'$ren')][contains(@di,'$di')]");

				break;
			case 'social':	//社交能力
				@list($node) = $resource_result->xpath("regular[@type='social']/rule[contains(@ren,'$ren')][contains(@tian,'$tian')]");
				break;
			case 'total' :
				$wx_ren = self::WuXing($ren)->name;
				$wx_tian = self::WuXing($tian)->name;
				$wx_di = self::WuXing($di)->name;

				@list($node) = $resource_result->xpath("regular[@type='total']/rule[@tian='$wx_tian'][@ren='$wx_ren'][@di='$wx_di']");

				break;
		}
		
		if (!$node)
			return null;

		$lucky = (int)$node->attributes()['lucky'];
		$desc = (string)$node;
		if ($subnodes = $node->children('if')){
			foreach($subnodes as $cond_node){
				if ($this->rule_test($cond_node)){
					$desc.= (string)$cond_node;
					if ($l =$cond_node->attributes()['lucky'])
						$lucky = (int)$l;
				}
			}
		}

		return array('lucky'=>$lucky,'description'=>$desc );
	}

	/**
	 * 获取各种运气诱导判断
	 */
	public function Lucky($subject){
		if (!is_object(self::$_resource_result))
			self::$_resource_result = simplexml_load_file(self::$_resource_result);

		$ren = $this->owner % 10;
		$wai = $this->outter % 10;
		$tian = $this->sky % 10;
		$di = $this->earth % 10;
		$gender = $this->gender;
		
		$node = null;
		switch ($subject){
			case 'success':	//成功运
				@list($node) = self::$_resource_result->xpath("regular[@type='success']/rule[contains(@ren,'$ren')][contains(@tian,'$tian')]");

				break;
			case 'basic':	//基础运
				@list($node) = self::$_resource_result->xpath("regular[@type='basic']/rule[contains(@ren,'$ren')][contains(@di,'$di')]");

				break;
			case 'social':	//社交能力
				@list($node) = self::$_resource_result->xpath("regular[@type='social']/rule[contains(@ren,'$ren')][contains(@tian,'$tian')]");
				break;
			case 'total' :
				$wx_ren = self::WuXing($ren)->name;
				$wx_tian = self::WuXing($tian)->name;
				$wx_di = self::WuXing($di)->name;

				@list($node) = self::$_resource_result->xpath("regular[@type='total']/rule[@tian='$wx_tian'][@ren='$wx_ren'][@di='$wx_di']");

				break;
		}
		
		if (!$node)
			return null;

		$lucky = (int)$node->attributes()['lucky'];
		$desc = (string)$node;
		if ($subnodes = $node->children('if')){
			foreach($subnodes as $cond_node){
				if ($this->rule_test($cond_node)){
					$desc.= (string)$cond_node;
					if ($l =$cond_node->attributes()['lucky'])
						$lucky = (int)$l;
				}
			}
		}

		return array('lucky'=>$lucky,'description'=>$desc );
	}

	/**
	 * 节点规则判断
	 */
	public function rule_test($cond_node,$data){
		$attrs = $cond_node->attributes();
		foreach($attrs as $k => $v){
			
			if (!isset($attr_data[$k]))
				return false;
			
			$v1 = $data[$k];
			
			$t = explode(',',$v);
			if (!in_array($v1,$t))
				return false;
		}
		
		return true;
	}

	/**
	 * 渲染
	 */
	public function Render($format = 'svg',$file = null){
		
		$outter = self::formatnum($this->outter);
		$sky = self::formatnum($this->sky);
		$earth = self::formatnum($this->earth);
		$owner = self::formatnum($this->owner);
		$total = self::formatnum($this->total);
		
		$word0 = ($this->_data['names'][0]['text']? $this->_data['names'][0]['text']:'　').' '.self::formatnum($this->_data['names'][0]['num']);
		$word1 = $this->_data['names'][1]['text'].' '.self::formatnum($this->_data['names'][1]['num']);
		$word2 = $this->_data['names'][2]['text'].' '.self::formatnum($this->_data['names'][2]['num']);
		$word3 = ($this->_data['names'][3]['text']?$this->_data['names'][3]['text']:'　').' '.self::formatnum($this->_data['names'][3]['num']);
		
		$font = '28';
		$output = '<svg width="240" height="300" viewbox="0 0 400 480">'
		    .'	<line x1="60" y1="60" x2="60" y2="360" style="stroke:rgb(0,0,0);stroke-width:2"/>'
		    .'	<line x1="60" y1="360" x2="220" y2="360" style="stroke:rgb(0,0,0);stroke-width:2"/>'
		    .'	<line x1="200" y1="10" x2="260" y2="10" style="stroke:rgb(0,0,0);stroke-width:2"/>'
		    .'	<line x1="260" y1="10" x2="260" y2="310" style="stroke:rgb(0,0,0);stroke-width:2"/>'
		    .'	<line x1="200" y1="110" x2="260" y2="110" style="stroke:rgb(0,0,0);stroke-width:2"/>'
		    .'	<line x1="200" y1="210" x2="260" y2="210" style="stroke:rgb(0,0,0);stroke-width:2"/>'
		    .'	<line x1="200" y1="310" x2="260" y2="310" style="stroke:rgb(0,0,0);stroke-width:2"/>'
			.'	<text x="80" y="30" style="font-size:'.$font.'px;" >'.$word0.'</text>'
			.'	<text x="80" y="130" style="font-size:'.$font.'px;" >'.$word1.'</text>'
			.'	<text x="80" y="230" style="font-size:'.$font.'px;">'.$word2 .'</text>'
			.'	<text x="80" y="330" style="font-size:'.$font.'px;">'.$word3.'</text>'
			.'	<text id="outter" x="0" y="280" style=font-size:'.$font.'px;">'.$outter.'</text>	<!--外格-->'
			.'	<text id="sky" x="280" y="70" style=font-size:'.$font.'px;">'.$sky.'</text>		<!--天格-->'
			.'	<text id="people" x="280" y="170" style=font-size:'.$font.'px;">'.$owner.'</text>	<!--人格-->'
			.'	<text id="earth" x="280" y="270" style=font-size:'.$font.'px;">'.$earth.'</text>	<!--地格-->'
			.'	<text id="total" x="130" y="410" style=font-size:'.$font.'px;">'.$total.'</text>	<!--总格-->'
			.'</svg>';
	
		return $output;
	}

	/**
	 * 按ID查询指定姓名格局
	 */
	public static function find_pattern($pid){
		$pattern = self::Resource('pattern');
		
		$p = $pattern->xpath("pattern[@id='$pid']");
		return count($p) > 0 ? $p[0]: null;
	}

	/**
	 * 按姓获取所有推荐格局
	 */
	public static function good_patterns($surname){
		$pattern = self::Resource('pattern');
		$word = self::Resource('word');


		$num = self::WordNum($surname);
		$patterns = $pattern->xpath("//word[1][@num='8']/parent::*");
		return $patterns;
	}

	/**
	 * 简单渲染格局
	 */
	public static function Render_Pattern($pattern,$surname = null,&$data = null){
		$words = $pattern->xpath('word');
		$word_nums = array(
			'0'	=> 1,
			'1'	=> $words[0]['num'],
			'2' => $words[1]['num'],
			'3' => $words[2]['num'],
		);
		$outter = $word_nums[0] + $word_nums[3];
		$sky = $word_nums[0] + $word_nums[1]; 
		$earth = $word_nums[2] + $word_nums[3];
		$owner = $word_nums[1] + $word_nums[2];
		$total = $word_nums[0] + $word_nums[1] + $word_nums[2] + $word_nums[3];
		$data = array($sky,$earth,$owner,$total,$outter);
		
		$word0=$word1=$word2=$word3='&nbsp;□&nbsp;';
		if ($surname)
			$word1 = $surname;
		$word0 .=' '.self::formatnum($word_nums[0]);
		$word1 .=' '.self::formatnum($word_nums[1]);
		$word2 .=' '.self::formatnum($word_nums[2]);
		$word3 .=' '.self::formatnum($word_nums[3]);
		
		$outter = self::formatnum($outter);
		$sky = self::formatnum($sky);
		$earth = self::formatnum($earth);
		$owner = self::formatnum($owner);
		$total = self::formatnum($total);

		$output = '<svg width="200" height="300" viewbox="0 0 400 480" >'
		    .'	<line x1="60" y1="60" x2="60" y2="360" style="stroke:rgb(0,0,0);stroke-width:2"/>'
		    .'	<line x1="60" y1="360" x2="220" y2="360" style="stroke:rgb(0,0,0);stroke-width:2"/>'
		    .'	<line x1="200" y1="10" x2="260" y2="10" style="stroke:rgb(0,0,0);stroke-width:2"/>'
		    .'	<line x1="260" y1="10" x2="260" y2="310" style="stroke:rgb(0,0,0);stroke-width:2"/>'
		    .'	<line x1="200" y1="110" x2="260" y2="110" style="stroke:rgb(0,0,0);stroke-width:2"/>'
		    .'	<line x1="200" y1="210" x2="260" y2="210" style="stroke:rgb(0,0,0);stroke-width:2"/>'
		    .'	<line x1="200" y1="310" x2="260" y2="310" style="stroke:rgb(0,0,0);stroke-width:2"/>'
			.'	<text x="80" y="30" style="font-size:22pt;" >'.$word0.'</text>'
			.'	<text x="80" y="130" style="font-size:22pt;" >'.$word1.'</text>'
			.'	<text x="80" y="230" style="font-size:22pt;">'.$word2 .'</text>'
			.'	<text x="80" y="330" style="font-size:22pt;">'.$word3.'</text>'
			.'	<text id="outter" x="0" y="280" style=font-size:22pt;">'.$outter.'</text>	<!--外格-->'
			.'	<text id="sky" x="280" y="70" style=font-size:22pt;">'.$sky.'</text>		<!--天格-->'
			.'	<text id="people" x="280" y="170" style=font-size:22pt;">'.$owner.'</text>	<!--人格-->'
			.'	<text id="earth" x="280" y="270" style=font-size:22pt;">'.$earth.'</text>	<!--地格-->'
			.'	<text id="total" x="130" y="410" style=font-size:22pt;">'.$total.'</text>	<!--总格-->'
			.'</svg>';
	
		return $output;

	}
}

NameDestiny::init();