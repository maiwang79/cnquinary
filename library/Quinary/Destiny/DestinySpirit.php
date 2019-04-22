<?php

namespace Quinary\Destiny;

/**
 * 命理神煞类
 */

class DestinySpirit {

	private static $_RESOURCE = [];

	private $_node;
	private $_code;
	private $_name;



	private function __CONSTRUCT($node){
		$this->_node = $node[0];
		$this->_code = (string)$node[0]->attributes()['code'];
		$this->_name = (string)$node[0]->attributes()['name'];
	}

	private static function _load(){
		$resource = 'destiny_spirits';
		
		if (!$file = Kohana::find_file('resource',$resource,'xml'))
			die('resource_lose');
		
		self::$_RESOURCE = simplexml_load_file($file);
	}

	public static function Resource(){
		if (empty(SELF::$_RESOURCE))
			self::_load();
		
		return SELF::$_RESOURCE;
	}

	/**
	 * 获取一种神煞实例
	 */
	public static function Instance($code){
		$resource = self::Resource();
		$record = $resource->xpath("/root/spirit[@code=$code]");

		return new self($record);
	}

	/**
	 * 神煞名称
	 */
	public static function Name($code){
		$resource = self::Resource();
		$record = $resource->xpath("/root/spirit[@code=$code]");

		return $record?(string)$record['name']:null;
	}

	/**
	 * 查单项神煞
	 */
	public function process($destiny){
		$spirit = $this->_node;
		switch((string)$spirit['type']){
			case 1:
				$found = self::_process_type1($destiny,$spirit);
				break;
			case 2:
				$found = self::_process_type2($destiny,$spirit);
				break;
			case 3:
				$found = self::_process_type3($destiny,$spirit);
				break;
			default:
		}
		return $found;
	}

	/**
	 * 神煞矩阵
	 * 列出命局及包括行运流年中可能遇到的所有神煞，以干支为索引
	 */
	public static function Spirits_Matrix($source){
		$resource = self::Resource();

		$result=[];
		foreach($resource->spirit as $spirit){
			switch((string)$spirit['type']){
				case 1:
					$found = self::_process_type1($destiny,$spirit);
					break;
				case 2:
					$found = self::_process_type2($destiny,$spirit);
					break;
				case 3:
					$found = self::_process_type3($destiny,$spirit);
					break;
				default:
			}
			$result[(string)$spirit['code']] = $found;
		}

		return $result;
	}

	/**
	 * 排神煞
	 */
	public static function Process_All(Destiny $destiny){
		$resource = self::Resource();

		$result=[];
		foreach($resource->spirit as $spirit){
			$found = null;
			$attrs = $spirit->attributes();

			switch((string)$spirit['type']){
				case 1:
					$found = self::_process_type1($destiny,$spirit);
					break;
				case 2:
					$found = self::_process_type2($destiny,$spirit);
					break;
				case 3:
					$found = self::_process_type3($destiny,$spirit);
					break;
				default:
			}
			$result[(string)$spirit['code']] = $found;
		}

		return $result;
	}
	
	/**
	 * 查类别一神煞
	 * 天干查支
	 */
	private static function _process_type1($destiny,$spirit){
		
		$sourcepos = ['yg','mg'];
		$destpos = ['yz','mz','dz','hz'];
		
		$found = [];
		foreach($sourcepos as $p){
			$source = $destiny->$p;
			if (!$source)
				continue;

			$dests = [];
			foreach($destpos as $dp){
				$dests[$dp] = $destiny->$dp->index;
			}

			$rule = $spirit->xpath('rules/rule[source/@val='.intval($source->index).']')[0];
			if (!$rule)
				continue;
			
			$val = $rule->dest->attributes()['val'];
			$vals = explode('|',$val);
			
			foreach($dests as $dp => $dv)
				if (in_array($dv,$vals)) {
					$destiny->AddSpirit($p,$dp,$spirit);
					$found[$dp] = $dv;
				}
		}
	}

	/**
	 * 查类别一神煞
	 * 月支查干支
	 */
	private static function _process_type2($destiny,$spirit){
		

		$sourcepos = ['mz'];
		$destpos = ['yg','yz','mg','mz','dg','dz','hg','hz'];
		
		$found = [];
		foreach($sourcepos as $p){
			$source = $destiny->$p;
			if (!$source)
				continue;

			$dests = [];
			foreach($destpos as $dp){
				$dests[$dp] = (substr($dp,1,1) == 'z'?'z':'').$destiny->$dp->index;
			}

			$rule = $spirit->xpath('rules/rule[source/@val='.$source->index.']')[0];
			if (!$rule)
				continue;	
			
			$val = $rule->dest->attributes()['val'];
			$vals = explode('|',$val);
			
			foreach($dests as $dp => $dv)
				if (in_array($dv,$vals)) {
					if ($p == $dp)
						continue;
					$destiny->AddSpirit($p,$dp,$spirit);
					$found[$dp] = $dv;
				}
		}

		return $found;
	}
	
	/**
	 * 查类别一神煞
	 * 年/日支查支
	 */
	private static function _process_type3($destiny,$spirit){

		$sourcepos = ['yz','dz'];
		$destpos = ['yz','mz','dz','hz'];

		$dests = [];
		foreach($destpos as $dp){
			if (!is_object( $destiny->$dp))	{
				print_r($destiny->year);
				print_r($destiny->$dp);exit($dp);
			}
			$dests[$dp] = $destiny->$dp->index;
		}

		$found = [];
		foreach($sourcepos as $p){
			$source = $destiny->$p;
			if (!$source)
				continue;

			$rule = $spirit->xpath('rules/rule[source/@val='.$source->index.']')[0];
			if (!$rule){
				continue;
			}

			$val = $rule->dest->attributes()['val'];
			$vals = explode('|',$val);

			foreach($dests as $dp => $dv){
				if (in_array($dv,$vals)) {
					if ($p == $dp)
						continue;
					$destiny->AddSpirit($p,$dp,$spirit);
					$found[$dp] = $dv;
				}
			}
		}

		return $found;
	}

	public function __toString(){
		return $this->_name;
	}
}