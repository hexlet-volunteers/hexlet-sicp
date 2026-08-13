Дайте интерпретацию потоку, порождаемому следующей процедурой:

```scheme
(define (expand num den radix)
  (cons-stream
   (quotient (* num radix) den)
   (expand (remainder (* num radix) den) den radix)))
```

(Элементарная процедура `quotient` возвращает целую часть частного двух целых чисел.) Каковы последовательные элементы потока, порожденного выражением `(expand 1 7 10)` ? Что дает вычисление `(expand 3 8 10)` ?
