Бен Битобор объясняет Хьюго, что один из способов избежать неприятностей в упражнении 3.34 — определить квадратор как новое элементарное ограничение. Заполните недостающие части в Беновой схеме процедуры, реализующей такое ограничение:

```scheme
(define (squarer a b)
  (define (process-new-value)
    (if (has-value? b)
        (if (< (get-value b) 0)
            (error "square less than 0 -- SQUARER" (get-value b))
            )
        ))
  (define (process-forget-value) )
  (define (me request) )
  
  me)
```
