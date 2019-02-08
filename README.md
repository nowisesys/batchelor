# BATCH JOB QUEUE MANAGER (BATCHELOR)

Batchelor is an batch job queue manager supporting schdeule task for later 
execution, controlling them and retrieving result. Suitable applications can be
scientific or heavy processing tasks (like media rendering).

### QUEUES

The job queue can be accessed thru both a web interface and web services (i.e. 
SOAP or JSON API). The simpliest configuration uses an local queue, but the 
installation can be configured as a frontend for multiple remote queues which 
gives an distributed system with a common interface.

### TASKS

Implement one or more classes that defines your application business logic
and register them with the processor service. An incoming processing request 
is queued (scheduled for later execution). 

A background process consumes queued tasks and uses the task registry to find 
a suitable class (among those registered by you) to process the data. The task
class is called with input data and working directory.

### END USERS

An end user can have multiple personal queues that they can switch between. 
Theres also support for authentication meant to give authorized users extra 
powers, for example relaxed upload limits.

### INTEGRATE

See the file docs/GETTING-STARTED that contains information for integrators on
how to configure batchelor to power your applications.

### QUICK START

It's easiest is to use composer to initialize your project using batchelor:

```bash
composer require nowise/batchelor
./vendor/bin/batchelor.sh --location /myapp --setup
```

Start the scheduled job processor to execute submitted jobs. During setup or 
development of your own task its recommended to run in the foreground with
debug enabled:

```bash
sudo -u apache ./utils/processor.sh -dk
```

Directory utils/boot contains script for starting the batch job processor at 
boot time. The scheduler can be monitored using:

```bash
sudo -u apache ./utils/scheduler.sh -lA
```

Remember to run these tools as the web server user or file permission errors
will occure. If seeing errors, try admin/fix-permissions.sh first to correct 
wrong permissions on the data directory content.

### FURTHER EXAMPLES

The system [ChemGPS-NP Web](https://chemgps.bmc.uu.se/) was built on top of
batchelor. Visit [Batchelor project page](https://nowise.se/oss/batchelor) 
for more online info.
