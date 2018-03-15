/*
 * squirrelmail_autoresponder_forwarder_proxy.c
 *
 * Accesses local files in a user's home directory
 *
 * Rewritten July 2007 by Dan Astoorian based on the original by
 * Jonathan Bayer and Paul Lesneiwski.
 *
 * PAM support courtesy of Christiane Ruetten.
 *
 * Installation:
 *
 *      Must be owned by root, and suid.
 *
 *      If WEBUSER was enabled at compile time then the executing user
 *      (real user) must have the same uid as the user defined by
 *      WEBUSER.
 *
 *      If MIN_UID was enabled at compile time, then this program may
 *      not be executed on behalf of users with uid < MIN_UID.
 *
 *      To use PAM to control access, define USEPAM, and set PAMSERVICE
 *      to the service name to identify the application to PAM.
 *
 *      If you are not using PAM, and your system uses /etc/shadow for
 *      passwords, define USESHADOW at compile time.
 *
 *      If USEPAM is not in effect:
 *
 *          If EMPTY_PASSWORD_OK was enabled at compile time, then
 *          access to accounts with no password set is not allowed; the
 *          default is to deny access to passwordless accounts when
 *          USEPAM is not defined.
 *
 *      By default, only remote filenames beginning with ".forward" or
 *      ".vacation" may be accessed, and access to subdirectories is not
 *      allowed.  This behaviour can be relaxed to varying degrees with
 *      the following definitions:
 *
 *      - If REMOTE_DOTFILES_OK is defined, filenames which do not begin
 *        with ".forward" or ".vacation" are allowed, but they must
 *        still begin with a ".".
 *      - If REMOTE_NORESTRICT is defined, filenames need not begin with
 *        a "." at all.
 *      - If REMOTE_SUBDIRECTORIES_OK is defined, the program will
 *        access any file under the user's home directory, including
 *        subdirectories.  (However, a "put" operation will not create
 *        new directories; the path to the final name must exist.)
 *
 *      If SILENT is defined, then errors will be suppressed; otherwise,
 *      they are reported to standard error, and typically will show up
 *      in the web server's error logs.
 *
 *      Advanced features:
 *
 *      If HOMEDIR_PREFIX is defined, then it is used as a path to prepend
 *      to the user's home directory; e.g., if HOMEDIR_PREFIX is
 *      "/userhomes", and joe's home directory is "/home/joe", then
 *      the path "/userhomes/home/joe" would be used when transferring
 *      files.
 *
 *      If CREATE_HOMEDIR is defined, then on a put or init operation,
 *      the program will attempt to create the user's home directory
 *      owned by the user if it does not already exist.
 *
 *      VACATION_PATH must be defined to enable the init operation; it
 *      should be set to the pathname of the vacation program.
 *
 *      VACATION_INIT_FLAG should be set to the flag that is passed to
 *      the vacation program to tell it to initialize the default
 *      (default -I).
 *
 *      If VACATION_USERARG is defined, the username for whom the
 *      program is being invoked will be supplied on the command line of
 *      the vacation program.
 *
 *      VACATION_PATHENV should be the value the PATH environment
 *      variable should be set to when running the VACATION_PATH
 *      program.
 *
 *      If HOMEDIR_PREFIX and VACATION_PREFIX_HOMEENV are both set, then
 *      the HOME environment variable passed to VACATION_PATH will be
 *      the process's working directory (i.e., effectively, the prefixed
 *      home directory).
 *
 * Actions:
 *      list get put delete init
 *
 * Syntax:
 *      squirrelmail_autoresponder_forwarder_proxy server user action source destination
 */

#if HAVE_CONFIG_H
#include "config.h"
#else
#define HAVE_SYS_TYPES_H
#define HAVE_SYS_STAT_H
#define HAVE_UNISTD_H
#define HAVE_FCNTL_H
#define HAVE_STDLIB_H
#define HAVE_STRINGS_H
#define HAVE_STRING_H
#define HAVE_CRYPT_H
#define HAVE_PWD_H
#define HAVE_GRP_H
#define HAVE_ERRNO_H
#define HAVE_LIBGEN_H
#define HAVE_SHADOW_H
#endif /* HAVE_CONFIG_H */

