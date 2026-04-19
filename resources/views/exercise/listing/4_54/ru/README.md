Если бы мы не догадались, что конструкцию `require` можно реализовать как обычную процедуру с помощью `amb` , так что пользователь сам может определить ее в своей недетерминистской программе, то нам пришлось бы задать эту конструкцию в виде особой формы. Потребовались бы синтаксические процедуры

```scheme
(define (require? exp) (tagged-list? exp 'require))

(define (require-predicate exp) (cadr exp))
```

новая ветвь разбора в `analyze` :

```scheme
((require? exp) (analyze-require exp))
```

а также процедура `analyze-require` , которая обрабатывает выражения `require` . Допишите следующее определение `analyze-require` :

```scheme
(define (analyze-require exp)
  (let ((pproc (analyze (require-predicate exp))))
    (lambda (env succeed fail)
      (pproc env
             (lambda (pred-value fail2)
               (if 
                   
                   (succeed 'ok fail2)))
             fail))))
```
