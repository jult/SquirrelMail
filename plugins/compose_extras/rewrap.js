/**
  * Removes citation prefixes from beginning of all
  * lines in input text
  *
  * @param string string   The input text 
  * @param string citation The citation string to strip
  *
  * @return string The modified text
  *
  */
function removeCitation(string, citation)
{
   regexp = new RegExp("^" + citation, "mg");
   return string.replace(regexp, "");
}



/* This is essentially a javascript reimplementation of nsInternetCiter::Rewrap
 * from the Mozilla project.  The original code is found in
 * mozilla/editor/libeditor/text/nsInternetCiter.cpp and is triple-licensed
 * under MPL 1.1/GPL 2.0/LGPL 2.1.  This code is available under the same
 * three licenses
 */

function isCiteMarker (c)
{
	return (c == '>')
}

function isSpace (c)
{
	return (c == ' ' || c == '\t')
}

function isWhiteSpace (c)
{
	return (isSpace (c) || c == '\n' || c == '\r')
}

function isNewLine (c)
{
	return (c == '\n')
}

// IE seems to use \r\n in the text so check for that special case
function isIENewLine (s)
{
	return (s.substring(0,2) == '\r\n')
}

function makeCite (citeLevel)
{
	var cite = ''
	for (i = 0; i < citeLevel; i++) {
		cite += '>'
	}
	if (citeLevel != 0) {
		cite += ' '
	}
	return cite
}

// can js return multiple values or do I have to pack them in an array?
function makeNewLine (citeLevel)
{
	var ret_vals = new Array(2)
	var string = '\n'
	var column = 0
	if (citeLevel > 0) {
		string += makeCite (citeLevel)
		column = citeLevel + 1
	} else {
		column = 0
	}
	ret_vals[0] = string
	ret_vals[1] = column
	return ret_vals
}

function sq_rewrap (body, wrapCol)
{
	var outString = ''
	var outStringCol = 0
	var length = body.length
	var posInString = 0
	var citeLevel = 0

	while (posInString < length) {
		// get the new cite level here, since we're at the beginning of
		// a line
		var newCiteLevel = 0
		while (posInString < length && isCiteMarker (body.substring(posInString,posInString+1))) {
			++newCiteLevel
			++posInString
			// skip over any spaces interleaved among the cite
			// markers
			while (posInString < length && isSpace (body.substring(posInString,posInString+1))) {
				++posInString
			}
			if (posInString >= length) {
				break
			}
		}

		// Special case: if this is a blank line, maintain a blank line
		// (i.e. retain the original paragraph breaks)
		if ((isNewLine (body.substring(posInString,posInString+1))
		|| isIENewLine (body.substring(posInString,posInString+2)))
		&& outString.length != 0) {
			if (! isNewLine (outString.substring(outString.length - 1,outString.length))) {
				outString += '\n'
			}
			outString += makeCite (newCiteLevel)
			outString += '\n'
			if (isIENewLine (body.substring(posInString,posInString+2))) {
				posInString += 2
			} else {
				posInString += 1
			}
			outStringCol = 0
			continue
		}

		// If the cite level has changed, then start a new line with
		// the new cite level (but if we're at the beginning of the
		// string don't bother)
		if (newCiteLevel != citeLevel && posInString > newCiteLevel + 1 && outStringCol != 0) {
			arr = makeNewLine (0);
			outString += arr[0]
			outStringCol = arr[1]
		}

		citeLevel = newCiteLevel

		// prepend the quote level if necessary
		if (outStringCol == 0) {
			outString += makeCite (citeLevel)
			outStringCol = citeLevel + (citeLevel ? 1 : 0)
		} else if (outStringCol > citeLevel) {
			// not a cite and we're not at the beginning of a line
			// in the output string so add a space to separate the
			// new text from the previous text
			outString += ' '
			outStringCol++
		}

		// find the next newline -- don't go farther than that
		var nextNewLine = body.indexOf ('\n', posInString)
		if (nextNewLine < 0) {
			nextNewLine = length
		}

		// Don't wrap unquoted lines at all.  The compose window should
		// handle them.  (Maybe revisit this later to handle this?)
		if (citeLevel == 0) {
			outString += body.substring (posInString, nextNewLine)
			outStringCol += nextNewLine - posInString
			if (nextNewLine != length) {
				outString += '\n'
				outStringCol = 0
			}
			posInString = nextNewLine + 1
			continue;
		}

		while (posInString < nextNewLine) {
			// skip over initial spaces
			while (posInString < nextNewLine && isWhiteSpace (body.substring(posInString,posInString+1))) {
				++posInString
			}

			// if this is a short line just append it and continue
			if (outStringCol + nextNewLine - posInString <= wrapCol - citeLevel - 1) {
				// if this short line is the final one in the
				// input string we need to include the final
				// newline, if any
				if (nextNewLine + 1 == length && body.substring(nextNewLine - 1,nextNewLine) == '\n') {
					++nextNewLine
				}

				// trim trailing spaces
				var lastRealChar = nextNewLine
				while (lastRealChar > posInString && isWhiteSpace (body.substring(lastRealChar - 1,lastRealChar))) {
					lastRealChar--
				}

				outString += body.substring (posInString, lastRealChar)
				outStringCol += lastRealChar - posInString
				posInString = nextNewLine + 1
				continue
			}

			var eol = posInString + wrapCol - citeLevel - outStringCol
			// eol is the prospective end of line
			// look backwards from there for a place to break
			// if it's already less than our current position then
			// our current line is too long, so break now
			if (eol <= posInString) {
				arr = makeNewLine (citeLevel);
				outString += arr[0]
				outStringCol = arr[1]
				continue;
			}

			// need to set breakPt properly
			// start at eol and look backwards for whitespace
			var breakPt = eol
			while (breakPt > posInString && ! isWhiteSpace (body.substring(breakPt,breakPt+1))) {
				breakPt--
			}

			// if we haven't found a breakpoint by looking
			// backwards then we need to figure out how to deal
			// with that
			if (breakPt == posInString) {
				// if we are NOT at the beginning then end this
				// line and start a new loop
				if (outStringCol > citeLevel + 1) {
					arr = makeNewLine (citeLevel);
					outString += arr[0]
					outStringCol = arr[1]
					continue;
				} else 
				// just hard break here.  could also try
				// searching forward for a break point, which
				// is what Moz does
				{
					breakPt = eol
				}
			}

			// special case: maybe we should have wrapped last
			// time.  if the first breakpoint here makes the
			// current line too long and there is already text on
			// the current line, break and loop again if at
			// beginning of current line, don't force break
			var SLOP = 6
			if (outStringCol + (breakPt - posInString) > wrapCol + SLOP && outStringCol > citeLevel + 1) {
				arr = makeNewLine (citeLevel);
				outString += arr[0]
				outStringCol = arr[1]
				continue;
			}

			//skip newlines or whitespace at the end of the string
			var subString = body.substring (posInString, breakPt)
			var subEnd = subString.length
			while (subEnd > 0 && isWhiteSpace(subString.substring(subEnd - 1,subEnd))) {
				subEnd--
			}
			subString = subString.slice (0, subEnd)
			outString += subString
			outStringCol += subString.length
			// advance past the whitespace which caused the wrap
			posInString = breakPt
			while (posInString < length && isWhiteSpace(body.substring(posInString,posInString+1))) {
				posInString++
			}
			// add a newline and the quote level to the out string
			if (posInString < length) {
				arr = makeNewLine (citeLevel);
				outString += arr[0]
				outStringCol = arr[1]
			}
		}
	}
	return outString
}


