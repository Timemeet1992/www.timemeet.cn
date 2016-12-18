<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\User;
use think\Session;
class Login extends Controller
{
	//登录界面显示
    public function login()
    {
        return $this->fetch('admin_login');
    }

    //登录操作
    public function doLogin()
    {
    	//取出$_POST的内容
    	$var = empty($_POST) ? null : $_POST;

    	//判断用户名长度
    	if(strlen(empty($var['account']) ? null : $var['account']) >= 3){
    		$data['username'] = $var['account'];
    	}else{
    		$this->error('用户名长度不得低于三位');
    	}

    	//判断密码是否填写
    	if(empty($var['pwd'])){
    		$this->error('请填写密码');
    	}else{
    		$data['password'] = md5($var['pwd']);
    	}

    	//获取当前登录ip与当前时间
    	$data['log_ip'] = ip2long($_SERVER['REMOTE_ADDR']);
    	$data['update_time'] = time();

    	//数据库查询数据
    	$user = new User();
    	$result = $user->where('username', $data['username'])->find();

    	//登录状态写入
    	if(empty($result['username']) || $data['password'] != $result['password']){
    		$this->error('您的用户名和密码有误');
    	}else{
    		//cookie写入
    		if(1 == empty($var['remember']) ? 0 : $var['remember']){
                setcookie("username",$result['username'],time()+3600*24*14);
                setcookie("password",$result['password'],time()+3600*24*14);
            }
    		//session写入
            Session::set('username',$result['username']);
            Session::set('password',$result['password']);
            Session::set('uid',$result['uid']);
            Session::set('update_time',date('Y-m-d H:i:s',$result->update_time));
            Session::set('log_ip',$result['log_ip']);
            Session::set('level',$result['level']);
            Session::set('money',$result['money']);
            Session::set('exp',$result['exp']);
            Session::set('vintegral',$result['vintegral']); 
    		// $_SESSION['username'] = $result['username'];
    		// $_SESSION['password'] = $result['password'];
    		// $_SESSION['uid'] = $result['uid'];
    		// $_SESSION['update_time'] = $result['update_time'];
    		// $_SESSION['log_ip'] = $result['log_ip'];
    		// $_SESSION['level'] = $result['level'];
			//将当前登录时间与登录ip写入数据库
            $user->save(['log_ip' => $data['log_ip'],'update_time' => $data['update_time']],['uid' => $result['uid']]);
			//成功跳转
            $this->success('登录成功', url('index/index/index'));
        }
    }

	//显示注册页面
    public function register()
    {
        return $this->fetch('admin_register');
    }

	//注册操作
    public function doRegister()
    {
    		//验证码验证
        $captcha = empty($_POST['_code']) ? null : $_POST['_code'];
        if(!captcha_check($captcha)){
            $this->error('验证码不正确', 'admin/login/register');
            return false;
        };		

    		//用户名验证
        $user = new User();

        $data['username'] = empty(trim($_POST['account'])) ? null : trim($_POST['account']);
        if($data['username']){
            $result = $user->where('username', $data['username'])->find();
        }else{
            $this->error('数据不得为空', 'admin/login/register');
        }


        if(!empty($result['username']) || $result['username']==$data['username']){
          $this->error('用户名重复了，请重新填写用户名', 'admin/login/register');
          return false;
      }

    		// $data['password'] = empty($_POST['pwd']) ? null : $_POST['pwd'];
    		// $data['email'] = empty($_POST['_email']) ? null : $_POST['_email'];
      $data['create_time'] = time();
    		// $data['update_time'] = $data['create_time'];
      $data['reg_ip'] = ip2long($_SERVER['REMOTE_ADDR']);
    		// $data['log_ip'] = $data['reg_ip'];

    		// 整理数据并插入数据库
      $user->data([
         'username' => $data['username'],
         'password' => md5(empty($_POST['pwd']) ? null : $_POST['pwd']),
         'create_time' => $data['create_time'],
         'update_time' => $data['create_time'],
         'reg_ip' => $data['reg_ip'],
         'log_ip' => $data['reg_ip'],
         'email' => empty($_POST['_email']) ? null : $_POST['_email'],
         ]);
      $result = $user->save();

      if($result == 1){
          $result = $user->where('username', $data['username'])->find();  
          Session::set('username',$result['username']);
          Session::set('password',$result['password']);
          Session::set('uid',$result['uid']);
          Session::set('update_time',$result['update_time']);
          Session::set('log_ip',$result['log_ip']);
          Session::set('level',$result['level']); 
          Session::set('money',$result['money']);
          Session::set('exp',$result['exp']);
          Session::set('vintegral',$result['vintegral']);	
        		// $_SESSION['username'] = $result['username'];
        		// $_SESSION['password'] = $result['password'];
        		// $_SESSION['uid'] = $result['uid'];
        		// $_SESSION['update_time'] = $result['update_time'];
        		// $_SESSION['log_ip'] = $result['log_ip'];
        		// $_SESSION['level'] = $result['level'];
          $this->success('注册成功','index/index/index');
      }else{
         $this->error('注册失败', 'admin/login/register');
     }
 }

