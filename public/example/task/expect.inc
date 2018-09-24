<?php

use Batchelor\Queue\Task\Execute\Expect;

// 
// Connect to localhost using SSH. Define the username and password if you 
// wan't to test this script.
// 
$user = "username";
$pass = "secret";

ini_set("expect.loguser", "Off");
ini_set("expect.logfile", "/tmp/expect.out");

// 
//  Exceute shell with command:
// 
$expect = new Expect("/bin/sh");
$expect->execute("date\n", static function($stream, $expect) {
        while ($expect->isReadable()) {
                printf("[SH] %s\n", trim(fgets($stream)));
        }
});
        
// 
// Execute bc for calculation. Notice that we are only processing
// the actual calculation (a * b):
// 
$expect = new Expect("bc");
$expect->process([[
            "For details type `warranty'.", false
    ]]);
$expect->execute("a=49\n");
$expect->execute("b=12\n");
$expect->execute("a*b\n", static function($stream, $expect) {
        while ($expect->isReadable()) {
                printf("[BC] %s\n", trim(fgets($stream)));
        }
});
$expect->execute("quit\n");

// 
// Open SSH connection:
// 
$expect = new Expect("ssh $user@localhost");

// 
// Handle login and key accept. Return false when seeing the shell prompt
// to break input processing loop.
// 
$expect->process([
        ["assword:", "$pass\n"],
        ["(yes/no)", "yes\n"],
        ["~$ ", false]
]);

// 
// Set custom prompt for readability and executed some simple commands. Pass
// true to get command echo and output:
// 
$expect->execute("unset PROMPT_COMMAND && export PS1='bash$> '\n", true);
$expect->execute("pwd\n", true);
$expect->execute("whoami\n", true);

// 
// List file in remote directory. The stream passed to callback is already in
// non-blocking mode and we use polling.
// 
$expect->execute("ls -l /home --color=never\n", static function($stream, $expect) {
        while ($expect->isReadable()) {
                print fgets($stream);
        }
});
$expect->execute("ls -l /usr --color=never\n", static function($stream, $expect) {
        while ($expect->isReadable()) {
                print fgets($stream);
        }
});

// 
// Close SSH connection:
// 
$expect->execute("exit\n");
