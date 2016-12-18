<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\User;
use app\admin\model\Cla;
use app\admin\model\Set;
use app\admin\model\Closeip;
use app\admin\model\Orders;
use app\admin\model\Goods;
use app\admin\model\News;
use app\admin\model\Addr;
use app\admin\Validate\Register;
use think\Db;
use think\Session;
use traits\model\SoftDelete;
use think\Validate;

class Admin extends Controller
{

    //初始化验证
    public function _initialize()
    {
        $msg = false;
        if(Session::has('uid')&&Session::has('username')&&Session::has('level')){
            $uid = Session::get('uid');
            $var = Session::get('username');
            $level = Session::get('level');
            $result = Db::table('phone_user')->where('uid', $uid)->find();
            if($result['uid'] == $uid && $result['username'] == $var && $result['level'] == $level && $level != 0){
                $msg = true;
            }
        }
        if(!$msg){
            $this->error('请先使用管理员账户登陆', 'admin/login/login');
        }
    }

    //输出后台操作界面页
    public function admin()
    {
        return $this->fetch('admin');
    }

    //输出一个首页
    public function index()
    {
        return $this->fetch('index');
    }

    //输出网站设置页
    public function info()
    {
        $set = new Set();
        $var = json_decode(json_encode($set->where('sid', '>', 0)->select()));
        
        $res = '';
        foreach ($var as $value) {

//      $res .= "\"".$value->setkey."\" => \"".$value->setcontent."\", ";
            $this->assign($value->setkey, $value->setcontent);
        }
        return $this->fetch('info');
    }



    //网站数据插入
    public function doInfo()
    {
        $set = new Set();
        //获取数据
        $Data['stitle'] = empty($_POST['stitle'])?null:$_POST['stitle'];
        //文件上传
        if($file = request()->file('image')){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if(!$info){
                $this->error($file->getError(),'admin/admin/downlist');
            }
            $data['slogo'] = strtr('/uploads/'.$info->getSaveName(), '\\', '/');
        }
        $Data['surl'] = empty($_POST['surl'])?null:$_POST['surl'];
        $Data['sentitle'] = empty($_POST['sentitle'])?null:$_POST['sentitle'];
        $Data['skeywords'] = empty($_POST['skeywords'])?null:$_POST['skeywords'];
        $Data['sdescription'] = empty($_POST['sdescription'])?null:$_POST['sdescription'];
        $Data['s_name'] = empty($_POST['s_name'])?null:$_POST['s_name'];
        $Data['s_phone'] = empty($_POST['s_phone'])?null:$_POST['s_phone'];
        $Data['s_tel'] = empty($_POST['s_tel'])?null:$_POST['s_tel'];
        $Data['s_400'] = empty($_POST['s_400'])?null:$_POST['s_400'];
        $Data['s_fax'] = empty($_POST['s_fax'])?null:$_POST['s_fax'];
        $Data['s_qq'] = empty($_POST['s_qq'])?null:$_POST['s_qq'];
        $Data['s_qqu'] = empty($_POST['s_qqu'])?null:$_POST['s_qqu'];
        $Data['s_email'] = empty($_POST['s_email'])?null:$_POST['s_email'];
        $Data['s_address'] = empty($_POST['s_address'])?null:$_POST['s_address'];
        $Data['scopyright'] = empty($_POST['scopyright'])?null:$_POST['scopyright'];

        $i = 0;
        foreach ($Data as $key => $value) {
            $i++;
            $value = empty($value) ? -1 : $value;
            $set->save([
                'setkey' => $key,
                'setcontent' => $value
                ],['sid' => $i]);
        }

        $this->success('修改网站信息成功！');
    }



    //输出修改密码页
    public function pass()
    {
        return $this->fetch('pass');
    }

