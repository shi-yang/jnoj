## Verifiers List

`verify()` supports all the verifiers listed here! :rocket:

### Array
```
contains
containsEquals
containsOnly
containsOnlyInstancesOf
count
hasKey
hasNotKey
notContains
notContainsEquals
notContainsOnly
notCount
notSameSize
sameSize
```

### BaseObject
```
hasAttribute
notHasAttribute
```

### Callable
```
throws
doesNotThrow
```

### Class
```
hasAttribute
hasStaticAttribute
notHasAttribute
notHasStaticAttribute
```

### Directory
```
doesNotExist
exists
existsAndIsNotReadable
existsAndIsNotWritable
existsAndIsReadable
existsAndIsWritable
isNotReadable
isNotWritable
isReadable
isWritable
```

### File
```
doesNotExists
equals
equalsCanonicalizing
equalsIgnoringCase
exists
existsAndIsNotReadable
existsAndIsNotWritable
existsAndIsReadable
existsAndIsWritable
isNotReadable
isNotWritable
isReadable
isWritable
notEquals
notEqualsCanonicalizing
notEqualsIgnoringCase
```

### JsonFile
```
equalsJsonFile
notEqualsJsonFile
```

### JsonString
```
equalsJsonFile
equalsJsonString
notEqualsJsonFile
notEqualsJsonString
```

### Mixed
```
empty
equals
equalsCanonicalizing
equalsIgnoringCase
equalsWithDelta
false
finite
greaterThan
greaterThanOrEqual
infinite
instanceOf
isArray
isBool
isCallable
isClosedResource
isFloat
isInt
isIterable
isNotArray
isNotBool
isNotCallable
isNotClosedResource
isNotFloat
isNotInt
isNotIterable
isNotNumeric
isNotObject
isNotResource
isNotScalar
isNotString
isNumeric
isObject
isResource
isScalar
isString
lessThan
lessThanOrEqual
nan
notEmpty
notEquals
notEqualsCanonicalizing
notEqualsIgnoringCase
notEqualsWithDelta
notFalse
notInstanceOf
notNull
notSame
notTrue
null
same
true
```

### String
```
containsString
containsStringIgnoringCase
doesNotMatchRegExp
endsWith
equalsFile
equalsFileCanonicalizing
equalsFileIgnoringCase
json
matchesFormat
matchesFormatFile
matchesRegExp
notContainsString
notContainsStringIgnoringCase
notEndsWith
notEqualsFile
notEqualsFileCanonicalizing
notEqualsFileIgnoringCase
notMatchesFormat
notMatchesFormatFile
startsNotWith
startsWith
```

### XmlFile
```
equalsXmlFile
notEqualsXmlFile
```

### XmlString
```
equalsXmlFile
equalsXmlString
notEqualsXmlFile
notEqualsXmlString
```