include config.mk

all:	wfetch

install: wfetch
	install -g root -o root -m 4555 wfetch $(BINDIR)
	install -g root -o root -m 4555 fetchmailloop.sh $(BINDIR)
	install -g root -o root -m 4555 dothefetch.sh $(BINDIR)

clean:
	rm -f *.o *~* core wfetch


wfetch: wfetch.o
	cc -o wfetch $(LFLAGS) wfetch.o $(LIBS)

wfetch.o: wfetch.c
