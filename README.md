# wobeis小程序商城

#### 目录结构
初始的目录结构如下：
~~~
wwwroot  WEB部署目录（或者子目录）
├─addons                应用插件目录
├─application           应用目录
│  ├─api                api接口模块目录
│  ├─b2c                前台模块
│  ├─common             公共模块目录
│  ├─crontab            定时任务目录
│  ├─job                任务队列目录
│  ├─manage             后台管理目录
│  ├─wechat             接收微信消息目录
│  ├─command.php        命令行工具配置文件
│  ├─common.php         公共函数文件
│  ├─tags.php           应用行为扩展定义文件
│
├─config                配置文件目录
├─public                WEB目录（对外访问目录）
│  ├─install            自动安装目录
│  ├─static             前台静态文件
│  ├─wap                前台手机端运行目录
│  ├─index.php          入口文件
│  └─.htaccess          用于apache的重写
│
├─thinkphp              thinkphp框架系统目录
├─update                版本升级包
├─extend                扩展类库目录
├─runtime               应用的运行时目录（可写，可定制）
├─vendor                第三方类库目录（Composer依赖库）
├─uni-app               前端源码（支持微信小程序、支付宝小程序、APP、公众号、H5端、PC端、抖音小程序、今日头条小程序、皮皮虾小程序、西瓜视频小程序）
├─build.php             自动生成定义文件（参考）
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
├─crontab               定时任务命令行入口文件
~~~


#### 环境要求
- Nginx/Apache/IIS
- PHP7.0-7.2
- MySQL5.5+

建议使用环境：Linux + Nginx1.14 + PHP7.0 + MySQL5.6



#### nginx 伪静态配置，apache请勿使用此配置
~~~
location / {
	if (!-e $request_filename){
		rewrite  ^(.*)$  /index.php?s=$1  last;   break;
	}
}
location /wap/ {
       try_files $uri /wap/index.html;
}

location ^~ /addon{
	deny all;
}
location ^~ /runtime {
	deny all;
}
location ^~ /extend{
	deny all;
}
location ^~ /application {
	deny all;
}
location ^~ /route{
	deny all;
}
location ^~ /thinkphp {
	deny all;
}
location ^~ /vendor {
	deny all;
}
~~~


#### 如果H5中保存图片有跨域问题
nginx中添加以下配置
```
    location ~ .*\.(gif|jpg|jpeg|png)$ {  
      add_header Access-Control-Allow-Origin *;
      add_header Access-Control-Allow-Headers X-Requested-With;
      add_header Access-Control-Allow-Methods GET,POST,OPTIONS;
    }
```


#### Apache 伪静态配置
~~~
    <IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^wap/(.*) /wap/index.html [QSA,PT,L]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond $1 !^(wap)
    RewriteRule ^(.*)$ index.php?s=/$1 [QSA,PT,L]
    </IfModule>
~~~

#### 定时任务配置
- Linux执行Shell命令
```
# 定时取消未支付订单
php think jshop cancle

# 定时催付即将被取消的订单
php think jshop remind

# 定时签收已发货订单
php think jshop sign

# 定时评价已签收订单
php think jshop evaluate

# 定时完成已评价订单
php think jshop complete

# 定时取消拼团失败的订单
php think jshop pintuan_cancle

# 定时清理后台操作日志
php think jshop remove_op_log
```
注意1： **think** 必须指定到项目根目录下的 **think** 文件。  
注意2： Shell命令下的php确保版本在7.0.* ~ 7.3.*之间，其他php版本可能会出现未知错误。  
