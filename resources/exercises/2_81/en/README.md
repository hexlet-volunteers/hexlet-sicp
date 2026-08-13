Louis Reasoner has noticed that `apply-generic` may try to coerce the arguments to each other's type even if they already have the same type. Therefore, he reasons, we need to put procedures in the coercion table to 'coerce' arguments of each type to their own type. For example, in addition to the `scheme-number->complex` coercion shown above, he would do:

```scheme
(define (scheme-number->scheme-number n) n)
(define (complex->complex z) z)
(put-coercion 'scheme-number 'scheme-number
              scheme-number->scheme-number)
(put-coercion 'complex 'complex complex->complex)
```

a. With Louis's coercion procedures installed, what happens if `apply-generic` is called with two arguments of type `scheme-number` or two arguments of type `complex` for an operation that is not found in the table for those types? For example, assume that we've defined a generic exponentiation operation:

```scheme
(define (exp x y) (apply-generic 'exp x y))
```

and have put a procedure for exponentiation in the `scheme-number` package but not in any other package:

;; following added to `scheme-number` package

```scheme
(put 'exp '(scheme-number scheme-number)
     (lambda (x y) (tag (expt x y)))) ; using primitive expt
```

What happens if we call `exp` with two complex numbers as arguments?

b. Is Louis correct that something had to be done about coercion with arguments of the same type, or does all work correctly as is?

c. Modify `apply-generic` so that it doesn't try coercion if the two arguments have the same type.
