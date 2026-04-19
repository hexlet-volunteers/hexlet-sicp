Why does `flatten-stream` use `delay` explicitly? What would be wrong with defining it as follows:

```scheme
(define (flatten-stream stream)
  (if (stream-null? stream)
      the-empty-stream
      (interleave
       (stream-car stream)
       (flatten-stream (stream-cdr stream)))))
```
