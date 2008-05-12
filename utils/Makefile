CC = gcc
CFLAGS = -Wall -O2

PROGRAMS = date simula

.PHONY : all clean all-clean

all : $(PROGRAMS)

date : date.o
simula : simula.o

clean :
	rm -f *.o
all-clean : clean
	rm -f *~ $(PROGRAMS)
