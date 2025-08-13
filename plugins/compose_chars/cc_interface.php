<html>
   	<head>
		<title>Special Characters</title>
	</head>
	<script language="Javascript" type="text/javascript">
	
		var target;
	
		function handleListChange(my_list) {			
	    	var numSelected = my_list.selectedIndex;
	    
	    	if (numSelected != 0) {
	        	document.characters.textbox.value += my_list.options[numSelected].value;
	        	my_list.selectedIndex = 0;
	    	}	
		}	
	</script>
	
	<body text="#000000" bgcolor="#ffffff" link="#000000" vlink="#000000" alink="#000000">
		<form name="characters">
			<table border=0>
				<tr>
					<td colspan="6">Select the character you want, and then <br>copy - paste them 
					into your compose window.
					</td>
				</tr>
				<tr>
					<td>
            			<select name="a" onchange="handleListChange(this)">
							<option value="a" selected> a </option>
	
				            <option value="&Aacute;"> &Aacute; </option>
				            <option value="&AElig;"> &AElig; </option>
				            <option value="&Acirc;"> &Acirc; </option>
				            <option value="&Agrave;"> &Agrave; </option>
				            <option value="&Aring;"> &Aring; </option>
				            <option value="&Atilde;"> &Atilde; </option>
				            <option value="&Auml;"> &Auml; </option>

				            <option value="&aacute;"> &aacute; </option>
				            <option value="&aelig;"> &aelig; </option>
				            <option value="&acirc;"> &acirc; </option>
				            <option value="&agrave;"> &agrave; </option>
				            <option value="&aring;"> &aring; </option>
				            <option value="&atilde;"> &atilde; </option>
				            <option value="&auml;"> &auml; </option>
						</select>
					</td>
					<td>
            			<select name="e" onchange="handleListChange(this)">
	              			<option value="e" selected> e </option>
	
				            <option value="&Eacute;"> &Eacute; </option>
				            <option value="&Ecirc;"> &Ecirc; </option>
				            <option value="&Egrave;"> &Egrave; </option>
				            <option value="&Euml;"> &Euml; </option>

				            <option value="&eacute;"> &eacute; </option>
				            <option value="&ecirc;"> &ecirc; </option>
				            <option value="&egrave;"> &egrave; </option>
				            <option value="&euml;"> &euml; </option>
						</select>
					</td>
					<td>
            			<select name="i" onchange="handleListChange(this)">
	              			<option value="i" selected> i </option>
	
				            <option value="&Iacute;"> &Iacute; </option>
				            <option value="&Icirc;"> &Icirc; </option>
				            <option value="&Igrave;"> &Igrave; </option>
				            <option value="&Iuml;"> &Iuml; </option>

				            <option value="&iacute;"> &iacute; </option>
				            <option value="&icirc;"> &icirc; </option>
				            <option value="&igrave;"> &igrave; </option>
				            <option value="&iuml;"> &iuml; </option>
						</select>
					</td>
					<td>
            			<select name="o" onchange="handleListChange(this)">
	              			<option value="o" selected> o </option>

				            <option value="&Oacute;"> &Oacute; </option>
				            <option value="&OElig;"> &OElig; </option>
				            <option value="&Ocirc;"> &Ocirc; </option>
				            <option value="&Ograve;"> &Ograve; </option>
				            <option value="&Oslash;"> &Oslash; </option>
				            <option value="&Otilde;"> &Otilde; </option>
				            <option value="&Ouml;"> &Ouml; </option>

				            <option value="&oacute;"> &oacute; </option>
				            <option value="&oelig;"> &oelig; </option>
				            <option value="&ocirc;"> &ocirc; </option>
				            <option value="&ograve;"> &ograve; </option>
				            <option value="&oslash;"> &oslash; </option>
				            <option value="&otilde;"> &otilde; </option>
				            <option value="&ouml;"> &ouml; </option>
						</select>
					</td>
					<td>
            			<select name="u" onchange="handleListChange(this)">
	              			<option value="u" selected> u </option>

				            <option value="&Uacute;"> &Uacute; </option>
				            <option value="&Ucirc;"> &Ucirc; </option>
				            <option value="&Ugrave;"> &Ugrave; </option>
				            <option value="&Uuml;"> &Uuml; </option>

				            <option value="&uacute;"> &uacute; </option>
				            <option value="&ucirc;"> &ucirc; </option>
				            <option value="&ugrave;"> &ugrave; </option>
				            <option value="&uuml;"> &uuml; </option>
						</select>
					</td>
					<td>
            			<select name="other" onchange="handleListChange(this)">
	              			<option value="other" selected> Other </option>

				            <option value="&Ccedil;"> &Ccedil; </option>
				            <option value="&ETH;"> &ETH; </option>
				            <option value="&Ntilde;"> &Ntilde; </option>
				            <option value="&szlig;"> &szlig; </option>
				            <option value="&THORN;"> &THORN; </option>
				            <option value="&Yacute;"> &Yacute; </option>
				            <option value="&Yuml;"> &Yuml; </option>

				            <option value="&ccedil;"> &ccedil; </option>
				            <option value="&eth;"> &eth; </option>
				            <option value="&ntilde;"> &ntilde; </option>
				            <option value="&thorn;"> &thorn; </option>
				            <option value="&yacute;"> &yacute; </option>
				            <option value="&yuml;"> &yuml; </option>
	
				            <option value="&not;"> &not; </option>
				            <option value="&iexcl;"> &iexcl; </option>
				            <option value="&iquest;"> &iquest; </option>
				            <option value="&deg;"> &deg; </option>
				            <option value="&curren;"> &curren; </option>
				            <option value="&euro;"> &euro; </option>
				            <option value="&yen;"> &yen; </option>
				            <option value="&pound;"> &pound; </option>
				            <option value="&#36;"> &#36; </option>
				            <option value="&cent;"> &cent; </option>
				            <option value="&sect;"> &sect; </option>
				            <option value="&uml;"> &uml; </option>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="6" align="center">
						<textarea rows="4" cols="25" name="textbox"></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="6" align="center">
						<input type="button" onclick="window.close();" name="close" value="Close Window" />
					</td>
				</tr>
			</table>
		</form>
	</body>
</html>	
