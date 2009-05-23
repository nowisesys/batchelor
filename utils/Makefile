CC = gcc
CFLAGS = -Wall -O2

PROGRAMS = date simula

.PHONY : all clean all-clean

all : $(PROGRAMS)

date : date.o
simula : simula.o

# Generate a stripped down version of ws.php for PHP4. This is required as the
# original ws.php contains language features (try and catch) that PHP4 is 
# unaware of which leads to compilation errors in the interpreter.
ws_php4 :
	@( \
	php -r 'include "../include/contents.inc"; printf("%s", preg_replace("/(function get_soap_response($$options)).*?\n}\n/ms", "$$1(\$$options)\n{\n\tdie(\"soap is not supported in php4\n\");\n}\n", stream_get_contents(STDIN)));' < ws.php > ws_php4.php; \
	echo "ws_php4.php created"; \
	)

clean :
	rm -f *.o
all-clean : clean
	rm -f *~ $(PROGRAMS)