#define BUFSIZE 512

#ifdef HAVE_SYS_TYPES_H
#include <sys/types.h>
#endif /* HAVE_SYS_TYPES_H */
#ifdef HAVE_SYS_STAT_H
#include <sys/stat.h>
#endif /* HAVE_SYS_STAT_H */
#ifdef HAVE_UNISTD_H
#include <unistd.h>
#endif /* HAVE_UNISTD_H */
#ifdef HAVE_FCNTL_H
#include <fcntl.h>
#endif /* HAVE_FCNTL_H */
#ifdef HAVE_STDLIB_H
#include <stdlib.h>
#endif /* HAVE_STDLIB_H */
#include <stdio.h>
#ifdef HAVE_STRINGS_H
#include <strings.h>
#endif /* HAVE_STRINGS_H */
#ifdef HAVE_STRINGS_H
#include <string.h>
#endif /* HAVE_STRING_H */
#ifdef HAVE_CRYPT_H
#include <crypt.h>
#endif /* HAVE_CRYPT_H */
#ifdef HAVE_PWD_H
#include <pwd.h>
#endif /* HAVE_PWD_H */
#ifdef HAVE_GRP_H
#include <grp.h>
#endif /* HAVE_GRP_H */
#ifdef HAVE_ERRNO_H
#include <errno.h>
#endif /* HAVE_ERRNO_H */
#ifdef HAVE_LIBGEN_H
#include <libgen.h>
#endif /* HAVE_LIBGEN_H */

/*
 * Defaults
 */
#ifndef LOCAL_FILEMODE
#define LOCAL_FILEMODE 0600
#endif
#ifndef REMOTE_FILEMODE
#define REMOTE_FILEMODE 0600
#endif
#ifndef REMOTE_HOMEDIRMODE
#define REMOTE_HOMEDIRMODE 0700
#endif
#ifndef PROG_UMASK
#define PROG_UMASK 0077 & ~(REMOTE_FILEMODE|LOCAL_FILEMODE|REMOTE_HOMEDIRMODE)
#endif
#if defined(NO_WEBUSER)
#undef WEBUSER
#elif !defined(WEBUSER)
#define WEBUSER "apache"
#endif /* !NO_WEBUSER, !defined(WEBUSER) */

#if defined(USESHADOW) && defined(HAVE_SHADOW_H)
#include <shadow.h>
#endif /* defined(USESHADOW) && defined(HAVE_SHADOW_H) */

#ifdef USEPAM
#include <security/pam_appl.h>
#ifndef PAMSERVICE
#define PAMSERVICE "squirrelmail_autoresponder_forwarder_proxy"
#endif /* PAMSERVICE */
#endif /* USEPAM */

#if !defined(ROOT_ALLOWED) && !defined(MIN_UID)
#define MIN_UID 1
#endif /* defined(ROOT_ALLOWED) && !defined(MIN_UID) */

#if !defined(REMOTEFILE_OKCHARS) && !defined(REMOTEFILE_OKCHARS_ANY)
#define REMOTEFILE_OKCHARS \
  "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.+-_"
#endif /* !defined(REMOTEFILE_OKCHARS) && !defined(REMOTEFILE_OKCHARS_ANY) */

#define SERVER  1
#define USER    2
#define ACTION  3
#define SRC     4
#define DEST    5

/* Exit status */
#define ERR_OK          0       /* no error */
#define ERR_NOTFOUND    1       /* file not found */
#define ERR_BADPASS     32      /* bad password */
#define ERR_USAGE       33      /* usage error */
#define ERR_RESTRICTED  34      /* not allowed to use this program */
#define ERR_REMOTEFILE  35      /* illegal remote filename */
#define ERR_LOCALFILE   36      /* illegal local filename */
#define ERR_CONFIG      37      /* global configuration problem */
#define ERR_USER        38      /* problem with this user */
#define ERR_HOME        39      /* problem accessing home directory */
#define ERR_SOURCEFILE  40      /* problem opening/stat()ing source file */
#define ERR_DESTFILE    41      /* problem opening/deleting dest file */
#define ERR_COPYFILE    42      /* problem copying file contents */
#define ERR_UNLINK      43      /* problem unlinking file */
#define ERR_FILETYPE    44      /* not a regular file */
#define ERR_EXEC        45      /* exec() of vacation program failed */
#define ERR_NOTSUPPORTED 46     /* feature not enabled */
#define ERR_PRIVILEGE   125     /* unexpected privileges problem */
#define ERR_UNEXPECTED  126     /* other unexpected error */

