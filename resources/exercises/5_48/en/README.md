The `compile-and-go` interface implemented in this section is awkward, since the compiler can be called only once (when the evaluator machine is started). Augment the compiler-interpreter interface by providing a `compile-and-run` primitive that can be called from within the explicit-control evaluator as follows:

```scheme
;;; EC-Eval input:
(compile-and-run
  '(define (factorial n)
    (if (= n 1)
        1
        (* (factorial (- n 1)) n))))

;;; EC-Eval value:
ok

;;; EC-Eval input:
(factorial 5)

;;; EC-Eval value:
120
```
