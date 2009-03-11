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

define ("WS_SOAP_INTERFACE_VERSION", "1.0");

//
// Synopsis: EnqueueParams(string)
//
class EnqueueParams {
    var $in0;	// string
    function EnqueueParams($in0) {
        $this->in0 = $in0;
    }
}

//
// Synopsis: EnqueueResponse(EnqueueData)
//
class EnqueueResponse {
    var $enqueueReturn;	// EnqueueData
    function EnqueueResponse($enqueueReturn) {
        $this->enqueueReturn = $enqueueReturn;
    }
}

//
// Synopsis: EnqueueData(string, string, int, string)
//
class EnqueueData {
    var $date;	// string
    var $jobID;	// string
    var $stamp;	// int
    var $time;	// string
    function EnqueueData($date, $jobID, $stamp, $time) {
        $this->date = $date;
        $this->jobID = $jobID;
        $this->stamp = $stamp;
        $this->time = $time;
    }
}

//
// Synopsis: QueueParams(string, string)
//
class QueueParams {
    var $in0;	// string
    var $in1;	// string
    function QueueParams($in0, $in1) {
        $this->in0 = $in0;
        $this->in1 = $in1;
    }
}

//
// Synopsis: QueueResponse(QueuedJob)
//
class QueueResponse {
    var $queueReturn;	// QueuedJob
    function QueueResponse($queueReturn) {
        $this->queueReturn = new SoapVar($queueReturn, SOAP_ENC_ARRAY, "QueuedJob");
    }
}

//
// Synopsis: JobIdent(string, string)
//
class JobIdent {
    var $jobId;	// string
    var $result;	// string
    function JobIdent($jobId, $result) {
        $this->jobId = $jobId;
        $this->result = $result;
    }
}

//
// Synopsis: QueuedJob(JobIdent, string)
//
class QueuedJob {
    var $jobIdent;	// JobIdent
    var $state;	// string
    function QueuedJob($jobIdent, $state) {
        $this->jobIdent = $jobIdent;
        $this->state = $state;
    }
}

//
// Synopsis: ResumeParams(JobIdent)
//
class ResumeParams {
    var $in0;	// JobIdent
    function ResumeParams($in0) {
        $this->in0 = $in0;
    }
}

//
// Synopsis: ResumeResponse(boolean)
//
class ResumeResponse {
    var $resumeReturn;	// boolean
    function ResumeResponse($resumeReturn) {
        $this->resumeReturn = $resumeReturn;
    }
}

//
// Synopsis: SuspendParams(JobIdent)
//
class SuspendParams {
    var $in0;	// JobIdent
    function SuspendParams($in0) {
        $this->in0 = $in0;
    }
}

//
// Synopsis: SuspendResponse(boolean)
//
class SuspendResponse {
    var $suspendReturn;	// boolean
    function SuspendResponse($suspendReturn) {
        $this->suspendReturn = $suspendReturn;
    }
}

//
// Synopsis: VersionParams()
//
class VersionParams {
    function VersionParams() {
    }
}

//
// Synopsis: VersionResponse(string)
//
class VersionResponse {
    var $versionReturn;	// string
    function VersionResponse($versionReturn) {
        $this->versionReturn = $versionReturn;
    }
}

//
// Synopsis: DequeueParams(JobIdent)
//
class DequeueParams {
    var $in0;	// JobIdent
    function DequeueParams($in0) {
        $this->in0 = $in0;
    }
}

//
// Synopsis: DequeueResponse(boolean)
//
class DequeueResponse {
    var $dequeueReturn;	// boolean
    function DequeueResponse($dequeueReturn) {
        $this->dequeueReturn = $dequeueReturn;
    }
}

//
// Synopsis: WatchParams(int)
//
class WatchParams {
    var $in0;	// int
    function WatchParams($in0) {
        $this->in0 = $in0;
    }
}

//
// Synopsis: WatchResponse(QueuedJob)
//
class WatchResponse {
    var $watchReturn;	// QueuedJob
    function WatchResponse($watchReturn) {
        $this->watchReturn = $watchReturn;
    }
}

//
// Synopsis: OpendirParams()
//
class OpendirParams {
    function OpendirParams() {
    }
}

//
// Synopsis: OpendirResponse(JobIdent)
//
class OpendirResponse {
    var $opendirReturn;	// JobIdent
    function OpendirResponse($opendirReturn) {
        $this->opendirReturn = $opendirReturn;
    }
}

//
// Synopsis: ReaddirParams(JobIdent)
//
class ReaddirParams {
    var $in0;	// JobIdent
    function ReaddirParams($in0) {
        $this->in0 = $in0;
    }
}

//
// Synopsis: ReaddirResponse(string)
//
class ReaddirResponse {
    var $readdirReturn;	// string
    function ReaddirResponse($readdirReturn) {
        $this->readdirReturn = $readdirReturn;
    }
}

//
// Synopsis: FopenParams(JobIdent, string)
//
class FopenParams {
    var $in0;	// JobIdent
    var $in1;	// string
    function FopenParams($in0, $in1) {
        $this->in0 = $in0;
        $this->in1 = $in1;
    }
}

//
// Synopsis: FopenResponse(string)
//
class FopenResponse {
    var $fopenReturn;	// string
    function FopenResponse($fopenReturn) {
        $this->fopenReturn = $fopenReturn;
    }
}

?>
