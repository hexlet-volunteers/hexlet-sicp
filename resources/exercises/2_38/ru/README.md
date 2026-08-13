Процедура accumulate известна также как `fold-right` (правая свертка), поскольку она комбинирует первый элемент последовательности с результатом комбинирования всех элементов справа от него. Существует также процедура `fold-left` (левая свертка), которая подобна `fold-right` , но комбинирует элементы в противоположном направлении:

```scheme
(define (fold-left op initial sequence)
  (define (iter result rest)
    (if (null? rest)
        result
        (iter (op result (car rest))
              (cdr rest))))
  (iter initial sequence))
```

Каковы значения следующих выражений?

```scheme
(fold-right / 1 (list 1 2 3))

(fold-left / 1 (list 1 2 3))

(fold-right list nil (list 1 2 3))

(fold-left list nil (list 1 2 3))
```

Укажите свойство, которому должна удовлетворять `op` , чтобы для любой последовательности `fold-right` и `fold-left` давали одинаковые результаты.
