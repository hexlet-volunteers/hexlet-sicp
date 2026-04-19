With power series represented as streams of coefficients as in exercise 3.59 , adding series is implemented by `add-streams` . Complete the definition of the following procedure for multiplying series:

```scheme
(define (mul-series s1 s2)
  (cons-stream  (add-streams  )))
```

You can test your procedure by verifying that `sin²x + cos²x = 1` , using the series from exercise 3.59