    //修改密码
    public function getDoPass()
    {
        //获取数据
        $var = $_POST;
        $data['username'] = empty($var['mname'])?null:$var['mname'];
        $data['password'] = md5(empty($var['mpass'])?null:$var['mpass']);
        //判断用户两次密码输入是否一致
        if(empty($var['newpass'])?null:$var['newpass'] == empty($var['renewpass'])?null:$var['renewpass'] && empty($var['newpass'])?null:$var['newpass'] != null){
            $newpass = $var['newpass'];
        }else{
            $this->error('修改失败', 'admin/admin/pass');
        }
        //查询输入的用户名与密码是否正确
        $user = new User();
        // 查询单个用户数据
        $result = json_decode(json_encode($user->where('username', $data['username'])->where('password', $data['password'])->find()));
        if(!$result){
            $this->error('密码填写错误，修改失败', 'admin/admin/pass');
        }
        //判断信息是否正确并再次验证密码，正确后更新数据库并跳转
        if(empty($result->uid) && $result->password == $data['password']){
            $this->error('密码错误，修改失败', 'admin/admin/pass');
        }else{
            $data['password'] = md5($newpass);
            $user->save([
                'password' => $data['password'],
                ],['uid' => $result->uid]);
            $this->success('修改成功', 'admin/admin/index');
        }
    }   

    //输出一个单页 暂时没有使用到
    public function page()
    {
        return $this->fetch('page');
    }

    //输出首页轮播管理
    public function adv()
    {
        return $this->fetch('adv');
    }

    //输出一个信息页面 暂时没有使用
    public function book()
    {
        return $this->fetch('book');
    }

    //输出一个分类管理页
    public function column()
    {
        $cla = new Cla();
        // 查询全部的数据集
        $result1 = json_decode(json_encode($cla->all()));
        if(empty($result1)){
            $msg = 0;
        }else{
            $msg = 1;
        }

        // //查询全站分类id
        // $result2 = json_decode(json_encode($cla->where('fatherid',0)->select()));
        // if(empty($result2)){
        //     $this->error('没有查询到分类信息', 'admin/admin/column');
        // }
        // //查询商品分类id
        // $result3 = json_decode(json_encode($cla->where('goodsclass',0)->where('fatherid','<>',0)->where('isdisplay','<>',0)->select()));
        // if(empty($result3)){
        //     $this->error('没有查询到分类信息', 'admin/admin/column');
        // }
        //向模版发送数据
        $this->assign('cla', $result1);    
        $this->assign('msg', $msg); 
        // $this->assign('fatherid', $result2);  
        // $this->assign('goodsclass', $result3);
        return $this->fetch('column');
    }

    //添加一个分类
    public function addColumn()
    {
        //获取数据
        $cla = new Cla();
        //dump($_POST);
        $data['fatherid'] = empty($_POST['fid'])?null:$_POST['fid'];
        $data['classname'] = empty($_POST['title'])?null:$_POST['title'];
        $data['goodsclass'] = empty($_POST['gid'])?null:$_POST['gid'];
        $data['isdisplay'] = empty($_POST['isshow'])?1:$_POST['isshow'];
        $result['cid'] = $cla->where('fatherid', 0)->where('goodsclass', 0)->column('cid');
        $k = false;
        //循环判断
        foreach ($result['cid'] as $value) {
            $values = '\''.$value.'\'';
            if($data['fatherid']==$values){
                $k = true;
            }
        }
        if($k){
            $this->error('输入的参数有错误，请重新输入', 'admin/admin/column');
        }
        //插入数据
        $cla->data([
            'fatherid' => $data['fatherid'],
            'classname' => $data['classname'],
            'goodsclass' => $data['goodsclass'],
            'isdisplay' => $data['isdisplay'],
            ]);
        $res = $cla->save();

        //跳转页面
        if($res){
            $this->success('添加成功', 'admin/admin/column') ;
        }else{
            $this->error('添加失败', 'admin/admin/column') ;
        }
    }


