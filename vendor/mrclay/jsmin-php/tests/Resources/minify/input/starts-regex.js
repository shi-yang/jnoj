// this is specifically designed so that, if the first "/" is misinterpreted as division,
// then "/.test(bar);" will be interpreted as an incomplete regexp, causing a failing test.
/return/.test(bar);