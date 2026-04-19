В процедуре `make-withdraw` локальная переменная `balance` создается в виде параметра ` make-withdraw` . Можно было бы создать локальную переменную и явно, используя `let` , а именно:

```scheme
(define (make-withdraw initial-amount)
  (let ((balance initial-amount))
    (lambda (amount)
      (if (>= balance amount)
          (begin (set! balance (- balance amount))
                 balance)
          "Insufficient funds"))))
```

Напомним, что в разделе 1.3.2 говорится, что `let` всего лишь синтаксический сахар для вызова процедуры:

```scheme
(let (( )) )
```

интерпретируется как альтернативный синтаксис для

```scheme
((lambda () ) )
```

С помощью модели с окружениями проанализируйте альтернативную версию `make-withdraw` . Нарисуйте картинки, подобные приведенным в этом разделе, для выражений

```scheme
(define W1 (make-withdraw 100))

(W1 50)

(define W2 (make-withdraw 100))
```

Покажите, что две версии `make-withdraw` создают объекты с одинаковым поведением. Как различаются структуры окружений в двух версиях?
