/* config.h.  Generated from config.h.in by configure.  */
/* config.h.in.  Generated from configure.ac by autoheader.  */

/* Create home directory if it doesn't exist */
/* #undef CREATE_HOMEDIR */

/* Empty passwords acceptable in passwd or shadow files */
/* #undef EMPTY_PASSWORD_OK */

/* Define to 1 if your system has a working `chown' function. */
#define HAVE_CHOWN 1

/* Define to 1 if you have the <crypt.h> header file. */
#define HAVE_CRYPT_H 1

/* Define to 1 if you have the <errno.h> header file. */
#define HAVE_ERRNO_H 1

/* Define to 1 if you have the <fcntl.h> header file. */
#define HAVE_FCNTL_H 1

/* Define to 1 if you have the `getspnam' function. */
#define HAVE_GETSPNAM 1

/* Define to 1 if you have the <grp.h> header file. */
#define HAVE_GRP_H 1

/* Define to 1 if you have the <inttypes.h> header file. */
#define HAVE_INTTYPES_H 1

/* Define to 1 if you have the `crypt' library (-lcrypt). */
#define HAVE_LIBCRYPT 1

/* Define to 1 if you have the <libgen.h> header file. */
#define HAVE_LIBGEN_H 1

/* Define to 1 if you have the `pam' library (-lpam). */
/* #undef HAVE_LIBPAM */

/* Define to 1 if `lstat' has the bug that it succeeds when given the
   zero-length file name argument. */
/* #undef HAVE_LSTAT_EMPTY_STRING_BUG */

/* Define to 1 if your system has a GNU libc compatible `malloc' function, and
   to 0 otherwise. */
#define HAVE_MALLOC 1

/* Define to 1 if you have the <memory.h> header file. */
#define HAVE_MEMORY_H 1

/* Define to 1 if you have the `mkdir' function. */
#define HAVE_MKDIR 1

/* Define to 1 if you have the <pwd.h> header file. */
#define HAVE_PWD_H 1

/* Define to 1 if you have the `rmdir' function. */
#define HAVE_RMDIR 1

/* Define to 1 if you have the <shadow.h> header file. */
#define HAVE_SHADOW_H 1

/* Define to 1 if `stat' has the bug that it succeeds when given the
   zero-length file name argument. */
/* #undef HAVE_STAT_EMPTY_STRING_BUG */

/* Define to 1 if you have the <stdint.h> header file. */
#define HAVE_STDINT_H 1

/* Define to 1 if you have the <stdlib.h> header file. */
#define HAVE_STDLIB_H 1

/* Define to 1 if you have the `strdup' function. */
#define HAVE_STRDUP 1

/* Define to 1 if you have the <strings.h> header file. */
#define HAVE_STRINGS_H 1

/* Define to 1 if you have the <string.h> header file. */
#define HAVE_STRING_H 1

/* Define to 1 if you have the `strspn' function. */
#define HAVE_STRSPN 1

/* Define to 1 if you have the <syslog.h> header file. */
#define HAVE_SYSLOG_H 1

/* Define to 1 if you have the <sys/stat.h> header file. */
#define HAVE_SYS_STAT_H 1

/* Define to 1 if you have the <sys/types.h> header file. */
#define HAVE_SYS_TYPES_H 1

/* Define to 1 if you have <sys/wait.h> that is POSIX.1 compatible. */
#define HAVE_SYS_WAIT_H 1

/* Define to 1 if you have the <unistd.h> header file. */
#define HAVE_UNISTD_H 1

/* Path to prefix to home directory */
/* #undef HOMEDIR_PREFIX */

/* Define to 1 if `lstat' dereferences a symlink specified with a trailing
   slash. */
#define LSTAT_FOLLOWS_SLASHED_SYMLINK 1

/* Don't allow uids less than this minimum */
/* #undef MIN_UID */

/* Disable WEBUSER restriction */
/* #undef NO_WEBUSER */

/* Name of package */
/* #undef PACKAGE */

/* Define to the address where bug reports for this package should be sent. */
#define PACKAGE_BUGREPORT "squirrelmail-plugins@lists.sourceforge.net"

/* Define to the full name of this package. */
#define PACKAGE_NAME "SquirrelMail Local User Autoresponder and Mail Forwarder SUID Backend"

/* Define to the full name and version of this package. */
#define PACKAGE_STRING "SquirrelMail Local User Autoresponder and Mail Forwarder SUID Backend 3.0"

/* Define to the one symbol short name of this package. */
#define PACKAGE_TARNAME "squirrelmail-local-user-autoresponder-and-mail-forwarder-suid-backend"

/* Define to the version of this package. */
#define PACKAGE_VERSION "3.0"

/* service name for use with PAM */
/* #undef PAMSERVICE */

/* Limit characters which may appear in a remote filename to these */
/* #undef REMOTEFILE_OKCHARS */

/* Do not restrict characters in remote filenames */
/* #undef REMOTEFILE_OKCHARS_ANY */

/* Allow dotfiles other than .forward and .vacation */
/* #undef REMOTE_DOTFILES_OK */

/* perms to use when creating remote files */
/* #undef REMOTE_FILEMODE */

/* perms to use when creating home directories */
/* #undef REMOTE_HOMEDIRMODE */

/* Allow files which do not begin with a "." */
/* #undef REMOTE_NORESTRICT */

/* Allow descending into subdirectories */
/* #undef REMOTE_SUBDIRECTORIES_OK */

/* Never send messages to stderr */
/* #undef SILENT */

/* Define to 1 if the `S_IS*' macros in <sys/stat.h> do not work properly. */
/* #undef STAT_MACROS_BROKEN */

/* Define to 1 if you have the ANSI C header files. */
#define STDC_HEADERS 1

/* Use PAM for authentication */
/* #undef USEPAM */

/* Use shadow password database for authentication */
#define USESHADOW 1

/* Flag to pass to the vacation program */
/* #undef VACATION_INIT_FLAG */

/* Path to the vacation program */
/* #undef VACATION_PATH */

/* PATH to be used by the vacation program */
/* #undef VACATION_PATHENV */

/* Whether to pass the username as an argument to the vacation program */
/* #undef VACATION_USERARG */

/* Version number of package */
/* #undef VERSION */

/* Username allowed to run the program */
/* #undef WEBUSER */

/* Define to empty if `const' does not conform to ANSI C. */
/* #undef const */

/* Define to 'unsigned int' if <grp.h> does not define. */
/* #undef gid_t */

/* Define to rpl_malloc if the replacement function should be used. */
/* #undef malloc */

/* Define to `unsigned' if <sys/types.h> does not define. */
/* #undef size_t */

/* Define to 'int' if <sys/types.h> does not define. */
/* #undef ssize_t */

/* Define to `int' if <sys/types.h> doesn't define. */
/* #undef uid_t */
