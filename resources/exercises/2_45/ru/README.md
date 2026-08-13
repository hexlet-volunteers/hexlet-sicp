`Right-split` и `up-split` можно выразить как разновидности общей операции разделения. Определите процедуру `split` с таким свойством, что вычисление

```scheme
(define right-split (split beside below))

(define up-split (split below beside))
```

порождает процедуры `right-split` и `up-split` с таким же поведением, как и определенные ранее.