static uid_t orig_uid = 0;
static gid_t orig_gid = 0;

#ifdef SILENT
#define PRINTERROR2(format, arg1)
#define PRINTERROR3(format, arg1, arg2)
#define PRINTSYSERROR(e, s)
#else /* SILENT */
#define PRINTERROR2(format, arg1) fprintf(stderr, format, arg1)
#define PRINTERROR3(format, arg1, arg2) fprintf(stderr, format, arg1, arg2)
#define PRINTSYSERROR(e, s) fprintf(stderr, "%s: %s", strerror(e), s)
#endif /* !SILENT */

/* report an error and exit */
void fail(int sts, char *message)
{
  PRINTERROR2("%s\n", message);
  exit(sts);
}
/* report an error including the system error status, and exit */
void efail(int sts, char *message)
{
  char *syserr;

  syserr = strerror(errno);
  if (!syserr) {
      syserr = "Unknown error";
  }
  PRINTERROR3("%s: %s\n", message, syserr);
  exit(sts);
}

/*
 * Set effective userid and effective groupid.
 *
 * Real uid is always set to 0 to ensure that the IDs can be changed
 * again later.
 */
int set_eugid(uid_t uid, gid_t gid)
{
  /* need euid=root to set ids */
  if (setreuid(0, 0) < 0) {
      return -1;
  }
  if (setegid(gid) < 0) {
      return -1;
  }
  if (setreuid(0, uid) < 0) {
      return -1;
  }
  return 0;
}

/* this PAM code is mostly taken from inn2.0-beta */

#ifdef USEPAM
static int pass_conv(int num_msg, const struct pam_message **msgm,
        struct pam_response **response, void *appdata_ptr)
{
  int i;

  *response = malloc(num_msg * sizeof(struct pam_response));
  if (*response == NULL)
      return PAM_CONV_ERR;
  for (i = 0; i < num_msg; i++) {
      (*response)[i].resp = (void *) strdup((char *)appdata_ptr);
      (*response)[i].resp_retcode = 0;
  }
  return PAM_SUCCESS;
}

static int auth_pam(char *username, char *password)
{
  pam_handle_t *pamh;
  struct pam_conv conv;
  int status;

  conv.conv = pass_conv;
  conv.appdata_ptr = password;
  status = pam_start(PAMSERVICE, username, &conv, &pamh);
  if (status != PAM_SUCCESS) {
      PRINTERROR2("pam_start failed: %s\n", pam_strerror(pamh, status));
      return 0;
  }
  status = pam_authenticate(pamh, PAM_SILENT);
  if (status != PAM_SUCCESS) {
      PRINTERROR2("pam_authenticate failed: %s\n", pam_strerror(pamh, status));
      return 0;
  }
  status = pam_acct_mgmt(pamh, PAM_SILENT);
  if (status != PAM_SUCCESS) {
      PRINTERROR2("pam_acct_mgmt failed: %s\n", pam_strerror(pamh, status));
      return 0;
  }
  status = pam_end(pamh, status);
  if (status != PAM_SUCCESS) {
      PRINTERROR2("pam_end failed: %s\n", pam_strerror(pamh, status));
      return 0;
  }

  /* If we get to here, the user successfully authenticated. */
  return 1;
}
#endif /* USEPAM */


/*
 * Assumes the user's credentials and chdir()s to his home directory.
 * Should be called with euid == 0.
 * If compiled with CREATE_HOMEDIR and missing_ok is true,
 * also creates the directory if it doesn't exist.
 * Returns 0 on success, or 1 if home directory doesn't exist and
 * missing_ok is false; otherwise, fails and exits.
 */
