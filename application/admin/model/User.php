<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class User extends Model
{
	public function getSoUser($keyword,$type='all')
	{
		if($type=='all'){
			$result = json_decode(json_encode($this->where('username','like','%'.$keyword.'%')->select()));
		}else{
			$result = json_decode(json_encode($this->where('level', $type)->where('username','like','%'.$keyword.'%')->select()));
		}
		return $result;
	}
}













