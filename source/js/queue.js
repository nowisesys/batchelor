// 
// Queue refresh support for Batchelor. 
// 
// When this script gets loaded by the client browser, it will start a timer 
// to poll the queue for completed jobs. If no jobs are waiting or running 
// it will cancel the timer. If one or more jobs get completed between each 
// poll, the queue page will be reloaded and the process starts over again 
// until all queued jobs are finished.
// 
// This script depends on the free, open source JSMX Ajax library. It can be
// downloaded from: http://www.lalabird.com/?fa=JSMX.downloads
// 
// Author: Anders Lövgren
// Date:   2009-05-15
// 

// 
// The queue state object template:
// 
function queue_state(count, pending, running, active)
{
	this.count = count;       // Total number of jobs.
	this.pending = pending;   // Number of pending jobs.
	this.running = running;   // Number of running jobs.
}

var state;   // The queue state object.
var timer;   // The timer ID.

// 
// This function should be called once when the page gets loaded. The 
// interval is the time between each poll in seconds.
// 
function start_poll_queue(interval) 
{
	timer = setInterval("check_queue_request()", 1000 * interval);
}

// 
// This function makes an XMLHttpRequest unless an request is already active.
// 
function check_queue_request() 
{
	// 
	// Create new state object.
	// 
	if(state == null) {
		state = new queue_state(0, 0, 0, false);
	}
	
	// 
	// Make the XMLHttpRequest:
	// 
	http("GET", "ws/http/queue?filter=waiting&format=json", check_queue_response);
}

// 
// This is the callback function for the XMLHttpRequest. We use the fact 
// that javascript functions are objects to store some static variables
// between invokations.
// 
function check_queue_response(result) 
{
	var pending = 0;
	var running = 0;
	
	// 
	// An empty queue means we are done.
	// 
	if(result.length == 0) {
		check_queue_reload();
	}
	
	// 
	// Count the number of pending and running jobs in this response.
	// 
	for(var i = 0; i < result.length; ++i) {		
		if(result[i].state == "pending") {
			pending++; 
		}
		if(result[i].state == "running") {
			running++;
		}
	}
	
	// 
	// This block should not be executed the first time this method gets called.
	// 
	if(state.count != 0) {
		// 
		// Refresh the page if the state of any job has changed.
		// 
		if(state.pending != pending || state.running != running) {
			check_queue_reload();
		}
		
		// 
		// Refresh the page if the number of waiting job has changed.
		// 
		if(state.count != null && state.count != result.length) {
			check_queue_reload();
		}
	}
	
	// 
	// Save the state for next invocation.
	// 
	state.count = result.count;	
	state.running = running;
	state.pending = pending;

	return true;
}

// 
// This function gets called if all jobs are finished or if the state
// of any of the jobs has changed (like pending => running).
// 
function check_queue_reload() 
{
	clearInterval(timer);
	window.location.reload(false);
}
