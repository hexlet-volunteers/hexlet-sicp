With `permanent-set!` as described in exercise 4.51 and `if-fail` as in exercise 4.52 , what will be the result of evaluating

```scheme
(let ((pairs '()))
  (if-fail (let ((p (prime-sum-pair '(1 3 5 8) '(20 35 110))))
             (permanent-set! pairs (cons p pairs))
             (amb))
           pairs))
```
