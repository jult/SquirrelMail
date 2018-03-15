<?php

// see bottom for possibly more up-to-date list


global $mimetypes;


// apache's list of mimie types
//
$mimetypes = array(
    'ez'        => 'application/andrew-inset',
    'hqx'        => 'application/mac-binhex40',
    'cpt'        => 'application/mac-compactpro',
    'doc'        => 'application/msword',
    'bin'        => 'application/octet-stream',
    'dms'        => 'application/octet-stream',
    'lha'        => 'application/octet-stream',
    'lzh'        => 'application/octet-stream',
    'exe'        => 'application/octet-stream',
    'class'        => 'application/octet-stream',
    'so'        => 'application/octet-stream',
    'dll'        => 'application/octet-stream',
    'oda'        => 'application/oda',
    'pdf'        => 'application/pdf',
    'ai'        => 'application/postscript',
    'eps'        => 'application/postscript',
    'ps'        => 'application/postscript',
    'smi'        => 'application/smil',
    'smil'        => 'application/smil',
    'mif'        => 'application/vnd.mif',
    'xls'        => 'application/vnd.ms-excel',
    'ppt'        => 'application/vnd.ms-powerpoint',
    'wbxml'        => 'application/vnd.wap.wbxml',
    'wmlc'        => 'application/vnd.wap.wmlc',
    'wmlsc'        => 'application/vnd.wap.wmlscriptc',
    'bcpio'        => 'application/x-bcpio',
    'vcd'        => 'application/x-cdlink',
    'pgn'        => 'application/x-chess-pgn',
    'cpio'        => 'application/x-cpio',
    'csh'        => 'application/x-csh',
    'dcr'        => 'application/x-director',
    'dir'        => 'application/x-director',
    'dxr'        => 'application/x-director',
    'dvi'        => 'application/x-dvi',
    'spl'        => 'application/x-futuresplash',
    'gtar'        => 'application/x-gtar',
    'hdf'        => 'application/x-hdf',
    'js'        => 'application/x-javascript',
    'skp'        => 'application/x-koan',
    'skd'        => 'application/x-koan',
    'skt'        => 'application/x-koan',
    'skm'        => 'application/x-koan',
    'latex'        => 'application/x-latex',
    'nc'        => 'application/x-netcdf',
    'cdf'        => 'application/x-netcdf',
    'sh'        => 'application/x-sh',
    'shar'        => 'application/x-shar',
    'swf'        => 'application/x-shockwave-flash',
    'sit'        => 'application/x-stuffit',
    'sv4cpio'    => 'application/x-sv4cpio',
    'sv4crc'    => 'application/x-sv4crc',
    'tar'        => 'application/x-tar',
    'tcl'        => 'application/x-tcl',
    'tex'        => 'application/x-tex',
    'texinfo'    => 'application/x-texinfo',
    'texi'        => 'application/x-texinfo',
    't'            => 'application/x-troff',
    'tr'        => 'application/x-troff',
    'roff'        => 'application/x-troff',
    'man'        => 'application/x-troff-man',
    'me'        => 'application/x-troff-me',
    'ms'        => 'application/x-troff-ms',
    'ustar'        => 'application/x-ustar',
    'src'        => 'application/x-wais-source',
    'xhtml'        => 'application/xhtml+xml',
    'xht'        => 'application/xhtml+xml',
    'zip'        => 'application/zip',
    'au'        => 'audio/basic',
    'snd'        => 'audio/basic',
    'mid'        => 'audio/midi',
    'midi'        => 'audio/midi',
    'kar'        => 'audio/midi',
    'mpga'        => 'audio/mpeg',
    'mp2'        => 'audio/mpeg',
    'mp3'        => 'audio/mpeg',
    'aif'        => 'audio/x-aiff',
    'aiff'        => 'audio/x-aiff',
    'aifc'        => 'audio/x-aiff',
    'm3u'        => 'audio/x-mpegurl',
    'ram'        => 'audio/x-pn-realaudio',
    'rm'        => 'audio/x-pn-realaudio',
    'rpm'        => 'audio/x-pn-realaudio-plugin',
    'ra'        => 'audio/x-realaudio',
    'wav'        => 'audio/x-wav',
    'pdb'        => 'chemical/x-pdb',
    'xyz'        => 'chemical/x-xyz',
    'bmp'        => 'image/bmp',
    'gif'        => 'image/gif',
    'ief'        => 'image/ief',
    'jpeg'        => 'image/jpeg',
    'jpg'        => 'image/jpeg',
    'jpe'        => 'image/jpeg',
    'png'        => 'image/png',
    'tiff'        => 'image/tiff',
    'tif'        => 'image/tiff',
    'djvu'        => 'image/vnd.djvu',
    'djv'        => 'image/vnd.djvu',
    'wbmp'        => 'image/vnd.wap.wbmp',
    'ras'        => 'image/x-cmu-raster',
    'pnm'        => 'image/x-portable-anymap',
    'pbm'        => 'image/x-portable-bitmap',
    'pgm'        => 'image/x-portable-graymap',
    'ppm'        => 'image/x-portable-pixmap',
    'rgb'        => 'image/x-rgb',
    'xbm'        => 'image/x-xbitmap',
    'xpm'        => 'image/x-xpixmap',
    'xwd'        => 'image/x-xwindowdump',
    'igs'        => 'model/iges',
    'iges'        => 'model/iges',
    'msh'        => 'model/mesh',
    'mesh'        => 'model/mesh',
    'silo'        => 'model/mesh',
    'wrl'        => 'model/vrml',
    'vrml'        => 'model/vrml',
    'css'        => 'text/css',
    'html'        => 'text/html',
    'htm'        => 'text/html',
    'asc'        => 'text/plain',
    'txt'        => 'text/plain',
    'rtx'        => 'text/richtext',
    'rtf'        => 'text/rtf',
    'sgml'        => 'text/sgml',
    'sgm'        => 'text/sgml',
    'tsv'        => 'text/tab-separated-values',
    'wml'        => 'text/vnd.wap.wml',
    'wmls'        => 'text/vnd.wap.wmlscript',
    'etx'        => 'text/x-setext',
    'xsl'        => 'text/xml',
    'xml'        => 'text/xml',
    'mpeg'        => 'video/mpeg',
    'mpg'        => 'video/mpeg',
    'mpe'        => 'video/mpeg',
    'qt'        => 'video/quicktime',
    'mov'        => 'video/quicktime',
    'mxu'        => 'video/vnd.mpegurl',
    'avi'        => 'video/x-msvideo',
    'movie'        => 'video/x-sgi-movie',
    'ice'        => 'x-conference/x-cooltalk',


// Not sure which one is best...
//
    'gz'         => 'application/gzip',
//    'gz'         => 'application/x-gzip',


// More types that duplicate extensions already listed above
// (added the number 2 after the extension... this allows
// File Manager to register as a listener for saving these
// mime types but it will not present these extensions as
// these mime types - it will use the types listed above)
//
    'exe2'    => 'application/x-msdownload',
    'zip2'    => 'application/x-zip-compressed',

);


