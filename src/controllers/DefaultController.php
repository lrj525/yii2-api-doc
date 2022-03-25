<?php

namespace badcat\yii2\apidoc\controllers;
use yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * Default controller for the `yii2apidoc` module
 */
class DefaultController extends Controller
{
    public $layout = false;
    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return parent::beforeAction($action);
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {

        $docpath='/apidoc';
        $modules='';
        $docresult=[];


        if(isset($this->module->docpath)){
            $docpath=$this->module->docpath;
        }
        if(empty($modules)){
            $modules=$this->module->modules;
        }
        $docrealpath=rtrim( Yii::getAlias('@webapp/web'.$docpath),'/');

        $create=Yii::$app->request->post("create");
        if($create=="true"){
            $docresult=  self::docCreate($docrealpath,$modules);
        }
        return $this->render('index',['docpath'=>$docpath,'modules'=>$modules,'docresult'=>$docresult,'docrealpath'=>$docrealpath]);
    }


    private function docCreate($docrealpath,$modules){
        self::delDir($docrealpath);
        $result=[];
        $allmodules=Yii::$app->getModules();
        foreach($allmodules as $key=>$val){
            if(in_array($key,$modules)){
                $module=new \ReflectionClass($val['class']);
                $moduleDoc=self::getDoc($module->getDocComment(),'@desc');
                if($moduleDoc[0]=='')
                {
                    $moduleDoc[0]=$key;
                }
                $moduledir=$docrealpath.'/'.$key;
                if(!file_exists($moduledir)){
                    mkdir($moduledir,0777,true);
                }


                $basepath=$val['basePath'];
                 $currmodule=[
                   'name'=>$key,
                   'dir'=>$basepath
                ];
                $controllerPath=Yii::getAlias($basepath).'/controllers';
                $allctls=glob($controllerPath.'/*Controller.php');
                foreach($allctls as $ctl){
                    $currctl=[
                        'apis'=>[]
                    ];
                    $ctlbasename=basename($ctl,'.php');
                    $classname=str_replace('Module','',$val['class']).'controllers\\'.$ctlbasename;

                    $class = new \ReflectionClass($classname);
                    $classDoc=self::getDoc($class->getDocComment(),'@desc');
                    $classpathname=$classDoc[0];
                    if(!$classpathname){
                        $classpathname=$classname;
                    }
                    $currctl['name']=$classpathname;
                    $currctl['class']=$classname;
                    $methods=$class->getMethods(1);
                    foreach($methods as $mk=>$mval){
                        $methodName=$mval->name;
                        if($methodName!='actions'){
                            if (preg_match('/action(.*)/', $methodName, $matchname)){
                                //转 -小写
                                $apiname = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '-$1', $matchname[1]));
                                $methodDocComment=$mval->getDocComment();

                                $methodDoc=self::getDoc($methodDocComment,'@desc');
                                $params=self::getDoc($methodDocComment,'@param');
                                $apipathname=$methodDoc[0];
                                $apidocpath='/'.$key.'/'.strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '-$1',str_replace('Controller','',$ctlbasename))).'/'.$apiname;
                                if(!$apipathname){
                                    $apipathname=$apidocpath;
                                }
                                $currctl['apis'][]=['path'=>$apidocpath,'name'=>$apipathname];
                                self::createMdDoc($methodDocComment,$apidocpath,$moduledir);
                            }

                        }


                    }
                    $currmodule['controllers'][]=$currctl;
                }



                //copy模板文件
                self::copyTemplate($moduledir);
                //设置导航
                self::setNav($moduledir,$moduleDoc,$currmodule['controllers']);

                $result[]=$currmodule;
            }
        }

        return $result;
    }
    private function delDir($dirName){
        if(file_exists($dirName) && $handle=opendir($dirName)){
            while(false!==($item = readdir($handle))){
                if($item!= "." && $item != ".."){
                    if(file_exists($dirName."/".$item) && is_dir($dirName."/".$item)){
                        self::delDir($dirName."/".$item);
                    }else{
                        $file=$dirName."/".$item;
                        unlink($file);
                    }
                }
            }
            closedir($handle);
            rmdir($dirName);
        }
    }

    /**
     * 获取注释内容
     * @param mixed $docall
     */
    private function getDoc($doctxt,$attr){
        preg_match_all("/$attr(.*)/",$doctxt,$methodDoc);
        if($methodDoc&&count($methodDoc)>1){
            $result=$methodDoc[1];
            if(is_array($result)){
                foreach($result as $k=>$d){
                    $result[$k]=trim(str_replace("\n","",str_replace("\r","",$d)));
                }
            }else{
                $result=trim(str_replace("\n","",str_replace("\r","",$result)));
            }
            if(!$result){
                $result[0]='';
            }
            return $result;
        }
        return '';
    }

    private function copyTemplate($moduledir){
        $templatePath= __DIR__.'/../template';
        $handle=opendir($templatePath);
        while(false  !== ($file = readdir($handle))){
            //DIRECTORY_SEPARATOR 为系统的文件夹名称的分隔符 例如：windos为'/'; linux为'/'
            $fileFrom=$templatePath.DIRECTORY_SEPARATOR.$file;
            $fileTo=$moduledir.DIRECTORY_SEPARATOR.$file;
            if($file=='.' || $file=='..'){
                continue;
            }
            if(is_dir($fileFrom)){
                mkdir($fileTo,0777);
                self::copyTemplate($fileFrom,$fileTo);
            }else{
                @copy($fileFrom,$fileTo);

            }
        }
    }
    private function setNav($moduledir,$moduleDoc,$controllers){
        $templatePath= $moduledir.'/index.html';
        $content=file_get_contents($templatePath);
        $content=str_replace('${{modulename}}',$moduleDoc[0],$content);
        $menu='';
        foreach($controllers as $ctl){
            $menu.='<dl class="menu">';
            $menu.='<dt>'.$ctl['name'].'</dt>';
            foreach($ctl['apis'] as $api){
                $path=$api['path'];
                $path=ltrim(str_replace('/','-',$path).'.md','-');
                $menu.='<dd mdpath="'.$path.'">'.$api['name'].'</dd>';
            }
            $menu.='</dl>';
        }
        $content=str_replace('${{moduleapilist}}',$menu,$content);
        file_put_contents($templatePath,$content);
    }

    private function createMdDoc($doccomment,$apidocpath,$moduledir){
        $desc=self::getDoc($doccomment,'@desc')[0];
        $params=self::getDoc($doccomment,'@param');
        $method=self::getDoc($doccomment,'@method')[0];
        $returnEx=self::getDoc($doccomment,'@returnEx')[0];
        $returnParam=self::getDoc($doccomment,'@returnParam');
        $requestEx=self::getDoc($doccomment,'@requestEx')[0];

        $mdcon=self::joinMd("##### 简要描述");
        $mdcon.=self::joinMd("\n- $desc");
        $mdcon.=self::joinMd("\n\n##### 请求URL");
        $mdcon.=self::joinMd("\n- ` $apidocpath `");
        $mdcon.=self::joinMd("\n\n##### 请求方式");
        $mdcon.=self::joinMd("\n- $method");
        $mdcon.=self::joinMd("\n\n##### 参数");
        $mdcon.=self::joinMd("\n|参数名|必选|类型|说明|");
        $mdcon.=self::joinMd("\n|:----    |:---|:----- |-----   |");
        foreach($params as $par){
            $par=ltrim($par,'$');
            $mdcon.=self::joinMd("\n| $par |");
        }

        $mdcon.=self::joinMd("\n\n##### 请求示例");
        $mdcon.=self::joinMd("\n ```json\n$requestEx\n```");

        $mdcon.=self::joinMd("\n\n##### 返回示例");
        $mdcon.=self::joinMd("\n ```json\n$returnEx\n```");

        $mdcon.=self::joinMd("\n\n##### 返回参数说明");
        $mdcon.=self::joinMd("\n|参数名|类型|说明|");
        $mdcon.=self::joinMd("\n|:----    |:----- |-----   |");
        foreach($returnParam as $rpar){
            $rpar=ltrim($rpar,'$');
            $mdcon.=self::joinMd("\n| $rpar |");
        }

        $path=ltrim(str_replace('/','-',$apidocpath).'.md','-');
        file_put_contents($moduledir.'/'.$path,$mdcon);
    }

    private function joinMd($str){
        return $str;

    }
}
