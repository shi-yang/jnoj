/*
 <option value=1 id='g++' selected>G++</option>   0
 <option value=2 id='gcc'>GCC</option>            1
 <option value=3 id=java>Java</option>            2
 <option value=4 id=pascal>Pascal</option>        3
 <option value=5 id=python>Python</option>        4
 <option value=6 id=csharp>C#</option>            5
 <option value=7 id=fortran>Fortran</option>      6
 <option value=8 id=perl>Perl</option>            7
 <option value=9 id=ruby>Ruby</option>            8
 <option value=10 id=ada>Ada</option>             9
 <option value=11 id=sml>SML</option>             10
 <option value=12 id='vc++'>VC++</option>         11
 <option value=13 id=vc>VC</option>               12
 <option value=14 id=clang>CLang</option>         13
 <option value=15 id='clang++'>CLang++</option>   14
 */

function adjustlist(flag, name) {
    var sel = document.getElementById("lang");
    if (name == "SGU") {
        sel.remove(14);
        sel.remove(13);
        for (var i = 10; i >= 6; i--) sel.remove(i);
        sel.remove(4);
        return;
    }
    if (name == "JNU") {
        for (var i = 14; i >= 9; i--) sel.remove(i);
        for (var i = 7; i >= 5; i--) sel.remove(i);
        return;
    }
    if (name == "PKU") {
        sel.remove(14);
        sel.remove(13);
        for (var i = 10; i >= 7; i--) sel.remove(i);
        sel.remove(5);
        sel.remove(4);
        return;
    }
    if (name == "CodeForces") {
        sel.remove(14);
        sel.remove(13);
        sel.remove(12);
        sel.remove(10);
        sel.remove(9);
        sel.remove(7);
        sel.remove(6);
        return;
    }
    if (name == "CodeForcesGym") {
        sel.remove(14);
        sel.remove(13);
        sel.remove(12);
        sel.remove(10);
        sel.remove(9);
        sel.remove(7);
        sel.remove(6);
        return;
    }
    if (name == "HDU") {
        sel.remove(14);
        sel.remove(13);
        for (var i = 10; i >= 6; i--) sel.remove(i);
        sel.remove(5);
        sel.remove(4);
        return;
    }
    if (name == "LightOJ") {
        for (var i = 14; i >= 5; i--) sel.remove(i);
        return;
    }
    if (name == "Ural") {
        for (var i = 14; i >= 6; i--) sel.remove(i);
        sel.remove(4);
        return;
    }
    if (name == "ZJU") {
        for (var i = 14; i >= 8; i--) sel.remove(i);
        sel.remove(6);
        sel.remove(5);
        return;
    }
    if (name == "SPOJ" || name == "CodeChef") {
        for (var i = 14; i >= 10; i--) sel.remove(i);
        return;
    }
    if (name == "UESTC") {
        for (var i = 14; i >= 4; i--) sel.remove(i);
        return;
    }
    if (name == "FZU") {
        sel.remove(14);
        sel.remove(13);
        for (var i = 10; i >= 4; i--) sel.remove(i);
        return;
    }
    if (name == "NBUT") {
        for (var i = 14; i >= 4; i--) sel.remove(i);
        sel.remove(2);
        return;
    }
    if (name == "WHU") {
        for (var i = 14; i >= 4; i--) sel.remove(i);
        return;
    }
    if (name == "SYSU") {
        for (var i = 14; i >= 4; i--) sel.remove(i);
        sel.remove(2);
        return;
    }
    if (name == "Aizu") {
        for (var i = 14; i >= 9; i--) sel.remove(i);
        sel.remove(7);
        sel.remove(6);
        sel.remove(3);
        return;
    }
    if (name == "ACdream") {
        for (var i = 14; i >= 2; i--) sel.remove(i);
        return;
    }

    if (name == "ACdream") {
        for (var i = 14; i >= 3; i--) sel.remove(i);
        return;
    }

    //if (name=="UVALive"||name=="UVA"||name=="OpenJudge"||name=="SCU"||name=="HUST") {
    for (var i = 14; i >= 4; i--) sel.remove(i);
    return;
    //}
}
