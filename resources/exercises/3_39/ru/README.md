Какие из пяти возможных исходов параллельного выполнения сохраняются, если мы сериализуем выполнение таким образом:

```scheme
(define x 10)

(define s (make-serializer))

(parallel-execute (lambda () (set! x ((s (lambda () (* x x))))))
                  (s (lambda () (set! x (+ x 1)))))
```
