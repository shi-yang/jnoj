function varmath_state(k) {
    var old_state = this.state_id;
    this.state_id = k;
    return (old_state == k || (old_state == 0 && k != 2) ||
    old_state < 0 || k <= 0) ? '' : '<span style="padding:0.25ex;"></span>';
}

function varmath_char(a, c) {
    if ('a' <= c.toLowerCase() && c.toLowerCase() <= 'z'
        || c == "'" || 'Α' <= c && c <= 'ϛ') {
        if (0 <= a.style) return a.state(1) + '<i>' + c + '</i>';
        return a.state(1) + c;
    }
    if ('0' <= c && c <= '9') {
        if (a.style <= 0) return a.state(1) + c;
        return a.state(1) + '<i>' + c + '</i>';
    }
    if (c == '_') {
        var current_id = a.state_id;
        return a.state(-1) + '<sub style="font-size:65%">' + varmath_next_char(a) + '</sub>'
            + a.state(0) + a.state(current_id);
    }
    if (c == '^') {
        var current_id = a.state_id;
        return a.state(-1) + '<sup style="font-size:65%">' + varmath_next_char(a) + '</sup>'
            + a.state(0) + a.state(current_id);
    }
    if (c == '{') return varmath(a);
    if (c == '*') return a.state(2) + '<span style="vertical-align:-0.25em;">*</span>';
    if (c == ' ' || c == "\n" || c == "\r") return '';
    if (c == '=' || c == '＝') return a.state(2) + '=';
    if (c == '/' || c == '／' || c == '⁄') return a.state(2) + '⁄';
    if (c == '[' || c == '［') return a.state(1) + '[' + a.state(0);
    if (c == ']' || c == '］') return a.state(1) + ']' + a.state(0);
    if (c == '(' || c == '（') return a.state(1) + '(' + a.state(0);
    if (c == ')' || c == '）') return a.state(1) + ')' + a.state(0);
    if (c == '<' || c == '＜') return a.state(2) + '&lt;';
    if (c == '>' || c == '＞') return a.state(2) + '&gt;';
    if (c == '≤') return a.state(2) + '≤';
    if (c == '≥') return a.state(2) + '≥';
    if (c == '∈') return a.state(2) + '∈';
    if (c == '∋') return a.state(2) + '∋';
    if (c == '⊆') return a.state(2) + '⊆';
    if (c == '≠') return a.state(2) + '≠';
    if (c == '←') return a.state(2) + '←';
    if (c == '→') return a.state(2) + '→';
    if (c == '+') return a.state(1) + a.state(2) + '+';
    if (c == '-' || c == '−') {
        if (a.state_id <= 0) return a.state(0) + '−';
        else if (a.state_id == 1) return a.state(2) + '−';
        else if (a.state_id == 2) return a.state(1) + '−';
    }
    if (c == '±') {
        if (a.state_id <= 0) return a.state(0) + '±';
        else if (a.state_id == 1) return a.state(2) + '±';
        else if (a.state_id == 2) return a.state(1) + '±';
    }
    if (c == '×') return a.state(2) + '×';
    if (c == '√') return a.state(2) + '<span style="letter-spacing:-0.1ex;">√</span>';
    return undefined;
}

function varmath_next_char(a) {
    var c = a.str[a.pos++];
    if (c == undefined) return '';
    var result = varmath_char(a, c);
    if (result != undefined) return result;
    if (a.str.substr(a.pos - 1, 3) == '...') {
        a.pos += 2;
        return a.state(1) + '…';
    }
    if (c == '\\') {
        var cc = a.str[a.pos];
        if (cc == ' ') {
            a.pos++;
            return a.state(0) + '&nbsp;';
        }
        if (cc == ',') {
            a.pos++;
            return a.state(0) + '&thinsp;';
        }
        if (cc == '\\') {
            a.pos++;
            return a.state(0) + '<br>';
        }
        if ('a' <= cc.toLowerCase() && cc.toLowerCase() <= 'z') {
            for (p = a.pos; 'a' <= a.str[p].toLowerCase() &&
            a.str[p].toLowerCase() <= 'z';) p++;
            var key = a.str.substr(a.pos, p - a.pos);
            a.pos = p;
            if (key == 'lt') return varmath_char(a, '<');
            if (key == 'gt') return varmath_char(a, '>');
            if (key == 'neq') return varmath_char(a, '≠');
            if (key == 'leq') return varmath_char(a, '≤');
            if (key == 'geq') return varmath_char(a, '≥');
            if (key == 'in') return varmath_char(a, '∈');
            if (key == 'ni') return varmath_char(a, '∋');
            if (key == 'times') return varmath_char(a, '×');
            if (key == 'pm') return varmath_char(a, '±');
            if (key == 'rightarrow') return varmath_char(a, '→');
            if (key == 'leftarrow') return varmath_char(a, '←');
            if (key == 'subseteq') return varmath_char(a, '⊆');
            if (key == 'rm') {
                a.style = -1;
                return '';
            }
            if (key == 'it') {
                a.style = 1;
                return '';
            }
            if (key == 'frac') {
                return '<table style="display:inline-table;vertical-align:middle;border-collapse:collapse;border-spacing:0;margin-bottom:0.8ex;"><tr><td style="text-align:center;border-bottom:1px solid #000;font-size:90%;padding:0.3ex;">' +
                    a.state(0) + varmath_next_char(a) + '</td></tr><tr><td style="text-align:center;font-size:90%;padding:0.3ex;">' +
                    a.state(0) + varmath_next_char(a) + '</td></tr></table>';
            }
            if (key == 'sqrt') return varmath_char(a, '√') +
                '<span style="border-top:1px solid #000;">' +
                '<span style="font-size:85%;">' +
                varmath_next_char(a) + '</span>&thinsp;</span>';
            if (key == 'mathbb') return '<span style="' +
                'font-weight:bold;-webkit-text-fill-color:#fff;' +
                '-webkit-text-stroke:1px #000;">' +
                varmath_next_char(a).split('<i>').join('').split('</i>').join('') +
                '</span>';
            return '[' + key + ']';
        }
        return a.state(0) + a.str[a.pos++];
    }
    if (a.style <= 0) return a.state(1) + c;
    return a.state(1) + '<i>' + c + '</i>'
}

function varmath(a) {
    if (a.space == undefined) a.space = '';
    if (a.state_id == undefined) a.state_id = -1;
    if (a.style == undefined) a.style = 0;
    var current_style = a.style;
    for (result = ''; a.str[a.pos] != undefined && a.str[a.pos] != '}';)
        result += varmath_next_char(a);
    if (a.str[a.pos] == '}') a.pos++;
    a.style = current_style;
    return result.split('</i><i>').join('');
}

window.onload = function () {
    var t = document.getElementsByTagName('var');
    var tr = {
        '<sub>': '_{', '</sub>': '}', '<sup>': '^{', '</sup>': '}',
        '&lt;': ' \\lt ', '&gt;': ' \\gt ', '&le;': ' \\leq ', '&ge;': ' \\geq '
    };
    for (i in t) {
        var html = t[i].innerHTML + ""
        for (key in tr) html = html.split(key).join(tr[key]);
        var result = varmath({'str': html, 'pos': 0, 'state': varmath_state});
        t[i].innerHTML = '<span style="font-family: \'Times New Roman\', Times, Georgia, serif;font-style:normal;color:#000;letter-spacing:0.15ex;font-size:110%;">' + result + '</span>';
    }
};

