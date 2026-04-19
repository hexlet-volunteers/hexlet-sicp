Реализуйте регистровые машины для следующих процедур. Считайте, что операции с памятью, реализующие списковую структуру, имеются в машине как примитивы.

```scheme
(define (count-leaves tree)
  (cond ((null? tree) 0)
        ((not (pair? tree)) 1)
        (else (+ (count-leaves (car tree))
                 (count-leaves (cdr tree))))))
```

а. Рекурсивная `count-leaves` :

```scheme
(define (count-leaves tree)
  (define (count-iter tree n)
    (cond ((null? tree) n)
          ((not (pair? tree)) (+ n 1))
          (else (count-iter (cdr tree)
                            (count-iter (car tree) n)))))
  (count-iter tree 0))
```

б. Рекурсивная `count-leaves` с явным счетчиком:
