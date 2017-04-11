<?php
$pagetitle = "Problem Category";
include_once("header.php");
include_once("functions/sidebars.php");
include_once("functions/problems.php");
?>
<div class="span9">
    <h2>Categories</h2>
    <form method="post" action="problem_category_result.php">
        <div class="well">
            <ul>
                <?php
                $res = problem_get_category();
                for ($i = 0; $i < sizeof($res); $i++) {
                    echo "<li><label class='checkbox inline'><input class='ccheck' type='checkbox' value='" . $res[$i]['id'] . "' name='check" . $res[$i]['id'] . "' /> " . $res[$i]['name'] . "</label>";
                    if ($res[$i]["depth"] < $res[$i + 1]["depth"]) echo " <button class='btn btn-mini cexpand'><i class='icon-plus'></i></button><button class='btn btn-mini chide hide'><i class='icon-minus'></i></button></li><ul class='hide'>\n";
                    else echo "</li>\n";
                    if ($res[$i]["depth"] > $res[$i + 1]["depth"]) echo str_repeat("</ul>\n", $res[$i]["depth"] - $res[$i + 1]["depth"]);
                }

                ?>
            </ul>
            <label class="radio inline"><input name="logic" value="or" type="radio" checked="checked"> OR</label> <label
                class="radio inline"><input name="logic" value="and" type="radio"> AND</label>
            <p> ( <b>Note:</b> If a node is checked, all its children will be ignored. )</p>
        </div>
        <button class="btn btn-primary" type="submit">Submit</button>
    </form>
</div>
<div class="span3">
    <?= sidebar_common() ?>
</div>

<script type="text/javascript">
    $(".ccheck").change(function () {
        if ($(this).prop('checked') === true) {
            $(".ccheck", $(this).parent().parent().next("ul")).prop('checked', true);
            $val = $(this).parent().parent();
            $uc = $("input:not(:checked)", $val.next());
            while ($val.length && $uc.length == 0) {
                $(".ccheck:first", $val).prop('checked', true);
                $val = $val.parent().prev();
                $uc = $("input:not(:checked)", $val.next());
            }
        }
        else {
            $(".ccheck", $(this).parent().parent().next("ul")).prop('checked', false);
            $val = $(this).parent().parent();
            while ($val.length) {
                $(".ccheck:first", $val).prop('checked', false);
                $val = $val.parent().prev();
            }
        }
    });
    $(".cexpand").click(function () {
        $(this).parent().next().show(500);
        $(this).hide();
        $(this).next().show();
        return false;
    });
    $(".chide").click(function () {
        $(this).parent().next().hide(500);
        $(this).hide();
        $(this).prev().show();
        return false;
    });
    $("#problem").addClass("active");
</script>

<?php
include("footer.php");
?>
