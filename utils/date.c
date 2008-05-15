/*
 * A simple replacement for the date(1) command on Solaris 8 (and later?) that don't
 * understand the '%s' formatter to get "seconds since epoch" used together with
 * the Coordinated Universal Time
 * 
 * Usage: date -u '%s'
 * 
 * Author: Anders Lövgren
 * Date:   2008-05-12
 */

#include <stdio.h>
#include <time.h>
#include <libgen.h>
#include <stdlib.h>

static void usage(const char *prog)
{
	printf("%s - drop in replacement for system date(1) command without '%%s' formatter\n", prog);
	printf("usage: %s -u '%%s'\n", prog);
}

int main(int argc, char **argv)
{
	char *prog = basename(argv[0]);
   
	if(argc != 3) {
		usage(prog);
		exit(1);
	}
   
	/*
	 * OK, we cheated a bit ;-)
	 */
	printf("%lu\n", time(NULL));
	return 0;
}
