include config.mk

OBJECTS = wfwd.o
LIBS =
CC = cc
INSTALL_BINARY = /usr/bin/install

ERRORMSG="IMPORTANT: Edit config.mk !!!"

wfwd: $(OBJECTS)
ifeq ($(REMOVE_THIS),YES)
	@echo $(ERRORMSG)
else
	$(CC) -o wfwd $(LFLAGS) $(OBJECTS) $(LIBS)
endif

install: wfwd
ifeq ($(REMOVE_THIS),YES)
	@echo $(ERRORMSG)
else
	$(INSTALL_BINARY) -g bin -o root -m 4555 wfwd $(BINDIR)
endif

clean:
	rm -f *.o *~* core wfwd

wfwd.o: wfwd.c
ifeq ($(REMOVE_THIS),YES)
	@echo $(ERRORMSG)
else
	$(CC) $(XTRA_CC_FLAGS) -c $(CFLAGS) $(RESTRICTUSE) -D WEBUSER=\"$(WEBUSER)\" wfwd.c
endif