int gohome(struct passwd *pw, int missing_ok)
{
  char *h;
#ifdef CREATE_HOMEDIR
  struct stat statbuf;
#endif

  if (pw->pw_dir == NULL || pw->pw_dir[0] != '/') {
      fail(ERR_USER, "gohome: invalid home directory");
  }

#ifdef HOMEDIR_PREFIX
  /* chdir to HOMEDIR_PREFIX while still privileged */
  if (chdir(HOMEDIR_PREFIX) < 0) {
      efail(ERR_CONFIG, "gohome: chdir(" HOMEDIR_PREFIX ")");
  }
  h = pw->pw_dir + strspn(pw->pw_dir, "/");
#else /* HOMEDIR_PREFIX */
  h = pw->pw_dir;
#endif /* HOMEDIR_PREFIX */

#ifdef CREATE_HOMEDIR
  if (missing_ok && lstat(h, &statbuf) < 0 && errno == ENOENT) {
      /* try to create it */
      if (mkdir(h, REMOTE_HOMEDIRMODE) < 0) {
          efail(ERR_HOME, "gohome: mkdir(HOME)");
      }
      if (chown(h, pw->pw_uid, pw->pw_gid) < 0) {
          rmdir(h);   /* remove it (if possible) */
          fail(ERR_HOME, "gohome: chown(HOME) failed after mkdir()");
      }
  }
#endif

  if (set_eugid(pw->pw_uid, pw->pw_gid) != 0) {
      efail(ERR_PRIVILEGE, "gohome: cannot set effective uid to target");
  }
  if (chdir(h) < 0) {
      /* File not found */
      if (errno == ENOENT && !missing_ok) {
          return 1;
      } else {
          efail(ERR_HOME, "gohome: chdir(HOME)");
      }
  }
  return 0;
}

void remoteok(char *rmtfile)
{
#ifdef REMOTE_SUBDIRECTORIES_OK
  int rmtfilelen;
#endif /* REMOTE_SUBDIRECTORIES_OK */

  if (rmtfile[0] == '\0') {
      fail(ERR_REMOTEFILE, "Remote filename cannot be empty");
  }
#ifdef REMOTE_SUBDIRECTORIES_OK
  /*
   * slashes are ok, but we disallow traversing parent directories;
   * i.e., the path cannot start with ../, contain /../, or end in /..
   */
  if (rmtfile[0] == '/') {
      fail(ERR_REMOTEFILE, "Remote filename cannot be absolute");
  }
  rmtfilelen = strlen(rmtfile);

  if (strncmp(rmtfile, "../", 3) == 0
   || strstr(rmtfile, "/../") != NULL
   || (rmtfilelen >= 3 && strncmp(&rmtfile[rmtfilelen - 3], "/..", 3) == 0)
  ) {
      fail(ERR_REMOTEFILE, "Remote filename cannot traverse ..");
  }
#else /* REMOTE_SUBDIRECTORIES_OK */
  /* no slashes allowed at all. */
  if (strchr(rmtfile, '/') != NULL) {
      fail(ERR_REMOTEFILE, "Remote filename cannot contain /");
  }

#if defined(REMOTE_DOTFILES_OK)
  if (rmtfile[0] != '.') {
      fail(ERR_REMOTEFILE, "Remote filename must start with a dot");
  }
#elif !defined(REMOTE_NORESTRICT)
#define FORWARD_PREFIX ".forward"
#define VACATION_PREFIX ".vacation"
  /* filename must start with .forward or .vacation */
  if (strncmp(rmtfile, FORWARD_PREFIX, strlen(FORWARD_PREFIX)) != 0
   && strncmp(rmtfile, VACATION_PREFIX, strlen(VACATION_PREFIX)) != 0) {
      fail(ERR_REMOTEFILE, "Only " FORWARD_PREFIX "* or "
          VACATION_PREFIX "* allowed in remote filename"
      );
  }
#endif /* !REMOTE_DOTFILES_OK, !REMOTE_NORESTRICT */
#endif /* REMOTE_SUBDIRECTORIES_OK */

#ifdef REMOTEFILE_OKCHARS
  if (strspn(rmtfile, REMOTEFILE_OKCHARS) != strlen(rmtfile)) {
      fail(ERR_REMOTEFILE, "Remote filename contains illegal character(s)");
  }
#endif
  if (strcmp(rmtfile, "..") == 0) {
      fail(ERR_REMOTEFILE, "Remote filename cannot be ..");
  }
}

