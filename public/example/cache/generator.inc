<?php

// 
// Test shared memory key generator.
// 


use Batchelor\Cache\Backend\Extension\ShmOp\Generator\Hashing;
use Batchelor\Cache\Backend\Extension\ShmOp\Generator\Prefixed;
use Batchelor\Cache\Backend\Extension\ShmOp\Generator\Sequential;
use Batchelor\Cache\Backend\Extension\ShmOp\Generator\Tracking;
use Batchelor\Cache\Backend\Extension\ShmOp\Segment;

// 
// Same key -> same ID:
// 
printf("%s\n", Segment::class);
printf("DEFAULT (1): %d\n", Segment::id("test1"));
printf("DEFAULT (2): %d\n", Segment::id("test2"));
printf("DEFAULT (3): %d\n", Segment::id("test2"));
printf("\n");

// 
// Generated numbers are untracked:
// 
$generator = new Sequential(ftok(__FILE__, "0"));

printf("%s\n", get_class($generator));
printf("SEQUENTIAL (S): %d\n", $generator->getSeed());
printf("SEQUENTIAL (1): %d\n", $generator->next("test1"));
printf("SEQUENTIAL (2): %d\n", $generator->next("test2"));
printf("SEQUENTIAL (3): %d\n", $generator->next("test2"));
printf("\n");

$generator = new Sequential(20180904);

printf("SEQUENTIAL (S): %d\n", $generator->getSeed());
printf("SEQUENTIAL (4): %d\n", $generator->next("test1"));
printf("SEQUENTIAL (5): %d\n", $generator->next("test2"));
printf("SEQUENTIAL (6): %d\n", $generator->next("test2"));
printf("\n");

// 
// Generated numbers are tracked:
// 
$generator = new Tracking(ftok(__FILE__, "0"));

printf("%s\n", get_class($generator));
printf("TRACKING (S): %d\n", $generator->getSeed());
printf("TRACKING (1): %d\n", $generator->next("test1"));
printf("TRACKING (2): %d\n", $generator->next("test2"));
printf("TRACKING (3): %d\n", $generator->next("test2"));
printf("\n");

$generator = new Tracking(20180904);

printf("TRACKING (S): %d\n", $generator->getSeed());
printf("TRACKING (4): %d\n", $generator->next("test1"));
printf("TRACKING (5): %d\n", $generator->next("test2"));
printf("TRACKING (6): %d\n", $generator->next("test2"));
printf("\n");

// 
// Generated numbers are tracked:
// 
$generator = new Hashing();

printf("%s\n", get_class($generator));
printf("HASHING (1): %d\n", $generator->next("test1"));
printf("HASHING (2): %d\n", $generator->next("test2"));
printf("HASHING (3): %d\n", $generator->next("test2"));
printf("\n");

// 
// Generated numbers are tracked:
// 
$generator = new Prefixed(new Sequential());

printf("%s\n", get_class($generator));
printf("PREFIXED (W): %s\n", get_class($generator->getGenerator()));
printf("PREFIXED (1): %d\n", $generator->next("test1"));
printf("PREFIXED (2): %d\n", $generator->next("test2"));
printf("PREFIXED (3): %d\n", $generator->next("test2"));
printf("\n");

$generator = new Prefixed(new Tracking());

printf("%s\n", get_class($generator));
printf("PREFIXED (W): %s\n", get_class($generator->getGenerator()));
printf("PREFIXED (4): %d\n", $generator->next("test1"));
printf("PREFIXED (5): %d\n", $generator->next("test2"));
printf("PREFIXED (6): %d\n", $generator->next("test2"));
printf("\n");

$generator = new Prefixed(new Hashing(), 620000000, 200000);

printf("%s\n", get_class($generator));
printf("PREFIXED (W): %s\n", get_class($generator->getGenerator()));
printf("PREFIXED (7): %d\n", $generator->next("test1"));
printf("PREFIXED (8): %d\n", $generator->next("test2"));
printf("PREFIXED (9): %d\n", $generator->next("test2"));
printf("\n");

print_r($generator);
