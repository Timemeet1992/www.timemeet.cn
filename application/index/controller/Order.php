<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use app\index\model\Orders;
use app\index\model\Goods;

class Order extends Controller
{
	public function order()
	{
		$orders = new Orders();
		$goods = new Goods();
		$type = empty($_GET['type'])?'0':$_GET['type'];
		if (empty(Session::get('uid'))) {
			$this->error('请先登录', 'admin/login/login');
		}else{
			$uid = Session::get('uid');
		}
		if ($type == 'all') {
			$result = json_decode(json_encode($orders->where('buyersid',$uid)
				->alias('o')
				->join('phone_goods g','g.gid = o.goodsid')
				->select()));
		} else {
			$result = json_decode(json_encode($orders->where('buyersid',$uid)->where('deal_status',$type)
				->alias('o')
				->join('phone_goods g','g.gid = o.goodsid')
				->select()));
		}
		$res = '';
		foreach ($result as $key => $value) {
			foreach ($value as $keys => $values) {
				if ($keys == 'create_time') {
					//dump($value);
					$values = date('Y-m-d H:i:s',$values);
				}
				$res[$key][$keys] = $values;

			}
		}
		//dump($res);
		//dump(time());
		$this->assign('order',$res);
		$this->assign('type',$type);
		return $this->fetch('order');
	}
	//取消订单
	public function deleteorder()
	{
		$orders = new Orders();
		$oid = $_GET['oid'];

		if (empty(Session::get('uid'))) {
			$this->error('请先登录', 'admin/login/login');
		}else{
			$uid = Session::get('uid');
		}
		$result = $orders->where('buyersid',$uid)->where('oid',$oid)->find();
		if (!$result) {
			$this->error('您要取消的订单不存在','index/order/order');
		}
		if ($result->deal_status == 0) {
			$res = $orders->where('oid',$oid)->delete();
			if ($res) {
				$this->success('取消订单成功',url('index/order/order',['type'=>'all']));
			} else {
				$this->error('取消订单失败',url('index/order/order',['type'=>'all']));
			}
		}else {
			$this->error('该订单无法取消',url('index/order/order',['type'=>'all']));
		}
	}

	//确认订单
	public function uporder()
	{
		$orders = new Orders();
		
		$oid = $_GET['oid'];
		//dump($oid);
		if (empty(Session::get('uid'))) {
			$this->error('请先登录', 'admin/login/login');
		}else{
			$uid = Session::get('uid');
		}
		$result = $orders->where('buyersid',$uid)->where('oid',$oid)->find();
		if (!$result) {
			$this->error('您要确认的订单不存在','index/order/order');
		}
		if ($result->deal_status == 2) {
			$res = $orders->save(['deal_status' => 4],['oid' => $oid]);
			if ($res) {
				$this->success('确认订单成功',url('index/order/order',['type'=>'all']));
			} else {
				$this->error('确认订单失败',url('index/order/order',['type'=>'all']));
			}
		}else {
			$this->error('该订单无法确认',url('index/order/order',['type'=>'all']));
		}	}

}