    //修改一个分类
    public function alterColumn()
    {
        //获取数据
        $cla = new Cla();
        $data['cid'] = empty($_POST['cid'])?null:$_POST['cid'];
        $data['fatherid'] = empty($_POST['fid'])?null:$_POST['fid'];
        $data['classname'] = empty($_POST['title'])?null:$_POST['title'];
        $data['goodsclass'] = empty($_POST['gid'])?null:$_POST['gid'];
        $data['isdisplay'] = $_POST['isshow'];
        //查询数据
        $result = $cla->where('cid', $data['cid'])->select();

        //判断查询结果
        if(!$result){
            $this->error('您要修改的数据不存在', 'admin/admin/column');
        }
        //更新数据
        $res = $cla->save([
            'fatherid' => $data['fatherid'],
            'classname' => $data['classname'],
            'goodsclass' => $data['goodsclass'],
            'isdisplay' => $data['isdisplay']
            ],['cid' =>  $data['cid']]);

        //跳转页面
        if($res){
            $this->success('修改成功', 'admin/admin/column') ;
        }else{
            $this->error('修改失败', 'admin/admin/column') ;
        }
    }

    //删除一个分类
    public function delColumn()
    {
        //获取id
        $cla = new Cla();
        $cid = $_GET['cid'];
        //判断是否存在
        $result = $cla->where('cid', $cid)->select();
        if(!$result){
            $this->error('您删除的数据不存在', 'admin/admin/column') ;
        }
        //执行删除操作
        $result = $cla->where('cid',$cid)->delete();
        //跳转页面
        if($result){
            $this->success('删除成功', 'admin/admin/column') ;
        }else{
            $this->error('删除失败', 'admin/admin/column') ;
        }
    }


    //输出一个商品管理页
    public function list()
    {
        $cla = new Cla();

        if (Session::get('level')==9) {
            $result = Db::table('phone_goods')   
            ->alias('g')
            ->where('isfall',1)
            ->join('phone_user u','g.sellerid = u.uid')
            ->join('phone_cla c','g.classify = c.cid')
            ->select();
        } else {
            $result = Db::table('phone_goods')
            ->alias('g')
            ->where('isfall',1)
            ->join('phone_user u','g.sellerid = u.uid')
            ->where('u.level',8)
            ->where('u.uid','g.sellerid')
            ->join('phone_cla c','g.classify = c.cid')
            ->select();
        }
        
        foreach ($result as $key => $value) {
            foreach ($value as $keys => $values) {
                if ($keys == 'update_time') {
                    $result[$key][$keys] = date('Y-m-d H:i:s',$values);
                }
                if ($keys == 'fatherid') {
                    $result[$key]['father'] = $cla->where('cid',$values)->select()[0]['classname'];
                }
                if ($keys == 'goodsclass') {
                    $result[$key]['gcs'] = $cla->where('cid',$values)->select()[0]['classname'];
                }
            }
        }
        //dump($result);
        $this->assign('goods', $result);
        return $this->fetch('list');
    }


    //下架管理页
    public function downlist()
    {
        $cla = new Cla();
        if (Session::get('level') ==9) {
            $result = Db::table('phone_goods')   
            ->alias('g')
            ->where('isfall',0)
            ->join('phone_user u','g.sellerid = u.uid')
            ->join('phone_cla c','g.classify = c.cid')
            ->select();
        } else {
            $result = Db::table('phone_goods')   
            ->alias('g')
            ->where('isfall',0)
            ->join('phone_user u','g.sellerid = u.uid')
            ->where('u.level',8)
            ->join('phone_cla c','g.classify = c.cid')
            ->select();
        }
        
        foreach ($result as $key => $value) {
            foreach ($value as $keys => $values) {
                if ($keys == 'update_time') {
                    $result[$key][$keys] = date('Y-m-d H:i:s',$values);
                }
                if ($keys == 'fatherid') {
                    $result[$key]['father'] = $cla->where('cid',$values)->select()[0]['classname'];
                }
                if ($keys == 'goodsclass') {
                    $result[$key]['gcs'] = $cla->where('cid',$values)->select()[0]['classname'];
                }
            }
        }
        //dump($result);
        $this->assign('goods', $result);

        return $this->fetch('downlist');
    }
    //上架、下架操作
    public function dolist()
    {
        $goods = new Goods();
        $gid = $_GET['gid'];
        $result = $goods->where('gid',$gid)->select()[0];
        if (!$result) {
            $this->error('传入的参数错误', 'admin/admin/downlist');
        }
        if ($result->isfall) {
            $goods->save(['isfall' => 0],['gid' => $gid]);
            $this->success('下架商品成功','admin/admin/list');
        }else {
            $goods->save(['isfall' => 1],['gid' => $gid]);
            $this->success('上架商品成功','admin/admin/downlist');
        }
    }
    //删除下架商品
    public function deletedownlist()
    {
        $goods = new Goods();
        $gid = $_GET['gid'];
        $result = $goods->where('gid',$gid)->find();
        if (!$result) {
            $this->error('您删除的商品不存在','admin/admin/downlist');
        }
        $result = $goods->where('gid',$gid)->delete();
        if ($result) {
            $this->success('删除下架商品成功','admin/admin/downlist');
        } else {
            $this->error('删除下架商品失败','admin/admin/downlist');
        }
    }

