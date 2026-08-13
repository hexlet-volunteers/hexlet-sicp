Louis Reasoner thinks that building a stream of pairs from three parts is unnecessarily complicated. Instead of separating the pair `(S₀, T₀)` from the rest of the pairs in the first row, he proposes to work with the whole first row, as follows:

```scheme
(define (pairs s t)
  (interleave
   (stream-map (lambda (x) (list (stream-car s) x))
               t)
   (pairs (stream-cdr s) (stream-cdr t))))
```

Does this work? Consider what happens if we evaluate `(pairs integers integers)` using Louis's definition of pairs.
