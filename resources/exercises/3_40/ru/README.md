Укажите все возможные значения `x` при выполнении

```scheme
(define x 10)

(parallel-execute (lambda () (set! x (* x x)))
                  (lambda () (set! x (* x x x))))
```

Какие из них сохраняются, если вместо этого мы выполняем сериализованные процедуры:

```scheme
(define x 10)

(define s (make-serializer))

(parallel-execute (s (lambda () (set! x (* x x))))
                  (s (lambda () (set! x (* x x x)))))
```
