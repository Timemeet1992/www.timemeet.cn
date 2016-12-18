<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use app\index\model\Goods;
use app\index\model\Cla;
use app\index\model\Shop;
use app\index\model\User;
use app\index\model\Orders;
use app\index\model\Addr;
class Money extends Controller
{
	//显示订单提交页
	public function money()
	{
		$shop = new Shop();
		$goods = new Goods();
		$user = new User();
		$addr = new Addr();

		if(Session::get('uid')){
			$uid = Session::get('uid');
		}else{
			$this->error('请先登陆','admin/login/login');
		}
		$phone = json_decode(json_encode($addr->all()));
		$addrs = json_decode(json_encode($user->where('uid',$uid)
			->alias('u')
			->join('addr a','a.aid = u.mailaddr')
			->select()));
		//dump($phone);
		//dump($addrs);
		$data = empty($_REQUEST)?null:$_REQUEST;
		$str = '';
		$gid = '';
		$num = '';

		if(Session::get('uid')){
			$uid = Session::get('uid');
		}else{
			$this->error('请先登陆','admin/login/login');
		}

		//dump($data);

		if(empty($data['shid']) && empty($data['gid']) && empty($data['num'])){
			$this->error('您选择的商品错误', 'index/index/index');
		}
		if(!empty($data['gid'])){
			$gid = $data['gid'];
			$num = $data['num'];
			$result = json_decode(json_encode($goods->alias('g')->where('g.gid', $gid)->join('phone_cla c','c.cid = g.classify')->select()));
		}else{
			$str = $data['shid'];
			$result = json_decode(json_encode($shop->alias('sh')
				->join('phone_goods g','g.gid = sh.goodsid')
				->join('phone_cla c','c.cid = g.classify')
				->select($str)));
		}

		$res = 0;
		foreach ($result as $key => $value) {
			if($value->discount){
				$res += ($value->price * $value->discount / 10);
			}else{
				$res += $value->price;
			}
			
		}

		$mailaddr = $user->where('uid',$uid)->value('mailaddr');
		$this->assign('gid', $gid);
		$this->assign('num', $num);
		$this->assign('addrid', $mailaddr);
		$this->assign('shid', $str);
		$this->assign('money', $res);
		$this->assign('info', $result);
		$this->assign('addr',$addrs);
		return $this->fetch('money');
	}	

	//下发订单操作
	public function domoney()
	{
		//dump($_POST);
		$cla = new Cla();
		$shop = new Shop();
		$goods = new Goods();
		$user = new User();
		$order = new Orders();

		if(Session::get('uid')){
			$uid = Session::get('uid');
		}else{
			$this->error('请先登陆','admin/login/login');
		}
		//dump($_POST);
		$data['gid'] = empty($_POST['gid'])?null:$_POST['gid'];
		$data['num'] = empty($_POST['num'])?null:$_POST['num'];
		$data['shid'] = empty($_POST['shid'])?null:$_POST['shid'];
		$data['addr'] = empty($_POST['addr'])?null:$_POST['addr'];
		
		if (empty($data['shid'])) {
			$sellerid = $goods->where('gid',$data['gid'])->select();		
		}else {
			$sellerid = $goods->all($data['shid']);
		}
		foreach ($sellerid as $value) {
			if ($value->sellerid == $uid) {
				$this->error('您不能购买自己出售的商品','index/index/index');
			}
		}

		if (!$data['addr']) {
			$this->error('请先添加收货地址','index/address/address');
		}
		$data['order'] = empty($_POST['order'])?null:$_POST['order'];

		if(empty($data)){
			$this->error("您提交信息有错误", url('index/money/money', ['shid' => $data['shid']]));
		}

		if($data['order'] != '下发订单'){
			$this->error("请点击下发订单按钮提交", url('index/money/money', ['shid' => $data['shid']]));
		}

		if(!empty($data['gid'])){
			$result = json_decode(json_encode($goods->alias('g')->where('g.gid', $data['gid'])->join('phone_cla c','c.cid = g.classify')->select()));

		}else{
			//查询商品数据
			$result = json_decode(json_encode($shop->alias('sh')
				->join('phone_goods g','g.gid = sh.goodsid')
				->join('phone_cla c','c.cid = g.classify')
				->select($data['shid'])));			
		}


		if(!$result){
			$this->error("商品不存在", url('index/money/money', ['shid' => $data['shid']]));
		}

		//dump($result);//die;
		$time = time();
		$downtime = $time + (86400*3);
		$arr = [];
		foreach ($result as $key => $value) {
			if(!empty($data['gid'])){
				$order_number = md5($uid.'+'.$data['gid'].'+'.$time);
				$amount = $data['num'];
				if(!empty($value->discount)){
					$deal_amount = $value->price * $data['num'] * $value->discount / 10;
				}else{
					$deal_amount = $value->price * $data['num'];
				}
				$arr[$key] = [
				'buyersid' => $uid,'sellerid' => $value->sellerid,'goodsid' => $value->gid,'create_time' => $time,'update_time' => $time,'deal_amount' => $deal_amount,'mail_addr_id' => $data['addr'],'order_number' => $order_number,'expire_date' => $downtime,'price' => $value->price,'amount' => $amount
				];	
			}else{
				$order_number = md5($uid.'+'.$value->goodsid.'+'.$time);
				$amount = $value->amount;
				if(!empty($value->discount)){
					$deal_amount = $value->price * $value->amount * $value->discount / 10;
				}else{
					$deal_amount = $value->price * $value->amount;
				}
				$arr[$key] = [
				'buyersid' => $uid,'sellerid' => $value->sellerid,'goodsid' => $value->goodsid,'create_time' => $time,'update_time' => $time,'deal_amount' => $deal_amount,'mail_addr_id' => $data['addr'],'order_number' => $order_number,'expire_date' => $downtime,'price' => $value->price,'amount' => $amount
				];
			}
		}


		$arrdel = $order->where('buyersid', $uid)->where('deal_status', 0)->delete();
		//$order->destroy($arrdel);
		$res = $order->saveAll($arr);
		if(!empty($res)){
			if(empty($data['shid'])){
				$shop->destroy($data['gid']);
			}else{
				$shop->destroy($data['shid']);
			}
			
			$this->error('订单下发成功，赶往收银台', url('index/checkstand/checkstand'));
		}else{
			$this->error('下发订单失败', url('index/money/money', ['shid' => $data['shid']]));
		}
	}
}