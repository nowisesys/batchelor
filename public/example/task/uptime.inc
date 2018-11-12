<?php

// 
// Test using interactive session with ssh. Define the username and password 
// if you wan't to test this script.
// 
$user = "username";
$pass = "secret";

// 
// Execute shell using SSH. Will accept the key if not yet done. Detect shell 
// prompt and execute a simple command.
// 

ini_set("expect.loguser", "Off");
ini_set("expect.logfile", "/tmp/expect.out");

define("PASSWORD", 1);
define("KEYQUEST", 2);
define("PROMPT", 3);

$stream = expect_popen("ssh $user@localhost");

$cases = array(
        array(0 => "Password:", 1 => PASSWORD),
        array(0 => "(yes/no)?", 1 => KEYQUEST),
        array(0 => "~$ ", 1 => PROMPT)      // Watch out for color terminal!
);

while (true) {
        switch (expect_expectl($stream, $cases)) {
                case PASSWORD:
                        fwrite($stream, "$pass\n");
                        break;
                case KEYQUEST:
                        fwrite($stream, "yes\n");
                        break;
                case PROMPT:
                        fwrite($stream, "uptime\n");
                        printf("%s: %s\n", trim(fgets($stream)), trim(fgets($stream)));
                        break 2;
                default:
                        die("Error occurred while connecting to the remote host!\n");
        }
}

fwrite($stream, "ls -l /tmp\n");
while ($line = fgets($stream)) {
        print $line;
}

fwrite($stream, "exit\n");
fclose($stream);
