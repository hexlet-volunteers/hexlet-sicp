Хьюго Дум пытается переписать первую из процедур `square-list` из упражнения 2.21 так, чтобы она работала как итеративный процесс:

```scheme
(define (square-list items)
  (define (iter things answer)
    (if (null? things)
        answer
        (iter (cdr things)
              (cons (square (car things))
                    answer))))
  (iter items nil))
```

К сожалению, такое определение `square-list` выдает список результатов в порядке, обратном желаемому. Почему?

Затем Хьюго пытается исправить ошибку, обменяв аргументы `cons` :

```scheme
(define (square-list items)
  (define (iter things answer)
    (if (null? things)
        answer
        (iter (cdr things)
              (cons answer
                    (square (car things))))))
  (iter items nil))
```

И так программа тоже не работает. Объясните это.
