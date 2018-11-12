<?php

switch ($this->params->getParam('test', 'func')) {
        case 'func':
                include("func.inc");
                break;
        case 'time':
                include("time.inc");
                break;
}

