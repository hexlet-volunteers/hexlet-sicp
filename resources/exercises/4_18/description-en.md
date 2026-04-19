Consider an alternative strategy for scanning out definitions that translates the example in the text to

```scheme
(lambda 
  (let ((u '*unassigned*)
        (v '*unassigned*))
    (let ((a )
          (b ))
      (set! u a)
      (set! v b))
    ))
```

Here `a` and `b` are meant to represent new variable names, created by the interpreter, that do not appear in the user's program. Consider the `solve` procedure from section 3.5.4:

```scheme
(define (solve f y0 dt)
  (define y (integral (delay dy) y0 dt))
  (define dy (stream-map f y))
  y)
```

Will this procedure work if internal definitions are scanned out as shown in this exercise? What if they are scanned out as shown in the text? Explain.
