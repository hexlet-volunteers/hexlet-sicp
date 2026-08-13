The `celsius-fahrenheit-converter` procedure is cumbersome when compared with a more expression-oriented style of definition, such as

```scheme
(define (celsius-fahrenheit-converter x)
  (c+ (c* (c/ (cv 9) (cv 5))
          x)
      (cv 32)))

(define C (make-connector))

(define F (celsius-fahrenheit-converter C))
```

Here `c+` , `c*` , etc. are the ''constraint'' versions of the arithmetic operations. For example, `c+` takes two connectors as arguments and returns a connector that is related to these by an adder constraint:

```scheme
(define (c+ x y)
  (let ((z (make-connector)))
    (adder x y z)
    z))
```

Define analogous procedures `c-` , `c*` , `c/` , and `cv` (constant value) that enable us to define compound constraints as in the converter example above.
