/*
 * Small pragram to simulate different batch job scenarios. Call this
 * program from inside script.sh to test:
 * 
 * 1. Job exit (success, error or crash).
 * 2. Workload (busy loop or sleep).
 * 3. Capture of standard streams (stdout and stderr).
 * 
 * Author: Anders Lövgren
 * Date:   2007-11-05
 * 
 * This program is released under GPL version 2 or later. See COPYING
 * for details.
 */

#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <unistd.h>
#include <time.h>
#include <signal.h>
#include <limits.h>
#include <string.h>

#define MIN_RUN_LENGTH 5
#define MAX_RUN_LENGTH 20

#ifdef  EXIT_SUCESS
# undef EXIT_SUCESS
#endif
#ifdef  EXIT_FAILED
# undef EXIT_FAILED
#endif
#ifdef  EXIT_CRASH
# undef EXIT_CRASH
#endif
#define EXIT_SUCESS 0
#define EXIT_FAILED 1
#define EXIT_CRASH  255

enum { STATUS_SUCCESS, 
       STATUS_WARNING, 
       STATUS_ERROR, 
       STATUS_CRASH, 
       STATUS_LAST };

static int simulate_success(const char *indata, const char *resdir);
static int simulate_error(const char *indata, const char *resdir);
static int simulate_warning(const char *indata, const char *resdir);
static int simulate_crash(const char *indata, const char *resdir);
static void dump_options(const char *indata, const char *resdir, int status, int duration, int endtime, int busy);
static int write_file(const char *path, const char *msg);
static void usage(void);
static void version(void);

int main(int argc, char **argv)
{
	int status;
	int duration;
	int endtime;
	int busy;
	int code = EXIT_FAILED;
	int c;
	const char *indata = NULL;
	const char *resdir = NULL;

	if(argc == 1) {
		usage();
		return 1;
	}
		
	srand(time(NULL));
	
	status   = rand() % STATUS_LAST;
	duration = MIN_RUN_LENGTH + rand() % (MAX_RUN_LENGTH - MIN_RUN_LENGTH);
	
	busy = rand() % 2;
	
	while((c = getopt(argc, argv, "bd:hi:r:s:V")) != -1) {
		switch(c) {
		case 'b':
			busy = 1;
			break;
		case 'd':
			duration = atoi(optarg);
			break;
		case 'h':
			usage();
			exit(0);
		case 'i':
			indata = optarg;
			break;
		case 'r':
			resdir = optarg;
			break;
		case 's':
			if(strcmp(optarg, "success") == 0) {
				status = STATUS_SUCCESS;
			}
			else if(strcmp(optarg, "warning") == 0) {
				status = STATUS_WARNING;
			}
			else if(strcmp(optarg, "error") == 0) {
				status = STATUS_ERROR;
			}
			else if(strcmp(optarg, "crash") == 0) {
				status = STATUS_CRASH;
			}
			else {
				fprintf(stderr, "unknown option value %s for '-s'\n", optarg);
				exit(1);
			}
			break;
		case 'V':
			version();
			exit(0);
		default:
			fprintf(stderr, "unknown option '%s'\n", optarg);
			exit(1);
		}
	}
	endtime = time(NULL) + duration;
	
	dump_options(indata, resdir, status, duration, endtime, busy);
	
	if(chdir(resdir) == 0) {
		if(busy) {
			while(1) {
				if(time(NULL) > endtime) {
					break;
				}
			}
		}
		else {
			sleep(duration);
		}
		
		switch(status) {
		case STATUS_SUCCESS:
			code = simulate_success(indata, resdir);
			break;
		case STATUS_WARNING:
			code = simulate_warning(indata, resdir);
			break;
		case STATUS_ERROR:
			code = simulate_error(indata, resdir);
			break;
		case STATUS_CRASH:
			code = simulate_crash(indata, resdir);
			break;
		}
	}
	else {
		fprintf(stderr, "Failed change diretory to %s\n", resdir);
	}
	
	exit(code);
}

int simulate_success(const char *indata, const char *resdir)
{
	char path[PATH_MAX];
	
	sprintf(path, "%s/file1", resdir);
	write_file(path, "Some text..., ");

	sprintf(path, "%s/file2", resdir);
	write_file(path, "some more text..., ");

	sprintf(path, "%s/file3", resdir);
	write_file(path, "the end!");
	
	printf("This message should be captured as stdout\n");
	printf("Exiting with status %d\n", EXIT_SUCCESS);
	return EXIT_SUCESS;
}

int simulate_error(const char *indata, const char *resdir)
{
	fprintf(stderr, "This message should be captured as stderr (error message)\n");
	fprintf(stderr, "Exiting with status %d\n", EXIT_FAILED);
	return EXIT_FAILED;
}

int simulate_warning(const char *indata, const char *resdir)
{
	fprintf(stderr, "This message should be captured as stderr (warning message)\n");
	fprintf(stderr, "Exiting with status %d\n", EXIT_SUCCESS);
	return EXIT_SUCCESS;
}

int simulate_crash(const char *indata, const char *resdir)
{
	kill(getppid(), SIGTERM);
	return EXIT_CRASH;
}

void dump_options(const char *indata, const char *resdir, int status, int duration, int endtime, int busy)
{
	printf("-------------------------------------------------------------------\n");
	printf("Using indata file %s\n", indata);
	printf("Using result directory %s\n", resdir);
	printf("Options:  status=%d, duration=%d, endtime=%d, busy=%d\n", status, duration, endtime, busy);
	printf("Defaults: runtime length=(%d/%d) (min/max)\n", MIN_RUN_LENGTH, MAX_RUN_LENGTH);
	printf("-------------------------------------------------------------------\n");
}

int write_file(const char *path, const char *msg)
{
	FILE *fs = fopen(path, "w");
	if(!fs) {
		fprintf(stderr, "Failed create file %s\n", path);
		return -1;
	}
	
	fprintf(fs, "%s", msg);
	fclose(fs);
	
	printf("Created file %s\n", path);
	return 0;
}

void usage(void)
{
	/* "bd:hi:r:s:V */
	printf("Usage: simula -i indata -r resdir [options...]\n");
	printf("Options:\n");
	printf("  -b:     run in busy mode\n");
	printf("  -d sec: set duration\n");
	printf("  -h:         this help\n");
	printf("  -i indata:  read indata file\n");
	printf("  -r resdir:  save result to resdir\n");
	printf("  -s status:  set exit status (success, warning, error or crash)\n");
	printf("  -V:         show version\n");
	printf("This program is released under GPL version 2 or later.\n");
	printf("Send bug reports to anders.lovgren@bmc.uu.se\n");
}

void version(void)
{
	printf("simula version 0.4.2\n");
}