void localok(char *localfile)
{
  if (localfile[0] != '/') {
      fail(ERR_LOCALFILE, "Local filename must be absolute pathname");
  }
}

int do_list(struct passwd *pw, char *filename)
{
  struct stat statbuf;

  remoteok(filename);
  if (gohome(pw, 0) != 0) {
      return ERR_NOTFOUND;
  }

  if (stat(filename, &statbuf) == 0) {
      if (! S_ISREG(statbuf.st_mode)) {
          fail(ERR_FILETYPE, "target is not a regular file");
      }
  } else if (errno == ENOENT) {
      return ERR_NOTFOUND;
  } else {
      efail(ERR_SOURCEFILE, "stat()");
  }
  return ERR_OK;
}

/*
 * copy contents of srcfd to destfd, then close both files
 */
char *copyfile(int srcfd, int destfd)
{
  ssize_t n;
  char buf[BUFSIZE];

  while ((n = read(srcfd, buf, BUFSIZE)) > 0) {
      if (write(destfd, buf, n) != n) {
          return "Error while writing file";
      }
  }
  if (n < 0) {
      return "Error reading file";
  }
  if (close(srcfd) < 0 || close(destfd) < 0) {
      return "Error closing files";
  }
  return NULL;
}

int do_get(struct passwd *pw, char *src, char *dest)
{
  int srcfd, destfd;
  char *copysts;

  remoteok(src);
  localok(dest);

  if (gohome(pw, 0) != 0) {
      return ERR_NOTFOUND;
  }
  srcfd = open(src, O_RDONLY);
  if (srcfd < 0) {
      if (errno == ENOENT) {
          return ERR_NOTFOUND;
      } else {
          efail(ERR_SOURCEFILE, "get: open() source file");
      }
  }

  /* open destination file as calling user */
  if (set_eugid(orig_uid, orig_gid) != 0) {
      efail(ERR_PRIVILEGE, "get: setuid(ruid)");
  }
  destfd = open(dest, O_WRONLY | O_CREAT | O_TRUNC, LOCAL_FILEMODE);
  if (destfd < 0) {
      efail(ERR_DESTFILE, "get: open() destination file");
  }

  /* copy contents */
  if ((copysts = copyfile(srcfd, destfd)) != NULL) {
      unlink(dest);   /* remove incomplete destination file */
      fail(ERR_COPYFILE, copysts);
  }
  return ERR_OK;
}

int do_put(struct passwd *pw, char *src, char *dest)
{
  int srcfd, destfd;
  char *copysts;

  localok(src);
  remoteok(dest);

  /* open source file as calling user */
  if (set_eugid(orig_uid, orig_gid) != 0) {
      efail(ERR_PRIVILEGE, "put: setuid(ruid)");
  }
  srcfd = open(src, O_RDONLY);
  if (srcfd < 0) {
      if (errno == ENOENT) {
          return ERR_NOTFOUND;
      } else {
          efail(ERR_SOURCEFILE, "put: open() source file");
      }
  }

  /* need euid=0 again to call gohome() */
  if (set_eugid(0, 0) != 0) {
      efail(ERR_PRIVILEGE, "put: cannot revert euid to superuser");
  }
  gohome(pw, 1);
  /* open destination file as target user */
  destfd = open(dest, O_WRONLY | O_CREAT | O_TRUNC, REMOTE_FILEMODE);
  if (destfd < 0) {
      efail(ERR_DESTFILE, "put: open() destination file");
  }

  /* copy contents */
  if ((copysts = copyfile(srcfd, destfd)) != NULL) {
      unlink(dest);   /* remove incomplete destination file */
      fail(ERR_COPYFILE, copysts);
  }
  return ERR_OK;
}

