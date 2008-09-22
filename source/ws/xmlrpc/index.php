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
// This is the XML-RPC web service implementing the Apache org specification:
// http://www.xmlrpc.com/spec
//

include "../../../include/common.inc";
include "../../../include/queue.inc";
include "../../../include/ws.inc";

// 
// Request broker. This structure list all XML-RPC functions (public and 
// internal and their parameter types.
// 
$functions = array( "batchelor.listFuncAPI"       => array( "func" => "api",
							    "desc" => "List XML-RPC function API",
							    "params" => array( "name" => "string" )
							    ),
		    "batchelor.suspendRunningJob" => array( "func" => "suspend",
							    "desc" => "Suspend a running job",
							    "params" => array( "resdir" => "string", 
									       "jobid"  => "string" )
							    ),
		    "batchelor.resumePausedJob"   => array( "func" => "resume",
							   "desc" => "Resume a paused/stopped job",
							   "params" => array( "resdir" => "string",
									      "jobid"  => "string")
							   ),
		    "batchelor.addNewJob"         => array( "func" => "enqueue",
							    "desc" => "Start a new job",
							    "params" => array( "indata" => "base64",
									       "job" => "struct" )
							    ),
		    "batchelor.deleteJob"         => array( "func" => "dequeue",
							    "desc" => "Delete an running or finished job",
							    "params" => array( "resdir" => "string", 
									       "jobid" => "string" )
							    ),
		    "batchelor.listQueuedJobs"    => array( "func" => "queue",
							    "desc" => "List queued and finished jobs",
							    "params" => array( "sort" => "string",
									       "filter" => "string" )
							    ),
		    "batchelor.listResultDirs"    => array( "func" => "result",
							    "desc" => "List result directories",
							    "params" => array()
							    ),
		    "batchelor.listResultFiles"   => array( "func" => "result",
							    "desc" => "List files in result directory",
							    "params" => array( "resdir" => "string",
									       "jobid"  => "string" )
							    ),
		    "batchelor.listResultFile"    => array( "func" => "result",
							    "desc" => "Get content of result file",
							    "params" => array( "resdir" => "string",
									       "jobid"  => "string",
									       "file"   => "string" )
							    )
		    );


?>		
