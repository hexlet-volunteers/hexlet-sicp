«Named let» is a variant of `let` that has the form

```scheme
(let var bindings body)
```

The `&lt;bindings&gt;` and `&lt;body&gt;` are just as in ordinary `let` , except that `&lt;var&gt;` is bound within `&lt;body&gt;` to a procedure whose body is `&lt;body&gt;` and whose parameters are the variables in the `&lt;bindings&gt;` . Thus, one can repeatedly execute the `&lt;body&gt;` by invoking the procedure named `&lt;var&gt;` . For example, the iterative Fibonacci procedure (section 1.2.2) can be rewritten using named `let` as follows:

```scheme
(define (fib n)
  (let fib-iter ((a 1)
                 (b 0)
                 (count n))
    (if (= count 0)
        b
        (fib-iter (+ a b) a (- count 1)))))
```

Modify `let->combination` of exercise 4.6 to also support named `let` .
