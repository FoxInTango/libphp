<?php

class optionCMD {
    public $runPath;
    public $logPath;
    public $params;
    public function __construct($runPath,$params,$logPath)
    {
        $this->runPath = $runPath;
        $this->logPath = $logPath;
        $this->params  = $params;
    }
}

class command {
    public $application;
    public $options;

    public function __construct($application,$options) {    
        $this->application = $application;
        $this->options = $options;
    }

    public function run(){
        if(!isset($this->application)) {
            return false;
        }

        $commandString = '';

        if(isset($this->options)) {
            if(isset($this->options->runPath) && strlen($this->options->runPath)) {
                $commandString .= 'cd ' . $this->options->runPath;
            }

            if(isset($this->options->params)  && strlen($this->options->params)) {
                $commandString .= " && nohup " . $this->application . " " . $this->options->params;
            } else {
                $commandString .= " && nohup " . $this->application;
            }

            if(isset($this->options->logPath)  && strlen($this->options->logPath)) {
                $commandString .= " >> " . $this->options->logPath . " &";
            } else {
                $commandString .= " >> /dev/null &";
            }
        } else {
            $commandString = "nohup " . $this->application . " >> /dev/null &";
        }
        //echo "command : " . $commandString;
        exec($commandString);
    }
}
/******************************************************************************************
$application = "git";
$optionA = new optionCMD("/Users/www/ssl","","");
$optionB = new optionCMD("/Users/www/ssl","pull","");
$optionC = new optionCMD("/Users/www/ssl","pull","/Users/www/ssl/ssl.log");

$commandA = new command($application,$optionA);
$commandB = new command($application,$optionB);
$commandC = new command($application,$optionC);
$commandA->run();
$commandB->run();
$commandC->run();
********************************************************************************************/
?>
