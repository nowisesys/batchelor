<?php

use Batchelor\Queue\Task\Execute\Command;
use Batchelor\Queue\Task\Execute\Interact;

// 
// Example using interactive class.
// 
// This example connects thru telnet and executes some commands. Define the 
// username and password for testing.
// 

$user = "username";
$pass = "secret";

$output = "";           // The output buffer.
//
// 
// Open telnet connection. Unset color prompt once connected, run some commands
// and exit remote shell.
// 
$interact = new Interact(
    new Command("telnet localhost")
);

$interact->setDebug(static function($message) {
        printf("[D]: %s\n", trim($message));
});

$interact->match("/.*login:/", static function($command, $line) use($user) {
        printf("[U]: %s\n", $line);
        $command->setInput("$user\n");
});
$interact->match("/.*assword:/", static function($command, $line) use($pass) {
        printf("[P]: %s\n", $line);
        $command->setInput("$pass\n");
});
$interact->match("/Last login:.*/", static function($command, $line) {       // Greeting ;-)
        printf("[S]: %s\n", $line);
});

$interact->run("unset PROMPT_COMMAND && export PS1='bash$> '\n");
$interact->run("pwd\n", $output);
$interact->run("whoami\n", $output);
$interact->run("ls -l /tmp --color=never\n", $output);

$interact->run("exit\n");

print_r($output);
