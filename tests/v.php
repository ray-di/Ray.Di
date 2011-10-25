<?php
/**
 * BEAR
 *
 * PHP versions 5
 *
 * @category  BEAR
 * @package
 * @author    Akihito Koriyama <koriyama@bear-project.net>
 * @copyright 2008-2011 Akihito Koriyama  All rights reserved.
 * @license   http://opensource.org/licenses/bsd-license.php BSD
 * @version   SVN: Release: @package_version@ $Id:$ v.php 416 2011-05-26 00:37:25Z koriyama@bear-project.net $
 * @link      http://www.bear-project.net/
 */

/**
 * BEAR
 *
 * @category  BEAR
 * @package
 * @author    Akihito Koriyama <koriyama@bear-project.net>
 * @copyright 2008-2011 Akihito Koriyama  All rights reserved.
 * @license   http://opensource.org/licenses/bsd-license.php BSD
 * @version   Release: @package_version@
 * @link      http://www.bear-project.net/
 */
/**
 * Debug print 'p'
 *
 * @param mixed  $values    any values
 *
 * @return void
 */
function v($values = null)
{
    static $paramNum = 0;

    // be recursive
    $args = func_get_args();
    if (count($args) > 1) {
        foreach ($args as $arg) {
            p($arg);
        }
    }
    $trace = debug_backtrace();
    $i = ($trace[0]['file'] === __FILE__ ) ? 1 : 0;
    $file = $trace[$i]['file'];
    $line = $trace[$i]['line'];
    $includePath = explode(":", get_include_path());
    // remove if include_path exists
    foreach ($includePath as $var) {
        if ($var != '.') {
            $file = str_replace("{$var}/", '', $file);
        }
    }
    $method = (isset($trace[1]['class'])) ? " ({$trace[1]['class']}" . '::' . "{$trace[1]['function']})" : '';
    $fileArray = file($file, FILE_USE_INCLUDE_PATH);
    $p = trim($fileArray[$line - 1]);
    unset($fileArray);
    $funcName = __FUNCTION__;
    preg_match("/{$funcName}\((.+)[\s,\)]/is", $p, $matches);
    $varName = isset($matches[1]) ? $matches[1] : '';
    // for mulitple arg names
    $varNameArray = explode(',', $varName);
    if (count($varNameArray) === 1) {
        $paramNum = 0;
        $varName = $varNameArray[0];
    } else {
        $varName = $varNameArray[$paramNum];
        if ($paramNum === count($varNameArray) - 1) {
            var_dump($_ENV);
            $paramNum = 0;
        } else {
            $paramNum++;
        }
    }
    $label = "$varName in {$file} on line {$line}$method";
    $label = (is_object($values)) ? ucwords(get_class($values)) . " $label" : $label;
    // if CLI
    if (PHP_SAPI === 'cli') {
        $colorOpenReverse = "\033[7;32m";
        $colorOpenBold = "\033[1;32m";
        $colorOpenPlain = "\033[0;32m";
        $colorClose = "\033[0m";
        echo $colorOpenReverse . "$varName" . $colorClose . " = ";
        var_dump($values);
        echo $colorOpenPlain . "in {$colorOpenBold}{$file}{$colorClose}{$colorOpenPlain} on line {$line}$method" . $colorClose . "\n";
        return;
    }
    $labelField = '<fieldset style="color:#4F5155; border:1px solid black;padding:2px;width:10px;">';
    $labelField .= '<legend style="color:black;font-size:9pt;font-weight:bold;font-family:Verdana,';
    $labelField .= 'Arial,,SunSans-Regular,sans-serif;">' . $label . '</legend>';
    if (class_exists('FB', false)) {
        $label = 'p() in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'];
        FB::group($label);
        FB::error($values);
        FB::groupEnd();
        return;
    }
    $pre = "<pre style=\"text-align: left;margin: 0px 0px 10px 0px; display: block; background: white; color: black; ";
    $pre .= "border: 1px solid #cccccc; padding: 5px; font-size: 12px; \">";
    if ($varName != FALSE) {
        $pre .= "<span style='color: #660000;'>" . $varName . '</span>';
    }
    $pre .= "<span style='color: #660000;'>" . htmlspecialchars($varName) . "</span>";
    $post = '&nbsp;&nbsp;' . "in <span style=\"color:gray\">{$file}</span> on line {$line}$method";
    echo $pre;
    var_dump($values);
    echo $post;
}
