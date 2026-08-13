Complete the following definitions of `reverse` (exercise 2.18 ) in terms of `fold-right` and `fold-left` from exercise 2.38 :

```scheme
(define (reverse-right sequence)
  (fold-right (lambda (x y) ) nil sequence))

(define (reverse-left sequence)
  (fold-left (lambda (x y) ) nil sequence))
```
