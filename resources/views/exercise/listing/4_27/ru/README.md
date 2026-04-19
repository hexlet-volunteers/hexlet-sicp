Допустим, мы вводим в ленивый интерпретатор следующее выражение:

```scheme
(define count 0)
(define (id x)
  (set! count (+ count 1))
  x)
```

Вставьте пропущенные значения в данной ниже последовательности действий и объясните свои ответы:

```scheme
(define w (id (id 10)))
;;; L-Eval input:
count
;;; L-Eval value:

;;; L-Eval input:
w
;;; L-Eval value:

;;; L-Eval input:
count
;;; L-Eval value:

```
