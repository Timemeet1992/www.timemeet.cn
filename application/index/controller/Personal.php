<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use app\index\model\User;
use app\index\model\Orders;

class Personal extends Controller
{
	public function personal()
	{
		$user = new User();
		$orders = new  Orders();
		if (empty(Session::get('uid'))) {
			$this->error('请先登录', 'admin/login/login');
		}else{
			$uid = Session::get('uid');
		}
		$result = json_decode(json_encode($user->where('uid',$uid)
			->alias('u')
			->join('orders o','u.uid = o.buyersid')
			->select()));
		$res = '';
		foreach ($result as $key => $value) {
			foreach ($value as $keys => $values) {
				if ($keys == 'create_time') {
					$values = date('Y-m-d H:i:s',$values);
				}
				$res[$key][$keys] = $values;

			}
		}
		//dump($res);
		$this->assign('users', $res);
		return $this->fetch('personal');
	}
}