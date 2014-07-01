<?php

//---修改本文件请务必小心!并做好相应备�?--
/*
配置说明:

SITE_URL        :   网站访问地址, 当您发生访问地址修改时修�? 必须带http://, 不要在末尾添�?/'

DB_CONFIG       :   数据库访问配�?协议://用户�?密码@数据库服务器地址:端口/数据库名)

DB_PREFIX       :   数据库表名前缀

LANG            :   字符集与语言

COOKIE_DOMAIN   :   网站Cookie作用�?

COOKIE_PATH     :   网站Cookie作用路径

ECM_KEY         :   网站密钥

MALL_SITE_ID    :   网站ID, 不可修改

ENABLED_GZIP    :   GZIP开�?开启GZIP后将提升用户的访问速度, 相应地服务器的开销将增�?1为开�?0为关�?

DEBUG_MODE      :   0: 生成缓存文件,不强制编译模�?1: 不生成缓存文�?不强制编译模�? 2: 生成缓存文件, 强制编译模板. 3: 不生成缓存文�? 强制编译模版. 4: 生成缓存, 编译模版但不生成编译文件. 5: 不生成缓�? 编译模版但不生成编译文件.

CACHE_SERVER    :   数据缓存服务�?可以是default(php文件缓存),也可以是memcached
CACHE_MEMCACHED : 存储缓存数据的memcached服务�?服务器地址1:端口1)

MEMBER_TYPE     :   可选�? default(使用内置的用户系�?,uc(使用UCenter做为用户系统), 也可以是任意的第三方系统, 前提是您做好了相关的扩展程序:)

ENABLED_SUBDOMAIN : 二级域名功能开�?0为关�?1为开�?开启时必须配置SUBDOMAIN_SUFFIX.二级域名功能开启方法请查看安装包中docs目录下的二级域名配置相关文档.

SUBDOMAIN_SUFFIX : 二级域名后缀,例如:用户的二级域名将�?test.mall.example.com", 则您只需要在此填�?mall.example.com".

SESSION_TYPE     : session数据存储类型，目前可选择session和mysql
SESSION_MEMCACHED : 存储session数据的memcached服务�?服务器地址1:端口1|服务器地址2:端口2)
*/

return array (
  'SITE_URL' => 'http://www.jrjlife.com',
  'DB_CONFIG' => 'mysql://root:@127.0.0.1/jrshop',
  'DB_PREFIX' => 'ecm_',
  'LANG' => 'sc-utf-8',
  'COOKIE_DOMAIN' => '',
  'COOKIE_PATH' => '/',
  'ECM_KEY' => 'b465ecdc33a54e3dadb9a69e09412ae8',
  'MALL_SITE_ID' => 'EMOEYBcpe7Tt9C66',
  'ENABLED_GZIP' => 0,
  'DEBUG_MODE' => 0,
  'CACHE_SERVER' => 'default',
  'MEMBER_TYPE' => 'default',
  'ENABLED_SUBDOMAIN' => 0,
  'SUBDOMAIN_SUFFIX' => '',
  'SESSION_TYPE' => 'mysql',
  'SESSION_MEMCACHED' => '192.168.0.169:11211',
  'CACHE_MEMCACHED' => '192.168.0.169:11211',
);

?>
