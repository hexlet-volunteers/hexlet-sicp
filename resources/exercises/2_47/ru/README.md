Вот два варианта конструкторов для рамок

```scheme
(define (make-frame-list origin edge1 edge2)
  (list origin edge1 edge2))

(define (make-frame-cons origin edge1 edge2)
  (cons origin (cons edge1 edge2)))
```

К каждому из этих конструкторов добавьте соответствующие селекторы, так, чтобы получить реализацию рамок.