        //退出登录
 public function dologout()
 {
    $data['username'] = Session::pull('username');
    $data['password'] = Session::pull('password');
    $data['uid'] = Session::pull('uid');
    $data['update_time'] = Session::pull('update_time');
    $data['log_ip'] = Session::pull('log_ip');
    $data['level'] = Session::pull('level');
    Session::clear();

    $this->success('退出成功','index/index/index');
}
public function email()
{
    return $this->fetch('email');
}
public function doemail()
{
    $user = new User();
    $data['username'] = $_POST['username'];
    $email = $_POST['email'];
    $result = $user->where('username',$data['username'])->select();
    if (!$result) {
        $this->error('用户名不存在','admin/login/email');
    }
    //dump($data);
    $subject = 'only账号服务中心';
    //dump($email);
    $key = md5(time());
    $user->save(['key'=>$key],['username'=>$data['username']]);
    $passpath = 'http://www.timemeet.cn/admin/login/findpass?key='.$key;

    $con = '<style class="fox_global_style"> div.fox_html_content { line-height: 1.5;} /* 一些默认样式 */ blockquote { margin-Top: 0px; margin-Bottom: 0px; margin-Left: 0.5em } ol, ul { margin-Top: 0px; margin-Bottom: 0px; list-style-position: inside; } p { margin-Top: 0px; margin-Bottom: 0px } </style><table style="-webkit-font-smoothing: antialiased;font-family:"微软雅黑", "Helvetica Neue", sans-serif, SimHei;padding:35px 50px;margin: 25px auto; background:rgb(247,246, 242); border-radius:5px" border="0" cellspacing="0" cellpadding="0" width="640" align="center"> <tbody> <tr> <td style="color:#000;"> </td> </tr> <tr><td style="padding:0 20px"><hr style="border:none;border-top:1px solid #ccc;"></td></tr> <tr> <td style="padding: 20px 20px 20px 20px;"> Hi 你好 </td> </tr> <tr> <td valign="middle" style="line-height:24px;padding: 15px 20px;"> 感谢您注册phpbryant <br> 请点击以下链接修改您的密码： </td> </tr> <tr> <td style="height: 50px;color: white;" valign="middle"> <div style="padding:10px 20px;border-radius:5px;background: rgb(64, 69, 77);margin-left:20px;margin-right:20px"> <a style="word-break:break-all;line-height:23px;color:white;font-size:15px;text-decoration:none;" href="'.$passpath.'">'.$passpath.'</a> </div> </td> </tr> <tr> <td style="padding: 20px 20px 20px 20px"> 请勿回复此邮件，如果有疑问，请联系我们：<a style="color:#5083c0;text-decoration:none" href="605506740@qq.com">605506740@qq.com
</a> </td> </tr><tr> <td style="padding: 20px 20px 20px 20px"> 交流群：000000 </td> </tr> <tr> <td style="padding: 20px 20px 20px 20px"> - phpbryant 团队-帮助你更快的完成项目- phpbryant.com </td> </tr> </tbody> </table>';
$status = send($email,$subject,$con);
// dump($status);
// die;
if($status){
    $this->success('发送成功');
}else{
    $this->error('发送失败');
}

}
public function findpass()
{
    $user = new User();
    $key = $_GET['key'];

    $result = $user->where('key',$key)->value('uid');
    if (!$result) {
        $this->error('链接失效','index/index/index');
    }
    $user->save(['key' => null],['uid' => $result]);
    $this->assign('uid',$result);
    return $this->fetch('findpass');
}
public function dofindpass()
{
    $user = new User();
    $uid = $_POST['uid'];
    $data['password'] = $_POST['pwd'];
    $data['repassword'] = $_POST['repwd'];
    if ($data['password'] != $data['repassword']) {
        $this->error('两次密码输入不一致，重新输入','admin/login/findpass');
    }
    $result = $user->save(['password' => md5($data['password'])],['uid' => $uid]);
    if ($result) {
        $this->success('修改成功','admin/login/login');
    } else {
        $this->error('修改失败','admin/login/login');
    }
}

}
