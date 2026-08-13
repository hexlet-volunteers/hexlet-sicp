При помощи имитатора можно определять пути данных, которые требуются для реализации машины с данным контроллером. Расширьте ассемблер и заставьте его хранить следующую информацию о модели машины:

    список всех команд, с удаленными дубликатами, отсортированный по типу команды (
    `assign`
    , 
    `goto`
     и так далее).
    
    список (без дубликатов) регистров, в которых хранятся точки входа (это те регистры, которые упоминаются в командах 
    `goto`
    )
    
    список (без дубликатов) регистров, к которым применяются команды 
    `save`
     или 
    `restore`
    .
    
    для каждого регистра, список (без дубликатов) источников, из которых ему присваивается значение (например, для регистра 
    `val`
     в факториальной машине на рисунке 5.11 источниками являются 
    `(const 1)`
     и 
    `((op *) (reg n) (reg val))`
    ).
    

Расширьте интерфейс сообщений машины и включите в него доступ к новой информации. Чтобы проверить свой анализатор, примените его к машине Фибоначчи с рисунка 5.12 и рассмотрите получившиеся списки.

![5.12](/images/exercises/5_12.gif)

```scheme
(controller
   (assign continue (label fact-done))     ; set up final return address
 fact-loop
   (test (op =) (reg n) (const 1))
   (branch (label base-case))
   ;; Set up for the recursive call by saving n and continue.
   ;; Set up continue so that the computation will continue
   ;; at after-fact when the subroutine returns.
   (save continue)
   (save n)
   (assign n (op -) (reg n) (const 1))
   (assign continue (label after-fact))
   (goto (label fact-loop))
 after-fact
   (restore n)
   (restore continue)
   (assign val (op *) (reg n) (reg val))   ; val now contains n(n - 1)!
   (goto (reg continue))                   ; return to caller
 base-case
   (assign val (const 1))                  ; base case: 1! = 1
   (goto (reg continue))                   ; return to caller
 fact-done)
```

Рис. 5.11. Рекурсивная факториальная машина.

```scheme
(controller
   (assign continue (label fib-done))
 fib-loop
   (test (op
