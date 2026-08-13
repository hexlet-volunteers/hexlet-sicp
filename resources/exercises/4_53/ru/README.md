Если у нас есть `permanent-set!` , описанное в упражнении 4.51 , и `if-fail` из упражнения 4.52 , то каков будет результат вычисления

```scheme
(let ((pairs '()))
  (if-fail (let ((p (prime-sum-pair '(1 3 5 8) '(20 35 110))))
             (permanent-set! pairs (cons p pairs))
             (amb))
           pairs))
```
