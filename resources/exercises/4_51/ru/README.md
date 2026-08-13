Реализуйте новую разновидность присваивания `permanent-set!` — присваивание, которое не отменяется при неудачах. Например, можно выбрать два различных элемента в списке и посчитать, сколько для этого потребовалось попыток, следующим образом:

```scheme
(define count 0)
(let ((x (an-element-of '(a b c)))
      (y (an-element-of '(a b c))))
  (permanent-set! count (+ count 1))
  (require (not (eq? x y)))
  (list x y count))
;;; Starting a new problem
;;; Amb-Eval value:
(a b 2)
;;; Amb-Eval input:
try-again
;;; Amb-Eval value:
(a c 3)
```

Какие значения были бы напечатаны, если бы мы использовали здесь обычный `set!` вместо `permanent-set!` ?