    //输出一个添加修改商品页
    public function add()
    {
        $goods = new Goods();
        //添加 修改商品页，商品信息展示
        if(!empty($_GET['gid'])){
            $gid = $_GET['gid'];
            $res = $goods->where('gid', $gid)->find();
            if($res){
                $result = $this->alterlist($gid)[0];
            }else{
                $result = null;
            }
        }else{
            $result = null;
        }
        //dump($result);
        $this->assign('goods', $result);
        return $this->fetch('add');
    }

    //添加商品操作
    public function addlist()
    {

        $goods = new Goods;
        $data = $_POST;
        //文件上传
        if($file = request()->file('image')){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if(!$info){
                $this->error($file->getError(),'admin/admin/downlist');
            }
            $data['exhibiturl'] = strtr('/uploads/'.$info->getSaveName(), '\\', '/');
        }

        
        $data['create_time'] = time();
        $data['update_time'] = $data['create_time'];
        $data['isfall'] = 0;
        $data['sellerid'] = $_SESSION['module']['uid'];
        foreach ($data as $key => $value) {
            if(strlen($value)==0){
                continue;
            }
            $plu[$key] = $value;
        }
        $goods->data($plu);
        $res = $goods->save();
        if($res){
            $this->success('添加成功','admin/admin/downlist');
        }else{
            $this->error('添加失败','admin/admin/downlist');
        }

    }

    //修改商品操作
    public function altlist()
    {
        $goods = new Goods;
        $data = $_POST;
        foreach ($data as $key => $value) {
            if ($key == 'update_time') {
                $data[$key] = time();
            }
        }
        foreach ($data as $key => $value) {
            if(strlen($value)==0){
                continue;
            }
            if(in_array($key,['sellerid','classify','create_time','attention'])){
                continue;
            }
            $plu[$key] = $value;
        }

        // save方法第二个参数为更新条件
        $res = $goods->save($plu,['gid' => $data['gid']]);
        $full = $goods->where('gid', $data['gid'])->find()['isfall'];
        if($res){
            if($full){
                $this->success('修改成功','admin/admin/list');
            }else{
                $this->success('修改成功','admin/admin/downlist');
            }
        }else{
            if($full){
                $this->error('修改失败','admin/admin/list');
            }else{
                $this->error('修改失败','admin/admin/downlist');
            }
        }  
    }
    //修改商品页，商品信息展示
    public function alterlist($gid)
    {

        $cla = new Cla();
        $result = Db::table('phone_goods')   
        ->alias('g')
        ->where('gid',$gid)
        ->join('phone_user u','g.sellerid = u.uid')
        ->join('phone_cla c','g.classify = c.cid')
        ->select();
        foreach ($result as $key => $value) {
            foreach ($value as $keys => $values) {
                if ($keys == 'update_time') {
                    $result[$key][$keys] = date('Y-m-d H:i:s',$values);
                }
                if ($keys == 'fatherid') {
                    $result[$key]['father'] = $cla->where('cid',$values)->select()[0]['classname'];
                }
                if ($keys == 'goodsclass') {
                    $result[$key]['gcs'] = $cla->where('cid',$values)->select()[0]['classname'];
                }
                if ($keys == 'create_time') {
                    $result[$key][$keys] = date('Y-m-d H:i:s',$values);
                }
            }
        }
        return $result;
    }


