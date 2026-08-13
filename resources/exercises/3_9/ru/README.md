В разделе 1.2.1 мы с помощью подстановочной модели анализировали две процедуры вычисления факториала, рекурсивную

```scheme
(define (factorial n)
  (if (= n 1)
      1
      (* n (factorial (- n 1)))))
```

и итеративную

```scheme
(define (factorial n)
  (fact-iter 1 1 n))
(define (fact-iter product counter max-count)
  (if (> counter max-count)
      product
      (fact-iter (* counter product)
                 (+ counter 1)
                 max-count)))
```

Продемонстрируйте, какие структуры окружений возникнут при вычислении `(factorial 6)` с каждой из версий процедуры `factorial` .
