Бен Битобор считает, что лучше было бы реализовать банковский счет таким образом (измененная строка отмечена комментарием):

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
  (let ((protected (make-serializer)))
    (define (dispatch m)
      (cond ((eq? m 'withdraw) (protected withdraw))
            ((eq? m 'deposit) (protected deposit))
            ((eq? m 'balance)
             ((protected (lambda () balance)))) ; serialized
            (else (error "Unknown request -- MAKE-ACCOUNT"
                         m))))
    dispatch))
```

поскольку несериализованный доступ к банковскому счету может привести к неправильному поведению. Вы согласны? Существует ли сценарий, который демонстрирует обоснованность беспокойства Бена?
