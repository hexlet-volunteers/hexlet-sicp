Implement register machines for the following procedures. Assume that the list-structure memory operations are available as machine primitives.

```scheme
(define (count-leaves tree)
  (cond ((null? tree) 0)
        ((not (pair? tree)) 1)
        (else (+ (count-leaves (car tree))
                 (count-leaves (cdr tree))))))
```

a. Recursive `count-leaves` :

```scheme
(define (count-leaves tree)
  (define (count-iter tree n)
    (cond ((null? tree) n)
          ((not (pair? tree)) (+ n 1))
          (else (count-iter (cdr tree)
                            (count-iter (car tree) n)))))
  (count-iter tree 0))
```

b. Recursive `count-leaves` with explicit counter:
