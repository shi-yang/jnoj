// this is specifically designed so that, if the first "/" is misinterpreted as division,
// then "/;" will be interpreted as an incomplete regexp, causing a failing test.
return /return/;