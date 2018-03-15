#                                               -*- Autoconf -*-
# Process this file with autoconf to produce a configure script.

AC_PREREQ(2.50)
AC_INIT(SquirrelMail Local User Autoresponder and Mail Forwarder SUID Backend, 3.0, squirrelmail-plugins@lists.sourceforge.net)
AC_CONFIG_SRCDIR([squirrelmail_autoresponder_forwarder_proxy.c])
AM_INIT_AUTOMAKE
AC_CONFIG_HEADER([config.h])

# Checks for programs.
AC_PROG_CC


# Checks for header files.
AC_HEADER_STDC
AC_HEADER_SYS_WAIT
AC_CHECK_HEADERS([crypt.h fcntl.h shadow.h stdlib.h string.h strings.h syslog.h unistd.h pwd.h grp.h errno.h libgen.h])

# Checks for typedefs, structures, and compiler characteristics.
AC_HEADER_STAT
AC_C_CONST
AC_TYPE_UID_T
AC_CHECK_TYPE([gid_t],,
   [AC_DEFINE_UNQUOTED([gid_t], [unsigned int],
   [Define to 'unsigned int' if <grp.h> does not define.])])
AC_TYPE_SIZE_T
AC_CHECK_TYPE([ssize_t],,
   [AC_DEFINE_UNQUOTED([ssize_t], [int],
   [Define to 'int' if <sys/types.h> does not define.])])

# Checks for library functions.
AC_FUNC_CHOWN
AC_FUNC_LSTAT
AC_FUNC_LSTAT_FOLLOWS_SLASHED_SYMLINK
AC_FUNC_MALLOC
AC_FUNC_STAT
AC_CHECK_FUNCS([mkdir rmdir strdup strspn])

AC_ARG_ENABLE(auth,
   [  --enable-auth=shadow           check encrypted passwords in /etc/shadow
  --enable-auth=passwd           check encrypted passwords in /etc/passwd
  --enable-auth=pam              use PAM for authentication],
   [auth="$enableval"],
   [auth=check])

AC_ARG_ENABLE(pamservice,
   [  --enable-pamservice=           specify service name for use with PAM],
   [pamservice="$enableval" ],
   [])

if test -n "$pamservice"; then
   case "$auth" in
       check)
           auth=pam
           ;;
       pam | PAM)
           ;;
       *)
           echo "Cannot combine --enable-auth=$auth and --enable-pamservice"
           exit 1
           ;;
   esac
   AC_DEFINE_UNQUOTED([PAMSERVICE], ["$pamservice"],
   [service name for use with PAM])
elif test "$auth" = "check"; then
   AC_CHECK_FUNCS([getspnam], [auth=shadow], [auth=passwd])
fi

case "$auth" in
passwd)
   # default defines
   AC_CHECK_LIB(crypt, crypt)
   ;;
shadow)
   AC_DEFINE([USESHADOW], [1],
       [Use shadow password database for authentication]
   )
   AC_CHECK_LIB(crypt, crypt)
   ;;
pam | PAM)
   AC_DEFINE([USEPAM], [1], [Use PAM for authentication])
   AC_CHECK_LIB(pam, pam_start)
   ;;
*)
   echo "Argument to --enable-auth must be passwd, shadow, or pam"
   exit 1
   ;;
esac

AC_ARG_ENABLE(vacation,
   [  --enable-vacation@<:@=PATH@:>@       enable running vacation command 
                                 via "init" command],
   [vacpath="$enableval"],
   [vacpath=check])

AC_ARG_ENABLE(vacation-init-flag,
   [  --enable-vacation-init-flag@<:@=FLAG@:>@  flag to pass to vacation (e.g., -I)],
   [vaciflag="$enableval"],
   [vaciflag=no])

AC_ARG_ENABLE(vacation-userarg,
   [  --enable-vacation-userarg      to pass the username as an argument
                                 to the vacation program],
   [vacuserarg="$enableval"],
   [vacuserarg=no])

AC_ARG_ENABLE(vacation-pathenv,
   [  --enable-vacation-pathenv@<:@=DIR:DIR...@:>@  PATH to pass to vacation],
   [vacpathenv="$enableval"],
   [vacpathenv=no])

# If any vacation-related options are given, imply --enable-vacation=yes
if test "$vacpath" = "check"; then
   if test "$vaciflag" != "no" -o "$vacpathenv" != "no" -o "$vacuserarg" != "no"; then
       vacpath=yes
   else
       vacpath=no
   fi
fi

if test "$vacpath" = "yes"; then
   # Default is /usr/bin/vacation if it exists
   if test -x /usr/bin/vacation; then
       vacpath=/usr/bin/vacation
   else
       echo "Use --enable-vacation= to set the path to the vacation program."
       exit 1
   fi
fi

if test "$vacpath" != "no"; then
   AC_DEFINE_UNQUOTED([VACATION_PATH], ["$vacpath"],
   [Path to the vacation program])
fi
if test "$vaciflag" != "no"; then
   AC_DEFINE_UNQUOTED([VACATION_INIT_FLAG], ["$vacflag"],
   [Flag to pass to the vacation program])
fi
if test "$vacpathenv" != "no"; then
   AC_DEFINE_UNQUOTED([VACATION_PATHENV], ["$vacpathenv"],
   [PATH to be used by the vacation program])
fi
if test "$vacuserarg" != "no"; then
   AC_DEFINE([VACATION_USERARG], [1],
   [Whether to pass the username as an argument to the vacation program])
fi

