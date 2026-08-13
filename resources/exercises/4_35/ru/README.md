Напишите процедуру `an-integer-between` , которая возвращает целое число, лежащее между двумя заданными границами. С ее помощью можно следующим образом реализовать процедуру для поиска Пифагоровых троек, то есть троек чисел `(i, j, k)` между заданными границами, таких, что `i ≤ j` и `i² + j² = k²` :

```scheme
(define (a-pythagorean-triple-between low high)
  (let ((i (an-integer-between low high)))
    (let ((j (an-integer-between i high)))
      (let ((k (an-integer-between j high)))
        (require (= (+ (* i i) (* j j)) (* k k)))
        (list i j k)))))
```