    //输出一个买家服务管理页
    public function sell()
    {
        $news = new News();

        $result = $news->all();

        $this->assign('news', $result);
        return $this->fetch('sell');
    }

    //买家消息同意处理
    public function dosell()
    {
        $news = new News();

        $nid = $_GET['nid'];

        $result = $news->where('nid',$nid)->find();

        $res = Db::table($result->tablename)
        ->where($result->supfield,$result->supposing)
        ->setField($result->field, $result->data);
        
        if($res){
            $news->destroy($nid);
            $this->success('已同意','admin/admin/sell');
        }else{
            $this->error('失败了吆', 'admin/admin/sell');
        }

    }

    //买家消息忽略处理
    public function delsell()
    {
        $news = new News();
        $nid = $_GET['nid'];
        $res = $news->destroy($nid);
        if($res){
            $this->success('删除成功','admin/admin/sell');
        }else{
            $this->error('删除失败', 'admin/admin/sell');
        }



    }

    //卖家服务管理页
    public function buy()
    {
        $addr = new Addr();
        $goods = new Goods();
        $user = new User();
        $orders = new Orders();
        
        if (empty(Session::get('uid'))) {
            $this->error('请先登录', 'admin/login/login');
        }else{
            $uid = Session::get('uid');
        }

        $result = json_decode(json_encode($user->where('uid',$uid)
            ->alias('u')
            ->join('phone_orders o', 'o.sellerid = u.uid')
            ->where('deal_status',1)
            ->join('phone_goods g','g.gid = o.goodsid')
            ->join('phone_addr a','o.mail_addr_id = a.aid')         
            ->select()));
        //dump($result);

        $this->assign('order',$result);
        return $this->fetch('buy');
    }

    //发货
    public function delivergoods()
    {
        $orders = new Orders();
        $oid = $_GET['oid'];
        $res = $orders->where('oid',$oid)->find();
        if (!$res) {
            $this->error('数据未找到','admin/admin/buy');
        }
        $result = $orders->save(['deal_status' => 2],['oid'=>$oid]);
        if ($result) {
            $this->success('发货成功','admin/admin/buy');
        } else {
            $this->error('发货失败', 'admin/admin/buy');
        }       
    }

    //用户等级提升管理页
    public function hoist()
    {
        return $this->fetch('hoist');
    }

    //用户管理页
    public function userlist()
    {
        $user = new User();
        $result = json_decode(json_encode($user->all()));
        if($result){
            $msg = 1;
        }else{
            $msg = 0;
        }
        $this->assign('msg_1', $msg);
        $this->assign('users', $result); 
        return $this->fetch('userlist');
    }

    //用户管理-禁止登陆
    public function banUserlist()
    {
        $user = new User();
        $var = empty($_GET['uid'])?null:$_GET['uid'];

        $res = $user->where('uid',$var)->find();
        if(!$res){
            $this->error('参数传递失败', 'admin/admin/userlist');
        }
        if($res->isban){
            $result = $user->save([
                'isban' => '0',
                ],['uid' => $var]);
            if(!$result){
                $this->error('恢复用户失败', 'admin/admin/userlist');
            }else{
                $this->success('恢复用户成功', 'admin/admin/userlist');
            }
        }else{
            $result = $user->save([
                'isban' => '1',
                ],['uid' => $var]);
            if(!$result){
                $this->error('禁止用户失败', 'admin/admin/userlist');
            }else{
                $this->success('禁止用户成功', 'admin/admin/userlist');
            }
        }
    }

    //用户管理-删除用户
    public function delUserlist()
    {
        $user = new User();
        $var = empty($_GET['uid'])?null:$_GET['uid'];
        //dump($var);
        $res = $user->where('uid',$var)->find();
        if(!$res){
            $this->error('参数传递失败', 'admin/admin/userlist');
        }

        $result = $user->where('uid',$var)->delete();

        if($result){
            $this->error('删除用户成功', 'admin/admin/userlist');
        }else{
            $this->success('删除用户失败', 'admin/admin/userlist');
        }

    }

