<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use think\Db;
use app\index\model\Goods;
use app\index\model\Cla;
use app\index\model\Shop;

class Shopcart extends Controller
{
	//显示购物车页面
	public function shopcart()
	{
		$goods = new Goods();
		$shop = new Shop();
		if (Session::get('uid')) {
			$uid = Session::get('uid');
		} else {
			$this->error('请先登录', 'admin/login/login');
		}
		$result = DB::table('phone_shop')
		->alias('s')
		->where('buyer',$uid)
		->join('phone_goods g','s.goodsid = g.gid')
		->select();
		//dump($result);
		$this->assign('goods',$result);
		return $this->fetch('shopcart');
	}

	//生成订单
	public function doshopcart(){
		$shop = new Shop();
		if (Session::get('uid')) {
			$uid = Session::get('uid');
		} else {
			$this->error('请先登录', 'admin/login/login');
		}

		$data['num'] = $_POST['num'];
		$data['shid'] = $_POST['shid'];
		foreach ($data as $key => $value) {
			foreach ($value as $keys => $values) {
				$var[$keys][$key] = $values;
			}
		}

		foreach ($var as $key => $value) {
			if(!empty($value['shid'])){
				$shop->save(['amount' => $value['num']],['shid' => $value['shid']]);
			}
		}

		$str = '';
		foreach ($data['shid'] as $value) {
			$str .= $value.',';
		}
		$str = rtrim($str,',');

		$this->success('订单正在生成，请稍后...',  url('index/money/money', ['shid' => $str]));
	}

	//删除购物车商品
	public function deleteshopcart()
	{
		$shop = new Shop();
		$shid = $_GET['shid'];

		$result = $shop->where('shid',$shid)->find();
		if (!$result) {
			$this->error('商品不存在','index/shopcart/shopcart');
		}

		$res = $shop->where('shid',$shid)->delete();
		if ($res) {
			$this->success('删除成功','index/shopcart/shopcart');
		} else {
			$this->error('删除失败','index/shopcart/shopcart');
		}
	}
}