<?php

namespace Quinary\Common;

use Predis\Client;

/**
 * Redis²Ù×÷·â×°
 */
class Redis {

	static $REDIS_CLIENT;

	static function Redis(){
		if (!self::$REDIS_CLIENT){
			$host = config('redis.host');
			$port = config('redis.port');
			$database = config('redis.database');
			self::$REDIS_CLIENT = new Client(compact(['host','port','database']));
		}
		return self::$REDIS_CLIENT;
	}	

	static function __callStatic($method,$params = []){
		$method = strtoupper($method);
		$redis = self::Redis();
		//if (method_exists($redis = self::Redis(),$method))
			return call_user_func_array([$redis,$method],$params);
		//else
		//	throw new \Exception('invalid_redis_method '.$method);
	}
	
	
}
?>