If we had not realized that `require` could be implemented as an ordinary procedure that uses `amb` , to be defined by the user as part of a nondeterministic program, we would have had to implement it as a special form. This would require syntax procedures

```scheme
(define (require? exp) (tagged-list? exp 'require))

(define (require-predicate exp) (cadr exp))
```

and a new clause in the dispatch in `analyze` :

```scheme
((require? exp) (analyze-require exp))
```

as well the procedure `analyze-require` that handles `require` expressions. Complete the following definition of `analyze-require` .

```scheme
(define (analyze-require exp)
  (let ((pproc (analyze (require-predicate exp))))
    (lambda (env succeed fail)
      (pproc env
             (lambda (pred-value fail2)
               (if 
                   
                   (succeed 'ok fail2)))
             fail))))
```
