<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use app\index\model\Goods;
use app\index\model\Cla;
use app\index\model\Shop;
use app\index\model\User;
use app\index\model\Orders;
class Checkstand extends Controller
{
	//显示支付页
	public function checkstand()
	{

		$order = new Orders();

		if(Session::get('uid')){
			$uid = Session::get('uid');
		}else{
			$this->error('请先登陆','admin/login/login');
		}
		$oid = '';
		if(empty($_GET['oid'])){
			$result = $order->where('buyersid',$uid)->where('deal_status',0)->select();
		}else{
			$oid = $_GET['oid'];
			$result = $order->where('buyersid',$uid)->where('oid',$oid)->where('deal_status',0)->select();
		}
		$time = time();
		$sum = 0;
		$num = 0;
		$order_number = '';
		foreach ($result as $key => $value) {
			$sum += $value->deal_amount;
			$num ++;
			$order_number .= '<br />'.$num.'、'.$value->order_number.'，';
		}

		$this->assign('oid',$oid);
		$this->assign('sum', $sum);
		$this->assign('num', $num);
		$this->assign('order_number', $order_number);
		return $this->fetch('checkstand');
	}	

	//支付操作
	public function docheckstand()
	{
		$order = new Orders();
		$user = new User();

		if(Session::get('uid')){
			$uid = Session::get('uid');
		}else{
			$this->error('请先登陆','admin/login/login');
		}



		$buy = $user->where('uid', $uid)->value('money');
		$buyexp = $user->where('uid', $uid)->value('exp');
		$buydis = $user->where('uid', $uid)->value('vintegral');

		if(empty($_GET['oid'])){
			$result = json_decode(json_encode($order->where('buyersid',$uid)->where('deal_status',0)->alias('o')
			->join('phone_goods g','g.gid = o.goodsid')
			->select()));
		}else{
			$oid = $_GET['oid'];
			$result = json_decode(json_encode($order->where('buyersid',$uid)->where('deal_status',0)->alias('o')
			->join('phone_goods g','g.gid = o.goodsid')->where('oid',$oid)
			->select()));
		}
		


		$time = time();
		$sum = 0;
		$num = 0;
		$order_number = '';
		$list = [];
		$exp = 0;
		$dis = 0;
		$olist = [];
		foreach ($result as $key => $value) {
			$sum += $value->deal_amount;
			$num ++ ;
			$exp +=	$value->exp;
			$dis += $value->integral;
			$order_number .= '<br />'.$num.'、'.$value->order_number.'，';
			$olist[$key] = ['oid' => $value->oid, 'deal_status' => 1];
		}
		//dump($olist);
		$bumoney = $buy - $sum;
		$bumexp = $buydis + $exp;
		$bumdis = $buydis + $dis;
		if($bumoney < 0){
			$this->error('您的余额不足', 'index/index/index');
		}
		$buylist[] = ['uid' => $uid, 'money' => $bumoney, 'exp' => $bumexp, 'vintegral' => $bumdis];
		//dump($buylist);
		foreach ($result as $key => $value) {
			$seller = $user->where('uid', $value->sellerid)->value('money');
			$semoney = $value->deal_amount + $seller;
			$sellerlist[$key] = ['uid' => $value->sellerid, 'money' => $semoney];
			$buyres = $user->saveAll($sellerlist);
			if(empty($buyres)){
				$this->error('支付失败了', 'index/index/index');
			}
		}

		$sellerres = $user->saveAll($buylist);
		$orderres = $order->saveAll($olist);

		
		if(!empty($buyres) && !empty($sellerres) && !empty($orderres)){
			$res = $user->where('uid',$uid)->find();
			Session::set('money',$res['money']);
			Session::set('exp',$res['exp']);
			Session::set('vintegral',$res['vintegral']);
			$this->success('支付成功', 'index/index/index');
		}else{
			$this->error('支付失败', 'index/index/index');
		}

	}
}