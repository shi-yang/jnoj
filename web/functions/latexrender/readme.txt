For other PHP programs create a new folder called latexrender and inside this copy
a) the 2 files: latex.php and class.latexrender.php
b) two empty subfolders /tmp and /pictures which must be writeable by the scripts so may need to be chmod 777

In latex.php change the lines
  $latexrender_path = "/home/domain_name/public_html/phpbb/latexrender";
  $latexrender_path_http = "/phpbb/latexrender";
to reflect your paths.

In your program take the text that you wish to render. Surround any latex code
with the tags [tex]...[/tex]. Suppose this is in the variable $latextext.

For example: 
$latextext = "This is just text but [tex]\sqrt{2}[/tex] should be shown as an image and so should [tex]\frac {1}{2}[/tex]";

Call latexrender with the 2 lines:

include_once('/full_path_here_to/latexrender/latex.php'); 
$latextext=latex_content($latextext);

$latextext will now contain a link to the image in latexrender/pictures

Steve Mayer mayer@dial.pipex.com