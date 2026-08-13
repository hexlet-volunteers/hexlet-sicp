Suppose we type in the following definitions to the lazy evaluator:

```scheme
(define count 0)
(define (id x)
  (set! count (+ count 1))
  x)
```

Give the missing values in the following sequence of interactions, and explain your answers.

```scheme
(define w (id (id 10)))
;;; L-Eval input:
count
;;; L-Eval value:

;;; L-Eval input:
w
;;; L-Eval value:

;;; L-Eval input:
count
;;; L-Eval value:

```
