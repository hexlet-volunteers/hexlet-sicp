Бен Битобор утверждает, что следующий метод порождения Пифагоровых троек эффективнее, чем приведенный в упражнении 4.35 . Прав ли он? (Подсказка: найдите, сколько вариантов требуется рассмотреть.)

```scheme
(define (a-pythagorean-triple-between low high)
  (let ((i (an-integer-between low high))
        (hsq (* high high)))
    (let ((j (an-integer-between i high)))
      (let ((ksq (+ (* i i) (* j j))))
        (require (>= hsq ksq))
        (let ((k (sqrt ksq)))
          (require (integer? k))
          (list i j k))))))
```
