The procedures `+` , `*` and `list` take arbitrary numbers of arguments. One way to define such procedures is to use define with dotted-tail notation. In a procedure definition, a parameter list that has a dot before the last parameter name indicates that, when the procedure is called, the initial parameters (if any) will have as values the initial arguments, as usual, but the final parameter's value will be a list of any remaining arguments. For instance, given the definition

```scheme
(define (f x y . z) )
```

the procedure `f` can be called with two or more arguments. If we evaluate

```scheme
(f 1 2 3 4 5 6)
```

then in the body of `f` , `x` will be `1` , `y` will be `2` , and `z` will be the list `(3 4 5 6)` . Given the definition

```scheme
(define (g . w) )
```

the procedure `g` can be called with zero or more arguments. If we evaluate

```scheme
(g 1 2 3 4 5 6)
```

then in the body of `g` , `w` will be the list `(1 2 3 4 5 6)` .

Use this notation to write a procedure `same-parity` that takes one or more integers and returns a list of all the arguments that have the same even-odd parity as the first argument. For example,

```scheme
(same-parity 1 2 3 4 5 6 7)
(1 3 5 7)

(same-parity 2 3 4 5 6 7)
(2 4 6)
```
