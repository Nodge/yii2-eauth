<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace nodge\eauth\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class WidgetAssetBundle extends AssetBundle
{
	public $sourcePath = '@eauth/assets';
	public $css = array(
		'css/eauth.css',
	);
	public $js = array(
		'js/eauth.js',
	);
	public $depends = array(
		'yii\web\JqueryAsset',
	);
}
