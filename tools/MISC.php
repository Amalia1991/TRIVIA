<?php
//various mist functions

function putToLog($message)
    {
        if(defined('TRIVIA_LOGS_DIR'))
        {
        echo $message;
        $a=file_put_contents('/var/tmp/writers_log',
            PHP_EOL.$_SERVER['SCRIPT_FILENAME'].':  '.date('r').' -='.$message.'=- ',
            FILE_APPEND);

        return ($a) ? true : false;
        }
        else
        {
            throw new Exception('Constant "TRIVIA_LOGS_DIR" is undefined!');
        }
    }

function obfuscateString($string)
    {
        $finished='';
        for($i=0;$i<strlen($string);++$i)
        {
            $n = rand(0,1);
            if($n)
                $finished.='&#x'.sprintf("%X",ord($string{$i})).';';
            else
                $finished.='&#'.ord($string{$i}).';';
        }
        return $finished;
    }