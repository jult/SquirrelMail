/*
 * wfwd.c
 *
 * Writes .fetchmailrc file for email fetching, works with fetchmail
 *
 * Copied from the mail_fwd plugin written by Ritchie Low
 *
 * Original author Ritchie Low
 *
 * Changes made by
 *	Pontus Ullgren <pontus@ullgren.com>
 * 
 * Instalation:	   
 *	Should be owned by root and suid root.
 *	If RESTRICTUSE was enabled at compile time the the executing user
 *	(real user) must have the same uid as the user defined by WEBUSER
 * 
 */
 
#define BUFSIZE 64
#define MAXEMAILADRESSES 40
#define FIELDS	5

#include <sys/types.h>
#include <sys/stat.h>
#include <sys/wait.h>
#include <unistd.h>
#include <fcntl.h>
#include <syslog.h>
#include <stdlib.h>
#include <stdio.h>
#include <strings.h>
#include <pwd.h>
#include <string.h>


main (argc, argv)
int argc;
char *argv[];
{
	 char line[BUFSIZE];
	 char *puid; 
	 char *pemail[FIELDS];
	 unsigned int noemail = 0;
	 struct passwd *pw, *getpwnam();
	 struct passwd *pwebuser;
	 FILE *fd;
	 int i; // To be used in for-loops

#ifdef RESTRICTUSE
	 /* RESTRICTUSE is defined.
	Then we start with a check to see if the real user is
	the valid user. */
	if ((pwebuser=getpwnam(WEBUSER))==NULL)
	{
		printf("Invalid webuser. %s\n");
		exit(1);
	}
	if( pwebuser->pw_uid != getuid() )
	{
		printf("Invalid real user.\n");
		exit(1);
	}
#endif /* RESTRICTUSE */

	 if (argc != 5) {
		printf("Usage: %s userid email-address password server\n",argv[0]);
		exit(1);
	 }

	 /* TODO: We should be able to set more than one 
		email-address */
	 // We allow a maximum of MAXEMAILADRESSES
//	 if (argc > (MAXEMAILADRESSES +3) ) {
//		printf("To many email-addresses supplied.\n");
//		exit(1);
//	}
	if (argc >= 3) {
		for(i=2; i < argc; i++)
		{
			pemail[noemail] = argv[i];
			noemail++;
	 	}
	} else {
		pemail[0] = "\0";
		noemail++;
	}

	puid = argv[1];
	if ((pw=getpwnam(puid))==NULL)
	{
		printf("Invalid user\n ");
		exit(1);
	}
	setgid (pw->pw_gid);
	setuid (pw->pw_uid);

	if( snprintf(line, BUFSIZE, "%s/.fetchmailrc", pw->pw_dir) > BUFSIZE ) {
		printf("Supplied users homedir path to long.\n");
		exit(1);
	}
	 
	 /* Check to see if any email was supplied */
	if(strcmp(pemail[0],"-")) {
		if ((fd = (FILE *)fopen(line,"w"))==NULL) {
		 printf("Cannot open %s\n",line);
		 exit(1);
	}

	fprintf(fd, "poll %s protocol pop3 username \"%s\" password \"%s\" is \"%s\" here\n",
		pemail[2], pemail[0], pemail[1], puid);

		fclose(fd);
		chmod(line, S_IRUSR | S_IWUSR );

	} else {
		/* No email supplied delete the file */
		struct stat statbuf;
		if (stat(line, &statbuf) == 0)
		{
			if(unlink(line) == -1) 
			{
				printf("Couldn't delete %s\n",line);
				exit(1);
			}
		}
	}
}