    //用户管理-搜索用户
    public function soUserlist()
    {
        $user = new User();

        $var1 = $_POST['cid'];
        $var2 = empty($_POST['keywords'])?null:$_POST['keywords'];
        $var3 = empty($_POST['sousuo'])?null:$_POST['sousuo'];
        if($var3!='搜索'){
            $this->error('请点击搜索按钮进行搜索');
        }
        // if(!($var1==0 || empty($var1))){
        //     $this->error('您没有选择搜索类型');
        // }
        if(!$var2){
            $this->error('请输入您要搜索的内容');
        }

        $result = $user->getSoUser($var2,$var1);

        $this->assign('so', 1);
        $this->assign('type', $var1);
        $this->assign('key', $var2);
        $this->assign('sousers', $result);
        return $this->fetch('userlist');

    }

     //禁止ip登陆页
    public function iplist()
    {
        $closeip = new Closeip();
        $result =json_decode(json_encode($closeip->order('pid', 'asc')->select()));

        if (!$result) {
            $msg = 0;
        }else{
            $msg = 1;
        }
        $res = [];
        foreach ($result as $key => $value) {
            $res[$key]['create_time'] = date('Y-m-d H:i:s',$value->create_time);
            $res[$key]['over_time'] = date('Y-m-d H:i:s',$value->over_time);
            $res[$key]['ip'] = long2ip($value->ip);
            $res[$key]['pid'] = $value->pid;
        }
        $this->assign('closeip', $res);
        $this->assign('msg', $msg);
        return $this->fetch('iplist');
    }

    //新增禁止ip
    public function addcloseip()
    {

        $closeip = new Closeip();
        $ip1 = $_POST['ip1new'];
        $ip2 = $_POST['ip2new'];
        $ip3 = $_POST['ip3new'];
        $ip4 = $_POST['ip4new'];
        $time1 = $_POST['validitynew'];

        if((($ip1 > 255) && ($ip2 > 255) && ($ip3 > 255) && ($ip4 > 255)) || (($ip1 < 0) && ($ip3 < 0) && ($ip3 < 0) && ($ip4 < 0))){
            $this->error('您输入的ip不正确', 'admin/admin/iplist');
        }
        $data['ip'] = ip2long($ip1.'.'.$ip2.'.'.$ip3.'.'.$ip4);
        $data['create_time'] = time();
        $data['over_time'] = (time()+($time1*60*60*24));
        $res = $closeip->where('ip',$data['ip'])->find();
        if ($res) {
            if ($res->over_time < $data['create_time']) {
                $r = $closeip->where('pid',$res->pid)->delete();
                if ($r) {
                    $this->error('旧信息删除失败','admin/admin/iplist');
                }
                $res = 1;
            } else {
                $this->error('有效IP已经存在', 'admin/admin/iplist');
            }
        }


        $closeip->data([
            'ip' => $data['ip'],
            'create_time' => $data['create_time'],
            'over_time' => $data['over_time']
            ]);
        $result = $closeip->save();
        if (!$result) {
            $this->error('新增失败', 'admin/admin/iplist');
        }else{
            $this->success('新增成功', 'admin/admin/iplist');
        }


    }
    //删除禁止ip
    public function deletecloseip()
    {
        $closeip = new Closeip();
        dump($_GET);
        $pid = $_GET['pid'];
        $result = $closeip->where('pid', $pid)->select();
        if(!$result){
            $this->error('您删除的数据不存在', 'admin/admin/iplist') ;
        }
        $result = $closeip->where('pid',$pid)->delete();
        if($result){
            $this->success('删除成功', 'admin/admin/iplist') ;
        }else{
            $this->error('删除失败', 'admin/admin/iplist') ;
        }
    }