int do_delete(struct passwd *pw, char *filename)
{
  struct stat statbuf;

  remoteok(filename);

  if (gohome(pw, 0) != 0) {
      return ERR_NOTFOUND;
  }

  if (lstat(filename, &statbuf) == 0) {
      if (S_ISREG(statbuf.st_mode)
#ifdef S_ISLNK
       || S_ISLNK(statbuf.st_mode)
#endif /* S_ISLNK */
      ) {
          if (unlink(filename) < 0) {
              efail(ERR_UNLINK, "delete: unlink()");
          }
      } else {
          fail(ERR_FILETYPE, "delete: target is not a regular file");
      }
  } else if (errno == ENOENT) {
      return ERR_NOTFOUND;
  } else {
      efail(ERR_DESTFILE, "delete: stat()");
  }
  return ERR_OK;
}

/*
 * return a string suitable for inclusion in an environment (VAR=value)
 */
char *mkenv(char *var, char *value) {
  size_t newvarlen;
  int n;
  char *newvar;

  newvarlen = strlen(var) + strlen(value) + 2;
  newvar = malloc(newvarlen);
  if (newvar == NULL) {
      fail(ERR_UNEXPECTED, "mkenv: malloc failed");
  }
  n = snprintf(newvar, newvarlen, "%s=%s", var, value);
  if (n < 0 || n > newvarlen) {
      fail(ERR_UNEXPECTED, "mkenv: snprintf failed");
  }
  return newvar;
}

#ifdef VACATION_PATH

/*
 * Run "vacation -I" with a very basic environment (PATH, HOME, USER)
 */
void do_init(struct passwd *pw)
{
  int i;
  char *vac_envp[4];
  char *vac_argv[4];

#ifdef HOMEDIR_PREFIX
  char curdirbuf[BUFSIZE], *curdir;
#endif /* HOMEDIR_PREFIX */

  gohome(pw, 1);

  /* need euid=root to set real ids correctly */
  if (setreuid(0, 0) < 0) {
      efail(ERR_PRIVILEGE, "init: seteuid(0)");
  }

  if (setgid(pw->pw_gid) < 0) {
      efail(ERR_PRIVILEGE, "init: setgid()");
  }
  if (setuid(pw->pw_uid) < 0) {
      efail(ERR_PRIVILEGE, "init: setuid()");
  }

  i = 0;

#ifndef VACATION_PATHENV
#define VACATION_PATHENV "/bin:/usr/bin"
#endif /* VACATION_PATHENV */
  vac_argv[i++] = VACATION_PATH;

#ifndef VACATION_INIT_FLAG
#define VACATION_INIT_FLAG "-I"
#endif /* VACATION_INIT_FLAG */
  vac_argv[i++] = VACATION_INIT_FLAG;

#ifdef VACATION_USERARG
  vac_argv[i++] = pw->pw_name;
#endif /* VACATION_USERARG */
  vac_argv[i++] = NULL;

  i = 0;
  vac_envp[i++] = "PATH=" VACATION_PATHENV;
#if defined(HOMEDIR_PREFIX) && defined(VACATION_PREFIX_HOMEENV)
  /*
   * Set HOME environment variable to the prefixed home directory.
   *
   * The vacation program will likely need to be customized.
   */
  curdir = getcwd(curdirbuf, BUFSIZE);
  if (!curdir) {
      efail(ERR_UNEXPECTED, "init: getcwd()");
  }
  vac_envp[i++] = mkenv("HOME", curdir);
#else /* defined(HOMEDIR_PREFIX) && defined(VACATION_PREFIX_HOMEENV) */
  vac_envp[i++] = mkenv("HOME", pw->pw_dir);
#endif /* !(defined(HOMEDIR_PREFIX) && defined(VACATION_PREFIX_HOMEENV)) */

  vac_envp[i++] = mkenv("USER", pw->pw_name);
  vac_envp[i++] = NULL;

  execve(VACATION_PATH, vac_argv, vac_envp);
  efail(ERR_EXEC, "init: execve()");
}
#else  /* VACATION_PATH */
void do_init(struct passwd *pw)
{
  fail(ERR_NOTSUPPORTED, "Wrapper was not compiled with init support");
}
#endif /* VACATION_PATH */

