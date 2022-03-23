<?php

namespace badcat\yii2\apidoc\controllers;

use yii\web\Controller;

/**
 * Default controller for the `yii2apidoc` module
 */
class DefaultController extends Controller
{
    public $layout = false;
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        
        return $this->render('index');
    }
}
