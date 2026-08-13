Какова цель выражений `let` в процедурах `add-assertion!` и `add-rule!` ? Что неправильно в следующем варианте `add-assertion!` ? Подсказка: вспомните определение бесконечного потока единиц из раздела 3.5.2: `(define ones (cons-stream 1 ones))` .

```scheme
(define (add-assertion! assertion)
  (store-assertion-in-index assertion)
  (set! THE-ASSERTIONS
        (cons-stream assertion THE-ASSERTIONS))
  'ok)
```
