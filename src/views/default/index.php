<html>
<head>
    <title>Yii2 API DOC</title>
    <style>
        .clearfix:after { content: ''; height: 0; line-height: 0; display: block; visibility: hidden; clear: both; }
        .clearfix { zoom: 1; }

        .yii2apidoc{padding:50px;}
        .yii2apidoc .block{padding:15px;background-color:#f2f2f2;}
        .yii2apidoc .title{font-weight:bold;}
        
        .yii2apidoc .create-btn { border: 1px #ccc solid; background-color: #28a745; border-radius: 5px; padding: 8px 15px;color:#fff; }
        .yii2apidoc .result { padding: 15px; margin-top: 30px; background-color: #ffffe0; }
        .yii2apidoc dl.apilist { padding: 15px; border: 1px #ccc solid; }
        .yii2apidoc .apilist dt{ font-weight:bold; line-height:24px;margin:0;}
        .yii2apidoc .apilist dd {  line-height:24px;padding-left:30px;margin:0;}
        .yii2apidoc .module-block{}
        .yii2apidoc .dir{color:#ccc;}
    </style>
</head>


<body>
    <div class="yii2apidoc">
        <div class="clearfix">
            <h1>API文档生成</h1>
            <form action="" method="post" >
                <input name="create" value="true" type="hidden" />
                <button type="submit" class="create-btn">生成文档</button>
                
            </form>
        </div>
        <p class="title">
            存储目录：
        </p>
        <div class="block">
            <?= $docpath ?>(<?= $docrealpath ?>)
        </div>
   
        <p class="title"> Module范围：</p>
        <div class="block">
            <?php foreach($modules as $ctl ):  ?>
            <p>
                <?= $ctl ?>

            </p>
            <?php  endforeach; ?>
        </div>
        <?php if($docresult): ?>
        <div class="result">
            <h1>生成结果：</h1>         
            <?php foreach($docresult as $module): ?>
            <div class="module-block">
                <h2>Module：<?= $module['name'] ?> <span class="dir">[<?= $module['dir'] ?>]</span></h2>
                <?php  foreach($module['controllers'] as $controller): ?>
                    <dl class="apilist">
                        <dt>Controller：<?= $controller['class'] ?></dt>
                        <?php foreach($controller['apis'] as $api): ?>
                            <dd><?= $api['path'] ?></dd>
                        <?php endforeach; ?>
                    </dl>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>