В разделе 3.2.3 мы видели, как модель с окружениями описывает поведение процедур, обладающих внутренним состоянием. Теперь мы рассмотрели, как работают локальные определения. Типичная процедура с передачей сообщений пользуется и тем, и другим. Рассмотрим процедуру моделирования банковского счета из раздела 3.1.1 :

```scheme
(define (make-account balance)
  (define (withdraw amount)
    (if (>= balance amount)
        (begin (set! balance (- balance amount))
               balance)
        "Insufficient funds"))
  (define (deposit amount)
    (set! balance (+ balance amount))
    balance)
  (define (dispatch m)
    (cond ((eq? m 'withdraw) withdraw)
          ((eq? m 'deposit) deposit)
          (else (error "Unknown request -- MAKE-ACCOUNT"
                       m))))
  dispatch)
```

Покажите, какая структура окружений создается последовательностью действий

```scheme
(define acc (make-account 50))

((acc 'deposit) 40)
90

((acc 'withdraw) 60)
30
```

Где хранится внутреннее состояние `acc` ? Предположим, что мы определяем еще один счет

```scheme
(define acc2 (make-account 100))
```

Каким образом удается не смешивать внутренние состояния двух счетов? Какие части структуры окружений общие у `acc` и `acc2` ?
