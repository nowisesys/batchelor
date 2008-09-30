<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007-2008 Anders Lövgren
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
// -------------------------------------------------------------------------------

// 
// This is the XML-RPC web service implementing the UserLand Sotware's specification:
// http://www.xmlrpc.com/spec
//

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../../");

include "include/common.inc";
include "include/queue.inc";
include "include/ws.inc";

//
// Get configuration.
// 
include "conf/config.inc";


?>		
