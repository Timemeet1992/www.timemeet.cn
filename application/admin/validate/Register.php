<?php
namespace app\admin\validate;
use think\Validate;
class Register extends Validate
{
        protected $rule = [
            'username' => 'require|length:3,20',
            'password' => 'require|max:18',
        ];
        protected $message = [
            'username.require' => '名称不能为空',
            'username.length' => '名称要在3-20个字符之间',
            'password.require'=> '密码不能为空',
            'password.max'=> '密码长度不得大于18个字符',
        ];
}