Хьюго Дум не понимает, почему процедуры `simple-query` и `disjoin` реализованы через явные операции `delay` , а не следующим образом:

```scheme
(define (simple-query query-pattern frame-stream)
  (stream-flatmap
   (lambda (frame)
     (stream-append (find-assertions query-pattern frame)
                    (apply-rules query-pattern frame)))
   frame-stream))

(define (disjoin disjuncts frame-stream)
  (if (empty-disjunction? disjuncts)
      the-empty-stream
      (interleave
       (qeval (first-disjunct disjuncts) frame-stream)
       (disjoin (rest-disjuncts disjuncts) frame-stream))))
```

Можете ли Вы дать примеры запросов, с которыми эти простые определения приведут к нежелательному поведению?
