Рассмотрим последовательность выражений

```scheme
(define sum 0)

(define (accum x)
  (set! sum (+ x sum))
  sum)

(define seq (stream-map accum (stream-enumerate-interval 1 20)))

(define y (stream-filter even? seq))

(define z (stream-filter (lambda (x) (= (remainder x 5) 0))
                         seq))
(stream-ref y 7)

(display-stream z)
```

Каково значение `sum` после вычисления каждого из этих выражений? Что печатается при вычислении выражений `stream-ref` и `display-stream` ? Изменился бы этот результат, если бы мы реализовали `(delay &lt;exp&gt;)` просто как `(lambda () &lt;exp&gt;)` , не применяя оптимизацию через `memo-proc` ? Объясните свой ответ.