/*


This is from 

http://www.cs.helsinki.fi/u/hahonen/dimes00/sisalto/cgi_php/mime.html


and might be more accurate...  as time allows, this file should be updated to use the list below (?)
(I hesitate because I read only as far as the doc type and it was application/msword as opposed 
to application/octet-stream.  This may  only be compatible with Windows (but since it's Word, 
does it matter?)...)


Standard MIME types
This is a copy of the mime.types file distributed with the Apache web server. The first word of each line (including the slash) is a valid MIME type. All of the following words on each line are file extentions that the server will map to the given MIME type.
Any of these types may be specified on a CGI's Content-type: header line. The most common would be, for example, 

Content-type: text/html

--------------------------------------------------------------------------------


application/activemessage
application/andrew-inset
application/applefile
application/atomicmail
application/dca-rft
application/dec-dx
application/mac-binhex40	hqx
application/mac-compactpro	cpt
application/macwriteii
application/msword		doc
application/news-message-id
application/news-transmission
application/octet-stream	bin dms lha lzh exe class
application/oda			oda
application/pdf			pdf
application/postscript		ai eps ps
application/powerpoint		ppt
application/remote-printing
application/rtf			rtf
application/slate
application/wita
application/wordperfect5.1
application/x-bcpio		bcpio
application/x-cdlink		vcd
application/x-compress		Z
application/x-cpio		cpio
application/x-csh		csh
application/x-director		dcr dir dxr
application/x-dvi		dvi
application/x-gtar		gtar
application/x-gzip		gz
application/x-hdf		hdf
application/x-httpd-cgi		cgi
application/x-koan		skp skd skt skm
application/x-latex		latex
application/x-mif		mif
application/x-netcdf		nc cdf
application/x-sh		sh
application/x-shar		shar
application/x-stuffit		sit
application/x-sv4cpio		sv4cpio
application/x-sv4crc		sv4crc
application/x-tar		tar
application/x-tcl		tcl
application/x-tex		tex
application/x-texinfo		texinfo texi
application/x-troff		t tr roff
application/x-troff-man		man
application/x-troff-me		me
application/x-troff-ms		ms
application/x-ustar		ustar
application/x-wais-source	src
application/zip			zip
audio/basic			au snd
audio/mpeg			mpga mp2
audio/x-aiff			aif aiff aifc
audio/x-pn-realaudio		ram
audio/x-pn-realaudio-plugin	rpm
audio/x-realaudio		ra
audio/x-wav			wav
chemical/x-pdb			pdb xyz
image/gif			gif
image/ief			ief
image/jpeg			jpeg jpg jpe
image/png			png
image/tiff			tiff tif
image/x-cmu-raster		ras
image/x-portable-anymap		pnm
image/x-portable-bitmap		pbm
image/x-portable-graymap	pgm
image/x-portable-pixmap		ppm
image/x-rgb			rgb
image/x-xbitmap			xbm
image/x-xpixmap			xpm
image/x-xwindowdump		xwd
message/external-body
message/news
message/partial
message/rfc822
multipart/alternative
multipart/appledouble
multipart/digest
multipart/mixed
multipart/parallel
text/html			html htm
text/plain			txt
text/richtext			rtx
text/tab-separated-values	tsv
text/x-setext			etx
text/x-sgml			sgml sgm
video/mpeg			mpeg mpg mpe
video/quicktime			qt mov
video/x-msvideo			avi
video/x-sgi-movie		movie
x-conference/x-cooltalk		ice
x-world/x-vrml			wrl vrml


*/

?>
