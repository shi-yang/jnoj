Thanks to treo for providing these instructions for Simple Minds Forum

http://www.simplemachines.org/community/index.php?topic=12724.msg119412#msg119412
----------------------------------------------------------------------------------
You can see a demo at
http://demoboard.hostspace.ath.cx/index.php?topic=2.0

--------------------------------------------------------------------------------
If you have RC1 you may need to paste the contents of latex.php it into the SMF Subs.php
See README_SMF.txt in the mimetex folder for a similar example.
--------------------------------------------------------------------------------

What you need is (debian packages):
tetex
tetex-extra
gs (useful though not used by LatexRender)
imagemagick

and

http://www.mayer.dial.pipex.com/latexrender.zip

Safe Mode must be off

From the latexrender.zip take only the otherPHP/latexrender folder,
edit the latex.php in it so it fits your environment
Rename the latexrender folder to latex and put it into your Sources folder.

chmod 777 latex/pictures
chmod 777 latex/tmp

try out whether it works with the example.php (open http://www.example.com/board/Sources/example.php), if it works delete example.php

In Subs.php:
Search for( should be at line 726):
                }
        }
        $message = substr(implode('', $parts), 1);

        // Fix things.
        $message = str_replace(
                array('{<{', '}>}', '  ', "\r", "\n"),

and replace it with:

                }
        }
        $message = substr(implode('', $parts), 1);
        include_once('Sources/latex/latex.php');
        $message = latex_content($message);
        // Fix things.
        $message = str_replace(
                array('{<{', '}>}', '  ', "\r", "\n"),

Now you can use [tex][/tex] in your Board.