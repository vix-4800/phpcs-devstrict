# DevStrict Coding Standard Rules

This document describes the custom coding standard rules implemented in the DevStrict PHPCS ruleset.

## Table of Contents

- [DevStrict Coding Standard Rules](#devstrict-coding-standard-rules)
  - [Table of Contents](#table-of-contents)
  - [Functions](#functions)
    - [DevStrict.Functions.DisallowIsNull](#devstrictfunctionsdisallowisnull)
    - [DevStrict.Functions.DisallowCompact](#devstrictfunctionsdisallowcompact)
  - [Control Structures](#control-structures)
    - [DevStrict.ControlStructures.DisallowThrowInTernary](#devstrictcontrolstructuresdisallowthrowinternary)
    - [DevStrict.ControlStructures.UseInArray](#devstrictcontrolstructuresuseinarray)

---

## Functions

### DevStrict.Functions.DisallowIsNull

**Type:** Warning

**Description:** Disallows the use of `is_null()` function. Use strict comparison with `=== null` instead for better
readability and consistency.

**Bad:**

```php
if (is_null($variable)) {
    // do something
}

$result = is_null($data) ? 'empty' : 'filled';
```

**Good:**

```php
if ($variable === null) {
    // do something
}

$result = $data === null ? 'empty' : 'filled';
```

---

### DevStrict.Functions.DisallowCompact

**Type:** Error

**Description:** Disallows the use of `compact()` function. The `compact()` function creates an array from variables and
their values, but it makes code less explicit and harder to track variable usage. Use explicit array syntax instead for
better readability and maintainability.

**Bad:**

```php
$name = 'John';
$age = 30;
$email = 'john@example.com';

$data = compact('name', 'age', 'email');

return compact('user', 'posts', 'comments');
```

**Good:**

```php
$name = 'John';
$age = 30;
$email = 'john@example.com';

$data = [
    'name' => $name,
    'age' => $age,
    'email' => $email,
];

return [
    'user' => $user,
    'posts' => $posts,
    'comments' => $comments,
];
```

---

## Control Structures

### DevStrict.ControlStructures.DisallowThrowInTernary

**Type:** Error

**Description:** Disallows throwing exceptions within ternary operators. Throwing exceptions in ternary expressions
reduces code readability and makes error handling less explicit.

**Bad:**

```php
$value = $condition
    ? $result
    : throw new Exception('Invalid condition');

$data = $isValid ? getData() : throw new RuntimeException('Invalid data');
```

**Good:**

```php
if (!$condition) {
    throw new Exception('Invalid condition');
}
$value = $result;

if (!$isValid) {
    throw new RuntimeException('Invalid data');
}
$data = getData();
```

---

### DevStrict.ControlStructures.UseInArray

**Type:** Warning

**Description:** Detects multiple OR/AND comparisons of the same variable and suggests using `in_array()` or
`!in_array()` instead. This makes the code more concise, readable, and easier to maintain. The sniff triggers when
there are 3 or more comparisons of the same variable.

**Bad:**

```php
if ($site_id === 1 || $site_id === 2 || $site_id === 3) {
    // do something
}

if ($status !== 'pending' && $status !== 'processing' && $status !== 'cancelled') {
    // do something
}
```

**Good:**

```php
if (in_array($site_id, [1, 2, 3], true)) {
    // do something
}

if (!in_array($status, ['pending', 'processing', 'cancelled'], true)) {
    // do something
}
```
