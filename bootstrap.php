<?php
/**
 * アプリケーション立ち上げ時の処理。
 * 作成したClassLoaderをオートロードに登録する。
 */

require 'core/ClassLoader.php';

$loader = new ClassLoader();
$loader->registerDir(dirname(__FILE__) . '/core');
$loader->registerDir(dirname(__FILE__) . '/models');
$loader->register();

