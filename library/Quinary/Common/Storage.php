<?php 

namespace Quinary\Common;

use Predis\Client;

/**
 * 定义存储方法
 */
Trait Storage {
	
	static $PREFIX = 'esy_';

	protected $_name = '';
	protected $_data = [];

	function Name(){
		return $this->_name;
	}

	static function Class(){
		$type = static::class;
		$ary = explode('\\',$type);
		return array_pop($ary);
	}


	function ToArray(){
		return $this->_data;	
	}

	function __CALL($func,$params = []){
		$type = get_class($this);
		call_user_func([$this,$func],['type'=>$type]+$params);
	}

	function __GET($prop) {
		if (isset($this->_data[$prop]))
			return $this->_data[$prop];

		return parent::__GET($prop);
	}

	protected function Empty(){
		return new self();
	}

	/**
	 * 恢复
	 */
	protected function Restore(array $data){
		$this->_name = $name;
		$this->_data = $data;

		$obj = self::Empty();

		foreach($data['attributes'] as $attr => $val)
			$this->_data[$attr] = $val;

		foreach($data['groups'] as $groupname => $group){
			$prefix = strlen($groupname.'_');
			$json = '{';
			$gdata = [];
			foreach($group as $key=>$name){
				$key = substr($key,$prefix);
				eval('\\$gdata['.str_replace('_','][',$key).'] = \\$name');
			}
			$this->{'_'.$groupname} = $gdata;
		}

		//为子类提供定制接口提取特定数据
		if (method_exists('_parse_attr',$this)){
			$this->_parse_attr($data);
		}

		return $this;
	}

	/**
	 * 检索并获取实例
	 */
	static function Fetch($name, $isname = false, $returnobj = false){
		$type = static::Class();
		if ($isname){
			$code = Common::IDEncode($name);
		}
		else{
			$code = $name;
			$name = Common::IDDecode($code);
		}
		$keyname = self::KeyName($type.'_list');
		$instkeyname = 	self::InstKeyName($name); 
		if (!$name = Redis::HGet($keyname,$code))
			return false;

		if (!Redis::Exists($instkeyname))
			return false;

		$data = Redis::HGetAll($instkeyname);

		if (!$returnobj)
			return $data;

		$obj = static::Empty();
		$obj->Restore($data);

		return $obj;
	}

	/**
	 * 获取存储键名
	 */
	static function KeyName($var){
		return self::$PREFIX.strtolower($var);
	}

	/**
	 * 实例存储键名
	 */
	static function InstKeyName($name){
		$class = static::Class();
		//$code = Common::IDEncode($name);
		return $keyname = self::KeyName($class.'_'.$code);		
	}

	/**
	 * 获取属性/元素值
	 */
	static function GetItem($instkeyname,$attr){
		return Redis::HGet($instkeyname,$attr);
	}

	/**
	 * 设置属性/元素值
	 */
	static function SetItem($instkeyname,$attr,$value){
		if (is_array($value) || is_object($value))
			$value = json_encode($value);
		return Redis::HSet($instkeyname,$attr,$value);
	}

	/**
	 * 设置成组数据
	 */
	static function SetGroup($instkeyname,$group,$clear = false){
		if ($clear){
			//清除新数据中不包括的旧键值，未完待续
		}
		return Redis::HMSet($instkeyname,$group);
	}



	/**
	 * 向列表添加项目
	 */
	static function AddListItem($value){
		$type = static::Class();
		$key = Common::IDEncode($value);
		$keyname = self::KeyName($type.'_list');
		Redis::HSet($keyname,$key,$value);
		$instkeyname = Self::KeyName($type.'_'.$key);
		Redis::HSet($instkeyname,'name',$value);
		return self::Fetch($key,0,1);
	}
	
	/**
	 * 获取所有项目
	 */
	static function AllItems(){
		$type = static::Class();
		$keyname = self::KeyName($type.'_list');
		return Redis::HGetAll($keyname);
	}
	
	/**
	 * 从列表中删除项目
	 */
	static function DropItem($key){
		$type = static::Class();		
		$keyname = self::KeyName($type.'_list');
		Redis::HDel($keyname,$key);
		return true;
	}
	
	/**
	 * 保存实例数据
	 */
	static function SaveData($keyname,$data){

		foreach($data as $k => $value){
			if (is_array($value))
				$value = json_encode($value);
			Redis::hset($keyname,$k,$value);
		}

		return $keyname;
	}

	/**
	 * 保存对象
	 */
	static function SaveObj($keyname,$objData){
		if (!isset($objData['class']))
			return null;

		self::SaveItem($keyname,'class',$objData['class']);

		foreach($objData['attributes']  as $attribute => $val)
			self::SaveItem($keyname,$attribute,$val);

		if(isset($objData['groups']))
			foreach($objData['groups'] as $groupname => $group){
				self::SaveObjGroup($keyname,$groupname,$group);
			}
		
		return true;
	}

	static function SaveObjGroup($keyname,$groupname,$group){
		$group1 = [];
		foreach($group as $k=>$v){
			if (is_object() || is_array($v))
				$v = json_encode($v);
			$group1['group_'.$groupname.'_'.$k] = $v;
		}
		Redis::hmset($keyname,$group1);
	}

	static function SaveObjAttr($keyname,$attr,$val){
		Redis::hset($keyname,'attr_'.$attr,$val);
	}

	/**
	 * 默认Redis对象化存储数据
	 */
	function StorageData(){
		$data = [];
		$data['class'] = get_class($this);
		$data['attributes'] = $this->StorageAttributes ?? [];

		$StorageGroups = $this->StorageGroups() ?? [];
		foreach ($StorageGroups as $groupname){
			$group = $this->StorageGroupData($groupname);
			if (empty($group))
				continue;
			$data['groups'][$groupname] = $group;
		}
		return $data;
	}

	/**
	 * 默认方法，子类可自定义
	 */
	function StorageGroupData($groupname){
		if (isset($this->_data[$groupname]) && is_array($this->_data[$groupname]))
			return $this->_data[$groupname];

		return [];
	}

}