# enable-webuser defaults to yes (apache) unless --disable-webuser is
# given explicitly
AC_ARG_ENABLE(webuser,
   [  --enable-webuser@<:@=USER@:>@        user allowed to run the program
  --disable-webuser              permit any user (not recommended)],
   [webuser="$enableval"],
   [webuser="yes"])

if test "$webuser" = "no"; then
   AC_DEFINE([NO_WEBUSER], [1], [Disable WEBUSER restriction])
elif test "$webuser" != "yes"; then
   AC_DEFINE_UNQUOTED([WEBUSER], ["$webuser"],
   [Username allowed to run the program])
fi

AC_ARG_ENABLE(empty-passwords,
   [  --enable-empty-passwords       permit empty passwords in passwd or
                                 shadow files (not recommended)],
   [emptypass="$enableval" ],
   [])

if test "$emptypass" = "yes"; then
   AC_DEFINE([EMPTY_PASSWORD_OK], [1],
   [Empty passwords acceptable in passwd or shadow files])
fi

AC_ARG_ENABLE(min-uid,
   [  --enable-min-uid=UID           don't allow uids less than this minimum],
   [min_uid="$enableval"],
   [min_uid="no"])
if test "$min_uid" = "yes"; then
   echo "An argument is required with --enable-min-uid"
   exit 1
elif test "$min_uid" != "no"; then
   AC_DEFINE_UNQUOTED([MIN_UID], [$min_uid],
       [Don't allow uids less than this minimum]
   )
fi

AC_ARG_ENABLE(silent,
   [  --enable-silent                never send messages to stderr],
   [silent="$enableval"],
   [silent="no"])
if test "$silent" != "no"; then
   AC_DEFINE([SILENT], [1],
       [Never send messages to stderr]
   )
fi

AC_ARG_ENABLE(filenames,
   [  --enable-filenames@<:@=VALUE@:>@     "restricted" (default)
                                     requires remote filenames to begin with 
                                     ".forward" or ".vacation"
                                 "dotfiles"
                                     requires remote filenames to begin with "."
                                 "any"
                                     permits remote filenames to begin with any 
                                     legal character
                                 "subdirectories" (not recommended) 
                                     allows subdirectories to be accessed
                                     (e.g., mail/.forward)],
   [fn_restrict="$enableval"],
   [fn_restrict=restricted])
case "$fn_restrict" in
   restricted|yes|no)
       ;;
   dotfiles)
       AC_DEFINE([REMOTE_DOTFILES_OK], [1],
       [Allow dotfiles other than .forward and .vacation])
       ;;
   any)
       AC_DEFINE([REMOTE_NORESTRICT], [1],
       [Allow files which do not begin with a "."])
       ;;
   subdirectories)
       AC_DEFINE([REMOTE_SUBDIRECTORIES_OK], [1],
       [Allow descending into subdirectories])
       ;;
   *)
       echo " --enable-filenames must be one of: restricted, dotfiles, any, subdirectories"
       exit 1
esac

AC_ARG_ENABLE(homedir-prefix,
   [  --enable-homedir-prefix=PATH   (advanced) path to be prefixed to home
                                 directories],
   [homedir_pref="$enableval"],
   [])
if test -n "$homedir_pref"; then
 case "$homedir_pref" in
 /*)
   AC_DEFINE_UNQUOTED([HOMEDIR_PREFIX], ["$homedir_pref"],
   [Path to prefix to home directory])
   ;;
 *)
   echo "Homedir-prefix must be an absolute pathname."
   exit 1
   ;;
 esac
fi

AC_ARG_ENABLE(create-homedir,
   [  --enable-create-homedir        (advanced) create home directory 
                                 if it doesn't exist],
   [createhome="$enableval"],
   [createhome="no"])

if test "$createhome" != "no"; then
   AC_DEFINE([CREATE_HOMEDIR], [1],
       [Create home directory if it doesn't exist]
   )
fi

AC_ARG_ENABLE(remote-filemode,
   [  --enable-remote-filemode=mode  set perms to use when creating remote files],
   [remotefm="$enableval"],
   [remotefm="no"])
if test "$remotefm" = "yes"; then
   echo "An argument is required with --enable-remote-filemode"
   exit 1
elif test "$remotefm" != "no"; then
   AC_DEFINE_UNQUOTED([REMOTE_FILEMODE], [0$remotefm],
      [perms to use when creating remote files]
   )
fi

AC_ARG_ENABLE(homedirmode,
   [  --enable-homedirmode=mode      (with --enable-create-homedir) set perms to
                                 use when creating home directories],
   [remotehdm="$enableval"],
   [remotehdm="no"])
if test "$remotehdm" = "yes"; then
   echo "An argument is required with --enable-homedirmode"
   exit 1
elif test "$remotehdm" != "no"; then
   AC_DEFINE_UNQUOTED([REMOTE_HOMEDIRMODE], [0$remotehdm],
      [perms to use when creating home directories]
   )
fi

AC_ARG_ENABLE(remote-chars,
   [  --enable-remote-chars=CHARSET  (advanced) change the set of characters which
                                 may appear in a remote filename to this list
  --disable-remote-chars         (not recommended) do not restrict characters
                                 in remote filenames],
   [remote_chars="$enableval"],
   [remote_chars="yes"])

case "$remote_chars" in
   yes) # default
       ;;
   no)
       AC_DEFINE([REMOTEFILE_OKCHARS_ANY], [1],
           [Do not restrict characters in remote filenames]
       )
       ;;
   *)
       AC_DEFINE_UNQUOTED([REMOTEFILE_OKCHARS], ["$remote_chars"],
           [Limit characters which may appear in a remote filename to these]
       )
       ;;
esac

AC_CONFIG_FILES([Makefile])
AC_OUTPUT

