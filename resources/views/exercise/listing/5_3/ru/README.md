Спроектируйте машину для вычисления квадратных корней методом Ньютона, как описано в разделе 1.1.7:

```scheme
(define (sqrt x)
  (define (good-enough? guess)
    (< (abs (- (square guess) x)) 0.001))
  (define (improve guess)
    (average guess (/ x guess)))
  (define (sqrt-iter guess)
    (if (good-enough? guess)
        guess
        (sqrt-iter (improve guess))))
  (sqrt-iter 1.0))
```

Для начала предположите, что операции `good-enough?` и `improve` имеются как примитивы. Затем покажите, как развернуть их с помощью арифметических операций. Опишите каждую из версий машины `sqrt` , нарисовав диаграмму путей данных, и написав определение контроллера на языке регистровых машин.
