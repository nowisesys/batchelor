<div class="w3-panel">
    <h4>Resource limit</h4>
    <p>
        If running the shmop dies with an 'No space left on device' error in call to shmop_open(), 
        try to increse the ulimit for the web server process.
    </p>
    <p>
        Use this command pipe to cleanup already opened shared memory segments:
    </p>
    <div class="w3-code bash">
        <span>bash$> ipcs | grep apache | grep 400 | awk -- '{print $2}' | while read k; do ipcrm -m $k; done</span>
    </div>
</div>
