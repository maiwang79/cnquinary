<?php 

namespace Quinary\Common;

use Predis\Client;

/**
 * 对象类存储特性
 */
Trait Cachable {

	public function SpecName(){

		$specattrs = static::$specattrs or 'id';
		foreach(explode(',',static::$specattrs)  as $attr){
			if ($this->$attr === null)
				throw new \Exception('spec_attr_error');
			$spec[]=$this->$attr;
		}
		return implode('|',$spec);
	}

	/**
	 * 实例存储键名
	 */
	static function KeyName($specname){
		$type = static::Class();
		return $keyname = Storage::KeyName($type.'_'.$specname);
	}

	/**
	 * 对象写入缓存
	 */
	public function Cache(){
		$class =explode('\\',__CLASS__);
		$class = array_pop($class);

		$specname = $this->SpecName();
		//$code = Common::IDEncode($name=$this->SpecName());
		if (static::$storagelist){
			if (!$token = self::SaveIndex($specname))
				return false;
			$instkeyname = Storage::KeyName($class.'_'.$token);
		}
		else{
			$instkeyname = Storage::KeyName($class.'_'.$specname);
		}

		if (self::SaveObj($instkeyname,$this->StorageData()))
			return $instkeyname;
		return false;
	}

	/**
	 * 用于缓存的数据
	 */
	public function StorageData(){
		$data = [];
		$data['class'] = __CLASS__;
		$attributes = static::$StorageAttributes ?? [];
		foreach($attributes as $attr)
			$data['attributes'][$attr] = $this->$attr;

		$StorageGroups = static::$StorageGroups ?? [];
		foreach ($StorageGroups as $groupname){
			$method = $groupname;
			if (!method_exists($this,$method) || !$group = $this->$method())
				continue;

			$data['groups'][$groupname] = $group;
		}
		return $data;
	}

	/**
	 * 保存一个同类对象的索引
	 */
	static function SaveIndex($specname,$find = 0){
		$class =explode('\\',__CLASS__);
		$class = array_pop($class);

		$list_key = Storage::KeyName($class.'#List');
		
		if ($token = Storage::GetItem($list_key,$specname))
			return $token;

		if ($find)
			return null;

		$token = md5(time().mt_rand(1000,9999));
		if (Storage::SetItem($list_key,$specname,$token))
			return $token;
		else
			return null;
	}

	/**
	 * 保存对象
	 */
	static function SaveObj($keyname,$objData){
		if (!isset($objData['class']))
			return null;

		Storage::SetItem($keyname,'class',$objData['class']);

		foreach($objData['attributes']  as $attribute => $val){
			echo 'set '.$attribute.' on '.$keyname.',';
			Storage::SetItem($keyname,'attr_'.$attribute,$val);
		}

		if(isset($objData['groups']))
			foreach($objData['groups'] as $groupname => $group){
				self::SaveObjGroup($keyname,$groupname,$group);
			}

		return true;
	}

	static function SaveObjGroup($keyname,$groupname,$group){
		$group1 = [];
		foreach($group as $k=>$v){
			if (is_object($v) || is_array($v))
				$v = json_encode($v);
			$group1['group_'.$groupname.'_'.$k] = $v;
		}
		return Storage::SetGroup($keyname,$group1);
	}

	/**
	 * 获取已缓存的数据
	 */
	function CacheData($specname = null){
		if (!$specname && !$specname = $this->SpecName())
			return false;

		$class =explode('\\',__CLASS__);
		$class = array_pop($class);

		$token = self::SaveIndex($specname,1);
		$instkeyname = Storage::KeyName($class.'_'.$token);
		return Redis::HGETALL($instkeyname);
	}

	/**
	 * 恢复数据
	 */
	function RestoreObj($cachedata = null){
		if (!is_array($cachedata)){
			$cachedata = $this->CacheData($cachedata);
		}
		if (!$cachedata && !$cachedata = $this->CacheData())
			return false;
	
		if (!isset($cachedata['class']) || $cachedata['class'] != get_class($this))
			return false;

		foreach($cachedata as $k => $v)
			if (substr($k,0,5) == 'attr_'){
				$attr = substr($k,5);
				//如何区分JSON是否自动转回，未完待续
				$this->$attr = $v;
			}
			elseif (substr($k,0,6) == 'group_'){
				list($groupname,$key) = explode('_',substr($k,6));
				$group_dataname = 'group_'.$groupname;
				if ($x = json_decode($v,1))
					$v = json_decode($v,1);
				$this->$group_dataname[$key] = $x;
			}

		return $this;
	}
}