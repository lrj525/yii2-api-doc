yii2-api-doc
==================

一个针对yii2 restful API的扩展插件 (https://github.com/lrj525/yii2-api-doc)

在项目中配置该扩展后，通过读取Controller类中的特定信息，自动生成API说明文档，并可在线进行查看。

Installation
------------

首选安装方式 [composer](http://getcomposer.org/download/).

执行

```
composer require  badcat/yii2-api-doc
```

或者把

```json
"badcat/yii2-api-doc": "^1.0"
```

添加到composer.json中，通过composer install 进行安装

# 使用方式

## 1. 项目配置
在配置文件中加入
```
'bootstrap' => [
        'yii2apidoc'
],
'modules'=>[
    'yii2apidoc'=>[
        'class' => 'badcat\yii2\apidoc\Module', //指定加载模块
        'modules' => ['adminapi','xapi'], //要生成api文档的Module名称
        'docpath' => '/apidoc/' //生成文档的存放目录,相对于web下的目录名，不存在则创建，为空时默认为/apidoc。注意重新生成会清空当前目录下的所有文件
    ]
]

```
## 2. 生成文档所需特定信息

### Module类配置

在类注释中使用@desc对Module类进行标注，程序会读取@desc的内容，暂只支持读取一行上的内容。

示例
```
/**
 * @desc 后台接口
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'webapp\modules\adminapi\controllers';

    public function init()
    {
        parent::init();
        Yii::configure(Yii::$app, require(__DIR__.'/../../config/adminapi.php'));
    }
}
```
### Controller类配置

在类注释中使用@desc对Controller类进行标注，程序会读取@desc的内容，暂只支持读取一行上的内容。

示例
```
/**
 * @desc 管理员
 *
 */
class AdminController extends BaseAuthController
{
}
```

### Controller类中的action配置

在Controller类中的action方法中使用以下方法进行标注。


```
/**
     *
     * @desc 添加管理员
     * @method POST
     * @author lrj
     * @param $user_name | 是 | string | 用户名
     * @param $pwd | 是 | string | 密码
     * @requestEx  {"user_name":"用户名","pwd":"密码"}
     * @returnEx {"user_name":"用户名","pwd":"密码"}
     * @returnParam $user_name | string | 用户名
     * @returnParam $pwd | string | 密码
     **/
    public function actionAdd()
    {        
        return $this->result;

    }
```

## 3. 生成文档

把项目使用自己擅长的方式部署后，访问：http://{您的域名}/yii2apidoc/ 打开

## 4. 查看结果