Exhibit a program that you would expect to run much more slowly without memoization than with memoization. Also, consider the following interaction, where the `id` procedure is defined as in exercise 4.27 and `count` starts at `0` :

```scheme
(define (square x)
  (* x x))
;;; L-Eval input:
(square (id 10))
;;; L-Eval value:

;;; L-Eval input:
count
;;; L-Eval value:

```

Give the responses both when the evaluator memoizes and when it does not.
