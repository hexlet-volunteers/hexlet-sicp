Внутренняя процедура `accept-action-procedure!`, определенная в `make-wire`, требует, чтобы в момент, когда процедура-действие добавляется к проводу, она немедленно исполнялась. Объясните, зачем требуется такая инициализация. В частности, проследите работу процедуры `half-adder` из этого текста и скажите, как отличалась бы реакция системы, если бы `accept-action-procedure!` была определена как

```scheme
(define (accept-action-procedure! proc)
  (set! action-procedures (cons proc action-procedures)))
```