    //用户黑名单管理页（暂时无用）
    public function userblacklist()
    {
        return $this->fetch('userblacklist');
    }

    //添加用户页
    public function adduser()
    {
        return $this->fetch('adduser');
    }
    //添加用户操作
    public function doadduser()
    {
        $user = new User();

        if($_POST['sub'] != '添加'){
            $this->error("请点击添加按钮添加",'admin/admin/adduser');
        }

        $data = [
        'username' => $_POST['s_username'],
        'password' => $_POST['s_password'],
        ];

        $result = $this->validate($data,'Register');
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result, 'admin/admin/adduser');
        }

        //数据库查询数据
        $result = $user->where('username', $_POST['s_username'])->find();
        if($result){
            $this->error('用户名已存在', 'admin/admin/adduser');
        }
        if($_POST['s_password']!=$_POST['r_password']){
            $this->error('两次密码输入不一致', 'admin/admin/adduser');
        }
        $data = [];

        //获取登录ip与时间
        $data['log_ip'] = ip2long($_SERVER['REMOTE_ADDR']);
        $data['update_time'] = time();
        $data['reg_ip'] = $data['log_ip'];
        $data['create_time'] = $data['update_time'];

        $var = $_POST;
        $data['username'] = $var['s_username'];
        $data['password'] = md5($var['s_password']);
        //文件上传
        if($file = request()->file('image')){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if(!$info){
                $this->error($file->getError(),'admin/admin/downlist');
            }
            $data['avatar'] = strtr('/uploads/'.$info->getSaveName(), '\\', '/');
        }
        $data['realname'] = $var['s_name'];
        $data['phone'] = $var['s_phone'];
        $data['mailaddr'] = $var['s_address'];
        $data['birthday'] = $var['birthday'];
        $data['qq'] = $var['s_qq'];
        $data['email'] = $var['s_email'];
        $data['position'] = $var['province'];
        $data['signature'] = $var['scopyright'];

        foreach ($data as $key => $value) {
            if(strlen($value)==0){
                continue;
            }
            $plu[$key] = $value;
        }

        $user->data($plu);
        $res = $user->save();
        if(!$res){
            $this->error('添加失败', 'admin/admin/adduser');
        }else{
            $this->success('添加成功', 'admin/admin/adduser');
        }
    }

    //显示用户个人资料
    public function userdata()
    {
        $user = new User();
        $uid = empty($_GET['uid'])?null:$_GET['uid'];
        //dump($uid);
        if($uid){
            $result = $user->where('uid',$uid)->find();
        }else{
            //$this->error('参数传递失败', 'admin/admin/userlist');
        }

        if(!$result){
            $this->error('数据查询失败', 'admin/admin/userlist');
        }

        $this->assign('info',$result);
        //dump($result);
        return $this->fetch('userdata');
    }

    //修改用户个人资料
    public function alterUserdata()
    {
        $user = new User();
        $data['uid'] = $_POST['uid'];
        //文件上传
        if($file = request()->file('image')){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if(!$info){
                $this->error($file->getError(),'admin/admin/downlist');
            }
            $data['avatar'] = strtr('/uploads/'.$info->getSaveName(), '\\', '/');
        }
        $data['realname'] = $_POST['realname'];
        $data['phone'] = $_POST['phone'];
        $data['mailaddr'] = $_POST['mailaddr'];
        $data['qq'] = $_POST['qq'];
        $data['email'] = $_POST['email'];
        $data['signature'] = $_POST['signature'];

        $res = $user->save($data,['uid' => $data['uid']]);
        $addr = 'admin/admin/userdata?uid='.$data['uid'];

        if(!$res){
            $this->error('修改失败', 'admin/admin/userlist');
        }else{
            $this->success('修改成功', 'admin/admin/userlist');
        }
    }

    //后台订单页显示
    public function order()
    {
        $level = Session::get('level');
        $userid = Session::get('uid');
        if (!empty($_GET['uid'])) {
            $uid = $_GET['uid'];
            $res = $this->doorder($uid);
        } else {
            $res = $this->allorder($level,$userid);
        }
        $this->assign('orders', $res);
        return $this->fetch('order');
    }
    //查询所有用户所有订单
    public function allorder($level,$userid)
    {
        $orders = new Orders();
        $user = new User();
        $goods = new Goods();
        if ($level==9) {
         $result = json_decode(json_encode($orders->all()));
     } else {
        $result = json_decode(json_encode($orders->where('sellerid',$userid)->select()));
    }

    $res = [];
    foreach ($result as $key => $value) {
        $res[$key]['buyersid'] = $value->buyersid;
        $u_name = $user->where('uid',$res[$key]['buyersid'] )->find();
        if (empty($u_name)) {
            $res[$key]['buyname'] = '当前买家不存在';
        } else {
            $res[$key]['buyname'] = $u_name->username;
        }            
        $res[$key]['sellerid'] = $value->sellerid;
        $u_name = $user->where('uid',$res[$key]['sellerid'] )->find();
        if (empty($u_name)) {
            $res[$key]['sellname'] = '当前卖家不存在';
        } else {
            $res[$key]['sellname'] = $u_name->username;
        }           
        $res[$key]['goodisid'] = $value->goodsid;
        $g_name = $goods->where('gid',$res[$key]['goodisid'] )->find();
        if (empty($u_name)) {
            $res[$key]['goodsname'] = '当前商品不存在';
        } else {
            $res[$key]['goodsname'] = $g_name->goodsname;
        } 
        $res[$key]['create_time'] = date('Y-m-d H:i:s',$value->create_time);
        $res[$key]['price'] = $value->price;
        $res[$key]['amount'] = $value->amount;
        $res[$key]['oid'] = $value->oid;
        $res[$key]['order_number'] = $value->order_number;
        $res[$key]['deal_status'] = $value->deal_status;
        $res[$key]['deal_amount'] = $value->deal_amount;
    }
    return $res;
}
     //查询某个用户所有订单
