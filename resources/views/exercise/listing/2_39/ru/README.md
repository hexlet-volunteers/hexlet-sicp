Закончите следующие определения `reverse` (упражнение 2.18 ) в терминах процедур `fold-right` и `fold-left` из упражнения 2.38 :

```scheme
(define (reverse-right sequence)
  (fold-right (lambda (x y) ) nil sequence))

(define (reverse-left sequence)
  (fold-left (lambda (x y) ) nil sequence))
```
