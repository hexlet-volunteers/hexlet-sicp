Вышеприведенная процедура `integral` была аналогична «непрямому» определению бесконечного потока натуральных чисел из раздела 3.5.2. В виде альтернативы можно дать определение `integral` , более похожее на `integers-starting-from` (также в разделе 3.5.2):

```scheme
(define (integral integrand initial-value dt)
  (cons-stream initial-value
               (if (stream-null? integrand)
                   the-empty-stream
                   (integral (stream-cdr integrand)
                             (+ (* dt (stream-car integrand))
                                initial-value)
                             dt))))
```

В системах с циклами эта реализациея порождает такие же проблемы, как и наша исходная версия `integral` . Модифицируйте процедуру так, чтобы она ожидала `integrand` как задержанный аргумент, а следовательно, могла быть использована в процедуре `solve` .
