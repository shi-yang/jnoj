<?php
/**
 * LaTeX Rendering Class - Simple Usage Example
 * Copyright (C) 2003  Benjamin Zeiss <zeiss@math.uni-goettingen.de>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * --------------------------------------------------------------------
 * @author Benjamin Zeiss <zeiss@math.uni-goettingen.de>
 * @version v0.8
 * @package latexrender
 *
 */
// the final image is shown on the page using
// echo latex_content($text);

// this is just an example page
echo "<html><title>LatexRender Demo</title>
    <head><script language=\"JavaScript\" type=\"text/javascript\">
	function addtags() {
		if (document.selection.createRange().text!='') {
	  		document.selection.createRange().text = '[tex]'+document.selection.createRange().text+'[/tex]';
	  	}
	}//--></script></head>";
echo "<body bgcolor='lightgrey'><center><h3>LatexRender Demo</h3>";
echo "<font size=-1><i>Add tags around text you want to convert to an image<br>
    or press the button to add them around highlighted text</i></font>";

echo "<form method='post'>";
echo "<input onclick=\"addtags()\" type=\"button\" value=\"Add TeX tags\" name=\"btnCopy\"><br><br>";
echo "<textarea name='latex_formula' rows=8 cols=50>";

if (isset($_POST['latex_formula'])) {
    echo stripslashes($_POST['latex_formula']);
} else {
    echo "Example Text:\nThis is just text but [tex]\sqrt{2}[/tex] should be shown as an image and so should [tex]\frac {1}{2}[/tex].
			\nAnother formula is [tex]\frac {43}{12} \sqrt {43}[/tex]";
}

echo "</textarea>";
echo "<br><br><input type='submit' value='Render'>";
echo "</form>";

if (isset($_POST['latex_formula'])) {
    $text = stripslashes($_POST['latex_formula']);
    echo "<u>Result</u><br><br>";
    // now convert and show the image
    include_once("latex.php");
    echo nl2br(latex_content($text));
}

echo "</center></body></html>";
?>
