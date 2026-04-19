Почему `flatten-stream` использует `delay` явно? Что было бы неправильно в таком определении:

```scheme
(define (flatten-stream stream)
  (if (stream-null? stream)
      the-empty-stream
      (interleave
       (stream-car stream)
       (flatten-stream (stream-cdr stream)))))
```
