Реализуйте новую конструкцию `if-fail` , которая позволяет пользователю перехватить неудачу при выполнении выражения. `If-fail` принимает два выражения. Первое она выполняет как обычно и, если вычисление успешно, возвращает его результат. Однако если вычисление неудачно, то возвращается значение второго выражения, как в следующем примере:

```scheme
;;; Amb-Eval input:
(if-fail (let ((x (an-element-of '(1 3 5))))
           (require (even? x))
           x)
         'all-odd)
;;; Starting a new problem
;;; Amb-Eval value:
all-odd
;;; Amb-Eval input:
(if-fail (let ((x (an-element-of '(1 3 5 8))))
           (require (even? x))
           x)
         'all-odd)
;;; Starting a new problem
;;; Amb-Eval value:
8
```
