Бен Битобор говорит, что слишком расточительно в ответ на каждое сообщение `withdraw` и `deposit` создавать по новой сериализованной процедуре. Он говорит, что можно изменить `make-account` так, чтобы все вызовы `protected` происходили вне процедуры `dispatch` . Таким образом, счет будет возвращать одну и ту же сериализованную процедуру (созданную тогда же, когда и сам счет) каждый раз, когда у него просят процедуру снятия денег:

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
    (let ((protected-withdraw (protected withdraw))
          (protected-deposit (protected deposit)))
      (define (dispatch m)
        (cond ((eq? m 'withdraw) protected-withdraw)
              ((eq? m 'deposit) protected-deposit)
              ((eq? m 'balance) balance)
              (else (error "Unknown request -- MAKE-ACCOUNT"
                           m))))
      dispatch)))
```

Безопасно ли такое изменение? В частности, есть ли разница в том, в каком порядке может происходить параллельное выполнение в этих двух версиях `make-account` ?
