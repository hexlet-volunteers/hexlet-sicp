Use the monitored stack to explore the tail-recursive property of the evaluator (section 5.4.2). Start the evaluator and define the iterative `factorial` procedure from section 1.2.1:

```scheme
(define (factorial n)
  (define (iter product counter)
    (if (> counter n)
        product
        (iter (* counter product)
              (+ counter 1))))
  (iter 1 1))
```

Run the procedure with some small values of `n` . Record the maximum stack depth and the number of pushes required to compute `n!` for each of these values.

a. You will find that the maximum depth required to evaluate `n!` is independent of `n` . What is that depth?

b. Determine from your data a formula in terms of `n` for the total number of push operations used in evaluating `n!` for any `n ≥ 1` . Note that the number of operations used is a linear function of `n` and is thus determined by two constants.