public function doorder($uid)
{

    $orders = new Orders();
    $user = new User();
    $goods = new Goods();
    $result = json_decode(json_encode($orders->where('buyersid',$uid)->select()));
    $res = [];
    foreach ($result as $key => $value) {
        $res[$key]['buyersid'] = $value->buyersid;
        $u_name = $user->where('uid',$res[$key]['buyersid'] )->find();
        if (empty($u_name)) {
            $res[$key]['buyname'] = '当前买家不存在';
        } else {
            $res[$key]['buyname'] = $u_name->username;
        }            
        $res[$key]['sellerid'] = $value->sellerid;
        $u_name = $user->where('uid',$res[$key]['sellerid'] )->find();
        if (empty($u_name)) {
            $res[$key]['sellname'] = '当前卖家不存在';
        } else {
            $res[$key]['sellname'] = $u_name->username;
        }           
        $res[$key]['goodisid'] = $value->goodsid;
        $g_name = $goods->where('gid',$res[$key]['goodisid'] )->find();
        if (empty($g_name)) {
            $res[$key]['goodsname'] = '当前商品不存在';
        } else {
            $res[$key]['goodsname'] = $g_name->goodsname;
        } 
        $res[$key]['create_time'] = date('Y-m-d H:i:s',$value->create_time);
        $res[$key]['price'] = $value->price;
        $res[$key]['amount'] = $value->amount;
        $res[$key]['oid'] = $value->oid;
        $res[$key]['order_number'] = $value->order_number;
        $res[$key]['deal_status'] = $value->deal_status;
        $res[$key]['deal_amount'] = $value->deal_amount;
    }
    return $res;
}

    //删除订单
public function deleteorder()
{
    $orders = new Orders();

    $oid = $_GET['oid'];
    $result = $orders->where('oid',$oid)->select();
    if (!$result) {
        $this->error('您删除的订单不存在', 'admin/admin/order');
    }
    $result = $orders->where('oid', $oid)->delete();
        //dump($result);
    if (!$result) {
        $this->error('删除订单失败','admin/admin/order');
    } else {
        $this->success('删除订单成功','admin/admin/order');
    }
}

}