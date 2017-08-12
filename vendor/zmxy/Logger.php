<?php
/*
 * PHP Logger Class
 * Created: 2011-10-26
 * Author: xingfei(http://blog.csdn.net/jakieyoung)
 * Licence: Free of use and redistribution
 */

if(!defined('_LOGGER_PHP_')) {
    define('_LOGGER_PHP_', '1');

    if(!file_exists(ZMXY_PATH.'logs')){
        mkdir(ZMXY_PATH.'logs/',0777,true);
    }

    if(!defined('LOG_ROOT')) {
        define('LOG_ROOT', ZMXY_PATH.'logs/');
    }

    define('LEVEL_FATAL', 0);
    define('LEVEL_ERROR', 1);
    define('LEVEL_WARN', 2);
    define('LEVEL_INFO', 3);
    define('LEVEL_DEBUG', 4);


    class Logger {
        static $LOG_LEVEL_NAMES = array(
            'FATAL', 'ERROR', 'WARN', 'INFO', 'DEBUG'
        );

        private $level = LEVEL_INFO;

        static function getInstance() {
            return new Logger;
        }

        function setLogLevel($lvl) {
            if($lvl >= count(Logger::$LOG_LEVEL_NAMES)  || $lvl < 0) {
                throw new Exception('invalid log level:' . $lvl);
            }
            $this->level = $lvl;
        }

        function _log($level, $message, $name) {
            if($level > $this->level) {
                return;
            }
            date_default_timezone_set('PRC');
            $log_file_path = LOG_ROOT . $name . '.log';
            $log_level_name = Logger::$LOG_LEVEL_NAMES[$this->level];
            $content ='目前记录等级：'.Logger::$LOG_LEVEL_NAMES[$level] .' '.date('Y-m-d H:i:s') . ' 最高BUG等级[' . $log_level_name . '] ' . $message . PHP_EOL;
//            if(!file_exists($log_file_path)){
//                fopen($log_file_path,'w+');
//                fwrite($log_file_path,$content);
//                fclose($log_file_path);
//            }else{
//                file_put_contents($log_file_path, $content, FILE_APPEND);
//            }
            file_put_contents($log_file_path, $content, FILE_APPEND);

        }


        function debug($message, $name = 'debug') {
            $this->_log(LEVEL_DEBUG, $message, $name);
        }
        function info($message, $name = 'info') {
            $this->_log(LEVEL_INFO, $message, $name);
        }
        function warn($message, $name = 'warn') {
            $this->_log(LEVEL_WARN, $message, $name);
        }
        function error($message, $name = 'error') {
            $this->_log(LEVEL_ERROR, $message, $name);
        }
        function fatal($message, $name = 'fatal') {
            $this->_log(LEVEL_FATAL, $message, $name);
        }

    }
}