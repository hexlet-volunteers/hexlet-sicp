Абстрагируйте свой ответ на упражнение 2.30 , получая процедуру `tree-map` , так, чтобы `square-tree` можно было определить следующим образом:

```scheme
(define (square-tree tree) (tree-map square tree))
```
