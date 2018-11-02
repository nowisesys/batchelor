<?php

echo "Notice that some properties are only available by reflection on object:\n";

$class = new ReflectionClass(DateTime::class);

printf("[C]: %s\n", print_r($class, true));
printf("[P]: %s\n", print_r($class->getProperties(), true));
printf("[M]: %s\n", print_r($class->getMethods(), true));

$class = new ReflectionObject(new DateTime());

printf("[O]: %s\n", print_r($class, true));
printf("[P]: %s\n", print_r($class->getProperties(), true));
printf("[M]: %s\n", print_r($class->getMethods(), true));