int main (int argc, char *argv[])
{
  int status;
  char *puid, *action;
  struct passwd *pw;
#ifndef USEPAM
  char *passfield, *testpwd;
#ifdef USESHADOW
  struct spwd *spw;
#endif /* USESHADOW */
#endif /* ifndef USEPAM */
  char passbuf[BUFSIZE];
  size_t passbuflen;

  umask(PROG_UMASK);
  /* Clear out supplementary group ids */
  if (setgroups(0, NULL) < 0) {
      efail(ERR_PRIVILEGE, "setgroups(0,NULL)");
  }
  orig_uid = getuid();
  orig_gid = getgid();

#ifdef WEBUSER
   /* WEBUSER is defined.
  Then we start with a check to see if the real user is
  the valid user (or root).
  We verify that WEBUSER exists even if we're running as root, to aid
  troubleshooting.
  */
  if ((pw=getpwnam(WEBUSER))==NULL) {
      fail(ERR_CONFIG, "Invalid webuser.");
  }
  if (orig_uid != 0) {
      if( pw->pw_uid != orig_uid ) {
          fail(ERR_RESTRICTED, "Invalid real user.");
      }
  }
#endif /* WEBUSER */

  if (argc < ACTION + 1) {
      fail(ERR_USAGE, "Incorrect usage");
  }

  /* read password from stdin */
  if (fgets(passbuf, BUFSIZE, stdin)==NULL) {
      fail(ERR_BADPASS, "Could not read password");
  }
  passbuflen = strlen(passbuf);
  if (passbuf[passbuflen-1] == '\n') {
      passbuf[passbuflen-1] = '\0';
  } else {
      fail(ERR_BADPASS, "Could not read password");
  }

  puid = argv[USER];

#ifndef ROOT_ALLOWED
  if (strcmp(puid, "root") == 0) {
      fail(ERR_USER, "root not allowed");
  }
#endif /* ROOT_ALLOWED */

  if ((pw=getpwnam(puid))==NULL) {
      fail(ERR_USER, "getpwnam() failed");
  }
#ifdef MIN_UID
  if (pw->pw_uid < MIN_UID) {
      fail(ERR_USER, "uid too small");
  }
#endif /* MIN_UID */

#ifdef USEPAM
  if (!auth_pam(puid, passbuf)) {
      fail(ERR_BADPASS, "PAM authentication failed");
  }
#else
#ifdef USESHADOW
#define PASSFILE "/etc/shadow"
  if ((spw=getspnam(puid))==NULL) {
      fail(ERR_USER, "getspnam() failed");
  }
  passfield = spw->sp_pwdp;
#else /* USESHADOW */
#define PASSFILE "/etc/passwd"
  passfield = pw->pw_passwd;
#endif /* USESHADOW */
#ifndef EMPTY_PASSWORD_OK
  if (passfield == NULL || passfield[0] == '\0') {
      fail(ERR_BADPASS, "Access not allowed for this account");
  }
#endif /* ifndef EMPTY_PASSWORD_OK */

  testpwd = crypt(passbuf, passfield);
  if (strcmp(testpwd, passfield) != 0) {
      fail(ERR_BADPASS, "Password does not match " PASSFILE);
  }
#endif /* !USEPAM */

  status = ERR_UNEXPECTED;
  action = argv[ACTION];

  if (strcmp(action, "list") == 0) {
      if (argc != SRC + 1) {
          fail(ERR_USAGE, "Incorrect usage");
      }
      status = do_list(pw, argv[SRC]);

  } else if (strcmp(action, "get") == 0) {
      if (argc != DEST + 1) {
          fail(ERR_USAGE, "Incorrect usage");
      }
      status = do_get(pw, argv[SRC], argv[DEST]);

  } else if (strcmp(action, "put") == 0) {
      if (argc != DEST + 1) {
          fail(ERR_USAGE, "Incorrect usage");
      }
      status = do_put(pw, argv[SRC], argv[DEST]);

  } else if (strcmp(action, "delete") == 0) {
      if (argc != SRC + 1) {
          fail(ERR_USAGE, "Incorrect usage");
      }
      status = do_delete(pw, argv[SRC]);

  } else if (strcmp(action, "init") == 0) {
      if (argc != ACTION + 1) {
          fail(ERR_USAGE, "Incorrect usage");
      }
      do_init(pw);

  } else {
      fail(ERR_USAGE, "Action not found");
  }
  exit(status);
}

